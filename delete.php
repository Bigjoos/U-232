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

$lang = array_merge(load_language('global'), load_language('delete'));

if (!mkglobal("id"))
    stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");

$id = intval($id);
if (!is_valid_id($id))
    stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");

function deletetorrent($id)
{
    global $INSTALLER09;
    sql_query("DELETE FROM torrents WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM coins WHERE torrentid = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM bookmarks WHERE torrentid = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM snatched WHERE torrentid = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    foreach (explode(".", "peers.files.comments.ratings") as $x)
        sql_query("DELETE FROM $x WHERE torrent = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$INSTALLER09['torrent_dir']}/$id.torrent");
}

$res = sql_query("SELECT name,owner,seeders FROM torrents WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
if (!$row)
    stderr("{$lang['delete_failed']}", "{$lang['delete_not_exist']}");

if ($CURUSER["id"] != $row["owner"] && $CURUSER["class"] < UC_MODERATOR)
    stderr("{$lang['delete_failed']}", "{$lang['delete_not_owner']}\n");

$rt = intval($_POST["reasontype"]);

if (!is_int($rt) || $rt < 1 || $rt > 5)
    bark("{$lang['delete_invalid']}");

//$r = $_POST["r"]; // whats this
$reason = htmlspecialchars($_POST["reason"]);

if ($rt == 1)
    $reasonstr = "{$lang['delete_dead']}";
elseif ($rt == 2)
    $reasonstr = "{$lang['delete_dupe']}" . ($reason[0] ? (": " . trim($reason[0])) : "!");
elseif ($rt == 3)
    $reasonstr = "{$lang['delete_nuked']}" . ($reason[1] ? (": " . trim($reason[1])) : "!");
elseif ($rt == 4) {
    if (!$reason[2])
        stderr("{$lang['delete_failed']}", "{$lang['delete_violated']}");
    $reasonstr = $INSTALLER09['site_name'] . "{$lang['delete_rules']}" . trim($reason[2]);
} else {
    if (!$reason[3])
        stderr("{$lang['delete_failed']}", "{$lang['delete_reason']}");
    $reasonstr = trim($reason[3]);
}

deletetorrent($id);
write_log("{$lang['delete_torrent']} $id ({$row['name']}){$lang['delete_deleted_by']}{$CURUSER['username']} ($reasonstr)\n");
//===remove karma 
sql_query("UPDATE users SET seedbonus = seedbonus-15.0 WHERE id = " . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
//===end
if (isset($_POST["returnto"]))
    $ret = "<a href='" . htmlspecialchars($_POST["returnto"]) . "'>{$lang['delete_go_back']}</a>";
else
    $ret = "<a href='{$INSTALLER09['baseurl']}/browse.php'>{$lang['delete_back_browse']}</a>";
$HTMLOUT = '';
$HTMLOUT .= "<h2>{$lang['delete_deleted']}</h2>
    <p>$ret</p>";

echo stdhead("{$lang['delete_deleted']}") . $HTMLOUT . stdfoot();

?>
