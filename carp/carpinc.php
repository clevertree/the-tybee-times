<?php
/*
CaRP Evolution v3.6.7
Copyright (c) 2002-8 Antone Roundy

All rights reserved
This program may not be redistributed in whole or in part without written
permission from the copyright owner.

http://www.geckotribe.com/rss/carp/
Installation & Configuration Manual: http://carp.docs.geckotribe.com/
Also available as a remotely hosted service for sites that cannot run
scripts. See http://www.geckotribe.com/rss/jawfish/
*/

class RSSParser {
	var $insideitem=0;
	var $insidechannel=0;
	var $displaychannel;
	var $tag='';
	var $dups;
	var $ivalues;
	var $cvalues;
	var $itemcount=0;
	var $itemindex=0;
	var $top='';
	var $bottom='';
	var $body='';
	var $showit;
	var $tagpairs;
	var $filterin;
	var $filterout;
	var $filterinfield;
	var $filteroutfield;
	var $linktargets=array('',' target="_blank"',' target="_top"');
	var $channelborder;
	var $channelaorder;
	var $itemorder;
	var $formatCheck;
	
	function SetItemOrder($iord) {
		$this->itemorder=explode(',',preg_replace('/[^a-z0-9,]/','',strtolower($iord)));
	}

	function GetFieldValue($name,$ischannel=0) {
		global $carpcapriority,$carpiapriority;
		
		$name=strtoupper($name);
		$rv='';
		if ($ischannel) {
			$priority=&$carpcapriority;
			$values=&$this->cvalues;
		} else {
			$priority=&$carpiapriority;
			$values=&$this->ivalues;
		}
		if (isset($priority["$name"])) {
			foreach ($priority["$name"] as $fn => $val) {
				if (isset($values["$fn"])) {
					$got=1;
					if (is_array($val)) {
						for ($i=1,$j=count($val);$i<$j;$i+=2) {
							if (!(isset($values[$val[$i]])&&preg_match($val[$i+1],$values[$val[$i]]))) {
								$got=0;
								break;
							}
						}
					}
					if ($got) {
						$rv=$values["$fn"];
						break;
					}
				}
			}
		}
		return $rv;
	}
	
	function CheckFilter($lookfor,$field) {
		if (!empty($field)) {
			if (strpos(strtolower($this->GetFieldValue($field)),$lookfor)!==false) return 1;
		} else {
			if (strpos(strtolower($this->GetFieldValue('TITLE').' '.$this->GetFieldValue('DESC')),$lookfor)!==false) return 1;
		}
		return 0;
	}

	function Truncate(&$text,$max,$after,$afterlen) {
		if (($max>0)&&(CarpStrLen(preg_replace("/<.*?>/",'',$text))>$max)) {
			$j=strlen($text);
			$truncmax=$max-$afterlen;
			$isUTF8=strtoupper($GLOBALS['carpconf']['encodingout'])=='UTF-8';
			$out='';
			for ($i=0,$len=0;($len<$truncmax)&&($i<$j);$i++) {
				switch($text{$i}) {
				case '<':
					for ($k=$i+1;($k<$j)&&($text{$k}!='>');$k++) {
						if (($text{$k}=='"')||($text{$k}=="'")) {
							if ($m=strpos($text,$text{$k},$k+1)) $k=$m;
							else $k=$j;
						}
					}
					if ($text{$k}=='>') $out.=substr($text,$i,$k+1-$i);
					$i=$k;
					break;
				case '&':
					if ($text{$i+1}=='#') {
						if ($text{$i+2}=='x') {
							$matchset='/[0-9]/';
							$start=$i+3;
						} else {
							$matchset='/[0-9a-fA-F]/';
							$start=$i+2;
						}
					} else {
						$matchset='/[a-zA-Z]/';
						$start=$i+1;
					}
					$valid=0;
					for ($k=$start;$k<$j;$k++) {
						$c=$text{$k};
						if (($c==';')||($c==' ')) {
							if ($k>$start) $valid=1;
							if ($c==' ') $k--;
							break;
						} else if (!preg_match($matchset,$c)) break;
					}
					if ($valid) {
						$out.=substr($text,$i,$k+1-$i);
						$i=$k;
					} else $out.='&amp;';
					$len++;
					break;
				default:
					if ($isUTF8) {
						$val=ord($text{$i});
						$bytes=($val<=0x7F)?1:(($val<=0xDF)?2:(($val<=0xEF)?3:4));
						$out.=substr($text,$i,$bytes);
						$i+=($bytes-1);
					} else $out.=$text{$i};
					$len++;
				}
			}
			$did=$i<$j;
			$text=$out.($did?$after:'');
		} else $did=0;
		return $did;
	}
	
