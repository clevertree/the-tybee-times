<?php
/*
AgeFormat 1.0
A plugin for CaRP Evolution
(c) 2006 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function AgeFormatReset() {
	global $ageformatconf;
	CarpUnregisterCallback('','DoAgeFormat','displayitem');
	$ageformatconf=array('now'=>time(),'settings'=>array());
	CarpRegisterCallback('','DoAgeFormat','displayitem');
}

AgeFormatReset();

function AgeFormatSort($a,$b) {
	return ($a[0]>$b[0])?1:-1;
}

function AgeFormatConf($setting,$settings) {
	global $ageformatconf;
	if (is_array($settings)&&(count($settings)>1)) {
		$newsettings=array();
		foreach ($settings as $k=>$v)
			$newsettings[]=array($ageformatconf['now']-($k*60),$v);
		usort($newsettings,'AgeFormatSort');
		$ageformatconf['settings'][]=array(-1,strtolower($setting),$newsettings);
	} else CarpError('Second argument to AgeFormatConf must be an array with at least two members.');
}

function DoAgeFormat($itemindex,$itemnum) {
	global $carpconf,$ageformatconf;
	if ($datestamp=CarpDecodeDate($carpconf['rssparser']->GetFieldValue('DATE'))) {
		$confs=&$ageformatconf['settings'];
		for ($i=0,$j=count($confs);$i<$j;$i++) {
			$conf=&$confs[$i];
			$settings=&$conf[2];
			for ($which=count($settings)-1;($which>0)&&($datestamp<$settings[$which-1][0]);$which--) {}
			if ($conf[0]!=$which) {
				$conf[0]=$which;
				if ($conf[1]=='iorder') $carpconf['rssparser']->SetItemOrder($settings[$which][1]);
				else CarpConf($conf[1],$settings[$which][1]);
			}
		}
	}
	return 1;
}

return;
?>