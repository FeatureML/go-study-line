<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/19 9:37
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\common\behavior;


class Test
{
    public function appInit()
    {
        echo 'app_init test 这里可以项目自动安装的处理<br/>';
    }

    public function appBegin()
    {
        echo 'app_begin test<br/>';
    }

    public function sayHello($request)
    {
        echo 'say hello ' . $request->module() . '!<br/>';
    }
}