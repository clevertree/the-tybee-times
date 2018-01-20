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

$carpversion='3.6.7Evolution';

function CarpConfReset() {
	global $carpcallbacks;
	global $carpconf,$carpcafields,$carpcapriority,$carpiafields,$carpiapriority;
	$carpconf=array(

	'cache-method'=>'file',
	
	'cachepath'=>'',
	'cacherelative'=>1,
	
	'mysql-database'=>'carpcache',
	'mysql-tables'=>array('carpaggregatecache','carpmanualcache','carpautocache'),
	'mysql-connect'=>0,
	'mysql-select-db'=>0,
	'mysql-connection'=>FALSE,
	'mysql-host'=>'localhost',
	'mysql-username'=>'',
	'mysql-password'=>'',
	'mysql-lock-timeout'=>10,
	
	'cacheinterval'=>60,
	'cacheerrorwait'=>30,
	'cachetime'=>'',
	
	'carperrors'=>2,
	'phperrors'=>0,
	'fixentities'=>0,

	'basicauth'=>'',
	'proxyauth'=>'',
	'proxyserver'=>'',

	'filterin'=>'',
	'filterout'=>'',
	'skipdups'=>1,

	'descriptiontags'=>'b|/b|i|/i|br|p|/p|hr|span|/span|font|/font',
	'linktarget'=>0,

	// replaced by cborder, bcb and acb - these will be removed eventually
	'corder'=>false,
	'bc'=>false,
	'ac'=>false,
	
	'cborder'=>'image,link,desc',
	'bcb'=>'',
	'acb'=>'<br />',
	'caorder'=>'',
	'bca'=>'',
	'aca'=>'<br />',
	'bcurl'=>'',
	'acurl'=>'<br />',
	'bctitle'=>'',
	'actitle'=>'<br />',
	'clinkclass'=>'',
	'clinkstyle'=>'',
	'clinktitles'=>1,
	'maxctitle'=>80,
	'atruncctitle'=>'...',
	'bcdate'=>'<i>',
	'acdate'=>'</i><br />',
	'cdateformat'=>'j M Y \a\t g:ia',
	'bcdesc'=>'',
	'acdesc'=>'<br />',
	'maxcdesc'=>0,
	'atrunccdesc'=>'...',
	'bcimage'=>'',
	'acimage'=>'<br />',
	'maxcimagew'=>144,
	'maxcimageh'=>400,
	'defcimagew'=>0,
	'defcimageh'=>0,
	'setcimagew'=>0,
	'setcimageh'=>0,

	'maxitems'=>15,
	'noitems'=>'No news items found',
	'shownoitems'=>0,
	'iorder'=>'image,link,author,date,desc,podcast',
	'bitems'=>'',
	'aitems'=>'',
	'bi'=>'',
	'ai'=>'',
	'biurl'=>'',
	'aiurl'=>'<br />',
	'bilink'=>'',
	'ailink'=>'<br />',
	'ilinkclass'=>'',
	'ilinkstyle'=>'',
	'ilinktitles'=>1,
	'defaultititle'=>'(no title)',
	'maxititle'=>80,
	'atruncititle'=>'...',
	'biauthor'=>'<i>by ',
	'aiauthor'=>'</i><br />',
	'bidate'=>'<i>',
	'aidate'=>'</i><br />',
	'idateformat'=>'j M Y \a\t g:ia',
	'timeoffset'=>0,
	'bidesc'=>'',
	'aidesc'=>'<br />',
	'maxidesc'=>0,
	'atruncidesc'=>'...',
	'biimage'=>'',
	'aiimage'=>'<br />',
	'maxiimagew'=>144,
	'maxiimageh'=>400,
	'defiimagew'=>0,
	'defiimageh'=>0,
	'setiimagew'=>0,
	'setiimageh'=>0,
	'bipodcast'=>'<a href="',
	'aipodcast'=>'">Listen</a><br />',

	'maxgroupfilter'=>3,
	'encodingin'=>'',
	'encodingout'=>'ISO-8859-1',
	'maxredir'=>10,
	'timeout'=>15,
	'sendhost'=>1,
	'removebadtags'=>1,
	'outputformat'=>0,
	
	'cachefunctions'=>array('CarpAggregatePath','CarpCachePath','CarpAutoCachePath'),
	
	'prefix-map'=>array(),

	// the following are both replaced by linktarget and will be removed eventually
	'clinktarget'=>-1,
	'ilinktarget'=>-1,

	/* If you change poweredby, we'd appreciate a link from elsewhere on your
	site. If you incorporate CaRP into another product and change it, please
	ensure that a link is shown by default somewhere. Thanks! */

	'poweredby'=>'<br /><i><a href="http://www.geckotribe.com/rss/carp/" target="_blank">Newsfeed display by CaRP</a></i>'
	);
	
	$carpcafields=array(
		'TITLE'=>array('TITLE'=>1),
		'DESCRIPTION'=>array('DESC'=>1),
		'LINK'=>array('URL'=>1),
		'LASTBUILDDATE'=>array('DATE'=>1),
		'PUBDATE'=>array('DATE'=>1),
		'DC:DATE'=>array('DATE'=>1),
		'IMAGE^URL'=>array('IMAGEURL'=>1),
		'IMAGE^TITLE'=>array('IMAGEALT'=>1),
		'IMAGE^LINK'=>array('IMAGELINK'=>1),
		'IMAGE^WIDTH'=>array('IMAGEWIDTH'=>1),
		'IMAGE^HEIGHT'=>array('IMAGEHEIGHT'=>1),

		'IMAGE:ITEM^RDF:ABOUT'=>array('IMAGEURL'=>1),
		'IMAGE:ITEM^RDF:RESOURCE'=>array('IMAGELINK'=>1),
		'IMAGE:ITEM^DC:TITLE'=>array('IMAGEALT'=>1),
		'IMAGE:ITEM^IMAGE:LINK'=>array('IMAGELINK'=>1),
		'IMAGE:ITEM^IMAGE:WIDTH'=>array('IMAGEWIDTH'=>1),
		'IMAGE:ITEM^IMAGE:HEIGHT'=>array('IMAGEHEIGHT'=>1),

		'MEDIA:THUMBNAIL^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:THUMBNAIL^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:THUMBNAIL^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),

		'ENCLOSURE^URL'=>array('IMAGEURL'=>1),
		'ENCLOSURE^TYPE'=>array('IMAGEURL'=>1),
	);
	$carpcapriority=array(
		'TITLE'=>array('TITLE'=>5),
		'DESC'=>array('DESCRIPTION'=>5),
		'URL'=>array('LINK'=>5),
		'DATE'=>array('LASTBUILDDATE'=>5,'PUBDATE'=>4,'DC:DATE'=>3),
		'IMAGEURL'=>array('IMAGE^URL'=>5,'IMAGE:ITEM^RDF:ABOUT'=>3,'ENCLOSURE^URL'=>array(2,'ENCLOSURE^TYPE','#^image/#i'),
			'MEDIA:THUMBNAIL^URL'=>1),
		'IMAGEALT'=>array('IMAGE^TITLE'=>5,'IMAGE:ITEM^DC:TITLE'=>3),
		'IMAGELINK'=>array('IMAGE^LINK'=>5,'IMAGE:ITEM^RDF:RESOURCE'=>3,'IMAGE:ITEM^IMAGE:LINK'=>2),
		'IMAGEWIDTH'=>array('IMAGE^WIDTH'=>5,'IMAGE:ITEM^IMAGE:WIDTH'=>3,'MEDIA:THUMBNAIL^WIDTH'=>1),
		'IMAGEHEIGHT'=>array('IMAGE^HEIGHT'=>5,'IMAGE:ITEM^IMAGE:HEIGHT'=>3,'MEDIA:THUMBNAIL^HEIGHT'=>1)
	);
	$carpiafields=array(
		'TITLE'=>array('TITLE'=>1),
		'DESCRIPTION'=>array('DESC'=>1),
		'CONTENT:ENCODED'=>array('DESC'=>1),
		'LINK'=>array('URL'=>1),
		'GUID'=>array('URL'=>1),
		'IMAGE^URL'=>array('IMAGEURL'=>1),
		'IMAGE^TITLE'=>array('IMAGEALT'=>1),
		'IMAGE^LINK'=>array('IMAGELINK'=>1),
		'IMAGE^WIDTH'=>array('IMAGEWIDTH'=>1),
		'IMAGE^HEIGHT'=>array('IMAGEHEIGHT'=>1),
		
		'IMAGE:ITEM^RDF:ABOUT'=>array('IMAGEURL'=>1),
		'IMAGE:ITEM^RDF:RESOURCE'=>array('IMAGELINK'=>1),
		'IMAGE:ITEM^DC:TITLE'=>array('IMAGEALT'=>1),
		'IMAGE:ITEM^IMAGE:LINK'=>array('IMAGELINK'=>1),
		'IMAGE:ITEM^IMAGE:WIDTH'=>array('IMAGEWIDTH'=>1),
		'IMAGE:ITEM^IMAGE:HEIGHT'=>array('IMAGEHEIGHT'=>1),

		'ENCLOSURE^URL'=>array('IMAGEURL'=>1,'PODCAST'=>1),
		'ENCLOSURE^TYPE'=>array('IMAGEURL'=>1,'PODCAST'=>1),
		
		'MEDIA:CONTENT^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:CONTENT^TYPE'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:CONTENT^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:CONTENT^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),
		
		'MEDIA:GROUP^MEDIA:CONTENT^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:GROUP^MEDIA:CONTENT^TYPE'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:GROUP^MEDIA:CONTENT^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:GROUP^MEDIA:CONTENT^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),
		
		'MEDIA:THUMBNAIL^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:THUMBNAIL^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:THUMBNAIL^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),
		
		'MEDIA:CONTENT^MEDIA:THUMBNAIL^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:CONTENT^MEDIA:THUMBNAIL^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:CONTENT^MEDIA:THUMBNAIL^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),
		
		'MEDIA:GROUP^MEDIA:THUMBNAIL^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:GROUP^MEDIA:THUMBNAIL^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:GROUP^MEDIA:THUMBNAIL^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),
		
		'MEDIA:GROUP^MEDIA:CONTENT^MEDIA:THUMBNAIL^URL'=>ARRAY('IMAGEURL'=>1),
		'MEDIA:GROUP^MEDIA:CONTENT^MEDIA:THUMBNAIL^WIDTH'=>ARRAY('IMAGEWIDTH'=>1),
		'MEDIA:GROUP^MEDIA:CONTENT^MEDIA:THUMBNAIL^HEIGHT'=>ARRAY('IMAGEHEIGHT'=>1),
		
		'PUBDATE'=>array('DATE'=>1),
		'DC:DATE'=>array('DATE'=>1),
		'AUTHOR'=>array('AUTHOR'=>1),
		'DC:CREATOR'=>array('AUTHOR'=>1)
	);
	$carpiapriority=array(
		'TITLE'=>array('TITLE'=>5),
		'DESC'=>array('DESCRIPTION'=>5,'CONTENT:ENCODED'=>3),
		'URL'=>array('LINK'=>5,'GUID'=>array(3,'GUID^ISPERMALINK','#true#i'),'GUID'=>array(2,'GUID^ISPERMALINK','')),
		'IMAGEURL'=>array('IMAGE^URL'=>5,'IMAGE:ITEM^RDF:ABOUT'=>4,'ENCLOSURE^URL'=>array(3,'ENCLOSURE^TYPE','#^image/#i'),
			'MEDIA:GROUP^MEDIA:CONTENT^MEDIA:THUMBNAIL^URL'=>2,'MEDIA:CONTENT^MEDIA:THUMBNAIL^URL'=>1,
			'MEDIA:GROUP^MEDIA:THUMBNAIL^URL'=>0,'MEDIA:THUMBNAIL^URL'=>-1,
			'MEDIA:GROUP^MEDIA:CONTENT^URL'=>array(-2,'MEDIA:GROUP^MEDIA:CONTENT^TYPE','#^image/#i'),
			'MEDIA:CONTENT^URL'=>array(-3,'MEDIA:CONTENT^TYPE','#^image/#i')),
		'IMAGEALT'=>array('IMAGE^TITLE'=>5,'IMAGE:ITEM^DC:TITLE'=>3),
		'IMAGELINK'=>array('IMAGE^LINK'=>5,'IMAGE:ITEM^RDF:RESOURCE'=>3,'IMAGE:ITEM^IMAGE:LINK'=>2),
		'IMAGEWIDTH'=>array('IMAGE^WIDTH'=>5,'IMAGE:ITEM^IMAGE:WIDTH'=>3,
			'MEDIA:GROUP^MEDIA:CONTENT^MEDIA:THUMBNAIL^WIDTH'=>2,'MEDIA:CONTENT^MEDIA:THUMBNAIL^WIDTH'=>1,
			'MEDIA:GROUP^MEDIA:THUMBNAIL^WIDTH'=>0,'MEDIA:THUMBNAIL^WIDTH'=>-1,
			'MEDIA:GROUP^MEDIA:CONTENT^WIDTH'=>array(-2,'MEDIA:GROUP^MEDIA:CONTENT^TYPE','#^image/#i'),
			'MEDIA:CONTENT^WIDTH'=>array(-3,'MEDIA:CONTENT^TYPE','#^image/#i')),
		'IMAGEHEIGHT'=>array('IMAGE^HEIGHT'=>5,'IMAGE:ITEM^IMAGE:HEIGHT'=>3,
			'MEDIA:GROUP^MEDIA:CONTENT^MEDIA:THUMBNAIL^HEIGHT'=>2,'MEDIA:CONTENT^MEDIA:THUMBNAIL^HEIGHT'=>1,
			'MEDIA:GROUP^MEDIA:THUMBNAIL^HEIGHT'=>0,'MEDIA:THUMBNAIL^HEIGHT'=>-1,
			'MEDIA:GROUP^MEDIA:CONTENT^HEIGHT'=>array(-2,'MEDIA:GROUP^MEDIA:CONTENT^TYPE','#^image/#i'),
			'MEDIA:CONTENT^HEIGHT'=>array(-3,'MEDIA:CONTENT^TYPE','#^image/#i')),
		
		'DATE'=>array('PUBDATE'=>5,'DC:DATE'=>3),
		'AUTHOR'=>array('AUTHOR'=>5,'DC:CREATOR'=>3),
		
		'PODCAST'=>array('ENCLOSURE^URL'=>array(5,'ENCLOSURE^TYPE','#^audio/#i'))
	);
	if (defined('CARP_INITED')) $saveresetcallbacks=$carpcallbacks['carpconfreset'];
	else {
		define('CARP_INITED',1);
		$saveresetcallbacks=array();
	}
	$carpcallbacks=array(
		'startelement'=>array(),
		'endelement'=>array(),
		'characterdata'=>array(),
		'outputfield'=>array(),
		'handlefield'=>array(),
		
		'displaychannel'=>array(),
		'displayitem'=>array(),
		'startprocessing'=>array(),
		'endprocessing'=>array(),
		'carpconfreset'=>array(),
		'aggregateitem'=>array()
	);
	foreach ($saveresetcallbacks as $cb)
		call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]));
}

