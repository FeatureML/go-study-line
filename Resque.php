<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/14 10:51
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/
date_default_timezone_set("GMT");

spl_autoload_register(function ($class_name){
    require_once dirname(__FILE__).'/vendor/queue/'.$class_name.'.php';
});

require_once dirname(__FILE__).'/vendor/chrisboulton/php-resque/lib/Resque.php';
require_once dirname(__FILE__).'/vendor/chrisboulton/php-resque/lib/Resque/Worker.php';

$QUEUE = getenv('QUEUE');

if(empty($QUEUE)){
    die('Specify the name of a job to add. e.g, php queue.php PHP_Job');
}
\Resque::setBackend('127.0.0.1:6379');
