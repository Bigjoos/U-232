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
require_once(INCL_DIR . 'bbcode_functions.php');
require_once(INCL_DIR . 'pager_functions.php');
require_once(INCL_DIR . 'html_functions.php');

dbconn(false);

loggedinorreturn();

$lang = array_merge(load_language('global'), load_language('userhistory'));

$userid = (int) $_GET["id"];

if (!is_valid_id($userid))
    stderr($lang['stderr_errorhead'], $lang['stderr_invalidid']);

if ($CURUSER['class'] < UC_POWER_USER || ($CURUSER["id"] != $userid && $CURUSER['class'] < UC_MODERATOR))
    stderr($lang['stderr_errorhead'], $lang['stderr_perms']);

$page = (isset($_GET['page']) ? htmlspecialchars($_GET["page"]) : ''); // not used?

$action = (isset($_GET['action']) ? htmlspecialchars($_GET["action"]) : '');

//-------- Global variables

$perpage = 25;

$HTMLOUT = '';

//-------- Action: View posts

if ($action == "viewposts") {
    $select_is = "COUNT(DISTINCT p.id)";
    
    $from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id";
    
    $where_is = "p.userid = " . sqlesc($userid) . " AND f.minclassread <= " . sqlesc($CURUSER['class']);
    
    $order_is = "p.id DESC";
    
    $query = "SELECT $select_is FROM $from_is WHERE $where_is";
    
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    
    $arr = mysqli_fetch_row($res) or stderr($lang['stderr_errorhead'], $lang['top_noposts']);
    
    $postcount = $arr[0];
    
    //------ Make page menu
    
    $pager = pager($perpage, $postcount, "userhistory.php?action=viewposts&amp;id=$userid&amp;");
    
    //------ Get user data
    
    $res = sql_query("SELECT id, username, class, donor, warned, leechwarn, chatpost, pirate, king, enabled FROM users WHERE id=".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    
    if (mysqli_num_rows($res) == 1) {
        $arr = mysqli_fetch_assoc($res);
        
        $subject = "" . format_username($arr, true);
    } else
        $subject = $lang['posts_unknown'] . '[' . $userid . ']';
    
    //------ Get posts
    
    $from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";
    
    $select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";
    
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is {$pager['limit']}";
    
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    
    if (mysqli_num_rows($res) == 0)
        stderr($lang['stderr_errorhead'], $lang['top_noposts']);
    
    
    
    $HTMLOUT .= "<h1>{$lang['top_posthfor']} $subject</h1>\n";
    
    if ($postcount > $perpage)
        $HTMLOUT .= $pager['pagertop'];
    
    //------ Print table
    
    $HTMLOUT .= begin_main_frame();
    
    $HTMLOUT .= begin_frame();
    
    while ($arr = mysqli_fetch_assoc($res)) {
        $postid = intval($arr["id"]);
        
        $posterid = intval($arr["userid"]);
        
        $topicid = intval($arr["t_id"]);
        
        $topicname = htmlspecialchars($arr["subject"]);
        
        $forumid = intval($arr["f_id"]);
        
        $forumname = htmlspecialchars($arr["name"]);
        
        $dt = (time() - $INSTALLER09['readpost_expiry']);
        
        $newposts = 0;
        
        if ($arr['added'] > $dt)
            $newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;
        
        $added = get_date($arr['added'], '');
        
        $HTMLOUT .= "<div class='sub'><table border='0' cellspacing='0' cellpadding='0'>
          <tr><td class='embedded'>
          $added&nbsp;--&nbsp;<b>{$lang['posts_forum']}:&nbsp;</b>
          <a href='forums.php?action=viewforum&amp;forumid=$forumid'>$forumname</a>
          &nbsp;--&nbsp;<b>{$lang['posts_topic']}:&nbsp;</b>
          <a href='forums.php?action=viewtopic&amp;topicid=$topicid'>$topicname</a>
          &nbsp;--&nbsp;<b>{$lang['posts_post']}:&nbsp;</b>
          #<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=p$postid#$postid'>$postid</a>" . ($newposts ? " &nbsp;<b>(<font color='red'>{$lang['posts_new']}</font>)</b>" : "") . "</td></tr></table></div>\n";
        
        $HTMLOUT .= begin_table(true);
        
        $body = format_comment($arr["body"]);
        
        if (is_valid_id($arr['editedby'])) {
            $subres = sql_query("SELECT username FROM users WHERE id=" . sqlesc($arr['editedby'])) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($subres) == 1) {
                $subrow = mysqli_fetch_assoc($subres);
                $body .= "<p><font size='1' class='small'>{$lang['posts_lasteditedby']} <a href='userdetails.php?id=" . intval($arr['editedby']) . "'><b>" . htmlspecialchars($subrow['username']) . "</b></a> {$lang['posts_at']} " . get_date($arr['editedat'], 'LONG', 1, 0) . " GMT</font></p>\n";
            }
        }
        
        $HTMLOUT .= "<tr valign='top'><td class='comment'>$body</td></tr>\n";
        
        $HTMLOUT .= end_table();
    }
    
    $HTMLOUT .= end_frame();
    
    $HTMLOUT .= end_main_frame();
    
    if ($postcount > $perpage)
        $HTMLOUT .= $pager['pagerbottom'];
    
    echo stdhead($lang['head_post']) . $HTMLOUT . stdfoot();
    
    die;
}

