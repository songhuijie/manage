<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/1/12
 * Time: 21:36
 */
namespace App\Libraries;

use App\Model\Category;
use Illuminate\Support\Facades\Redis;

class Lib_make{


    /**
     * 获取所有分类
     */
    public static function getCategory($bool = true){

        $category_key = Lib_redis::PrefixStitching(Lib_redis::CATEGORY_KEY);

        if($bool == false){
            Redis::del($category_key);
            self::getCategory();
        }else{


            $json = Redis::get($category_key);


            if($json){
                return json_decode($json,true);
            }else{

                $category_model = new Category();

                $category_data = $category_model->all()->toArray();
                $data = array_column($category_data,'cate_name','id');


                Redis::set($category_key,json_encode($data));

                return $data;

            }
        }


    }
}