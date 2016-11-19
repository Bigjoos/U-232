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
dbconn(false);
loggedinorreturn();

$lang = array_merge(load_language('global'), load_language('friends'));

$userid = isset($_GET['id']) ? (int) $_GET['id'] : $CURUSER['id'];
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';

//if (!$userid)
//	$userid = $CURUSER['id'];

if (!is_valid_id($userid))
    stderr($lang['friends_error'], $lang['friends_invalid_id']);

if ($userid != $CURUSER["id"])
    stderr($lang['friends_error'], $lang['friends_no_access']);


// action: add -------------------------------------------------------------

if ($action == 'add') {
    $targetid = intval($_GET['targetid']);
    $type     = htmlspecialchars($_GET['type']);
    
    if (!is_valid_id($targetid))
        stderr($lang['friends_error'], $lang['friends_invalid_id']);
    
    if ($type == 'friend') {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
    } elseif ($type == 'block') {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    } else
        stderr($lang['friends_error'], $lang['friends_unknown']);
    
    $r = sql_query("SELECT id FROM $table_is WHERE userid=" . sqlesc($userid) . " AND $field_is=" . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($r) == 1)
        stderr($lang['friends_error'], sprintf($lang['friends_already'], htmlentities($table_is)));
    
    
    sql_query("INSERT INTO $table_is VALUES (0," . sqlesc($userid) . ", " . sqlesc($targetid) . ")") or sqlerr(__FILE__, __LINE__);
    header("Location: {$INSTALLER09['baseurl']}/friends.php?id=$userid#$frag");
    die;
}

// action: delete ----------------------------------------------------------

if ($action == 'delete') {
    $targetid = (int) $_GET['targetid'];
    $sure     = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
    $type     = isset($_GET['type']) ? ($_GET['type'] == 'friend' ? 'friend' : 'block') : stderr($lang['friends_error'], 'LoL');
    
    if (!is_valid_id($targetid))
        stderr($lang['friends_error'], $lang['friends_invalid_id']);
    
    if (!$sure)
        stderr("{$lang['friends_delete']} $type", "{$lang['friends_sure']}", $type, $userid, $type, $targetid);
    
    if ($type == 'friend') {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) == 0)
            stderr($lang['friends_error'], $lang['friends_no_friend']);
        $frag = "friends";
    } elseif ($type == 'block') {
        sql_query("DELETE FROM blocks WHERE userid=" . sqlesc($userid) . " AND blockid=" . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) == 0)
            stderr($lang['friends_error'], $lang['friends_no_block']);
        $frag = "blocks";
    } else
        stderr($lang['friends_error'], $lang['friends_unknown']);
    
    header("Location: {$INSTALLER09['baseurl']}/friends.php?id=$userid#$frag");
    die;
}

// main body  -----------------------------------------------------------------