//-------- Action: View comments

if ($action == "viewcomments") {
    $select_is = "COUNT(*)";
    
    // LEFT due to orphan comments
    $from_is = "comments AS c LEFT JOIN torrents as t
                  ON c.torrent = t.id";
    
    $where_is = "c.user = " . sqlesc($userid);
    $order_is = "c.id DESC";
    
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";
    
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    
    $arr = mysqli_fetch_row($res) or stderr($lang['stderr_errorhead'], $lang['top_nocomms']);
    
    $commentcount = intval($arr[0]);
    
    //------ Make page menu
    
    $pager = pager($perpage, $commentcount, "userhistory.php?action=viewcomments&amp;id=$userid&amp;");
    
    //------ Get user data
    
    $res = sql_query("SELECT id, class, username, donor, warned, leechwarn, chatpost, pirate, king,  enabled FROM users WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    
    if (mysqli_num_rows($res) == 1) {
        $arr = mysqli_fetch_assoc($res);
        
        $subject = "" . format_username($arr, true);
    } else
        $subject = $lang['posts_unknown'] . '[' . $userid . ']';
    
    //------ Get comments
    
    $select_is = "t.name, c.torrent AS t_id, c.id, c.added, c.text";
    
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is {$pager['limit']}";
    
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    
    if (mysqli_num_rows($res) == 0)
        stderr($lang['stderr_errorhead'], $lang['top_nocomms']);
    
    
    
    $HTMLOUT .= "<h1>{$lang['top_commhfor']} $subject</h1>\n";
    
    if ($commentcount > $perpage)
        $HTMLOUT .= $pager['pagertop'];
    
    //------ Print table
    
    $HTMLOUT .= begin_main_frame();
    
    $HTMLOUT .= begin_frame();
    
    while ($arr = mysqli_fetch_assoc($res)) {
        
        $commentid = intval($arr["id"]);
        
        $torrent = htmlspecialchars($arr["name"]);
        
        // make sure the line doesn't wrap
        if (strlen($torrent) > 55)
            $torrent = substr($torrent, 0, 52) . "...";
        
        $torrentid = intval($arr["t_id"]);
        
        //find the page; this code should probably be in details.php instead
        
        $subres = sql_query("SELECT COUNT(*) FROM comments WHERE torrent = " . sqlesc($torrentid) . " AND id < " . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        $subrow    = mysqli_fetch_row($subres);
        $count     = $subrow[0];
        $comm_page = floor($count / 20);
        $page_url  = $comm_page ? "&amp;page=$comm_page" : "";
        
        $added = get_date($arr['added'], '') . " (" . get_date($arr['added'], '', 0, 1) . ")";
        
        $HTMLOUT .= "<div class='sub'><table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>" . "$added&nbsp;---&nbsp;<b>{$lang['posts_torrent']}:&nbsp;</b>" . ($torrent ? ("<a href='details.php?id=$torrentid&amp;tocomm=1'>$torrent</a>") : " [{$lang['posts_del']}] ") . "&nbsp;---&nbsp;<b>{$lang['posts_comment']}:&nbsp;</b>#<a href='details.php?id=$torrentid&amp;tocomm=1$page_url'>$commentid</a>
        </td></tr></table></div>\n";
        
        $HTMLOUT .= begin_table(true);
        
        $body = format_comment($arr["text"]);
        
        $HTMLOUT .= "<tr valign='top'><td class='comment'>$body</td></tr>\n";
        
        $HTMLOUT .= end_table();
    }
    
    $HTMLOUT .= end_frame();
    
    $HTMLOUT .= end_main_frame();
    
    if ($commentcount > $perpage)
        $HTMLOUT .= $pager['pagerbottom'];
    
    echo stdhead($lang['head_comm']) . $HTMLOUT . stdfoot();
    
    die;
}

//-------- Handle unknown action

if ($action != "")
    stderr($lang['stderr_histerrhead'], $lang['stderr_unknownact']);

//-------- Any other case

stderr($lang['stderr_histerrhead'], $lang['stderr_invalidq']);

?>
