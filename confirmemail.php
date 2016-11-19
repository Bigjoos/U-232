<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');

$lang = array_merge(load_language('global'), load_language('confirmemail'));

if (!isset($_GET['uid']) OR !isset($_GET['key']) OR !isset($_GET['email']))
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_idiot']}");

if (!preg_match("/^(?:[\d\w]){32}$/", $_GET['key'])) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_no_key']}");
}

if (!preg_match("/^(?:\d){1,}$/", $_GET['uid'])) {
    stderr("{$lang['confirmmail_user-error']}", "{$lang['confirmmail_no_id']}");
}

$id    = intval($_GET['uid']);
$md5   = $_GET['key'];
$email = urldecode($_GET['email']);

if (!validemail($email))
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_false_email']}");

dbconn();


$res = sql_query("SELECT editsecret FROM users WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);

if (!$row)
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");

$sec = hash_pad($row["editsecret"]);

if (preg_match('/^ *$/s', $sec))
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");

if ($md5 != md5($sec . $email . $sec))
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");

sql_query("UPDATE users SET editsecret='', email=" . sqlesc($email) . " WHERE id=" . sqlesc($id) . " AND editsecret=" . sqlesc($row["editsecret"])) or sqlerr(__FILE__, __LINE__);

if (!mysqli_affected_rows($GLOBALS["___mysqli_ston"]))
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
header("Refresh: 0; url={$INSTALLER09['baseurl']}/my.php?emailch=1");
?>
