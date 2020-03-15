<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [

        ],
        'HttpRun'  => [
        	'app\\common\\listener\\Config',
        	'app\\common\\listener\\Hook',
        ],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
    ],
];
