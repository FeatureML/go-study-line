<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/27
 * Time: 22:01
 * 参考文章：https://github.com/overtrue/easy-sms
 */

namespace app\frontend\controller;

use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Strategies\OrderStrategy;
use think\Controller;

class Sms extends Controller
{
    /**
     * [0] 单条短信测试
     * URL：http://test.thinkphp5-line.com/frontend/sms/init
     */
    public function init()
    {
        $ac = "1001@";
        $authkey = "2312";
        $cgid = 6;
        $mobile_code1 = rand(100000, 999999);
        $mobile_code2 = rand(100000, 999999);
        $mobile_code3 = rand(100000, 999999);
        $mobile_code4 = rand(100000, 999999);
//       $content= urlencode("亲爱的用户，您的验证码是：".rand(100000, 999999)."。 有效期为10分钟，请尽快验证，如非本人操作，请忽略本短信。");
        $str = "各位家人：
        今晚（2017年8月3日20:00）召开在线视频培训会议，每位市代配4个分会场的份额，请各个市代合理分配分会场的名额，在确保分会场网络环境通畅的情况下通过以下方式进入视频在线视频会议直播系统：
        1、关注微信公众号：三三玉茶坊资讯；
        2、点击右下角菜单选择“视频会议”；
        3、选择3号直播室，输入邀请码(".$mobile_code1."，".$mobile_code2."，".$mobile_code3."，".$mobile_code4.") 即可参加视频会议；
        4、会议支持电话：400-019-1331转1
        请各位市代严格按配额数量组织分会场及参会人员参会！每个验证码仅支持1个分会场使用！
        三三信息中心";
        $content= urlencode($str);
        $mobile = '13669361192';
        $url = "http://127.0.0.1/OpenPlatform/OpenApi?action=sendOnce&ac=$ac&authkey=$authkey&cgid=$cgid&c=$content&m=$mobile";
        $xml = get_uri($url);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        /**
         * 发送成功
         */
        $resArr = [];
        if($result["@attributes"]["result"] == 1){
            $resArr["发送短信的企业编号"] = $result["Item"]["@attributes"]["cid"];
            $resArr["发送短信的员工编号"] = $result["Item"]["@attributes"]["sid"];
            $resArr["每次发送的消息编号"] = $result["Item"]["@attributes"]["msgid"];
            $resArr["任务需要的短信条数"] = $result["Item"]["@attributes"]["total"];
            $resArr["任务中每条短信的价格"] = $result["Item"]["@attributes"]["price"];
            $resArr["本次发送后企业的帐户余额"] = $result["Item"]["@attributes"]["remain"];
        }else{
            echo "发送失败，错误原因：".$result["@attributes"]["result"];
            halt($result);
        }
        halt($resArr);
        return "init";
    }

    /**
     *
     */
    public function send1()
    {
        $config = [
            'timeout' => 5.0,
            'default' => [
                'strategy' => OrderStrategy::class,
                'gateways' => ['alidayu'],
            ],
            'gateways' => [
                'errorlog' => [
                    'file' => '',
                ],
                'alidayu' => [
                    'app_key' => config("sms")['dayu']['app_key'],
                    'app_secret' => config("sms")['dayu']['app_secret'],
                    'sign_name' => config("sms")['dayu']["template_register"]["sign_name"],
                ],
            ],
        ];
        $easySms = new EasySms($config);
        $sendRes = $easySms->send(13669361192,[
            'template' => 'SMS_82985023',
            'data' => [
                'code' => 6379
        ],["alidayu"]]);
        halt($sendRes);
    }

    public function sendDayuSmsPlus()
    {
        $res = send_dayu_sms("13669361192", "register", ['code' => rand(100000, 999999)]);
        halt($res);
    }
}