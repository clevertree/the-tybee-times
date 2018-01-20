<?php
/*
Filter Plugin 1.0.1
A plugin for CaRP Evolution
Providing more complex filtering than is possible with CaRP alone
(c) 2004-6 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function FilterCarpReset() {
	global $filtercarpconf;
	$filtercarpconf=array(
		'encoding'=>'ISO-8859-1',
		'inout'=>'in',
		'in'=>array('','',''),
		'out'=>array('','','')
	);
	$filtercarpconf['current']=&$filtercarpconf['in'];
	$filtercarpconf['heirarchy']=array(&$filtercarpconf['in']);
	CarpUnregisterCallback('','FilterCarpDoFilter');
	CarpRegisterCallback('','FilterCarpDoFilter','displayitem');
}

FilterCarpReset();

function FilterCarpAdd($fields,$pattern,$and_or='and',$operator='contain') {
	global $filtercarpconf;
	$and_or=(($and_or=='and')||!strlen($and_or))?1:0;
	if (substr($operator,0,1)=='!') {
		$not=1;
		$operator=substr($operator,1);
	} else $not=0;
	if (!strlen($operator)) $operator='contain';
	
	$filtercarpconf['current'][]=array(0,$and_or,$not,explode(',',preg_replace('/ /','',$fields)),$pattern,$operator);
}

function FilterCarpStartGroup($and_or='or',$not=0) {
	global $filtercarpconf;
	$filtercarpconf['current'][]=array(1,($and_or=='and')?1:0,$not);
	$filtercarpconf['current']=&$filtercarpconf['current'][count($filtercarpconf['current'])-1];
	$filtercarpconf['heirarchy'][]=&$filtercarpconf['current'];
}

function FilterCarpEndGroup() {
	global $filtercarpconf;
	if (($hc=count($filtercarpconf['heirarchy']))==1)
		CarpError('ERROR: FilterCarpEndGroup() called at outermost level.','plugin/filter-end-without-group');
	else {
		$filtercarpconf['current']=&$filtercarpconf['heirarchy'][$hc-2];
		unset($filtercarpconf['heirarchy'][$hc-1]);
	}
}

function FilterCarpInOrOut($in_out) {
	global $filtercarpconf;
	$filtercarpconf['inout']=$in_out;
	$filtercarpconf['current']=&$filtercarpconf[$in_out];
	$filtercarpconf['heirarchy']=array(&$filtercarpconf[$in_out]);
}

function FilterCarpDoLevel(&$level) {
	global $filtercarpconf,$carpconf;
	$parser=&$carpconf['rssparser'];
	$r=-1;
	for ($i=3,$c=count($level);$i<$c;$i++) {
		$ao=$level[$i][1];
		$not=$level[$i][2];
		if ((($r==0)&&($ao==1))||(($r==1)&&($ao==0))) continue;
		if ($level[$i][0]==1) $r=FilterCarpDoLevel($level[$i]);
		else {
			$f=&$level[$i][3];
			$p=&$level[$i][4];
			$op=&$level[$i][5];
			for ($j=0,$cf=count($f);$j<$cf;$j++) {
				$r=0;
				$fv=html_entity_decode($parser->GetFieldValue($f[$j]),ENT_QUOTES,$filtercarpconf['encoding']);
				switch ($op) {
				case 'equal': $r=strcasecmp($fv,$p)?0:1; break;
				case 'contain': $r=(stristr($fv,$p)===FALSE)?0:1; break;
				case 'begin': $r=strcasecmp(substr($fv,0,strlen($p)),$p)?0:1; break;
				case 'end': $r=strcasecmp(substr($fv,0-strlen($p)),$p)?0:1; break;
				case 'regex': $r=preg_match($p,$fv)?1:0; break;
				}
				if ($r) break;
			}
		}
		if ($not) $r=$r?0:1;
	}
	return $r;
}

function FilterCarpDoFilter($itemindex,$itemnumber) {
	global $filtercarpconf;
	$r=-1;
	if (count($filtercarpconf['in'])>3) $r=FilterCarpDoLevel($filtercarpconf['in']);
	if ($r&&(count($filtercarpconf['out'])>3)) $r=FilterCarpDoLevel($filtercarpconf['out'])?0:1;
	return $r?1:0;
}

return;
?>