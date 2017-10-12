<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/3 9:43
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/
namespace Openresty;

class LuaRedis
{
    public static function getView()
    {
        echo "NameSpace = ".__NAMESPACE__."<br/> ClassName = ".__CLASS__."<br/> Method = ".__METHOD__;
    }
}