	function FormatLink($title,$link,$class,$style,$maxtitle,$atrunc,$atrunclen,$btitle,$atitle,$deftitle,$titles) {
		global $carpcallbacks;
		global $carpconf;

		$fulltitle=$title=trim(preg_replace("/<.*?>/",'',$title));
		$didTrunc=$this->Truncate($title,$maxtitle,$atrunc,$atrunclen);
		if (!strlen($title)) $title=$deftitle;
		
		$rv=$btitle.
			(strlen($link=trim(str_replace('"','&quot;',str_replace('&','&amp;',$link))))?(
				"<a href=\"$link\"".$this->linktargets[$carpconf['linktarget']].
				((($titles&&$didTrunc)||($titles==2))?" title=\"".str_replace('"','&quot;',$fulltitle)."\"":'')
			):(strlen($class.$style)?'<span':'')).
			(strlen($class)?(' class="'.$class.'"'):'').
			(strlen($style)?(' style="'.$style.'"'):'').
			(strlen($link.$class.$style)?'>':'').
			$title.
			(strlen($link)?'</a>':(strlen($class.$style)?'</span>':'')).
			$atitle."\n";
		foreach ($carpcallbacks['outputfield'] as $cb)
			if (($cb[2]=='')||($cb[2]=='title')||($cb[2]=='link'))
				$rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),$this->insideitem,(strlen($link)?'link':'title'),$this->itemindex,$this->itemcount,$rv);
		return $rv;
	}

	function FormatImage($url,$link,$w,$h,$alt,$ci) {
		global $carpcallbacks;
		global $carpconf;
		if (strlen($url)) {
			if ($carpconf["set$ci".'imagew']) {
				if ($carpconf["set$ci".'imageh']) {
					$w=$carpconf["set$ci".'imagew'];
					$h=$carpconf["set$ci".'imageh'];
				} else {
					if ($w) {
						$sr=$carpconf["set$ci".'imagew']/$w;
						$h=round($h*$sr);
					}
					$w=$carpconf["set$ci".'imagew'];
				}
			} else if ($carpconf["set$ci".'imageh']) {
				if ($h) {
					$sr=$carpconf["set$ci".'imageh']/$h;
					$w=round($w*$sr);
				}
				$h=$carpconf["set$ci".'imageh'];
			} else if ($carpconf["max$ci".'imagew']&&($w>$carpconf["max$ci".'imagew'])) {
				$wr=($carpconf["max$ci".'imagew']/$w);
				if ($carpconf["max$ci".'imageh']) {
					$hr=($h>$carpconf["max$ci".'imageh'])?($carpconf["max$ci".'imageh']/$h):1;
					$sr=min($wr,$hr);
					$w=round($w*$sr);
					$h=round($h*$sr);
				} else {
					$w=$carpconf["max$ci".'imagew'];
					$h=round($h*$wr);
				}
			} else if ($carpconf["max$ci".'imageh']&&($h>$carpconf["max$ci".'imageh'])) {
				$sr=($carpconf["max$ci".'imageh']/$h);
				$h=$carpconf["max$ci".'imageh'];
				$w=round($w*$sr);
			}
			if (!$w&&$carpconf["def$ci".'imagew']) $w=$carpconf["def$ci".'imagew'];
			if (!$h&&$carpconf["def$ci".'imageh']) $h=$carpconf["def$ci".'imageh'];
			$rv=($carpconf["b$ci".'image'].
				(strlen($link)?('<a href="'.str_replace('&','&amp;',$link).'"'.$this->linktargets[$carpconf['linktarget']].'>'):'').
				'<img src="'.str_replace('&','&amp;',$url).'"'.($w?" width=\"$w\"":'').($h?" height=\"$h\"":'').' border="0"'.(strlen($alt)?(' alt="'.str_replace('"','&quot;',$alt).'"'):'').'>'.
				(strlen($link)?'</a>':'').
				$carpconf["a$ci".'image']."\n");
		} else $rv='';
		foreach ($carpcallbacks['outputfield'] as $cb)
			if (($cb[2]=='')||($cb[2]=='image'))
				$rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),($ci=='i'),'image',$this->itemindex,$this->itemcount,$rv);
		return $rv;
	}

	function FormatDate($val,$ci) {
		global $carpcallbacks;
		global $carpconf;
		$rv=($val>0)?($carpconf["b$ci".'date'].date($carpconf[$ci.'dateformat'],$val).$carpconf["a$ci".'date']."\n"):'';
		foreach ($carpcallbacks['outputfield'] as $cb)
			if (($cb[2]=='')||($cb[2]=='date'))
				$rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),($ci=='i'),'date',$this->itemindex,$this->itemcount,$rv);
		return $rv;
	}
	
	function FormatSimpleField($val,$ci,$name,$fixamp=0) {
		global $carpcallbacks;
		global $carpconf;
		if ($fixamp) $val=str_replace('&','&amp;',$val);
		$rv=strlen($val)?($carpconf["b$ci$name"].$val.$carpconf["a$ci$name"]."\n"):'';
		foreach ($carpcallbacks['outputfield'] as $cb)
			if (($cb[2]=='')||($cb[2]==$name))
				$rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),($ci=='i'),$name,$this->itemindex,$this->itemcount,$rv);
		return $rv;
	}
	
	function FormatDescription($description,$maxdesc,$b,$a,$atrunc,$atrunclen) {
		global $carpcallbacks;
		global $carpconf;
		if (strlen($description)) {
			if (strlen($carpconf['desctags'])) {
				$adddesc=trim(preg_replace("#<(?!".$carpconf['desctags'].")(.*?)>#is",
					($carpconf['removebadtags']?'':"&lt;\\1\\2&gt;"),$description));
				$adddesc=preg_replace('/(<.*?)\bon[a-z]+\s*=\s*"[^"]*"(.*?>)/i',"\\1\\2",$adddesc);
			} else $adddesc=trim(preg_replace("#<(.*?)>#s",($carpconf['removebadtags']?'':"&lt;\\1&gt;"),$description));
			$didTrunc=$this->Truncate($adddesc,$maxdesc,'',$atrunclen);
			
			preg_match_all("#<(/?\w*).*?>#",$adddesc,$matches);
			$opentags=$matches[1];
			for ($i=0;$i<count($opentags);$i++) {
				$tag=strtolower($opentags[$i]);
				if (strcmp(substr($tag,0,1),'/')) {
					$baretag=$tag;
					$isClose=0;
				} else {
					$baretag=substr($tag,1);
					$isClose=1;
				}
				if (!isset($this->tagpairs["$baretag"])) {
					array_splice($opentags,$i,1);
					$i--;
				} else if ($isClose) {
					array_splice($opentags,$i,1);
					$i--;
					for ($j=$i;$j>=0;$j--) {
						if (!strcasecmp($opentags[$j],$baretag)) {
							array_splice($opentags,$j,1);
							$i--;
							$j=-1;
						}
					}
				}
			}
			if (strlen($adddesc)) {
				$adddesc=$b.$adddesc.(($didTrunc)?$atrunc:'');
				for ($i=count($opentags)-1;$i>=0;$i--) $adddesc.="</$opentags[$i]>";
				$adddesc.="$a\n";
			}
		} else $adddesc='';
		foreach ($carpcallbacks['outputfield'] as $cb)
			if (($cb[2]=='')||($cb[2]=='desc'))
				$adddesc=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),$this->insideitem,'desc',$this->itemindex,$this->itemcount,$adddesc);
		return $adddesc;
	}
	
	function XMLFormatError($show=0) {
		switch ($this->formatCheck) {
		case -1: $rv='Unknown RDF-based format or non-standard RSS element name prefix.'; break;
		case -3: $rv='CaRP cannot process Atom 0.3 feeds.'; break;
		case -10: $rv='CaRP cannot process Atom 1.0 feeds.'; break;
		case -11:
		case -12: $rv='Unknown feed format.'; break;
		case -20: $rv='This appears to be an HTML webpage, not a feed.'; break;
		case -100:
		case -101: $rv='Unknown document format.'; break;
		default: $rv='';
		}
		if (strlen($rv)&&$show) CarpError($rv,'unknown-format');
		return " - $rv";
	}
	
	function CheckFormat($tagName,&$attrs) {
		if (strpos($tagName,':')) {
			list($prefix,$name)=explode(':',$tagName);
			switch ($name) {
			case 'RDF':
				foreach ($attrs as $k=>$v) {
					if ((strpos($k,'XMLNS')===0)&&(strpos($v,'http://purl.org/rss/')===0)) {
						$this->formatCheck=1;
						break;
					}
				}
				if (!$this->formatCheck) $this->formatCheck=-1;
				break;
			case 'feed':
				switch ($attrs["XMLNS:$prefix"]) {
				case 'http://www.w3.org/2005/Atom': $this->formatCheck=-10; break;
				case 'http://purl.org/atom/ns#': $this->formatCheck=-3; break;
				default: $this->formatCheck=-11;
				}
				break;
			default: $this->formatCheck=-100;
			}
		} else {
			switch(strtolower($tagName)) {
			case 'rss': $this->formatCheck=2; break;
			case 'html': $this->formatCheck=-20; break;
			case 'feed':
				switch ($attrs["XMLNS"]) {
				case 'http://www.w3.org/2005/Atom': $this->formatCheck=-10; break;
				case 'http://purl.org/atom/ns#': $this->formatCheck=-3; break;
				default: $this->formatCheck=-12;
				}
				break;					
			default: $this->formatCheck=-101;
			}
		}	
	}
	
	function MapPrefix(&$tagName,&$attrs) {
		global $carpconf;
		foreach ($carpconf['prefix-map'] as $k=>$v) {
			if (strlen($v)) $v="$v:";
			$tagName=preg_replace("/^$k:/",$v,$tagName);
			if (is_array($attrs)) {
				$newattrs=array();
				foreach ($attrs as $ak=>$av) {
					if (($nak=preg_replace("/^$k:/","$v:",$ak))!=$ak) $newattrs[$nak]=$av;
					else $newattrs[$ak]=$av;
				}
				$attrs=$newattrs;
			}
		}
	}
	
	function startElement($parser,$tagName,$attrs) {
		global $carpcallbacks;
		global $carpconf;
		global $carpcafields,$carpiafields;
		
		$this->MapPrefix($tagName,$attrs);
		
		$this->tag.=(strlen($this->tag)?'^':'').$tagName;
		if (!$this->formatCheck) $this->CheckFormat($tagName,$attrs);
		foreach ($carpcallbacks['startelement'] as $cb)
			if (($cb[2]=='')||preg_match("/$cb[2]/",$this->tag))
				call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),$this->insideitem,$this->tag,$attrs);
		if ($this->insidechannel) $this->insidechannel++;
		if ($this->insideitem) $this->insideitem++;
		if ($tagName=="ITEM") {
			$this->insideitem=1;
			$this->ivalues=array();
			$this->tag='';
		} else if ($tagName=="CHANNEL") {
			$this->insidechannel=1;
			$this->cvalues=array();
			$this->tag='';
		}
		else {
			if ($this->insideitem) {
				$f=&$carpiafields;
				$v=&$this->ivalues;
			} else {
				$f=$carpcafields;
				$v=&$this->cvalues;
			}
			foreach($attrs as $key => $val) {
				if (isset($f[$this->tag."^$key"])) $v[$this->tag."^$key"]=$val;
			}
		}
	}

	function endElement($parser,$tagName) {
		global $carpconf;
		global $carpcallbacks;
		$empty='';
		$this->MapPrefix($tagName,$empty);

		foreach ($carpcallbacks['endelement'] as $cb)
			if (($cb[2]=='')||preg_match("/$cb[2]/",$this->tag))
				call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),$this->insideitem,$this->tag);
		$this->tag=substr($this->tag,0,strrpos($this->tag,'^'));
		if ($tagName=="ITEM") {
			if ($this->itemcount<$carpconf['maxitems']) {
				$filterblock=0;

				if (count($this->filterin)) {
					$filterblock=1;
					for ($i=count($this->filterin)-1;$i>=0;$i--) {
						if ($this->CheckFilter($this->filterin[$i],$this->filterinfield[$i])) {
							$filterblock=0;
							break;
						}
					}
				}
				if (count($this->filterout)&&!$filterblock) {
					for ($i=count($this->filterout)-1;$i>=0;$i--) {
						if ($this->CheckFilter($this->filterout[$i],$this->filteroutfield[$i])) {
							$filterblock=1;
							break;
						}
					}
				}
				if (!$filterblock) foreach ($carpcallbacks['displayitem'] as $cb)
					if (($rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),$this->itemindex,$this->itemcount))<=0) {
						$filterblock=1;
						if ($rv<0) $this->itemcount=$carpconf['maxitems'];
					}
				if (!$filterblock) {
					$fulltitle=trim($this->GetFieldValue('TITLE'));
					$skipit=0;
					if ($carpconf['skipdups']) {
						if (!isset($this->dups["$fulltitle"])) $this->dups["$fulltitle"]=1;
						else $skipit=1;
					}
					if (!$skipit) {
						$thisitem=$carpconf['bi'];
						
						$pubdate=CarpDecodeDate($this->GetFieldValue('DATE'));

						for ($ioi=0;$ioi<count($this->itemorder);$ioi++) {
							switch ($this->itemorder[$ioi]) {
							case "link":
							case "title":
								$thisitem.=$this->FormatLink($this->GetFieldValue('TITLE'),(($this->itemorder[$ioi]=='link')?$this->GetFieldValue('URL'):''),
								$carpconf['ilinkclass'],$carpconf['ilinkstyle'],$carpconf['maxititle'],$carpconf['atruncititle'],$carpconf['atruncititlelen'],
								$carpconf['bilink'],$carpconf['ailink'],$carpconf['defaultititle'],$carpconf['ilinktitles']); break;
							case "url": $thisitem.=$this->FormatSimpleField($this->GetFieldValue('URL'),'i','url',1); break;
							case "author": $thisitem.=$this->FormatSimpleField($this->GetFieldValue('AUTHOR'),'i','author'); break;
							case "date": $thisitem.=$this->FormatDate($pubdate,'i'); break;
							case "podcast": $thisitem.=$this->FormatSimpleField(
									trim(str_replace('"','&quot;',str_replace('&','&amp;',$this->GetFieldValue('PODCAST'))))
									,'i','podcast');
								break;
							case "image": $thisitem.=$this->FormatImage($this->GetFieldValue('IMAGEURL'),$this->GetFieldValue('IMAGELINK'),$this->GetFieldValue('IMAGEWIDTH'),$this->GetFieldValue('IMAGEHEIGHT'),$this->GetFieldValue('IMAGEALT'),'i'); break;
							case "desc":
								$thisitem.=$this->FormatDescription($this->GetFieldValue('DESC'),
									$carpconf['maxidesc'],$carpconf['bidesc'],$carpconf['aidesc'],$carpconf['atruncidesc'],$carpconf['atruncidesclen']);
								break;
							default:
								$rv='';
								foreach ($carpcallbacks['handlefield'] as $cb)
									if ($cb[2]==$this->itemorder[$ioi])
										$rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),1,$this->itemorder[$ioi],$this->itemindex,$this->itemcount,$this->ivalues,$rv);
								$thisitem.=$rv;
							}
						}					
						$thisitem.=$carpconf['ai'];
						$this->itemcount++;
						if ($this->showit) $this->body.=$thisitem."\n";
						else $this->body.=(
								$pubdate?($pubdate+($carpconf['timeoffset']*60)):(($cdate=CarpDecodeDate($this->GetFieldValue('DATE',1)))?
									($cdate+($carpconf['timeoffset']*60)-$this->itemcount):(($carpconf['lastmodified']>0)?($carpconf['lastmodified']-$this->itemcount):0)
								)
							).
							':'.preg_replace("/[\r\n]/",' ',(str_replace(":",'_',$fulltitle).':'.$thisitem))."\n";
					}
				}
			}
			$this->insideitem=0;
			$this->itemindex++;
		} else if ($tagName=="CHANNEL") {
			$this->displaychannel=1;
			foreach ($carpcallbacks['displaychannel'] as $cb)
				if (($rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1])))<=0) {
					$this->displaychannel=0;
					if ($rv<0) $this->itemcount=$carpconf['maxitems'];
				}
			$this->insidechannel=0;
		}
		if ($this->insidechannel) $this->insidechannel--;
		if ($this->insideitem) $this->insideitem--;
	}

	function DoEndChannel(&$data,&$order,&$b,&$a) {
		global $carpconf;
		
		for ($coi=0;$coi<count($order);$coi++) {
			switch ($order[$coi]) {
			case "link":
			case "title":
				$data.=$this->FormatLink($this->GetFieldValue('TITLE',1),(($order[$coi]=='link')?$this->GetFieldValue('URL',1):''),
				$carpconf['clinkclass'],$carpconf['clinkstyle'],$carpconf['maxctitle'],$carpconf['atruncctitle'],$carpconf['atruncctitlelen'],
				$carpconf['bctitle'],$carpconf['actitle'],'',$carpconf['clinktitles']); break;
			case "url": $data.=$this->FormatSimpleField($this->GetFieldValue('URL',1),'c','url',1); break;
			case "desc":
				$data.=$this->FormatDescription($this->GetFieldValue('DESC',1),
					$carpconf['maxcdesc'],$carpconf['bcdesc'],$carpconf['acdesc'],$carpconf['atrunccdesc'],$carpconf['atrunccdesclen']);
				break;
			case "date": $data.=$this->FormatDate(CarpDecodeDate($this->GetFieldValue('DATE',1)),'c'); break;
			case "image": $data.=$this->FormatImage($this->GetFieldValue('IMAGEURL',1),$this->GetFieldValue('IMAGELINK',1),$this->GetFieldValue('IMAGEWIDTH',1),$this->GetFieldValue('IMAGEHEIGHT',1),$this->GetFieldValue('IMAGEALT',1),'c'); break;
			default:
				$rv='';
				foreach ($carpcallbacks['handlefield'] as $cb)
					if ($cb[2]==$order[$coi])
						$rv=call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),1,$order[$coi],0,$this->cvalues,$rv);
				$data.=$rv;
				
			}
		}
		if (strlen($data)) $data=$b.$data.$a;
		if (!$this->showit) $data=preg_replace("/\n/",' ',$data);
	}
		
	function characterData($parser,$data) {
		global $carpconf,$carpcafields,$carpiafields;
		global $carpcallbacks;

		if ($this->insideitem&&($this->itemcount==$carpconf['maxitems'])) return;
		
		foreach ($carpcallbacks['characterdata'] as $cb)
			if (($cb[2]=='')||preg_match("/$cb[2]/",$this->tag))
				call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),$this->insideitem,$this->tag,$data);
		if ($this->insideitem) {
			$f=&$carpiafields;
			$v=&$this->ivalues;
		} else {
			$f=$carpcafields;
			$v=&$this->cvalues;
		}
		if (isset($f[$this->tag])) {
			if (isset($v[$this->tag])) $v[$this->tag].=$data;
			else $v[$this->tag]=$data;
		}
	}
	
	function PrepTagPairs($tags) {
		$this->tagpairs=$findpairs=array();
		$temptags=explode('|',strtolower(preg_replace("/\\\\b/",'',$tags)));
		for ($i=count($temptags)-1;$i>=0;$i--) {
			$tag=$temptags[$i];
			if (strcmp(substr($tag,0,1),'/')) {
				$searchpre='/';
				$baretag=$tag;
			} else {
				$searchpre='';
				$baretag=substr($tag,1);
			}
			if (isset($findpairs["$searchpre$baretag"])) {
				$this->tagpairs["$baretag"]=1;
				$findpairs["$baretag"]=$findpairs["/$baretag"]=2;
			} else $findpairs["$tag"]='1';
		}
	}
}

