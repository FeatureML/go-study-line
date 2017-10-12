<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/10 16:43
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/

namespace app\common\model;

class Comment extends BaseModel
{
    protected $pk = "comment_id";
    protected $table = "resty_comment";

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //如果数据库没有创建create_time和更新时间create_time,则按照以下的做一个映射既可以
    protected $createTime = "create_time";

    //插入的时候自动完成
    protected $insert = [
        "create_time"
    ];

    public static function  test($data){
        halt($data);
    }

    /**
     * 添加评论
     * @param $data
     * @return array
     */
    public function store($data)
    {
        $result = $this->validate(true)->save($data);
        if (false === $result) {
            // 验证失败 输出错误信息
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "添加成功",'id'=>$this->comment_id];
    }

    /**
     * commentReply
     * @param $data
     */
    public function commentReply($data)
    {
        $result = $this->validate(true)->save($data);
        if (false === $result) {
            // 验证失败 输出错误信息
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "回复成功",'id'=>$this->comment_id];
    }

    /**
     * 更具评论获取回复
     * @param $allData
     * @param $id
     * @return array
     */
    public function getReplyByCommentId($allData, $id)
    {
        static $tmp = [];
        foreach ($allData as $k => $v) {
            if ($id == $v['parent_id']) {
                $tmp[] = $v['id'];
                $this->getReplyByCommentId($allData, $v['id']);
            }
        }
        return $tmp;
    }

}
