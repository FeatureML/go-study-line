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

use think\Db;
use think\Loader;
use think\Log;
use think\Queue;
use think\Validate;

class Admin extends BaseModel
{
    /**
     * 主键
     * @var string
     */
    protected $pk = "id";

    /**
     * 设置当前模型对应的完整数据表名称
     * @var string
     */
    protected $table = "resty_user";


    /**
     * 登录验证
     * @param $data
     * @return array
     */
    public function login($data)
    {
        // 1 验证数据
        $validate = Loader::validate('Admin');
        if (!$validate->check($data)) {
            // 返回给控制器
            return ['valid' => 0, 'msg' => $validate->getError()];
        }
        // 2 验证用户名的正确性 查找不到返回 null
        $infoUser = $this->where("email", $data['email'])->where("password", md5($data['password']))->find();
        if ($infoUser['deleted'] == 1) {
            return ['valid' => 0, 'msg' => "该用户已经被冻结"];
        }
        if (!$infoUser) {
            // 数据库中为匹配到
            return ['valid' => 0, 'msg' => "邮箱或者密码错误"];
        }
        if ($infoUser["enable"] == 0) return ['valid' => 0, 'msg' => "该账号还没有激活，请登录邮箱" . $infoUser["email"] . '立即激活'];
        // 3 记录session
        session('admin.admin_id', $infoUser['id']);
        session('admin.username', $infoUser['username']);
        // 4 每天登录增加经验值
        $today = strtotime(date('Y-m-d')); // 获取今天0时0分0秒的时间
        // 如果上次的登录时间小于今天的时间，则增加经验值
        if ($infoUser['logintime'] < $today) {
            $this->where('id', $infoUser['id'])->setInc('login_points', 10);
        }
        return ['valid' => 1, 'msg' => "登录成功"];
    }

    /**
     * 邮箱注册
     * @param $data
     * @return array
     */
    public function emailRegister($data)
    {
        // 1 验证数据
        $validate = new Validate([
            'email' => 'require|email',
            'password' => 'require',
            'repassword' => 'require|confirm:password'
        ], [
            'password.require' => "密码不能为空！",
            'repassword.require' => "两次密码输入不一致！",
            'repassword.confirm' => "两次密码输入不一致！"
        ]);
        if (!$validate->check($data)) {
            return ['valid' => 0, 'msg' => $validate->getError()];
        }
        // 2 检测邮箱是否被注册
        $time = time();
        $passwordToken = md5($data['email'] . md5($data['password']) . $time); //创建用于激活识别码
        $userInfo = Db::table('resty_user')->where("enable=:enable and email=:email")->bind(['enable' => 1, 'email' => $data['email']])->find();
        if ($userInfo) return ['valid' => 0, 'msg' => "该邮箱已经被注册"];
        // 考虑URL地址失效问题，重新发送邮件
        $userInfoEnable = Db::table('resty_user')->where("enable=:enable and email=:email")->bind(['enable' => 0, 'email' => $data['email']])->find();
        if ($userInfoEnable) {
            // 4 放入邮件队列
            $taskData['task_type'] = 2;
            $taskData['status'] = 0;
            $taskData['email_type'] = 1;
            $taskData['email_scene'] = 2;
            $taskData['user_email'] = $data['email'];
            Db::table('resty_task_list')->insert($taskData);
            return ['valid' => 1, 'msg' => "邮件重新发送成功，请立即验证邮箱:" . $data['email']];
        }
        // 3 插入数据库
        $res = $this->data([
            'username' => self::getRandUserName(),
            'password' => md5($data["password"]),
            'email' => $data["email"],
            'password_token' => $passwordToken,
            'loginip' => "127.0.0.1",
        ])->save();
        if (!$res) return ['valid' => 0, 'msg' => "数据库添加数据失败"];
        // 4 放入邮件队列
        $taskData['task_type'] = 2;
        $taskData['status'] = 0;
        $taskData['email_type'] = 1;
        $taskData['email_scene'] = 2;
        $taskData['user_email'] = $data['email'];
        Db::table('resty_task_list')->insert($taskData);
        return ['valid' => 1, 'msg' => $data['email'] . "注册成功，请立即验证邮箱<br/>邮件发送至: " . $data['email']];
    }

