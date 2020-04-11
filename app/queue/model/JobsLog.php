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
class JobsLog extends Model
{
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
		return sprintf('任务名称：%s%s任务指令：%s', $data['title'], '<br>', $data['uri']);
	}

	public function getTimeAttr($value, $data){
		return sprintf('创建时间：%s%s跟进时间：%s', $data['create_at'], '<br>', $data['status_at']);
	}

	public function getStatusTextAttr($value, $data){
		$double_text  = $data['double'] == 1?'复':'单';
		$status_text  = self::status_text[$data['status']];
		$status_class = self::status_class[$data['status']];

		// halt([
		// 	$status_text,
		// 	$status_class
		// ]);

		return sprintf('任务状态：%s%s状态描述：%s', "<span class='label label-info'>{$double_text}</span><span class='label label-{$status_class}'>{$status_text}</span>", '<br>', "<span class='label label-default'>{$data['desc']}</span>");
	}
}
