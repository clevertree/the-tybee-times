<?php
/*
PHPFTP 1.0
by Antone Roundy
public domain
*/

class PHPFTP {
	var $use_usleep=0;	// change to 1 for faster execution
		// don't change to 1 on Windows servers unless you have PHP 5
	var $sleeptime=125000;
	var $loginsleeptime=1000000;

	var $fp=NULL;

	/*
	0 = success
	1 = couldn't open network connection
	2 = unknown host
	3 = login failed
	4 = PHP version too low
	*/
	function Connect($server,$user,$pass) {
		$rv=0;
		$vers=explode('.',PHP_VERSION);
		$needvers=array(4,3,0);
		$j=count($vers);
		$k=count($needvers);
		if ($k<$j) $j=$k;
		for ($i=0;$i<$j;$i++) {
			if (($vers[$i]+0)>$needvers[$i]) break;
			if (($vers[$i]+0)<$needvers[$i]) return 4;
		}
		
		$this->Disconnect();
		
		if (strlen($server)) {
			if (preg_match('/[^0-9.]/',$server)) {
				$ip=gethostbyname($server);
				if ($ip==$server) {
					$ip='';
					$rv=2;
				}
			} else $ip=$server;
		} else $ip='127.0.0.1';
		
		if (strlen($ip)) {
			if ($this->fp=@fsockopen($ip,21)) {
				if ($this->use_usleep) usleep($this->sleeptime);
				$this->GetResponse($n,$t);
				if ($n==220) {
					$this->DoCommand("user $user",$n,$t);
					if ($n==331) $this->DoCommand("pass $pass",$n,$t);
					if ($n!=230) $rv=3;
				} else $rv=1;
			} else $rv=1;
		}
		return $rv;
	}
	
	function Disconnect($exit=1) {
		if ($this->fp) {
			if ($exit) $this->DoCommand('quit',$j1,$j2);
			fclose($this->fp);
			$this->fp=NULL;
		}
	}

	function DoCommand($c,&$rn,&$rt) {
		$rn=0;
		$rt='';
		if ($this->fp) {
			fputs($this->fp,"$c\r\n");
			if ($this->use_usleep) usleep($this->sleeptime);
			else sleep(1);
			$this->GetResponse($rn,$rt);
		}
		return $this->fp?1:0;
	}
	
	function GetResponse(&$rn,&$rt) {
		$more=1;
		$rn=0;
		$rt='';
		do { 
			$l=preg_replace("/[\r\n]+/",' ',fread($this->fp,5000));
			if (preg_match('/^([0-9]{3})( |-)(.*)$/',$l,$m)) {
				$rn=$m[1]+0;
				$rt.=$m[3];
				if ($m[2]==' ') $more=0;
			} else $rt.=$l;
			$s=socket_get_status($this->fp);
		} while ($more&&$s['unread_bytes']);
		$rt=trim($rt);
	}
}

return;
?>