<?php
// 应用公共文件

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
        $file_url = config('upload_path') . DS .'images'.DS. date("Ymd") . DS;
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