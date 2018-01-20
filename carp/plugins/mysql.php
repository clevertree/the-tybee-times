<?php
/*
mySQL Plugin 1.0.2
A plugin for CaRP Evolution
Stores RSS data in a mySQL database, making it available to other applications
(c) 2004-6 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function MysqlCarpReset() {
	global $mysqlcarpconf;
	$mysqlcarpconf=array(
		'connect'=>0,
		'username'=>'',
		'password'=>'',
		'host'=>'',
		'dbname'=>'',
		'closeconnection'=>0,
		'connection'=>FALSE,
		'feedtable'=>'rssfeeds',
		'itemtable'=>'rssitems',
		'feed_id'=>0,
		'savefields'=>array(
			'title'=>'title',
			'url'=>'url',
			'author'=>'author',
			'date'=>'posted',
			'desc'=>'descr',
			'imageurl'=>'imageurl',
			'imageheight'=>'imageheight',
			'imagewidth'=>'imagewidth',
			'imagelink'=>'imagelink',
			'imagetitle'=>'imagetitle',
			'guid'=>'guid'
		),
		'datefields'=>array(
			'posted'=>1,
		),
		'numberfields'=>array(
			'imageheight'=>1,
			'imagewidth'=>1,
		),
		'checksumfields'=>array('title','date','desc'),
		'duplicatefields'=>array(
			array('guid'),
			array('url','title')
		),
		'auxfields'=>array(
			'guid'=>'guid',
			'feed_id'=>'feed_id',
			'item_id'=>'item_id',
			'checksum'=>'checksum',
			'updated'=>'updated',
			'url'=>'url'
		)
	);
	CarpUnregisterCallback('','MysqlCarpStore');
	CarpUnregisterCallback('','MysqlCarpInitialize');
	CarpUnregisterCallback('','MysqlCarpFinish');
	CarpRegisterCallback('','MysqlCarpStore','displayitem');
	CarpRegisterCallback('','MysqlCarpInitialize','startprocessing');
	CarpRegisterCallback('','MysqlCarpFinish','endprocessing');
	CarpMapField('guid','guid');
}

MysqlCarpReset();

function MysqlCarpConf($field,$value) {
	global $mysqlcarpconf;
	if (isset($mysqlcarpconf[$field])) $mysqlcarpconf[$field]=$value;
	else CarpError("mySQL plugin: unknown configuration field: $field",'unknown-option',0);
}

function MysqlCarpRegisterField($internal_name,$database_field) {
	global $mysqlcarpconf;
	$mysqlcarpconf['savefields'][$internal_name]=$database_field;
}

function MysqlCarpUnRegisterField($internal_name) {
	global $mysqlcarpconf;
	if (isset($mysqlcarpconf['savefields'][$internal_name])) unset($mysqlcarpconf['savefields'][$internal_name]);
}

function MysqlCarpBuildDupQuery(&$f,&$p,&$fields,$checklen=1) {
	$q='';
	for ($i=count($f)-1;$i>=0;$i--) {
		if (strlen($v=$p->GetFieldValue('mysqlcarp'.$f[$i]))) $q.='&&('.$fields[$f[$i]].'="'.addslashes($v).'")';
		else if ($checklen) break;
	}
	return ($i<0)?$q:'';
}

function MysqlCarpStore($itemindex,$itemnumber) {
	global $mysqlcarpconf,$carpconf;
	
	if (($mysqlcarpconf['connection']===FALSE)||!$mysqlcarpconf['feed_id']) return 1;

	$p=$carpconf['rssparser'];
	$guid=$p->GetFieldValue('guid');
	$date=CarpDecodeDate($p->GetFieldValue('date'));
	$title=$p->GetFieldValue('title');
	
	$new=1;
	$updated=0;
	$q=$qt=$old='';
	$fields=&$mysqlcarpconf['savefields'];
	$auxfields=&$mysqlcarpconf['auxfields'];
	foreach ($mysqlcarpconf['duplicatefields'] as $a) {
		if (strlen($q=MysqlCarpBuildDupQuery($a,$p,$fields))) break;
	}

	if (!strlen($q)) $q=MysqlCarpBuildDupQuery($mysqlcarpconf['duplicatefields'][count($mysqlcarpconf['duplicatefields'])-1],$p,$fields,0);
	if (strlen($q)) {
		if ($r=MysqlCarpDoQuery('select '.$auxfields['checksum'].' as checksum,'.$auxfields['item_id'].' as item_id from '.$mysqlcarpconf['itemtable'].' where ('.$auxfields['feed_id'].'="'.$mysqlcarpconf['feed_id'].'")'.$q)) {
			if (mysql_num_rows($r)) {
				$new=0;
				$old=mysql_fetch_array($r);
			}
			mysql_free_result($r);
		} else CarpError('mySQL plugin: Database error while attempting to check for duplicate item','database-error',0);
	}
	
	$q='';
	$checksum='';
	for ($i=count($mysqlcarpconf['checksumfields'])-1;$i>=0;$i--) {
		$checksum.=$p->GetFieldValue($mysqlcarpconf['checksumfields'][$i]);
	}
	$checksum=md5($checksum);
	$date=CarpDecodeDate($p->GetFieldValue('date'));
	if (!$date) $date=$mysqlcarpconf['runtime'];
	if ($new) {
		$q='insert into '.$mysqlcarpconf['itemtable'].' set new=1,'.$auxfields['feed_id'].'="'.$mysqlcarpconf['feed_id'].'",';
	} else if (is_array($old)) {
		if ($old['checksum']!=$checksum) {
			$q='update '.$mysqlcarpconf['itemtable'].' set ';
			$qt=' where item_id="'.$old['item_id'].'"';
		}
	}
	if (strlen($q)) {
		$q.=$auxfields['updated'].'='.$mysqlcarpconf['runtime'].','.$auxfields['checksum']."=\"$checksum\"";
		foreach ($fields as $k=>$v) {
			$fv=$p->GetFieldValue($k);
			if ($mysqlcarpconf['datefields'][$v]) $fv=CarpDecodeDate($fv);
			else if ($mysqlcarpconf['numberfields'][$v]) $fv+=0;
			$q.=",$v=\"".addslashes($fv).'"';
		}
		if (!MysqlCarpDoQuery($q.$qt)) CarpError('mySQL plugin: Database error while attempting to store item','database-error',0);
	}
	return 1;
}

function MysqlCarpInitialize($functionname,$url) {
	global $mysqlcarpconf;
	$mysqlcarpconf['runtime']=time();
	if ($functionname=='CarpAggregate') return;
	if ($mysqlcarpconf['connect']&&($mysqlcarpconf['connection']===FALSE))
		$mysqlcarpconf['connection']=mysql_connect($mysqlcarpconf['host'],$mysqlcarpconf['username'],$mysqlcarpconf['password']);
	if ($mysqlcarpconf['connection']===FALSE) CarpError('mySQL plugin: Database not connected or unable to open connection','database-not-connected',0);
	else {
		if (strlen($mysqlcarpconf['dbname'])) mysql_select_db($mysqlcarpconf['dbname']);
		$auxfields=&$mysqlcarpconf['auxfields'];
		if ($r=MysqlCarpDoQuery('select '.$auxfields['feed_id'].' from '.$mysqlcarpconf['feedtable'].' where '.$auxfields['url'].'="'.md5($url).'"')) {
			if (mysql_num_rows($r)) $mysqlcarpconf['feed_id']=mysql_result($r,0);
			else {
				if (mysql_query('insert into '.$mysqlcarpconf['feedtable'].' set url="'.md5($url).'"')) {
					$mysqlcarpconf['feed_id']=$mysqlcarpconf['connection']?mysql_insert_id($mysqlcarpconf['connection']):mysql_insert_id();
				} else CarpError('mySQL plugin: Database error while attempting to create database record for newsfeed','database-error',0);
			}
			mysql_free_result($r);
		} else CarpError('mySQL plugin: Database error while attempting to check for existing record for newsfeed','database-error',0);
		if ($mysqlcarpconf['feed_id']) {
			$donefields=array();
			foreach ($mysqlcarpconf['duplicatefields'] as $a) {
				for ($i=count($a)-1;$i>=0;$i--) {
					if (!isset($donefields[$a[$i]])) {
						$donefields[$a[$i]]=1;
						CarpMapField('mysqlcarp'.$a[$i],$a[$i]);
					}
				}
			}
		}	
	}
}

function MysqlCarpFinish($functionname) {
	global $mysqlcarpconf;
	if ($functionname=='CarpAggregate') return;
	if ($mysqlcarpconf['connection']!==FALSE) {
		if ($mysqlcarpconf['closeconnection']) {
			if ($mysqlcarpconf['connection']) mysql_close($mysqlcarpconf['connection']);
			else mysql_close();
			$mysqlcarpconf['connection']=FALSE;
		}
	}
}

function MysqlCarpDoQuery($q) {
	global $mysqlcarpconf;
	return $mysqlcarpconf['connection']?mysql_query($q,$mysqlcarpconf['connection']):mysql_query($q);
}

return;
?>