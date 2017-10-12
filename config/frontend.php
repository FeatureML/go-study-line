<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/20 15:12
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
use think\Route;

//注册首页的路由
//Route::get('/','frontend/Index/index');
Route::get('home','frontend/Index/index');
Route::get('live/:id','frontend/Live/index');

// 注册MISS路由,表示当其他的非法GET请求访问的时候，系统会自动路由重定向到博客首页。
//Route::miss('/','GET');

/**
 * ------------------------------------------------------路由配置定义----------------------------------------------------
 */
return [
    "fd/:id" => "frontend/index/detail", // 文章详细页面：http://test.thinkphp5-line.com/d/23.html
    "ft/:id" => "frontend/index/searchByTagId", // 标签页面：http://test.thinkphp5-line.com/t/3.html
    "fc/:id" => "frontend/index/searchByCategoryId", // 根据分类Id查询文章：http://test.thinkphp5-line.com/c/3.html
];