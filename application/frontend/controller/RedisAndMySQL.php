<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/18 9:01
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\frontend\controller;


use redis\BaseRedis;

class RedisAndMySQL
{
    /**
     * 消息Redis数据保存到Mysql数据库
     * @param string $liveKey
     */
    public function redisSaveMysqlAction()
    {
        $liveKey = $this->request->getQuery('liveKey');
        if (empty($liveKey)) {
            $result = array("errcode" => 500, "errmsg" => "this parameter is empty!");
            return $this->toJson($result);
        }
        $redis = BaseRedis::LocationInstance();
        $redisInfo = $redis->lRange($liveKey, 0, 9);
        $dataLength = $redis->lLen($liveKey);
        while ($dataLength > 20) {
            try {
                // 开启事务
                $this->db->begin();
                $sql = "INSERT INTO livecomment (liveId,username,createTime,userId,content) VALUES";
                foreach ($redisInfo as $action) {
                    $sql .= "('" . json_decode($action, true)['roomId'] . "',
                    '" . json_decode($action, true)['userName'] . "',
                    '" . json_decode($action, true)['createTime'] . "',
                    '" . json_decode($action, true)['userId'] . "',
                    '" . json_decode($action, true)['content'] . "'),";
                }
                $sql = rtrim($sql, ',');
                $this->db->execute($sql);
                $redis->set('message_insert_success', '1');
                $redis->lTrim($liveKey, 10, -1);
                $redisInfo = $redis->lRange($liveKey, 0, 9);
                $dataLength = $redis->lLen($liveKey);
                $redis->set('dataLength_backenk', $dataLength);
                $this->db->commit();
            } catch (\Exception $e) {
                $redis->set('message_insert_fail', '0');
                $this->db->rollback();
            }
        }
        $redis->set('log' . $liveKey, $redis->incr('request_counts'));
        $result = array("errcode" => 200, "errmsg" => "Data Insert into Success!", 'data' => 'dataLength:' . $dataLength . 'liveKey:' . $liveKey);
        return $this->toJson($result);
    }
}