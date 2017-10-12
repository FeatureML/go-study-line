<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 17:29
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
use think\Hook;
use app\common\model\Role;

Hook::add('controller_init', function ($controller, $request) {
    // 绑定请求对象的属性
    $request->bind('role', new Role($request->param('id')));
}, true);