<?php
function HiddenField($n,$v='') {
	global $carpsetup;
	if ((!strlen($v))&&isset($carpsetup[$n])) $v=$carpsetup[$n];
	if (strlen($v)) echo '<input type="hidden" name="'.$n.'" value="'.preg_replace('/"/','&quot;',$v)."\" />\n";
}

function HiddenFields($step,$asking=0) {
	global $carpsetup;
	if ($step>-1) HiddenField('step',$step);
	if ($asking!=1) {
		if (isset($carpsetup['incdir'])) HiddenField('incdir');
		if (isset($carpsetup['chrincdir'])) HiddenField('chrincdir');
	}
	if ($asking!=2) {
		if (isset($carpsetup['proxyserver'])) HiddenField('proxyserver');
		if (isset($carpsetup['proxyport'])) HiddenField('proxyport');
		if (isset($carpsetup['proxyuser'])) HiddenField('proxyuser');
		if (isset($carpsetup['proxypass'])) HiddenField('proxypass');
	}
	if ($asking!=3) {
		if (isset($carpsetup['mysql-database'])) HiddenField('mysql-database');
		if (isset($carpsetup['mysql-u'])) HiddenField('mysql-u');
		if (isset($carpsetup['mysql-p'])) HiddenField('mysql-p');
		if (isset($carpsetup['mysql-host'])) HiddenField('mysql-host');
		if (isset($carpsetup['mysql-aggregate-table'])) HiddenField('mysql-aggregate-table');
		if (isset($carpsetup['mysql-manual-table'])) HiddenField('mysql-manual-table');
		if (isset($carpsetup['mysql-auto-table'])) HiddenField('mysql-auto-table');
	}
	if ($asking!=4) {
		if (isset($carpsetup['cache-method'])) HiddenField('cache-method');
	}
}

function AddToFailed($failedname) {
	global $failed,$havefailed;
	$failed.=($havefailed?', ':'').$failedname;
	$havefailed=1;
}

function HostingChange() {
	?>
	<b>If changing web hosting providers is an option,
		Gecko Tribe recommends <a href='http://www.geckotribe.com/moreinfo/cj/9007370'>iPowerWeb</a>.</b>
		<img src="http://www.geckotribe.com/moreinfo/cjimg/9007370" border="0">
	We have verifed that CaRP installs easily and works properly with iPowerWeb's "Web Hosting" service.
	(NOTE: we have <i>not</i> verified it on their "Windows Hosting" option, but it may work there too).<br /><br />
	
	iPowerWeb is a top-notch provider offering full-featured web and email hosting with excellent prices.
	I personally selected iPowerWeb for another business of which I am a part owner,
		and I continue to recommend them without reservation.
	In fact, <b>if you have any difficulty installing CaRP on iPowerWeb's "Web Hosting" service,
		I will personally complete the installation at no charge,</b>
		even if you are using the free version of CaRP.
	<a href="https://secure.geckotribe.com/rss/iPowerWeb-installation.php">Request installation on iPowerWeb</a><br /><br />

	<hr /><br />
	<?php
}

