<?php
/*
Replace Text 1.0.4
A plugin for CaRP Evolution
(c) 2003-6 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function ReplaceTextReset() {
	global $replacetextconf;
	CarpUnregisterCallback('','DoReplaceText');
	$replacetextconf=array();
}

ReplaceTextReset();

function ReplaceTextConf($initem,$f,$isregex,$s,$r) {
	global $replacetextconf;
	CarpUnregisterCallback('','DoReplaceText','outputfield',$f=strtolower($f));
	CarpRegisterCallback('','DoReplaceText','outputfield',$f);
	if (!strlen($f)) $f='global';
	if (!isset($replacetextconf["$f$initem"])) $replacetextconf["$f$initem"]=array();
	if ($isregex) $s=str_replace('/','\\/',$s);
	$replacetextconf["$f$initem"][]=array($isregex,$s,$r);
}

function DoReplaceText($initem,$field,$i,$j,$rv) {
	global $replacetextconf;
	$initem=$initem?1:0;
	if (isset($replacetextconf["$field$initem"])) {
		foreach ($replacetextconf["$field$initem"] as $a)
			$rv=$a[0]?preg_replace("/$a[1]/s",$a[2],$rv):str_replace($a[1],$a[2],$rv);
	}
	if (isset($replacetextconf["global$initem"])) {
		foreach ($replacetextconf["global$initem"] as $a)
			$rv=$a[0]?preg_replace("/$a[1]/s",$a[2],$rv):str_replace($a[1],$a[2],$rv);
	}
	return $rv;
}

return;
?>