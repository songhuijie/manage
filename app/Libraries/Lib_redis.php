<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/1/12
 * Time: 21:37
 */
namespace App\Libraries;

class Lib_redis{


    const CATEGORY_KEY = ':category:key';

    /**
     * 前缀拼接
     */
    public static function PrefixStitching($key){

        return env('REDIS_PREFIX','blog').$key;
    }
}