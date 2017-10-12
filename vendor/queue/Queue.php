<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/14 10:14
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace queue;


class Queue
{
    // 队列名称
    static protected $queue_name;
    // 任务名
    static protected $job_name;
    // 参数
    static protected $args;

    /**
     * Queue::in('push', 'NotificationJob', ['push_uid' => $push_uid, 'push_msg' => $push_msg]);
     * @param string $queue_name
     * @param $job_name
     * @param $args
     * @return bool
     * @static
     */
    public static function in($queue_name = 'default', $job_name, $args)
    {
        if(empty($job_name)) return false;
        self::$queue_name = $queue_name;
        self::$job_name = $job_name;
        self::$args = $args;

        date_default_timezone_set("GMT");

        // 这是Redis链接
        \Resque::setBackend('127.0.0.1:6379');

        $jobId = \Resque::enqueue($queue_name, $job_name, $args, true);
//        echo "Queued job ".$jobId."\n\n";
        return $jobId;
    }
}