<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/25
 * Time: 15:16
 */
return [
    'app_title'          => '个人博客',
    'view_replace_str' => [
        "__STATIC__" => "/blog",
        "__JS__" => "/blog/js",
        "__CSS__" => "/blog/css",
        "__IMAGES__" => "/blog/images",
        "__COMMON__" => "/common"
    ],
    'template'               => [
        //全局配置方式开启
        'layout_on'     =>  true,
        'layout_name'   =>  'layout',
    ]

];