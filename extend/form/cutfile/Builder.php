<?php
namespace form\cutfile;

class Builder
{
    /**
     * 添加分块单文件上传
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param string $size 文件大小，单位为kb
     * @param string $ext 文件后缀
     * @param string $extra_class 额外css类名
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
	public function item($name = '', $title = '', $tips = '', $default = '', $size = '', $ext = '', $extra_class = '')
	{
		$size = ($size != '' ? $size : config('upload_file_size')) * 1024;
        $ext  = $ext != '' ? $ext : config('upload_file_ext');
        return [
            'type'        => 'file',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'size'        => $size,
            'ext'         => $ext,
            'extra_class' => $extra_class,
        ];
	}

	/**
	 * @var array 需要加载的js
	 */
	public $js = [
		// '__ADMIN_JS__/core/jquery.min.js',
		'__LIBS__/webuploader/webuploader.js',
		'init.js'
	];

	/**
	 * @var array 需要加载的css
	 */
	public $css = [
		'__LIBS__/webuploader/webuploader.css',
	];
}
