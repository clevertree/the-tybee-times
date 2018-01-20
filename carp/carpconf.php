<?php
/*
If you wish to change CaRP's default configuration values, we recommeng doing
it in this file rather than modifying carp.php. That way, when you upgrade to
a new version, you won't need to copy your override settings into the new
version.

See the online documentation for details.
http://carp.docs.geckotribe.com/
*/
function MyCarpConfReset($theme='') {
	global $carpconf;
	CarpConfReset();
	
	// Add configuration code that applies to all themes here
	
	
	
	switch ($theme) {
	case 'podcast-heavy': CarpLoadTheme('podcast-heavy.php'); break;
	case 'podcast-lite': CarpLoadTheme('podcast-lite.php'); break;
	case 'ul': CarpLoadTheme('ul.php'); break;
	
	// Add new themes below here
	
	
	
	// Add new themes above here
	
	default: // Enter code for your default theme here
		break;
	}
}
MyCarpConfReset(isset($GLOBALS['carptheme'])?$GLOBALS['carptheme']:'');

return;
?>