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

use houdunwang\arr\Arr;

class Category extends BaseModel
{
    protected $pk = "id";
    protected $table = "resty_category"; //完整的表名

    /**
     * 需要改进
     * @return mixed
     */
    public function getAll()
    {
        return Arr::tree(db('category')->order('id desc')->select(), 'name', $fieldPri = 'id', $fieldPid = 'pid');
    }

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
     * 编辑分类
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
     * 获取分类编辑name
     */
    public function getEditCategory($id)
    {
        // 1 首先查找到 $id 的子集
        $subIds = $this->getSubCategory(db('category')->select(), $id);
        // 2 将当前的分类id追加进去
        $subIds[] = $id;
        // 3 找到除了自己和自己子集的所有数据,构成梳妆
        $res = db('category')->whereNotIn('id', $subIds)->select();
        return Arr::tree($res, 'name', $fieldPri = 'id', $fieldPid = 'pid');

    }

    /**
     * 获取二级分类
     * @param $allData
     * @param $id
     * @return array
     */
    public function getSubCategory($allData, $id)
    {
        static $tmp = [];
        foreach ($allData as $k => $v) {
            if ($id == $v['pid']) {
                $tmp[] = $v['id'];
                $this->getSubCategory($allData, $v['id']);
            }
        }
        return $tmp;
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function del($id)
    {
        //1 获取当前删除数据id 的pid的值
        $pid = $this->where('id', $id)->value('pid');
        //2 将当前要删除的id的子集数据的pid 修改为删除数据自己的pid ,这样子就做到了往上提一级的概念
        $this->where('pid', $id)->update(['pid' => $pid]);
        //3 执行当前数据的删除
        $res = Category::destroy($id);
        if (false === $res) return ['valid' => 0, 'msg' => "删除失败"];
        return ['valid' => 1, 'msg' => "删除成功"];
    }
}
