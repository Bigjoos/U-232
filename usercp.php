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
|   $usercp
|   
+------------------------------------------------
*/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'user_functions.php');
require_once(INCL_DIR . 'html_functions.php');
require_once(INCL_DIR . 'bbcode_functions.php');
require_once(INCL_DIR . 'page_verify.php');
require_once(CACHE_DIR . 'timezones.php');
dbconn(false);
loggedinorreturn();

$lang    = array_merge(load_language('global'), load_language('usercp'));
$newpage = new page_verify();
$newpage->create('tkepe');
$HTMLOUT = '';

$stylesheets = '';
$templates = sql_query("SELECT id, name FROM stylesheets ORDER BY id") or sqlerr(__FILE__, __LINE__);
while ($templ = mysqli_fetch_assoc($templates)) {
    if (file_exists("templates/" . intval($templ['id']) . "/template.php"))
        $stylesheets .= "<option value='" . intval($templ['id']) . "'" . ($templ['id'] == $CURUSER['stylesheet'] ? " selected='selected'" : "") . ">" . htmlspecialchars($templ['name']) . "</option>";
}

$countries = "<option value='0'>---- {$lang['usercp_none']} ----</option>\n";
$ct_r = sql_query("SELECT id,name FROM countries ORDER BY name") or sqlerr(__FILE__, __LINE__);
while ($ct_a = mysqli_fetch_assoc($ct_r)) {
    $countries .= "<option value='" . intval($ct_a['id']) . "'" . ($CURUSER["country"] == $ct_a['id'] ? " selected='selected'" : "") . ">" . htmlspecialchars($ct_a['name']) . "</option>\n";
}

$offset      = ($CURUSER['time_offset'] != "") ? (string) $CURUSER['time_offset'] : (string) $INSTALLER09['time_offset'];
$time_select = "<select name='user_timezone'>";

foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
    }
}
$time_select .= "</select>";

if ($CURUSER['dst_in_use']) {
    $dst_check = 'checked="checked"';
} else {
    $dst_check = '';
}

if ($CURUSER['auto_correct_dst']) {
    $dst_correction = 'checked="checked"';
} else {
    $dst_correction = '';
}

$HTMLOUT .= "<script type='text/javascript'>
        /*<![CDATA[*/
        function daylight_show()
        {
        if ( document.getElementById( 'tz-checkdst' ).checked )
        {
        document.getElementById( 'tz-checkmanual' ).style.display = 'none';
        }
        else
        {
        document.getElementById( 'tz-checkmanual' ).style.display = 'block';
        }
        }
        /*]]>*/
        </script>";

$action = isset($_GET["action"]) ? htmlspecialchars(trim($_GET["action"])) : '';

if (isset($_GET["edited"])) {
    $HTMLOUT .= "<div class='roundedCorners' align='center' style='width:80%; background:#bcffbf; border:1px solid #49c24f; color:#333333;padding:5px;font-weight:bold;'>{$lang['usercp_updated']}!</div>";
    
    if (isset($_GET["mailsent"]))
        $HTMLOUT .= "<h2>{$lang['usercp_mail_sent']}!</h2>\n";
}

elseif (isset($_GET["emailch"])) {
    $HTMLOUT .= "<h1>{$lang['usercp_emailch']}!</h1>\n";
}

$HTMLOUT .= "<h1>Welcome <a href='userdetails.php?id=" . intval($CURUSER['id']) . "'>" . htmlspecialchars($CURUSER['username']) . "</a> !</h1>\n
    <form method='post' action='takeeditcp.php'>
    <table border='1' width='600' cellspacing='0' cellpadding='3' align='center'><tr>
    <td width='600' valign='top'>";
