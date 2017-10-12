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

use think\Db;
use think\worker\Server;
use Workerman\Worker;

class Push extends Server
{
    protected $socket = 'websocket://0.0.0.0:11001';

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        // 开启内部推送
        $push = new Worker('text://0.0.0.0:11002');
        $push->name = 'push_inner';
        $push->user = 'www';
        $push->onMessage = function ($conn, $data) use ($worker) {
            // JSON 数据解析
            $data = json_decode($data, true);
            // 用户连接是否存在
            if (isset($worker->uid[$data['uid']])) {
                // 消息系统总数加1
                $worker->uid[$data['uid']]['data'][$data['type']] += 1;
                // 已存储用户数据
                $data['_data'] = $worker->uid[$data['uid']]['data'];
                // 向客户推送信息
                $res = $worker->uid[$data['uid']]['conn']->send(json_encode($data));
            } else {
                $res = null;
            }
            // 向外部清酒返回推送结果
            $conn->send($res ? "ok" : "fail");
        };
        $push->listen();
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($conn, $data)
    {
        if (isset($conn->uid)) {
            $temp = json_decode($data, true);
            $data = is_array($temp) ? $temp['data'] : $temp;
            $conn->uid = $data;
            try {
                //查询未读取信息
                $res = Db::table('dfds');
            } catch (\Exception $e) {
                $res = [];
            }
            //存储用户信息
            $this->worker->uid[$conn->uid] = [
                'data' => $res,
                'conn' => &$conn
            ];
        }
        // 向客户推送数据
        $conn->send(json_encode($this->worker->uid[$conn->uid]['data']));
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($conn)
    {

    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($conn)
    {
        if (isset($conn->uid)) {
            // 连接断开时删除映射
            unset($this->worker->uid[$conn->uid]);
        }
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