function CarpConf($n,$v) {
	global $carpconf;
	if (is_null($v)) $v='';
	$n=explode('|',strtolower(str_replace(' ','',$n)));
	for ($i=count($n)-1;$i>=0;$i--) {
		if (isset($carpconf[$n[$i]])) $carpconf[$n[$i]]=$v;
		else CarpError("Unknown option ($n[$i]). Please check the spelling of the option name and that the version of CaRP you are using supports this option.",'unknown-option',0);
	}
}

function CarpConfAdd($n,$v,$ba=1) {
	global $carpconf;
	$n=explode('|',strtolower(str_replace(' ','',$n)));
	for ($i=count($n)-1;$i>=0;$i--) {
		if (isset($carpconf[$n[$i]])) {
			if ($ba) $carpconf[$n[$i]].=$v;
			else $carpconf[$n[$i]]=$v.$carpconf[$n[$i]];
		} else CarpError("Unknown option ($n[$i]). Please check the spelling of the option name and that the version of CaRP you are using supports this option.",'unknown-option',0);
	}
}

function CarpConfRemove($n,$v) {
	global $carpconf;
	$n=explode('|',strtolower(str_replace(' ','',$n)));
	for ($i=count($n)-1;$i>=0;$i--) {
		if (isset($carpconf[$n[$i]])) $carpconf[$n[$i]]=str_replace($v,'',$carpconf[$n[$i]]);
		else CarpError("Unknown option ($n[$i]). Please check the spelling of the option name and that the version of CaRP you are using supports this option.",'unknown-option',0);
	}
}

