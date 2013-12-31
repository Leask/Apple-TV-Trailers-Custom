<?php
/*
 * Object	模拟Apple TV自带网站，默认类
 * Author	Missde,陈州府
 * link	http://weibo.com/missde
 * mTime	2012-09-02 23:44
 *
**/

class ini_class{
	function __construct(){
		$this->xml=new xml_class();
		$this->cache=new cache_class();
		$this->g=(object)$_GET;
		//$this->cache->url();
	}

	function __destruct(){
	}

	protected function alert($title,$desc="",$box=""){
		if(!$desc){
			$desc=$title;
			$title="Error";
		}
		if($box=="search"){
			$A=$this->xml->searchShow(array(array('title'=>$title?$title:' V_V  Sorry...','desc'=>$desc?$desc:'未找到相关节目，有问题请@missde...')));
		}elseif($box=="tabRight"){
			$A=$this->xml->tabRight(array(array("url"=>'',"name"=>'出错了或无内容',"img"=>'')));
		}else{
			$box="alert";
			$A=Array($title,$desc);
		}
		$A=$this->xml->show($A,$box);
		echo $A[1];exit();
	}
	function get($str){
		return isset($_GET[$str])?$_GET[$str]:"";
	}
	function get_caiji_class($x){
		$site="caiji_".$x."_class";
		if(!file_exists("caiji.".$x.".class.php"))return false;
		return new $site();
	}
	function read_menu(){
		return $this->cache(-1,"menu.php");
	}

	function missde(){
		$m=@$this->g->m;
		if($m=="zhibo"){
			$this->cache->url=$this->cache->url(INDEX);
		}elseif($m=="webtv"){
			$this->getCache(86400);
			$A=$this->read_menu();
			$A=$A['zhibo'];
			if($a=@$this->g->a){
				$a=urldecode($a);
				if(isset($A[$a])){
					$A=$A[$a];
					foreach($A as $k=>$r)$A[$k]['url']=$this->url($r['url']);
					return $this->tabRight($A);
				}
			}
			$menu=array();
			foreach($A as $n=>$u)$menu['电视直播'][$n]=INDEX."m=webtv&amp;a=".urlencode($n);
			$this->tabLeft($menu);
			exit();
		}elseif($m=="search"){
			$this->getCache();
			$this->search();
			exit();
		}elseif($m=="play"){
			$this->look($this->g->u,"play");
			exit();
		}elseif(@$this->g->s){
			if($m=="site")$m="index";
			if($site=$this->get_caiji_class($this->g->s)){
				if(method_exists($site,$m))return $site->$m();
			}
			$this->alert('参数错误');
			exit();
		}else{
		}

		//主界面页
		$this->getCache(86400);
		$this->shouye('','',1,0);

		exit();
	}

	function shouye($A="",$M="",$br=1,$showname=1){
		//主界面。缓存2小时
		if(!$A){
			//加载默认菜单
			$menu=include "menu.php";
			$A=&$menu['index'];
			//合并网站HOT,并判断是否有搜索功能
			$search=array();
			$hot=array();
			foreach($A as $a=>$b){
				foreach($b as $d=>$c){
					$A[$a][$d]['name']="";
					if($s=@$c['search']){
						$h=$this->cache(-1,'hot_'.$s.'.php');
						if(is_array($h))$hot=array_merge($hot,$h);
						$search[$s]=$c['name'];
					}
				}
			}
			$A=array_merge(array("推荐|5|0|1"=>$hot),$A);
			$menu['search']=$search;
			//--
			$this->cache($menu,"menu.php");
                        $delIndex=0;
		}else{
			$delIndex=1;
		}
		$menu=$M?$this->menu($M):"";
		$xml=$this->xml->DT($A,$br,$showname);
		$this->look($menu.$xml,"index");

		//重新生成采集网站时，刷新首页
		if($delIndex)$this->refreshIndex();
	}
	function refreshIndex(){
		//删除首页缓存
		$this->cache->del($this->cache->url(str_replace('?','',INDEX)),CACHE);
	}
	function lis($M="",$A="",$P="",$N=5,$catetime=86400){
		$xml="";
		if($M)$xml.=$this->menu($M);
		if($A)$xml.=$this->xml->lis($A,$N);
		if($P)$xml.=$this->xml->page($P);
		$this->look($xml,"lis");
	}
	function DT($A,$br=1,$showname=1){
		$xml=$this->xml->DT($A,$br,$showname);
		$this->look($xml,"lis");
	}

