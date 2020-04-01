<?php
use think\facade\Cache;
use think\facade\Db;
use util\File;
use util\Tree;

if (!function_exists('get_front_cache')) {
	function get_front_cache() {
		// if (false === Cache::get('front_cache')) {
		$single_list = \app\cms\model\Page::where('status', 1)->order('update_time DESC')->select();
		$new_article = \app\cms\model\Document::view('cms_document', true)
				->view("cms_column", ['name' => 'column_name'], 'cms_column.id=cms_document.cid', 'left')
				->view("admin_user", 'username', 'admin_user.id=cms_document.uid', 'left')
				->where(['cms_document.status' => 1])
				->order('update_time DESC')
				->limit(3)->select();

		$category = model('cms/column')->where('status = 1')->order('sort asc')->select();
		$count = model('cms/document')->group('cid')->where('status=1')->column('cid, count(*) as num');
		foreach ($category as $key => $value) {
			$category[$key]['article_num'] = isset($count[$value['id']]) ? $count[$value['id']] : 0;
		}
		// $cate = \app\cms\model\Column::where('status=1')->column(true);
		$cate = Tree::config(['title' => 'name'])->toList($category);
		$list = app\cms\model\Document::getList([], 'create_time DESC, id DESC');
		$date = $time = [];
		foreach ($list as $key => $value) {
			if ($value['create_time']) {
				$time[] = date('F Y', $value['create_time']);
			}
		}
		$time = array_unique($time);
		foreach ($time as $key => $value) {
			$date[] = array(
				'text' => $value,
				'link' => sprintf('/index/archive/year/%d/month/%s',
					date('Y', strtotime($value)),
					date('m', strtotime($value))
				)
			);
		}

		$cache = array(
			'single_list' => $single_list,
			'new_article' => $new_article,
			'cate' => $cate,
			'date' => $date,
		);

		// dump($date);
		// Cache::set('front_cache', $cache);
		// }
		return $cache;
		// return Cache::get('front_cache');
	}

}

// 函数库
function base64EncodeImage ($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}
function base64DecodeImage($base64)
{
    $base64_image = str_replace(' ', '+', $base64);
    //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
        //匹配成功
        if ($result[2] == 'jpeg') {
            $image_name = time() . '.jpg';
            //纯粹是看jpeg不爽才替换的
        } else {
            $image_name = time() . '.' . $result[2];
        }
        $file_url = config('app.upload_path') . DS .'images'.DS. date("Ymd") . DS;
        $create   = create_folder($file_url);
        if (!$create) {
            if(PHP_SAPI == 'cli'){
                return '上传失败：创建目录失败，请检查' . $file_url . '目录有无可写权限';
            }else{
                halt('上传失败：创建目录失败，请检查/' . $file_url . '目录有无可写权限');
            }
        }
        $image_file = "{$file_url}{$image_name}";
        //服务器文件存储路径
        if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))) {
            return $image_file;
        } else {
            session('upload_error_msg', error_get_last());
            return false;
        }
    } else {
        session('upload_error_msg', '非正常的图片内容格式');
        return false;
    }
}

function get_num_from_str($str){
    return preg_replace('/\D/', '', $str);
}

// 生成js时间戳
function jstime(){
    return substr(get_num_from_str(microtime(true)), 0, 13);
}

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0) {
	$name = Db::name('admin_user')->getFieldById($uid, 'nickname');
	return $name ? $name : '无名';
}

if (!function_exists('get_cate_name')) {

	function get_cate_name($cid) {
		return Db::name('cms_column')->getFieldById($cid, 'name');
	}

}

//获取标签
function get_tag($id, $link = true) {
	$tags = Db::name('cms_document_article')->getFieldByAid($id, 'tags');
	if ($link && $tags) {
		$tags = explode(',', $tags);
		$link = array();
		foreach ($tags as $value) {
			$link[] = '<a href="' . url('/') . '?tag=' . $value . '">' . $value . '</a>';
		}
		return join($link, ',');
	} else {
		return $tags ? $tags : 'none';
	}
}

//缩短网址
function short_url($url) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "http://dwz.cn/create.php",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS =>"url={$url}",
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
		trace("cURL Error #:" . $err);
		return 0;
		echo "cURL Error #:" . $err;
	} else {
		$ret = json_decode($response, 1);
		if($ret['status']){
			return $ret['tinyurl'];
		}else{
			trace('err_msg:'.$ret['err_msg']);
			return 0;
		}
//        echo $response;
	}
}

