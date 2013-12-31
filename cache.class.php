<?php
/*
 * Object	缓存XML数据
 * Author	Missde,陈州府
 * link	http://weibo.com/missde
 * mTime	2012-09-05 19:29
 *
**/

//	header("Content-Type: text/xml");


class cache_class{
	//读取的时候指定过期时间
	function __construct(){
		$this->url();
	}

	function url($u=""){
		$url=$u;
		//if(!$url)$url=@$_SERVER['HTTP_X_REWRITE_URL'];
		if(!$url)$url="http://".$_SERVER['HTTP_HOST'].@$_SERVER['REQUEST_URI'];
		$url=preg_replace("/\&amp;/is",'&',$url);
		$url=$url?md5(strtolower($url)."missde").".xml":"";

		preg_match("/\.(\w+)$/",$url,$B);
		$path=@$B[1]?strtolower($B[1])."/":"";
		$url=$path.substr($url,0,3).'/'.substr($url,3);
		if($u){
			return $url;
		}else{
			$this->url=$url;
		}
	}


	function read($t=3600,$file="",$folder=""){
		if(!$file)$file=$this->cacheUrl;
		return $this->io($t,$file,$folder);
	}

	function del($file,$folder=""){
		if(class_exists("SaeStorage")){
			$s = new SaeStorage();
			@$s->delete(st_domain,$file);
			unset($s);
		}elseif(class_exists("SaeKV")){
			$kv = new SaeKV();
			$kv->init();
			@$kv->delete($file);
		}else{
			@unlink($folder.$file);
		}
		//die("ff");
	}


	//Sina KVDB缓存--据说比较消耗新浪豆，弃用
	function kv($value,$tag=""){//,$o=""
		if(!$tag)$tag=$this->cacheUrl;
		$kv = new SaeKV();
		$kv->init();
		if(is_numeric($value)){
			if($r=$kv->get($tag)){
				if(time()-$r['t']<=$value)return $r['v'];
			}
			return false;
		}else{
			$x=array("t"=>time(),"v"=>$value);//,'o'=>$o
			$kv->set($tag,$x);
		}
	}

	//Sina SaeStorage文件缓存
	/*
		写//st('hellow saestorage ok!','001.php','play','i');
		读//echo st(88,"001.php","play","i");
	*/
	function st($value,$domain,$file){
		//官方设置有防盗链，关闭才可以使用
		$s = new SaeStorage();
		if(is_numeric($value)){
			if($s->fileExists($domain,$file)){
				$x=$s->getAttr($domain,$file);
				if($value==-1 || time()-$x['datetime']<=$value){
					if(stripos($file,'.php')>1){
						return include $s->getUrl($domain,$file);
					}
					if(stripos($file,'.xml')>1)header("Content-Type: text/xml");
					return $s->read($domain,$file);
					//if($x=$s->read($domain,$file))return unserialize($x);
				}
			}
		}else{
			//$value=serialize($value);
			if(is_array($value)){
				$value=var_export($value,true);
				$value="<"."?php\n return ".$value.';';
			}
			return $s->write($domain,$file,$value);
		}
	}

	//单机时的文件缓存
	function io($value,$file){
		if(is_numeric($value)){
			if($value==-1 || (time()-@filemtime($file))<=$value){
				if($x=@file_get_contents($file)){
					$y=stripos($x,"<?php");
					if($y!==false && $y<5){
						return include @$file;
					}else{
						if(stripos($file,'.xml')>1)header("Content-Type: text/xml");
						return $x;
					}
				}
				//return include $file;

			}
		}else{
			//检测目录
			$x=preg_split('/\\\|\\//',$file);
			$folder="";
			for($i=0;$i<count($x)-1;$i++){
				if($f=$x[$i]){
					$folder.=$f."/";
					if(!is_dir($folder))@mkdir($folder,0777);
				}
			}

			if(is_array($value)){
				$value=var_export($value,true);
				return file_put_contents($file,'<?php return '.$value.';');
			}else{
				return file_put_contents($file,$value);
			}
		}
	}

}