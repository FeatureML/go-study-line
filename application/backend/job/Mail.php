<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/8/4
 * Time: 22:15
 */

namespace application\backend\job;

use think\Controller;
use think\Log;
use think\queue\Job;

class Mail extends Controller
{
    /**
     * fire方法是消息队列默认调用的方法
     * @param Job $job 当前的任务对象
     * @param array|mixed $data 发布任务时自定义的数据
     */
    public function fire(Job $job, $data)
    {
        //删除任务
        $isJobDone = $this->doJob($data);
        if ($isJobDone) {
            //如果任务执行成功， 记得删除任务
            Log::error("[20000000000]邮件队列执行 ".date("Y-m-d H:i:s"),time());
            $job->delete();
            print("<info>Hello Job has been done and deleted" . "</info>\n");
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print("<warn>Hello Job has been retried more than 3 times!" . "</warn>\n");
                $job->delete();
                // 也可以重新发布这个任务
                print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                $job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
            }
        }
    }

    /**
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function doJob($data)
    {
        // 根据消息中的数据进行实际的业务处理...
        print("<info>Hello Job Started. job Data is: " . var_export($data, true) . "</info> \n");
        //开始发送邮件
        send_email($data["email"], '物联网智能数据 帐户激活邮件--', "1722318623@qq.com");
        Log::error("[10000000000]邮件队列执行 ".date("Y-m-d H:i:s"),time());
        print("<info>Hello Job is Fired at " . date('Y-m-d H:i:s') . "</info> \n");
        print("<info>Hello Job is Done!" . "</info> \n");
        return true;
    }
}