function CarpSetup0() {
	global $failed,$havefailed;
	
	echo '<b>Checking your server\'s PHP version...</b>';
	$vers=explode('.',PHP_VERSION);
	$needvers=array(4,0);
	$j=count($vers);
	$k=count($needvers);
	if ($k<$j) $j=$k;
	for ($i=0;$i<$j;$i++) {
		if (($vers[$i]+0)>$needvers[$i]) break;
		if (($vers[$i]+0)<$needvers[$i]) {
			echo '<span class="fail">Failed</span>. Your server is running PHP version '.PHP_VERSION.
				'. CaRP requires PHP version ';
			for ($i=0,$j=count($needvers);$i<$j;$i++) echo ($i?'.':'').$needvers[$i];
			echo ' or higher.<br /><br />';
			HostingChange();
			return;
		}
	}
	
	echo 'Pass<br /><br /><b>Checking your server\'s PHP function support...</b>';
	$failed='';
	$havefailed=0;
	if (trim(' a ')!='a') AddToFailed('trim');
	if (str_replace('ab','x','1ab2ab3')!='1x2x3') AddToFailed('str_replace');
	if (!file_exists(__file__)) AddToFailed('file_exists');
	if (count(explode('.','1.2.3.4'))!=4) AddToFailed('explode');
	if (strlen('12345')!=5) AddToFailed('strlen');
	if (strpos('12345','4')!=3) AddToFailed('strpos');
	if (strtolower('CaRP')!='carp') AddToFailed('strtolower');
	if (strcmp('carp','carp')||!strcmp('carp','grouper')) AddToFailed('strcmp');
	if (preg_replace('/a/','b','asdf')!='bsdf') AddToFailed('preg_replace');
	if (!preg_match('/a/','ace')) AddToFailed('preg_match');
	
	if (strlen($_SERVER['SERVER_ADDR'])) $ip=$_SERVER['SERVER_ADDR'];
	else if (strlen($_SERVER['SERVER_NAME'])||strlen($_SERVER['HOST_NAME'])) {
		$server=$_SERVER[strlen($_SERVER['SERVER_NAME'])?'SERVER_NAME':'HOST_NAME'];
		if (preg_match('/[^0-9.]/',$server)) {
			$ip=gethostbyname($server);
			if ($ip==$server) $ip='127.0.0.1';
		} else $ip=$server;
	} else $ip='127.0.0.1';
	if ($fp=fsockopen($ip,$_SERVER['SERVER_PORT'])) fclose($fp);
	else if ($fp=fsockopen('www.geckotribe.com',80)) fclose($fp);
	else AddToFailed('fsockopen');

	if ($fp=fopen(__file__,'r')) fclose($fp);
	else AddToFailed('fopen');
	if ($p=xml_parser_create()) xml_parser_free($p);
	else AddToFailed('xml_parser_create');
	/*
	next most important to check:
	unlink, fstat, flock (just be sure function exists--okay for it to fail)
	
	not checked:
	preg_match_all, call_user_func, error_reporting, array_splices, strcasecmp, strtotime, parse_url,
	fputs, feof, fgets, clearstatcache, fclose, ftruncate, fflush, xml_parser_set_option, 
	xml_set_element_handler, xml_set_character_data_handler, fread, xml_error_string,
	xml_get_current_line_number, xml_parser_free
	*/
	
	if (strlen($failed)) {
		include_once dirname(__file__).'/carp.php';
		?>
		<span class="fail">Failed</span><br />The following functions are either disabled on your server,
			or are not working correctly.
		CaRP will not work on this server unless this situation is resolved:
		<?php echo $failed; ?><br /><br />
		
		<?php HostingChange(); ?>
		
		<form action="http://www.geckotribe.com/rss/carp/installer_feedback.php" method="post" style="display:inline;">
		We would be appreciate your sending us the follow information to help us with future CaRP development.
		All items are optional.<br /><br />
		
		<table border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td>Host name:</td>
			<td><input name="host" size="60" maxlength="255" value="<?php echo $_SERVER['SERVER_NAME']; ?>" /></td>
		</tr><tr>
			<td>CaRP Version:</td>
			<td><input name="version" size="60" maxlength="255" value="<?php echo $carpversion; ?>" /></td>
		</tr><tr>
			<td>Error message:</td>
			<td><input name="errormsg" size="60" value="Unsupported functions: <?php echo $failed; ?>" /></td>
		</tr><tr>
			<td valign="top">Comments:</td>
			<td>
				<textarea name="comments" rows="5" cols="60" wrap="virtual"></textarea><br /><br />
				If you would like a response to your comments, please indicate your email address.
			</td>
		</tr><tr>
			<td></td>
			<td><input type="submit" value="Send"></td>
		</tr>
		</table>
		</form>		
		<?php
		return;
	}
	echo "Pass<br /><br />\n";

	CarpSetup9();
}

function CarpSetup9() { ?>
	<form action="carpsetup.php" method="post">
	<?php HiddenFields(-1,4); ?>
	<b style="font-size:135%;">Select Caching Method:</b><br />
	To increase performance, CaRP keeps locally cached copies of feed data.
	I can either create folders for storing cache files or set up caching tables in a mySQL database.
	If I am unable to complete the setup for your selected method, you will see this form again and have an opportunity to select another method.<br /><br />
	
	<input type="radio" name="step" value="10" checked /> Create cache folders<br />
	<input type="radio" name="step" value="11" /> Use a mySQL database<br /><br />
	
	<input type="submit" value="Continue..." />
	</form>
	<?php
}

function ShowInput($name,$default='',$size=20) {
	global $carpsetup;
	echo '<input name="'.$name.'" size="'.$size.'" value="'.(isset($carpsetup[$name])?$carpsetup[$name]:$default).'" />';
}