function CarpPrioritySort($a,$b) {
	$na=is_array($a)?$a[0]:$a;
	$nb=is_array($b)?$b[0]:$b;
	return ($na==$nb)?0:(($na>$nb)?-1:1);
}

function CarpMapField($internal,$rss,$ischannel=0,$priority=0) {
	global $carpcafields,$carpcapriority,$carpiafields,$carpiapriority;
	
	$internal=strtoupper($internal);
	$rsse=strtoupper(is_array($rss)?$rss[0]:$rss);
	
	if ($ischannel) {
		$f=&$carpcafields;
		$p=&$carpcapriority;
	} else {
		$f=&$carpiafields;
		$p=&$carpiapriority;
	}
	if (!isset($f["$rsse"])) $f["$rsse"]=array();
	$f["$rsse"]["$internal"]=1;
	if (!isset($p["$internal"])) $p["$internal"]=array();
	if (is_array($rss)) {
		$p["$internal"]["$rsse"]=array($priority);
		$pa=&$p["$internal"]["$rsse"];
		for ($i=1,$j=count($rss);$i<$j;$i+=2) {
			$pa[]=strtoupper($rss[$i]);
			$pa[]=$rss[$i+1];
		}
	} else $p["$internal"]["$rsse"]=$priority;
	uasort($p["$internal"],'CarpPrioritySort');
}

