<?php
/*
Access Keys 1.0.2
A plugin for CaRP Evolution
(c) 2003 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function AccessKeysReset() {
	global $accesskeysconf;
	$accesskeysconf=array(
	'keys'=>'123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	'before'=>'[',
	'after'=>']',
	'method'=>'choosechar'
	);
	CarpUnregisterCallback('','AccessKeysAdd');
	CarpRegisterCallback('','AccessKeysAdd','outputfield','link');
}

AccessKeysReset();
$accesskeysused='';
$accesskeyindex=0;

function AccessKeysAdd($initem,$field,$i,$j,$rv) {
	global $accesskeysconf,$accesskeysused,$accesskeyindex;
	if ($initem&&($field=='link')&&strlen($rv)) {
		switch ($accesskeysconf['method']) {
		case 'choosechar':
			if (preg_match("#^(.*<a [^>]+>)([^<]*)(</a>.*)$#",$rv,$matches)) {
				$title=$matches[2];
				for ($j=0,$inentity=0;$j<strlen($title);$j++) {
					$key=substr($title,$j,1);
					if ($inentity) {
						if (preg_match("/[^a-zA-Z0-9#]/",$key)) $inentity=0;
					} else if ($key=='&') $inentity=1;
					else if (stristr($accesskeysconf['keys'],$key=substr($title,$j,1))&&!stristr($accesskeysused,$key)) {
						$accesskeysused.=$key;
						break;
					}
				}
			}
			if ($j==strlen($title)) return;
			break;
		default:
			$accesskeysused.=($key=substr($accesskeysconf['keys'],$accesskeyindex,1));
			$accesskeyindex++;
		}
		$showkey=$accesskeysconf['before'].$key.$accesskeysconf['after'];
		
		switch ($accesskeysconf['method']) {
		case 'beforelink': $rv=preg_replace("#<a #","$showkey<a ",$rv); break;
		case 'afterlink': $rv=preg_replace("#</a>#","</a>$showkey",$rv); break;
		case 'choosechar': $rv=$matches[1].substr($title,0,$j).$showkey.substr($title,$j+1).$matches[3]; break;
		}

		$rv=preg_replace("/href=/","accesskey=\"$key\" href=",$rv);
	}
	return $rv;
}

return;
?>