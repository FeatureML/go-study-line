<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/7/1
 * Time: 13:40
 */

namespace app\frontend\controller;


use think\Controller;
use think\Db;

class Cli extends Controller
{
    public function index(){
        echo  "Cli";
    }

    //这个方法将被cli模式调用
    public function cmdCliTest()
    {
        echo date("Y-m-d H:i:s",time()).' : ThinnPHP cli Mode Run Success:';
    }

    /**
     * 发送邮件
     */
    public function sendMail(){
        while (true){
            $res = Db::table('resty_task_list')->where('status',0)->select();
            foreach ($res as $k=>$v){
                $result = send_email($v['user_email'], '物联网智能数据 帐户激活邮件--', '我是贰萬先生');
                if ($result['error'] == 0){
                    Db::table('resty_task_list')->where('user_email',$v['user_email'])->setField('status',1);
                }
            }
            sleep(3);
        }
        echo 'finished';
    }


    public function sendMailQQ()
    {
//        $address = '756684177@qq.com';
//        $subject = '弍萬QQ邮箱发送';
//        $content = '恭喜你成功加入TINYWAN实验室，开启你的学习之旅吧！';
//        $result = send_email_qq($address, $subject, $content);
//        if ($result['error'] == 1) return json(['valid' => 0, 'msg' => "邮件发送失败，请联系管理员"]);
//        echo "Ok";
//        die;
        session('user_mobile_code456','user_mobile_code12312312312');
        halt(session('user_mobile_code456'));
    }
}