<?php
/*
 * Object	将数组转换成Apple TV要求的XML
 * Author	Missde,陈州府
 * link	http://weibo.com/missde
 * mTime	2012-09-02 23:44
 *
**/


class xml_class{
	//xml
	function show($A,$box=0) {
		$err=0;
		if(!$A || count($A)==0)$box=0;
		switch($box){
			case "index":
				$xml='<scroller id="missde.cn-index"><items>'.$A.'</items></scroller>';
				break;
			case "lis":
				//分类列表 A['menu']+A['lis']+A['pages']
				$xml='<scroller id="missde.cn-lis"><items>'.$A.'</items></scroller>';
				break;
			case "play":
				//播放页
				if(is_string($A))$A=array("url"=>$A);
				$xml='<videoPlayer id="missde.cn-player"><httpFileVideoAsset id="show-player">
					<mediaURL>'.$this->fXML(@$A['url']).'</mediaURL>
					<title>'.$this->fXML(@$A['title']).'</title>
					<description>'.$this->fXML(@$A['desc']).'</description>
					</httpFileVideoAsset></videoPlayer>';
				break;
			case "juji1":
				//单页剧集
				$xml='<scroller id="missde.cn-juji1"><items>'.$A.'</items></scroller>';
				break;
			case "tabLeft":
				//左侧TAB
				$xml='<listScrollerSplit id="com.sample.list-scroller-split"><menu><sections>'.$A.'</sections></menu></listScrollerSplit>';
				break;
			case "tabRight":
				//右侧TAB
				$xml='<preview><scrollerPreview id="com.sample.scrollerPreview"><items>'.$A.'</items></scrollerPreview></preview>';
				break;
			case "search":
				//搜索列表
				return array($err,'<plist version="1.0"><dict><key>items</key><array>'.$A.'</array></dict></plist>');
				break;
			case "xml":
				//搜索-左侧输入框
				return array($err,$A);
				break;
			default:
				$err=1;
				if(!is_array($A))$A=Array("Error","无数据");
				$xml='<dialog id="errorDialog">
				    <title><![CDATA['.$A[0].']]></title>
				    <description><![CDATA[' . $A[1] . ']]></description>
				</dialog>';
		}
		return array($err,'<?xml version="1.0" encoding="UTF-8"?>
			<atv><body>'.$xml.'</body></atv>');
	}
	function HR($title,$align="left"){
		return "\n".'<collectionDivider alignment="'.$align.'" accessibilityLabel=""><title>'.$this->fXML(@$title).'</title></collectionDivider>';
	}
	function fXML($str=""){
		return $str?'<![CDATA['.$str.']]>':'';
	}
	function menu($A){
		$xml="";$i=0;$j=0;$gid=Array();
		foreach($A as $name=>$r){
			//$title=preg_replace("/^_/i","",$name);
			//$img=preg_replace("/\W/is","",urlencode($title));
			$gid[]='<moviePoster id="menu_'.($i++).'" accessibilityLabel="" featured="true" '.$this->A(@$r['url']).'>
				<title>'.$r['title'].'</title>
				<image>'.$r['img'].'</image>
				<defaultImage>resource://Poster.png</defaultImage>
			</moviePoster>';
			if(count($gid)==14){
				$xml.='<grid id="grid_'.($i++).'" columnCount="14"><items>'.implode($gid).'</items></grid>';
				$j=1;$gid=array();
			}
		}
		if(count($gid)>0)$xml.='<grid id="grid_'.($i++).'" columnCount="14"><items>'.implode($gid).'</items></grid>';
		return $j==0?$xml:'<pagedGrid id="pages_'.($i++).'">'.$xml.'</pagedGrid>';
	}
	function DT($A,$br=0,$showname=1){
		$xml="";
		if(!is_array($A))return;
		foreach($A as $sname=>$ul){
			if(@count($ul)>0){
				$sname=explode("|",$sname);
				$num=max(intval(@$sname[1]),5);
				$b=isset($sname[2])?intval(@$sname[2]):$br;
				$s=isset($sname[3])?intval(@$sname[3]):$showname;
				$xml.=$this->HR($sname[0]);
				$xml.=$this->lis($ul,$num,$b,$s);
			}
		}
		return $xml;
	}

	function page($A){
		$xml="";$i=0;
		foreach($A as $page=>$url){
			$title=preg_replace("/^_/i","",$page);
			$ico=$url?'':'<image>resource://Like.png</image>';
			$xml.='<actionButton id="page_'.($i++).'" '.$this->A(@$url).'>
				<title>'.$this->fXML(@$title).'</title>
				'.$ico.'
				<focusedImage>resource://PlayFocused.png</focusedImage>
			</actionButton>';
		}
		return $this->box($xml,0,min($i,10));
	}

	function lis($A,$num=7,$br=1,$showname=1){
		$xml="";
		//if(!$A)$A[0]=array("url"=>'',"name"=>'出错了，或者无内容。',"img"=>'');
		foreach(@$A as $i=>$B){
			if(@$B['img']){
				if($showname){
					$xml.='<sixteenByNinePoster id="shelf_item_'.$i.'" accessibilityLabel="" alwaysShowTitles="true" '.$this->A(@$B['url']).'>
							<title>'.$this->fXML(@$B['name']).'</title>
							<image>'.$this->fXML(@$B['img']).'</image>
							<defaultImage>resource://16X9.png</defaultImage>
						</sixteenByNinePoster>';
				}else{
					$xml.='<moviePoster id="line_'.$i.'" accessibilityLabel="" featured="true" '.$this->A(@$B['url']).'>
							<title>'.$this->fXML(@$B['name']).'</title>
							<image>'.$this->fXML(@$B['img']).'</image>
							<defaultImage>resource://16X9.png</defaultImage>
						</moviePoster>';
				}
			}else{
				$xml.='<sixteenByNinePoster id="shelf_item_'.$i.'" accessibilityLabel="" alwaysShowTitles="true" '.$this->A(@$B['url']).'>
						<title>'.$this->fXML(@$B['name']).'</title>
						<image>resource://Play.png</image>
						<defaultImage>resource://16X9.png</defaultImage>
					</sixteenByNinePoster>';
			}
		}
		return $this->box($xml,$br,$num);
	}
	function tabLeft($A){
		$xml="";$i=0;
		foreach($A as $sname=>$ul){
			$xml.='<menuSection><header><textDivider alignment="left"><title>'.$this->fXML(@$sname).'</title></textDivider></header><items>';
			foreach($ul as $title=>$url){
				$xml.='<oneLineMenuItem id="tabLeft'.(++$i).'"><label>'.$this->fXML(@$title).'</label><preview><link>'.$url.'</link></preview></oneLineMenuItem>';
			}
			$xml.='</items></menuSection>';
		}
		return $xml;
	}
	function tabRight($A,$num=5,$br=1,$showname=1){
		//return $this->lis(($A,$num,$br,$showname);
		return $this->lis($A,$num,$br,$showname);
	}


	//搜索-右侧显示
	function searchShow($A){
		$xml="";
		foreach($A as $i=>$x){
			$title=preg_replace("/<.[^\>]*>/is","",$x['title']);
			$desc=preg_replace("/<.[^\>]*>/is","",$x['desc']);

			$xml.='<dict>
				<key>menu-item</key>
				<dict>
					<key>type</key><string>two-line-enhanced-menu-item</string>
					<key>label</key><string>'.$this->fXML(@$title).'</string>
					<key>label2</key><string>'.$this->fXML(@$desc).'</string>
					<key>image</key><dict><key>image</key><string>'.$this->fXML(@$x['img']).'</string></dict>
					<key>event-handlers</key>
					<dict>
						<key>select</key>
						<dict><key>action</key><string>load-url</string><key>parameters</key><dict><key>url</key><string>'.@$x['url'].'</string></dict></dict>
						<key>play</key>
						<dict><key>action</key><string>load-url</string><key>parameters</key><dict><key>url</key><string>'.@$x['url'].'</string></dict></dict>
					</dict>
				</dict>
				<key>identifier</key><string>list_0</string>
			</dict>';
		}
		return $xml;
	}

	//搜索-左侧输入框
	function searchFrame($s="",$sname=""){
		return '<?xml version="1.0" encoding="UTF-8"?>
		<plist version="1.0">
		<dict>
			  <key>搜索</key>
			  <string>wsj</string>
			  <key>identifier</key>
			  <string>wsj.wsjlive.search</string>
			  <key>page-type</key>
			  <dict>
				    <key>template-name</key>
				    <string>search</string>
				    <key>template-parameters</key>
				    <dict>
						<key>header</key>
						<dict>
							  <key>type</key>
							  <string>simple-header</string>
							  <key>title</key>
							  <string>'.$sname.'视频搜索</string>
							  <key>subtitle</key>
							  <string>支持拼音首字母搜索</string>
						</dict>
				    </dict>
			  </dict>
			  <key>url</key>
			  <string>'.INDEX.'m=search&amp;s='.$s.'&amp;key=</string>
		</dict>
		</plist>';
	}

	private function A($url=""){
		if(!$url)return"";
		if(stripos($url,"http://")===0){
			//href
			return 'onSelect="atv.loadURL(\''.$url.'\')" onPlay="atv.loadURL(\''.$url.'\')"';
		}else{
			//JS
			return 'onSelect="'.$url.'" onPlay="'.$url.'"';
		}
	}
	private function box($xml,$br,$num){
		if($xml){
			$id="_"+rand();
			if(!$br){
				return '<shelf id="'.$id.'" columnCount="'.$num.'"><sections><shelfSection><items>'.$xml.'</items></shelfSection></sections></shelf>';
			}else{
				return '<grid id="'.$id.'" columnCount="'.$num.'"><items>'.$xml.'</items></grid>';
			}
		}
		return "";
	}
}