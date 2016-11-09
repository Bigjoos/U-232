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
dbconn();
global $CURUSER;
if (!$CURUSER) {
    get_template();
}

ini_set('session.use_trans_sid', '0');

$lang    = array_merge(load_language('global'), load_language('login'));
$newpage = new page_verify();
$newpage->create('takelogin');


//== 09 failed logins
function left()
{
    global $TBDEV;
    $total = 0;
    $ip    = sqlesc(getip());
    $fail = sql_query("SELECT SUM(attempts) FROM failedlogins WHERE ip=$ip") or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($fail);
    $left = $TBDEV['failedlogins'] - $total;
    if ($left <= 2)
        $left = "<font color='red' size='4'>" . $left . "</font>";
    else
        $left = "<font color='green' size='4'>" . $left . "</font>";
    return $left;
}
//== End Failed logins

$HTMLOUT = '';

unset($returnto);
if (!empty($_GET["returnto"])) {
    $returnto = htmlspecialchars($_GET["returnto"]);
    if (!isset($_GET["nowarn"])) {
        $HTMLOUT .= "<h1>{$lang['login_not_logged_in']}</h1>\n";
        $HTMLOUT .= "{$lang['login_error']}";
    }
}

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
    <form method='post' action='takelogin.php'>
    <noscript>Javascript must be enabled to login and use this site</noscript>
    <p>Note: You need cookies enabled to log in.</p>
    <b>[{$TBDEV['failedlogins']}]</b> Failed logins in a row will ban your ip from access<br />You have <b> " . left() . " </b> login attempt(s) remaining.<br /><br />
    <table border='0' cellpadding='5'>
      <tr>
        <td class='rowhead'>{$lang['login_username']}</td>
        <td align='left'><input type='text' size='40' name='username' /></td>
      </tr>
      <tr>
        <td class='rowhead'>{$lang['login_password']}</td>
        <td align='left'><input type='password' size='40' name='password' /></td>
      </tr>	
      <!--<tr>
      <td>Duration:</td><td align='left'><input type='checkbox' name='logout' value='yes' />Log me out after 15 minutes inactivity</td>
      </tr>-->
     <tr>
     <td class='rowhead' colspan='2' id='captchalogin'></td>
     </tr>
     <tr>
     <td align='center' colspan='2'>Now click the button marked <strong>X</strong></td>
     </tr>
     <tr>
     <td colspan='2' align='center'>";
for ($i = 0; $i < count($value); $i++) {
    $HTMLOUT .= "<input name=\"submitme\" type=\"submit\" value=\"" . $value[$i] . "\" class=\"btn\" />";
}
$HTMLOUT .= "</td></tr></table>";
if (isset($returnto))
    $HTMLOUT .= "<input type='hidden' name='returnto' value='" . htmlentities($returnto) . "' />\n";
$HTMLOUT .= "</form>
     {$lang['login_signup']}{$lang['login_forgot']}";


echo stdhead("{$lang['login_login_btn']}") . $HTMLOUT . stdfoot();

?>