    /**
     * checkstr=NzU2Njg0MTc3QHFxLmNvbQ==&amp;auth_key=1499661824-0-0-0c3729355a38452f491584711b76e46d
     * 邮箱注册回调地址的有效性检测 emailRegisterUrlValid
     * @param $data
     * @return array
     */
    public function emailRegisterUrlValid($data)
    {
        $email = base64_decode($data['checkstr']);
        // 签名验证
        $res = check_auth_key($email, $data['auth_key']);
        // 1 邮箱过期时间
        if (!$res["valid"]) return ['valid' => 0, 'msg' => $res["msg"]];
        // 2 验证邮箱
        $userInfo = $this->where('email', $email)->where('enable', 0)->find();
        if (!$userInfo) return ['valid' => 0, 'msg' => "该账户已被激活或者该账户不存在"];
        // 3、修改数据库表：auth_group表和user表
        // 启动事务
        Db::startTrans();
        try {
            $this->save([
                'enable' => 1  # 表示已经激活
            ], [$this->pk => $userInfo['id']]);
            // 系统默认审核为最低权限
            $groupData['uid'] = $userInfo['id'];
            $groupData['group_id'] = 13;
            Db::table("resty_auth_group_access")->insert($groupData);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::error("auth_group表和user表修改异常：" . $e->getMessage());
            return ['valid' => 0, 'msg' => "邮件激活失败"];
        }
        // 4 记录session
        session('admin.admin_id', $userInfo['id']);
        session('admin.username', $userInfo['username']);
        return ['valid' => 1, 'msg' => "邮箱激活成功"];
    }

    /**
     * 修改密码
     * @param $data
     * @return array
     */
    public function changePassword($data)
    {
        // 1 验证数据
        $validate = new Validate([
            'password' => 'require',
            'new_password' => 'require',
            'repassword' => 'require|confirm:new_password'
        ], [
            'password.require' => "密码不能为空！",
            'new_password.require' => "新密码不能为空！",
            'repassword.require' => "两次密码输入不一致！",
            'repassword.confirm' => "两次密码输入不一致！"
        ]);
        if (!$validate->check($data)) {
            return ['valid' => 0, 'msg' => $validate->getError()];
        }
        // 2 原始密码是否正确
        $userInfo = $this->where('id', session("admin.admin_id"))->where('password', md5($data['password']))->find();
        if (!$userInfo) {
            // 数据库中为匹配到
            return ['valid' => 0, 'msg' => "原始密码错误"];
        }
        // 3 更新数据
        $res = $this->save([
            'password' => md5($data['new_password'])
        ], [$this->pk => session("admin.admin_id")]
        ); //更新主键
        if ($res) return ['valid' => 1, 'msg' => "密码修改成功"];
        return ['valid' => 0, 'msg' => "密码修改失败"];
    }

    /**
     * 检测邮箱是否注册
     * @param $data
     * @return array
     */
    public function checkSendEmail($data)
    {
        // 1 验证数据
        $validate = new Validate([
            'email' => 'require|email'
        ], [
            'email.require' => "发送邮箱不能为空",
            'email.email' => "邮箱格式不合适"
        ]);
        if (!$validate->check($data)) {
            return ['valid' => 0, 'msg' => "邮箱不能为空或者格式不合适"];
        }
        // 2 该邮箱是否注册
        $userInfo = $this->where('email', $data['email'])->find();
        if (!$userInfo) return ['valid' => 0, 'msg' => "该邮箱尚未注册"];
        // 3 修改密码发送时间
        $updateResult = $this->save([
            'password_time' => time()
        ], [$this->pk => $userInfo['id']]);
        // 4 放入邮件队列
        $taskData['task_type'] = 2; // 2：邮件
        $taskData['status'] = 0;
        $taskData['email_type'] = 2; // 2：修改密码
        $taskData['email_scene'] = 2; // 2：后台
        $taskData['user_email'] = $data['email'];
        Db::table('resty_task_list')->insert($taskData);
        if (!$updateResult) return ['valid' => 0, 'msg' => "修改数据库密码发送时间失败"];
        return ['valid' => 1, 'msg' => $data['email'] . "系统已向您的邮箱发送了一封邮件<br/>请登录到您的邮箱及时重置您的密码"];
    }

    /**
     * 邮箱回调地址的有效性检测
     * @param $data
     * @return array
     */
    public function checkEmailUrlValid($data)
    {
        // 1 检查url地址有效性
        $email = base64_decode($data['checkstr']);
        $res = check_auth_key($email, $data['auth_key']);
        if (!$res["valid"]) return ['valid' => 0, 'msg' => $res["msg"]];
        // 2 验证邮箱
        $userInfo = $userInfo = $this->where('email', $email)->find();
        if (!$userInfo) return ['valid' => 0, 'msg' => "该邮箱尚未注册"];
        // 3、验证通过，跳转到密码修改界面
        session('admin.admin_id', $userInfo['id']);
        session('admin.username', $userInfo['username']);
        return ['valid' => 1, 'email' => $email];
    }