function CarpSetup11() { 
	global $carpsetup;
	?>
	<form action="carpsetup.php" method="post">
	<?php
	$carpsetup['cache-method']='mysql';
	HiddenFields(12,3); ?>
	<b>Set up mySQL Caching:</b><br />
	<span style="color:#c00;font-weight:bold;">IMPORTANT:</span>
	Before I create CaRP's cache tables, you must have a mySQL database to hold them (ie. I do not create the database, just the tables).
	If you do not already have a suitable database, please open a new browser window and use your website's control panel
		(or whatever method you use to manage your mySQL databases)
		to create a database.<br /><br />
		
	Once you have a suitable database, submit the following form.
	I recommend not changing the values which have been automatically entered for some items unless you have a specific need to do so:<br /><br />
	
	<table border="1" cellpadding="5" cellspacing="1">
	<tr>
		<td>Database name</td>
		<td><?php ShowInput('mysql-database'); ?></td>
	</tr><tr>
		<td>Username</td>
		<td><?php ShowInput('mysql-u'); ?></td>
	</tr><tr>
		<td>Password</td>
		<td><?php ShowInput('mysql-p'); ?></td>
	</tr><tr>
		<td>Host Name</td>
		<td><?php ShowInput('mysql-host','localhost'); ?></td>
	</tr><tr>
		<td>Aggregation Cache Table</td>
		<td><?php ShowInput('mysql-aggregate-table','carpaggregatecache'); ?></td>
	</tr><tr>
		<td>Manual Cache Table</td>
		<td><?php ShowInput('mysql-manual-table','carpmanualcache'); ?></td>
	</tr><tr>
		<td>Auto Cache Table</td>
		<td><?php ShowInput('mysql-auto-table','carpautocache'); ?></td>
	</tr><tr>
		<td></td><td><input type="submit" value="Create Database Tables..." /></td>
	</tr>
	</table></form>
	<?php
}

function FindOrCreateTable($table,$idlen=64) {
	return mysql_query("select count(*) from $table")||
		mysql_query("create table $table ( id varchar($idlen) primary key, updated bigint, cache mediumtext )");
}

function CarpSetupCreateDatabaseTables() {
	global $carpsetup;
	$rv=0;
	if (mysql_select_db($carpsetup['mysql-database'])) {
		if (FindOrCreateTable($carpsetup['mysql-aggregate-table'])) {
			if (FindOrCreateTable($carpsetup['mysql-manual-table'])) {
				if (FindOrCreateTable($carpsetup['mysql-auto-table'],32)) {
					$rv=1;
					echo 'Success<br />';
				} else echo 'Failed to create autocache table. mySQL said: '.mysql_error().'<br /><br />';
			} else echo 'Failed to create manualcache table. mySQL said: '.mysql_error().'<br /><br />';
		} else echo 'Failed to create aggregatecache table. mySQL said: '.mysql_error().'<br /><br />';
	} else echo 'Failed to select requested database. mySQL said: '.mysql_error().'<br /><br />';
	return $rv;
}

function TestDatabase($table) {
	$rv=0;
	if (mysql_query('insert into '.$table.' set updated='.time().',id="test",cache="test cache data"')) {
		if ($r=mysql_query('select * from '.$table.' where id="test"')) {
			if ($l=mysql_fetch_array($r)) {
				if ($l['cache']=='test cache data') {
					if (mysql_query('delete from '.$table.' where id="test"')) $rv=1;
					else echo 'Database error deleting test record from table: '.$table.'. mySQL said: '.mysql_error().'<br /><br />';
				} else echo 'Data retrieved from table: '.$table.' doesn\'t match data stored in table.<br /><br />';
			} else echo 'Database error retrieving data from table: '.$table.'. mySQL said: '.mysql_error().'<br /><br />';
			mysql_free_result($r);
		} else echo 'Database error selecting data from table: '.$table.'. mySQL said: '.mysql_error().'<br /><br />';
	} else echo 'Database error inserting data into table: '.$table.'. mySQL said: '.mysql_error().'<br /><br />';
	return $rv;
}

function CarpSetup12() {
	global $carpsetup;
	$result=0;
	echo '<b>Connecting to database...</b>';
	if (mysql_connect($carpsetup['mysql-host'],$carpsetup['mysql-u'],$carpsetup['mysql-p'])) {
		echo ' Success<br /><b>Creating database tables...</b>';
		if (CarpSetupCreateDatabaseTables()) {
			echo '<b>Testing storage, retrieval and deletion of data from database...</b>';
			if (TestDatabase($carpsetup['mysql-aggregate-table'])&&
				TestDatabase($carpsetup['mysql-manual-table'])&&
				TestDatabase($carpsetup['mysql-auto-table'])
			) {
				echo 'Success. Cache database setup complete.<br /><br />';
				$result=1;
			}
		}
	} else echo '<b style="color:#c00;">Database connection failed.</b> mySQL said: '.mysql_error().'<br /><br />';
	if ($result) CarpSetup7();
	else CarpSetup9();
}

