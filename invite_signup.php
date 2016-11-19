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
require_once(CACHE_DIR.'timezones.php');
require_once(INCL_DIR.'page_verify.php');
dbconn();
get_template();

$stdfoot = array(/** include js **/'js' => array('check','jquery.pstrength-min.1.2'));

$lang = array_merge( load_language('global'), load_language('signup') );
$newpage = new page_verify(); 
$newpage->create('tkIs');

$res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
if ($arr[0] >= $INSTALLER09['maxusers'])
stderr("Sorry", "The current user account limit (" . number_format($INSTALLER09['maxusers']) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");

if(!$INSTALLER09['openreg'])
    stderr('Sorry', 'Invite only - Signups are closed presently');

// TIMEZONE STUFF
        $offset = (string)$INSTALLER09['time_offset'];
        
        $time_select = "<select name='user_timezone'>";
        
        foreach( $TZ as $off => $words )
        {
          if ( preg_match("/^time_(-?[\d\.]+)$/", $off, $match))
          {
            $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
          }
        }
        
        $time_select .= "</select>";
    // TIMEZONE END

$HTMLOUT='';

$HTMLOUT .= "
    <script type='text/javascript'>
    /*<![CDATA[*/
    $(function() {
    $('.password').pstrength();
    });
    /*]]>*/
    </script>";
// Normal Entry Point...
$value = array('...','...','...','...','...','...');
$value[rand(1,count($value)-1)] = 'X';
$HTMLOUT .="<script type='text/javascript' src='scripts/jquery.js'></script>
    <script type='text/javascript' src='scripts/jquery.simpleCaptcha-0.2.js'></script>
    <script type='text/javascript'>
	  $(document).ready(function () {
	  $('#captchainvite').simpleCaptcha();
    });
    </script>
<p>Note: You need cookies enabled to sign up or log in.</p>
<form method='post' action='{$INSTALLER09['baseurl']}/take_invite_signup.php'>
<noscript>Javascript must be enabled to login and use this site</noscript>
<table border='1' cellspacing='0' cellpadding='10'>

<tr><td align='right' class='heading'>Desired username:</td><td align='left'><input type='text' size='40' name='wantusername' id='wantusername' onblur='checkit();' /><div id='namecheck'></div></td></tr>
<tr><td align='right' class='heading'>Pick a password:</td><td align='left'><input class='password' type='password' size='40' name='wantpassword' /></td></tr>
<tr><td align='right' class='heading'>Enter password again:</td><td align='left'><input type='password' size='40' name='passagain' /></td></tr>
<tr><td align='right' class='heading'>Enter invite-code:</td><td align='left'><input type='text' size='40' name='invite' /></td></tr>
<tr valign='top'><td align='right' class='heading'>Email address:</td><td align='left'><input type='text' size='40' name='email' />
<table width='250' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><font class='small'>The email address must be valid. The email address won't be publicly shown anywhere unless you chose to from your settings.</font></td></tr></table></td></tr>
<tr><td align='right' class='heading'>{$lang['signup_timez']}</td><td align='left'>{$time_select}</td></tr>";
//==Passhint
     $passhint="";
     $questions = array(
	    array("id"=> "1", "question"=> "{$lang['signup_q1']}"),
			array("id"=> "2", "question"=> "{$lang['signup_q2']}"),
			array("id"=> "3", "question"=> "{$lang['signup_q3']}"),
			array("id"=> "4", "question"=> "{$lang['signup_q4']}"),
			array("id"=> "5", "question"=> "{$lang['signup_q5']}"),
			array("id"=> "6", "question"=> "{$lang['signup_q6']}"));
		  foreach($questions as $sph){  
		  $passhint .= "<option value='".$sph['id']."'>".$sph['question']."</option>\n"; 
		  }
		  $HTMLOUT .= "<tr><td align='right' class='heading'>{$lang['signup_select']}</td><td align='left'><select name='passhint'>\n$passhint\n</select></td></tr>

		  <tr><td align='right' class='heading'>{$lang['signup_enter']}</td><td align='left'><input type='text' size='40'  name='hintanswer' /><br /><font class='small'>{$lang['signup_this_answer']}<br />{$lang['signup_this_answer1']}</font></td></tr>
<tr><td align='right' class='heading'></td><td align='left'><input type='checkbox' name='rulesverify' value='yes' /> I will read the site rules page.<br />
<input type='checkbox' name='faqverify' value='yes' /> I agree to read the FAQ before asking questions.<br />
<input type='checkbox' name='ageverify' value='yes' /> I am at least 18 years old.</td></tr>
<tr><td class='rowhead' colspan='2' id='captchainvite'></td></tr>
<tr><td align='center' colspan='2'>Now click the button marked <strong>X</strong> to complete the sign up!</td></tr><tr>
<td colspan='2' align='center'>";
 for ($i=0; $i < count($value); $i++) {
 $HTMLOUT .= "<input name=\"submitme\" type=\"submit\" value=\"".$value[$i]."\" class=\"btn\" />";
 }
 $HTMLOUT .= "</td></tr></table></form>";

echo stdhead('Invites') . $HTMLOUT . stdfoot($stdfoot);
?>
