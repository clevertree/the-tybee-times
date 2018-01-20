<?php
/*
FlexFormat 1.0
A plugin for CaRP Evolution
(c) 2003 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function FlexFormatReset() {
	global $flexformatconf;
	CarpUnregisterCallback('','DoFlexFormat','displayitem');
	$flexformatconf=array();
	CarpRegisterCallback('','DoFlexFormat','displayitem');
}

FlexFormatReset();

function FlexFormatConf($itemnum,$field,$value) {
	global $flexformatconf;
	$itemnum--;
	if (!isset($flexformatconf[$itemnum])) $flexformatconf[$itemnum]=array();
	$flexformatconf[$itemnum][]=array(strtolower($field),$value);
}

function DoFlexFormat($itemindex,$itemnum) {
	global $carpconf,$flexformatconf;
	if (isset($flexformatconf[$itemnum])) {
		foreach ($flexformatconf[$itemnum] as $v) {
			if ($v[0]=='iorder') $carpconf['rssparser']->SetItemOrder($v[1]);
			else CarpConf($v[0],$v[1]);
		}
	}
	return 1;
}

return;
?>