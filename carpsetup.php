<html>
<head>
	<title>CaRP Setup Assistant</title>
	<style type="text/css">
	.fail {
		font-weight:bold;
		color:#c00;
	}
	body, td {
		font-family:Verdana,Arial,Helvetica,sans-serif;
		font-size:10pt;
	}
	ul {
		margin:0;
	}
	</style>
</head>
<body bgcolor="white">
<h2>CaRP Setup Assistant</h2>

<?php if (0) { ?>
	<h3 style="color:#c00;">If you are seeing this message,
			PHP is either not installed on your webserver,
			has not been activated,
			or is not associated with the filename extension ".php".
		CaRP cannot be installed and used on this webserver until those things are done.
		Please activate PHP or contact your hosting provider and ask them to do so.</h3>
<?php
}
if (version_compare(PHP_VERSION,'4.1')<0) {
	if (isset($HTTP_POST_VARS)) {
		$_POST=array();
		foreach ($HTTP_POST_VARS as $k=>$v) $_POST[$k]=$v;
	}
}
$carpsetup=array();
if (isset($_POST)) foreach ($_POST as $k=>$v) $carpsetup[$k]=$v;
if (isset($carpsetup['step'])) $carpsetup['step']+=0;
else $carpsetup['step']=0;

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set(display_errors,1);

if (!isset($carpsetup['incdir'])) {
	for ($path=preg_replace('/\\\\+/','/',dirname(__file__));strlen($path)&&!isset($carpsetup['incdir']);$path=substr($path,0,strrpos($path,'/'))) {
		if (file_exists("$path/carp/carpsetupinc.php")) $carpsetup['incdir']="$path/carp";
	}
}
if (!isset($carpsetup['incdir'])) {
	if (file_exists('/www/carp/carpsetupinc.php')) $carpsetup['incdir']='/www/carp';
}
if (isset($carpsetup['incdir'])&&file_exists($carpsetup['incdir'].'/carpsetupinc.php')) {
	if (!isset($carpsetup['chrincdir'])) $carpsetup['chrincdir']=$carpsetup['incdir'];
	include $carpsetup['incdir'].'/carpsetupinc.php';
	if (!function_exists('CarpSetup1')) {
		$carpsetup['chrincdir']='/'.$carpsetup['chrincdir'];
		$carpsetup['incdir']='/'.$carpsetup['incdir'];
		include $carpsetup['incdir'].'/carpsetupinc.php';
	}
	call_user_func('CarpSetup'.$carpsetup['step']);
} else {
	?>
	<form action="carpsetup.php" method="post">
	I was unable to find carpsetupinc.php<?php if (isset($carpsetup['incdir'])) echo ' in the directory '.$carpsetup['incdir']; ?>.
	Please enter the path to the directory containing carpsetupinc.php below.<br /><br />

	<input name="incdir" size="40" value="<?php echo isset($carpsetup['incdir'])?$carpsetup['incdir']:dirname(__file__); ?>" /><br />
	<input type="submit" value="Continue..." /><br /><br />
	
	NOTES:
	<ul>
	<li>Depending on your server's configuration, the directory path may look different to me that it does when you log in to your server.
		The path to this file (carpsetup.php) as I see it is <?php echo __file__; ?>.
		Please take that into account when entering the path above.</li>
	<li>If you see an error message about a safe mode violation, you may need to move the carp directory into your web directory.</li>
	</form>
	<?php
}
?>
</body>
</html>
