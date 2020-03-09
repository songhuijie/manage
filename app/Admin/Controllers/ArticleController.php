<?php

namespace App\Admin\Controllers;

use App\Libraries\Lib_make;
use App\Model\Article;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ArticleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '文章列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Article);

        $grid->column('id', __('Id'));
        $grid->column('cate_id', __('Cate id'));
        $grid->column('title', __('Title'));
        $grid->column('cover', __('Cover'));
        $grid->column('author', __('Author'));
        $grid->column('content', __('Content'));
        $grid->column('browse', __('Browse'));
        $grid->column('comment', __('Comment'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Article::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('cate_id', __('Cate id'));
        $show->field('title', __('Title'));
        $show->field('cover', __('Cover'));
        $show->field('author', __('Author'));
        $show->field('content', __('Content'));
        $show->field('browse', __('Browse'));
        $show->field('comment', __('Comment'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Article);


        $category = Lib_make::getCategory();
        $form->select('cate_id','类型' )->options($category);
        $form->text('title', __('Title'));
        $form->image('cover', __('Cover'));
        $form->text('author', __('Author'));
//        $form->textarea('content', __('Content'));
        $form->ueditor('content', '内容')->rules('required');;
        $form->number('browse', __('Browse'));
        $form->number('comment', __('Comment'));

        return $form;
    }
}