function CarpSetup10() {
	$GLOBALS['carpsetup']['cache-method']='file';
	if (CarpSetupCreateDirectories(1)&&CarpSetupAccessDirectories(1)) {
		echo '<b>Checking for cache directories...</b>Found<br /><br />'; 
		CarpSetup7();
	} else CarpSetup1();
}

function CarpSetup1() {
	global $carpsetup;
	?>
	<b>Create cache folders:</b><br/>
	The easiest method is to enter your FTP or Telnet login name and password, and let me try to do it automatically.
	<b style="color:#c00;">If the automatic method fails for any reason, you will need to use the manual method.</b>
	Please choose your preferred method:<br /><br />
	
	<table border="1" cellpadding="5" cellspacing="1"><tr>
	<td valign="top">
		<form action="carpsetup.php" method="post">
		<?php HiddenFields(2); ?>
		<b>Automatic:</b><br />
		<table border="0" cellspacing="0" cellpadding="2">
		<tr><td>FTP&nbsp;or&nbsp;Telnet&nbsp;login:</td><td><input name="u" size="12" value="<?php echo $_POST['u']; ?>"></td></tr>
		<tr><td>Password:</td><td><input type="password" name="p" size="12" value="<?php echo $_POST['p']; ?>"></td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="Continue..." /></td></tr>
		</table><br />
		NOTES:
		<ul>
		<li>This process may take a few seconds,
			and if it is successful, I'll take a few seconds attempting to load a newsfeed.
			Please be patient.</li>
		<li>If you can connect to your webserver using SFTP, SSH, or a control panel that uses SSL (HTTPS),
			you may wish to use the manual method for greater security.</li>
		</ul>
		</form>
	</td><td valign="top">
		<form action="carpsetup.php" method="post">
		<?php HiddenFields(3); ?>
		<b>Manual:</b><br />
		
		Use one of: FTP, SFTP, Telnet, SSH or your web host's control panel or file manager to temporarily give full access to the directory in which carp.php is located to all users.
		On a UNIX, Linux, or BSD server, set the permissions to 777 or read/write/execute for everyone.
		Read below for more information.
		Once you have changed the access permissions, click "Continue...":<br /><br />
		
		<input type="submit" value="Continue..." /><br /><br />
		
		<b>If you have Telnet or SSH access</b> to your server, enter this command:<br /><br />
		<code>chmod 777 <?php echo $carpsetup['chrincdir']; ?></code><br /><br />
		
		If you get a file not found error, then your login is in a "chroot" environment, making the path for Telnet or SSH different from the path that PHP scripts see.
		In that case, you'll either need to figure out and enter the path to the carp directory the way you see it when using Telnet or SSH,
			or use some other method to set access permissions.<br /><br />

		<b>If you do not have Telnet or SSH access</b> to your server,
			<a href="http://www.geckotribe.com/help/access-permissions.php" target="_blank">click here for help with using FTP, SFTP or some other method</a> (opens in a new window).
		</form>
	</td>
	</tr></table>
	<?php
}

function CarpSetupCheckAccess($desired,$mask) {
	global $carpsetup;
	$rv=0;
	clearstatcache();
	if ($dstat=stat($carpsetup['incdir'])) {
		if (($dstat['mode']&$mask)==($desired&$mask)) $rv=1;
	}
	return $rv;
}

function CarpSetupTrySetPerms() {
	global $carpsetup,$telnet,$ftp;
	if ($carpsetup['protocol']=='ftp') $ftp->DoCommand('site chmod 777 '.$carpsetup['chrincdir'],$rn,$rt);
	 else $telnet->DoCommand('chmod 777 '.$carpsetup['chrincdir'],$result);
	return CarpSetupCheckAccess(0777,0777);
}

function CarpSetup2LastTry() {
	if (CarpSetupTrySetPerms()) {
		echo 'Success<br /><br />';
		CarpSetup4();
	} else {
		echo 'Failed. Please set the access permissions manually or use mySQL caching.<br /><br />';
		CarpSetup9();
	}
}

function CarpSetupTryAllPaths() {
	global $carpsetup;
	$succeeded=0;
	while (strlen($carpsetup['chrincdir'])&&!$succeeded) {
		$carpsetup['chrincdir']=substr($carpsetup['chrincdir'],strpos($carpsetup['chrincdir'],'/',1));
		if (CarpSetupTrySetPerms()) {
			$succeeded=1;
			echo 'Success<br /><br />';
			CarpSetup4();
		}
	}
	if (!$succeeded) {
		echo 'Failed. Please set the access permissions manually or use mySQL caching.<br /><br />';
		CarpSetup9();
	}
}

