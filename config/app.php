<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 应用地址
    'app_host'         => Env::get('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 自动多应用模式
    'auto_multi_app'   => true,
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => true,

    // 后台公共模板
    'admin_base_layout'  => app_path() . 'admin/view/layout.html',

    // 插件目录路径
    'plugin_path' => '../extend/plugins/',

    // 数据包目录路径
    'packet_path'        => root_path(). 'packet/',
    // 文件上传路径
    'upload_path'        => root_path() . 'public' . DIRECTORY_SEPARATOR . 'uploads',
    // 文件上传临时目录
    'upload_temp_path'   => root_path() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp/',
];