    /**
     * 邮箱重设密码
     * @param $data
     * @return array
     */
    public function reSetPassword($data)
    {
        // 1 验证数据
        $validate = new Validate([
            'new_password' => 'require|min:6|max:64',
            'repassword' => 'require|confirm:new_password'
        ], [
            'new_password.require' => "密码不能为空！",
            'new_password.min' => "密码不能少于6位！",
            'new_password.max' => "密码不能大于6位！",
            'repassword.require' => "两次密码输入不一致！",
            'repassword.confirm' => "两次密码输入不一致！"
        ]);
        if (!$validate->check($data)) {
            return ['valid' => 0, 'msg' => $validate->getError()];
        }
        // 2 更新数据
        $userInfo = $this->where('email', $data['email'])->find();
        $res = $this->save([
            'password' => md5($data['new_password'])
        ], [$this->pk => $userInfo['id']]
        );
        if (!$res) return ['valid' => 0, 'msg' => "邮箱密码修改失败" . $userInfo['username']];
        // 3 密码修改成功直接跳转到主页面
        session('admin.admin_id', $userInfo['id']);
        session('admin.username', $userInfo['username']);
        return ['valid' => 1, 'msg' => "邮箱密码修改成功，正在跳转到主页面..."];
    }

    /**
     * oAuth 邮箱发送邮件
     * @param $data
     * @return array
     */
    public function oAuthSendEmail($data)
    {
        // 1 验证数据
        $validate = new Validate([
            'email' => 'require|email',
        ], [
            'email.require' => "邮箱是不能为空！",
        ]);
        if (!$validate->check($data)) {
            return ['valid' => 0, 'msg' => $validate->getError()];
        }
        // 2 检测邮箱是否被注册
        $userInfo = $userInfo = $this->where('email', $data['email'])->find();
        if ($userInfo) return ['valid' => 0, 'msg' => "该邮箱已经被注册"];
        // 4 发送邮件
        $emailSendDomain = config('email.EMAIL_SEND_DOMAIN');
        $checkstr = base64_encode($data['email']);
        $auth_key = get_auth_key($data['email']);
        $email_code = mt_rand(1111, 9999);
        $str = <<<html
            您好！你的验证码：<p></p>
            <h1>$email_code</h1><p></p>
html;
        //传递一个数组，可以实现多邮件发送,有人注册的时候给管理员也同时发送一份邮件
        $result = send_email($data["email"], '物联网智能数据 邮件验证码：', $str);
        if ($result['error'] == 1) return ['valid' => 0, 'msg' => "邮件发送失败，请联系管理员"];
        //存储方便验证
        session($data['email'] . ':email_code', $email_code);
        return ['valid' => 1, 'msg' => $data['email'] . "注册成功，请立即验证邮箱<br/>邮件发送至: " . $data['email']];
    }

    /**
     * 手机注册
     * @param $data
     * @return array
     */
    public function mobileRegister($data)
    {
        // 1 手机验证码验证
        Log::error("----------手机验证码验证--------" . json_encode($data));
        $serverCode = session("TINYWAN:" . $data["mobile"]);
        if ($serverCode != $data["code"]) return ['valid' => 0, 'msg' => "手机验证码错误"];
        // 2 验证数据
        $validate = new Validate([
            'mobile' => 'require',
            'password' => 'require'
        ], [
            'mobile.require' => "手机号码不能为空！",
            'password.require' => "密码不能为空！"
        ]);
        if (!$validate->check($data)) {
            return ['valid' => 0, 'msg' => $validate->getError()];
        }
        // 3 插入数据库
        $res = $this->data([
            'username' => self::getRandUserName(),
            'password' => md5($data["password"]),
            'mobile' => $data["mobile"],
            'loginip' => "127.0.0.1",
        ])->save();
        if (!$res) return ['valid' => 0, 'msg' => "数据库添加数据失败"];
        session('frontend.id', $this->pk);
        session('frontend.username', $data["mobile"]);
        return ['valid' => 1, 'msg' => "注册成功"];
    }

    public function del($id)
    {
        //1 获取当前删除数据id 的pid的值
        //2 将当前要删除的id的子集数据的pid 修改为删除数据自己的pid ,这样子就做到了往上提一级的概念
        $res = $this->where('id', $id)->update(['deleted' => 1]);
        if (false === $res) return ['valid' => 0, 'msg' => "删除失败"];
        return ['valid' => 1, 'msg' => "删除成功"];
    }

}
