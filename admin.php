<?php
include "config.php";
switch(@$_GET['m']){
	case "cache":
		cache();
		break;
	default:
}

function md5C($file){
	$file=md5(strtolower($file)."missdeAppleTV").".xml";

	$file='/xml/'.substr($file,0,3).'/'.substr($file,3);
	return $file;

}
function cache(){
	global $html;
	$pages=array();
	$pages['menu.php']="menu.php";
	$pages['首页']=md5C(str_replace('?','',INDEX));
	$pages['直播']=md5C(INDEX."m=webtv");
	$menu=include "menu.php";
    	foreach($menu['zhibo'] as $k=>$x)$pages['直播-'.$k]=md5C(INDEX."m=webtv&a=".urlencode($k));

	$pages['奇艺-首页']=md5C(INDEX."m=site&s=qiyi");
	$pages['hot_qiyi.php']="hot_qiyi.php";
	$menu=array(
		'搜索'=>INDEX."m=search&amp;s=qiyi",
		"电影"=>"http://list.iqiyi.com/www/1/------------2-1-1-1---.html",
		"电视剧"=>"http://list.iqiyi.com/www/2/------------2-1-1-1---.html",
		"微电影"=>"http://list.iqiyi.com/www/16/------------2-1-1-1---.html",
		"动漫"=>"http://list.iqiyi.com/www/4/------------2-1-1-1---.html",
		"综艺"=>"http://list.iqiyi.com/www/6/------------2-1-1-1---.html",
		"纪录片"=>"http://list.iqiyi.com/www/3/----------0--2-1-1-1---.html",
		"音乐"=>"http://list.iqiyi.com/www/5/------------2-1-1-1---.html",
		"娱乐"=>"http://list.iqiyi.com/www/7/------------2-1-1-1---.html",
		"旅游"=>"http://list.iqiyi.com/www/9/------------2-1-1-1---.html",
		"时尚"=>"http://list.iqiyi.com/www/13/------------2-1-1-1---.html",
		"片花"=>"http://list.iqiyi.com/www/10/1007------------2-1-1-2---.html"
	);
  //	foreach($menu as $k=>$x)$pages['奇艺-'.$k]=md5C(INDEX."m=lispage&s=qiyi&u=".urlencode($x));

	$pages['音悦台-首页']=md5C(INDEX."m=site&s=yinyuetai");
	$menu=array(
		//'搜索'=>INDEX."m=search&amp;s=yinyuetai",
		"流行"=>"http://www.yinyuetai.com/mv/all?genre=1",
		"民谣"=>"http://www.yinyuetai.com/mv/all?genre=2",
		"蓝调"=>"http://www.yinyuetai.com/mv/all?genre=3",
		"古典"=>"http://www.yinyuetai.com/mv/all?genre=4",
		"乡村"=>"http://www.yinyuetai.com/mv/all?genre=5",
		"舞曲"=>"http://www.yinyuetai.com/mv/all?genre=6",
		"嘻哈"=>"http://www.yinyuetai.com/mv/all?genre=8",
		"爵士"=>"http://www.yinyuetai.com/mv/all?genre=10",
		"轻音乐"=>"http://www.yinyuetai.com/mv/all?genre=23",
		"电影原声"=>"http://www.yinyuetai.com/mv/all?genre=16",
		"世界音乐"=>"http://www.yinyuetai.com/mv/all?genre=17",
		"环境音乐"=>"http://www.yinyuetai.com/mv/all?genre=18",
		"搞笑"=>"http://www.yinyuetai.com/mv/all?genre=25",
		"儿童音乐"=>"http://www.yinyuetai.com/mv/all?genre=26",
		"中国风"=>"http://www.yinyuetai.com/mv/all?genre=28",
		"民族风"=>"http://www.yinyuetai.com/mv/all?genre=34",
		"内地"=>"http://www.yinyuetai.com/mv/all?area=ML",
		"港台"=>"http://www.yinyuetai.com/mv/all?area=HT",
		"欧美"=>"http://www.yinyuetai.com/mv/all?area=US",
		"韩语"=>"http://www.yinyuetai.com/mv/all?area=KR",
		"日语"=>"http://www.yinyuetai.com/mv/all?area=JP",
		"其它"=>"http://www.yinyuetai.com/mv/all?area=Other",
		"美女"=>"http://www.yinyuetai.com/mv/all?artist=Girl",
		"帅哥"=>"http://www.yinyuetai.com/mv/all?artist=Boy",
		"乐队"=>"http://www.yinyuetai.com/mv/all?artist=Combo",
		"官方版"=>"http://www.yinyuetai.com/mv/all?version=music_video",
		"演唱会"=>"http://www.yinyuetai.com/mv/all?version=concert",
		"现场版"=>"http://www.yinyuetai.com/mv/all?version=live",
		"字幕版"=>"http://www.yinyuetai.com/mv/all?version=subtitle",
		"热舞"=>"http://www.yinyuetai.com/mv/all?tag=%E7%83%AD%E8%88%9E",
		"超清版"=>"http://www.yinyuetai.com/mv/all?tag=%E8%B6%85%E6%B8%85",
		"高清版"=>"http://www.yinyuetai.com/mv/all?tag=%E9%AB%98%E6%B8%85",
	);
  //foreach($menu as $k=>$x)$pages['音悦台-'.$k]=md5C(INDEX."m=lispage&s=qiyi&u=".urlencode($x));



	$s = @new SaeStorage();

	$html="<ul class='cache'>";
	foreach($pages as $n=>$url){
		$del=@$s->delete("i",$url)?" del！":"";
		//$del=@unlink(CACHE.$url)?" del！":"";
		$html.="<li><a href='".CACHE.$url."' target='_blank'>".$n."__".$url."</a> <font color=red>$del</font>";
	}
	$html.="</ul>";

	unset($s);
}

?>
<!Doctype html>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<meta http-equiv=Content-Type content="text/html;charset=utf-8">
<style type="text/css">
body{font-size:14px;}
ul.cache{}
ul.cache li{height:22px;line-height:22px;}
ul.cache li a{text-decoration:none;}
</style>
</head>
<body>
<a href="?m=cache">清理缓存</a>
<?php

echo @$html;