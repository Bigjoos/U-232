<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
/*
+------------------------------------------------
|   $Date$
|   $Revision$ 09 Final
|   $Failedlogins
|   $Author$ Bigjoos
|   $URL$    
+------------------------------------------------
*/
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
require_once(INCL_DIR.'html_functions.php');

$lang = array_merge( $lang , load_language('failedlogins'));

$HTMLOUT="";
 
if (!min_class(UC_STAFF)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$INSTALLER09['baseurl']}/index.php");

$mode = (isset($_GET['mode']) ? htmlspecialchars($_GET['mode']) : '');

$id = isset($_GET['id']) ? (int) $_GET['id'] : '';

function validate ($id)
{
global $lang;
if (!is_valid_id($id))
stderr($lang['failed_sorry'], "{$lang['failed_bad_id']}");
else
return true;
}

//==Actions
if ($mode == 'ban'){
validate($id);
sql_query("UPDATE failedlogins SET banned = 'yes' WHERE id=".sqlesc($id)) or sqlerr(__FILE__,__LINE__);
header('Refresh: 2; url='.$INSTALLER09['baseurl'].'/admin.php?action=failedlogins');
stderr($lang['failed_success'],"{$lang['failed_message_ban']}");
exit();
}

if ($mode == 'removeban') {
validate($id);
sql_query("UPDATE failedlogins SET banned = 'no' WHERE id=".sqlesc($id)) or sqlerr(__FILE__,__LINE__);
header('Refresh: 2; url='.$INSTALLER09['baseurl'].'/admin.php?action=failedlogins');
stderr($lang['failed_success'],"{$lang['failed_message_unban']}");
exit();
}

if ($mode == 'delete') {
validate($id);
sql_query("DELETE FROM failedlogins WHERE id=".sqlesc($id)) or sqlerr(__FILE__,__LINE__);
header('Refresh: 2; url='.$INSTALLER09['baseurl'].'/admin.php?action=failedlogins');
stderr($lang['failed_success'],"{$lang['failed_message_deleted']}");
exit();
}
//==End
//==Main output
$HTMLOUT ="";

$HTMLOUT .="<table border='1' cellspacing='0' cellpadding='5' width='80%'>\n";

$res = sql_query("SELECT f.*,u.id as uid, u.username FROM failedlogins as f LEFT JOIN users as u ON u.ip = f.ip ORDER BY f.added DESC") or sqlerr(__FILE__,__LINE__);

if (mysqli_num_rows($res) == 0)
  $HTMLOUT .="<tr><td colspan='2'><b>{$lang['failed_message_nothing']}</b></td></tr>\n";
else
{  
  $HTMLOUT .="<tr><td class='colhead'>ID</td><td class='colhead' align='left'>{$lang['failed_main_ip']}</td><td class='colhead' align='left'>{$lang['failed_main_added']}</td>".
	"<td class='colhead' align='left'>{$lang['failed_main_attempts']}</td><td class='colhead' align='left'>{$lang['failed_main_status']}</td></tr>\n";
  while ($arr = mysqli_fetch_assoc($res))
  {
  $HTMLOUT .="<tr><td align='left'><b>".intval($arr['id'])."</b></td>
  <td align='left'><b>".htmlspecialchars($arr['ip'])."" . ($arr['uid'] ? "<a href='{$INSTALLER09['baseurl']}/userdetails.php?id=".intval($arr['uid'])."'>" : "" ) . " " . ( $arr['username'] ? "(".htmlspecialchars($arr['username']).")" : "" ) . "</a></b></td>
  <td align='left'><b>".get_date($arr['added'], '', 1,0)."</b></td>
  <td align='left'><b>".intval($arr['attempts'])."</b></td>
  <td align='left'>".($arr['banned'] == "yes" ? "<font color='red'><b>{$lang['failed_main_banned']}</b></font> <a href='admin.php?action=failedlogins&amp;mode=removeban&amp;id=".intval($arr['id'])."'> <font color='green'>[<b>{$lang['failed_main_remban']}</b>]</font></a>" : "<font color='green'><b>{$lang['failed_main_noban']}</b></font> <a href='admin.php?action=failedlogins&amp;mode=ban&amp;id=".intval($arr['id'])."'><font color='red'>[<b>{$lang['failed_main_ban']}</b>]</font></a>")."  <a onclick=\"return confirm('{$lang['failed_main_delmessage']}');\" href='admin.php?action=failedlogins&amp;mode=delete&amp;id=".intval($arr['id'])."'>[<b>{$lang['failed_main_delete']}</b>]</a></td></tr>\n";
  }
  }
$HTMLOUT .="</table>\n";
echo stdhead($lang['failed_main_logins']) .$HTMLOUT . stdfoot();
?>
