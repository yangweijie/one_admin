<?php
declare (strict_types = 1);

namespace app\index\home;

use app\index\model\Test;

use think\facade\Db;

class Index extends Home
{
    public function index()
    {
        // 默认跳转模块
        if (config('app.home_default_module') != '' && config('app.home_default_module') != 'index') {
            $this->redirect(config('app.home_default_module'). '/index/index');
        }

        $start = microtime(true);

        Db::startTrans();
        try {
            $data   = [];
            $arr    = [];
            for ($i = 0; $i < 250*50; $i++) { 
                $arr[] = 'aaaaaaaaaa';
            }
            $data = [
                'title'   => 'aaa',
                'content' => implode(',', $arr),
            ];
            Db::connect('sqlite')
                ->table('test')
                // ->limit(100)
                ->insert($data);

                // ->limit(100)
                // ->insertAll($data);
            // $testModel->saveAll($data);
            // 提交事务
            Db::commit();
            $end = microtime(true);

            $time=$end-$start;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }

        return sprintf('耗时%s 秒', number_format($time, 10, '.', ''));
        // return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> '.config("dolphin.product_name").' '.config("dolphin.product_version").'<br/><span style="font-size:30px">极速 · 极简 · 极致</span></p></div>';
    }
}
