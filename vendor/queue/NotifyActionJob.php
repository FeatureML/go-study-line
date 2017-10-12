<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/14 10:14
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace queue;

class NotifyActionJob
{
    // key
    protected $key = '111';
    // secret
    protected $secret = '111111111111';

    public function setUp()
    {
        // ... Set up environment for this job
    }

    // 每一个job类都需要实现这个操作
    public function perform(){
        $push_uid = $this->args['push_uid'];
        $push_msg = $this->args['push_msg'];
        fwrite(STDOUT,'NotifyActionJob push_uid :'.$push_uid.' push_msg :',$push_msg);

        if($push_uid && $push_msg){
            fwrite(STDOUT,'NotifyActionJob start ...'.$push_uid.' push_msg :',$push_msg);
            // 延迟1s输出
            sleep(1);
            fwrite(STDOUT,'NotifyActionJob end');

        }
    }
}