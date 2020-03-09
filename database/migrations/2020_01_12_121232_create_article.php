<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cate_id')->comment('类别ID');
            $table->string('title')->comment('文章标题');
            $table->string('cover')->comment('文章封面');
            $table->string('author')->comment('文章作者');
            $table->text('content')->comment('文章内容');
            $table->integer('browse')->default(0)->comment('浏览数');
            $table->integer('comment')->default(0)->comment('评论数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aritcle');
    }
}
