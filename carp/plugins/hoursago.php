<?php
/*
Hours Ago 1.1
A plugin for CaRP Evolution
(c) 2005-7 Antone Roundy
All rights reserved

http://carp.docs.geckotribe.com/plugins/
*/
function HoursAgoReset() {
	global $hoursagoconf;
	CarpUnregisterCallback('','DoHoursAgo','outputfield');
	CarpUnregisterCallback('','HoursAgoStart','startprocessing');
	CarpUnregisterCallback('','HoursAgoFinish','endprocessing');
	CarpRegisterCallback('','DoHoursAgo','outputfield','date');
	CarpRegisterCallback('','HoursAgoStart','startprocessing');
	CarpRegisterCallback('','HoursAgoFinish','endprocessing');
	$hoursagoconf=array(
		'dodays'=>0,
		'minuteformat'=>'1 minute ago',
		'minutesformat'=>'%d minutes ago',
		'hourformat'=>'1 hour ago',
		'hoursformat'=>'%d hours ago',
		'dayformat'=>'1 day ago',
		'daysformat'=>'%d days ago',
		'showfuturedates'=>1
	);
}

HoursAgoReset();

function DoHoursAgo($initem,$field,$i,$j,$rv) {
	global $hoursagoconf;
	$rv+=0;
	$letter=$initem?'i':'c';
	if ($rv) {
		$age=$hoursagoconf['time']-$rv;
		if ($age<0)
			$rv=$hoursagoconf['showfuturedates']?date($hoursagoconf[$letter.'dateformat'],$rv):'';
		else if ($rv>=$hoursagoconf['yesterday']) {
			$hours=floor($age/3600);
			$minutes=floor(($age%3600)/60);
			switch($hours) {
			case 0: $rv=sprintf($hoursagoconf[($minutes==1)?'minuteformat':'minutesformat'],$minutes); break;
			case 1: $rv=sprintf($hoursagoconf['hourformat'],$hours); break;
			default: $rv=sprintf($hoursagoconf['hoursformat'],$hours); break;
			}
		} else {
			$days=floor($age/(24*3600));
			if (($hoursagoconf['dodays']>0)||
				(($hoursagoconf['dodays']<0)&&($days<=(0-$hoursagoconf['dodays'])))
			) $rv=sprintf($hoursagoconf[($days==1)?'dayformat':'daysformat'],$days);
			else $rv=date($hoursagoconf[$letter.'dateformat'],$rv);
		}
	} else $rv='';
	return empty($rv)?'':($hoursagoconf["b$letter".'date'].$rv.$hoursagoconf["a$letter".'date']);
}

function HoursAgoStart($function,$url) {
	global $hoursagoconf,$carpconf;
	$hoursagoconf['cdateformat']=$carpconf['cdateformat'];
	$hoursagoconf['bcdate']=$carpconf['bcdate'];
	$hoursagoconf['acdate']=$carpconf['acdate'];
	$hoursagoconf['idateformat']=$carpconf['idateformat'];
	$hoursagoconf['bidate']=$carpconf['bidate'];
	$hoursagoconf['aidate']=$carpconf['aidate'];
	$carpconf['cdateformat']=$carpconf['idateformat']='U';
	$carpconf['bcdate']=$carpconf['acdate']=$carpconf['bidate']=$carpconf['aidate']='';
	$hoursagoconf['time']=time();
	$hoursagoconf['yesterday']=$hoursagoconf['time']-(24*3600);
}

function HoursAgoFinish($function) {
	global $hoursagoconf,$carpconf;
	$carpconf['cdateformat']=$hoursagoconf['cdateformat'];
	$carpconf['idateformat']=$hoursagoconf['idateformat'];	
	$carpconf['bcdate']=$hoursagoconf['bcdate'];	
	$carpconf['acdate']=$hoursagoconf['acdate'];	
	$carpconf['bidate']=$hoursagoconf['bidate'];	
	$carpconf['aidate']=$hoursagoconf['aidate'];	
}

return;
?>