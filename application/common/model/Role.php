<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 17:31
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
namespace app\common\model;

class Role
{
    public $name;
    protected $type = [
        1 => 'admin',
        2 => 'leader',
        3 => 'operator',
    ];

    public function __construct($id)
    {
        if (isset($this->type[$id])) {
            $this->name = $this->type[$id];
        } else {
            $this->name = 'guest';
        }
    }
}