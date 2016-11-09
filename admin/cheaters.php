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
require_once(INCL_DIR.'html_functions.php');
require_once(INCL_DIR.'pager_functions.php');


$lang = array_merge( $lang, load_language('cheaters') );

$HTMLOUT="";

if (!min_class(UC_MODERATOR)) // or just simply: if (!min_class(UC_STAFF))
stderr($lang['cheaters_error'], "{$lang['cheaters_rc']}");

if (isset($_POST["nowarned"]) && htmlspecialchars($_POST["nowarned"]) == "nowarned") {
    if (empty($_POST["desact"]) && empty($_POST["remove"]))
        stderr("Error...", "You must select a user.");

    if (!empty($_POST["remove"])) {
        sql_query("DELETE FROM cheaters WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST["remove"])) . ")") or sqlerr(__FILE__, __LINE__);
    }

    if (!empty($_POST["desact"])) {
        sql_query("UPDATE users SET enabled = 'no' WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST["desact"])) . ")") or sqlerr(__FILE__, __LINE__);
    }
}

$HTMLOUT .= begin_main_frame();
$HTMLOUT .= begin_frame("Cheating Users:", true);

$res = sql_query("SELECT COUNT(*) FROM cheaters") or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res);
$count = intval($row[0]);
$perpage = 15;
$pager = pager($perpage, $count, "admin.php?action=cheaters&amp;");


$HTMLOUT .="<form action='admin.php?action=cheaters' method='post'>
<script type='text/javascript'>
/*<![CDATA[*/
function klappe(id)
{var klappText=document.getElementById('k'+id);var klappBild=document.getElementById('pic'+id);if(klappText.style.display=='none'){klappText.style.display='block';}
else{klappText.style.display='none';}}
function klappe_news(id)
{var klappText=document.getElementById('k'+id);var klappBild=document.getElementById('pic'+id);if(klappText.style.display=='none'){klappText.style.display='block';klappBild.src='{$TBDEV['pic_base_url']}minus.gif';}
else{klappText.style.display='none';klappBild.src='{$TBDEV['pic_base_url']}plus.gif';}}	
</script>
<script type='text/javascript'>
var checkflag = 'false';
function check(field) {
if (checkflag == 'false') {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = 'true';
return 'Uncheck All Disable'; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = 'false';
return 'Check All Disable'; }
}
function check2(field) {
if (checkflag == 'false') {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = 'true';
return 'Uncheck All Remove'; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = 'false';
return 'Check All Remove'; }
}
/*]]>*/
</script>";

$HTMLOUT .= $pager['pagertop'];
$HTMLOUT .="<table width=\"80%\">
<tr>
<td class=\"tableb\" width=\"10\" align=\"center\" valign=\"middle\">#</td>
<td class=\"tableb\">{$lang['cheaters_uname']}</td>
<td class=\"tableb\" width=\"10\" align=\"center\" valign=\"middle\">{$lang['cheaters_d']}</td>
<td class=\"tableb\" width=\"10\" align=\"center\" valign=\"middle\">{$lang['cheaters_r']}</td></tr>\n";

$res = sql_query("SELECT * FROM cheaters ORDER BY added DESC ".$pager['limit']) or sqlerr(__FILE__, __LINE__);
while ($arr = mysqli_fetch_assoc($res)) {
    $rrr = sql_query("SELECT id, username, class, downloaded, uploaded FROM users WHERE id = ".sqlesc($arr['userid'])) or sqlerr(__FILE__, __LINE__);
    $aaa = mysqli_fetch_assoc($rrr);

    $rrr2 = sql_query("SELECT name FROM torrents WHERE id = ".sqlesc($arr['torrentid'])) or sqlerr(__FILE__, __LINE__);
    $aaa2 = mysqli_fetch_assoc($rrr2);

    if ($aaa["downloaded"] > 0) {
        $ratio = number_format($aaa["uploaded"] / $aaa["downloaded"], 3);
    } else {
        $ratio = "---";
    }
    $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";

    $uppd = mksize($arr["upthis"]);

    $cheater = "<b><a href='{$TBDEV['baseurl']}/userdetails.php?id=".intval($aaa['id'])."'>".htmlspecialchars($aaa['username'])."</a></b>{$lang['cheaters_hbcc']}<br /><br />{$lang['cheaters_upped']} <b>$uppd</b><br />{$lang['cheaters_speed']} <b>".mksize($arr['rate'])."/s</b><br />{$lang['cheaters_within']} <b>".intval($arr['timediff'])." {$lang['cheaters_sec']}</b><br />{$lang['cheaters_uc']} <b>".htmlspecialchars($arr['client'])."</b><br />{$lang['cheaters_ipa']} <b>".htmlspecialchars($arr['userip'])."</b>";

    $HTMLOUT .="<tr><td class=\"tableb\" width=\"10\" align=\"center\">".intval($arr['id'])."</td>
    <td class=\"tableb\" align=\"left\"><a href=\"javascript:klappe('a1".intval($arr['id'])."')\">".htmlspecialchars($aaa['username'])."</a> - Added: ".get_date($arr['added'], 'DATE')."
    <div id=\"ka1$arr[id]\" style=\"display: none;\"><font color=\"red\">$cheater</font></div></td>
    <td class=\"tableb\" valign=\"top\" width=\"10\"><input type=\"checkbox\" name=\"desact[]\" value=\"" . intval($aaa["id"]) . "\"/></td>
    <td class=\"tableb\" valign=\"top\" width=\"10\"><input type=\"checkbox\" name=\"remove[]\" value=\"" . intval($arr["id"]) . "\"/></td></tr>";
}

$HTMLOUT .="<tr>
<td class=\"tableb\" colspan=\"4\" align=\"right\">
<input type=\"button\" value=\"{$lang['cheaters_cad']}\" onclick=\"this.value=check(this.form.elements['desact[]'])\"/> <input type=\"button\" value=\"{$lang['cheaters_car']}\" onclick=\"this.value=check(this.form.elements['remove[]'])\"/> <input type=\"hidden\" name=\"nowarned\" value=\"nowarned\" /><input type=\"submit\" name=\"submit\" value=\"{$lang['cheaters_ac']}\" />
</td>
</tr>
</table></form>";

$HTMLOUT .= $pager['pagerbottom'];

$HTMLOUT .= end_frame();
$HTMLOUT .= end_main_frame();
echo stdhead('Ratio Cheats') . $HTMLOUT . stdfoot();
die;
?>
