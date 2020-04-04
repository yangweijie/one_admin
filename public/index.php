<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 定义后台入口文件
define('ADMIN_FILE', 'admin.php');

// 检查是否安装
if(!is_file('../data/install.lock')){
    define('BIND_MODULE', 'install');
    // 执行HTTP应用并响应
	$http = (new App())->http->name('install');
} else {
    // 执行HTTP应用并响应
	$http = (new App())->http;
}

$response = $http->run();

$response->send();

$http->end($response);
