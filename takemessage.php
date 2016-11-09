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
require_once(INCL_DIR . 'page_verify.php');
if ($_SERVER["REQUEST_METHOD"] != "POST")
    stderr("Error", "Method");

dbconn();
loggedinorreturn();


if ($CURUSER["sendpmpos"] == 0 || $CURUSER["sendpmpos"] > 1)
    stderr($lang['takemessage_sorry'], $lang['takemessage_no_auth']);

$lang = array_merge(load_language('global'), load_language('takemessage'));
flood_limit('messages');
$newpage = new page_verify();
$newpage->check('tkmg');

function ratios($up, $down)
{
    global $lang;
    if ($down > 0) {
        $ratio = number_format($up / $down, 3);
        return "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
    } else {
        if ($up > 0)
            return $lang['takemessage_inf'];
        else
            return "---";
    }
    return;
}

$n_pms = isset($_POST["n_pms"]) ? $_POST["n_pms"] : false;
if ($n_pms) { //////  MM  ///
    if ($CURUSER['class'] < UC_MODERATOR)
        stderr($lang['takemessage_error'], $lang['takemessage_denied']);
    
    $msg = trim($_POST["body"]);
    if (!$msg)
        stderr($lang['takemessage_error'], $lang['takemessage_something']);
    
    $subject = trim($_POST['subject']);
    
    $sender_id = ($_POST['sender'] == $lang['takemessage_system'] ? 0 : $CURUSER['id']);
    
    foreach (explode(':', $_POST['pmees']) as $k => $v) {
        if (ctype_digit($v))
            $from_is[] = sqlesc($v);
    }
    $from_is = "FROM users u WHERE u.id IN (" . join(',', $from_is) . ")";
    
    $query1 = "INSERT INTO messages (sender, receiver, added, msg, subject, location, poster) " . "SELECT " . sqlesc($sender_id) . ", u.id, " . time() . ", " . sqlesc($msg) . ", " . sqlesc($subject) . ", 1, $sender_id " . $from_is;
    
    sql_query($query1) or sqlerr(__FILE__, __LINE__);
    $n = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    
    $comment  = isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '';
    $snapshot = isset($_POST['snap']) ? htmlspecialchars($_POST['snap']) : '';
    
    // add a custom text or stats snapshot to comments in profile
    if ($comment || $snapshot) {
        $res = sql_query("SELECT u.id, u.uploaded, u.downloaded, u.modcomment " . $from_is) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            $l = 0;
            while ($user = mysqli_fetch_assoc($res)) {
                unset($new);
                $new = '';
                $old = $user['modcomment'];
                if ($comment)
                    $new .= $comment;
                if ($snapshot) {
                    
                    $new .= ($new ? "\n" : "") . "{$lang['takemessage_mmed']}, " . gmdate("Y-m-d") . ", " . "{$lang['takemessage_ul']}: " . mksize($user['uploaded']) . ", " . "{$lang['takemessage_dl']}: " . mksize($user['downloaded']) . ", " . "{$lang['takemessage_r']}: " . ratios($user['uploaded'], $user['downloaded']) . " - " . ($_POST['sender'] == $lang['takemessage_system'] ? $lang['takemessage_System'] : $CURUSER['username']);
                }
                $new .= $old ? ("\n" . $old) : $old;
                sql_query("UPDATE users SET modcomment = " . sqlesc($new) . " WHERE id = " . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
                if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]))
                    $l++;
            }
        }
    }
} else { //////  PM  ///
    $receiver = isset($_POST["receiver"]) ? intval($_POST["receiver"]) : false;
    $origmsg  = isset($_POST["origmsg"]) ? intval($_POST["origmsg"]) : false;
    $save     = isset($_POST["save"]) ? $_POST["save"] : false;
    $returnto = isset($_POST["returnto"]) ? $_POST["returnto"] : '';
    
    if (!is_valid_id($receiver) || ($origmsg && !is_valid_id($origmsg)))
        stderr($lang['takemessage_error'], $lang['takemessage_id']);
    
    $msg = trim($_POST["body"]);
    if (!$msg)
        stderr($lang['takemessage_error'], $lang['takemessage_something']);
    
    $save = ($save == 'yes') ? "yes" : "no";
    
    $res = sql_query("SELECT acceptpms, email, notifs, parked, last_access as la FROM users WHERE id=" . sqlesc($receiver)) or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_assoc($res);
    
    
    if (!$user)
        stderr($lang['takemessage_error'], $lang['takemessage_no_user']);
    
    //Make sure recipient wants this message
    if ($CURUSER['class'] < UC_MODERATOR) {
        if ($user["parked"] == "yes")
            stderr("Refused", "This account is parked.");
        if ($user["acceptpms"] == "yes") {
            $res2 = sql_query("SELECT * FROM blocks WHERE userid=" . sqlesc($receiver) . " AND blockid=" . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res2) == 1)
                stderr($lang['takemessage_refused'], $lang['takemessage_blocked']);
        } elseif ($user["acceptpms"] == "friends") {
            $res2 = sql_query("SELECT * FROM friends WHERE userid=" . sqlesc($receiver) . " AND friendid=" . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res2) != 1)
                stderr($lang['takemessage_refused'], $lang['takemessage_friends']);
        } elseif ($user["acceptpms"] == "no")
            stderr($lang['takemessage_refused'], $lang['takemessage_no_pms']);
    }
    
    $subject = trim(htmlspecialchars($_POST['subject']));
    
    sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location) VALUES(" . sqlesc($CURUSER["id"]) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($receiver) . ", " . time() . ", " . sqlesc($msg) . ", " . sqlesc($subject) . ", " . sqlesc($save) . ", 1)") or sqlerr(__FILE__, __LINE__);
    
    if (strpos($user['notifs'], '[pm]') !== false) {
        if (time() - $user["la"] >= 300) {
            $username = $CURUSER["username"];
            $body     = <<<EOD
You have received a PM from $username!

You can use the URL below to view the message (you may have to login).

{$TBDEV['baseurl']}/messages.php

--
{$TBDEV['site_name']}
EOD;
            @mail($user["email"], "{$lang['takemessage_received']} " . $username . "!", $body, "{$lang['takemessage_from']} {$TBDEV['site_email']}");
        }
    }
    $delete = isset($_POST["delete"]) ? htmlspecialchars($_POST["delete"]) : '';
    
    if ($origmsg) {
        if ($delete == "yes") {
            // Make sure receiver of $origmsg is current user
            $res = sql_query("SELECT * FROM messages WHERE id=" . sqlesc($origmsg)) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res) == 1) {
                $arr = mysqli_fetch_assoc($res);
                if ($arr["receiver"] != $CURUSER["id"])
                    stderr($lang['takemessage_woot'], $lang['takemessage_happen']);
                if ($arr["saved"] == "no")
                    sql_query("DELETE FROM messages WHERE id=" . sqlesc($origmsg)) or sqlerr(__FILE__, __LINE__);
                elseif ($arr["saved"] == "yes")
                    sql_query("UPDATE messages SET location = '0' WHERE id=" . sqlesc($origmsg)) or sqlerr(__FILE__, __LINE__);
                
            }
        }
        
        
        
        if (!$returnto)
            $returnto = "{$TBDEV['baseurl']}/messages.php";
    }
    
    if ($returnto) {
        header("Location: $returnto");
        die;
    }
    
    
}

$l = (isset($l) ? $l : '');
stderr($lang['takemessage_succeed'], (($n_pms > 1) ? "$n {$lang['takemessage_out_of']} $n_pms {$lang['takemessage_were']}" : "{$lang['takemessage_msg_was']}") . " {$lang['takemessage_sent']}" . ($l ? " $l {$lang['takemessage_comment']}" . (($l > 1) ? "{$lang['takemessage_s_were']}" : " {$lang['takemessage_was']}") . " {$lang['takemessage_updated']}" : ""));
exit;
?>
