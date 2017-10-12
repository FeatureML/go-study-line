<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 16:22
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\index\controller;


class Error
{
    // 如果需要拦截所有方法，需要给空控制器定义一个空操作（_empty）方法
    public function _empty($method)
    {
        return '当前操作名：' . $method;
    }
}