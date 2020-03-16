<?php
namespace form\hr;

class Builder
{
    /**
     * 取色器
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param string $extra_class extra_class
     * @param string $param 额外参数
     * @author yangweijie <917647288@qq.com>
     * @return mixed
     */
    public function item($name = '', $title = '', $tips = '', $options = [], $default = [], $extra_class = '')
    {
        return [
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'options'     => $options,
            'extra_class' => $extra_class,
        ];
    }

    /**
     * @var array 需要加载的js
     */
    public $js = [

    ];

    /**
     * @var array 需要加载的css
     */
    public $css = [

    ];
}
