<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan
 * |  Date: 2017/2/20
 * |  Time: 8:36
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm. http://115.29.8.55
 * '-------------------------------------------------------------------*/

namespace app\socket\controller;

use think\worker\Server;

class PushWorker extends Server
{
    protected $socket = 'websocket://0.0.0.0:12345';

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        echo "connection success\n";
    }

    /**
     * 接受发送消息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        $connection->send("Hello World");
    }


    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        echo "connection close \n";
    }


    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }


}