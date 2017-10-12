<?php

/**
 * Created for thinkphp5-study-line.
 * File: HelloJob.php
 * User: cools
 * Date: 2017/8/6 0006
 * Time: 14:03
 * Description :
 */
namespace app\queue\consumer;

use think\Log;

class HelloJob
{
    public function fire( $job ,$payload)
    {
        $done = $this->consume($payload);
        if ($done) {
            $this->deleteJob($job);
        }else{
            $this->releaseJob($job);
        }
    }

    private function consume( $payload )
    {
        try {
            Log::error("think-queue running on windows".date("Y-m-d H:i:s"));
            send_email($payload["mail"], '物联网智能数据--', $payload["url"]);
            echo ' Job Processed send_email!' . ' payload: '. PHP_EOL . var_export($payload,true) .PHP_EOL;
            return true;
        } catch (\Exception $e) {
            echo 'Hello Job Process Failed!' ;
            var_dump($e);
            return false;
        }
    }
    /**
     * 该方法用于接收任务执行失败的通知，你可以发送邮件给相应的负责人员
     * @param $jobData  string|array|...      //发布任务时传递的 jobData 数据
     */
    public function failed($jobData){
        Log::error("Job failed after max retries");
        print("Warning: Job failed after max retries. job data is :".var_export($jobData,true)."\n");
    }


    private function deleteJob( $job )
    {
        $job->delete();
    }

    private function releaseJob( $job )
    {
        $job->release(3);
    }
}