	function tabLeft($A){
		$xml=$this->xml->tabLeft($A);
		$this->look($xml,"tabLeft");
	}
	function tabRight($A){
		$xml=$this->xml->tabRight($A);
		$this->look($xml,"tabRight");
	}


	//格式化菜单按钮并生成菜单图片
	function menu($A){
		foreach($A as $name=>$r){
			$title=preg_replace("/^_/i","",$name);
			if(is_array($r)){
				if(!isset($r['title']))$r['title']=$title;
				if(!isset($r['url']))$r['url']="";
				if(!isset($r['img']))$r['img']=$this->cImg($title,'menu');
			}else{
				$r=array("title"=>$title,"url"=>$r,"img"=>$this->cImg($title,'menu'));
			}
			$A[$name]=$r;
		}
		return $this->xml->menu($A);
	}
	function search(){
		if(!@$key)$key=@$this->g->key;
		if($key){
			$key=strtolower($key);
			if(strlen($key)<2)return $this->alert('提示：请继续输入....',"反馈问题请到新浪微博 @Missde ","search");
			if($key=="missde")return $this->alert('Yes! I\'m Missde.',"http://weibo.com/missde","search");
		}
		$webname="";
		$x=false;
		if($s=$this->g->s){
			if($x=$this->get_caiji_class($s)){
				if(method_exists($x,"search")){
					$webname=$x->name;
				}else{
					$x=false;
				}
			}
		}
		if($key){
			$lis=array();
			/*
			$key=urlencode($key);
			$baidukey=$this->curl("http://suggestion.baidu.com/su?wd=".$key);
			$baidukey=@iconv("gbk","utf-8//IGNORE",$baidukey);
			preg_match('/s\:\["(.[^\"]*)\"/is',$baidukey,$B);
			if(@$B[1])$key=urlencode($B[1]);
			*/

			if($x){
				$lis=$x->search($key);
			}else{
				$A=$this->read_menu();
				//全列出，每个网站最多列出10条
				foreach($A['search'] as $n=>$r){
					$x=$this->get_caiji_class($n);
					$y=$x->search($key,1);
					if(is_array($y))$lis=array_merge($lis,$y);
				}
			}
			$xml=$this->xml->searchShow($lis);
			$this->look($xml,"search");
		}else{
			$this->look($this->xml->searchFrame(@$this->g->s,$webname),"xml");
		}
	}
	function look($A,$box=0){
		if(!$A)return $this->alert("获取数据失败！","",$box);

		$xml=$this->xml->show($A,$box);
		echo $xml[1];
		if($xml[0]==0 && $this->cache->url)$this->cache($xml[1]);//写缓存
	}

	function go($url){
		//跳转页面永久缓存，用cache/play目录。
		$url=preg_replace('/&amp;/is','&',$url);
		@header('Location: '.$url);
		$this->cache("<"."?php @header(\"Location: ".$url."\");");
		exit();
	}

	function boxCache($A,$box,$url){
		$xml=$this->xml->show($A,$box);
		if($xml[0]==0)return $this->cache($xml[1],$url);
		return false;

	}

	function getCache($t=36000){
		if($x=$this->cache($t,"","")){
			print_r($x);
			exit();
		}
	}
	function cache($xml,$url="",$folder=""){
		//$xml是数字时，为读取缓存
		if(!$url)$url=$this->cache->url;

		//下面是新浪的SaeStorage
		if(class_exists("SaeStorage"))return $this->cache->st($xml,@st_domain,$url);

		//下面是读取 sina KVDB缓存 - 比较消耗新浪云豆
                //is_object("SaeKV")
        	///if(@class_exists("SaeKV"))return $this->cache->kv($xml,$url);
		//下面是单机文件缓存
		return $this->cache->io($xml,CACHE.$url);

	}

