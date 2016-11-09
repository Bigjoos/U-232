<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
//== Bookmark.php - by pdq
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'user_functions.php');
dbconn();
loggedinorreturn();

$lang = array_merge(load_language('global'));

$HTMLOUT = '';

if (!mkglobal("torrent"))
    stderr("Error", "missing form data");

$userid = intval($CURUSER['id']);
if (!is_valid_id($userid))
    stderr("Error", "Invalid ID.");

if ($userid != intval($CURUSER["id"]))
    stderr("Error", "Access denied.");

$torrentid = intval($_GET["torrent"]);
if (!is_valid_id($torrentid))
    die();

if (!isset($torrentid))
    stderr("Error", "Failed. No torrent selected");

$action = isset($_GET["action"]) ? htmlspecialchars($_GET["action"]) : '';

if ($action == 'add') {
    
    $torrentid = intval($_GET['torrent']);
    $sure      = isset($_GET['sure']) ? intval($_GET['sure']) : '';
    
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");
    
    $hash = md5('s5l6t0mu55yt4hwa7e5' . $torrentid . 'add' . 's5l6t0mu55yt4hwa7e5');
    if (!$sure)
        stderr("Add Bookmark", "Do you really want to add this bookmark? Click\n" . "<a href='?torrent=$torrentid&amp;action=add&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", false);
    
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');
    
    function addbookmark($torrentid)
    {
        global $CURUSER;
        if ((get_row_count("bookmarks", "WHERE userid=" . sqlesc($CURUSER['id']) . " AND torrentid =" . sqlesc($torrentid))) > 0)
            stderr("Error", "Torrent already bookmarked");
        sql_query("INSERT INTO bookmarks (userid, torrentid) VALUES (" . sqlesc($CURUSER['id']) . ", " . sqlesc($torrentid) . ")") or sqlerr(__FILE__, __LINE__);
    }
    
    $HTMLOUT .= addbookmark($torrentid);
    $HTMLOUT .= "<h2>Bookmark added!</h2>";
}

if ($action == 'delete') {
    $torrentid = intval($_GET['torrent']);
    $sure      = isset($_GET['sure']) ? intval($_GET['sure']) : '';
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");
    
    $hash = md5('s5l6t0mu55yt4hwa7e5' . $torrentid . 'delete' . 's5l6t0mu55yt4hwa7e5');
    if (!$sure)
        stderr("Delete Bookmark", "Do you really want to delete this bookmark? Click\n" . "<a href='?torrent=$torrentid&amp;action=delete&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", false);
    
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');
    
    function deletebookmark($torrentid)
    {
        global $CURUSER;
        sql_query("DELETE FROM bookmarks WHERE torrentid =" . sqlesc($torrentid) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    
    $HTMLOUT .= deletebookmark($torrentid);
    $HTMLOUT .= "<h2>Bookmark deleted!</h2>";
}

elseif ($action == 'public') {
    $torrentid = intval($_GET['torrent']);
    $sure      = isset($_GET['sure']) ? intval($_GET['sure']) : '';
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");
    
    $hash = md5('s5l6t0mu55yt4hwa7e5' . $torrentid . 'public' . 's5l6t0mu55yt4hwa7e5');
    if (!$sure)
        stderr("Share Bookmark", "Do you really want to mark this bookmark public? Click\n" . "<a href='?torrent=$torrentid&amp;action=public&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", false);
    
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');
    
    function publickbookmark($torrentid)
    {
        global $CURUSER;
        sql_query("UPDATE bookmarks SET private = 'no' WHERE private = 'yes' AND torrentid = " . sqlesc($torrentid) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    
    $HTMLOUT .= publickbookmark($torrentid);
    $HTMLOUT .= "<h2>Bookmark made public!</h2>";
} elseif ($action == 'private') {
    $torrentid = intval($_GET['torrent']);
    $sure      = isset($_GET['sure']) ? intval($_GET['sure']) : '';
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");
    
    $hash = md5('s5l6t0mu55yt4hwa7e5' . $torrentid . 'private' . 's5l6t0mu55yt4hwa7e5');
    if (!$sure)
        stderr("Make Bookmark Private", "Do you really want to mark this bookmark private? Click\n" . "<a href='?torrent=$torrentid&amp;action=private&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", false);
    
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');
    
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");
    
    function privatebookmark($torrentid)
    {
        global $CURUSER;
        sql_query("UPDATE bookmarks SET private = 'yes' WHERE private = 'no' AND torrentid = " . sqlesc($torrentid) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    
    $HTMLOUT .= privatebookmark($torrentid);
    $HTMLOUT .= "<h2>Bookmark made private!</h2>";
}

if (isset($_POST["returnto"]))
    $ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Go back to whence you came</a>";
else
    $ret = "<a href=\"bookmarks.php\">Go to My Bookmarks</a><br /><br />
<a href=\"browse.php\">Go to Browse</a>";
$HTMLOUT .= $ret;
echo stdhead('Bookmark') . $HTMLOUT . stdfoot();
?>
