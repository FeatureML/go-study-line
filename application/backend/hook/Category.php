<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/19 10:31
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
namespace app\backend\hook;

class Category
{
    public function index(){
        echo "我是Category类型钩子中的index方法<br>";
    }

    public function index_5(){
        echo "我是Category类型钩子中的index方法，我的权重比较低<br>";
    }
}