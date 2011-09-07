<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
dbconn();
loggedinorreturn();
$lang = array_merge( load_language('global'));
// / Mod by dokty - tbdev.net

$id = 0 + $_GET["id"];
$points = 0 + $_GET["points"];
if (!is_valid_id($id) || !is_valid_id($points))
    die();

$pointscangive = array("10", "20", "50", "100", "200", "500", "1000");
if (!in_array($points, $pointscangive))
    stderr("Error", "You can't give that amount of points!!!");

$sdsa = mysql_query("SELECT 1 FROM coins WHERE torrentid=" . sqlesc($id) . " AND userid =" . sqlesc($CURUSER["id"])) or die();
$asdd = mysql_fetch_array($sdsa);
if ($asdd)
    stderr("Error", "You already gave points to this torrent.");

$res = mysql_query("SELECT owner,name FROM torrents WHERE id = " . sqlesc($id)) or die();
$row = mysql_fetch_assoc($res) or stderr("Error", "Torrent was not found");
$userid = $row["owner"];

if ($userid == $CURUSER["id"])
    stderr("Error", "You can't give your self points!");

if ($CURUSER["seedbonus"] < $points)
    stderr("Error", "You dont have enough points");

mysql_query("INSERT INTO coins (userid, torrentid, points) VALUES (" . sqlesc($CURUSER["id"]) . ", " . sqlesc($id) . ", " . sqlesc($points) . ")") or sqlerr(__FILE__, __LINE__);
mysql_query("UPDATE users SET seedbonus=seedbonus+" . $points . " WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
mysql_query("UPDATE users SET seedbonus=seedbonus-" . $points . " WHERE id=" . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
mysql_query("UPDATE torrents SET points=points+" . $points . " WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$msg = sqlesc("You have been given " . $points . " points by " . $CURUSER["username"] . " for torrent [url=" . $TBDEV['baseurl'] . "/details.php?id=" . $id . "]" . $row["name"] . "[/url].");
mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES(0, $userid, $msg, " . sqlesc(time()) . ", 'You have been given a gift')") or sqlerr(__FILE__, __LINE__);
stderr("Done", "Successfully gave points to this torrent.");

?>