function CarpSetup2() {
	global $carpsetup,$telnet,$ftp;
	
	$carpsetup['protocol']='ftp';
	include_once dirname(__file__).'/PHPFTP.php';
	$ftp=new PHPFTP;
	$dotelnet=1;
	echo '<b>Attempting to open FTP connection...</b>';
	if (!($r=$ftp->Connect('',$carpsetup['u'],$carpsetup['p']))) {
		echo 'Connection opened<br />';
		$dotelnet=0;
		$carpsetup['chrincdir']=$carpsetup['incdir'];
		echo 'Attempting to set access permissions...';
		if (CarpSetupTrySetPerms()) {
			echo 'Success<br /><br />';
			CarpSetup4();
		} else {
			echo 'Failed<br />Trying again...';
			$ftp->DoCommand('pwd',$rn,$path);
			$rn="$rn";
			if ($rn{0}=='2') {
				$path=($path{0}=='"')?substr($path,1,strpos(substr($path,1),'"',1)):trim($path);
				if ($path=='/') CarpSetupTryAllPaths();
				else if (($start=strpos($carpsetup['incdir'],$path))!==false) {
					$carpsetup['chrincdir']=substr($carpsetup['incdir'],$start);
					CarpSetup2LastTry();
				} else if (($start=strpos($carpsetup['incdir'],'public_html'))!==false) {
					$carpsetup['chrincdir']='/www'.substr($carpsetup['incdir'],$start+strlen('public_html'));
					CarpSetup2LastTry();
				} else {
					echo 'Failed. Please set the access permissions manually or use mySQL caching.<br /><br />';
					CarpSetup9();
				}
			} else {
				echo 'Failed (pwd command failed)<br />';
				$dotelnet=1;
			}
		}
	} else {
		echo 'Connection failed (';
		switch($r) {
		case 1: echo 'unable to create network connection'; break;
		case 2: echo 'unknown host'; break;
		case 3: echo 'login failed--please be sure to enter your username and password accurately'; break;
		case 4: echo 'PHP version too low to use FTP'; break;
		}
		echo ')<br />';
	}
	if ($dotelnet) {
		$carpsetup['protocol']='telnet';
		include_once dirname(__file__).'/PHPTelnet.php';
		$telnet=new PHPTelnet;
		echo '<b>Attempting to open Telnet connection...</b>';
		if (!($r=$telnet->Connect('',$carpsetup['u'],$carpsetup['p']))) {
			echo 'Connection opened<br />';
			$carpsetup['chrincdir']=$carpsetup['incdir'];
			echo 'Attempting to set access permissions...';
			if (CarpSetupTrySetPerms()) {
				echo 'Success<br /><br />';
				CarpSetup4();
			} else {
				echo 'Failed<br />Trying again...';
				$telnet->DoCommand('pwd',$path);
				if ($path=='/') CarpSetupTryAllPaths();
				else if (($start=strpos($carpsetup['incdir'],$path))!==false) {
					$carpsetup['chrincdir']=substr($carpsetup['incdir'],$start);
					CarpSetup2LastTry();
				} else if (($start=strpos($carpsetup['incdir'],'public_html'))!==false) {
					$carpsetup['chrincdir']='/www'.substr($carpsetup['incdir'],$start+strlen('public_html'));
					CarpSetup2LastTry();
				} else {
					echo 'Failed. Please set the access permissions manually or use mySQL caching.<br /><br />';
					CarpSetup9();
				}
			}
		} else {
			echo 'Connection failed (';
			switch($r) {
			case 1: echo 'unable to create network connection'; break;
			case 2: echo 'unknown host'; break;
			case 3: echo 'login failed--please be sure to enter your username and password accurately'; break;
			case 4: echo 'PHP version too low to use Telnet'; break;
			}
			echo ')<br />';

			echo '<span class="fail">Failed to set access permissions</span><br />';
			echo 'If you are unable to resolve any error messages displayed above, you will need to set access permissions manually or use mySQL caching.<br /><br />';
			CarpSetup9();
		}
	}
}

