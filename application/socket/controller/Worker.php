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

use think\Log;
use think\worker\Server;
use Workerman\Lib\Timer;
use GatewayWorker\Lib\Gateway;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        // 给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
//        $connection->lastMessageTime = time();
//        Gateway::sendToAll($data);
        while (true) {
            $rand = rand(00000000, 99999999);
            $sendData = json_encode(['data' => $rand, 'errcode' => 0, 'errmsg' => $data]);
//            $connection->send($sendData);
            $this->broadcast($connection,$sendData);
            Log::info("发送的信息为" . $rand);
            sleep(2);
        }
//        if (!isset($connection->name)) {
//            $data = json_decode($data, true);
//            if (!isset($data['name'])) {
//                return $connection->close("auth fail and close 1");
//            }
//            $connection->name = $data['name'];
//            return $this->broadcast($connection->name . "Login");
//        }

//        while (true) {
//            $rand = rand(00000000, 99999999);
//            $this->broadcast($connection->name . "said : ".$sendData);
//            foreach ($this->worker->connections as $connection) {
//                $sendData = json_encode(['data' => $connection->id, 'errcode' => 0, 'errmsg' => 'success ']);
//                $connection->send($sendData);
//            }
//            $connection->send($sendData);
//            sleep(2);
//        }
    }

    /**
     * 发送消息 $connections
     * @param $msg
     */
    public function broadcast($connection,$msg)
    {
        $connection->send($msg);
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        // 向当前client_id发送数据
//        echo "当前连接的IP地址：" . $connection->getRemoteIP();
        // 添加一个定时器
        Timer::add(10, function ($worker) use ($connection) {
            if (!isset($connection->name)) {
                return $connection->close("auth fail and close 2");
            }
        }, null, false);
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        echo $connection->id . " : disconnect \r\n";
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

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        // 进程启动后设置一个每秒运行一次的定时器
        Timer::add(1, function () use ($worker) {
            $time_now = time();
            foreach ($worker->connections as $connection) {
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                if (empty($connection->lastMessageTime)) {
                    $connection->lastMessageTime = $time_now;
                    continue;
                }
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $connection->lastMessageTime > 10) {
                    $connection->close();
                }
            }
        });
        echo $worker->id . "\r\n";
    }

}
