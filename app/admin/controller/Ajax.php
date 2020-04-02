<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 广东卓锐软件有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\controller\Common;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Attachment as AttachmentModel;
use think\facade\Cache;
use think\facade\Db;

/**
 * 用于处理ajax请求的控制器
 * @package app\admin\controller
 */
class Ajax extends Common
{
    /**
     * 获取联动数据
     * @param string $token token
     * @param int $pid 父级ID
     * @param string $pidkey 父级id字段名
     * @author 蔡伟明 <314013107@qq.com>
     * @return \think\response\Json
     */
    public function getLevelData($token = '', $pid = 0, $pidkey = 'pid')
    {
        if ($token == '') {
            return json(['code' => 0, 'msg' => '缺少Token']);
        }

        $token_data = session($token);
        $table      = $token_data['table'];
        $option     = $token_data['option'];
        $key        = $token_data['key'];

        $data_list = Db::name($table)->where($pidkey, $pid)->column($option, $key);

        if ($data_list === false) {
            return json(['code' => 0, 'msg' => '查询失败']);
        }

        if ($data_list) {
            $result = [
                'code' => 1,
                'msg'  => '请求成功',
                'list' => format_linkage($data_list)
            ];
            return json($result);
        } else {
            return json(['code' => 0, 'msg' => '查询不到数据']);
        }
    }

    /**
     * 获取筛选数据
     * @param string $token
     * @param array $map 查询条件
     * @param string $options 选项，用于显示转换
     * @param string $list 选项缓存列表名称
     * @author 蔡伟明 <314013107@qq.com>
     * @return \think\response\Json
     */
    public function getFilterList($token = '', $map = [], $options = '', $list = '')
    {
        if ($list != '') {
            $result = [
                'code' => 1,
                'msg'  => '请求成功',
                'list' => Cache::get($list)
            ];
            return json($result);
        }
        if ($token == '') {
            return json(['code' => 0, 'msg' => '缺少Token']);
        }

        $token_data = session($token);
        $table = $token_data['table'];
        $field = $token_data['field'];

        if ($field == '') {
            return json(['code' => 0, 'msg' => '缺少字段']);
        }
        if (!empty($map) && is_array($map)) {
            foreach ($map as &$item) {
                if (is_array($item)) {
                    foreach ($item as &$value) {
                        $value = trim($value);
                    }
                } else {
                    $item = trim($item);
                }
            }
        }

        if (strpos($table, '/')) {
            $data_list = model($table)->where($map)->group($field)->column($field);
        } else {
            $data_list = Db::name($table)->where($map)->group($field)->column($field);
        }

        if ($data_list === false) {
            return json(['code' => 0, 'msg' => '查询失败']);
        }

        if ($data_list) {
            if ($options != '') {
                // 从缓存获取选项数据
                $options = cache($options);
                if ($options) {
                    $temp_data_list = [];
                    foreach ($data_list as $item) {
                        $temp_data_list[$item] = isset($options[$item]) ? $options[$item] : '';
                    }
                    $data_list = $temp_data_list;
                } else {
                    $data_list = parse_array($data_list);
                }
            } else {
                $data_list = parse_array($data_list);
            }

            $result = [
                'code' => 1,
                'msg'  => '请求成功',
                'list' => $data_list
            ];
            return json($result);
        } else {
            return json(['code' => 0, 'msg' => '查询不到数据']);
        }
    }

    /**
     * 获取指定模块的菜单
     * @param string $module 模块名
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
    public function getModuleMenus($module = '')
    {
        $menus = MenuModel::getMenuTree(0, '', $module);
        $result = [
            'code' => 1,
            'msg'  => '请求成功',
            'list' => format_linkage($menus)
        ];
        return json($result);
    }

    /**
     * 设置配色方案
     * @param string $theme 配色名称
     * @author 蔡伟明 <314013107@qq.com>
     */
    public function setTheme($theme = '') {
        $map['name']  = 'system_color';
        $map['group'] = 'system';
        if (false !== Db::name('admin_config')->where($map)->update(['value'=>$theme])) {
            $this->success('设置成功');
        } else {
            $this->error('设置失败，请重试');
        }
    }

