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
	print $HTMLOUT;
	exit();
}


require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'pager_functions.php');
require_once(INCL_DIR.'html_functions.php');

$lang = array_merge( $lang );

$HTMLOUT = '';

$delt_link='';

$type='';

if (!min_class(UC_STAFF)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

// === cute solved in thing taked from helpdesk mod
function round_time($ts)
{
    $mins = floor($ts / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    $t = "";
    if ($weeks > 0)
        return "$weeks week" . ($weeks > 1 ? "s" : "");
    if ($days > 0)
        return "$days day" . ($days > 1 ? "s" : "");
    if ($hours > 0)
        return "$hours hour" . ($hours > 1 ? "s" : "");
    if ($mins > 0)
        return "$mins min" . ($mins > 1 ? "s" : "");
    return "< 1 min";
}
// === now all reports just use a single var $id and a type thanks dokty... again :P
if (isset($_GET["id"])) {
    $id = ($_GET["id"] ? intval($_GET["id"]) : intval($_POST["id"]));
    if (!is_valid_id($id))
        stderr("Error", "Bad ID!");
}
if (isset($_GET["type"])) {
    $type = ($_GET["type"] ? htmlspecialchars($_GET["type"]) : htmlspecialchars($_POST["type"]));
    $typesallowed = array("User", "Comment", "Request_Comment", "Offer_Comment", "Request", "Offer", "Torrent", "Hit_And_Run", "Post");
    if (!in_array($type, $typesallowed))
        stderr("Error", "Bad report type!");
}
// === Let's deal with this damn report :P
if ((isset($_GET["deal_with_report"])) || (isset($_POST["deal_with_report"]))) { 
    if (empty($_POST["id"]))
    stderr("Error", "I smell a rat!");
    $how_delt_with = "how_delt_with = " . sqlesc($_POST["how_delt_with"]);
    $when_delt_with = "when_delt_with = " . sqlesc(time());
    sql_query("UPDATE reports SET delt_with = 1, $how_delt_with, $when_delt_with , who_delt_with_it = ".sqlesc($CURUSER['id'])." WHERE delt_with!=1 AND id = ".sqlesc($_POST['id'])) or sqlerr(__FILE__, __LINE__);
    }
    // === end deal_with_report
    // === main reports page
    $HTMLOUT .="<table width='600'><tr><td class='colhead'><h1>Active Reports</h1></td></tr><tr><td class='clearalt6' align='center'>";
    // === if get delete
    if ((isset($_GET["delete"])) && ($CURUSER["class"] == UC_SYSOP)) {
    $res = sql_query("DELETE FROM reports WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .="<h1>Report Deleted!</h1>\n";
    }
    // === get the count make the page
   $res = sql_query("SELECT count(id) FROM reports") or sqlerr(__FILE__, __LINE__);
   $row = mysqli_fetch_array($res);
   $count = $row[0];
   $perpage = 15;
   $pager = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?&amp;");

   if ($count == '0')
   $HTMLOUT .="<p align='center'><b>No Reports, they are all playing nice!</b></p></td></tr>";
   else {
   $HTMLOUT .= $pager['pagertop'];
   $HTMLOUT .="<form method='post' action='admin.php?action=reports&amp;deal_with_report=1'>
   <table width='650'><tr><td class='colhead' align='left' valign='top'>Added</td>
   <td class='colhead' align='left' valign='top'>Reported by</td>" . "
   <td class='colhead' align='left' valign='top'>Reporting What</td>"."
   <td class='colhead' align='left' valign='top'>Type</td><td class='colhead' align='left' valign='top'>Reason</td>" . "
   <td class='colhead' align='center' valign='top'>Dealt With</td><td class='colhead' align='center' valign='top'>Deal With It</td>" . "
   " . ($CURUSER["class"] == UC_SYSOP ? "<td class='colhead' align='center' valign='top'>Delete</td>" : "") . "</tr>";
    // === get the info
    $res_info = sql_query("SELECT reports.id, reports.reported_by, reports.reporting_what, reports.reporting_type, reports.reason, reports.who_delt_with_it, reports.delt_with, reports.added, reports.how_delt_with, reports.when_delt_with, reports.2nd_value, users.username FROM reports INNER JOIN users on reports.reported_by = users.id ORDER BY id DESC ".$pager['limit']) or sqlerr(__FILE__, __LINE__);
    while ($arr_info = mysqli_fetch_assoc($res_info)) {
        // =======change colors thanks Jaits
        $count2='';
        $count2 = (++$count2) % 2;
        $class = 'clearalt' . ($count2 == 0?'6':'7');
        // =======end
        // === cute solved in thing taked from helpdesk mod by nuerher
        $added = $arr_info["added"];
        $solved_date = $arr_info["when_delt_with"];

        if ($solved_date == "0") {
            $solved_in = " [N/A]";
            $solved_color = "pink";
        } else {
            $solved_in_wtf = $arr_info["when_delt_with"] - $arr_info["added"];
            $solved_in = "&nbsp;[" . round_time($solved_in_wtf) . "]";

            if ($solved_in_wtf > 4 * 3600)
                $solved_color = "red";
            else if ($solved_in_wtf > 2 * 3600)
                $solved_color = "yellow";
            else if ($solved_in_wtf <= 3600)
                $solved_color = "green";
        }
        // === has it been delt with yet?
        if ($arr_info["delt_with"]) {
            $res_who = sql_query("SELECT username FROM users WHERE id=".sqlesc($arr_info['who_delt_with_it'])) or sqlerr(__FILE__, __LINE__);
            $arr_who = mysqli_fetch_assoc($res_who);
            $dealtwith = "<font color='" . $solved_color . "'><b>Yes -</b> </font> by: <a class='altlink' href='userdetails.php?id=".intval($arr_info['who_delt_with_it'])."'><b>".htmlspecialchars($arr_who['username'])."</b></a><br /> in: <font color='" . $solved_color . "'>" . $solved_in . "</font>";
            $checkbox = "<input type='radio' name='id' value='".intval($arr_info['id'])."' disabled='disabled' />";
        } else {
            $dealtwith = "<font color='red'><b>No</b></font>";
            $checkbox = "<input type='radio' name='id' value='".intval($arr_info['id'])."' />";
        }
        // === make a link to the reported thing
        if ($arr_info["reporting_type"] != "") {
            switch ($arr_info["reporting_type"]) {
                case "User":
                    $res_who2 = sql_query("SELECT username FROM users WHERE id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='userdetails.php?id=".intval($arr_info['reporting_what'])."'><b>".htmlspecialchars($arr_who2['username'])."</b></a>";
                    break;
                case "Comment":
                    $res_who2 = sql_query("SELECT comments.user, users.username, torrents.id FROM comments, users, torrents WHERE comments.user = users.id AND comments.id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='details.php?id=".intval($arr_who2['id'])."&amp;viewcomm=".intvala($arr_info['reporting_what'])."#comm".intval($arr_info['reporting_what'])."'><b>".htmlspecialchars($arr_who2['username'])."</b></a>";
                    break;
                case "Request_Comment":
                    $res_who2 = sql_query("SELECT comments.request, comments.user, users.username FROM comments, users WHERE comments.user = users.id AND comments.id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='viewrequests.php?id=".intval($arr_who2['request'])."&amp;req_details=1&amp;viewcomm=".intval($arr_info['reporting_what'])."#comm".intval($arr_info['reporting_what'])."'><b>".htmlspecialchars($arr_who2['username'])."</b></a>";
                    break;
                case "Offer_Comment":
                    $res_who2 = sql_query("SELECT comments.offer, comments.user, users.username FROM comments, users WHERE comments.user = users.id AND comments.id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='viewoffers.php?id=".intval($arr_who2['offer'])."&amp;off_details=1&amp;viewcomm=".intval($arr_info['reporting_what'])."#comm".intval($arr_info['reporting_what'])."'><b>".htmlspecialchars($arr_who2['username'])."</b></a>";
                    break;
                case "Request":
                    $res_who2 = sql_query("SELECT request FROM requests WHERE id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='viewrequests.php?id=".intval($arr_info['reporting_what'])."&amp;req_details=1'><b>" . htmlspecialchars($arr_who2['request']) . "</b></a>";
                    break;
                case "Offer":
                    $res_who2 = sql_query("SELECT name FROM offers WHERE id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='viewoffers.php?id=".intval($arr_info['reporting_what'])."&amp;off_details=1'><b>" . htmlspecialchars($arr_who2['name']) . "</b></a>";
                    break;
                case "Torrent":
                    $res_who2 = sql_query("SELECT name FROM torrents WHERE id = ".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='altlink' href='details.php?id=".intval($arr_info['reporting_what'])."'><b>" . htmlspecialchars($arr_who2['name']) . "</b></a>";
                    break;
                case "Hit_And_Run":
                    $res_who2 = sql_query("SELECT users.username, torrents.name, r.2nd_value FROM users, torrents LEFT JOIN reports AS r ON r.2nd_value = torrents.id WHERE users.id=".sqlesc($arr_info['reporting_what'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<b>user:</b> <a class='altlink' href='userdetails.php?id=" . intval($arr_info['reporting_what']) . "&amp;completed=1'><b>" . htmlspecialchars($arr_who2['username']) . "</b></a><br />hit and run on:<br /> <a class='altlink' href='details.php?id=" . intval($arr_info['2nd_value']) . "&amp;page2=0#snatched'><b>" . htmlspecialchars($arr_who2['name']) . "</b></a>";
                    break;
                case "Post":
                    $res_who2 = sql_query("SELECT subject FROM topics WHERE id = " . sqlesc($arr_info['2nd_value'])) or sqlerr(__FILE__, __LINE__);
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<b>post:</b> <a class='altlink' href='forums.php?action=viewtopic&amp;topicid=" . intval($arr_info['2nd_value']) . "&amp;page=last#" . intval($arr_info['reporting_what']) . "'><b>" . htmlspecialchars($arr_who2['subject']) . "</b></a>";
                    break;
            }
        }
        
        $HTMLOUT .="<tr><td align='left' valign='top' class='$class'>" .get_date($arr_info['added'], 'DATE',0,1) . "</td>
        <td align='left' valign='top' class='$class'><a class='altlink' href='userdetails.php?id=" . intval($arr_info['reported_by']) . "'>" . "<b>" . htmlspecialchars($arr_info['username']) . "</b></a></td>
        <td align='left' valign='top' class='$class'>{$link_to_thing}</td>
        <td align='left' valign='top' class='$class'><b>" . str_replace("_" , " ", $arr_info["reporting_type"]) . "</b>" . "</td>
        <td align='left' valign='top' class='$class'>" .htmlspecialchars($arr_info['reason']) . "</td>
        <td align='center' valign='top' class='$class'>{$dealtwith} {$delt_link}</td>
        <td align='center' valign='middle' class='$class'>{$checkbox}</td>" . ($CURUSER["class"] == UC_SYSOP ? "<td align='center' valign='middle' class='$class'><a class='altlink' href='admin.php?action=reports&amp;id=" . intval($arr_info['id']) . "&amp;delete=1'><font color='red'>Delete</font></a></td>" : "") . "</tr>\n";
        // ===how was it delt with?
        if ($arr_info['how_delt_with'])
        $HTMLOUT .="<tr>
        <td colspan='" . ($CURUSER["class"] == UC_SYSOP ? "6" : "5") . "' class='$class' align='left'><b>Delt with by " . htmlspecialchars($arr_who['username']) . ":</b> " .get_date($arr_info['when_delt_with'], 'LONG',0,1) . "</td></tr>
        <tr><td colspan='" . ($CURUSER["class"] == UC_SYSOP ? "6" : "5") . "' class='$class' align='left'>" .htmlspecialchars($arr_info['how_delt_with']) . "<br /><br /></td></tr>";
    }
}
$HTMLOUT .="</table>";
if ($count > '0') {
// === deal with it
$HTMLOUT .= "<br /><br /><p align='center'><b>How ".htmlspecialchars($CURUSER['username'])." Delt with this report:</b> [ required ] </p><textarea name='how_delt_with' cols='70' rows='5'></textarea><br /><br />" . "<input type='submit' class='button' value='Confirm' /><br /><br /></form></td></tr></table>";
} //=== end if count
echo stdhead("Active Reports") . $HTMLOUT . stdfoot();
die;
?>
