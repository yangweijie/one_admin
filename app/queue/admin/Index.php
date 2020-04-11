<?php
namespace app\queue\admin;
use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\queue\model\JobsLog;
use think\facade\Console;
use think\facade\View;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Admin
{
	public function index()
	{

		// 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = JobsLog::where($map)->order($order)->append([
        	'info',
        	'time',
        	'status_text'
        ])->paginate();
        $msg      = Console::call('xtask:state')->fetch();
        $cmds     = JobsLog::distinct('uri')->column('uri','uri');
        $top_html = View::fetch('statics', ['total'=>[
        	'pre' => 0,
        	'dos' => 0,
        	'oks' => 0,
        	'ers' => 0,
        ]]);
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
        	->setExtraHtml($top_html, 'block_top')
        	->setPageTips($msg)
            ->setSearch(['title' => '任务名称']) // 设置搜索框
            ->addTimeFilter('create_at', '', '创建时间')
            ->addTopSelect('uri', '全部指令', $cmds)
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['info', '任务信息'],
                ['time', '任务时间'],
                ['status_text', '任务状态'],
                ['right_button', '操作', 'btn']
            ])
            ->hideColumn('id', 'lg')
            ->addRightButton('redo', [
            	'title'=>'重做',
            	'href'=>url('redo', ['id'=>'__id__']),
            	'icon'=>'fa fa-fw fa-repeat'
            ])
            ->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']]) // 批量添加右侧按钮
            ->setColumnWidth([
            	'id'=>20
            ])
            ->replaceRightButton(['status'=>['in', '1,2']], 'redo', '')
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}

	public function redo(){
		try {
            $where = ['id' => $this->request->post('id')];
            $info = JobsLog::where($where)->find();
            if (empty($info)) $this->error('需要重置的任务获取异常！');
            $data = isset($info['data']) ? json_decode($info['data'], true) : '[]';
            \app\admin\service\QueueService::add($info['title'], $info['uri'], $info['later'], $data, $info['double'], $info['desc']);
            $this->success('任务重置成功！', url('index'));
        } catch (\think\exception\HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            $this->error("任务重置失败，请稍候再试！<br> {$e->getMessage()}");
        }
	}

	public function config()
	{
		// 调用ModuleConfig()方法即可
		return $this->moduleConfig();
	}
}
