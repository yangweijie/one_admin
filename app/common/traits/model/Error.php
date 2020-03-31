<?php

/**
 * 用法：
 *
 * class index
 * {
 *     use \app\common\traits\model\Error;
 *     public function index(){
 *         $this->error();
 *         $this->redirect();
 *     }
 * }
 */
namespace app\common\traits\model;


trait Error
{
	public function getError(){
		return $this->error;
	}
}