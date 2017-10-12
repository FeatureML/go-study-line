<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用行为扩展定义文件
return [
    // 模块初始化
    'module_init'  => [
        function($request){
            echo 'hello111,'.$request->module().'!<br/>';
        },
    ],
    // 操作开始执行
    'action_begin' => [
        function($request){
            echo 'hello111,'.$request->method().'!<br/>';
        },
    ],

];