function CarpUnmapField($internal,$rss,$ischannel=0) {
	global $carpcafields,$carpcapriority,$carpiafields,$carpiapriority;
	
	$internal=strtoupper($internal);
	$rss=strtoupper($rss);

	if ($ischannel) {
		$f=&$carpcafields;
		$p=&$carpcapriority;
	} else {
		$f=&$carpiafields;
		$p=&$carpiapriority;
	}
	if (isset($f["$rss"]["$internal"])) {
		unset($f["$rss"]["$internal"]);
		if (!count($f["$rss"])) unset($f["$rss"]);
	}
	if (isset($p["$internal"]["$rss"])) {
		if (is_array($p["$internal"]["$rss"])) {
			$conditions=&$p["$internal"]["$rss"];
			for ($i=1,$j=count($conditions);$i<$j;$i+=2)
				if (isset($f[$conditions[$i]])&&isset($f[$conditions[$i]]["$internal"])) {
					unset($f[$conditions[$i]]["$internal"]);
					if (!count($f[$conditions[$i]])) unset($f[$conditions[$i]]);
				}
		}
		unset($p["$internal"]["$rss"]);
	}
}

function CarpMapPrefix($from='',$to='') {
	global $carpconf;
	if (strlen($from.$to)) $carpconf['prefix-map'][strtoupper($from)]=strtoupper($to);
	else $carpconf['prefix-map']=array();
}

