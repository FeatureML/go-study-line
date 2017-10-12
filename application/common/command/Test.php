<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/13 12:53
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\frontend\command;

use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Test extends Command
{
    protected $sleep = 3;
    protected function configure()
    {
        $this->setName('test')->setDescription('Here is the remark frontend THINKPHP5.11');
    }

    protected function execute(Input $input, Output $output)
    {
        while(true){
            $output->writeln(json_encode($this->checkDo()));
            sleep($this->sleep);
        }
    }

    protected function checkDo(){
        $state = false;
        $int = Cache::inc("Command",2);
        return $int;
    }
}