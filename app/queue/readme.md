# 任务队列

## 来源
移植自 think-admin 的系统任务功能

依赖 think-library 库

## 特点

* 只依赖一张system_queue表 不需要redis
* win 友好，模块列表上可以手动开启监听 且无命令弹窗
* 支持任务间进度消息
* 队列执行内容代码更新是实时的不常驻内存，不需要重启监听进程 （win 已测，linux 待测试）
* 支持延时队列
* 自带清理死任务 执行超过1小时

## 使用

### 定义
#### 命令
```
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

```

定义一个命令 继承 think\admin\Command ，然后app\config\console 里增加命令映射，尝试过应用配置console 里增加，无效

里面有 $this->queue 和 $this->process 对象属性 调试可自行序列化后输出到 2状态 queue message sleep(10)长点时间 查看进度弹窗可见

$this->queue['data'] 就是创建队列任务的参数

#### 类库

```
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

```

参考 TestQueue 定义一个类 继承QueueService
execute 方法入参 $data 直接是创建队列任务时的参数

### 添加队列任务

#### 类库
```
QueueService::instance()->register('测试任务', '\app\queue\command\TestQueue', 3, ['total'=>3000], 1, 0);
```

#### 命令
```
QueueService::instance()->register('测试任务', 'test', 3, ['total'=>2000], 1, 0);
```

### 失败

在execute 方法中 抛异常 错误码 3 或 4


