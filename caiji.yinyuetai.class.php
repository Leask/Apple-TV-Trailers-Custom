<?php
/*
 * Object	采集音悦台路径并转换成Apple TV要求的XML
 * Author	Missde,陈州府
 * link	http://weibo.com/missde
 * mTime	2012-09-15 12:00
 *
**/


class caiji_yinyuetai_class extends ini_class{
	function __construct(){
		parent::__construct();
		$this->name="音悦台";
		$this->www="http://www.yinyuetai.com";
		$this->ico=ICO."yinyuetai.gif";
		$this->menu=array(
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
	}
	function index(){
		$this->getCache(86400);
		//$html=$this->curl("http://192.168.1.106/appletv/y.html");
		$html=$this->curl($this->www."/mv/recommend");
		$html=preg_replace("/\r|\n|\s|\t/s","",$html);

		/*
		//获取推荐列表 - 首页 , 并缓存
		preg_match_all('/<ahref="(.[^\"]*)"target="_blank"class="song">(.[^\<]*)</is',$html,$B);
		preg_match_all('/onclick="playVideoHere\(this,\'\d+\'\)"><imgsrc="(.[^\"]*)"/is',$html,$C);
		preg_match_all('/data\-url\=\"(.[^\"]*)\"/is',$html,$D);
		$lis=array();
		foreach($B[1] as $i=>$url){
			if(!$url)continue;
			$url=$this->url("http://www.yinyuetai.com".$url,"show");
			$lis[$url]=array("url"=>$url,"mp4"=>@$D[1][$i],"name"=>@$B[2][$i],"img"=>preg_replace("/\?.8/","",@$C[1][$i]));
		}
		//sort($lis);
		foreach($lis as $r){
			$play=array("url"=>$r['mp4'],"title"=>$r['name'],"desc"=>"");
			$this->boxCache($play,"play",$this->cacheURL($r['url']));
		}
		*/
		//获取热门推荐
		$html=$this->curl($this->www.'/mv/include/hot-today-mvlist');
		$html.=$this->curl($this->www.'/mv/include/hot-week-mvlist');
		//$html.=$this->curl('http://www.yinyuetai.com/mv/include/hot-month-mvlist');

		preg_match_all('/<a href="(.[^\"]*)" target="_blank"><img src="(.[^\"]*)" alt="(.[^\"]*)"/is',$html,$B);
		foreach($B[1] as $i=>$url){
			if($i>10)break;
			$url=$this->url($this->www.$url,"show");
			if(!isset($lis[$url]))$lis[$url]=array("url"=>$url,"name"=>@$B[3][$i],"img"=>preg_replace("/\?.*/","",@$B[2][$i]));
		}
		sort($lis);


		parent::shouye(array("推荐音乐"=>$lis),$this->M());
	}
	function M(){
		$M=array();
		foreach($this->menu as $n=>$u)$M[$n]=$this->url($u,"lispage");
		return $M;
	}

	function lispage(){
		$this->getCache(86400);
		$url=$this->g->u;

		$html=$this->curl($this->g->u);
	//	$html=$this->curl("http://192.168.1.106/appletv/f.html");
		$html=preg_replace("/\r|\n|\s|\t/s","",$html);

		//分页
		$pages=array();
		$page=$this->substring($html,'<divclass="page-nav"id="pageNav">','</div>');
		preg_match_all("/>(\d+)</is",$page,$A);
		preg_match_all('/common_page\(\'(\d+)\'/is',$page,$B);
		foreach($A[1] as $i){
			if(in_array($i,$B[1])){
				$u=preg_replace("/(\?|\&)page=.&/i","",$url);
				$u.=(stripos($u,'?')===false?"?":"&")."page=".$i;
				$pages[$i]=$this->url($u,'lispage');
			}else{
				$pages[$i]="";
			}
		}

		//列表
		preg_match_all('/<ahref="(\/video\/\d+)"target="_blank"><imgsrc="(.[^\"]*)"alt="(.[^\"]*)"/is',$html,$B);
		foreach($B[1] as $i=>$url){
			$lis[]=array("url"=>$this->url($this->www.$url,"show"),"img"=>$B[2][$i],"name"=>$B[3][$i]);
		}
		parent::lis($this->M(),$lis,$pages);
	}

	function show(){
		$this->getCache(-1);
                /*
                */
		preg_match("/video\/(\d+)/i",$this->g->u,$B);
		if(@$B[1]){
			$url="http://www.yinyuetai.com/insite/get-video-info?flex=true&videoId=".$B[1];
			$html=$this->curl($url);
			preg_match("/http:\/\/hd\.yinyuetai\.com.[\w\/]+\.flv/i",$html,$C);
			if($url=@$C[0]){
				$A=array("url"=>$url,"title"=>"","desc"=>"");
				parent::look($A,"play");
				return;
			}
			//-----stop
		}


		$html=$this->curl($this->g->u);
		preg_match('/setHtml5Video\(\'(.[^\']*)\'/is',$html,$B);
		if(!($mp4=@$B[1]))$this->alert("没有找到适合Apple TV播放的视频...");

		preg_match('/og:title" content="(.[^\"]*)"/is',$html,$B);
		$title=@$B[1];

		//preg_match('/og:image" content="(.[^\"]*)"/is',$html,$B);
		//$img=@$B[1];

		preg_match('/og:description" content="(.[^\"]*)"/is',$html,$B);
		$description=@$B[1];

		$A=array("url"=>$mp4,"title"=>$title,"desc"=>$description);
		parent::look($A,"play");
	}

	function search($key=""){
	}

}