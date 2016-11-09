<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
//==Start execution time
$q['start'] = microtime(true);
//==End
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
require_once(CACHE_DIR.'free_cache.php');
require_once(INCL_DIR.'function_happyhour.php');
/**** validip/getip courtesy of manolete <manolete@myway.com> ****/
// IP Validation
function validip($ip)
{
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		// reserved IANA IPv4 addresses
		// http://www.iana.org/assignments/ipv4-address-space
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

// Patched function to detect REAL IP address if it's valid
function getip() {
   if (isset($_SERVER)) {
     if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
     } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
       $ip = $_SERVER['HTTP_CLIENT_IP'];
     } else {
       $ip = $_SERVER['REMOTE_ADDR'];
     }
   } else {
     if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
       $ip = getenv('HTTP_X_FORWARDED_FOR');
     } elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
       $ip = getenv('HTTP_CLIENT_IP');
     } else {
       $ip = getenv('REMOTE_ADDR');
     }
   }

   return $ip;
 }

function dbconn($autoclean = false)
{
    global $TBDEV;

    if (!@($GLOBALS["___mysqli_ston"] = mysqli_connect($TBDEV['mysql_host'],  $TBDEV['mysql_user'],  $TBDEV['mysql_pass'])))
    {
	  switch (((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)))
	  {
		case 1040:
		case 2002:
			if ($_SERVER['REQUEST_METHOD'] == "GET")
				die("<html><head><meta http-equiv='refresh' content=\"5 {$_SERVER['REQUEST_URI']}\"></head><body><table border='0' width='100%' height='100%'><tr><td><h3 align='center'>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
			else
				die("Too many users. Please press the Refresh button in your browser to retry.");
        default:
    	    die("[" . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) . "] dbconn: mysql_connect: " . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
      }
    }
    ((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE {$TBDEV['mysql_db']}"))
        or die('dbconn: mysql_select_db: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    //mysql_query("SET NAMES utf8");
    //mysql_set_charset('utf8');
    userlogin();

    if ($autoclean)
        register_shutdown_function("autoclean");
}

function status_change($id) {
sql_query('UPDATE announcement_process SET status = 0 WHERE user_id = '.sqlesc($id).' AND status = 1') or sqlerr(__FILE__, __LINE__);
}

function hashit($var,$addtext="")
{
return md5("Th15T3xt".$addtext.$var.$addtext."is5add3dto66uddy6he@water...");
}

function userlogin() {
    global $TBDEV;
    unset($GLOBALS["CURUSER"]);
    $dt = time();
    $ip = getip();
	  $nip = ip2long($ip);
    require_once(CACHE_DIR.'bans_cache.php');
    if(count($bans) > 0)
    {
      foreach($bans as $k) {
        if($nip >= $k['first'] && $nip <= $k['last']) {
        header("HTTP/1.0 403 Forbidden");
        print "<html><body><h1>403 Forbidden</h1>Unauthorized IP address.</body></html>\n";
        exit();
        }
      }
      unset($bans);
    }

   if (!$TBDEV['site_online'] || !get_mycookie('uid') || !get_mycookie('pass')|| !get_mycookie('hashv') )
       return;
    $id = 0 + get_mycookie('uid');
    if (!$id OR (strlen( get_mycookie('pass') ) != 32) OR (get_mycookie('hashv') != hashit($id,get_mycookie('pass'))))
       return;
   // ==Retro's Announcement mod
    $prefix = 'ChangeMe';
    $res = sql_query("SELECT ".$prefix.".*, ann_main.subject AS curr_ann_subject, ann_main.body AS curr_ann_body " . "FROM users AS ".$prefix." " . "LEFT JOIN announcement_main AS ann_main " . "ON ann_main.main_id = ".$prefix.".curr_ann_id " . "WHERE ".$prefix.".id = $id AND ".$prefix.".enabled='yes' AND ".$prefix.".status = 'confirmed'") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    if (!$row)
        return;
   if (get_mycookie('pass') !== md5($row["passhash"].$_SERVER["REMOTE_ADDR"]))
   return; 
	  
	  // If curr_ann_id > 0 but curr_ann_body IS NULL, then force a refresh
	 if (($row['curr_ann_id'] > 0) AND ($row['curr_ann_body'] == NULL)) {
	 $row['curr_ann_id'] = 0;
	 $row['curr_ann_last_check']	= '0';
	 }
			// If elapsed > 10 minutes, force a announcement refresh.
			if (($row['curr_ann_last_check'] != '0') AND
					($row['curr_ann_last_check']) < (time($dt) - 600))
					$row['curr_ann_last_check'] = '0';

 	 if (($row['curr_ann_id'] == 0) AND ($row['curr_ann_last_check'] == '0'))
 	 { // Force an immediate check...
 		 $query = sprintf('SELECT m.*,p.process_id FROM announcement_main AS m '.
 			 'LEFT JOIN announcement_process AS p ON m.main_id = p.main_id '.
 			 'AND p.user_id = %s '.
 			 'WHERE p.process_id IS NULL '.
 			 'OR p.status = 0 '.
 			 'ORDER BY m.main_id ASC '.
 			 'LIMIT 1',
 	sqlesc($row['id']));

 	$result = sql_query($query) or sqlerr(__FILE__, __LINE__);

 	if (mysqli_num_rows($result))
 	{ // Main Result set exists
 	$ann_row = mysqli_fetch_assoc($result);

 	$query = $ann_row['sql_query'];

 	// Ensure it only selects...
 	if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $query)) die();

 	// The following line modifies the query to only return the current user
 	// row if the existing query matches any attributes.
 	$query .= ' AND u.id = '.sqlesc($row['id']).' LIMIT 1';

 	$result = sql_query($query) or sqlerr(__FILE__, __LINE__);

 	if (mysqli_num_rows($result))
 	{ // Announcement valid for member
 	$row['curr_ann_id'] = $ann_row['main_id'];

 	// Create two row elements to hold announcement subject and body.
 	$row['curr_ann_subject'] = $ann_row['subject'];
 	$row['curr_ann_body'] = $ann_row['body'];

 	// Create additional set for main UPDATE query.
 	$add_set = ', curr_ann_id = '.sqlesc($ann_row['main_id']);
 	$status = 2;
 	}
 	else
 	{
 	// Announcement not valid for member...
 	$add_set = ', curr_ann_last_check = '.sqlesc($dt);
 	$status = 1;
 	}

 	// Create or set status of process
 	if ($ann_row['process_id'] === NULL)
 	{
 	// Insert Process result set status = 1 (Ignore)
 	$query = sprintf('INSERT INTO announcement_process (main_id, '.
 	'user_id, status) VALUES (%s, %s, %s)',
 	sqlesc($ann_row['main_id']),
 	sqlesc($row['id']),
 	sqlesc($status));
 	}
 	else
 	{
 	// Update Process result set status = 2 (Read)
 	$query = sprintf('UPDATE announcement_process SET status = %s '.
 	'WHERE process_id = %s',
 	sqlesc($status),
 	sqlesc($ann_row['process_id']));
 	}
 	sql_query($query);
 	}
 	else
 	{
  // No Main Result Set. Set last update to now...
 	$add_set = ', curr_ann_last_check = '.sqlesc($dt);
 	}
 	unset($result);
 	unset($ann_row);
 	}

    $ip = ($row['class'] >= UC_UPLOADER ? '127.0.0.1' : $ip);  //==Null Staff ips
    $add_set = (isset($add_set))?$add_set:'';
    if (($row['last_access'] != '0') AND (($row['last_access']) < (time($dt) - 180))/** 3 mins **/ || ($row['ip'] !== $ip)) 
    {
    sql_query("UPDATE users SET last_access=".sqlesc($dt).", ip=".sqlesc($ip).$add_set." WHERE id=".sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
    sql_query('INSERT INTO iplog (ip, userid, access) VALUES (' . ip2long($ip) . ', ' .sqlesc($row['id']). ', ' . sqlesc($row['last_access']) . ') on DUPLICATE KEY update access=values(access)') or sqlerr(__FILE__, __LINE__);
    }
    if ($row['override_class'] < $row['class']) $row['class'] = $row['override_class']; // Override class and save in GLOBAL array below.
    $GLOBALS["CURUSER"] = $row;
    get_template();
    }

  function autoclean()
  {
	global $TBDEV;
	$now = time();
	/* Better cleanup function with db-optimization and slow clean by x0r @ tbdev.net */
	$w00p = sql_query("SELECT arg, value_u FROM avps") or sqlerr(__FILE__, __LINE__);
	while ($row = mysqli_fetch_assoc($w00p))
	{
	if ($row['arg'] == "lastcleantime" && ($row['value_u'] + $TBDEV['autoclean_interval']) < $now)
	{
	sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastcleantime'") or sqlerr(__FILE__, __LINE__);
  require_once(INCL_DIR.'cleanup.php');
  docleanup();
  }
	else if ($row['arg'] == "lastslowcleantime" && ($row['value_u'] + $TBDEV['autoslowclean_interval']) < $now)
	{
	sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastslowcleantime'") or sqlerr(__FILE__, __LINE__);
	require_once(INCL_DIR.'cleanup.php');
	doslowcleanup();
	}
	else if ($row['arg'] == "lastslowcleantime2" && ($row['value_u'] + $TBDEV['autoslowclean_interval2']) < $now)
	{
	sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastslowcleantime2'") or sqlerr(__FILE__, __LINE__);
	require_once(INCL_DIR.'cleanup.php');
	doslowcleanup2();
	}
	else if ($row['arg'] == "lastoptimizedbtime" && ($row['value_u'] + $TBDEV['optimizedb_interval']) < $now)
	{
	sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastoptimizedbtime'") or sqlerr(__FILE__, __LINE__);
	require_once(INCL_DIR.'cleanup.php');
	dooptimizedb();
	}
	}
	((mysqli_free_result($w00p) || (is_object($w00p) && (get_class($w00p) == "mysqli_result"))) ? true : false);
	return;
  }

  function get_template(){
	global $CURUSER, $TBDEV;
	if(isset($CURUSER)){
		if(file_exists(TEMPLATE_DIR."{$CURUSER['stylesheet']}/template.php")){
			require_once(TEMPLATE_DIR."{$CURUSER['stylesheet']}/template.php");
		}else{
			if(isset($TBDEV)){
				if(file_exists(TEMPLATE_DIR."{$TBDEV['stylesheet']}/template.php")){
			require_once(TEMPLATE_DIR."{$TBDEV['stylesheet']}/template.php");
				}else{
					print("Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.");
				}
			}else{
				if(file_exists(TEMPLATE_DIR."1/template.php")){
					require_once(TEMPLATE_DIR. "1/template.php");
				}else{
					print("Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.");
				}
			}
		}
	}else{
	if(file_exists(TEMPLATE_DIR."{$TBDEV['stylesheet']}/template.php")){
			require_once(TEMPLATE_DIR."{$TBDEV['stylesheet']}/template.php");
		}else{
			print("Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.");
		}
	}
	if(!function_exists("stdhead")){
		print("stdhead function missing");
		function stdhead($title="", $message=true){
			return "<html><head><title>$title</title></head><body>";
		}
	}
	if(!function_exists("stdfoot")){
		print("stdfoot function missing");
		function stdfoot(){
			return "</body></html>";
		}
	}
	if(!function_exists("stdmsg")){
		print("stdmgs function missing");
		function stdmsg($TITLE, $MSG){
			return "<b>".$TITLE."</b><br />$MSG";
		}
	}
	if(!function_exists("StatusBar")){
		print("StatusBar function missing");
		function StatusBar(){
			global $CURUSER, $lang;
			return "{$lang['gl_msg_welcome']}, $CURUSER[username]";
		}
	}
}

function unesc($x) {
    if (get_magic_quotes_gpc())
        return stripslashes($x);
    return $x;
}

function mksize($bytes)
{
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GB";
	else
		return number_format($bytes / 1099511627776, 2) . " TB";
}

function mkprettytime($s) {
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        }
        else
            $v = $s;
        $t[$y[1]] = $v;
    }

    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
}

function mkglobal($vars) {
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = unesc($_GET[$v]);
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = unesc($_POST[$v]);
        else
            return 0;
    }
    return 1;
}