    /**
     * 获取侧栏菜单
     * @param string $module_id 模块id
     * @param string $module 模型名
     * @param string $controller 控制器名
     * @author 蔡伟明 <314013107@qq.com>
     * @return string
     */
    public function getSidebarMenu($module_id = '', $module = '', $controller = '')
    {
        role_auth();
        $menus = MenuModel::getSidebarMenu($module_id, $module, $controller);

        $output = '';
        foreach ($menus as $key => $menu) {
            if (!empty($menu['url_value'])) {
                $output = $menu['url_value'];
                break;
            }
            if (!empty($menu['child'])) {
                $output = $menu['child'][0]['url_value'];
                break;
            }
        }
        return $output;
    }

    /**
     * 检查附件是否存在
     * @param string $md5 文件md5
     * @author 蔡伟明 <314013107@qq.com>
     * @return \think\response\Json
     */
    public function check($md5 = '')
    {
        $md5 == '' && $this->error('参数错误');

        // 判断附件是否已存在
        if ($file_exists = AttachmentModel::where(['md5' => $md5])->find()) {
            if ($file_exists['driver'] == 'local') {
                $file_path = PUBLIC_PATH.$file_exists['path'];
            } else {
                $file_path = $file_exists['path'];
            }
            return json([
                'code'   => 1,
                'info'   => '上传成功',
                'class'  => 'success',
                'id'     => $file_exists['id'],
                'path'   => $file_path
            ]);
        } else {
            $this->error('文件不存在');
        }
    }

    public function cutfile_upload($action = 'token', $token ='', $data = []){
    	$_tokenPath = 'uploads/tokens/';
    	if(! is_dir($_tokenPath)){
			mkdir($_tokenPath, 0700);
		}
    	$_filePath  = 'uploads/files/';
    	switch ($action) {
    		case 'token':
    			$file         = [];
    			$file['name'] = $_GET['name'];                  //上传文件名称
				$file['size'] = $_GET['size'];                  //上传文件总大小
				$file['token'] = md5(json_encode($file['name'] . $file['size']));
				//判断是否存在该令牌信息
				if(! file_exists($tokenPath . $file['token'] . '.token')){

					$file['up_size'] = 0;                       //已上传文件大小
					$pathInfo        = pathinfo($file['name']);
					$path            = $filePath . date('Ymd') .'/';

					//生成文件保存子目录
					if(! is_dir($path)){
						mkdir($path, 0700);
					}
					//上传文件保存目录
					$file['filePath'] = $path . $file['token'] .'.'. $pathInfo['extension'];
					$file['modified'] = $_GET['modified'];      //上传文件的修改日期
					//保存令牌信息
					$this->cutfile_upload('set_token_info', $file['token'], $file);
				}
				$result['token']    = $file['token'];
				$result['success']  = true;

				//$result['server'] = '';

				echo json_encode($result);
				exit;
    			break;
    		case 'flash':
    			$result['success'] = false;
    			return json($result);
    			break;
    		case 'set_token_info':
    			file_put_contents($tokenPath . $token . '.token', json_encode($data, JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK));
    			break;
    		case 'get_token_info':

    			break;
    		default:
    			if('html5' == $_GET['client']){
    				$token = $_GET['token'];
					$fileInfo = $this->cutfile_upload('get_token_info', $token);
					if($fileInfo['size'] > $fileInfo['up_size']){
						//取得上传内容
						$data = file_get_contents('php://input', 'r');
						if(! empty($data)){
							//上传内容写入目标文件
							$fp = fopen($fileInfo['filePath'], 'a');
							flock($fp, LOCK_EX);
							fwrite($fp, $data);
							flock($fp, LOCK_UN);
							fclose($fp);
							//累积增加已上传文件大小
							$fileInfo['up_size'] += strlen($data);
							if($fileInfo['size'] > $fileInfo['up_size']){
								$this->cutfile_upload('set_token_info', $token, $fileInfo);
							}
							else {
								//上传完成后删除令牌信息
								@unlink($this->_tokenPath . $token . '.token');
							}
						}
					}
					$result['start'] = $fileInfo['up_size'];
					$result['success'] = true;

					echo json_encode($result);
					exit;
				}elseif('form' == $_GET['client']){
					$this->cutfile_upload('flash');
				}else {
					return '';
				}
    			break;
    	}
    }

