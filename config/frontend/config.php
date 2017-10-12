<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/25
 * Time: 15:16
 */
return [
    'app_title'          => '快速智能平台',
    //    // 是否开启路由
    'url_route_on'           => true,
    // 是否强制使用路由
    'url_route_must'         => false,
    // 视图输出字符串内容替换
    'view_replace_str' => [
        "__STATIC__" => "/frontend",
        "__JS__" => "/frontend/js",
        "__CSS__" => "/frontend/css",
        "__IMAGES__" => "/frontend/images",
        "__COMMON__" => "/common",
    ],
    'template'               => [
        //全局配置方式开启
        'layout_on'     =>  true,
        'layout_name'   =>  'layout',
    ]
];