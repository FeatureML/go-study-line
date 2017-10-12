<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/19 9:02
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\backend\controller;


use redis\BaseRedis;

class RedisToMySQL
{
    /**
     * 读取Redis数据批量插入MySQL数据库
     * 使用PDO绑定参数去指定插入
     */
    public function commentRedisToMysqlPdoAction()
    {
        $this->view->disable();
        $redis = BaseRedis::LocationInstance();
        $liveKey = $this->request->getQuery('live_key');
        if (empty($liveKey)) {
            $result = ["status_code" => 500, "msg" => "The request parameter is empty"];
            return $this->toJson($result);
        }
        if ($redis->lLen($liveKey) != 0) {
            $commInfo = $redis->lRange($liveKey, 0, -1);
            try {
                $this->db->begin();
                $sql = "INSERT INTO livecomment (liveId,username,createTime,userId,content,isPresented,presentedId,isRedPacket,redPacketId,subtype)
                        VALUES (:liveId,:username,:createTime,:userId,:content,:isPresented,:presentedId,:isRedPacket,:redPacketId,:contentType)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':liveId', $liveId);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':createTime', $createTime);
                $stmt->bindParam(':userId', $userId);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':isPresented', $isPresented);
                $stmt->bindParam(':presentedId', $presentedId);
                $stmt->bindParam(':isRedPacket', $isRedPacket);
                $stmt->bindParam(':redPacketId', $redPacketId);
                $stmt->bindParam(':contentType', $contentType);
                foreach ($commInfo as $action) {
                    $commentArr = json_decode($action, true);
                    $liveId = $commentArr['roomId'];
                    $username = $commentArr['userName'];
                    $createTime = $commentArr['createTime'];
                    $userId = $commentArr['userId'];
                    $content = $commentArr['content'];
                    $isPresented = $commentArr['isPresented'];
                    $presentedId = $commentArr['presentId'] ? $commentArr['presentId'] : 0;
                    $isRedPacket = isset($commentArr['isRedPacket']) ? $commentArr['isRedPacket'] : 0;
                    $redPacketId = isset($commentArr['redPacketId']) ? $commentArr['redPacketId'] : 0;
                    $contentType = isset($commentArr['subtype']) ? $commentArr['subtype'] : "text";
                    $stmt->execute();
                }
                $insertResult = $this->db->lastInsertId(); //success return 1
                $this->db->commit();
                $redis->del($liveKey);
            } catch (\Exception $e) {
                $this->db->rollback();
                $result = ["status_code" => 501, "msg" => $liveKey . " PDO:mysql error"];
                return $this->toJson($result);
            }
            $result = ["status_code" => 200, "msg" => $liveKey . " PDO:insert success", 'result:' . $insertResult];
        } else {
            $redis->del($liveKey);
            $result = ["status_code" => 502, "msg" => $liveKey . " PDO:insert fail current list length = " . $redis->lLen($liveKey)];
        }
        return $this->toJson($result);
    }
}