function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email) {
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

//== putyn  08/08/2011
function sqlesc($x)
{
    if (is_integer($x)) return (int)$x;
    return sprintf('\'%s\'', mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x));
}
function sqlwildcardesc($x)
{
    return str_replace(array('%', '_'), array('\\%', '\\_'), mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x));
}

function genbark($x,$y) {
    stdhead($y);
    print("<h2>" . htmlspecialchars($y) . "</h2>\n");
    print("<p>" . htmlspecialchars($x) . "</p>\n");
    stdfoot();
    exit();
}

function httperr($code = 404) {
    header("HTTP/1.0 404 Not found");
    print("<h1>Not Found</h1>\n");
    print("<p>Sorry pal :(</p>\n");
    exit();
}

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff)
{
    set_mycookie( "uid", $id, $expires );
    set_mycookie( "pass", $passhash, $expires );
    set_mycookie( "hashv", hashit($id,$passhash), $expires );
    if ($updatedb)
      sql_query("UPDATE users SET last_login = ".TIME_NOW." WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
}

function set_mycookie( $name, $value="", $expires_in=0, $sticky=1 )
    {
		global $TBDEV;
		
		if ( $sticky == 1 )
    {
      $expires = time() + 60*60*24*365;
    }
		else if ( $expires_in )
		{
			$expires = time() + ( $expires_in * 86400 );
		}
		else
		{
			$expires = FALSE;
		}
		
		$TBDEV['cookie_domain'] = $TBDEV['cookie_domain'] == "" ? ""  : $TBDEV['cookie_domain'];
    $TBDEV['cookie_path']   = $TBDEV['cookie_path']   == "" ? "/" : $TBDEV['cookie_path'];
      	
		if ( PHP_VERSION < 5.2 )
		{
      if ( $TBDEV['cookie_domain'] )
      {
        @setcookie( $TBDEV['cookie_prefix'].$name, $value, $expires, $TBDEV['cookie_path'], $TBDEV['cookie_domain'] . '; HttpOnly' );
      }
      else
      {
        @setcookie( $TBDEV['cookie_prefix'].$name, $value, $expires, $TBDEV['cookie_path'] );
      }
    }
    else
    {
      @setcookie( $TBDEV['cookie_prefix'].$name, $value, $expires, $TBDEV['cookie_path'], $TBDEV['cookie_domain'], NULL, TRUE );
    }
			
}

function get_mycookie($name) 
    {
      global $TBDEV;
      
    	if ( isset($_COOKIE[$TBDEV['cookie_prefix'].$name]) AND !empty($_COOKIE[$TBDEV['cookie_prefix'].$name]) )
    	{
    		return urldecode($_COOKIE[$TBDEV['cookie_prefix'].$name]);
    	}
    	else
    	{
    		return FALSE;
    	}
}

function logoutcookie() {
    set_mycookie('uid', '-1');
    set_mycookie('pass', '-1');
    set_mycookie('hashv', '-1');
}

function loggedinorreturn() {
    global $CURUSER, $TBDEV;
    if (!$CURUSER) {
        header("Location: {$TBDEV['baseurl']}/login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}


function searchfield($s) {
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist() {
    $ret = array();
    $res = sql_query("SELECT id, image, name FROM categories ORDER BY name") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_array($res))
        $ret[] = $row;
    return $ret;
}

function get_row_count($table, $suffix = "")
{
  if ($suffix)
  $suffix = " $suffix";
  ($r = sql_query("SELECT COUNT(*) FROM $table$suffix")) or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  ($a = mysqli_fetch_row($r)) or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  return $a[0];
}


function stderr($heading, $text)
{
    $htmlout = stdhead();
    $htmlout .= stdmsg($heading, $text);
    $htmlout .= stdfoot();
    
    print $htmlout;
    exit();
}
	
// Basic MySQL error handler
function sqlerr($file = '', $line = '') {
    global $TBDEV, $CURUSER;
    
		$the_error    = ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
		$the_error_no = ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));

    	if ( SQL_DEBUG == 0 )
    	{
			exit();
    	}
     	else if ( $TBDEV['sql_error_log'] AND SQL_DEBUG == 1 )
		{
			$_error_string  = "\n===================================================";
			$_error_string .= "\n Date: ". date( 'r' );
			$_error_string .= "\n Error Number: " . $the_error_no;
			$_error_string .= "\n Error: " . $the_error;
			$_error_string .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
			$_error_string .= "\n in file ".$file." on line ".$line;
			$_error_string .= "\n URL:".$_SERVER['REQUEST_URI'];
			$_error_string .= "\n Username: {$CURUSER['username']}[{$CURUSER['id']}]";
			
			if ( $FH = @fopen( $TBDEV['sql_error_log'], 'a' ) )
			{
				@fwrite( $FH, $_error_string );
				@fclose( $FH );
			}
			
			print "<html><head><title>MySQL Error</title>
					<style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
		    		   <blockquote><h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
		    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>
				  </body></html>";
		}
		else
		{
    		$the_error = "\nSQL error: ".$the_error."\n";
	    	$the_error .= "SQL error code: ".$the_error_no."\n";
	    	$the_error .= "Date: ".date("l dS \of F Y h:i:s A");
    	
	    	$out = "<html>\n<head>\n<title>MySQL Error</title>\n
	    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
	    		   <blockquote>\n<h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
	    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
	    		   <br /><br /><b>Error Returned</b><br />
	    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">".htmlentities($the_error, ENT_QUOTES)."</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";
    		   
    
	       	print $out;
		}
		
        exit();
}
    
function coin($coins, $add=true){
	global $TBDEV, $CURUSER;
	if($TBDEV['coins']){
	if ($add) 
        sql_query("UPDATE users SET coins=coins+".sqlesc($coins)." WHERE is=".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
	    else 
        sql_query("UPDATE users SET coins=coins-".sqlesc($coins)." WHERE is=".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
}
}

function get_dt_num()
{
  return gmdate("YmdHis");
}

function write_log($text)
{
  $text = sqlesc($text);
  $added = TIME_NOW;
  sql_query("INSERT INTO sitelog (added, txt) VALUES($added, $text)") or sqlerr(__FILE__, __LINE__);
}

function sql_timestamp_to_unix_timestamp($s)
{
  return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

function unixstamp_to_human( $unix=0 )
    {
    	$offset = get_time_offset();
    	$tmp    = gmdate( 'j,n,Y,G,i', $unix + $offset );
    	
    	list( $day, $month, $year, $hour, $min ) = explode( ',', $tmp );
  
    	return array( 'day'    => $day,
                    'month'  => $month,
                    'year'   => $year,
                    'hour'   => $hour,
                    'minute' => $min );
    }
    
function get_time_offset() {
    
    	global $CURUSER, $TBDEV;
    	$r = 0;
    	
    	$r = ( ($CURUSER['time_offset'] != "") ? $CURUSER['time_offset'] : $TBDEV['time_offset'] ) * 3600;
			
      if ( $TBDEV['time_adjust'] )
      {
        $r += ($TBDEV['time_adjust'] * 60);
      }
      
      if ( $CURUSER['dst_in_use'] )
      {
        $r += 3600;
      }
        
        return $r;
}
    
function get_date($date, $method, $norelative=0, $full_relative=0)
    {
        global $TBDEV;
        
        static $offset_set = 0;
        static $today_time = 0;
        static $yesterday_time = 0;
        $time_options = array( 
        'JOINED' => $TBDEV['time_joined'],
        'SHORT'  => $TBDEV['time_short'],
				'LONG'   => $TBDEV['time_long'],
				'TINY'   => $TBDEV['time_tiny'] ? $TBDEV['time_tiny'] : 'j M Y - G:i',
				'DATE'   => $TBDEV['time_date'] ? $TBDEV['time_date'] : 'j M Y'
				);
        
        if ( ! $date )
        {
            return '--';
        }
        
        if ( empty($method) )
        {
        	$method = 'LONG';
        }
        
        if ($offset_set == 0)
        {
        	$GLOBALS['offset'] = get_time_offset();
			
          if ( $TBDEV['time_use_relative'] )
          {
            $today_time     = gmdate('d,m,Y', ( time() + $GLOBALS['offset']) );
            $yesterday_time = gmdate('d,m,Y', ( (time() - 86400) + $GLOBALS['offset']) );
          }	
        
          $offset_set = 1;
        }
        
        if ( $TBDEV['time_use_relative'] == 3 )
        {
        	$full_relative = 1;
        }
        
        if ( $full_relative and ( $norelative != 1 ) )
        {
          $diff = time() - $date;
          
          if ( $diff < 3600 )
          {
            if ( $diff < 120 )
            {
              return '< 1 minute ago';
            }
            else
            {
              return sprintf( '%s minutes ago', intval($diff / 60) );
            }
          }
          else if ( $diff < 7200 )
          {
            return '< 1 hour ago';
          }
          else if ( $diff < 86400 )
          {
            return sprintf( '%s hours ago', intval($diff / 3600) );
          }
          else if ( $diff < 172800 )
          {
            return '< 1 day ago';
          }
          else if ( $diff < 604800 )
          {
            return sprintf( '%s days ago', intval($diff / 86400) );
          }
          else if ( $diff < 1209600 )
          {
            return '< 1 week ago';
          }
          else if ( $diff < 3024000 )
          {
            return sprintf( '%s weeks ago', intval($diff / 604900) );
          }
          else
          {
            return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
          }
        }
        else if ( $TBDEV['time_use_relative'] and ( $norelative != 1 ) )
        {
          $this_time = gmdate('d,m,Y', ($date + $GLOBALS['offset']) );
          
          if ( $TBDEV['time_use_relative'] == 2 )
          {
            $diff = time() - $date;
          
            if ( $diff < 3600 )
            {
              if ( $diff < 120 )
              {
                return '< 1 minute ago';
              }
              else
              {
                return sprintf( '%s minutes ago', intval($diff / 60) );
              }
            }
          }
          
            if ( $this_time == $today_time )
            {
              return str_replace( '{--}', 'Today', gmdate($TBDEV['time_use_relative_format'], ($date + $GLOBALS['offset']) ) );
            }
            else if  ( $this_time == $yesterday_time )
            {
              return str_replace( '{--}', 'Yesterday', gmdate($TBDEV['time_use_relative_format'], ($date + $GLOBALS['offset']) ) );
            }
            else
            {
              return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
            }
        }
        else
        {
          return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
        }
}


function hash_pad($hash) {
    return str_pad($hash, 20);
}

    function load_language($file='') {
    global $TBDEV;
    if( !isset($GLOBALS['CURUSER']) OR empty($GLOBALS['CURUSER']['language']) )
    {
    if( !file_exists(LANG_DIR."lang_{$file}.php") )
    {
    stderr('SYSTEM ERROR', 'Can\'t find language files');
    }   
    require_once(LANG_DIR."lang_{$file}.php");
    return $lang;
    }
    if( !file_exists(LANG_DIR."lang_{$file}.php") )
    {
    stderr('SYSTEM ERROR', 'Can\'t find language files');
    }
    else
    {
    require_once LANG_DIR."lang_{$file}.php"; 
    }   
    return $lang;
}

function flood_limit($table) {
global $CURUSER,$TBDEV,$lang;
	if(!file_exists($TBDEV['flood_file']) || !is_array($max = unserialize(file_get_contents($TBDEV['flood_file']))))
		return;
	if(!isset($max[$CURUSER['class']]))
	return;
	$tb = array('posts'=>'posts.userid','comments'=>'comments.user','messages'=>'messages.sender');
	$q = sql_query('SELECT min('.$table.'.added) as first_post, count('.$table.'.id) as how_many FROM '.$table.' WHERE '.$tb[$table].' = '.$CURUSER['id'].' AND '.time().' - '.$table.'.added < '.$TBDEV['flood_time']) or sqlerr(__FILE__, __LINE__);
	$a = mysqli_fetch_assoc($q);
	if($a['how_many'] > $max[$CURUSER['class']])
  stderr($lang['gl_sorry'] ,$lang['gl_flood_msg'].''.mkprettytime($TBDEV['flood_time'] - (time() - $a['first_post'])));
}

//==Sql query count
$q['querytime'] = 0;
function sql_query($query) {
    global $queries, $q, $querytime, $query_stat;
	  $q = isset($q) && is_array($q) ? $q : array();
	  $q['query_stat']= isset($q['query_stat']) && is_array($q['query_stat']) ? $q['query_stat'] : array();
    $queries++;
    $query_start_time  = microtime(true); // Start time
    $result            = mysqli_query($GLOBALS["___mysqli_ston"], $query);
    $query_end_time    = microtime(true); // End time
    $query_time        = ($query_end_time - $query_start_time);
    $querytime = $querytime + $query_time;
    $q['querytime']    = (isset($q['querytime']) ? $q['querytime'] : 0) + $query_time;
    $query_time        = substr($query_time, 0, 8);
    $q['query_stat'][] = array('seconds' => $query_time, 'query' => $query);
    return $result;
    }
    
    //==Install dir check
    if (file_exists("install/index.php")){
    $HTMLOUT='';
    $HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
    <html xmlns='http://www.w3.org/1999/xhtml'>
    <head>
    <title>Warning</title>
    </head>
    <body><div style='font-size:33px;color:white;background-color:red;text-align:center;'>Delete the install directory</div></body></html>";
    echo $HTMLOUT;
    exit();
    }
?>
