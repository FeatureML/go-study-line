<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 16:28
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\index\controller;


use think\Request;

class TraitsTest
{
    use \traits\controller\Jump;

    protected $request;

    // 架构函数注入
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * https://www.tinywan.top/index/TraitsTest/hello/name/TinywanHello
     * @return string
     */
    public function hello()
    {
        return 'Hello,' . $this->request->param('name') . '！';
    }
}