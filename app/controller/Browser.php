<?php
namespace app\controller;
use PhpPupeeteer\PhpPupeeteer;
use app\BaseController;
use think\facade\View;

class Browser extends BaseController
{
	public function index(){
		return View::fetch();
	}

	public function screenshot($site = 'http://www.baidu.com'){
		$path         = 'example.png';
		$phpPuppeteer = new PhpPupeeteer;
		$browser      = $phpPuppeteer->getBrowser([
		    'args' => [
		        '--no-sandbox',
		        '--disable-setuid-sandbox',
		        '--disable-dev-shm-usage',
		        '--disable-gpu',
		        '--incognito',
		    ],
		]);
		$page = $browser->newPage();
		$page->gotoWithWait($site);
		$page->screenshot(['path' => $path]);
		$browser->close();
		if(is_file($path)){
			return sprintf('<img src="%s">',base64EncodeImage($path) );
		}else{
			return '截图失败';
		}
	}
}