$res = sql_query("SELECT * FROM users WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$user = mysqli_fetch_assoc($res) or stderr($lang['friends_error'], $lang['friends_no_user']);
//stderr("Error", "No user with ID.");

$HTMLOUT = '';

$donor  = ($user["donor"] == "yes") ? "<img src='{$INSTALLER09['pic_base_url']}starbig.gif' alt='{$lang['friends_donor']}' style='margin-left: 4pt' />" : '';
$warned = ($user["warned"] == "yes") ? "<img src='{$INSTALLER09['pic_base_url']}warnedbig.gif' alt='{$lang['friends_warned']}' style='margin-left: 4pt' />" : '';

/////////////////////// FRIENDS BLOCK ///////////////////////////////////////

$res = sql_query("SELECT f.friendid as id, u.username AS name, u.class, u.chatpost, u.leechwarn, u.username, u.avatar, u.offavatar, u.pirate, u.king, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=" . sqlesc($userid) . " ORDER BY name") or sqlerr(__FILE__, __LINE__);

$count   = mysqli_num_rows($res);
$friends = '';

if (!$count) {
    $friends = "<em>{$lang['friends_friends_empty']}.</em>";
} else {
    
    while ($friend = mysqli_fetch_assoc($res)) {
        $title = $friend["title"];
        if (!$title)
            $title = get_user_class_name($friend["class"]);
        
        $userlink = "<a href='userdetails.php?id=" . intval($friend['id']) . "'></a>";
        $userlink .= format_username($friend) . " ($title)<br />{$lang['friends_last_seen']} " . get_date($friend['last_access'], '');
        
        $delete = "<span class='btn'><a href='friends.php?id=$userid&amp;action=delete&amp;type=friend&amp;targetid=" . intval($friend['id']) . "'>{$lang['friends_remove']}</a></span>";
        
        $pm = "&nbsp;<span class='btn'><a href='sendmessage.php?receiver=" . intval($friend['id']) . "'>{$lang['friends_pm']}</a></span>";
        
        $avatar = ($CURUSER["avatars"] == "all" ? htmlspecialchars($friend["avatar"]) : ($CURUSER["avatars"] == "some" && $friend["offavatar"] == "no" ? htmlspecialchars($friend["avatar"]) : ""));
        if (!$avatar)
            $avatar = "{$INSTALLER09['pic_base_url']}default_avatar.gif";
        
        $friends .= "<div style='border: 1px solid black;padding:5px;'>" . ($avatar ? "<img width='50px' src='$avatar' style='float:right;' alt='' />" : "") . "<p >{$userlink}<br /><br />{$delete}{$pm}</p></div><br />";
        
    }
    
}

//if ($i % 2 == 1)
//$HTMLOUT .= "<td class='bottom' width='50%'>&nbsp;</td></tr></table>\n";
//print($friends);
// $HTMLOUT .= "</td></tr></table>\n";

/////////////////////// FRIENDS BLOCK END///////////////////////////////////////

//////////////////// ENEMIES BLOCK ////////////////////////////

$res = sql_query("SELECT b.blockid as id, u.username AS name, u.donor, u.warned, u.chatpost, u.leechwarn, u.pirate, u.king, u.username, u.enabled, u.last_access FROM blocks AS b LEFT JOIN users as u ON b.blockid = u.id WHERE userid=" . sqlesc($userid) . " ORDER BY name") or sqlerr(__FILE__, __LINE__);

$blocks = '';

if (mysqli_num_rows($res) == 0) {
    $blocks = "{$lang['friends_blocks_empty']}<em>.</em>";
} else {
    //$i = 0;
    //$blocks = "<table width='100%' cellspacing='0' cellpadding='0'>";
    while ($block = mysqli_fetch_assoc($res)) {
        $blocks .= "<div style='border: 1px solid black;padding:5px;'>";
        $blocks .= "<span class='btn' style='float:right;'><a href='friends.php?id=$userid&amp;action=delete&amp;type=block&amp;targetid=" . intval($block['id']) . "'>{$lang['friends_delete']}</a></span><br />";
        $blocks .= "<p><a href='userdetails.php?id=" . intval($block['id']) . "'></a>";
        $blocks .= format_username($block) . "</p></div><br />";
        
    }
    
}
//////////////////// ENEMIES BLOCK END ////////////////////////////  

$HTMLOUT .= "<table class='main' border='0' cellspacing='0' cellpadding='0'>" . "<tr><td class='embedded'><h1 style='margin:0px'> {$lang['friends_personal']} " . htmlentities($user['username'], ENT_QUOTES) . "</h1>$donor$warned</td></tr></table>";

$HTMLOUT .= "<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'>
    <tr>
      <td class='colhead'><h2 align='left' style='width:50%;'><a name='friends'>{$lang['friends_friends_list']}</a></h2></td>
      <td class='colhead'><h2 align='left' style='width:50%;vertical-align:top;'><a name='blocks'>{$lang['friends_blocks_list']}</a></h2></td>
    </tr>
    <tr>
      <td style='padding:10px;background-color:#ECE9D8;width:50%;'>$friends</td>
      <td style='padding:10px;background-color:#ECE9D8' valign='top'>$blocks</td>
    </tr>
    </table>";

$HTMLOUT .= " <p><a href='users.php'><b>{$lang['friends_user_list']}</b></a></p>";

echo stdhead("{$lang['friends_stdhead']} {$user['username']}") . $HTMLOUT . stdfoot();
?>