function CarpDecodeDate($val) {
	global $carpconf;
	if (strlen($val)) {
		if (
			(($rv=strtotime($val))==-1)&&
			(($rv=strtotime(preg_replace("/([0-9]+\-[0-9]+\-[0-9]+)T(.*)(?:Z|([-+][0-9]{1,2}):([0-9]{2}))/","$1 $2 $3$4",$val)))==-1)
		) $rv=0;
	} else $rv=0;
	return $rv?($rv+($carpconf['timeoffset']*60)):0;
}

function OpenRSSFeed($url) {
	global $carpconf,$carpversion,$CarpRedirs;
	
	$carpconf['lastmodified']='';
	if (preg_match("#^http://#i",$url)) {
		if (strlen($carpconf['proxyserver'])) {
			$urlparts=parse_url($carpconf['proxyserver']);
			$therest=$url;
		} else {
			$urlparts=parse_url($url);
			$therest=$urlparts['path'].(isset($urlparts['query'])?('?'.$urlparts['query']):'');
		}
		$domain=$urlparts['host'];
		$port=isset($urlparts['port'])?$urlparts['port']:80;
		$fp=fsockopen($domain,$port,$errno,$errstr,$carpconf['timeout']);
		if ($fp) {
			fputs($fp,"GET $therest HTTP/1.0\r\n".
				($carpconf['sendhost']?"Host: $domain\r\n":'').
				(strlen($carpconf['proxyauth'])?('Proxy-Authorization: Basic '.base64_encode($carpconf['proxyauth']) ."\r\n"):'').
				(strlen($carpconf['basicauth'])?('Authorization: Basic '.base64_encode($carpconf['basicauth']) ."\r\n"):'').
				"User-Agent: CaRP/$carpversion\r\n\r\n");
			while ((!feof($fp))&&preg_match("/[^\r\n]/",$header=fgets($fp,1000))) {
				if (preg_match("/^Location:/i",$header)) {
					fclose($fp);
					if (count($CarpRedirs)<$carpconf['maxredir']) {
						$loc=trim(substr($header,9));
						if (!preg_match("#^http://#i",$loc)) {
							if (strlen($carpconf['proxyserver'])) {
								$redirparts=parse_url($url);
								$loc=$redirparts['scheme'].'://'.$redirparts['host'].(isset($redirparts['port'])?(':'.$redirparts['port']):'').$loc;
							} else $loc="http://$domain".(($port==80)?'':":$port").$loc;
						}
						for ($i=count($CarpRedirs)-1;$i>=0;$i--) if (!strcmp($loc,$CarpRedirs[$i])) {
							CarpError('Redirection loop detected. Giving up.','redirection-loop');
							return 0;
						}
						$CarpRedirs[count($CarpRedirs)]=$loc;
						return OpenRSSFeed($loc);
					} else {
						CarpError('Too many redirects. Giving up.','redirection-too-many');
						return 0;
					}
				} else if (preg_match("/^Last-Modified:/i",$header))
					$carpconf['lastmodified']=CarpDecodeDate(substr($header,14));
			}
		} else CarpError("$errstr ($errno)",'connection-failed');
	} else if ($fp=fopen($url,'r')) {
		if ($stat=fstat($fp)) $carpconf['lastmodified']=$stat['mtime'];
	} else CarpError("Failed to open file: $url",'local-file-open-failed');
	return $fp;
}