	//缓存热门
	function cacheHot($A=""){
		if(!($m=$this->getCallClass(2)))return die("err");
		if(!is_array($A))$A=array();
		$this->cache($A,"hot_".$m.".php");
	}

	function cfile($file,$msg){
		return @file_put_contents($file,$msg);
	}

	function url($url,$act="play",$site="",$args=array()){
		$site=$this->getCallClass(2);
		if(stripos($url,APPLETV)===0)return $url;
		$url=urlencode($url);
		if(preg_replace("/\.(ts|m3u8|mov|mp4|rmvb|avi|mkv)$/is","",$url)!=$url){
			return INDEX."m=play&amp;u=".$url;
		}else{
			$path=INDEX."m=".$act;
			if($site)$path.="&amp;s=".$site;
			if($url)$path.="&amp;u=".$url;
			foreach($args as $k=>$v){
				if(is_numeric($k))continue;
				$path.="&amp;".$k."=".$v;
			}
			return $path;
		}
	}

	function substring($msg,$a,$b="",$c=0){
		$i=$a==""?0:intval(stripos($msg,$a));
		if($i<1)return "";
		if($c<1)$i+=strlen($a);
		$msg=substr($msg,$i);

		$j=$b==""?strlen($msg):stripos($msg,$b);
		$msg=substr($msg,0,$j);
		return $msg;
	}
	function curl($url){
		//Sina SAE
		if(is_object("SaeFetchurl")){
			$f = new SaeFetchurl();
			$content = $f->fetch($url);
			return $f->errno()==0?$content:$f->errmsg();
		}

		//普通
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		if(function_exists("curl_easy_setopt"))curl_easy_setopt(curl_handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		$msg = curl_errno($ch)?curl_errno($ch):curl_exec($ch);
		curl_close($ch);
		return $msg;
	}
	//动态创建图片
	function cImg($name="",$s="menu"){
		$file=urlencode(trim($name));
		$file=preg_replace("/\W/is","",$file);

		$path=$s."/".$file.".gif";
		$url=IMG.$path;

		//sina sae不生成图片--图片改道放在 google code.
		if(class_exists("SaeStorage")){
			return $url;
			/*
			$s = new SaeStorage();
			if($s->fileExists(st_domain,$path))return $url;
			return "";
			*/
		}

		$path=ROOT.'i/'.$path;
		if(@is_file($path)) return $url;

		$set=array(
			"page"=>array("fsize"=>20,"length"=>100,"top"=>40,"left"=>5,"cnwidth"=>20,"enwidth"=>17),
			"menu"=>array("fsize"=>18,"length"=>100,"top"=>60,"left"=>5,"cnwidth"=>18,"enwidth"=>15)
		);
		$bg=ROOT."i/bg_".$s.".gif";


		$fontFile="i/dahei.ttf";

		$img_im=imagecreatefromgif($bg);
		$fontcolor = imagecolorallocate( $img_im, 255,255,255);


		$len=$set[$s]["left"];
		for($i=0;$i<strlen($name);$i++){
			$text=$name[$i];
			if(ord($name[$i])>128){
				if($i>0)$len+=$set[$s]['cnwidth']+($f=="cn"?10:0);
				$text.=$name[++$i].$name[++$i];
				$f="cn";
			}else{
				if($i>0)$len+=$set[$s]['enwidth']+($f=="cn"?12:0);
				$f="en";
			}
		}

		$x=max(($set[$s]["length"]-$len)/2-5,3);


		imagettftext( $img_im, $set[$s]['fsize'], 0,$x, $set[$s]["top"], $fontcolor, $fontFile,$name);
		imagegif($img_im,$path);
		imagedestroy($img_im);

		return $url;
	}

	//根据debug获取当前采集的网站
	private function getCallClass($i){
		$m=debug_backtrace();
		preg_match("/caiji_(\w+)_class/i",@$m[$i]['class'],$B);
		return @$B[1];
	}
}