//== Avatar
if ($action == "avatar") {
    $HTMLOUT .= begin_table(true);
    $HTMLOUT .= "<tr><td align='left' class='colhead' style='height:25px;' colspan='2'><input type='hidden' name='action' value='avatar' />Avatar Options</td></tr>";
    if (!($CURUSER["avatarpos"] == 0 OR $CURUSER["avatarpos"] != 1)) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['usercp_avatar']}</td><td><input name='avatar' size='50' value='" . htmlspecialchars($CURUSER["avatar"]) . "' /><br />
    <font class='small'>Width should be 150px. (Will be resized if necessary)\n<br />
    If you need a host for the picture, try our  <a href='{$INSTALLER09['baseurl']}/bitbucket.php'>Bitbucket</a>.</font>
    <br /><input type='checkbox' name='offavatar' " . ($CURUSER["offavatar"] == "yes" ? " checked='checked'" : "") . " /><b>This avatar may be offensive to some people.</b><br />
    <font class='small'>Please check this box if your avatar contains nudity or may otherwise be potentially offensive to or unsuitable for minors.</font></td></tr>";
    } else {
        $HTMLOUT .= tr("{$lang['usercp_avatar']}", "{$lang['usercp_no_avatar_allow']}");
    }
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['usercp_view_avatars']}</td><td><input type='radio' name='avatars'" . ($CURUSER["avatars"] == "all" ? " checked='checked'" : "") . " value='all' />All
    <input type='radio' name='avatars' " . ($CURUSER["avatars"] == "some" ? " checked='checked'" : "") . " value='some' />All except potentially offensive
    <input type='radio' name='avatars' " . ($CURUSER["avatars"] == "none" ? " checked='checked'" : "") . " value='none' />None</td></tr>";
    $HTMLOUT .= "<tr><td align='center' colspan='2'><input type='submit' value='Submit changes!' style='height: 25px' /></td></tr>";
    $HTMLOUT .= end_table();
}
//== Signature
elseif ($action == "signature") {
    $HTMLOUT .= begin_table(true);
    $HTMLOUT .= "<tr><td align='left' class='colhead' style='height:25px;' colspan='2'><input type='hidden' name='action' value='signature' />Signature Options</td></tr>";
    $HTMLOUT .= tr($lang['usercp_signature'], "<input name='signature' size='50' value='" . htmlspecialchars($CURUSER["signature"]) . "' /><br />\n{$lang['usercp_signature_info']}", 1);
    $HTMLOUT .= tr($lang['usercp_view_signatures'], "<input type='checkbox' name='signatures'" . ($CURUSER["signatures"] == "yes" ? " checked='checked'" : "") . " /> {$lang['usercp_low_bw']}", 1);
    $HTMLOUT .= tr($lang['usercp_info'], "<textarea name='info' cols='50' rows='4'>" . htmlentities($CURUSER["info"], ENT_QUOTES) . "</textarea><br />{$lang['usercp_tags']}", 1);
    $HTMLOUT .= "<tr ><td align='center' colspan='2'><input type='submit' value='Submit changes!' style='height: 25px' /></td></tr>";
    $HTMLOUT .= end_table();
}
//== Security
    elseif ($action == "security") {
    $HTMLOUT .= begin_table(true);
    $HTMLOUT .= "<tr><td class='colhead' colspan='2' style='height:25px;'><input type='hidden' name='action' value='security' />Security Options</td></tr>";
    $HTMLOUT .= tr($lang['usercp_acc_parked'], "<input type='radio' name='parked'" . ($CURUSER["parked"] == "yes" ? " checked='checked'" : "") . " value='yes' />yes
    <input type='radio' name='parked'" . ($CURUSER["parked"] == "no" ? " checked='checked'" : "") . " value='no' />no
    <br /><font class='small' size='1'>{$lang['usercp_acc_parked_message']}<br />{$lang['usercp_acc_parked_message1']}</font>", 1);
    if (get_anonymous() != '0')
        $HTMLOUT .= tr($lang['usercp_anonymous'], "<input type='checkbox' name='anonymous'" . ($CURUSER["anonymous"] == "yes" ? " checked='checked'" : "") . " /> {$lang['usercp_default_anonymous']}", 1);
    $HTMLOUT .= tr($lang['usercp_email'], "<input type='text' name='email' size='50' value='" . htmlspecialchars($CURUSER["email"]) . "' /><br />{$lang['usercp_email_pass']}<br /><input type='password' name='chmailpass' size='50' />", 1);
    $HTMLOUT .= "<tr><td colspan='2' align='left'>{$lang['usercp_note']}</td></tr>\n";
    $HTMLOUT .= tr($lang['usercp_chpass'], "<input type='password' name='chpassword' size='50' />", 1);
    $HTMLOUT .= tr($lang['usercp_pass_again'], "<input type='password' name='passagain' size='50' />", 1);
    $secretqs  = "<option value='0'>{$lang['usercp_none_select']}</option>\n";
    $questions = array(
        array(
            "id" => "1",
            "question" => "{$lang['usercp_q1']}"
        ),
        array(
            "id" => "2",
            "question" => "{$lang['usercp_q2']}"
        ),
        array(
            "id" => "3",
            "question" => "{$lang['usercp_q3']}"
        ),
        array(
            "id" => "4",
            "question" => "{$lang['usercp_q4']}"
        ),
        array(
            "id" => "5",
            "question" => "{$lang['usercp_q5']}"
        ),
        array(
            "id" => "6",
            "question" => "{$lang['usercp_q6']}"
        )
    );
    foreach ($questions as $sctq) {
        $secretqs .= "<option value='" . $sctq['id'] . "'" . ($CURUSER["passhint"] == $sctq['id'] ? " selected='selected'" : "") . ">" . $sctq['question'] . "</option>\n";
    }
    $HTMLOUT .= tr($lang['usercp_question'], "<select name='changeq'>\n$secretqs\n</select>", 1);
    $HTMLOUT .= tr($lang['usercp_sec_answer'], "<input type='text' name='secretanswer' size='40' />", 1);
    $HTMLOUT .= "<tr ><td align='center' colspan='2'><input type='submit' value='Submit changes!' style='height: 25px' /></td></tr>";
    $HTMLOUT .= end_table();
}
//== Torrents
    elseif ($action == "torrents") {
    $HTMLOUT .= begin_table(true);
    $HTMLOUT .= "<tr><td class='colhead' colspan='2'  style='height:25px;' ><input type='hidden' name='action' value='torrents' />Torrent Options</td></tr>";
    $categories = '';
    $r = sql_query("SELECT id,name FROM categories ORDER BY name") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($r) > 0) {
        $categories .= "<table><tr>\n";
        $i = 0;
        while ($a = mysqli_fetch_assoc($r)) {
            $categories .= ($i && $i % 2 == 0) ? "</tr><tr>" : "";
            $categories .= "<td class='bottom' style='padding-right: 5px'><input name='cat{$a['id']}' type='checkbox' " . (strpos($CURUSER['notifs'], "[cat{$a['id']}]") !== false ? " checked='checked'" : "") . " value='yes' />&nbsp;" . htmlspecialchars($a["name"]) . "</td>\n";
            ++$i;
        }
        $categories .= "</tr></table>\n";
    }
    $HTMLOUT .= tr($lang['usercp_email_notif'], "<input type='checkbox' name='pmnotif'" . (strpos($CURUSER['notifs'], "[pm]") !== false ? " checked='checked'" : "") . " value='yes' /> {$lang['usercp_notify_pm']}<br />\n" . "<input type='checkbox' name='emailnotif'" . (strpos($CURUSER['notifs'], "[email]") !== false ? " checked='checked'" : "") . " value='yes' /> {$lang['usercp_notify_torrent']}\n", 1);
    $HTMLOUT .= tr($lang['usercp_browse'], $categories, 1);
    $HTMLOUT .= tr($lang['usercp_clearnewtagmanually'], "<input type='checkbox' name='clear_new_tag_manually'" . ($CURUSER["clear_new_tag_manually"] == "yes" ? " checked='checked'" : "") . " /> {$lang['usercp_default_clearnewtagmanually']}", 1);
    $HTMLOUT .= tr($lang['usercp_scloud'], "<input type='checkbox' name='viewscloud'" . ($CURUSER["viewscloud"] == "yes" ? " checked='checked'" : "") . " /> {$lang['usercp_scloud1']}", 1);
    $HTMLOUT .= "<tr><td align='center' colspan='2'><input type='submit' value='Submit changes!' style='height: 25px' /></td></tr>";
    $HTMLOUT .= end_table();
}
//== Personal
    elseif ($action == "personal") {
    $HTMLOUT .= begin_table(true);
    $HTMLOUT .= "<tr><td class='colhead' colspan='2'  style='height:25px;' ><input type='hidden' name='action' value='personal' />Personal Options</td></tr>";
    $HTMLOUT .= tr($lang['usercp_tor_perpage'], "<input type='text' size='10' name='torrentsperpage' value='$CURUSER[torrentsperpage]' /> {$lang['usercp_default']}", 1);
    $HTMLOUT .= tr($lang['usercp_top_perpage'], "<input type='text' size='10' name='topicsperpage' value='$CURUSER[topicsperpage]' /> {$lang['usercp_default']}", 1);
    $HTMLOUT .= tr($lang['usercp_post_perpage'], "<input type='text' size='10' name='postsperpage' value='$CURUSER[postsperpage]' /> {$lang['usercp_default']}", 1);
    $HTMLOUT .= tr($lang['usercp_tz'], $time_select, 1);
    $HTMLOUT .= tr($lang['usercp_checkdst'], "<input type='checkbox' name='checkdst' id='tz-checkdst' onclick='daylight_show()' value='1' $dst_correction />&nbsp;{$lang['usercp_auto_dst']}<br />
    <div id='tz-checkmanual' style='display: none;'><input type='checkbox' name='manualdst' value='1' $dst_check />&nbsp;{$lang['usercp_is_dst']}</div>", 1);
    $HTMLOUT .= tr($lang['usercp_language'], "English", 1);
    $HTMLOUT .= tr($lang['usercp_country'], "<select name='country'>\n$countries\n</select>", 1);
    $HTMLOUT .= tr($lang['usercp_stylesheet'], "<select name='stylesheet'>\n$stylesheets\n</select>", 1);
    $HTMLOUT .= tr($lang['usercp_gender'], "<input type='radio' name='gender'" . ($CURUSER["gender"] == "Male" ? " checked='checked'" : "") . " value='Male' />{$lang['usercp_male']}
    <input type='radio' name='gender'" . ($CURUSER["gender"] == "Female" ? " checked='checked'" : "") . " value='Female' />{$lang['usercp_female']}
    <input type='radio' name='gender'" . ($CURUSER["gender"] == "N/A" ? " checked='checked'" : "") . " value='N/A' />{$lang['usercp_na']}", 1);
    $HTMLOUT .= tr($lang['usercp_shoutback'], "<input type='radio' name='shoutboxbg'" . ($CURUSER["shoutboxbg"] == "1" ? " checked='checked'" : "") . " value='1' />{$lang['usercp_shoutback_white']}
    <input type='radio' name='shoutboxbg'" . ($CURUSER["shoutboxbg"] == "2" ? " checked='checked'" : "") . " value='2' />{$lang['usercp_shoutback_grey']}<input type='radio' name='shoutboxbg'" . ($CURUSER["shoutboxbg"] == "3" ? " checked='checked'" : "") . " value='3' />{$lang['usercp_shoutback_black']}", 1);
    $HTMLOUT .= "<tr><td align='center' colspan='2'><input type='submit' value='Submit changes!' style='height: 25px' /></td></tr>";
    $HTMLOUT .= end_table();
} else {
    //== Pms
    $HTMLOUT .= begin_table(true);
    $HTMLOUT .= "<tr><td class='colhead' colspan='2'  style='height:25px;' ><input type='hidden' name='action' value='pm' />Pm options</td></tr>";
    $HTMLOUT .= tr($lang['usercp_accept_pm'], "<input type='radio' name='acceptpms'" . ($CURUSER["acceptpms"] == "yes" ? " checked='checked'" : "") . " value='yes' />{$lang['usercp_except_blocks']}
    <input type='radio' name='acceptpms'" . ($CURUSER["acceptpms"] == "friends" ? " checked='checked'" : "") . " value='friends' />{$lang['usercp_only_friends']}
    <input type='radio' name='acceptpms'" . ($CURUSER["acceptpms"] == "no" ? " checked='checked'" : "") . " value='no' />{$lang['usercp_only_staff']}", 1);
    $HTMLOUT .= tr($lang['usercp_delete_pms'], "<input type='checkbox' name='deletepms'" . ($CURUSER["deletepms"] == "yes" ? " checked='checked'" : "") . " /> {$lang['usercp_default_delete']}", 1);
    $HTMLOUT .= tr($lang['usercp_save_pms'], "<input type='checkbox' name='savepms'" . ($CURUSER["savepms"] == "yes" ? " checked='checked'" : "") . " /> {$lang['usercp_default_save']}", 1);
    $HTMLOUT .= tr("Forum Subscribe Pm", "<input type='radio' name='subscription_pm' " . ($CURUSER["subscription_pm"] == "yes" ? " checked='checked'" : "") . " value='yes' />yes <input type='radio' name='subscription_pm' " . ($CURUSER["subscription_pm"] == "no" ? " checked='checked'" : "") . " value='no' />no<br /> When someone posts in a subscribed thread, you will be PMed.", 1);
    $HTMLOUT .= "<tr><td align='center' colspan='2'><input type='submit' value='Submit changes!' style='height: 25px' /></td></tr>";
    $HTMLOUT .= end_table();
}

$HTMLOUT .= "</td><td width='95' valign='top' ><table border='1'>";
$HTMLOUT .= "<tr><td class='colhead' width='95'  style='height:25px;' >" . htmlentities($CURUSER["username"], ENT_QUOTES) . "'s Avatar</td></tr>";
if (!empty($CURUSER['avatar']) && $CURUSER['av_w'] > 5 && $CURUSER['av_h'] > 5)
    $HTMLOUT .= "<tr><td><img src='{$CURUSER['avatar']}' width='{$CURUSER['av_w']}' height='{$CURUSER['av_h']}' alt='' />
    <a href='mytorrents.php'>{$lang['usercp_edit_torrents']}</a><br />
    <a href='friends.php'>{$lang['usercp_edit_friends']}</a><br />
    <a href='users.php'>{$lang['usercp_search']}</a>
    </td></tr>";
else
    $HTMLOUT .= "<tr><td><img src='{$INSTALLER09['pic_base_url']}forumicons/default_avatar.gif' alt='' /><a href='mytorrents.php'>{$lang['usercp_edit_torrents']}</a><br />
    <a href='friends.php'>{$lang['usercp_edit_friends']}</a><br />
    <a href='users.php'>{$lang['usercp_search']}</a></td></tr>";
$HTMLOUT .= "<tr><td class='colhead' width='95' style='height:18px;'>" . htmlentities($CURUSER["username"], ENT_QUOTES) . "'s Menu</td></tr>";
$HTMLOUT .= "<tr><td align='left'><a href='usercp.php?action=avatar'>Avatar</a></td></tr>
    <tr><td align='left'><a href='usercp.php?action=signature'>Signature</a></td></tr>
    <tr><td align='left'><a href='usercp.php'>Pm's</a></td></tr>
    <tr><td align='left'><a href='usercp.php?action=security'>Security</a></td></tr>
    <tr><td align='left'><a href='usercp.php?action=torrents'>Torrents</a></td></tr>
    <tr><td align='left'><a href='usercp.php?action=personal'>Personal</a></td></tr>
    <tr><td align='left'><a href='invite.php'>Invites</a></td></tr>
    <tr><td align='left'><a href='tenpercent.php'>Lifesaver</a></td></tr>
    <tr><td class='colhead' width='95'>" . htmlentities($CURUSER["username"], ENT_QUOTES) . "'s Entertainment</td></tr>
    <tr><td align='left'><a href='topmoods.php'>Top Member Mood's</a></td></tr>";

$HTMLOUT .= "</table></td></tr></table></form>";

echo stdhead(htmlentities($CURUSER["username"], ENT_QUOTES) . "{$lang['usercp_stdhead']}", false) . $HTMLOUT . stdfoot();

?>
