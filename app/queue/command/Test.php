<?php

namespace app\queue\command;

use think\admin\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Test extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('test')->setDescription('the test command');
    }

    protected function execute(Input $input, Output $output)
    {
    	$todos = range(1, $this->queue->data['total']);
    	// $todos = range(1, 50);
    	$total = count($todos);
    	$done  = 0;
    	foreach ( array_chunk($todos, 100) as $todo) {
    		foreach ($todo as $value) {
	    		$string = str_pad(++$done, strlen((string)$total), '0', STR_PAD_LEFT);
	            $message = "({$string}/{$total}) -> $done";
	            $this->setQueueProgress(2, $message, $done * 100 / $total);
    		}
            sleep(1);
    	}
        $this->output->comment('--> 同步完成');
        $this->output->newLine();
    	$this->setQueueMessage(3, "同步{$total}个数据成功");
    }
}
