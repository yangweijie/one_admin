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

	public function showbrowser($site = 'http://www.baidu.com'){
		$phpPuppeteer = new PhpPupeeteer;
		$path         = 'example.png';
		$browser = $phpPuppeteer->getBrowser([
		    'headless' => false,
		    'args' => [
		        '--no-sandbox',
		        '--disable-setuid-sandbox',
		        '--disable-dev-shm-usage',
		        '--incognito',
		        '--start-maximized',
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

	// 获取设置cookie的字符串
	public static function cookie_set($cookie_str, $domain){
		$arr = explode(';', $cookie_str);
		$ret = [];
		foreach ($arr as $a){
			$pair = explode('=', $a);
			$name = $pair[0];
			$value = $pair[1];
			$ret[] = [
				'name'  => $name,
				'value' => $value,
				'domain'   => $domain
			];
		}
		return $ret;
	}

	public function setcookie(){
		$site = 'http://cpquery.sipo.gov.cn/txnPantentInfoList.do?inner-flag:open-type=window&inner-flag:flowno='.jstime();
		$cookie = '_gscu_930750436=67739144ppjx2z17; _gscbrs_930750436=1; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; _gscs_930750436=68594346i4zxjw21|pv:2; JSESSIONID=78f3f1a23ad7b3b4b6702af0b21a; bg4=36|ADMIY';
		$cookie_arr = self::cookie_set($cookie, 'cpquery.cnipa.gov.cn');
		$phpPuppeteer = new PhpPupeeteer;
		$path         = 'example2.png';
		$browser = $phpPuppeteer->getBrowser([
		    'headless' => false,
		    'args' => [
		        '--no-sandbox',
		        '--disable-setuid-sandbox',
		        '--disable-dev-shm-usage',
		        '--incognito',
		        '--start-maximized',
		    ],
		]);
		$page = $browser->newPage();
		// foreach ($cookie_arr as $key => $cookie) {
		// 	$page->setCookie($cookie);
		// }
		$page->gotoWithWait('http://www.baidu.com');
		$page->setCookie($cookie_arr);
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