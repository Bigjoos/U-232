<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'user_functions.php');
dbconn();
loggedinorreturn();
parked();

$lang    = array_merge(load_language('global'));
$HTMLOUT = '';
$id_2    = '';
$id_2b   = '';
// === Now all reports just use a single var $id and a type thanks dokty... again :P
$id      = ($_GET["id"] ? intval($_GET["id"]) : intval($_POST["id"]));
$type    = ($_GET["type"] ? htmlspecialchars($_GET["type"]) : htmlspecialchars($_POST["type"]));
if (!is_valid_id($id))
    stderr("Error", "Bad ID!");
$typesallowed = array(
    "User",
    "Comment",
    "Request_Comment",
    "Offer_Comment",
    "Request",
    "Offer",
    "Torrent",
    "Hit_And_Run",
    "Post"
);
if (!in_array($type, $typesallowed))
    stderr("Error", "What you are trying to report doesn't exist!");
// === Still need a second value passed for stuff like hit and run where you need two id's :P
if ((isset($_GET["id_2"])) || (isset($_POST["id_2"]))) {
    $id_2 = ($_GET["id_2"] ? intval($_GET["id_2"]) : intval($_POST["id_2"]));
    if (!is_valid_id($id_2))
        stderr("Error", "I smell a rat!");
    $id_2b = "&amp;id_2=$id_2";
}

if ((isset($_GET["do_it"])) || (isset($_POST["do_it"]))) {
    $do_it = ($_GET["do_it"] ? intval($_GET["do_it"]) : intval($_POST["do_it"]));
    if (!is_valid_id($do_it))
        stderr("Error", "I smell a rat!");
    // == Make sure the reason is filled out and is set
    $reason = htmlspecialchars($_POST["reason"]);
    if (empty($_POST["reason"]))
        stderr("Error", "You MUST enter a reason for this report! Use your back button and fill in the reason");
    // === check if it's been reported already
    $res = sql_query("SELECT id FROM reports WHERE reported_by = " . sqlesc($CURUSER['id']) . " AND reporting_what = " . sqlesc($id) . " AND reporting_type = " . sqlesc($type)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) != 0)
        stderr("Report Failure!", "You have allready reported <b>" . str_replace("_", " ", $type) . "</b> with id: <b>$id</b>!");
    // === ok it's not been reported yet let's go on
    $dt = sqlesc(time());
    sql_query("INSERT into reports (reported_by, reporting_what, reporting_type, reason, added, 2nd_value) VALUES (" . sqlesc($CURUSER['id']) . ", " . sqlesc($id) . ", " . sqlesc($type) . ", " . sqlesc($reason) . ", $dt, " . sqlesc($id_2) . ")") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "<table width='650'><tr><td class='colhead'><h1>Success!</h1></td></tr>" . "<tr><td class='clearalt6' align='center'>Successfully Reported <b>" . str_replace("_", " ", $type) . "</b> with id: <b>$id</b>!<br /><b>Reason:</b> $reason</td></tr></table>";
    echo stdhead("Reports") . $HTMLOUT . stdfoot();
    die();
} //=== End do_it
// === Starting main page for reporting all...
$HTMLOUT .= "<form method='post' action='report.php?type=$type$id_2b&amp;id=$id&amp;do_it=1'>
    <table width='650'>
    <tr><td class='colhead' colspan='2'>
    <h1>Report: " . str_replace("_", " ", $type) . "</h1></td></tr>" . "
    <tr><td class='clearalt6' colspan='2' align='center'>
    <img src='{$INSTALLER09['pic_base_url']}warned.gif' alt='warned' title='Warned' border='0' /> Are you sure you would like to report <b>" . str_replace("_", " ", $type) . "</b> with id: <b>$id</b>" . "
    <img src='{$INSTALLER09['pic_base_url']}warned.gif' alt='warned' title='Warned' border='0' /><br />to the Staff for violation of the <a class='altlink' href='rules.php' target='_blank'>rules</a>?</td></tr>" . "
    <tr><td class='clearalt6' align='right'><b>Reason:</b></td><td class='clearalt6'><textarea name='reason' cols='70' rows='5'></textarea> [ required ]<br /></td></tr>" . "
    <tr><td class='clearalt6' colspan='2' align='center'><input type='submit' class='button' value='Confirm Report' /></td></tr></table></form>";
echo stdhead("Report") . $HTMLOUT . stdfoot();
die;
?>
