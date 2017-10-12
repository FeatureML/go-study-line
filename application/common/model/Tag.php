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

class Tag extends BaseModel
{
    protected $pk = "id";
    protected $table = "resty_tag"; //完整的表名

    /**
     * 添加分类
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
        return ['valid' => 1, 'msg' => "添加成功"];
    }

    /**
     * 编辑标签
     * @param $data
     * @return array
     */
    public function edit($data)
    {
        // id 已经存在的，只能更新
        $result = $this->validate(true)->save($data, [$this->pk => $data['id']]);
        if (false === $result) {
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "编辑成功"];
    }

    /**
     * 删除标签？？？
     * @param $id
     * @return array
     */
    public function del($id)
    {
        $res = $this->where('id', $id)->update(['recordStatus' => 1]);
        if (false === $res) return ['valid' => 0, 'msg' => "删除失败"];
        return ['valid' => 1, 'msg' => "删除成功"];
    }
}
