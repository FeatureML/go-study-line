<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 15:46
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\socket\controller;


use think\swoole\Server;

class SwooleServer extends Server
{
    protected $host = '127.0.0.1';
    protected $port = 9502;
    protected $option = [
        'worker_num'	=> 4,
        'daemonize'	=> true,
        'backlog'	=> 128
    ];

    public function onReceive($server, $fd, $from_id, $data)
    {
        $server->send($fd, 'Swoole: '.$data);
    }
}