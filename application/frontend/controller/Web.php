<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/27
 * Time: 22:01
 */
namespace app\frontend\controller;
use app\common\controller\BaseFrontend;
use redis\BaseRedis;

class Web extends BaseFrontend
{
    public function index()
    {
        return $this->fetch();
    }

    public function workerMan()
    {
        $this->view->engine->layout(false);
        return $this->fetch();
    }

    /**
     * 内存管理
     */
    public function internalManage()
    {        //  监控的服务器IP
        $moniServerIp = "127.0.0.1";
        $moniServerSshUsername = "www";
        $moniServerSshPassword = "www123456";
        $this->view->engine->layout(false);
        return $this->fetch("internalManage",[
            "moniServerIp"=>$moniServerIp,
            "moniServerSshUsername"=>$moniServerSshUsername,
            "moniServerSshPassword"=>$moniServerSshPassword,
        ]);
    }

    public function login()
    {
        $callback = function($a,$b) {
            return $a + $b;
        };
        echo $callback(120,120);
    }

    public function redisLogin()
    {
        $redis = BaseRedis::Instance();
        $redis->connect("127.0.0.1",63789);
        $redis->auth("12312312");
        var_dump($redis->keys("*"));
        $REDIS_MEMORY_INFO = $redis->lRange("REDIS_MEMORY_INFO:001",0,-1);
        //通过索引获取列表中的元素
        $need = $redis->lIndex("REDIS_MEMORY_INFO:001",-1);
        var_dump($REDIS_MEMORY_INFO);
        var_dump($need);
    }

    public function loginTicket($tax)
    {
        $total = 0.0;
        $callback = function($quantity,$product) use ($tax , &$total){
            //constant 返回常量的值
            //__class__返回类名
            $price = constant(__CLASS__."::PRICE_".strtoupper($product));
            $total += ($price * $quantity)*($tax+1.0);
        };
        //array_walk() 函数对数组中的每个元素应用用户自定义函数。在函数中，数组的键名和键值是参数
        array_walk($this->products,$callback);
        //回调匿名函数
        return round($total,2);
    }
}