function CarpCacheUpdatedMysql() {
	global $carpconf;
	$rv=-1;
	CarpCacheMysqlConnect();
	if (CarpParseMySQLPath($carpconf['cachefile'],$which,$key)) {
		if ($r=CarpMySQLQuery('select updated from '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' where id="'.addslashes($key).'"')) {
			if (mysql_num_rows($r)) $rv=mysql_result($r,0);
			else $rv=0;
			mysql_free_result($r);
		} else CarpError('Database error attempting to check cache update time.','database-error');
	} else CarpError('Invalid cache identifier checking cache update time.','cache-not-found');
	return $rv;
}

function CarpCacheUpdatedFile($f) {
	global $carpconf;
	if ($s=fstat($f)) $rv=$s['mtime'];
	else {
		$rv=-1;
		CarpError('Can\'t stat cache file.','cache-file-access');
		fclose($f);
	}
	return $rv;
}

function CarpSaveCache($f,$data) {
	global $carpconf;
	switch($carpconf['cache-method']) {
	case 'mysql': if (CarpParseMySQLPath($carpconf['cachefile'],$which,$key)) {
			if (!CarpMySQLQuery('update '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' set updated='.time().',cache="'.addslashes($data).'" where id="'.addslashes($key).'"'))
				CarpError('Database error attempting to cache formatted data.','database-error');
			} else CarpError('Invalid cache indentifier saving cache.','cache-not-found');
		break;
	default: fwrite($f,$data); break;
	}
}

