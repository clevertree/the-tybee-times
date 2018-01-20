<?php
/*
Newer Than 1.0.3
A plugin for CaRP Evolution
(c) 2004-6 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function NewerThanReset() {
	global $newerthanconf;
	CarpUnregisterCallback('','DoNewerThan');
	CarpUnregisterCallback('','DoNewerThanAggregate');
	$newerthanconf=array(
		'when'=>time()-(24*3600),
		'minitems'=>0,
		'stop-at-old'=>1
	);
	CarpRegisterCallback('','DoNewerThan','displayitem','');
	CarpRegisterCallback('','DoNewerThanAggregate','aggregateitem','');
}

NewerThanReset();

function NewerThanDay($time=0,$minutes=0) {
	global $newerthanconf;
	$gd=getdate($time?$time:time());
	$newerthanconf['when']=mktime(0,$minutes,0,$gd['mon'],$gd['mday'],$gd['year']);
}

function DoNewerThan($itemindex,$itemnumber) {
	global $newerthanconf,$carpconf;
	if ($itemnumber<$newerthanconf['minitems']) return 1;
	if (strlen($itemdate=$carpconf['rssparser']->GetFieldValue('DATE'))) {
		if (CarpDecodeDate($itemdate)>=$newerthanconf['when']) return 1;
	}
	return $newerthanconf['stop-at-old']?-1:0;
}

function DoNewerThanAggregate($itemindex,$itemnumber,$key,$data) {
	global $newerthanconf;
	if ($itemnumber<$newerthanconf['minitems']) return 1;
	list($datetime,$junk)=explode('.',$key);
	return (($datetime+0)>=$newerthanconf['when'])?1:-1;	
}

return;
?>