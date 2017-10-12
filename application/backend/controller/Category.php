<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  Date: 2017/1/20 16:25
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
namespace app\backend\controller;

use app\common\controller\BaseBackend;

class Category extends BaseBackend
{
    protected $db;

    public function _initialize()
    {
        parent::_initialize();
        $this->db = new \app\common\model\Category();  //对象存储在一个db属性中
    }

    public function index()
    {
        $res = $this->db->getAll();
        $this->assign('sub_title',"栏目列表");
        return $this->fetch('index', [
            'categorys' => $res
        ]);
    }

    /**
     * 添加顶级栏目
     */
    public function store()
    {
        if (request()->isPost()) {
            $res = $this->db->store(input('post.'));
            if ($res["valid"]) {
                $this->success($res["msg"], "backend/category/index");
                exit;
            } else {
                $this->error($res["msg"]);
                exit;
            }
        }
    }

    /**
     * 添加子集栏目
     */
    public function subPage()
    {
        if (request()->isPost()) {
            $res = $this->db->store(input('post.'));
            if ($res["valid"]) {
                $this->success($res["msg"], "backend/category/index");
                exit;
            } else {
                $this->error($res["msg"]);
                exit;
            }
        }
        $id = input('param.id');
        $sub = $this->db->where('id', $id)->find();

        $res = $this->db->getAll();
        $this->assign('categorys', $res);
        $this->assign('sub', $sub);
        return $this->fetch();
    }

    /**
     * 分类编辑
     * @return mixed
     */
    public function edit()
    {
        $this->assign('sub_title',"栏目编辑");
        if(request()->isPost()){
            $res = $this->db->edit(input('post.'));
            if ($res["valid"]) {
                $this->success($res["msg"], "backend/category/index");
                exit;
            } else {
                $this->error($res["msg"]);
                exit;
            }
        }
        $id = input('param.id');
        $oldData = $this->db->find($id);
        $lastData = $this->db->getEditCategory($id);
        $this->assign('old_data',$oldData);
        $this->assign('last_data',$lastData);
        return $this->fetch();
    }

    /**
     * 删除栏目
     */
    public function del(){
        $id = input('param.id');
        $res = $this->db->del($id);
        if ($res["valid"]) {
            $desc = '删除分类id : ' . $id . '成功';
            add_operation_log($desc);
            $this->success($res["msg"], "backend/category/index");
            exit;
        } else {
            $desc = '删除分类id : ' . $id . '失败';
            add_operation_log($desc);
            $this->error($res["msg"]);
            exit;
        }
    }
}