function CarpSetupCreateDirectories($silent=0) {
	global $carpsetup;
	if ($silent) $ser=error_reporting(0);
	else echo '<b>Attempting to create cache directories...</b>';
	$rv=(file_exists($carpsetup['incdir']."/manualcache")||mkdir($carpsetup['incdir']."/manualcache",0700))
		&&(file_exists($carpsetup['incdir']."/autocache")||mkdir($carpsetup['incdir']."/autocache",0700))
		&&(file_exists($carpsetup['incdir']."/aggregatecache")||mkdir($carpsetup['incdir']."/aggregatecache",0700));
	if (!$silent)  {
		if ($rv) echo "Success<br /><br />\n";
		else {
			echo '<span class="fail">Unexpected error</span><br />';
			echo 'Although the access permissions on your carp directory are correct, I am unable to create subdirectories inside it. '.
				'Unable to proceed with installation.<br /><br />';
			HostingChange();
		}
	}
	if ($silent) error_reporting($ser);
	return $rv;
}

function CarpSetupAccessDirectories($silent=0) {
	global $carpsetup;
	$rv=1;
	if ($silent) $ser=error_reporting(0);
	else echo '<b>Attempting to create files in cache directories...</b>';
	if ($f=fopen($carpsetup['incdir']."/manualcache/test",'w')) {
		fclose($f);
		unlink($carpsetup['incdir']."/manualcache/test");
		if ($f=fopen($carpsetup['incdir']."/autocache/test",'w')) {
			fclose($f);
			unlink($carpsetup['incdir']."/autocache/test");
			if ($f=fopen($carpsetup['incdir']."/aggregatecache/test",'w')) {
				fclose($f);
				unlink($carpsetup['incdir']."/aggregatecache/test");
			} else $rv=0;
		} else $rv=0;
	} else $rv=0;
	if (!$silent)  {
		if ($rv) echo "Success<br /><br />\n";
		else echo '<span class="fail">Failed.</span> Unable to create files inside your cache directories. '.
			'If you created these directories manually (for example, with the command "mkdir manualcache", etc.), please delete them and run the installation script again. '.
			'If the installation script created them, then some unexected error is causing the problem.';
	}
	if ($silent) error_reporting($ser);
	return $rv;
}

function CarpSetup3() {
	if (CarpSetupCheckAccess(0777,0777)) {
		if (CarpSetupCreateDirectories()&&CarpSetupAccessDirectories()) CarpSetup6();
	} else {
		echo '<span class="fail">Access permissions incorrect. Please fix the access permissions or use mySQL caching.</span><br />';
		CarpSetup9();
	}
}

function CarpSetup4() {
	if (CarpSetupCreateDirectories()&&CarpSetupAccessDirectories()) CarpSetup5();
}

function CarpSetup5() {
	global $carpsetup,$telnet,$ftp;
	if ($carpsetup['protocol']=='ftp') $ftp->DoCommand('site chmod 711 '.$carpsetup['chrincdir'],$rn,$rt);
	else $telnet->DoCommand('chmod 711 '.$carpsetup['chrincdir'],$junk);
	if (CarpSetupCheckAccess(0711,0777)) CarpSetup7();
	else {
		echo '<span class="fail">I was unable to reset the access permissions on the carp directory.</span><br />';
		CarpSetup6();
	}
}

function CarpSetup6() {
	global $carpsetup;
	?>
	<form action="carpsetup.php" method="post">
	<?php HiddenFields(7); ?>
	For security purposes, please change the access permissions for the directory where carp.php is located so that it is only writable by its owner.
	If using Telnet or SSH, enter the following command
		(changing the path if necessary as you did when setting the access permissions to 777 before):<br /><br />
	
	<code>chmod 711 <?php echo $carpsetup['chrincdir']; ?></code><br /><br />
	
	Once you have changed the access permissions, click "Continue...".<br /><br />

	<input type="submit" value="Continue..." />
	</form>
	<?php
}

function CarpSetupAskProxy() {
	global $carpsetup;
	?>
	<form action="carpsetup.php" method="post">
	<?php HiddenFields(7,2); ?>
	<table border="0" cellpadding="3" cellspacing="0" width="610">
	<tr><td colspan="3" style="color:white;background:#003399;">
		If the server where you are installing CaRP connects to the internet through a web proxy server, please enter the following.
		Otherwise, leave them blank.
	</td></tr>
	<tr>
		<td>Proxy server name:</td>
		<td>http://<input name="proxyserver" size="20"></td>
		<td>eg. www.myproxyserver.com</td>
	</tr>
	<tr>
		<td>Proxy server port:</td>
		<td><input name="proxyport" size="4" value="80"></td>
		<td>&nbsp;</td>
	</tr>
	<tr><td colspan="3" style="color:white;background:#003399;">
		If the proxy server requires a username and password, enter them here.
		Otherwise, leave them blank.
	</td></tr>
	<tr>
		<td>Username:</td>
		<td><input name="proxyuser" size="20"></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input name="proxypass" size="20"></td>
		<td>&nbsp;</td>
	</tr>
	</table><p>

	<input type="submit" value="Continue...">
	</form>
	<?php
}

