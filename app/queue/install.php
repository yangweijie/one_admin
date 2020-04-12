<?php
if(!class_exists('\think\admin\service\QueueService')){
	$this->error('请先安装think-library 6.0.x-dev');
}

util\File::copy_dir(base_path().'queue/install_files/queue', './static/queue');