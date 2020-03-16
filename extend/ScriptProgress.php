<?php

/**
 * @author Thomas Sch锟絝er <thomas.schaefer@query4u.de>
 * Class for creating a simple server-based progress indicator that
 * works in all browsers (yes, it supports google's chrome, too). Useful for many different applications
 * where you want to show a status of a server-side operation. No ajax needed. Runs in all major browsers
 * (tested with ff, chrome, safari, opera).
 * use cases:
 * - file upload
 * - any application running within iframe
 *
 * @example
 * $buff = new ScriptProgress();
 * do{
 *   if($buff->getIteration() < $buff->getMax()){
 *     $buff->next();
 *   } else {
 *     echo sprintf("<pre>%s</pre>", print_r($_SERVER,true));
 *     break;
 *   }
 *
 * }while(true);
 */
class ScriptProgress
{

    protected $_interval = 150000; // 0.150 sec
    protected $_inc = 0;
    protected $_steps;
    protected $_progess_indicator = ".";
    protected $_browser;

    /**
     * @param integer $steps max iterations
     * @param integer $interval interval wait state
     * @param string $_progess_indicator
     */
    public function __construct($steps = 10, $interval = 150000, $_progess_indicator = '.')
    {
        $this->_steps = $steps;
        $this->_interval = $interval;
        $this->_progess_indicator = $_progess_indicator;
        // header('X-Accel-Buffering: no');
        // ignore_user_abort(true); // run script in background
        set_time_limit(0); // run script forever
        $this->_quirkMode();

        ob_start();
    }

    public function addStylesheet($name = "style")
    {
        echo sprintf('<link href="%s.css" type="text/css" media="screen, projection" rel="stylesheet"/>', $name);
        if (stristr($_SERVER["HTTP_USER_AGENT"], 'MSIE 9')) {
            echo '<!--[if gte IE 9]><style type="text/css">div{filter: none;}</style><![endif]-->';
        }
        return $this;
    }

    public function addScript($name)
    {
        echo sprintf('<script type="text/javascript" src="%s"></script>', $name);
        return $this;
    }

    /**
     * inject piece of code to override the indicator string
     * @param type $value
     * @return \ScriptProgress
     */
    public function set($value)
    {
        $this->_progess_indicator = $value;
        return $this;
    }

    public function js($code)
    {
        return sprintf('<script type="text/javascript">%s</script>', $code);
    }

    // 执行js
    public function runjs($code){
        echo $this->js($code);
        return $this;
    }

    // bootstrap里提示
    public function bs_notify($msg, $type ='up'){
        $dom_action = $type == 'up'? 'prepend':'append';
        $code = <<<JS
$('.alert-info').{$dom_action}('<p>{$msg}</p>');
JS;
        echo $this->js($code);
        return $this;
    }

    // bootstrap 里 新增行
    public function bs_table_tr($tr, $type){
        $dom_action = $type == 'up'? 'prepend':'append';
        $code = <<<JS
$('.table-builder').{$dom_action}('{$tr}');
JS;
        echo $this->js($code);
        return $this;
    }

    public function notify($msg)
    {
        $code = <<<JS
var dl = document.getElementById('notify');
var dt = document.createElement('dt');
var ddt = document.createTextNode('{$msg}');
dt.appendChild(ddt);
dl.insertBefore(dt,dl.childNodes[0]);
JS;
        echo $this->js($code);
        return $this;
    }

    public function notify2($msg)
    {
        $code = <<<JS
var dl = document.getElementById('notify2');
var div = document.createElement('div');
div.innerHTML = '{$msg}';
dl.insertBefore(div,dl.childNodes[0]);
JS;
        echo $this->js($code);
        return $this;
    }

	// 插入格式化内容
	public function notify_pre($msg){
		$msg = str_replace(PHP_EOL, '\n', $msg);
		$code = <<<JS
var dl = document.getElementById('notify2');
var div = document.createElement('div');
var pre = document.createElement('pre');
pre.innerHTML = "{$msg}";
div.appendChild(pre);
dl.insertBefore(div,dl.childNodes[0]);
JS;
        echo $this->js($code);
        return $this;
	}

    /**
     * write to browser console
     * @param type $string
     */
    public function console($string, $type = 'string')
    {
    	if($string){
    		$tmpl = '<script>console.log("%s")</script>';
    	}else{
    		$tmpl = '<script>console.log(%s)</script>';
    	}
        $this->_progess_indicator = sprintf($tmpl, addslashes($string));
        return $this;
    }

    /**
     * get max iterations
     * @return int
     */
    public function getMax()
    {
        return $this->_steps;
    }

    public function getIteration()
    {
        return $this->_inc;
    }

    /**
     * increase iterations
     * @param int $inc
     */
    public function increase($inc = 1)
    {
        $this->_inc += (int) $inc;
        return $this;
    }

    /**
     * decrease iterations
     * @param int $inc
     */
    public function decrease($inc = 1)
    {
        $this->_inc -= (int) $inc;
        return $this;
    }

    /**
     * next progress indicator
     */
    public function next()
    {
        $this->_buffer_flush();
        echo $this->_progess_indicator;
        $this->_buffer_flush();
        // usleep($this->_interval);
        $nano = time_nanosleep(0, 1);
        $this->_buffer_flush();
        $this->_inc++;
        return $this;
    }

    private function _quirkMode()
    {
        if (
            strpos($_SERVER["HTTP_USER_AGENT"], "Gecko") or
            strpos($_SERVER["HTTP_USER_AGENT"], "WebKit")
        ) {
            $this->_browser = 'gecko';
            echo '<?xml version="1.0" encoding="iso-8859-1"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        } elseif (
            strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")
        ) {

            $this->_browser = 'ie';
            echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">';
        } elseif (
            stristr($_SERVER["HTTP_USER_AGENT"], "Opera")
        ) {
            $this->_browser = 'opera';
            echo '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            # opera hack
            echo '<html><body>';
        }
    }

    public function show_time()
    {
        $time = datetime();
        return <<<HTML
<script>
document.getElementById('timer').innerHTML = '{$time}';
</script>
HTML;
    }

    /**
     * write some browser-related strings to fill up content header
     */
    private function _buffer_flush()
    {
        switch ($this->_browser) {
            default:
                echo str_repeat(' ', 1024 * 4);
                while (ob_get_level()) {
                    ob_end_flush();
                }

                if (ob_get_length()) {
                    @ob_flush();
                    @flush();
                    @ob_end_flush();
                }
                @ob_start();
                break;
        }
    }

}
