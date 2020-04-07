<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 模块信息
 */
return [
  'name' => 'crontab',
  'title' => '定时任务',
  'identifier' => 'crontab.wjq.module',
  'icon' => 'glyphicon glyphicon-time',
  'description' => '定时任务模块',
  'author' => '流风回雪',
  'author_url' => 'http://www.dolphinphp.com/',
  'version' => '1.0.0',
  'need_module' => [],
  'need_plugin' => [],
  'tables' => [
    'crontab',
    'crontab_log',
  ],
  'database_prefix' => 'msx_',
  'config' => [],
  'action' => [],
  'access' => [],
];
