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

$lang = array_merge(load_language('global'), load_language('confirm'));

$id  = isset($_GET['id']) ? intval($_GET['id']) : 0;
$md5 = isset($_GET['secret']) ? htmlspecialchars($_GET['secret']) : '';

if (!is_valid_id($id))
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");

if (!preg_match("/^(?:[\d\w]){32}$/", $md5)) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_key']}");
}

dbconn();


$res = sql_query("SELECT passhash, editsecret, status FROM users WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);

if (!$row)
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");

if ($row['status'] != 'pending') {
    header("Refresh: 0; url={$TBDEV['baseurl']}/ok.php?type=confirmed");
    exit();
}

$sec = $row['editsecret'];
if ($md5 != $sec)
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");

sql_query("UPDATE users SET status='confirmed', editsecret='' WHERE id=" . sqlesc($id) . " AND status='pending'") or sqlerr(__FILE__, __LINE__);

if (!mysqli_affected_rows($GLOBALS["___mysqli_ston"]))
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");

logincookie($id, $row['passhash']);
header("Refresh: 0; url={$TBDEV['baseurl']}/ok.php?type=confirm");
?>
