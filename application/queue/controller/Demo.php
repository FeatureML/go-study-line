<?php

/**
 * Created for thinkphp5-study-line.
 * File: Demo.php
 * User: cools
 * Date: 2017/8/6 0006
 * Time: 14:02
 * Description :
 */
namespace app\queue\controller;

use think\Log;
use think\Queue;
use app\queue\consumer\HelloJob;
class Demo
{
    /**
     * push a new job into queue
     */
    public function test(  )
    {
        return "test";
    }

    public function index(  )
    {
        $consumer = HelloJob::class;    // app\queue\consumer\HelloJob
        $link = "http://www.baidu.com/backend/login/emailRegisterUrlValid?checkstr=11111111111&auth_key=909090909090";
        $payLoad = [
            'time' => time(),
            'mail' => "1722318623@qq.com",
            'url' => $link,
            ];
        $queue = 'mail';

        $pushed = Queue::push($consumer ,$payLoad ,$queue);
        if ($pushed) {
            Log::error("[100000000000000000]加入队列成功，开始执行队列：".$pushed);
//            $command = 'queue:work' ;
//            $params = ['--queue='.$queue , '--sleep=3'];
//            $result = \think\Console::call($command ,$params);
//            Log::error("[200000000000000000]队列执行结束，返回结果：".json_encode($result));
            echo 'job Pushed.' . '<br/>' .'job payload :' . '<br/>' .var_export($payLoad,TRUE);
        }else{
            echo 'Oops, something wrong with your queue';
        }
    }

    /**
     * 命令行执行
     */
    public function cmdCliTest(  )
    {
        $queue = 'mail';
        $queueFile = CLI_PATH."queue_cli.php";
        echo '------------------------------------ 开始一个CLi进程--------------------------------';
//        exec("php think queue:work --queue mail",$output, $return_val);
        exec("php D:\wamp64\www\thinkphp5-study-line/cli/queue_cli.php mail",$output, $return_val);
        echo "<hr/>";
        var_dump($output);  //命令执行后生成的数组
        echo "执行的状态:".$return_val."<br/>"; //执行的状态
        echo "<hr/>";
        echo '-----------------------------------结束一个CLi进程 ----------------------------------';
    }
}