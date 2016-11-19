<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
/*
+------------------------------------------------
|   $Date$ 010810
|   $Revision$ 2.0
|   $Author$ Bigjoos
|   $URL$
|   $takeeditcp
|   
+------------------------------------------------
*/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'user_functions.php');
require_once(INCL_DIR . 'page_verify.php');
require_once(INCL_DIR . 'password_functions.php');
dbconn();
loggedinorreturn();

$lang    = array_merge(load_language('global'), load_language('takeeditcp'));
$newpage = new page_verify();
$newpage->check('tkepe');

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

$action    = isset($_POST["action"]) ? htmlspecialchars(trim($_POST["action"])) : '';
$updateset = array();
$urladd    = '';
//== Avatars stuffs
if ($action == "avatar") {
    $avatars        = (isset($_POST['avatars']) ? $_POST['avatars'] : 'all');
    $avatar_choices = array(
        'all' => 1,
        'some' => 2,
        'none' => 3
    );
    $offavatar      = (isset($_POST['offavatar']) && $_POST["offavatar"] != "" ? "yes" : "no");
    if (!($CURUSER["avatarpos"] == 0 OR $CURUSER["avatarpos"] != 1)) {
        $avatar = trim(urldecode($_POST["avatar"]));
        if (preg_match("/^http:\/\/$/i", $avatar) or preg_match("/[?&;]/", $avatar) or preg_match("#javascript:#is", $avatar) or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $avatar)) {
            $avatar = '';
        }
    }
    if (!empty($avatar)) {
        $img_size = @GetImageSize($avatar);
        if ($img_size == FALSE || !in_array($img_size['mime'], $INSTALLER09['allowed_ext']))
            stderr($lang['takeeditcp_user_error'], $lang['takeeditcp_image_error']);
        if ($img_size[0] < 5 || $img_size[1] < 5)
            stderr($lang['takeeditcp_user_error'], $lang['takeeditcp_small_image']);
        if (($img_size[0] > $INSTALLER09['av_img_width']) OR ($img_size[1] > $INSTALLER09['av_img_height'])) {
            $image = resize_image(array(
                'max_width' => $INSTALLER09['av_img_width'],
                'max_height' => $INSTALLER09['av_img_height'],
                'cur_width' => $img_size[0],
                'cur_height' => $img_size[1]
            ));
        } else {
            $image['img_width']  = $img_size[0];
            $image['img_height'] = $img_size[1];
        }
        $updateset[] = "av_w = " . $image['img_width'];
        $updateset[] = "av_h = " . $image['img_height'];
    }
    if (isset($avatar_choices[$avatars]))
        $updateset[] = "avatars = '$avatars'";
    $updateset[] = "offavatar = " . sqlesc($offavatar);
    if (!($CURUSER["avatarpos"] == 0 OR $CURUSER["avatarpos"] != 1)) {
        $updateset[] = "avatar = " . sqlesc($avatar);
    }
    $action = "avatar";
}
//== Signature stuffs
elseif ($action == "signature") {
    $signatures = (isset($_POST['signatures']) && $_POST["signatures"] != "" ? "yes" : "no");
    $signature  = trim(urldecode($_POST["signature"]));
    if (preg_match("/^http:\/\/$/i", $signature) or preg_match("/[?&;]/", $signature) or preg_match("#javascript:#is", $signature) or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $signature)) {
        $signature = '';
    }
    if (!empty($signature)) {
        $img_size = @GetImageSize($signature);
        if ($img_size == FALSE || !in_array($img_size['mime'], $INSTALLER09['allowed_ext']))
            stderr('USER ERROR', 'Not an image or unsupported image!');
        if ($img_size[0] < 5 || $img_size[1] < 5)
            stderr('USER ERROR', 'Image is too small');
        if (($img_size[0] > $INSTALLER09['sig_img_width']) OR ($img_size[1] > $INSTALLER09['sig_img_height'])) {
            $image = resize_image(array(
                'max_width' => $INSTALLER09['sig_img_width'],
                'max_height' => $INSTALLER09['sig_img_height'],
                'cur_width' => $img_size[0],
                'cur_height' => $img_size[1]
            ));
        } else {
            $image['img_width']  = $img_size[0];
            $image['img_height'] = $img_size[1];
        }
        $updateset[] = "sig_w = " . $image['img_width'];
        $updateset[] = "sig_h = " . $image['img_height'];
        $updateset[] = "signature = " . sqlesc("[img]" . $signature . "[/img]\n");
    }
    
    $updateset[] = "signatures = '$signatures'";
    
    
    if (isset($_POST["info"]) && (($info = $_POST["info"]) != $CURUSER["info"])) {
        $updateset[] = "info = " . sqlesc($info);
    }
    $action = "signature";
}
//== Security Stuffs
    elseif ($action == "security") {
    if (!mkglobal("email:chpassword:passagain:chmailpass:secretanswer"))
        stderr("Error", $lang['takeeditcp_no_data']);
    if ($chpassword != "") {
        if (strlen($chpassword) > 40)
            stderr("Error", $lang['takeeditcp_pass_long']);
        if ($chpassword != $passagain)
            stderr("Error", $lang['takeeditcp_pass_not_match']);
        $secret      = mksecret();
        $passhash    = make_passhash($secret, md5($chpassword));
        $updateset[] = "secret = " . sqlesc($secret);
        $updateset[] = "passhash = " . sqlesc($passhash);
        logincookie($CURUSER["id"], md5($passhash . $_SERVER["REMOTE_ADDR"]));
    }
    if ($email != $CURUSER["email"]) {
        if (!validemail($email))
            stderr("Error", $lang['takeeditcp_not_valid_email']);
        $r = @sql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr();
        if (mysqli_num_rows($r) > 0 || ($CURUSER["passhash"] != make_passhash($CURUSER['secret'], md5($chmailpass))))
            stderr("Error", $lang['takeeditcp_address_taken']);
        $changedemail = 1;
    }
    if ($secretanswer != '') {
        if (strlen($secretanswer) > 40)
            stderr("Sorry", "secret answer is too long (max is 40 chars)");
        if (strlen($secretanswer) < 6)
            stderr("Sorry", "secret answer is too sort (min is 6 chars)");
        $new_secret_answer = md5($secretanswer);
        $updateset[]       = "hintanswer = " . sqlesc($new_secret_answer);
    }
    if (isset($_POST["parked"]) && ($parked = $_POST["parked"]) != $CURUSER["parked"]) {
        $updateset[] = "parked = " . sqlesc($parked);
    }
    if (get_anonymous() != '0') {
        $anonymous   = (isset($_POST['anonymous']) && $_POST["anonymous"] != "" ? "yes" : "no");
        $updateset[] = "anonymous = " . sqlesc($anonymous);
    }
    if (isset($_POST["changeq"]) && (($changeq = (int) $_POST["changeq"]) != $CURUSER["passhint"]) && is_valid_id($changeq)) {
        $updateset[] = "passhint = " . sqlesc($changeq);
    }
    $urladd       = "";
    $changedemail = 0;
    if ($changedemail) {
        $sec         = mksecret();
        $hash        = md5($sec . $email . $sec);
        $obemail     = urlencode($email);
        $updateset[] = "editsecret = " . sqlesc($sec);
        //$thishost = $_SERVER["HTTP_HOST"];
        //$thisdomain = preg_replace('/^www\./is', "", $thishost);
        $body        = str_replace(array(
            '<#USERNAME#>',
            '<#SITENAME#>',
            '<#USEREMAIL#>',
            '<#IP_ADDRESS#>',
            '<#CHANGE_LINK#>'
        ), array(
            $CURUSER['username'],
            $INSTALLER09['site_name'],
            $email,
            $_SERVER['REMOTE_ADDR'],
            "{$INSTALLER09['baseurl']}/confirmemail.php?uid={$CURUSER['id']}&key=$hash&email=$obemail"
        ), $lang['takeeditcp_email_body']);
        mail($email, "$thisdomain {$lang['takeeditcp_confirm']}", $body, "From: {$INSTALLER09['site_email']}");
        $urladd .= "&mailsent=1";
    }
    $action = "security";
}
//== Torrent stuffs
    elseif ($action == "torrents") {
    $pmnotif    = isset($_POST["pmnotif"]) ? $_POST["pmnotif"] : '';
    $emailnotif = isset($_POST["emailnotif"]) ? $_POST["emailnotif"] : '';
    $notifs     = ($pmnotif == 'yes' ? "[pm]" : "");
    $notifs .= ($emailnotif == 'yes' ? "[email]" : "");
    $r = sql_query("SELECT id FROM categories") or sqlerr(__FILE__, __LINE__);
    $rows = mysqli_num_rows($r);
    for ($i = 0; $i < $rows; ++$i) {
        $a = mysqli_fetch_assoc($r);
        if (isset($_POST["cat{$a['id']}"]) && $_POST["cat{$a['id']}"] == 'yes')
            $notifs .= "[cat{$a['id']}]";
    }
    $updateset[] = "notifs = '$notifs'";
    $viewscloud  = (isset($_POST['viewscloud']) && $_POST["viewscloud"] != "" ? "yes" : "no"); {
        $updateset[] = "viewscloud = " . sqlesc($viewscloud);
    }
    $clear_new_tag_manually = (isset($_POST['clear_new_tag_manually']) && $_POST["clear_new_tag_manually"] != "" ? "yes" : "no"); {
        $updateset[] = "clear_new_tag_manually = " . sqlesc($clear_new_tag_manually);
    }
    $action = "torrents";
}
//== Personal stuffs
    elseif ($action == "personal") {
    if (isset($_POST['stylesheet']) && (($stylesheet = (int) $_POST['stylesheet']) != $CURUSER['stylesheet']) && is_valid_id($stylesheet))
        $updateset[] = 'stylesheet = ' . sqlesc($stylesheet);
    if (isset($_POST["country"]) && (($country = $_POST["country"]) != $CURUSER["country"]) && is_valid_id($country))
        $updateset[] = "country = " . sqlesc($country);
    if (isset($_POST["torrentsperpage"]) && (($torrentspp = min(100, 0 + $_POST["torrentsperpage"])) != $CURUSER["torrentsperpage"]))
        $updateset[] = "torrentsperpage = " . sqlesc($torrentspp);
    if (isset($_POST["topicsperpage"]) && (($topicspp = min(100, 0 + $_POST["topicsperpage"])) != $CURUSER["topicsperpage"]))
        $updateset[] = "topicsperpage = " . sqlesc($topicspp);
    if (isset($_POST["postsperpage"]) && (($postspp = min(100, 0 + $_POST["postsperpage"])) != $CURUSER["postsperpage"]))
        $updateset[] = "postsperpage = " . sqlesc($postspp);
    if (isset($_POST["gender"]) && ($gender = $_POST["gender"]) != $CURUSER["gender"])
        $updateset[] = "gender = " . sqlesc($gender);
    $shoutboxbg  = 0 + $_POST["shoutboxbg"];
    $updateset[] = "shoutboxbg = " . sqlesc($shoutboxbg);
    if (isset($_POST["user_timezone"]) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone']))
        $updateset[] = "time_offset = " . sqlesc($_POST['user_timezone']);
    $updateset[] = "auto_correct_dst = " . (isset($_POST['checkdst']) ? 1 : 0);
    $updateset[] = "dst_in_use = " . (isset($_POST['manualdst']) ? 1 : 0);
    $action      = "personal";
}
//== Pm stuffs
    elseif ($action == "pm") {
    $acceptpms_choices = array(
        'yes' => 1,
        'friends' => 2,
        'no' => 3
    );
    $acceptpms         = (isset($_POST['acceptpms']) ? $_POST['acceptpms'] : 'all');
    if (isset($acceptpms_choices[$acceptpms]))
        $updateset[] = "acceptpms = " . sqlesc($acceptpms);
    $deletepms       = isset($_POST["deletepms"]) ? "yes" : "no";
    $updateset[]     = "deletepms = " . sqlesc($deletepms);
    $savepms         = (isset($_POST['savepms']) && $_POST["savepms"] != "" ? "yes" : "no");
    $updateset[]     = "savepms = " . sqlesc($savepms);
    $subscription_pm = $_POST["subscription_pm"];
    $updateset[]     = "subscription_pm = " . sqlesc($subscription_pm);
    $action          = "pm";
}
//== End == then update the sets :)
if (sizeof($updateset) > 0)
    sql_query("UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
header("Location: {$INSTALLER09['baseurl']}/usercp.php?edited=1&action=$action" . $urladd);
?>
