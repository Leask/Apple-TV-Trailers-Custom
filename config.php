<?php
date_default_timezone_set('Asia/Shanghai');


define('ROOT',dirname(__FILE__)."/");
if($_SERVER['HTTP_HOST']=='192.168.1.106'){
	//本地测试
	define('APPLETV','http://192.168.1.106/appletv/');
	define('IMG',APPLETV.'i/');
	define('CACHE',ROOT."cache/");
}else{
	//请在这里配置你的SAE
	define('APPLETV','http://***.sinaapp.com/');
	define('IMG','http://appletv.googlecode.com/svn/');
	define('CACHE',"");
	define('st_domain',"");
}
define('INDEX',APPLETV.'index.php?');
define('ICO',IMG.'ico/');
define("MENU",IMG.'menu/');


function __autoload($class_name) {
	$class_path = ROOT.str_replace('_','.',$class_name).".php";
	if(file_exists($class_path)){
		return require_once($class_path);
	}else{
		//die($class_path." is not defined;");
		return false;
	}
}