// 异步curl_post
function curl_post_async($url, $data = null, $type = 'array'){
	return curl_post($url, $data,$type, 1);
}

function curl_post($url, $data = null, $type = 'array', $async = 0)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //协议头 https，curl 默认开启证书验证，所以应关闭
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制ipv4解析
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		if($async){
			curl_setopt($ch, CURLOPT_NOSIGNAL, 1);     //注意，毫秒超时一定要设置这个
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);
		}
		if ($type == 'array') {
			if (!$data) {
				$data = [];
			}
			$data = http_build_query($data);
		} else {
			if ($data) {
				$data = json_encode($data);
			} else {
				$data = '';
			}
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$ret = curl_exec($ch);
		if (curl_errno($ch)) {
			$err_code = curl_errno($ch);
			$err_msg  = curl_error($ch);
			curl_close($ch);
			throw new \Exception("curl错误:" . $err_code . ',' . $err_msg);
		}
		curl_close($ch);
		return $ret;
	}


define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);



if(!function_exists('is_online')){

	function is_online()
	{
		if(PHP_SAPI == 'cli'){
			return isset($_SERVER['LOGNAME']) && $_SERVER['LOGNAME'] != 'jay';
		}else{
			return stripos($_SERVER['HTTP_HOST'], config('app.web_site_domain')) !== false;
		}
	}
}


/**
 * 导出数据为excel
 * @param  string $filename 文件名
 * @param  array $data     查询出来的数组
 * @param  array  $header   ['name'=>'名称'] 和数组查询出来的顺序保持一致
 * @return mixed  下载
 */
function export_excel_form_select($filename, $data, $header){
	if($data){
		if(isset($header['table'])){
			exception('数据中包含与到处逻辑不兼容的字段 table');
		}
		// dump($data);
	}
	// dump($header);
	// die;
	$table = '<head><meta charset="UTF-8"><title>Document</title></head>';
	// style="vnd.ms-excel.numberformat:@"
	$table .= '<table style="vnd.ms-excel.numberformat:@"><thead><tr>';
	foreach ($header as $key=>$col) {
		$table.= sprintf('<th class="name">%s</th>', $col);
	}
	$table.='</tr></thead><tbody>';
	if($data){
		foreach ($data as $row) {
			extract($row);
			$tr = compact(array_keys($header));
			$table.='<tr>';
			foreach ($tr as $v) {
				$table.= sprintf('<td class="name">%s</td>', (string)$v);
			}
			$table.='</tr>';
		}
	}else{
		$table.='</tbody></table>';
	}

	// header("Content-type:application/octet-stream");
  //   header("Accept-Ranges:bytes");
  //   header("Content-type:application/vnd.ms-excel");
  //   header("Content-Disposition:attachment;filename=".$filename.".xls");
  //   header("Pragma: no-cache");
  //   header("Expires: 0");



	header("Pragma: public");
	header("Content-type: text/html; charset=utf-8");
	header("Expires: 0");
	header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
	header("Content-Type:application/force-download");
	header("Content-Type:application/vnd.ms-execl");
	header("Content-Type:application/octet-stream");
	header("Content-Type:application/download");
	header('Content-Disposition:attachment;filename="'.$filename.'.xls"');
	header("Content-Transfer-Encoding:binary");
	exit($table);
}

/**
 * 在数据列表中搜索
 * @access public
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 * @return array
 */
function list_search($list,$condition) {
	if(is_string($condition))
		parse_str($condition,$condition);
	// 返回的结果集合
	$resultSet = [];
	foreach ($list as $key=>$data){
		$find   =   false;
		foreach ($condition as $field=>$value){
			if(isset($data[$field])) {
				if(0 === strpos($value,'/')) {
					$find   =   preg_match($value,$data[$field]);
				}elseif($data[$field]==$value){
					$find = true;
				}
			}
		}
		if($find)
			$resultSet[]     =   &$list[$key];
	}
	return $resultSet;
}

// 生成js时间戳
function jstime(){
	return substr(get_num_from_str(microtime(true)), 0, 13);
}

function get_num_from_str($str){
	return preg_replace('/\D/', '', $str);
}