function CarpDirName() { return str_replace("\\","/",dirname(__file__)); }

function CarpLoadTheme($name) {
	if (file_exists($fn=dirname(__file__)."/themes/$name")) require $fn;
	else CarpError("Theme \"$name\" not found in CaRP themes directory",'theme-not-found',0);
}

function CarpLoadPlugin($name) {
	if (file_exists($fn=CarpDirName()."/plugins/$name")) require_once $fn;
	else CarpError("Plugin \"$name\" not found in CaRP plugins directory",'plugin-not-found');
}

function CarpRegisterCallback($object,$function,$phase,$o1='') {
	global $carpcallbacks,$carpconf;
	$phase=strtolower($phase);
	
	if ($object!=='') {
		if (!get_class($object)===false) {
			CarpError("CarpRegisterCallback: first parameter is not an object",'register-callback-object',0);
			return;
		}
		if (!method_exists($object,$function)) {
			CarpError("CarpRegisterCallback: method $method does not exist",'register-callback-invalid-method',0);
			return;
		}
	} else if (!function_exists($function)) {
		CarpError("CarpRegisterCallback: Invalid function name: $function",'register-callback-invalid-function',0);
		return;
	}
	
	switch ($phase) {
	case 'handlefield':
		if (!strlen($o1)) {
			CarpError("CarpRegisterCallback: Field not specified ($phase)",'register-callback-need-option',0);
			return;
		}
		$o1=strtolower($o1);
		// don't break
	case 'startelement':
	case 'endelement':
	case 'characterdata':
	case 'outputfield':
		$carpcallbacks[$phase][]=array($object,$function,$o1);
		break;

	case 'displaychannel':
	case 'displayitem':
	case 'startprocessing':
	case 'endprocessing':
	case 'carpconfreset':
	case 'aggregateitem':
		$carpcallbacks[$phase][]=array($object,$function);
		break;

	default: CarpError("CarpRegisterCallback: Unknown phase ($phase)",0);
	}
}

function CarpUnregisterCallback($object,$function,$phase='',$o1='') {
	global $carpcallbacks;
	$phases=strlen($phase)?array($phase):
		array('startelement','endelement','characterdata','outputfield','displaychannel','displayitem','startprocessing','endprocessing','carpconfreset','aggregateitem');
	foreach ($phases as $phase) {
		if (isset($carpcallbacks[$phase])) {
			for ($i=count($carpcallbacks[$phase])-1;$i>=0;$i--) {
				if (($carpcallbacks[$phase][$i][0]==$object)&&($carpcallbacks[$phase][$i][1]==$function)) {
					if ((!strlen($o1))||(isset($carpcallbacks[$phase][$i][2])&&($o1==$carpcallbacks[$phase][$i][2])))
						array_splice($carpcallbacks[$phase],$i,1);
				}
			}
		}
	}
}

function CarpOutput($t) {
	global $carpconf,$carpoutput;

	if (is_array($t)) { for ($i=0,$j=count($t);$i<$j;$i++) $t[$i]=ereg_replace("&apos;","'",$t[$i]); }
	else $t=ereg_replace("&apos;","'",$t);
	switch ($carpconf['outputformat']) {
	case 1:
		if (!is_array($t)) $t=explode("\n",preg_replace("/[\r\n]+/","\n",$t));
		for ($i=0,$j=count($t);$i<$j;$i++) echo 'document.writeln("'.str_replace('"','\"',trim(str_replace("\r",' ',$t[$i])))."\");\n";
  		break;
	case 2:
		if (is_array($t)) for ($i=0,$j=count($t);$i<$j;$i++) $carpoutput.=$t[$i];
		else $carpoutput.=$t;
		break;
	default:
		if (is_array($t)) for ($i=0,$j=count($t);$i<$j;$i++) echo $t[$i];
		else echo $t;
	}
}