function CarpSetup7() {
	global $carpsetup;
	if (isset($carpsetup['proxyserver'])) {
		$proxy=preg_replace("#/$#",'',$carpsetup['proxyserver']).((isset($carpsetup['proxyport'])&&($carpsetup['proxyport']!=80))?(':'.$carpsetup['proxyport']):'');
	} else $proxy=$carpsetup['proxyuser']=$carpsetup['proxypass']='';
	
	if (strlen($proxy)) {
		if (preg_match('/[^0-9.]/',$carpsetup['proxyserver'])) $ip=gethostbyname($carpsetup['proxyserver']);
		else $ip=$carpsetup['proxyserver'];
		$rq='http://www.geckotribe.com/help/installtest.txt';
		$port=($carpsetup['proxyport']+0)?($carpsetup['proxyport']+0):80;
		$server=$carpsetup['proxyserver'];
	} else {
		$ip=gethostbyname('www.geckotribe.com');
		$rq='/help/installtest.txt';
		$port=80;
		$server='www.geckotribe.com';
	}
	if (preg_match('/[^0-9.]/',$ip)) {
		echo "<span class=\"fail\">DNS lookup of $server failed.</span><br />CaRP may be installed properly, but I am unable to confirm at this time.";
	} else {
		if ($tfp=fsockopen($ip,$port)) {
			fputs($tfp,"GET $rq HTTP/1.0\r\nHost: $server\r\nUser-Agent: CaRPInstaller/1.0\r\n");
			if (strlen($carpsetup['proxyuser']))
				fputs($tfp,'Proxy-Authorization: Basic '.base64_encode($carpsetup['proxyuser'].':'.$carpsetup['proxypass'])."\r\n");
			fputs($tfp,"\r\n");
			do { $l=fgets($tfp,4096); } while (strlen(preg_replace("/[\r\n]/",'',$l))&&!feof($tfp));
					if (feof($tfp)) {
						fclose($tfp);
						CarpSetupAskProxy();
					} else {
						$l=fgets($tfp,4096);
						fclose($tfp);
				if (preg_match('/Installation Success/',$l)) CarpSetup8();
				else CarpSetupAskProxy();
			}
		} else CarpSetupAskProxy();
	}
}