function CarpOpenCacheWriteMySQL() {
	global $carpconf;
	CarpCacheMysqlConnect();
	$rv=0;
	if (($a=CarpCacheUpdatedMysql())>=0) {
		if ($r=CarpMySQLQuery('select GET_LOCK("'.$carpconf['cachefile'].'",'.$carpconf['mysql-lock-timeout'].')')) {
			if (mysql_result($r,0)+0) {
				$b=CarpCacheUpdatedMysql();
				if ($a!=$b) CarpMySQLQuery('select RELEASE_LOCK("'.$carpconf['cachefile'].'")');
				else {
					CarpParseMySQLPath($carpconf['cachefile'],$rv,$key);
					$rv++;
					if (!$b) CarpTouchCache($carpconf['cachefile']);
				}
			}
		} else $rv=-1;
	} else $rv=-1;
	if ($rv==-1) CarpError('Failed to access database cache record.','cache-prepare-failed'); 
	return $rv;
}

function CarpOpenCacheWriteFile() {
	global $carpconf;
	$rv=0;
	if (!file_exists($carpconf['cachefile'])) touch($carpconf['cachefile']);
	if ($f=fopen($carpconf['cachefile'],'r+')) {
		if ($a=CarpCacheUpdatedFile($f)) {
			flock($f,LOCK_EX); // ignore result--doesn't work for all systems and situations
			clearstatcache();
			if ($b=CarpCacheUpdatedFile($f)) {
				if ($a!=$b) {
					flock($f,LOCK_UN);
					fclose($f);
				} else $rv=$f;
			} else $rv=-1;
		} else $rv=-1;
	} else $rv=-1;
	if ($rv==-1) {
		CarpError("Can't open cache file.",'cache-prepare-failed');
		if ($f) fclose($f);
	}
	return $rv;
}

