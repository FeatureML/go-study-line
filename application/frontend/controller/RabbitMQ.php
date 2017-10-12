<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/13 22:20
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/

namespace app\frontend\controller;


use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQ
{
    public function connection(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    }
}