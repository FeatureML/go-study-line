<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/25 20:53
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/

namespace app\api\controller;

use app\common\controller\Base;
use think\Db;

class OpenApi extends Base
{
    public function index(){
        return '11'.__FUNCTION__;
    }

    /**
     * 录像信息添加
     */
    public function createStreamVideo(){
        $version = input("get.version");
        $streamName = input("get.streamName");
        $channelId = input("get.channelId");
        $baseName = input("get.baseName");
        $duration = input("get.duration");
        $fileSize = input("get.fileSize");
        $fileTime = input("get.fileTime");

        $videoData = [
            'streamName' => $streamName,
            'channelId' => $channelId,
            'name' => $baseName,
            'fileName' => $baseName,
            'fileTime' => strftime("%Y-%m-%d %X", $fileTime),
            'fileSize' => $fileSize,
            'duration' => $duration,
            'createTime' => date("Y-m-d H:i:s"),
        ];
        $res = Db::table('resty_stream_video')->insertGetId($videoData);
        if($res){
            // 加入消息队列
            $taskData['task_type'] = 1;
            $taskData['status'] = 0;
            $taskData['mobile_type'] = 2;
            $taskData['user_mobile'] = 18170603953;
            $taskData['msg'] = "909090";
            $taskData['live_id'] = $streamName;
            // 加入邮件队列
            $this->addTaskList($taskData);
            exit('200:success');
        }else{
            exit('500:error');
        }
    }

    public function testSendMail()
    {
        $res = Db::table('resty_task_list')->where('status', 0)->find();
            $emailSendDomain = config('email.EMAIL_SEND_DOMAIN');
            $result = send_email_qq('756684177@qq.com', '新用户注册', '11111111');
        return $result;
    }

    public function testSendMail2()
    {
        $email_smtp_host = config('email.EMAIL_SMTP_HOST');
        $email_username = config('email.EMAIL_USERNAME');
        $email_password = config('email.EMAIL_PASSWORD');
        $email_from_name = config('email.EMAIL_FROM_NAME');
        $email_host = config('email.EMAIL_SEND_DOMAIN');
        $address= '756684177@qq.com';
        $subject = '弍萬邮件标题';
        $content = '测试邮件发送';
        try{
            //实例化PHPMailer核心类
            $phpmailer = new \PHPMailer();

            //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
            $phpmailer->SMTPDebug = 1;

            //使用smtp鉴权方式发送邮件
            $phpmailer->IsSMTP();

            //smtp需要鉴权 这个必须是true
            $phpmailer->SMTPAuth = true;

            // 设置SMTP服务器。
            $phpmailer->Host = 'smtp.qq.com';

            //设置使用ssl加密方式登录鉴权
            $phpmailer->SMTPSecure = 'ssl';

            //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
            $phpmailer->Port = 465;

            //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
//            $phpmailer->Hostname = $email_host;

            // 	设置邮件的字符编码'
            $phpmailer->CharSet = 'UTF-8';

            // 设置发件人名字
            $phpmailer->FromName = '弍萬罚金人';

            //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
            $phpmailer->Username = '1722318623@qq.com';

            //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
            $phpmailer->Password = 'nsluzzfumknicgfa';

            //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
            $phpmailer->From = $email_username;

            //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
            $phpmailer->IsHTML(true);

            //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
            $phpmailer->addAddress($address,'lsgo在线通知');
            // 设置邮件标题
            $phpmailer->Subject = $subject;
            // 设置邮件正文,这里最好修改为这个，不是boby
            $phpmailer->MsgHTML($content);
            $res = $phpmailer->Send();
            echo '1111111111111';
            var_dump($res);
        }catch (\phpmailerException $e){
            return "邮件发送失败：".$e->errorMessage();
        }
    }
}