function OpenCacheWrite() {
	switch($GLOBALS['carpconf']['cache-method']) {
	case 'mysql': $rv=CarpOpenCacheWriteMySQL(); break; 
	default: $rv=CarpOpenCacheWriteFile(); break; 
	}
	return $rv;
}

function CloseCacheWrite($f) {
	global $carpconf;
	switch($carpconf['cache-method']) {
	case 'mysql': CarpMySQLQuery('select RELEASE_LOCK("'.$carpconf['cachefile'].'")'); break; 
	default: ftruncate($f,ftell($f));
		fflush($f);
		flock($f,LOCK_UN);
		fclose($f);
	}
	$carpconf['mtime']=time();
}

function CacheRSSFeed($url) {
	global $carpconf;
	if ($f=OpenRSSFeed($url)) {
		if (($outf=OpenCacheWrite())>0) {
			switch($carpconf['cache-method']) {
			case 'mysql': for ($d='';$l=fread($f,10000);) $d.=$l;
				CarpParseMySQLPath($carpconf['cachefile'],$which,$key);
				if (!CarpMySQLQuery('update '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' set updated='.time().',cache="'.addslashes($d).
					'" where id="'.$key.'"')
				) CarpError('Database error attempting to cache feed.','database-error');
				break;
			default: while ($l=fread($f,1000)) fwrite($outf,$l);
			}
			CloseCacheWrite($outf);
		}
		fclose($f);
	}
}