    public function upload(){
    	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-type: text/html; charset=gbk32");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        if ( !empty($_REQUEST[ 'debug' ]) ) {
            $random = rand(0, intval($_REQUEST[ 'debug' ]) );
            if ( $random === 0 ) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }
        // header("HTTP/1.0 500 Internal Server Error");
        // exit;
        // 5 minutes execution time
        set_time_limit(5 * 60);
        // Uncomment this one to fake upload time
        // Settings
        $targetDir = config('app.upload_path').DIRECTORY_SEPARATOR.'file_material_tmp';            //存放分片临时目录
        $uploadDir = config('app.upload_path').DIRECTORY_SEPARATOR.'file_material'.DIRECTORY_SEPARATOR.date('Ymd');    //分片合并存放目录

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Create target dir
        if (!file_exists($targetDir)) {
            mkdir($targetDir,0777,true);
        }
        // Create target dir
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir,0777,true);
        }
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $oldName = $fileName;

        $fileName = input('md5');
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        // $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory111."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                if(is_file($tmpfilePath)){
	                // Remove temp file if it is older than the max age and is not the current file
	                if (preg_match('/\.(part|parttmp)$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
	                    unlink($tmpfilePath);
	                }
                }
            }
            closedir($dir);
        }
        // Open temp file
        if (!$out = fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream222."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file333."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream444."}, "id" : "id"}');
            }
        } else {
            if (!$in = fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream555."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        fclose($out);
        fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
        $index = 0;
        $done = true;
        for( $index = 0; $index < $chunks; $index++ ) {
            if ( !file_exists("{$filePath}_{$index}.part") ) {
                $done = false;
                break;
            }
        }

        if ($done) {
            $pathInfo = pathinfo($oldName);
            trace($pathInfo);
            $hashStr = substr(md5($pathInfo['basename']),8,16);
            $hashName = time() . $hashStr . '.' .$pathInfo['extension'];
            $uploadPath = $uploadDir . DIRECTORY_SEPARATOR .$hashName;
            if (!$out = fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream666."}, "id" : "id"}');
            }
            //flock($hander,LOCK_EX)文件锁
            if ( flock($out, LOCK_EX) ) {
                for( $index = 0; $index < $chunks; $index++ ) {
                    if (!$in = fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    fclose($in);
                    unlink("{$filePath}_{$index}.part");
                }
                flock($out, LOCK_UN);
            }
            fclose($out);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_info = [
                'uid'    => session('user_auth.uid'),
                'name'   => $pathInfo['basename'],
                'mime'   => finfo_file($finfo, $uploadPath),
                'path'   => $uploadPath,
                'ext'    => ".{$pathInfo['extension']}",
                'size'   => filesize($uploadPath),
                'md5'    => md5_file($uploadPath),
                'sha1'   => sha1_file($uploadPath),
                'thumb'  => '',
                'module' => 'admin',
                'width'  => 0,
                'height' => 0,
            ];

            $file_add = AttachmentModel::create($file_info);
            $response = [
            	'code'   => 1,
                'info'     => '上传成功',
                'class'    => 'success',
                'id'       => $file_add['id'],
                'path'     => $uploadPath
            ];
            return json($response);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

	//分片验证
	public function checkChunk(){
	    $md5 = request()->param()['md5'];
        $targetDir = config('app.upload_path').DIRECTORY_SEPARATOR.'file_material_tmp';            //存放分片临时目录
        $uploadDir = config('app.upload_path').DIRECTORY_SEPARATOR.'file_material'.DIRECTORY_SEPARATOR.date('Ymd');
	    if(is_dir($targetDir)) {
	    	$files = [];
	    	$tmp_files = glob("{$targetDir}/{$md5}_*");
	    	if($tmp_files){
		    	foreach ($tmp_files as $file) {
		    		$num = str_ireplace(["{$targetDir}/{$md5}_",".part"], ['', ''], $file);
		    		if(stripos($num, 'tmp') === false){
		    			$files[] = $num;
		    		}
		    	}
	    	}
	    	sort($files);
	        return json(['code'=>1, 'data'=>$files], 200, [], ['json_encode_param'=>JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK]);
	    }else{
	        return json(['code'=>0]);
	    }
	}
}