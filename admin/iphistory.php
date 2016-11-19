<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	echo $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'pager_functions.php');
require_once(INCL_DIR.'html_functions.php');


function gethost($ip) {
	if($ip != long2ip(ip2long($ip)))
		return;
switch( strpos(PHP_OS,'WIN') === false ? 2 : 1) {
	case 1 : //windows
		$op = array('pattern'=>'/name:([\s]+)([\w\.]+)/i','key'=>2);
	break;
	case 2 : //linux
		$op = array('pattern'=>'/name\s=\s([\w\.]+)/i','key'=>1);
	break;
}
exec('nslookup '.$ip,$a); 
$a = join("\n",$a);
preg_match($op['pattern'],$a,$out);
if(isset($out[$op['key']]) && !empty($out[$op['key']]))
	return $out[$op['key']];
unset($a,$out);
}

$lang = array_merge( $lang );

if ($CURUSER['class'] < UC_MODERATOR)
	stderr("Error", "No Access");

$uid = isset($_GET['id']) && is_valid_id($_GET['id']) ? 0+$_GET['id'] : 0;
$do = isset($_GET['do']) && in_array($_GET['do'],array('check','')) ? $_GET['do'] : '';
$order = isset($_GET['order']) && in_array($_GET['order'],array('access','ip')) ? $_GET['order'] : 'access';

switch($do) {

	case 'check' : 
		#ipban check
	break;
	default :
	$res = sql_query("SELECT username FROM users WHERE id =".sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
	if (mysqli_num_rows($res) == 0) {
		stderr("Error", "User not found");
		exit;
	}


	$perpage = 5;
	$username = mysql_result($res,0);
	$count = get_row_count("iplog", 'WHERE userid ='.$uid);

	$pager = pager($perpage,$count,'admin.php?action=iphistory&amp;id='.$uid.'&amp;'.(($order == 'access' ? 'order=access' : 'order=ip').'&amp;'));
	$q1 = sql_query('SELECT u.id,INET_ATON(u.ip) as cip, l.ip, l.access AS last_access, (SELECT count(u2.id) FROM users as u2 WHERE u2.id != u.id AND INET_ATON(u2.ip) = l.ip ) as log_count, (SELECT count(b.id) FROM bans as b WHERE l.ip >= first AND l.ip <= last ) as ban_count FROM users as u LEFT JOIN iplog as l ON u.id = l.userid WHERE u.id = '.sqlesc($uid).' ORDER BY '.($order == 'access' ? 'l.access' : 'l.ip').' DESC '.$pager['limit']) or sqlerr(__FILE__,__LINE__);


	$HTMLOUT = begin_main_frame().begin_frame("Historical IP addresses used by <a href='{$INSTALLER09['baseurl']}/userdetails.php?id=$uid'><b>".$username."</b></a>", true);

	if ($count > $perpage)
		$HTMLOUT .= $pager['pagertop'];

	$HTMLOUT .= begin_table()."<tr>
	<td class='colhead'><a class='colhead' href='{$INSTALLER09['baseurl']}/admin.php?action=iphistory&amp;id=".$uid."&amp;order=access'>Last access</a></td>\n
	<td class='colhead'><a class='colhead' href='{$INSTALLER09['baseurl']}/admin.php?action=iphistory&amp;id=".$uid."&amp;order=ip'>IP</a></td>\n
	<td class='colhead'>Hostname</td>\n
	</tr>\n";
	while ($a = mysqli_fetch_assoc($q1))
	{
		$HTMLOUT .="<tr><td>".get_date($a["last_access"], 'DATE', 1,0)."</td>\n";
		$ip = long2ip($a['ip']);
		if($a['log_count'] >= 1 )
			$HTMLOUT .= "<td><b><a href='{$INSTALLER09['baseurl']}/admin.php?action=ipsearch&amp;ip=". $ip ."' title='ip used by other persons'>" . $ip ."</a></b></td>\n";
		elseif($a['ban_count'] > 0) 
			$HTMLOUT .= "<td><a href='{$INSTALLER09['baseurl']}/admin.php?action=testip&amp;ip=" . $ip . "' title='ip banned'><font color='#FF0000' ><b>" . $ip . "</b></font></a></td>\n";
		else
			$HTMLOUT .= "<td><b>" . $ip . "</b></td>\n";
		//$d = gethost($ip);
		//$HTMLOUT .="<td>".htmlspecialchars((!empty($d) && $d != $a['ip']) ? $d : $a['id'])."</td></tr>\n";
		$HTMLOUT .="<td>".long2ip($a['ip'])."</td></tr>\n";
	}

	$HTMLOUT .= end_table();

	if ($count > $perpage)
	$HTMLOUT .= $pager['pagerbottom'];

	$HTMLOUT .= end_frame();

	$HTMLOUT .= end_main_frame();
	echo stdhead("IP History Log for ".$username) . $HTMLOUT . stdfoot();
	break;
}
?>
