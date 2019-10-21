<?php
/**
 * Created by PhpStorm.
 * User: shj
 * Date: 2019/9/4
 * Time: 下午5:52
 */

namespace App\Admin\Controllers\Auth;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    const USER_PASSWORD = 'mine:manage:admin:user:password:';
    const SEND_MANAGE_PHONE_CODE = 'mine:manage:admin:phone:code:';
    const SEND_MANAGE_EMAIL_CODE = 'mine:manage:admin:email:code:';

    //手机验证验证码是否正确的情况
    const VERIFY_PHONE_CODE = 'https://et-api.et.exchange/v1/api/verifyphonecode';

    //手机验证验证码是否正确的情况
    const VERIFY_PHONE_CODE_DEV = 'https://dev-api.et.exchange/v1/api/verifyphonecode';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view('admin.login');
    }

    public function verifyLogin(Request $request)
    {

        $data = $request->only([$this->username(), 'password', 'captcha']);


        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($data, [
            $this->username() => 'required',
            'password' => 'required',
            'captcha' => 'required|captcha',
        ], [
            'captcha.required' => '请输入验证码',
            'captcha.captcha' => '验证码错误',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $username = $data['username'];
        $user = Administrator::where('username', $username)->first();

        if (empty($user)) {
            return back()->withInput()->withErrors(['empty_user' => 1]);
        }

        $key = self::USER_PASSWORD . $user->id;

        $credentials = $request->only([$this->username(), 'password']);
        if ($this->guard()->attempt($credentials)) {

            if (!empty($user->phone)) {
                Redis::set($key, $data['password']);
                return redirect()->route('loginPhone', ['id' => $user->id]);
            }

            if ($username == 'admin') {
                $credentials = $request->only([$this->username(), 'password']);
                if ($this->guard()->attempt($credentials)) {
                    return $this->sendLoginResponse($request);
                }
            }

        } else {
            return back()->withInput()->withErrors([
                $this->username() => $this->getFailedLoginMessage(),
            ]);
        }
        return back()->withInput()->withErrors(['verify_login' => 1]);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $user_id = $request->id;
        $user = Administrator::where('id', $user_id)->first();
        $key = self::USER_PASSWORD . $user_id;
        $password = Redis::get($key);
        $credentials = ['username' => $user->username, 'password' => $password];
        $phone = $user->phone;
        $minute_ago_key = self::SEND_MANAGE_PHONE_CODE . $phone;
        Redis::DEL($minute_ago_key);
        $url = self::VERIFY_PHONE_CODE;
        $post = ['phone' => $phone, 'code' => $request->code];
        $result = admin_response_post($url, [], $post);
        if (!empty($result)) {
            $data = $result->data;
            $data = json_decode($data, true);
            if (is_array($data) && $data['code'] == 0) {
                if ($this->guard()->attempt($credentials)) {
                    return $this->sendLoginResponse($request);
                }
            }
            return back()->withInput()->withErrors(['verify_login' => 1]);
        } else {
            return back()->withInput()->withErrors(['verify_login' => 1]);
        }
    }

    /**
     * User logout.
     *
     * @param Request $request
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
            }
        );

        return $content
            ->header(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $form = new Form(new Administrator());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_base_path('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_base_path('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
