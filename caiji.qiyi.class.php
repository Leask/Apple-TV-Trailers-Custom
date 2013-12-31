<?php
/*
 * Object	采集奇艺视频路径并转换成Apple TV要求的XML
 * Author	Missde,陈州府
 * link	http://weibo.com/missde
 * mTime	2012-09-02 23:44
 *
**/


class caiji_qiyi_class extends ini_class{
	function __construct(){
		parent::__construct();
		$this->name="奇艺";
		$this->www="http://www.iqiyi.com/";
		$this->ico=ICO."qiyi.gif";
		$this->menu=array(
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
		//在导航中加入搜索按钮---后期好改为全自动的
		//if(method_exists($this,"search"))$this->menu=array_merge(array('搜索'=>INDEX."m=search&amp;s=qiyi"),$this->menu);
	}

	function fURL($u,$m){
		$args=array();
		if($m=="index"){
			$m="show";
			$args['check']=1;
		}
		return parent::url($u,$m,$args);
	}
	function index(){
		$this->getCache(86400);
		//--获取推荐视频，仅用于缓存
		$html=$this->curl("http://www.qiyi.com/ext/common/focus/js/index2012.js");
		$html=$this->substring($html,'{','',1);
		$html=@json_decode($html);
		$html=@$html->playlist;
		$lis=array();
		if(@is_array($html)){
			foreach($html as $r){
				preg_match('/http\:\/\/\w+\.iqiyi.*/',$r->url,$B);
				$url=preg_replace('/\.html\?.*/is',".html",@$B[0]);
				if($url)$lis[@$r->g_si_name]=array("img"=>@$r->bpic,"url"=>$this->fURL($url,'show'),"name"=>@$r->g_si_name);
			}
		}
		$this->cacheHot($lis);
		//--end

		$html=$this->curl($this->www);
		$A=array();
		$html=preg_replace("/\s|\r|\n|\t/is","",$html);
		$html=preg_replace("/<\/ul>/is","\n",$html);
		$html=preg_replace("/<h2/is","\n<h2",$html);
		preg_match_all('/<h2(.[^\n]*)/is',$html,$B);

		foreach($B[0] as $i=>$ul){
			if(stripos($ul,"<ul")===false)continue;
			$ul=preg_replace('/(id|class|title|j\-\w+)=\".[^\"]*\"|<a.[^\>]*><\/a>|width="\d+"|height="\d+"/is',"",$ul);
			preg_match_all('/<li><ahref="(.[^\"]*)"(.[^\>]*)?><img(alt="(.[^\"]*)")?data-lazy="(.[^\"]*)"(alt="(.[^\"]*)")?/is',$ul,$C);

			if(@$C[3]){
				preg_match('/>(\W+)</is',$ul,$D);
				$title=$D[1]."|8";
				$A[$title]=array();
				$baby=array();
				foreach($C[4] as $i=>$name){
					if($name=="")$name=$C[7][$i];
					$name=preg_replace("/更新至|更新/is","",$name);
					$A[$title][]=array("name"=>$name,"url"=>$this->fURL($C[1][$i],"index"),"img"=>$C[5][$i]);
				}
			}
		}
		$M=array();
		foreach($this->menu as $n=>$u)$M[$n]=$this->fURL($u,"lispage");
		parent::shouye($A,$M);
	}
	function lispage(){
		$this->getCache();
		$html=$this->curl($this->g->u);
		$menu=array();
		preg_match_all('/<li.[^\>]*><a(.[^\>]*)href="(.[^\"]*)"(.[^\>]*)>(.[^\<]*)<\/a><span><em>/is',$html,$B);
		foreach(@$B[2] as $i=>$u){
			$name=trim($B[4][$i]);
			if($name=="全部" || $name=='付费')continue;
			$u=$this->fURL($u,"lispage");
			$menu[$name]=$u;
		}
		$html=$this->substring($html,"<!--列表部分_START-->","<!--列表部分_END-->");
		$html=preg_replace("/\r|\n|\t/is","",$html);
		$lis=array();
		$pages=array();
		if($html){
			//列表
			preg_match_all('/class="imgBg1" href="(.[^\>]*)"> <img width="160"height="90" title=".[^\>]*"alt="(.[^\>]*)" src="(.[^\>]*)"/is',$html,$B);
			if(@$B[3])foreach($B[3] as $i=>$img)$lis[]=array("url"=>$this->fURL($B[1][$i],"show"),"name"=>$B[2][$i],"img"=>$img);

			//分页
			$html=$this->substring($html,"<!--分页 begin-->","<!--分页 end-->");
			/* 无当前页，不好。
			//preg_match_all('/href="(.[^\"]*)">(.[^\<]*)<\/a>/is',$html,$B);
			//foreach($B[2] as $i=>$p)$pages[$p]=$this->fURL($B[1][$i],'lispage');
			*/
			preg_match_all('/<(span|a) data-key="(\d+|dotdown)"(.[^\>]*)/is',$html,$B);
			foreach($B[2] as $i=>$num){
				$url=$this->substring($B[3][$i],'href="','"');
				$pages[is_numeric($num)?$num:"..."]=$this->fURL($url,'lispage');
			}
		}
		parent::lis($menu,$lis,$pages);
	}
	function show(){
		if($this->isVIP($this->g->u))parent::alert("不提供VIP视频播放，有问题请到新浪微博@missde ");
            	$this->getCache();
		//剧集详细、单剧列表

		$html=$this->curl($this->g->u);
		//$html=$this->curl("192.168.1.106/appletv/z.html");
		$html=preg_replace("/\s|\r|\n|\t/is","",$html);

		//播放页
		if(preg_replace('/\/\d+\/\w+\.html/is','',$this->g->u)!=$this->g->u){
			return $this->play(0);
		}elseif(stripos($this->g->u,".inc")>10){
			//INC列表
			return $this->gInc();
		}

		$title=$this->substring($html,'title>','</title>');
		if(stripos($html,"剧集列表")>100 || stripos($html,"节目列表")>100){
			/*
			preg_match('/<divid="j-album-more"style="display:none">(.[^\<]*)/is',$html,$B);
			$desc=@$B[1];
			*/
			$lis=array();
			//分页码
			$pages=array();
			preg_match_all('/<divid="j-album-[\w\-]+"style="display:none;">(.[^.]*\.inc)<\/div>/is',$html,$B);

			if(@$B[1][1]){
				//有多页。进入左右2栏格式：左侧页码，右侧剧集
				preg_match('/(title|tvName)\"?\:"(.[^\"]*)"/i',$html,$C);
				if($C[2])$title=$C[2];
				else $title=preg_replace('/\-.*/','',$title);
				foreach($B[1] as $i=>$u){
					$pages[$title]['第'.($i+1).'页']=$this->fURL($this->www.$u,'show');
				}
				return parent::tabLeft($pages);
			}elseif(stripos($html,"info.data.source")>100){
				//综艺节目，按年月分类
				preg_match('/info.data.source="(.[^\"]*)"/',$html,$B);
				if($source=@$B[1]){
					$source=urlencode($source);
					$u='http://search.video.qiyi.com/staticJsSource/?source='.$source;
																//&cb=videodatelist&category=urlencode(综艺)
					$html=$this->curl($u);
					$html=str_replace("var countBack =","",$html);
					if($countBack=@json_decode($html,true)){
						foreach(@$countBack['data'] as $y=>$ms){
							//&cb=programlist&sortKey=2&cur=0&limit=20&albumId=&category=%E7%BB%BC%E8%89%BA&domain=www
							rsort($ms);
							foreach($ms as $m)$pages[$B[1]][$y.'年'.$m."月"]=$this->fURL('http://search.video.qiyi.com/staticJsDateAlbum/?date='.$y.$m.'&source='.$source,'source');
						}
					}
				}
				if($pages)return parent::tabLeft($pages);
			}else{
				//只有1页，在列表页显示：上标题，下列表
				if(@$B[1][0]){
					//有列表的，去列表页读数据
					$lis=$this->gInc($this->www.$B[1][0]);
				}else{
					//无列表页的，从当前页面中分析
					preg_match_all('/<li><ahref="(http.[\w\-\.\:\/]+\/\d+\/\w+\.html)"(title="(.[^\"]*)?")?><imgsrc="(http.[\w\-\.\:\/]+\.jpg)"alt="(.[^\"]*)?"\/>(.[^\<]*)/is',$html,$B);
					if(@$B[5][1]){
						foreach($B[5] as $i=>$name){
							if($name=="")$name=$B[6][$i];;
							preg_match("/第\d+集/is",$name,$C);
							if(isset($C[0]))$name=$C[0];
							$lis[]=array("name"=>$name,"img"=>$B[4][$i],"url"=>$this->fURL($B[1][$i],'show'));
						}
					}
				}
				if($lis)return parent::DT(array($title=>$lis));
			}
		}
		$title2=$this->substring($html,'title:"','"');
		//许多奇艺的个性专题，在这里解析。未必能全部解析出来。
		$lis=array();
		$html=preg_replace('/<\/li>/is',"\r",$html);
		preg_match_all('/<li>.[^\r]*/is',$html,$B);
		foreach(@$B[0] as $li){
			preg_match("/http.[\w\-\.\:\/]+\/\d+\/\w+\.html/is",$li,$C);
			if(!@$C[0])continue;
			$url=$this->fURL($C[0],'show');

			$name=$this->substring($li,'title="','"');
			if($name=="")$name=$this->substring($li,'alt="','"');
			$name=str_replace($title2,'',$name);

			preg_match("/http.[\w\-\.\:\/]+\.jpg/is",$li,$C);
			$img=@$C[0];
			if($img || $name)$lis[]=array("name"=>$name,"url"=>$url,"img"=>$img,'li'=>$li);
		}
//print_r($lis);exit();
		parent::DT(array($title=>$lis));
	}

	function play($cache=1){
		if($cache)$this->getCache(-1);
		$html=$this->curl($this->g->u);
		$html=preg_replace('/":/is',":",$html);

		if(isset($this->g->check) && stripos($html,'j-tab')>30){
			//return $this->lisFromPlay($html);
			//列表页直接进入播放页时，重回到列表页
			preg_match('/<strong><a href="(.[^\"]*)">.[^\<]*<\/a><\/strong>/is',$html,$B);
			if(@$B[1]){
				$url=$this->fURL($B[1],"show");
				$this->go($url);
			}
		}

		$A=array();
		preg_match('/videoId:"(\w+)"/is',$html,$B);
		$videoId=@$B[1];
		if($videoId){
			preg_match('/title:"(.[^\"]*)"/is',$html,$B);
			$title=@$B[1];

			preg_match('/videoDesc:"(.[^\"]*)"/is',$html,$B);
			$videoDesc=@$B[1];

			preg_match('/playLength:"(.[^\"]*)"/is',$html,$B);
			$playLength=@$B[1];

			$html=$this->curl("http://cache.video.qiyi.com/m/".$videoId."/");
			$ipadUrl=preg_replace("/^.[^\{]*/is",'',$html);
			if($ipadUrl=@json_decode($ipadUrl,true)){
				$url=@$ipadUrl['data']['url'];
				if(!$url)$url=@$ipadUrl['data']['mp4Url'];
				if($url){
					$A=array(
						"url"=>$url,
						"title"=>$title,
						"desc"=>$videoDesc." ".$playLength
					);
				}
			}
		}
		parent::look($A,"play");
	}
	function source(){
		$html=$this->curl($this->g->u);
		$html=str_replace("var searchBack =","",$html);

		$lis=array();
		if($searchBack=@json_decode($html,true)){
			foreach($searchBack['data']['list'] as $r){
				//[VrsVideoTv.tvAlias] => 非诚勿扰20120908
				if(!($url=@$r['TvApplication.purl']))continue;
				$lis[]=array("name"=>str_replace(@$r['VrsFieldValue.fieldValue'],'',@$r['VrsVideoTv.tvAlias']).@$r['VrsVideoTv.tvName'],"url"=>$this->fURL($url,'show'),"img"=>@$r['broadImg']);
			}
		}
		if($lis)parent::tabRight($lis);
	}
	function lisFromPlay($html){
		//在播放页中获取播放列表
		if(strlen($html)<100){
			$html=$this->curl($html);
			$html=preg_replace("/\":/is",":");
		}
		preg_match("/albumName:\"(.[^\"]*)\"/i",$html,$B);
		$title=@$B[1];

		preg_match("/albumId:\"(.[^\"]*)\"/i",$html,$B);
		$albumId=@$B[1];

		preg_match("/pid:\"(.[^\"]*)\"/i",$html,$B);
		$pid=@$B[1];

		preg_match("/ptype:\"(.[^\"]*)\"/i",$html,$B);
		$ptype=@$B[1];

		//列表
		$url="http://cache.video.qiyi.com/avlist/".$albumId."/".$pid."/".$ptype;
		preg_match("/j-total\" type=\"hidden\" value=\"(\d+)\"/i",$html,$B);
		$total=intval(@$B[1]);
		$pnum=ceil($total/75);

		$title="《".$title."》 共".count($B)."页";
		$pages=array();
		$pages[$title]=Array();
		for($i=1;$i<=$pnum;$i++){
			$pages[$title]["第".$i."页"]=$this->fURL($url."/".$i."/","show");
		}
	}

	function search($key,$s=0){
		//Modify by Missde 2012-09-17 12:04
		$key=urlencode($key);
		$A=array();
		$name="";
		$num=$s?"10":"20";
		//搜关键词提示
		if($lis=$this->searchJSON($key,$num)){
			foreach($lis as $l){
				if(!$name)$name=urlencode($l->name);
				if(!($url=$l->recentLink))$url=$l->link;
				if($url)$A[$url]=array('url'=>$this->fURL($url,'show'),'title'=>$l->name,'desc'=>$this->name." - ".$l->cname,'img'=>'','m'=>'show');
			}
		}
		if($s)return $A;

		//关键词搜索
		$key=stripos($key,"%")===false && $name?$name:$key;
		if($lis=$this->searchHTML($key))if(is_array($lis)){
			//$A=array_merge($A,$lis);
			foreach($lis as $u=>$l){
				if(isset($A[$u])){
					$A[$u]['img']=$l['img'];
					$A[$u]['desc'].=" - ".$l['title'];
				}else{
					$A[]=$l;
				}
			}
		}
		return $A;
	}
	function searchJSON($key,$num=20){
		$key=str_replace('%','_',$key);
		if(!($json=$this->curl('http://search.video.qiyi.com/userSuggest/'.$key.'/'.$num.'/suggestResult/www/')))return;
		$json=$this->substring($json,'{','',1);
		$json=@json_decode($json);
		if(!is_array(@$json->data))return ;
		return $json->data;

	}

	function searchHTML($key=""){
		$url="http://so.iqiyi.com/so/q_".$key."_f_2";

		$html=$this->curl($url);

		$html=preg_replace('/\w+=\"[\w\-]+\"/is',"",$html);
		$html=preg_replace('/\r|\n|\s|\ "/is',"",$html);
		$html=preg_replace('/\w+=\"[\w\-]+\"/is',"",$html);
		$lis=array();

		//die($html);
		//die($html);
		$htm =$this->substring($html,'outputstart');

		$html=$this->substring($html,'第一位展示开始','第一位展示结束');

		//大分类
		preg_match_all('/<dt><ahref="(.[^\"]*)"><imgsrc="(.[^\"]*\.jpg)"(title="(.[^\"]*)?")?alt="(.[^\"]*)"/is',$html,$B);
		foreach($B[4] as $i=>$title){
			if($title=="")$title=$B[5][$i];
			$url=$B[1][$i];
			$lis[$url]=array('url'=>$this->fURL($url,'show'),'title'=>$title,'desc'=>'','img'=>$B[2][$i],'m'=>'show');
		}

		preg_match_all('/<li><ahref="(http(.[^\"]*)\/\d+\/\w+\.html)"><imgsrc="(.[^\"]*\.jpg)"(title="(.[^\"]*)?")?alt="(.[^\"]*)"/is',$html,$B);
		foreach($B[1] as $i=>$url){
			$lis[$url]=array('url'=>$this->fURL($url,'show'),'title'=>$B[5][$i],'desc'=>$B[6][$i],'img'=>$B[3][$i],'m'=>'show');
		}

		//列表
		preg_match_all('/<li><ahref="(http(.[^\"]*)\/\d+\/\w+\.html)"title="(.[^\"]*)">(.[^\"]*)<\/a><\/li>/is',$html,$B);
		foreach($B[1] as $i=>$url){
			$lis[$url]=array('url'=>$this->fURL($url,'show'),'title'=>$B[3][$i],'desc'=>$B[4][$i],'img'=>'','m'=>'show');
		}

		//片花
		preg_match_all('/<li><ahref="(http(.[^\"]*)\/\d+\/\w+\.html)"><imgsrc="(.[^\"]*\.jpg)"(title="(.[^\"]*)?")?alt="(.[^\"]*)"/is',$htm,$B);
		foreach($B[1] as $i=>$url){
			$lis[$url]=array('url'=>$this->fURL($url,'show'),'title'=>$B[5][$i],'desc'=>$B[6][$i],'img'=>$B[3][$i],'m'=>'show');
		}
		return $lis;
	}
	//获取INC视频列表
	private function gInc($u=""){
		$lis=array();
		$html=$this->curl($u?$u:$this->g->u);
		$html=preg_replace("/\s|\r|\n|\t/is","",$html);
		$html=preg_replace("/<li.[^\>]*>/is","\n",$html);

		preg_match_all('/\n<ahref="(http.[\w\-\.\:\/]+\.html)"(.[^>]*)?><img(.[^\>]*)(src|data\-lazy)="(http.[\w\-\.\:\/]+\.jpg)"(title="(.[^\"]*)?)?"alt="(.[^\"]*)?".[^\n]*/is',$html,$B);
		foreach(@$B[8] as $i=>$name){
			if($name=="")$name=$B[7][$i];
			preg_match("/第\d+集/is",$name,$C);
			if(isset($C[0]))$name=$C[0];
			$lis[]=array("name"=>$name,"img"=>$B[5][$i],"url"=>$this->fURL($B[1][$i],'show'));
		}
		if(!$lis){
			$h=split("\n",$html);
			for($i=1;$i<count($h);$i++){
				$li=$h[$i];
				preg_match('/href="(.[^\"]*)"/i',$li,$B);
				if(!($url=@$B[1]))continue;

				preg_match('/src="(.[^\"]*)"/i',$li,$B);
				$img=@$B[1];

				preg_match('/alt="(.[^\"]*)"/i',$li,$B);
				if(!($name=@$B[1])){
					preg_match('/title="(.[^\"]*)"/i',$li,$B);
					$name=@$B[1];
				}
				$lis[]=array("name"=>$name,"img"=>$img,"url"=>$this->fURL($url,'show'));
			}
		}
		return $u?$lis:parent::tabRight($lis);
	}
	function isVIP($u){
		return stripos($u,'vip.iqiyi.com')==7;
	}
}