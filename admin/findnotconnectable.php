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
require_once(INCL_DIR.'torrenttable_functions.php');


$lang = array_merge( $lang, load_language('non_con') );
$HTMLOUT ='';

if (!min_class(UC_STAFF)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

if (isset($_GET["action1"]) && htmlspecialchars($_GET["action1"]) == "list") {
    $res2 = sql_query("SELECT userid, seeder, torrent, agent FROM peers WHERE connectable='no' ORDER BY userid DESC") or sqlerr(__FILE__,__LINE__);

    
$HTMLOUT .="<h3><a href='admin.php?action=findnotconnectable&amp;action1=sendpm'>{$lang['non_con_sendall']}</a></h3>
	<h3><a href='admin.php?action=findnotconnectable'>{$lang['non_con_view']}</a></h3>
	<h1>{$lang['non_con_peers']}</h1>
	{$lang['non_con_this']}<br /><p><font color='red'>*</font> {$lang['non_con_means']}<br />";
    $result = sql_query("SELECT DISTINCT userid FROM peers WHERE connectable = 'no'") or sqlerr(__FILE__,__LINE__);
    $count = mysqli_num_rows($result);
    $HTMLOUT .="$count {$lang['non_con_unique']}</p>";
    ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

    if (mysqli_num_rows($res2) == 0)
        $HTMLOUT .="<p align='center'><b>{$lang['non_con_all']}</b></p>\n";
    else {
        $HTMLOUT .="<table border='1' cellspacing='0' cellpadding='5'>\n";
        $HTMLOUT .="<tr><td class='colhead'>{$lang['non_con_name']}</td><td class='colhead'>{$lang['non_con_tor']}</td><td class='colhead'>{$lang['non_con_client']}</td></tr>\n";
        while ($arr2 = mysqli_fetch_assoc($res2)) {
            $r2 = sql_query("SELECT username FROM users WHERE id=".sqlesc($arr2['userid'])) or sqlerr(__FILE__,__LINE__);
            $a2 = mysqli_fetch_assoc($r2);
            $HTMLOUT .="<tr><td><a href='userdetails.php?id=".intval($arr2['userid'])."'>".htmlspecialchars($a2['username'])."</a></td><td align='left'><a href='details.php?id=".intval($arr2['torrent'])."&amp;dllist=1#seeders'>".intval($arr2['torrent'])."</a>";
            if ($arr2['seeder'] == 'yes')
                $HTMLOUT .="<font color='red'>*</font>";
            $HTMLOUT .="</td><td align='left'>".htmlspecialchars($arr2['agent'])."</td></tr>\n";
        }
        $HTMLOUT .="</table>\n";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dt = sqlesc(time());
    $msg = htmlspecialchars($_POST['msg']);
    if (!$msg)
        stderr("Error", "Please Type In Some Text");

    $query = sql_query("SELECT distinct userid FROM peers WHERE connectable='no'") or sqlerr(__FILE__,__LINE__);
    while ($dat = mysqli_fetch_assoc($query)) {
    $subject = "Connectability";
		sql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES (0,".sqlesc($dat['userid'])." , '" . time() . "', " . sqlesc($msg) . ", " . sqlesc($subject) . ")") or sqlerr(__FILE__, __LINE__);
    }
    sql_query("INSERT INTO notconnectablepmlog (user,date) VALUES (".sqlesc($CURUSER['id']).", ".sqlesc($dt).")") or sqlerr(__FILE__, __LINE__);
    header("Refresh: 0; url=admin.php?action=findnotconnectable");
    
}

if (isset($_GET["action1"]) && htmlspecialchars($_GET["action1"]) == "sendpm") {

$HTMLOUT .="<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<div align='center'>
<h1>{$lang['non_con_mass']}</h1>
<form method='post' action='admin.php?action=findnotconnectable'>";

    if (isset($_GET["returnto"]) || isset($_SERVER["HTTP_REFERER"])) {

$HTMLOUT .="<input type='hidden' name='returnto' value='".(isset($_GET["returnto"]) ? htmlspecialchars($_GET["returnto"]) : $_SERVER["HTTP_REFERER"])."' />";
    }
	$receiver = '';
    // default message
    $body = "{$lang['non_con_body']}";


$HTMLOUT .="<table cellspacing='0' cellpadding='5'>
<tr>
<td>{$lang['non_con_sendall']}<br />
<table style='border: 0' width='100%' cellpadding='0' cellspacing='0'>
<tr>
<td style='border: 0'>&nbsp;</td>
<td style='border: 0'>&nbsp;</td>
</tr>
</table>
</td>
</tr>
<tr><td><textarea name='msg' cols='120' rows='15'>$body</textarea></td></tr>

<tr><td colspan='2' align='center'><input type='submit' value='Send' class='btn'/></td></tr>
</table>
<input type='hidden' name='receiver' value='$receiver'/>
</form>
</div></td></tr></table>
<br />
NOTE: No HTML Code Allowed. (NO HTML)
";
}
if (isset($_GET["action1"]) == "") {

    $getlog = sql_query("SELECT * FROM `notconnectablepmlog` ORDER BY date DESC LIMIT 20") or sqlerr(__FILE__, __LINE__);
	$HTMLOUT .="<h1>{$lang['non_con_uncon']}</h1>
	<h3><a href='admin.php?action=findnotconnectable&amp;action1=sendpm'>{$lang['non_con_sendall']}</a></h3>
	<h3><a href='admin.php?action=findnotconnectable&amp;action1=list'>{$lang['non_con_list']}</a></h3><p>
	<br />{$lang['non_con_please1']}<br /></p>
	<table border='1' cellspacing='0' cellpadding='5'>\n
	<tr><td class='colhead'>{$lang['non_con_by']}</td>
	<td class='colhead'>{$lang['non_con_date']}</td><td class='colhead'>{$lang['non_con_elapsed']}</td></tr>";
	
    
    while ($arr2 = mysqli_fetch_assoc($getlog)) {
        $r2 = sql_query("SELECT username FROM users WHERE id=".sqlesc($arr2['user'])) or sqlerr(__FILE__, __LINE__);
        $a2 = mysqli_fetch_assoc($r2);
        $elapsed = get_date( $arr2['date'],'',0,1);
        
        $HTMLOUT .="<tr><td class='colhead'><a href='userdetails.php?id=".intval($arr2['user'])."'>".htmlspecialchars($a2['username'])."</a></td><td class='colhead'>" . get_date($arr2['date'], '') . "</td><td>$elapsed</td></tr>";
    }
    $HTMLOUT .="</table>";
}

echo stdhead() . $HTMLOUT . stdfoot();
die();
?>
