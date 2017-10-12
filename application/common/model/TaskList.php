<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/15 13:07
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\common\model;

class TaskList extends BaseModel
{
    protected $pk = "task_id";

    protected $table = "resty_task_list";

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //如果数据库没有创建create_time和更新时间create_time,则按照以下的做一个映射既可以
    protected $createTime = "create_time";

    //插入的时候自动完成
    protected $insert = [
        "create_time"
    ];

    /**
     * 添加任务
     * @param $data
     * @return array
     */
    public function addTaskList($data)
    {
        $result = $this->save($data);
        if (false === $result) {
            // 验证失败 输出错误信息
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "添加成功",'id'=>$this->task_id];
    }
}