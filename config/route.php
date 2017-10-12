<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/24
 * Time: 10:47
 */

/**
 * ----------------------------------------------------路由方法注册------------------------------------------------------
 */
use think\Route;

/**
 * 静态路由规则
 * 静态规则部分不区分大小写
 * 接受参数$name： https://www.tinywan.top/hello/name/Tinywan
 */
//Route::rule('hello','backend/Demo/hello');

/**
 * 动态路由规则
 * 接受参数$name： https://www.tinywan.top/hello/Tinywan
 */
//Route::rule('hello/:name','backend/Demo/hello');

/**
 * 限定请求类型为get
 * 以下两个的定义是一样的
 * 接受参数$name： https://www.tinywan.top/hello/Tinywan
 *
 */
//Route::rule('hello/:name','backend/demo/hello','GET');
//Route::get('hello/:name','backend/demo/hello');

/**
 * 支持所有的请求类型
 */
//Route::any('hello/:name','backend/demo/hello');

/**
 * 包含多个匹配条件
 * 错误的URL地址：https://www.tinywan.top/hello/Tinywan
 * 正确的URL地址：https://www.tinywan.top/hello/Tinywan.html
 */
//Route::any('hello/:name','backend/demo/hello',[
//    'ext'    => 'html',
//    'method' => 'get'
//]);

/**
 * 路由变量设置规则，这个时候就需要用到第四个参数
 * 变量name的变量规则（正则表达式）
 * 正确的URL地址：https://www.tinywan.top/hello/tinywan123.html
 * 错误的URL地址：https://www.tinywan.top/hello/Tinywan
 *              https://www.tinywan.top/hello/tinywan&!123.html
 */
Route::any('hello/:name','backend/demo/hello',[
    'ext'    => 'html',
    'method' => 'get'
],['name' => '[A-Za-z0-9]+']);


/**
 * ------------------------------------------------------路由配置定义----------------------------------------------------
 */
return [
    "bnews/:id" => "backend/index/info", // http://127.0.0.1/news/234
    "d/:id" => "frontend/index/detail", // 文章详细页面：http://test.thinkphp5-line.com/d/23.html
    "t/:id" => "frontend/index/searchByTagId", // 标签页面：http://test.thinkphp5-line.com/t/3.html
    "c/:id" => "frontend/index/searchByCategoryId", // 根据分类Id查询文章：http://test.thinkphp5-line.com/c/3.html
    // 后台路由配置
//    "tinywan" => "backend/login/login",
    // 路由别名
    '__alias__' => [
        'user' => 'index/User',
        'system' => 'backend/system',
        'category' => 'backend/category',
        'tag' => 'backend/tag',
        'article' => 'backend/article',
    ],
];