function CarpError($s,$link='',$c=1) {
	global $carpconf;
	if ($carpconf['carperrors']) {
		$link=preg_replace('#[^-a-zA-Z0-9/]#','',$link);
		if (($carpconf['carperrors']==1)||!strlen($link)) CarpOutput("<br />\n[CaRP] $s<br />\n");
		else  CarpOutput("<br />\n[CaRP] <a target=\"_blank\" href=\"http://carp.docs.geckotribe.com/errors/$link.php\">$s</a><br />\n");
	}
	if ($c&&$carpconf['cacheerrorwait']&&isset($carpconf['cachefile'])&&strlen($carpconf['cachefile']))
		CarpTouchCache($carpconf['cachefile'],time()+60*($carpconf['cacheerrorwait']-$carpconf['cacheinterval']),0);
}

function CarpMySQLQuery($q) {
	global $carpconf;
	return $carpconf['mysql-connection']?mysql_query($q,$carpconf['mysql-connection']):mysql_query($q);
}

function CarpCacheMysqlConnect() {
	global $carpconf;
	if (!isset($carpconf['mysql-database-name'])) $carpconf['mysql-database-name']='';
	if ($carpconf['mysql-connection']===FALSE) {
		$carpconf['mysql-connection']=$carpconf['mysql-connect']?
			mysql_connect($carpconf['mysql-host'],$carpconf['mysql-username'],$carpconf['mysql-password']):0;
		if ($carpconf['mysql-connection']===FALSE) CarpError('Database not connected or unable to open connection','database-not-connected');
		else if ($carpconf['mysql-select-db']) {
			$carpconf['mysql-database-name']='';
			if ($carpconf['mysql-connection']) mysql_select_db($carpconf['mysql-database'],$carpconf['mysql-connection']);
			else mysql_select_db($carpconf['mysql-database']);
		} else if (!empty($carpconf['mysql-database'])) $carpconf['mysql-database-name']=$carpconf['mysql-database'].'.';
	}
}

function CarpParseMySQLPath($path,&$which,&$key) {
	$rv=0;
	if (preg_match('#^mysql:([^/]+)/(.*)$#',$path,$m)) {
		switch($m[1]) {
		case 'aggregatecache': $which=0; break;
		case 'manualcache': $which=1; break;
		case 'autocache': $which=2; break;
		default: CarpError('Invalid mySQL cache database specified.','database-invalid-table'); return;
		}
		$key=$m[2];
		$rv=1;
	}
	return $rv;
}

function CarpDataPath() {
	global $carpconf;
	$rv='';
	switch($carpconf['cache-method']) {
	case 'mysql': $rv='mysql:'; break;
	default: $rv=preg_replace("#/{2,}#",'/',($carpconf['cacherelative']?(CarpDirName().'/'):'').$carpconf['cachepath'].(strlen($carpconf['cachepath'])?'/':''));
	}
	return $rv;
}

function CarpAggregatePath() { return CarpDataPath().'aggregatecache/'; }
function CarpCachePath() { return CarpDataPath().'manualcache/'; }
function CarpAutoCachePath() { return CarpDataPath().'autocache/'; }

function CarpClearCache($which,$stale=0) {
	global $carpconf;
	if ($stale) $stale=ceil(time()-($stale*86400));
	switch($carpconf['cache-method']) {
	case 'mysql': CarpCacheMysqlConnect();
		CarpMySQLQuery('delete from '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].($stale?(" where updated<$stale"):''));
		break; 
	default:
		if ($d=opendir($dir=call_user_func($carpconf['cachefunctions'][$which]))) {
			while (false!==($fn=readdir($d))) if (($fn!='.')&&($fn!='..')) {
				if ($stale) {
					$s=stat($dir.$fn);
					if ($s['mtime']<$stale) unlink($dir.$fn);
				} else unlink($dir.$fn);
			}
		} else CarpError('Unable to access cache folder.','cache-folder-access',0);
	}
}

function CarpClearCacheFile($which,$filename) {
	global $carpconf;
	switch($carpconf['cache-method']) {
	case 'mysql': CarpCacheMysqlConnect();
		CarpMySQLQuery('delete from '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' where id="'.(($which==2)?md5($filename):addslashes($filename)).'"');
		break; 
	default: unlink(call_user_func($carpconf['cachefunctions'][$which]).(($which==2)?md5($filename):$filename)); break; 
	}
}

function CarpGetCacheUpdated($fn) {
	global $carpconf;
	$rv=0;
	if (CarpParseMySQLPath($fn,$which,$key)) {
		CarpCacheMysqlConnect();
		if ($r=CarpMySQLQuery('select updated from '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' where id="'.$key.'"')) {
			if (mysql_num_rows($r)) $rv=mysql_result($r,0)+0;
			mysql_free_result($r);
		} else CarpError('Database error while checking when the cache was last updated.','database-error'); 
	} else if (file_exists($fn)) $rv=filemtime($fn);
	return $rv;
}

