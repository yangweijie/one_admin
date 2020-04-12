<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 广东卓锐软件有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------

namespace app\queue\model;

use think\Model;


/**
 * @package app\queue\model
 */
class SystemQueue extends Model
{

	protected $name = 'system_queue';

 	const status_text = [
		'1'=>'待处理',
		'2'=>'处理中',
		'3'=>'处理完成',
		'4'=>'处理失败',
	];

 	const status_class = [
		'1' => 'primary',
		'2' => 'success',
		'3' => 'info',
		'4' => 'danger',
	];

	public function getInfoAttr($value, $data){
		return sprintf('任务编号：%s%s任务名称：%s%s任务指令：%s', $data['code'], '<br>', $data['title'], '<br>', $data['command']);
	}

	public function getTimeAttr($value, $data){
		$return   = [];
		$first    = '计划时间：'.format_datetime($data['exec_time']);
		if(isset($data['exec_pid']) && $data['exec_pid']>0){
			$first .= sprintf('（ 进程 <b class="text-primary">%s</b> ）', $data['exec_pid']?:'-');
		}
		$return[] = $first;
		if($data['enter_time']>0 && $data['outer_time'] >0){
			$return[] = sprintf('执行时间：%s（ 耗时 <b class="text-primary">%s</b> 秒 ）', format_datetime($data['enter_time']), sprintf("%.4f",$data['outer_time'] - $data['enter_time']) );
		}elseif($data['status'] == 2){
			$return[] = sprintf('执行时间：%s（ 任务执行中 ）', format_datetime($data['enter_time']));
		}else{
			$return[] = '执行时间：<span class="color-desc">任务还没有执行，等待执行...</span>';
		}
		$third = '创建时间：'.format_datetime($data['create_at']);
		if(isset($data['loops_time']) && $data['loops_time'] > 0){
			$third .= sprintf('每 <b class="color-blue">%d</b> 秒执行，共 <b class="color-blue">%d</b> 次）', $data['loops_time']?:0, $data['attempts']);
		}else{
			$third .= sprintf('（ 共执行 <b class="color-blue">%d</b> 次 ）', $data['attempts']);
		}

		$return[] = $third;
		return implode('<br>', $return);
	}

	public function getStatusTextAttr($value, $data){
		$loop_text    = $data['loops_time'] && $data['loops_time'] > 0 ?'循':'';
		$double_text  = $data['rscript'] == 1?'复':'单';
		$status_text  = self::status_text[$data['status']];
		$status_class = self::status_class[$data['status']];

		$return = [''];
		$first  = '';
		if($loop_text){
			$first .= "<span class='label label-warning'>{$loop_text}</span>&nbsp;&nbsp;";
		}
		$first .= $double_text == '复'? "<span class='label label-success'>{$double_text}</span>&nbsp;&nbsp":"<span class='label label-info'>{$double_text}</span>&nbsp;&nbsp;";

		$first .= "<span class='label label-{$status_class}'>{$status_text}</span>";
		$return[] = $first;
		$return[] = "<p class='text-muted'>{$data['exec_desc']}</p>";
		return implode('<br>', $return);
	}
}
