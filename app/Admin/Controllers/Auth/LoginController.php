<?php
/**
 * Created by PhpStorm.
 * User: shj
 * Date: 2019/9/4
 * Time: 下午5:52
 */

namespace App\Admin\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    const SEND_MANAGE_PHONE_CODE = 'mine:manage:admin:phone:code:';
    const SEND_MANAGE_EMAIL_CODE = 'mine:manage:admin:email:code:';
    const MOBILE_SEND_A_MINUTE_AGO = 'mine:manage:admin:code:ago:';
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    //use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function email(Request $request)
    {
        $user_id = $request->id;
        $user = Administrator::where('id', $user_id)->first();
        $email = $user->email;
        return view('auth.email', ['email' => $email]);
    }

    public function sendEmailCode(Request $request)
    {

    }


    protected function guard()
    {
        return Auth::guard('admin');
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
}
