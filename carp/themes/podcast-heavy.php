<?php
/*
Podcast-Heavy Theme
Version 1.0
by Antone Roundy
http://antone.geckotribe.com/

Adjust the image URL below so that it points
to the podcast image on your server.
*/

CarpConf('iorder','podcast,link,author,date,desc');
CarpConf('maxititle',60);
CarpConf('maxidesc',250);

CarpConf('bitems','<table class="carppodcastheavy">');
CarpConf('aitems','</table>');
CarpConf('bi','<tr><td valign="top">');
CarpConf('ai','</td></tr>');
CarpConf('bilink','</td><td>');

CarpConf('aiauthor','</i> - ');
CarpConf('aidate','</i>');
CarpConf('bidesc','<br />');

CarpConf('aipodcast',
	'"><img src="http://example.com/img/mp3-gray-white.png" alt="podcast" border="0" /></a>');

return;
?>