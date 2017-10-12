<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/20 15:13
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

/**
 * ----------------------------------------------------路由方法注册------------------------------------------------------
 */
use think\Route;

// 后台登陆,这里不可以定义为get,表单提交是post格式
Route::rule('console','backend/Login/login');

/**
 * ------------------------路由分组，使用闭包方式定义
 */

/**
 * URL：https://www.tinywan.top/demo/tinywan
 */
Route::group('demo',function (){
    Route::get(':id','backend/Demo/insert');
    Route::rule(':name','backend/Demo/hello');
    Route::get('test','backend/Demo/test');
});


/**
 * -------------------------别名路由注册
 */

/**
 * category 别名路由到 backend/Category 控制器
 * URL：https://www.tinywan.top/category/index.html
 * URL：https://www.tinywan.top/category/edit/id/99.html
 */
Route::alias('category','backend/Category');

/**
 * 别名路由参数
 * URL：https://www.tinywan.top/category/index.html
 */
Route::alias('category','backend/Category',['ext'=>'html']);
// 单独注册路由


/**
 * ------------------------------------------------------路由配置定义----------------------------------------------------
 */
return [
    // 后台路由配置
//    "console" => "backend/login/login",
];