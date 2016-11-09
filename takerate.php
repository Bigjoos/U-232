<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');

dbconn();
loggedinorreturn();
$lang = array_merge(load_language('global'), load_language('takerate'));


if (!isset($CURUSER))
    stderr("Error", "{$lang['rate_login']}");

if (!mkglobal("rating:id"))
    stderr("Error", "{$lang['rate_miss_form_data']}");

$id = 0 + $id;
if (!$id)
    stderr("Error", "{$lang['rate_invalid_id']}");

$rating = 0 + $rating;
if ($rating <= 0 || $rating > 5)
    stderr("Error", "{$lang['rate_invalid']}");

$res = sql_query("SELECT owner FROM torrents WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
if (!$row)
    stderr("Error", "{$lang['rate_torrent_not_found']}");

$time_now = sqlesc(time());
$res = sql_query("INSERT INTO ratings (torrent, user, rating, added) VALUES (" . sqlesc($id) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($rating) . ", $time_now)") or sqlerr(__FILE__, __LINE__);
if (!$res) {
    if (((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062)
        stderr("Error", "{$lang['rate_already_voted']}");
    else
        ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
}

sql_query("UPDATE torrents SET numratings = numratings + 1, ratingsum = ratingsum + " . sqlesc($rating) . " WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
//===add karma 
sql_query("UPDATE users SET seedbonus = seedbonus+5.0 WHERE id = " . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
//===end
header("Refresh: 0; url=details.php?id=$id&rated=1");

?>