function CarpSetCache($cachefile,$cachefunction=1) {
	global $carpconf;
	$cache=0;
	$cachefile=preg_replace("/\.+/",'.',$cachefile);
	$carpconf['cachefile']=call_user_func($carpconf['cachefunctions'][$cachefunction]).$cachefile;
	if ($carpconf['mtime']=CarpGetCacheUpdated($carpconf['cachefile'])) {
		$nowtime=time();
		if (strlen($carpconf['cachetime'])) {
			list($hour,$min)=explode(':',$carpconf['cachetime']);
			$limtime=mktime($hour,$min,0);
			$cache=($carpconf['mtime']>$limtime-(($nowtime<$limtime)?86400:0))?1:2;
		} else $cache=(($nowtime-$carpconf['mtime'])<($carpconf['cacheinterval']*60))?1:2;
	} else $cache=2;
	return $cache;
}

function CarpGetCache($fn) {
	global $carpconf;
	$rv='';
	if (CarpParseMySQLPath($fn,$which,$key)) {
		CarpCacheMysqlConnect();
		if ($r=CarpMySQLQuery('select cache from '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' where id="'.addslashes($key).'"')) {
			if (mysql_num_rows($r)) $rv=mysql_result($r,0);
			else CarpError('Cache record not found.','cache-not-found');
			mysql_free_result($r);
		} else CarpError('Database error attempting to retrieve cache record.','database-error');
	} else if (file_exists($fn)) {
		$temp=file($fn);
		$rv=is_array($temp)?implode('',$temp):$temp;
	} else CarpError('Cache file not found.','cache-not-found');
	return $rv;
}

function CarpTouchCache($fn,$time=0,$errors=1) {
	global $carpconf;
	if (!$time) $time=time();
	else $time+=0;
	if (CarpParseMySQLPath($fn,$which,$key)) {
		CarpCacheMysqlConnect();
		if ($r=CarpMySQLQuery('select cache from '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which].' where id="'.addslashes($key).'"')) {
			if (mysql_num_rows($r)) CarpMySQLQuery('update '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which]." set updated=\"$time\" where id=\"".addslashes($key).'"');
			else CarpMySQLQuery('insert into '.$carpconf['mysql-database-name'].$carpconf['mysql-tables'][$which]." set updated=\"$time\",id=\"".addslashes($key).'"');
		} else if ($errors) CarpError('Database error attempting to set update time on cache record.','database-error');
	} else touch($fn,$time); 
}

function CarpCacheFilter($url,$cachefile) { CarpCacheShow($url,$cachefile,0); }
function CarpFilter($url,$cachefile) { CarpShow($url,$cachefile,0); }
function CarpGroupFilter($feeds) {
	global $carpconf;
	$cachelist='';
	if (!is_array($feeds)) CarpError('The argument to CarpGroupFilter must be an array of URL=&gt;cache-file pairs.','group-filter-arg');
	else {
		$r=0;
		$aggpath=call_user_func($carpconf['cachefunctions'][0]);
		$autopath=call_user_func($carpconf['cachefunctions'][2]);
		$oldencodingin=$carpconf['encodingin'];
		$resetencoding=0;
		foreach ($feeds as $k=>$v) {
			if (is_array($v)) {
				$resetencoding=1;
				list($v,$encodingin)=$v;
				CarpConf('encodingin',$encodingin);
			}
			$cachelist.="|$v";
			if (($r<$carpconf['maxgroupfilter'])||(!$carpconf['maxgroupfilter'])||(!CarpGetCacheUpdated("$aggpath$v"))) {
				$r+=CarpCache($k,$autocache=md5($k),2);
				CarpShow($autopath.$autocache,$v,0);
			}
			if ($resetencoding) {
				$resetencoding=0;
				CarpConf('encodingin',$oldencodingin);
			}
		}
	}
	return substr($cachelist,1);
}

function CarpCacheShow($url,$cachefile='',$showit=1) {
	global $carpconf;
	$rv=CarpCache($url,$autocache=md5($url),2);
	CarpShow(call_user_func($carpconf['cachefunctions'][2]).$autocache,$cachefile,$showit);
	return $rv;
}

function CarpPHPErrors($set) {
	global $carpconf;
	if ($carpconf['phperrors']>=0) {
		if ($set) {
			$carpconf['savephperrors']=error_reporting($carpconf['phperrors']);
			$carpconf['savedisplayerrors']=ini_set('display_errors',1);
		} else {
			ini_set('display_errors',$carpconf['savedisplayerrors']);
			error_reporting($carpconf['savephperrors']);
		}
	}
}

