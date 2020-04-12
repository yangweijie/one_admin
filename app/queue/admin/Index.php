<?php
namespace app\queue\admin;
use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\queue\model\SystemQueue;

use think\admin\service\ProcessService;
use think\admin\service\QueueService;
use think\exception\HttpResponseException;

use think\App;
use think\admin\helper\ValidateHelper;
use think\facade\Console;
use think\facade\View;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Admin
{

    protected $table = 'SystemQueue';


	/**
     * 应用容器
     * @var App
     */
    public $app;

    /**
     * 请求对象
     * @var Request
     */
    public $request;

    /**
     * 初始化
     * @author 蔡伟明 <314013107@qq.com>
     * @throws \think\Exception
     */
    protected function initialize()
    {
        parent::initialize();
        $app           = app();
        $this->app     = $app;
        $this->request = $app->request;
        $this->app->bind('think\admin\Controller', $this);
    }

	public function index()
	{
		try {
            $this->command = ProcessService::instance()->think('xtask:start');
            $this->message = $this->app->console->call('xtask:state')->fetch();
            $this->listen  = preg_match('/process.*?\d+.*?running/', $this->message, $attr);
        } catch (\Exception $exception) {
            $this->listen  = false;
            $this->message = $exception->getMessage();
        }
		// 查询
		$map = $this->getMap();
		// 排序
		$order = $this->getOrder('loops_time desc,id desc');
		// 数据列表
		$data_list = SystemQueue::where($map)->order($order)->append([
			'info',
			'time',
			'status_text'
		])->paginate();
		$msg      = Console::call('xtask:state')->fetch();
		$cmds     = SystemQueue::distinct('command')->column('command','command');
		$this->total = ['dos' => 0, 'pre' => 0, 'oks' => 0, 'ers' => 0];
		$query = $this->app->db->name($this->table)->field('status,count(1) count');
        foreach ($query->group('status')->select()->toArray() as $item) {
            if ($item['status'] == 1) $this->total['pre'] = $item['count'];
            if ($item['status'] == 2) $this->total['dos'] = $item['count'];
            if ($item['status'] == 3) $this->total['oks'] = $item['count'];
            if ($item['status'] == 4) $this->total['ers'] = $item['count'];
        }
        $title = sprintf('队列任务（待处理：%d | 处理中：%d | 处理完成：%d | 处理失败：%d）', $this->total['pre'], $this->total['dos'], $this->total['oks'], $this->total['ers']);
        $iswin = ProcessService::instance()->iswin();
		$css = <<<style
<style>
.block-options > li > a.btn{
	opacity:1 !important;
	color:#FFF !important;
}
.builder-table-body td{
	font-size:12px;
}
.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th {
	padding:8px !important;
}
</style>
style;
		$js = View::fetch('index_js');
		// 使用ZBuilder快速创建数据表格
		$zb = ZBuilder::make('table')
			->setPageTitle($title)
			->setTableName('system_queue')
			->setExtraCss($css)
			->setHeight(280)
			->setExtraHtmlFile('statics', 'block_top', [
				'total'   => $this->total,
				'msg'     => $msg,
				'command' => $this->command,
			])
			// ->setExtraHtml($top_html, 'block_top')
			->setExtraJs($js)
			->js('queue/layui.all')
    		->css('layui,console')
			->setSearch([
				'code'  => '任务编号',
		        'title' => '任务名称',
			]) // 设置搜索框
			->addTopSelect('command', '任务指令', $cmds)
			->addTopSelect('status', '任务状态', SystemQueue::status_text)
			// ->addTimeFilter('exec_time', '', '计划时间')
			// ->addTimeFilter('enter_time', '', '执行时间')
			->addTimeFilter('create_at', '', '创建时间')
			;

			if($iswin){
				$start_url   = url('start');
				$stop_url    = url('stop');
				$listen_btns = <<<HTML
<div class="row data-table-toolbar">
<div class="col-sm-12">
	<div class="toolbar-btn-action pull-right">
		<a title="开始监听" class="btn btn-primary ajax-get confirm" target-form="ids" href="{$start_url}">开始监听</a>
		<a title="停止监听" class="btn btn-primary ajax-get confirm" target-form="ids" href="{$stop_url}">停止监听</a>
	</div>
</div>
</div>
HTML;
				$zb->setExtraHtml($listen_btns, 'toolbar_top');
			}
			$zb->addTopButton('clear', [
				'href'  => url('clear'),
				'class' => 'btn btn-primary ajax-get confirm',
				'title' => '定时清理'
			]);
			$zb->addTopButton('test', [
				'href'  => url('test'),
				'class' => 'btn btn-primary ajax-get confirm',
				'title' => '测试'
			]);
			$zb->addTopButton('delete');

			$zb->addColumns([ // 批量添加数据列
				['id', 'ID'],
				['info', '任务信息'],
				['time', '任务时间'],
				['status_text', '任务状态'],
				['right_button', '操作', 'btn']
			])
			->hideColumn('id', 'lg')
			->addRightButton('redo', [
				'title' => '重做',
				'class'=>'btn btn-xs btn-default ajax-get',
				'href'  => url('redo', ['id'=>'__id__']),
				'icon'  => 'fa fa-fw fa-repeat'
			])
			->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']]) // 批量添加右侧按钮
			->setColumnWidth('_checkbox', 16)
			->setColumnWidth([
				'info'         => 100,
				'time'         => 200,
				'right_button' => 38,
			])
			->addRightButton('process', [
				'onclick' => '$.loadQueue(this.getAttribute(\'code\'),false);return false',
				'class'   => 'btn btn-xs btn-default process',
				'icon'    => 'fa fa-fw fa-inbox',
				'href'    => '__code__',
				'title'=>'任务进度信息',
			])
			->setRowList($data_list); // 设置表格数据
		return $zb->fetch(); // 渲染模板
	}

	/**
     * 重启系统任务
     * @auth true
     */
    public function redo($id)
    {
    	$row = SystemQueue::find($id);
    	if(!$row){
    		$this->error('任务不存在！');
    	}else{
    		$code = $row['code'];
    	}
        try {
            $queue = QueueService::instance()->initialize($code)->reset();
            $queue->progress(1, '>>> 任务重置成功 <<<', 0.00);
            $this->success('任务重置成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 重启任务结果处理
     * @param boolean $state
     */
    protected function _redo_save_result($state)
    {
        if ($state) {
            $this->success('重启任务成功！');
        }
    }

	/**
     * WIN创建监听进程
     * @auth true
     */
    public function start()
    {
        try {
            $message = nl2br($this->app->console->call('xtask:start')->fetch());
            if (preg_match('/process.*?\d+/', $message, $attr)) {
                $this->success('任务监听主进程启动成功！');
            } else {
                $this->error($message);
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * WIN停止监听进程
     * @auth true
     */
    public function stop()
    {
        try {
            $message = nl2br($this->app->console->call('xtask:stop')->fetch());
            if (stripos($message, 'succeeded')) {
                $this->success('停止任务监听主进程成功！');
            } elseif (stripos($message, 'finish')) {
                $this->success('没有找到需要停止的进程！');
            } else {
                $this->error($message);
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 创建记录清理任务
     * @auth true
     */
    public function clear()
    {
        try {
            QueueService::instance()->addCleanQueue();
            $this->success('创建清理任务成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    // 任务进度查询
    public function progress($code = ''){
    	if(empty($code)){
    		$this->error('任务编号不能为空！');
    	}
        $queue = QueueService::instance()->initialize($code);
        return json([
        	'code'=>1,
        	'info'=>'获取任务进度成功！',
        	'data'=>$queue->progress()
        ]);
    }

    public function test(){
    	try {
            // QueueService::instance()->register('测试任务', '\app\queue\command\TestQueue', 3, ['total'=>3000], 1, 0);
            QueueService::instance()->register('测试任务', 'test', 3, ['total'=>2000], 1, 0);
            $this->success('添加测试任务成功');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

	public function config()
	{
		// 调用ModuleConfig()方法即可
		return $this->moduleConfig();
	}
}