function CarpReadData($fp) {
	global $carpconf;
	return $carpconf['fixentities']?
		preg_replace("/&(?!lt|gt|amp|apos|quot|#[0-9]+|#x[0-9a-f]+)(.*\b)/is","&amp;\\1\\2",preg_replace("/\\x00/",'',fread($fp,4096))):
		preg_replace("/\\x00/",'',fread($fp,4096));
}

function CarpStrLen($s) {
	if (strtoupper($GLOBALS['carpconf']['encodingout'])=='UTF-8') {
		for ($i=$len=0,$j=strlen($s);$i<$j;$len++) {
			$val=ord($s{$i});
			$i+=($val<=0x7F)?1:(($val<=0xDF)?2:(($val<=0xEF)?3:4));
		}
	} else $len=strlen($s);
	return $len;
}

function GetRSSFeed($url,$cache,$showit) {
	global $carpconf,$CarpRedirs;
	$carpconf['desctags']=preg_replace("/(^\\|)|(\\|$)/",'',preg_replace("/\\|+/","|",preg_replace("#/?(script|embed|object|applet|iframe)#i",'',$carpconf['descriptiontags'])));
	if (strlen($carpconf['desctags'])) $carpconf['desctags']=str_replace('|','\b|',$carpconf['desctags']).'\b';
	
	$carpconf['atruncititlelen']=CarpStrLen($carpconf['atruncititle']);
	$carpconf['atruncctitlelen']=CarpStrLen($carpconf['atruncctitle']);
	$carpconf['atruncidesclen']=CarpStrLen($carpconf['atruncidesc']);
	$carpconf['atrunccdesclen']=CarpStrLen($carpconf['atrunccdesc']);
	
	// 3 lines for backwards compatibility
	if ($carpconf['corder']!==false) $carpconf['cborder']=$carpconf['corder'];
	if ($carpconf['bc']!==false) $carpconf['bcb']=$carpconf['bc'];
	if ($carpconf['ac']!==false) $carpconf['acb']=$carpconf['ac'];
	
	$rss_parser=new RSSParser();
	$carpconf['rssparser']=&$rss_parser;
	if ($carpconf['skipdups']) $rss_parser->dups=array();
	$rss_parser->showit=$showit;
	$rss_parser->channelborder=explode(',',preg_replace('/[^a-z0-9,]/','',strtolower($carpconf['cborder'])));
	$rss_parser->channelaorder=explode(',',preg_replace('/[^a-z0-9,]/','',strtolower($carpconf['caorder'])));
	$rss_parser->SetItemOrder($carpconf['iorder']);
	$rss_parser->formatCheck=0;
	
	// the next 2 lines are for backward compatibility and will eventually be removed
	if ($carpconf['ilinktarget']!='-1') $carpconf['linktarget']=$carpconf['ilinktarget'];
	else if ($carpconf['clinktarget']!='-1') $carpconf['linktarget']=$carpconf['clinktarget'];

	if (preg_match("/[^0-9]/",$carpconf['linktarget'])) $rss_parser->linktargets[$carpconf['linktarget']]=' target="'.$carpconf['linktarget'].'"';
	$rss_parser->filterinfield=array();
	if (strlen($carpconf['filterin'])) {
		$rss_parser->filterin=explode('|',strtolower($carpconf['filterin']));
		for ($i=count($rss_parser->filterin)-1;$i>=0;$i--) {
			if (strpos($rss_parser->filterin[$i],':')!==false)
				list($rss_parser->filterinfield[$i],$rss_parser->filterin[$i])=explode(':',$rss_parser->filterin[$i],2);
			else $rss_parser->filterinfield[$i]='';
		}
	} else $rss_parser->filterin=array();
	$rss_parser->filteroutfield=array();
	if (strlen($carpconf['filterout'])) {
		$rss_parser->filterout=explode('|',strtolower($carpconf['filterout']));
		for ($i=count($rss_parser->filterout)-1;$i>=0;$i--) {
			if (strpos($rss_parser->filterout[$i],':')!==false)
				list($rss_parser->filteroutfield[$i],$rss_parser->filterout[$i])=explode(':',$rss_parser->filterout[$i],2);
			else $rss_parser->filteroutfield[$i]='';
		}
	} else $rss_parser->filterout=array();

	$fromfp=0;
	if (substr($url,0,6)=='mysql:') $data=CarpGetCache($url);
	else if (substr($url,0,8)=='grouper:') $data=GrouperGetCache(substr($url,8))?$GLOBALS['grouperrawdata']:'';
	else $fromfp=1;
	if ((!$fromfp)||($fp=OpenRSSFeed($url))) {
		if ($fromfp) $data=CarpReadData($fp);
		$data=preg_replace('/^[^<]+/','',$data);
		$encodings_internal=array('ISO-8859-1','UTF-8','US-ASCII');
		$transcodeout=(strlen($carpconf['encodingout'])&&!in_array(strtoupper($carpconf['encodingout']),$encodings_internal))?1:0;
		if (strlen($carpconf['encodingin'])) $encodingin=$carpconf['encodingin'];
		else $encodingin=preg_match("/^<\?xml\b.*?\bencoding=(\"|')(.*?)(\"|')/",$data,$matches)?
			strtoupper($matches[2]):'UTF-8';
		$encodingquestion=0;
		if (!in_array($encodingin,$encodings_internal)) {
			if (function_exists('iconv')) {
				if ($fromfp) {
					while ($temp=CarpReadData($fp)) $data.=$temp;
					fclose($fp);
					$fromfp=0;
				}
				$newencodingin=$transcodeout?'UTF-8':
					(strlen($carpconf['encodingout'])?$carpconf['encodingout']:'ISO-8859-1');
				if ($od=iconv($encodingin,"$newencodingin//TRANSLIT",$data)) {
					$data=preg_replace("/(<\?xml\b.*?\bencoding=)(\"|').*?(\"|')/","\\1\\2$newencodingin\\3",$od);
					$od='';
					$encodingin=$newencodingin;
				} else CarpError('Encoding conversion (iconv) failed. Attempting to use original data...','iconv-failed',0);
			} else {
				$actualencoding=$encodingin;
				$encodingin='UTF-8';
				$encodingquestion=1;
			}
		}

		$xml_parser=xml_parser_create(strtoupper($encodingin));
		if (strlen($carpconf['encodingout']))
			xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING,$transcodeout?'UTF-8':$carpconf['encodingout']);

		xml_set_object($xml_parser,$rss_parser);
		xml_set_element_handler($xml_parser,"startElement","endElement");
		xml_set_character_data_handler($xml_parser,"characterData");
		$CarpRedirs=array();

		$rss_parser->PrepTagPairs($carpconf['desctags']);
		while (strlen($data)||($fromfp&&($data=CarpReadData($fp)))) {
			if (!xml_parse($xml_parser,$data,$fromfp?feof($fp):1)) {
				CarpError("XML error: ".xml_error_string(xml_get_error_code($xml_parser))." at line ".xml_get_current_line_number($xml_parser).
					($encodingquestion?(". This error may be caused by the fact that PHP is unable to process this feed's encoding ($actualencoding), ".
						"and your server does not support the \"iconv\" function, which is necessary to convert it to an encoding that PHP can process."):'').
						$rss_parser->XMLFormatError()
					,'xml-error');
				if ($fromfp) fclose($fp);
				xml_parser_free($xml_parser);
				unset($rss_parser);
				unset($carpconf['rssparser']);
				return;
			}
			$data='';
		}
		if ($fromfp) fclose($fp);

		if ($rss_parser->displaychannel) {
			if (strlen($rss_parser->channelborder[0])) $rss_parser->DoEndChannel($rss_parser->top,$rss_parser->channelborder,$carpconf['bcb'],$carpconf['acb']);
			if (strlen($rss_parser->channelaorder[0])) $rss_parser->DoEndChannel($rss_parser->bottom,$rss_parser->channelaorder,$carpconf['bca'],$carpconf['aca']);
		}
		$data=($showit?($rss_parser->top.$carpconf['bitems']):('cb: :'.$rss_parser->top."\n".'ca: :'.$rss_parser->bottom."\n")).
			$rss_parser->body.
			($showit?($carpconf['aitems'].$rss_parser->bottom.$carpconf['poweredby']):'');
		if ($transcodeout) {
			if (function_exists('iconv')) {
				if ($od=iconv('UTF-8',$carpconf['encodingout'].'//TRANSLIT',$data)) $data=&$od;
				else CarpError('Encoding conversion (iconv) failed. Outputting unconverted data.','iconv-failed',0);
			} else CarpError('Your server does not support the "iconv" function, which is needed to convert CaRP\'s output to '.$carpconf['encodingout'].'. Outputting unconverted data.', 'no-iconv', 0);
		}

		if ($showit) {
			if ($carpconf['shownoitems']&&!$rss_parser->itemcount) CarpOutput($carpconf['noitems']);
			else CarpOutput($data);
			if (isset($rss_parser)&&!$rss_parser->itemcount) $rss_parser->XMLFormatError(1);
		}
		if ($cache) {
			if (($cfp=OpenCacheWrite())>0) {
				if ($carpconf['shownoitems']&&!$rss_parser->itemcount) CarpSaveCache($cfp,$carpconf['noitems']);
				else CarpSaveCache($cfp,$data);
				CloseCacheWrite($cfp);
			}
		}
		xml_parser_free($xml_parser);
		unset($carpconf['rssparser']);
	} else if ($showit&&strlen($carpconf['cachefile'])) CarpOutput(CarpGetCache($carpconf['cachefile']));
	else if ($showit) CarpError('Can\'t open remote newsfeed.','feed-access-failed',0);
}

return;
?>
