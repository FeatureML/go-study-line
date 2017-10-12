<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/19 10:03
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
//use think\Hook;
//
//Hook::add('module_init',[
//    function($request){
//        echo 'welcome,'.$request->module().'!<br/>';
//    },
//    ['\app\common\behavior\Test','sayHello'],
//]);

use think\Hook;

Hook::add('controller_init',function($controller,$request){
    // 绑定请求对象的属性
    $request->bind('role',new \app\common\model\Role($request->param('id')));
},true);