function CarpShow($url,$cachefile='',$showit=1) {
	global $carpconf,$carpoutput;
	global $carpcallbacks;

	CarpPHPErrors(1);
	$carpoutput='';
	$cache=0;
	if (strlen($cachefile)) $cache=CarpSetCache($cachefile,$showit);
	else if (!$showit) {
		CarpError('No cache file indicated when calling CarpFilter or CarpShow with showit=0.','cache-required',0);
		$carpconf['cachefile']='';
		CarpPHPErrors(0);
		return 0;
	}
	foreach ($carpcallbacks['startprocessing'] as $cb)
		call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),($showit?'CarpShow':'CarpFilter'),$url);
	if ($cache%2==0) {
		require_once CarpDirName().'/carpinc.php';
		GetRSSFeed($url,$cache,$showit);
	} else if ($showit) CarpOutput(CarpGetCache($carpconf['cachefile']));
	$carpconf['cachefile']='';
	CarpPHPErrors(0);
	foreach ($carpcallbacks['endprocessing'] as $cb)
		call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),($showit?'CarpShow':'CarpFilter'));
}

function CarpAggregateSort($a,$b) {
	$na=floor($a);
	$nb=floor($b);
	return ($a==$b)?0:(($a>$b)?-1:1);
}

function CarpAggregate($feeds) {
	global $carpconf,$carpoutput;
	global $carpcallbacks;
	CarpPHPErrors(1);
	if ($carpconf['skipdups']) $dups=array();
	foreach ($carpcallbacks['startprocessing'] as $_cb)
		call_user_func(($_cb[0]==='')?$_cb[1]:array($_cb[0],$_cb[1]),'CarpAggregate',$url);
	$carpoutput='';
	$fl=explode('|',$feeds);
	$id=$il=$cb=$ca=$ci=array();
	$j=0;
	for ($i=count($fl)-1;$i>=0;$i--) {
		if ($f=CarpGetCache(call_user_func($carpconf['cachefunctions'][0]).$fl[$i])) {
			if (!is_array($f)) {
				$f=explode("\n",preg_replace("/[\r\n]+/","\n",$f));
				for ($an=count($f)-1;$an>=0;$an--) $f[$an].="\n";
			}
			foreach ($f as $l) if ($l{0}!="\n") {
				list($datetime,$reserved,$data)=explode(':',$l,3);
				switch ($datetime) {
				case 'cb': $cb[$i]=$data; break;
				case 'ca': $ca[$i]=$data; break;
				default:
					if ($carpconf['skipdups']) {
						if (!isset($dups["$reserved"])) $dups["$reserved"]=1;
						else continue;
					}
					$il[$j]=$data;
					$id["$datetime.$j"]=$j;
					$ci[$j]=$i;
					$j++;
				}
			}
		}
	}
	if ($carpconf['shownoitems']&&!count($id)) CarpOutput($carpconf['noitems']);
	else {
		uksort($id,'CarpAggregateSort');
		CarpOutput($carpconf['bitems']);
		for ($i=$j=0;($i<$carpconf['maxitems'])&&(list($k,$v)=each($id));$j++) {
			$continue=1;
			foreach ($carpcallbacks['aggregateitem'] as $_cb)
				switch (call_user_func(($_cb[0]==='')?$_cb[1]:array($_cb[0],$_cb[1]),$j,$i,$k,$v)) {
				case 0: if ($continue>0) $continue=0; break;
				case -1: $continue=-1; break;
				}
			switch ($continue) {
			case 1:
				CarpOutput((isset($cb[$ci[$v]])?$cb[$ci[$v]]:'').$il[$v].(isset($ca[$ci[$v]])?$ca[$ci[$v]]:''));
				$i++;
				break;
			case -1:
				$i=$carpconf['maxitems'];
				break;
			}
		}
		CarpOutput($carpconf['aitems'].$carpconf['poweredby']);
	}
	CarpPHPErrors(0);
	foreach ($carpcallbacks['endprocessing'] as $cb)
		call_user_func(($cb[0]==='')?$cb[1]:array($cb[0],$cb[1]),'CarpAggregate');
}

function CarpCache($url,$cachefile,$cachefunction=1) {
	global $carpconf;
	CarpPHPErrors(1);
	if (strlen($cachefile)) {
		$cache=CarpSetCache($cachefile,$cachefunction);
		if ($cache%2==0) {
			require_once CarpDirName().'/carpinc.php';
			CacheRSSFeed($url);
			$carpconf['cachefile']='';
			CarpPHPErrors(0);
			return 1;
		}
	} else CarpError('No cache file indicated when calling CarpCache.','cache-required',0);
	$carpconf['cachefile']='';
	CarpPHPErrors(0);
	return 0;
}

if (file_exists(CarpDirName()."/carpconf.php")) require_once CarpDirName()."/carpconf.php";
else CarpConfReset();

return;
?>