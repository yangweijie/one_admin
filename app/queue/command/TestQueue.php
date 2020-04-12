<?php

namespace app\queue\command;

use think\admin\Service\QueueService;

class TestQueue extends QueueService
{

	public function execute(array $data = [])
	{
		$todos = range(1,$data['total']);
		// $todos = range(1,50);
		$total = count($todos);
		$done  = 0;
		foreach ( array_chunk($todos, 100) as $todo) {
			foreach ($todo as $value) {
				$string = str_pad(++$done, strlen((string)$total), '0', STR_PAD_LEFT);
				$message = "({$string}/{$total}) -> $done";
				$this->progress(2, $message, $done * 100 / $total);
			}
			sleep(1);
		}
		return "同步{$total}个数据成功";
	}
}
