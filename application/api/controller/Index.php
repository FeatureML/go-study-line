<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan
 * |  Date: 2017/2/20
 * |  Time: 8:36
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
namespace app\api\controller;

use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return "This is api module";
    }

    public function test()
    {
        trace('Hello Test');
        return '111111111111';
    }

    /**
     * 默认输出类型 测试
     * URL访问：http://127.0.0.1:8080/api.php/index/default_return_type
     * @return string
     */
    public function default_return_type()
    {
        $res = [
            'code' => 200,
            'result'=>[
                'username'=>"tinywan",
                'age'=>24
            ]
        ];
        return $res;
    //        返回结果：
    //        {
    //            "code": 200,
    //            "result": {
    //                        "username": "tinywan",
    //                        "age": 24
    //            }
    //        }
    }

    public function notify()
    {
        // 配置信息
        $config = [
            'app_key'    => '23651008',
            'app_secret' => '1ef044c5017a0337da3b43e3a1236822',
        ];
        $client = new Client(new App($config));
        $req    = new AlibabaAliqinFcSmsNumSend();
        $req->setRecNum("13669361192")
            ->setSmsParam([
                'number' => rand(100000, 999999)
            ])
            ->setSmsFreeSignName("弍萬")
            ->setSmsTemplateCode("SMS_50285067");
        print_r($client->execute($req));

    }
}
