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

class AuthRule extends BaseModel
{
    protected $pk = "id";
    protected $table = "resty_auth_rule"; //完整的表名

    /**
     * 需要改进
     * @return mixed
     */
    public function getAll()
    {
        return Arr::tree(db('auth_rule')->order('id desc')->select(), 'title', $fieldPri = 'id', $fieldPid = 'pid');
    }

    /**
     * 保存数据
     * @param $data
     * @return array
     */
    public function store($data)
    {
        $result = $this->validate(false)->save($data);
        if (false === $result) {
            // 验证失败 输出错误信息
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "添加成功"];
    }

    /**
     * 编辑Rule
     */
    public function getEditRule($id)
    {
        // 1 首先查找到 $id 的子集
        $subIds = $this->getSubRule(db('auth_rule')->select(), $id);
        // 2 将当前的分类id追加进去
        $subIds[] = $id;
        // 3 找到除了自己和自己子集的所有数据,构成树结构
        $res = db('auth_rule')->whereNotIn('id', $subIds)->select();
        return Arr::tree($res, 'title', $fieldPri = 'id', $fieldPid = 'pid');

    }

    public function getSubRule($allData, $id)
    {
        static $tmp = [];
        foreach ($allData as $k => $v) {
            if ($id == $v['pid']) {
                $tmp[] = $v['id'];
                $this->getSubRule($allData, $v['id']);
            }
        }
        return $tmp;
    }

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
     * 删除
     * @param $id
     * @return array
     */
    public function del($id)
    {
        //1 获取当前删除数据id 的pid的值
        $delArr = $this->getChildrenId($id);
        //2 删除一个数据
        $res = AuthRule::destroy($delArr);
        if (false === $res) return ['valid' => 0, 'msg' => "删除失败"];
        return ['valid' => 1, 'msg' => "删除成功"];
    }

    /**
 * 获取子权限
 * @param $id
 * @return mixed
 */
    public function getChildrenId($id)
    {
        // 1 首先查找到 $id 的子集
        $subIds = $this->_getChildrenId(db('auth_rule')->select(), $id);
        // 2 将当前的分类id追加进去
        $subIds[] = $id;
        return $subIds;
    }

    public function _getChildrenId($allData, $id)
    {
        static $tmp = [];
        foreach ($allData as $k => $v) {
            if ($id == $v['pid']) {
                $tmp[] = $v['id'];
                $this->_getChildrenId($allData, $v['id']);
            }
        }
        return $tmp;
    }

    /**
     * 获取父权限
     * @param $id
     * @return mixed
     */
    public function getParentId($authRuleId)
    {
        // 1 首先查找到 $id 的子集
        $subIds = $this->_getgetParentId(db('auth_rule')->select(), $authRuleId);
        // 2 将当前的分类id追加进去
        $subIds[] = $authRuleId;
        return $subIds;
    }

    public function _getgetParentId($allData, $authRuleId)
    {
        static $tmp = [];
        foreach ($allData as $k => $v) {
            if ($authRuleId == $v['pid']) {
                $tmp[] = $v['id'];
                $this->_getChildrenId($allData, $v['pid']);
            }
        }
        return $tmp;
    }
}
