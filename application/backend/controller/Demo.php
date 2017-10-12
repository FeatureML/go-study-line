<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 17:23
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\backend\controller;


use app\common\controller\Base;
use app\common\controller\BaseBackend;
use app\common\library\Auth;
use think\Route;

class Demo extends Base
{
    public function index()
    {
        return '当前用户角色：' . $this->role;
    }

    public function test()
    {
        $method = $this->request->method();
// 获取当前请求类型的路由规则
        return 'run index 11<br/>';
    }

    public function hello($name)
    {
        return 'Hello,' . $name . '!';
    }

    public function insert($id)
    {
        return 'insert,' . $id . '!!!';
    }

    public function getRole()
    {
        $auth = new Auth();
        halt($auth->getGroups('178'));
    }

    /**
     * AJAX请求伪装
     * https://www.tinywan.top/backend/demo/testAjax?_ajax=1
     */
    public function testAjax(){
        if(request()->isAjax()){
            halt('11111111');
        }else{
            halt('0000000000000');
        }
    }
}