function CarpSetup8() {
	global $carpsetup,$carpconf,$carpversion;
	include_once $carpsetup['incdir'].'/carp.php';
	if (isset($carpconf)) {
		echo "I will now attempt to display a newsfeed. Please scroll down if necessary to see what follows the newsfeed.<br />";
		echo '<div style="margin:15px;padding:6px;background:#ccc;border:1px solid:#333;">';
		CarpConf('maxitems',3);
		CarpConf('phperrors',E_ALL);
		CarpConf('carperrors',1);
		if (isset($carpsetup['proxyserver'])) {
			$proxy=preg_replace("#/$#",'',$carpsetup['proxyserver']).((isset($carpsetup['proxyport'])&&($carpsetup['proxyport']!=80))?(':'.$carpsetup['proxyport']):'');
		} else $proxy=$carpsetup['proxyuser']=$carpsetup['proxypass']='';
		if (strlen($proxy)) CarpConf('proxyserver',$proxy);
		if (isset($carpsetup['proxyuser'])&&strlen($carpsetup['proxyuser'])) CarpConf('proxyauth',$carpsetup['proxyuser'].':'.$carpsetup['proxypass']);
		CarpShow('http://rss.geckotribe.com/rss/9.rss');
		?>
		</div><br />
		If a newsfeed was successfully displayed above, then installation is complete.
		If error messages were displayed, you will need to resolve them.<br /><br />
		
		<h2 style="margin:0;display:inline;">Displaying newsfeeds in your web pages:</h2><br />
		<span style="color:#c00;font-weight:bold;">Copy the code in the gray box below and save it for future reference.
		It contains the proper path for loading CaRP on your system.</span>
		To display feeds in your webpages, follow the instructions shown below for the filename extension or CMS system of your webpage.
		These instructions may also be found in the README.html file in the CaRP archive:
		
		<ul style="margin:12px 0;">
		<li><b>.php</b> - Paste the code shown below into the page in the place where you want the newsfeed to appear.</li>
		<li><b>.shtm or .shtml</b> (or .htm or .html, if you have SSI enabled for those filename extensions) -
			See the last paragraph of <a href="http://www.geckotribe.com/help/carp/2005/06/displaying-feeds-in-non-php-pages.php" target="_blank">this information</a>.</li>
		<li><b>.htm or .html</b> - You have three options:
			<ul>
			<li>Change the filename extension to ".php" and use the instructions shown above.</li>
			<li><a href="http://www.geckotribe.com/help/carp/2005/06/displaying-feeds-in-non-php-pages.php" target="_blank">Turn on PHP processing for your HTML pages</a> and use the instructions shown above.</li>
			<li>Use the <a href="http://carp.docs.geckotribe.com/examples/js.php" target="_blank">example code for converting RSS to JavaScript</a>.</li>
			</ul></li>
		<li><b>.asp</b> - Follow <a href="http://www.geckotribe.com/help/carp/2005/11/displaying-rss-feeds-in-asp-pages.php" target="_blank">these instructions</a>.</li>
		<li><b>Mambo or Joomla CMS</b> - Follow <a href="http://www.geckotribe.com/help/carp/2006/01/displaying-rss-feeds-in-mambojoomla.php" target="_blank">these instructions</a>.</li>
		</ul>
		
		Change the URL on the line where "CarpCacheShow" is called to the URL of the feed you wish to display,
			and add any desired configuration settings where shown.<br /><br />
		
		<?php
		$hasSetupCode=(isset($carpsetup['proxyuser'])&&strlen($carpsetup['proxyuser']))||
			($carpsetup['cache-method']=='mysql')||
			(isset($proxy)&&strlen($proxy));
		 if ($hasSetupCode) { ?>
			NOTE: We strongly recommend copying the "Setup Code" section and pasting it into your carpconf.php file
				immediately under the line of that file that says "// Add configuration code that applies to all themes here".
			If you do so, there is no need to include that code on each page where you use CaRP.
			Otherwise, you <i>must</i> include those lines each time you load CaRP.<br /><br />
		<?php } ?>
		
		For more information, please refer to the <a href="http://carp.docs.geckotribe.com/" target="_blank">CaRP documentation</a>.<br /><br />

		<div style="margin:15px;padding:6px;background:#ccc;border:1px solid:#333;">&lt;?php<br>
		require_once '<?php echo $carpsetup['incdir']; ?>/carp.php';<br />
		<?php
		if ($hasSetupCode) {
			echo '/*** Setup Code ***/<br />';
			if ($carpsetup['cache-method']=='mysql') {
				echo "CarpConf('cache-method','mysql');<br />";
				echo "CarpConf('mysql-connect',1);<br />";
				if ($carpsetup['mysql-database']!='carpcache') echo "CarpConf('mysql-database','".$carpsetup['mysql-database']."');<br />";
				if (preg_match('/^[0-9]/',$carpsetup['mysql-database'])||preg_match('/-/',$carpsetup['mysql-database'])) echo "CarpConf('mysql-select-db',1);<br />";
				if ($carpsetup['mysql-host']!='localhost') echo "CarpConf('mysql-host','".$carpsetup['mysql-host']."');<br />";
				echo "CarpConf('mysql-username','".$carpsetup['mysql-u']."');<br />";
				echo "CarpConf('mysql-password','".$carpsetup['mysql-p']."');<br />";
				if ($carpsetup['mysql-aggregate-table']!='carpaggregatecache')
					echo "\$carpconf['mysql-tables'][0]='".$carpsetup['mysql-aggregate-table']."';<br />";
				if ($carpsetup['mysql-manual-table']!='carpmanualcache')
					echo "\$carpconf['mysql-tables'][1]='".$carpsetup['mysql-manual-table']."';<br />";
				if ($carpsetup['mysql-auto-table']!='carpautocache')
					echo "\$carpconf['mysql-tables'][2]='".$carpsetup['mysql-auto-table']."';<br />";
			}
			if (isset($carpsetup['proxyuser'])&&strlen($carpsetup['proxyuser'])) echo "CarpConf('proxyauth',".$carpsetup['proxyuser'].':'.$carpsetup['proxypass'].");<br>\n";
			if (isset($proxy)&&strlen($proxy)) echo "CarpConf('proxyserver',$proxy);<br>\n";
			echo '/*** End Setup Code ***/<br />';
		}
		?>
		// Add any desired configuration settings before CarpCacheShow<br />
		// using "CarpConf" and other functions<br /><br />
		CarpCacheShow('http://www.geckotribe.com/press/rss/pr.rss');<br>
		?&gt;</div>
		<?php
	} else echo "An unexpected error occurred while attempting to load carp.php. Please resolve this issue and then load the CaRP setup assistant again.";
}

return;
?>
