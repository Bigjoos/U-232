<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'html_functions.php');
require_once(INCL_DIR . 'page_verify.php');
require_once(CACHE_DIR . 'timezones.php');
dbconn();

global $CURUSER;
if (!$CURUSER) {
    get_template();
}


if (isset($CURUSER)) {
    header("Location: {$TBDEV['baseurl']}/index.php");
    exit();
}

ini_set('session.use_trans_sid', '0');

$stdfoot = array(
    /** include js **/
    'js' => array(
        'check',
        'jquery.pstrength-min.1.2'
    )
);

if (!$TBDEV['openreg'])
    stderr('Sorry', 'Invite only - Signups are closed presently');

$HTMLOUT = '';

$HTMLOUT .= "
    <script type='text/javascript'>
    /*<![CDATA[*/
    $(function() {
    $('.password').pstrength();
    });
    /*]]>*/
    </script>";

$res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);

$lang    = array_merge(load_language('global'), load_language('signup'));
$newpage = new page_verify();
$newpage->create('tesu');

if (isset($_SESSION['captcha_time']))
    (time() - $_SESSION['captcha_time'] < 10) ? exit($lang['captcha_spam']) : NULL;

if ($arr[0] >= $TBDEV['maxusers'])
    stderr($lang['stderr_errorhead'], sprintf($lang['stderr_ulimit'], $TBDEV['maxusers']));


// TIMEZONE STUFF
$offset = (string) $TBDEV['time_offset'];

$time_select = "<select name='user_timezone'>";

foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
    }
}

$time_select .= "</select>";
// TIMEZONE END

$thistime                          = time();
// Normal Entry Point...
$value                             = array(
    '...',
    '...',
    '...',
    '...',
    '...',
    '...'
);
$value[rand(1, count($value) - 1)] = 'X';
$HTMLOUT .= "<script type='text/javascript' src='scripts/jquery.js'></script>
    <script type='text/javascript' src='scripts/jquery.simpleCaptcha-0.2.js'></script>
    <script type='text/javascript'>
	  $(document).ready(function () {
	  $('#captchalogin').simpleCaptcha();
    });
    </script>
    <noscript>Javascript must be enabled to signup and use this site</noscript>
    <p>{$lang['signup_cookies']}</p>
    <form method='post' action='takesignup.php'>
    <table border='1' cellspacing='0' cellpadding='10'>
     <tr><td align='right' class='heading'>{$lang['signup_uname']}</td><td align='left'><input type='text' size='40' name='wantusername' id='wantusername' onblur='checkit();' /><div id='namecheck'></div></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_pass']}</td><td align='left'><input class='password' type='password' size='40' name='wantpassword' /></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_passa']}</td><td align='left'><input type='password' size='40' name='passagain' /></td></tr>
    <tr valign='top'><td align='right' class='heading'>{$lang['signup_email']}</td><td align='left'><input type='text' size='40' name='email' />
	 
     <table width='250' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><font class='small'>{$lang['signup_valemail']}</font></td></tr>
     </table>
     </td></tr>
     <tr><td align='right' class='heading'>{$lang['signup_timez']}</td><td align='left'>{$time_select}</td></tr>";
//==Passhint
$passhint  = "";
$questions = array(
    array(
        "id" => "1",
        "question" => "{$lang['signup_q1']}"
    ),
    array(
        "id" => "2",
        "question" => "{$lang['signup_q2']}"
    ),
    array(
        "id" => "3",
        "question" => "{$lang['signup_q3']}"
    ),
    array(
        "id" => "4",
        "question" => "{$lang['signup_q4']}"
    ),
    array(
        "id" => "5",
        "question" => "{$lang['signup_q5']}"
    ),
    array(
        "id" => "6",
        "question" => "{$lang['signup_q6']}"
    )
);
foreach ($questions as $sph) {
    $passhint .= "<option value='" . $sph['id'] . "'>" . $sph['question'] . "</option>\n";
}
$HTMLOUT .= "<tr><td align='right' class='heading'>{$lang['signup_select']}</td><td align='left'><select name='passhint'>\n$passhint\n</select></td></tr>
		  <tr><td align='right' class='heading'>{$lang['signup_enter']}</td><td align='left'><input type='text' size='40'  name='hintanswer' /><br /><font class='small'>{$lang['signup_this_answer']}<br />{$lang['signup_this_answer1']}</font></td></tr>	
      <tr><td align='right' class='heading'></td><td align='left'><input type='checkbox' name='rulesverify' value='yes' /> {$lang['signup_rules']}<br />
      <input type='checkbox' name='faqverify' value='yes' /> {$lang['signup_faq']}<br />
      <input type='checkbox' name='ageverify' value='yes' /> {$lang['signup_age']}</td></tr>
      <tr><td class='rowhead' colspan='2' id='captchalogin'></td></tr>
      <tr><td align='center' colspan='2'>Now click the button marked <strong>X</strong> to complete the sign up!</td></tr><tr>
      <td colspan='2' align='center'>";
for ($i = 0; $i < count($value); $i++) {
    $HTMLOUT .= "<input name=\"submitme\" type=\"submit\" value=\"" . $value[$i] . "\" class=\"btn\" />";
}
$HTMLOUT .= "</td></tr></table></form>";

echo stdhead($lang['head_signup']) . $HTMLOUT . stdfoot($stdfoot);
?>
