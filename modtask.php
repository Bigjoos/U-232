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
dbconn(false);
loggedinorreturn();
$lang    = array_merge(load_language('global'), load_language('modtask'));
$newpage = new page_verify();
$newpage->check('mdk1@@9');

function resize_image($in)
{
    $out = array(
        'img_width' => $in['cur_width'],
        'img_height' => $in['cur_height']
    );
    if ($in['cur_width'] > $in['max_width']) {
        $out['img_width']  = $in['max_width'];
        $out['img_height'] = ceil(($in['cur_height'] * (($in['max_width'] * 100) / $in['cur_width'])) / 100);
        $in['cur_height']  = $out['img_height'];
        $in['cur_width']   = $out['img_width'];
    }
    if ($in['cur_height'] > $in['max_height']) {
        $out['img_height'] = $in['max_height'];
        $out['img_width']  = ceil(($in['cur_width'] * (($in['max_height'] * 100) / $in['cur_height'])) / 100);
    }
    return $out;
}

if ($CURUSER['class'] < UC_MODERATOR)
    stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");

//== Correct call to script
if ((isset($_POST['action'])) && ($_POST['action'] == "edituser")) {
    //== Set user id
    if (isset($_POST['userid']))
        $userid = intval($_POST['userid']);
    else
        stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
    
    //== and verify...
    if (!is_valid_id($userid))
        stderr("{$lang['modtask_error']}", "{$lang['modtask_bad_id']}");
    require_once(INCL_DIR . 'validator.php');
    if (!validate($_POST['validator'], "ModTask_$userid"))
        die("Invalid");
    
    //== Fetch current user data...
    $res = sql_query("SELECT * FROM users WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_assoc($res) or sqlerr(__FILE__, __LINE__);
    
    if ($CURUSER['class'] <= $user['class'] && ($CURUSER['id'] != $userid && $CURUSER['class'] < UC_ADMINISTRATOR))
        stderr('Error', 'You cannot edit someone of the same or higher class.. injecting stuff arent we? Action logged');
    
    if (($user['immunity'] >= 1) && ($CURUSER['class'] < UC_SYSOP))
        stderr("Error", "This user is immune to your commands !");
    
    $updateset  = array();
    $modcomment = (isset($_POST['modcomment']) && $CURUSER['class'] == UC_SYSOP) ? $_POST['modcomment'] : $user['modcomment'];
    
    //== Set class
    if ((isset($_POST['class'])) && (($class = $_POST['class']) != $user['class'])) {
        if ($class >= UC_SYSOP || ($class >= $CURUSER['class']) || ($user['class'] >= $CURUSER['class']))
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        if (!valid_class($class) || $CURUSER['class'] <= $_POST['class'])
            stderr(("Error"), "Bad class :P");
        
        //== Notify user
        $what  = ($class > $user['class'] ? "{$lang['modtask_promoted']}" : "{$lang['modtask_demoted']}");
        $msg   = sqlesc(sprintf($lang['modtask_have_been'], $what) . " '" . get_user_class_name($class) . "' {$lang['modtask_by']} " . $CURUSER['username']);
        $added = time();
        sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, " . sqlesc($userid) . ", $msg, " . sqlesc($added) . ")") or sqlerr(__FILE__, __LINE__);
        if (is_file("cache/funds.txt"))
            unlink("cache/funds.txt");
        $updateset[] = "class = " . sqlesc($class);
        $modcomment  = get_date(time(), 'DATE', 1) . " - $what to '" . get_user_class_name($class) . "' by $CURUSER[username].\n" . $modcomment;
    }
    
    // === Add donated amount to user and to funds table
    if ((isset($_POST['donated'])) && (($donated = $_POST['donated']) != $user['donated'])) {
        $added = sqlesc(time());
        sql_query("INSERT INTO funds (cash, user, added) VALUES ($donated, $userid, $added)") or sqlerr(__FILE__, __LINE__);
        if (is_file("cache/funds.txt"))
            unlink("cache/funds.txt");
        $updateset[] = "donated = " . sqlesc($donated);
        $updateset[] = "total_donated = " . $user['total_donated'] . " + " . sqlesc($donated);
    }
    // ====End
    
    // === Set donor - Time based
    if ((isset($_POST['donorlength'])) && ($donorlength = 0 + $_POST['donorlength'])) {
        if ($donorlength == 255) {
            $modcomment  = get_date(time(), 'DATE', 1) . "{$lang['modtask_donor_set']}" . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("You have received donor status from " . $CURUSER['username']);
            $subject     = sqlesc("Thank You for Your Donation!");
            $updateset[] = "donoruntil = '0'";
        } else {
            $donoruntil  = (time() + $donorlength * 604800);
            $dur         = $donorlength . " week" . ($donorlength > 1 ? "s" : "");
            $msg         = sqlesc("Dear " . htmlspecialchars($user['username']) . "
       :wave:
       Thanks for your support to {$TBDEV['site_name']} !
       Your donation helps us in the costs of running the site!
       As a donor, you are given some bonus gigs added to your uploaded amount, the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile:

       so, thanks again, and enjoy!
       cheers,
       {$TBDEV['site_name']} Staff

       PS. Your donator status will last for $dur and can be found on your user details page and can only be seen by you :smile: It was set by " . $CURUSER['username']);
            $subject     = sqlesc("Thank You for Your Donation!");
            $modcomment  = get_date(time(), 'DATE', 1) . "{$lang['modtask_donor_set']}" . $CURUSER['username'] . ".\n" . $modcomment;
            $updateset[] = "donoruntil = " . sqlesc($donoruntil);
            $updateset[] = "vipclass_before = " . sqlesc($user["class"]);
        }
        $added = sqlesc(time());
        sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        $updateset[] = "donor = 'yes'";
        $res = sql_query("SELECT class FROM users WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_array($res);
        if ($user['class'] < UC_UPLOADER)
            $updateset[] = "class = '2'"; //=== set this to the number for vip on your server
    }
    
    // === Add to donor length // thanks to CoLdFuSiOn
    if ((isset($_POST['donorlengthadd'])) && ($donorlengthadd = 0 + $_POST['donorlengthadd'])) {
        $donoruntil = $user["donoruntil"];
        $dur        = $donorlengthadd . " week" . ($donorlengthadd > 1 ? "s" : "");
        $msg        = sqlesc("Dear " . htmlspecialchars($user['username']) . "
       :wave:
       Thanks for your continued support to {$TBDEV['site_name']} !
       Your donation helps us in the costs of running the site. Everything above the current running costs will go towards next months costs!
       As a donor, you are given some bonus gigs added to your uploaded amount, and, you have the the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile:

       so, thanks again, and enjoy!
       cheers,
       {$TBDEV['site_name']} Staff

        PS. Your donator status will last for an extra $dur on top of your current donation status, and can be found on your user details page and can only be seen by you :smile: It was set by " . $CURUSER['username']);
        
        $subject        = sqlesc("Thank You for Your Donation... Again!");
        $modcomment     = get_date(time(), 'DATE', 1) . " - Donator status set for another $dur by " . $CURUSER['username'] . ".\n" . $modcomment;
        $donorlengthadd = $donorlengthadd * 7;
        sql_query("UPDATE users SET vipclass_before=" . sqlesc($user["class"]) . ", donoruntil = IF(donoruntil=0, " . TIME_NOW . " + 86400 * $donorlengthadd, donoruntil + 86400 * $donorlengthadd) WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $added = sqlesc(time());
        sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        $updateset[] = "donated = " . $user['donated'] . " + " . sqlesc($_POST['donated']);
        $updateset[] = "total_donated = " . $user['total_donated'] . " + " . sqlesc($_POST['donated']);
    }
    // === End add to donor length
    
    // === Clear donor if they were bad
    if (isset($_POST['donor']) && (($donor = $_POST['donor']) != $user['donor'])) {
        $updateset[] = "donor = " . sqlesc($donor);
        $updateset[] = "donoruntil = '0'";
        $updateset[] = "donated = '0'";
        $updateset[] = "class = " . sqlesc($user["vipclass_before"]);
        if ($donor == 'no') {
            $modcomment = get_date(time(), 'DATE', 1) . "{$lang['modtask_donor_removed']} " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg        = sqlesc(sprintf($lang['modtask_donor_removed']) . $CURUSER['username']);
            $added      = sqlesc(time());
            $subject    = sqlesc("Donator status expired.");
            sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        }
    }
    // ===end
    
    //== Enable / Disable
    if ((isset($_POST['enabled'])) && (($enabled = $_POST['enabled']) != $user['enabled'])) {
        if ($enabled == 'yes')
            $modcomment = get_date(time(), 'DATE', 1) . " {$lang['modtask_enabled']}" . $CURUSER['username'] . ".\n" . $modcomment;
        else
            $modcomment = get_date(time(), 'DATE', 1) . "{$lang['modtask_disabled']}" . $CURUSER['username'] . ".\n" . $modcomment;
        
        $updateset[] = "enabled = " . sqlesc($enabled);
    }
    //== Set download posssible Time based
    if (isset($_POST['downloadpos']) && ($downloadpos = 0 + $_POST['downloadpos'])) {
        unset($disable_pm);
        if (isset($_POST['disable_pm']))
            $disable_pm = $_POST['disable_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($downloadpos == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Download disablement by " . $CURUSER['username'] . ".\nReason: $disable_pm\n" . $modcomment;
            $msg         = sqlesc("Your Downloading rights have been disabled by " . $CURUSER['username'] . ($disable_pm ? "\n\nReason: $disable_pm" : ''));
            $updateset[] = 'downloadpos = 0';
        } elseif ($downloadpos == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Download disablement status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Downloading rights have been restored by " . $CURUSER['username'] . ".");
            $updateset[] = 'downloadpos = 1';
        } else {
            $downloadpos_until = ($added + $downloadpos * 604800);
            $dur               = $downloadpos . ' week' . ($downloadpos > 1 ? 's' : '');
            $msg               = sqlesc("You have received $dur Download disablement from " . $CURUSER['username'] . ($disable_pm ? "\n\nReason: $disable_pm" : ''));
            $modcomment        = get_date($added, 'DATE', 1) . " - Download disablement for $dur by " . $CURUSER['username'] . ".\nReason: $disable_pm\n" . $modcomment;
            $updateset[]       = "downloadpos = " . sqlesc($downloadpos_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set upload posssible Time based
    if (isset($_POST['uploadpos']) && ($uploadpos = 0 + $_POST['uploadpos'])) {
        unset($updisable_pm);
        if (isset($_POST['updisable_pm']))
            $updisable_pm = $_POST['updisable_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($uploadpos == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Upload disablement by " . $CURUSER['username'] . ".\nReason: $updisable_pm\n" . $modcomment;
            $msg         = sqlesc("Your Uploading rights have been disabled by " . $CURUSER['username'] . ($updisable_pm ? "\n\nReason: $updisable_pm" : ''));
            $updateset[] = 'uploadpos = 0';
        } elseif ($uploadpos == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Upload disablement status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Uploading rights have been restored by " . $CURUSER['username'] . ".");
            $updateset[] = 'uploadpos = 1';
        } else {
            $uploadpos_until = ($added + $uploadpos * 604800);
            $dur             = $uploadpos . ' week' . ($uploadpos > 1 ? 's' : '');
            $msg             = sqlesc("You have received $dur Upload disablement from " . $CURUSER['username'] . ($updisable_pm ? "\n\nReason: $updisable_pm" : ''));
            $modcomment      = get_date($added, 'DATE', 1) . " - Upload disablement for $dur by " . $CURUSER['username'] . ".\nReason: $updisable_pm\n" . $modcomment;
            $updateset[]     = "uploadpos = " . sqlesc($uploadpos_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	          VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set Forum posting posssible Time based
    if (isset($_POST['forumpost']) && ($forumpost = 0 + $_POST['forumpost'])) {
        unset($forumdisable_pm);
        if (isset($_POST['forumdisable_pm']))
            $forumdisable_pm = $_POST['forumdisable_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($forumpost == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Forum Posting disablement by " . $CURUSER['username'] . ".\nReason: $forumdisable_pm\n" . $modcomment;
            $msg         = sqlesc("Your posting rights have been disabled by " . $CURUSER['username'] . ($forumdisable_pm ? "\n\nReason: $forumdisable_pm" : ''));
            $updateset[] = 'forumpost = 0';
        } elseif ($forumpost == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Posting disablement status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your posting rights have been restored by " . $CURUSER['username'] . ".");
            $updateset[] = 'forumpost = 1';
        } else {
            $forumpost_until = ($added + $forumpost * 604800);
            $dur             = $forumpost . ' week' . ($forumpost > 1 ? 's' : '');
            $msg             = sqlesc("You have received $dur Posting disablement from " . $CURUSER['username'] . ($forumdisable_pm ? "\n\nReason: $forumdisable_pm" : ''));
            $modcomment      = get_date($added, 'DATE', 1) . " - Forum posting disablement for $dur by " . $CURUSER['username'] . ".\nReason: $forumdisable_pm\n" . $modcomment;
            $updateset[]     = "forumpost = " . sqlesc($forumpost_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set Pm posssible Time based
    if (isset($_POST['sendpmpos']) && ($sendpmpos = 0 + $_POST['sendpmpos'])) {
        unset($pmdisable_pm);
        if (isset($_POST['pmdisable_pm']))
            $pmdisable_pm = $_POST['pmdisable_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($sendpmpos == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Pm disablement by " . $CURUSER['username'] . ".\nReason: $pmdisable_pm\n" . $modcomment;
            $msg         = sqlesc("Your Pm rights have been disabled by " . $CURUSER['username'] . ($pmdisable_pm ? "\n\nReason: $pmdisable_pm" : ''));
            $updateset[] = 'sendpmpos = 0';
        } elseif ($sendpmpos == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Pm disablement status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Pm rights have been restored by " . $CURUSER['username'] . ".");
            $updateset[] = 'sendpmpos = 1';
        } else {
            $sendpmpos_until = ($added + $sendpmpos * 604800);
            $dur             = $sendpmpos . ' week' . ($sendpmpos > 1 ? 's' : '');
            $msg             = sqlesc("You have received $dur Pm disablement from " . $CURUSER['username'] . ($pmdisable_pm ? "\n\nReason: $pmdisable_pm" : ''));
            $modcomment      = get_date($added, 'DATE', 1) . " - Pm disablement for $dur by " . $CURUSER['username'] . ".\nReason: $pmdisable_pm\n" . $modcomment;
            $updateset[]     = "sendpmpos = " . sqlesc($sendpmpos_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set shoutbox posssible Time based
    if (isset($_POST['chatpost']) && ($chatpost = 0 + $_POST['chatpost'])) {
        unset($chatdisable_pm);
        if (isset($_POST['chatdisable_pm']))
            $chatdisable_pm = $_POST['chatdisable_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($chatpost == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Shout disablement by " . $CURUSER['username'] . ".\nReason: $chatdisable_pm\n" . $modcomment;
            $msg         = sqlesc("Your Shoutbox rights have been disabled by " . $CURUSER['username'] . ($chatdisable_pm ? "\n\nReason: $chatdisable_pm" : ''));
            $updateset[] = 'chatpost = 0';
        } elseif ($chatpost == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Shoutbox disablement status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Shoutbox rights have been restored by " . $CURUSER['username'] . ".");
            $updateset[] = 'chatpost = 1';
        } else {
            $chatpost_until = ($added + $chatpost * 604800);
            $dur            = $chatpost . ' week' . ($chatpost > 1 ? 's' : '');
            $msg            = sqlesc("You have received $dur Shoutbox disablement from " . $CURUSER['username'] . ($chatdisable_pm ? "\n\nReason: $chatdisable_pm" : ''));
            $modcomment     = get_date($added, 'DATE', 1) . " - Shoutbox disablement for $dur by " . $CURUSER['username'] . ".\nReason: $chatdisable_pm\n" . $modcomment;
            $updateset[]    = "chatpost = " . sqlesc($chatpost_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set avatar posssible Time based
    if (isset($_POST['avatarpos']) && ($avatarpos = 0 + $_POST['avatarpos'])) {
        unset($avatardisable_pm);
        if (isset($_POST['avatardisable_pm']))
            $avatardisable_pm = $_POST['avatardisable_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($avatarpos == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Avatar disablement by " . $CURUSER['username'] . ".\nReason: $avatardisable_pm\n" . $modcomment;
            $msg         = sqlesc("Your Avatar rights have been disabled by " . $CURUSER['username'] . ($avatardisable_pm ? "\n\nReason: $avatardisable_pm" : ''));
            $updateset[] = 'avatarpos = 0';
        } elseif ($avatarpos == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Avatar disablement status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Avatar rights have been restored by " . $CURUSER['username'] . ".");
            $updateset[] = 'avatarpos = 1';
        } else {
            $avatarpos_until = ($added + $avatarpos * 604800);
            $dur             = $avatarpos . ' week' . ($avatarpos > 1 ? 's' : '');
            $msg             = sqlesc("You have received $dur Avatar disablement from " . $CURUSER['username'] . ($avatardisable_pm ? "\n\nReason: $avatardisable_pm" : ''));
            $modcomment      = get_date($added, 'DATE', 1) . " - Avatar disablement for $dur by " . $CURUSER['username'] . ".\nReason: $avatardisable_pm\n" . $modcomment;
            $updateset[]     = "avatarpos = " . sqlesc($avatarpos_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set Immunity Status Time based
    if (isset($_POST['immunity']) && ($immunity = 0 + $_POST['immunity'])) {
        unset($immunity_pm);
        if (isset($_POST['immunity_pm']))
            $immunity_pm = $_POST['immunity_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($immunity == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Immune Status enabled by " . $CURUSER['username'] . ".\nReason: $immunity_pm\n" . $modcomment;
            $msg         = sqlesc("You have received immunity Status from " . $CURUSER['username'] . ($immunity_pm ? "\n\nReason: $immunity_pm" : ''));
            $updateset[] = 'immunity = 1';
        } elseif ($immunity == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Immunity Status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Immunity Status has been removed by " . $CURUSER['username'] . ".");
            $updateset[] = 'immunity = 0';
        } else {
            $immunity_until = ($added + $immunity * 604800);
            $dur            = $immunity . ' week' . ($immunity > 1 ? 's' : '');
            $msg            = sqlesc("You have received $dur Immunity Status from " . $CURUSER['username'] . ($immunity_pm ? "\n\nReason: $immunity_pm" : ''));
            $modcomment     = get_date($added, 'DATE', 1) . " - Immunity Status for $dur by " . $CURUSER['username'] . ".\nReason: $immunity_pm\n" . $modcomment;
            $updateset[]    = "immunity = " . sqlesc($immunity_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Set leechwarn Status Time based
    if (isset($_POST['leechwarn']) && ($leechwarn = 0 + $_POST['leechwarn'])) {
        unset($leechwarn_pm);
        if (isset($_POST['leechwarn_pm']))
            $leechwarn_pm = $_POST['leechwarn_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($leechwarn == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - leechwarn Status enabled by " . $CURUSER['username'] . ".\nReason: $leechwarn_pm\n" . $modcomment;
            $msg         = sqlesc("You have received leechwarn Status from " . $CURUSER['username'] . ($leechwarn_pm ? "\n\nReason: $leechwarn_pm" : ''));
            $updateset[] = 'leechwarn = 1';
        } elseif ($leechwarn == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - leechwarn Status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your leechwarn Status has been removed by " . $CURUSER['username'] . ".");
            $updateset[] = 'leechwarn = 0';
        } else {
            $leechwarn_until = ($added + $leechwarn * 604800);
            $dur             = $leechwarn . ' week' . ($leechwarn > 1 ? 's' : '');
            $msg             = sqlesc("You have received $dur leechwarn Status from " . $CURUSER['username'] . ($leechwarn_pm ? "\n\nReason: $leechwarn_pm" : ''));
            $modcomment      = get_date($added, 'DATE', 1) . " - leechwarn Status for $dur by " . $CURUSER['username'] . ".\nReason: $leechwarn_pm\n" . $modcomment;
            $updateset[]     = "leechwarn = " . sqlesc($leechwarn_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        
    }
    //= Set warn Status Time based
    if (isset($_POST['warned']) && ($warned = 0 + $_POST['warned'])) {
        unset($warned_pm);
        if (isset($_POST['warned_pm']))
            $warned_pm = $_POST['warned_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($warned == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - warned Status enabled by " . $CURUSER['username'] . ".\nReason: $warned_pm\n" . $modcomment;
            $msg         = sqlesc("You have received warned Status from " . $CURUSER['username'] . ($warned_pm ? "\n\nReason: $warned_pm" : ''));
            $updateset[] = 'warned = 1';
        } elseif ($warned == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - warned Status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your warned Status has been removed by " . $CURUSER['username'] . ".");
            $updateset[] = 'warned = 0';
        } else {
            $warned_until = ($added + $warned * 604800);
            $dur          = $warned . ' week' . ($warned > 1 ? 's' : '');
            $msg          = sqlesc("You have received $dur warned Status from " . $CURUSER['username'] . ($warned_pm ? "\n\nReason: $warned_pm" : ''));
            $modcomment   = get_date($added, 'DATE', 1) . " - warned Status for $dur by " . $CURUSER['username'] . ".\nReason: $warned_pm\n" . $modcomment;
            $updateset[]  = "warned = " . sqlesc($warned_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    //== Add remove uploaded
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $uploadtoadd   = 0 + $_POST["amountup"];
        $downloadtoadd = 0 + $_POST["amountdown"];
        $formatup      = $_POST["formatup"];
        $formatdown    = $_POST["formatdown"];
        $mpup          = $_POST["upchange"];
        $mpdown        = $_POST["downchange"];
        if ($uploadtoadd > 0) {
            if ($mpup == "plus") {
                $newupload  = $user["uploaded"] + ($formatup == 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                $modcomment = get_date(time(), 'DATE', 1) . " {$lang['modtask_add_upload']} (" . $uploadtoadd . " " . $formatup . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
            } else {
                $newupload = $user["uploaded"] - ($formatup == 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                if ($newupload >= 0)
                    $modcomment = get_date(time(), 'DATE', 1) . " {$lang['modtask_subtract_upload']} (" . $uploadtoadd . " " . $formatup . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
            }
            if ($newupload >= 0)
                $updateset[] = "uploaded = " . sqlesc($newupload);
        }
        
        if ($downloadtoadd > 0) {
            if ($mpdown == "plus") {
                $newdownload = $user["downloaded"] + ($formatdown == 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                $modcomment  = get_date(time(), 'DATE', 1) . " {$lang['modtask_added_download']} (" . $downloadtoadd . " " . $formatdown . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
            } else {
                $newdownload = $user["downloaded"] - ($formatdown == 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                if ($newdownload >= 0)
                    $modcomment = get_date(time(), 'DATE', 1) . " {$lang['modtask_subtract_download']} (" . $downloadtoadd . " " . $formatdown . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
            }
            if ($newdownload >= 0)
                $updateset[] = "downloaded = " . sqlesc($newdownload);
        }
    }
    //== End add/remove upload
    //== Change Custom Title
    if ((isset($_POST['title'])) && (($title = $_POST['title']) != ($curtitle = $user['title']))) {
        $modcomment = get_date(time(), 'DATE', 1) . "{$lang['modtask_custom_title']}'" . $title . "' from '" . $curtitle . "'{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;
        
        $updateset[] = "title = " . sqlesc($title);
    }
    
    // The following code will place the old passkey in the mod comment and create
    // a new passkey. This is good practice as it allows usersearch to find old
    // passkeys by searching the mod comments of members.
    
    //== Reset Passkey
    if ((isset($_POST['resetpasskey'])) && ($_POST['resetpasskey'])) {
        $newpasskey  = md5($user['username'] . time() . $user['passhash']);
        $modcomment  = get_date(time(), 'DATE', 1) . "{$lang['modtask_passkey']}" . sqlesc($user['passkey']) . "{$lang['modtask_reset']}" . sqlesc($newpasskey) . "{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "passkey=" . sqlesc($newpasskey);
    }
    
    //== Seedbonus
    if ((isset($_POST['seedbonus'])) && (($seedbonus = $_POST['seedbonus']) != ($curseedbonus = $user['seedbonus']))) {
        $modcomment  = get_date(time(), 'DATE', 1) . " - Seedbonus amount changed to '" . $seedbonus . "' from '" . $curseedbonus . "' by " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = 'seedbonus = ' . sqlesc($seedbonus);
    }
    
    //== Reputation
    if ((isset($_POST['reputation'])) && (($reputation = $_POST['reputation']) != ($curreputation = $user['reputation']))) {
        $modcomment  = get_date(time(), 'DATE', 1) . " - Reputation points changed to '" . $reputation . "' from '" . $curreputation . "' by " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = 'reputation = ' . sqlesc($reputation);
    }
    /* This code is for use with the safe mod comment modification. If you have installed
    the safe mod comment mod, then uncomment this section...
    */
    // Add Comment to ModComment
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment']))) {
        $modcomment = get_date(time(), 'DATE', 1) . " - " . $addcomment . " - " . $CURUSER['username'] . ".\n" . $modcomment;
    }
    
    //== Avatar Changed
    if ((isset($_POST['avatar'])) && (($avatar = $_POST['avatar']) != ($curavatar = $user['avatar']))) {
        
        $avatar = trim(urldecode($avatar));
        
        if (preg_match("/^http:\/\/$/i", $avatar) or preg_match("/[?&;]/", $avatar) or preg_match("#javascript:#is", $avatar) or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $avatar)) {
            $avatar = '';
        }
        
        if (!empty($avatar)) {
            $img_size = @GetImageSize($avatar);
            
            if ($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_not_image']}");
            
            if ($img_size[0] < 5 || $img_size[1] < 5)
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_image_small']}");
            
            if (($img_size[0] > $TBDEV['av_img_width']) OR ($img_size[1] > $TBDEV['av_img_height'])) {
                $image = resize_image(array(
                    'max_width' => $TBDEV['av_img_width'],
                    'max_height' => $TBDEV['av_img_height'],
                    'cur_width' => $img_size[0],
                    'cur_height' => $img_size[1]
                ));
                
            } else {
                $image['img_width']  = $img_size[0];
                $image['img_height'] = $img_size[1];
            }
            
            $updateset[] = "av_w = " . sqlesc($image['img_width']);
            $updateset[] = "av_h = " . sqlesc($image['img_height']);
        }
        
        $modcomment = get_date(time(), 'DATE', 1) . "{$lang['modtask_avatar_change']}" . htmlspecialchars($curavatar) . "{$lang['modtask_to']}" . htmlspecialchars($avatar) . "{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;
        
        $updateset[] = "avatar = " . sqlesc($avatar);
    }
    //==Sig checks
    if ((isset($_POST['signature'])) && (($signature = $_POST['signature']) != ($cursignature = $user['signature']))) {
        
        $signature = trim(urldecode($signature));
        
        if (preg_match("/^http:\/\/$/i", $signature) or preg_match("/[?&;]/", $signature) or preg_match("#javascript:#is", $signature) or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $signature)) {
            $signature = '';
        }
        
        if (!empty($signature)) {
            $img_size = @GetImageSize($signature);
            
            if ($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_not_image']}");
            
            if ($img_size[0] < 5 || $img_size[1] < 5)
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_image_small']}");
            
            if (($img_size[0] > $TBDEV['sig_img_width']) OR ($img_size[1] > $TBDEV['sig_img_height'])) {
                $image = resize_image(array(
                    'max_width' => $TBDEV['sig_img_width'],
                    'max_height' => $TBDEV['sig_img_height'],
                    'cur_width' => $img_size[0],
                    'cur_height' => $img_size[1]
                ));
                
            } else {
                $image['img_width']  = $img_size[0];
                $image['img_height'] = $img_size[1];
            }
            
            $updateset[] = "sig_w = " . sqlesc($image['img_width']);
            $updateset[] = "sig_h = " . sqlesc($image['img_height']);
        }
        
        $modcomment = get_date(time(), 'DATE', 1) . "{$lang['modtask_signature_change']}" . htmlspecialchars($cursignature) . "{$lang['modtask_to']}" . htmlspecialchars($signature) . "{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;
        
        $updateset[] = "signature = " . sqlesc($signature);
    }
    //==End
    //==09  Offensive Avatar
    if ((isset($_POST['offavatar'])) && (($offavatar = $_POST['offavatar']) != $user['offavatar'])) {
        if ($offavatar == 'yes') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Marked as Offensive Avatar by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg        = sqlesc("Your Avatar is set as Offensive by  " . htmlspecialchars($CURUSER['username']) . ", Please PM " . htmlspecialchars($CURUSER['username']) . " for the reason why.");
            $added      = time();
            $subject    = sqlesc("Your Avatar is set as Offensive.");
            sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
        } elseif ($offavatar == 'no') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Un-Marked as Not Offensive Avatar by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg        = sqlesc("Your Avatar is set as Not Offensive by  " . htmlspecialchars($CURUSER['username']) . ".");
            $added      = time();
            $subject    = sqlesc("Your Avatar is not Offensive.");
            sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
        } else
            die();
        $updateset[] = "offavatar = " . sqlesc($offavatar);
    }
    //==End
    //=== allow invites
    if ((isset($_POST['invite_on'])) && (($invite_on = $_POST['invite_on']) != $user['invite_on'])) {
        $modcomment  = get_date(time(), 'DATE', 1) . " - Invites allowed changed from $user[invite_on] to $invite_on by " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "invite_on = " . sqlesc($invite_on);
    }
    //== change invites
    if ((isset($_POST['invites'])) && (($invites = $_POST['invites']) != ($curinvites = $user['invites']))) {
        $modcomment  = get_date(time(), 'DATE', 1) . " - Invite amount changed to '" . $invites . "' from '" . $curinvites . "' by " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "invites = " . sqlesc($invites);
    }
    //==Fls Support
    if ((isset($_POST['support'])) && (($support = $_POST['support']) != $user['support'])) {
        if ($support == 'yes') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Promoted to FLS by " . $CURUSER['username'] . ".\n" . $modcomment;
        } elseif ($support == 'no') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Demoted from FLS by " . $CURUSER['username'] . ".\n" . $modcomment;
        } else
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        
        $supportfor = htmlspecialchars($_POST['supportfor']);
        
        $updateset[] = "support = " . sqlesc($support);
        $updateset[] = "supportfor = " . sqlesc($supportfor);
    }
    //== change freeslots
    if ((isset($_POST['freeslots'])) && (($freeslots = $_POST['freeslots']) != ($curfreeslots = $user['freeslots']))) {
        $modcomment = get_date(time(), 'DATE', 1) . " - freeslots amount changed to '" . $freeslots . "' from '" . $curfreeslots . "' by " . $CURUSER['username'] . ".\n" . $modcomment;
    }
    $updateset[] = 'freeslots = ' . sqlesc($freeslots);
    //== Set Freeleech Status Time based
    if (isset($_POST['free_switch']) && ($free_switch = 0 + $_POST['free_switch'])) {
        unset($free_pm);
        if (isset($_POST['free_pm']))
            $free_pm = $_POST['free_pm'];
        $subject = sqlesc('Notification!');
        $added   = time();
        
        if ($free_switch == 255) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Freeleech Status enabled by " . $CURUSER['username'] . ".\nReason: $free_pm\n" . $modcomment;
            $msg         = sqlesc("You have received Freeleech Status from " . $CURUSER['username'] . ($free_pm ? "\n\nReason: $free_pm" : ''));
            $updateset[] = 'free_switch = 1';
        } elseif ($free_switch == 42) {
            $modcomment  = get_date($added, 'DATE', 1) . " - Freeleech Status removed by " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg         = sqlesc("Your Freeleech Status has been removed by " . $CURUSER['username'] . ".");
            $updateset[] = 'free_switch = 0';
        } else {
            $free_until  = ($added + $free_switch * 604800);
            $dur         = $free_switch . ' week' . ($free_switch > 1 ? 's' : '');
            $msg         = sqlesc("You have received $dur Freeleech Status from " . $CURUSER['username'] . ($free_pm ? "\n\nReason: $free_pm" : ''));
            $modcomment  = get_date($added, 'DATE', 1) . " - Freeleech Status for $dur by " . $CURUSER['username'] . ".\nReason: $free_pm\n" . $modcomment;
            $updateset[] = "free_switch = " . sqlesc($free_until);
        }
        
        sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    
    //== Set higspeed Upload Enable / Disable
    if ((isset($_POST['highspeed'])) && (($highspeed = $_POST['highspeed']) != $user['highspeed'])) {
        if ($highspeed == 'yes') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Highspeed Upload enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
            $subject    = sqlesc("Highspeed uploader status.");
            $msg        = sqlesc("You  have been set as a high speed uploader by  " . $CURUSER['username'] . ". You can now upload torrents using highspeeds without being flagged as a cheater  .");
            $added      = sqlesc(time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or sqlerr(__FILE__, __LINE__);
        } elseif ($highspeed == 'no') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Highspeed Upload disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
            $subject    = sqlesc("Highspeed uploader status.");
            $msg        = sqlesc("Your highspeed upload setting has been disabled by " . $CURUSER['username'] . ". Please PM " . $CURUSER['username'] . " for the reason why.");
            $added      = sqlesc(time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or sqlerr(__FILE__, __LINE__);
        } else
            die(); //== Error
        $updateset[] = "highspeed = " . sqlesc($highspeed);
    }
    
    //== Parked accounts
    if ((isset($_POST['parked'])) && (($parked = $_POST['parked']) != $user['parked'])) {
        if ($parked == 'yes') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Account Parked by " . $CURUSER['username'] . ".\n" . $modcomment;
        } elseif ($parked == 'no') {
            $modcomment = get_date(time(), 'DATE', 1) . " - Account UnParked by " . $CURUSER['username'] . ".\n" . $modcomment;
        } else
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        $updateset[] = "parked = " . sqlesc($parked);
    }
    //end parked
    
    //==Forum moderator mod by putyn tbdev
    //==Start
    if (isset($_POST["forum_mod"]) && ($forum_mod = $_POST["forum_mod"]) != $user["forum_mod"]) {
        $whatm = ($forum_mod == "yes" ? "added " : "removed");
        if ($forum_mod == "no") {
            $updateset[] = "forums_mod = ''";
            sql_query("DELETE FROM forum_mods WHERE uid=" . sqlesc($user["id"])) or sqlerr(__FILE__, __LINE__);
        }
        $updateset[] = "forum_mod=" . sqlesc($forum_mod);
        $modcomment  = get_date(time(), 'DATE', 1) . " " . $CURUSER["username"] . " " . $whatm . " forum rights\n" . $modcomment;
    }
    //==Update forums list
    $forumsc = (isset($_POST["forums_count"]) ? 0 + $_POST["forums_count"] : 0);
    
    if ($forumsc > 0 && $forum_mod != "no") {
        for ($i = 1; $i < $forumsc + 1; $i++) {
            if (substr($_POST["forums_$i"], 0, 3) == "yes")
                $foo[] = (int) substr($_POST["forums_$i"], 4);
        }
        foreach ($foo as $fo) {
            $boo[]  = "(" . $fo . "," . $user["id"] . "," . sqlesc($user["username"]) . ")";
            $boo1[] = "[" . $fo . "]";
        }
        
        sql_query("DELETE FROM forum_mods WHERE uid=" . sqlesc($user["id"])) or sqlerr(__FILE__, __LINE__);
        sql_query("INSERT INTO forum_mods(fid,uid,user) VALUES " . join(",", $boo)) or sqlerr(__FILE__, __LINE__);
        $updateset[] = "forums_mod=" . sqlesc(join("", $boo1));
    }
    //== End forum moderator mod
    //== Add ModComment... (if we changed stuff we update otherwise we dont include this..)
    if (($CURUSER['class'] == UC_SYSOP && ($user['modcomment'] != $_POST['modcomment'] || $modcomment != $_POST['modcomment'])) || ($CURUSER['class'] < UC_SYSOP && $modcomment != $user['modcomment']))
        $updateset[] = "modcomment = " . sqlesc($modcomment);
    if (sizeof($updateset) > 0)
        sql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    status_change($userid);
    
    $returnto = $_POST["returnto"];
    header("Location: {$TBDEV['baseurl']}/$returnto");
    
    stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
}

stderr("{$lang['modtask_user_error']}", "{$lang['modtask_no_idea']}");

?>
