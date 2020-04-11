<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 广东卓锐软件有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------

/**
 * 菜单信息
 */
return [
  [
    'title' => '队列',
    'icon' => '',
    'url_type' => 'module_admin',
    'url_value' => 'queue/index/index',
    'url_target' => '_self',
    'online_hide' => 0,
    'sort' => 100,
    'status' => 0,
    'child' => [
      [
        'title' => '任务列表',
        'icon' => '',
        'url_type' => 'module_admin',
        'url_value' => 'queue/index/index',
        'url_target' => '_self',
        'online_hide' => 0,
        'sort' => 100,
        'status' => 1,
        'child' => [
          [
            'title' => '编辑',
            'icon' => '',
            'url_type' => 'module_admin',
            'url_value' => 'queue/index/edit',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
          ],
          [
            'title' => '删除',
            'icon' => '',
            'url_type' => 'module_admin',
            'url_value' => 'queue/index/delete',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
          ],
          [
            'title' => '启用',
            'icon' => '',
            'url_type' => 'module_admin',
            'url_value' => 'queue/index/enable',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
          ],
          [
            'title' => '禁用',
            'icon' => '',
            'url_type' => 'module_admin',
            'url_value' => 'queue/index/disable',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
          ],
          [
            'title' => '快速编辑',
            'icon' => '',
            'url_type' => 'module_admin',
            'url_value' => 'queue/index/quickedit',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
          ],
          [
            'title' => '重做任务',
            'icon' => '',
            'url_type' => 'module_admin',
            'url_value' => 'queue/index/redo',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
          ],
        ],
      ],
    ],
  ],
];
