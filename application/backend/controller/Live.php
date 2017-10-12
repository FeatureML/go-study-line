<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/28 15:44
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\backend\controller;


use app\common\controller\BaseBackend;
use think\Db;

class Live extends BaseBackend
{
    protected $db;

    public function _initialize()
    {
        parent::_initialize();
        $this->db = new \app\common\model\Live();
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 活动管理
     * @return mixed
     */
    public function liveIndex()
    {
        $lives = Db::table('resty_live')->select();
        $this->assign('categorys', $lives);
        return $this->fetch();
    }

    /**
     * 保存
     */
    public function liveStore()
    {
        if (request()->isPost()) {
            $res = $this->db->store(input('post.'));
            if ($res["valid"]) {
                $this->success($res["msg"], "backend/live/liveIndex");
                exit;
            } else {
                $this->error($res["msg"]);
                exit;
            }
        }
    }

    /**
     * 预览观看
     */
    public function liveView()
    {
        $id = input('param.id');
        $this->assign('live', Db::table('resty_live')->where('id', $id)->find());
        return $this->fetch();
    }

    /**
     * 录像列表
     * @return mixed
     */
    public function indexRecord()
    {
        if (request()->isPost()) {
            $liveStartTime = input('post.liveStartTime');
            halt($liveStartTime);
        }
        $videos = Db::table('resty_stream_video')->order('createTime desc')->paginate(12);
        $this->assign('videos', $videos);
        return $this->fetch();
    }

    /**
     * 录像操作
     */
    public function recordHandle()
    {
        if ($this->request->isAjax()) {
            $data = input('post.');
            $res = $this->db->recordHandle($data);
            if ($res['valid']) {
                return json(['code' => 200, 'msg' => $res["msg"]]);
            }
            return json(['code' => 500, 'msg' => $res["msg"]]);
        }
        return json(['code' => 401, 'msg' => "Not Forbidden"]);
    }
}