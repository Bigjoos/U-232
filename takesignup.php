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
require_once(INCL_DIR.'page_verify.php');
require_once(INCL_DIR.'password_functions.php');
global $CURUSER;
if(!$CURUSER){
get_template();
}
dbconn();

    if(!$TBDEV['openreg'])
    stderr('Sorry', 'Invite only - Signups are closed presently');
    
    $res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    
    if ($arr[0] >= $TBDEV['maxusers'])
      stderr($lang['takesignup_error'], $lang['takesignup_limit']);

    $lang = array_merge( load_language('global'), load_language('takesignup') );
    $newpage = new page_verify(); 
    $newpage->check('tesu');

    foreach( array('wantusername','wantpassword','passagain','email','captchaSelection','submitme','passhint','hintanswer') as $x )
    {
      if( !isset($_POST[ $x ]) )
      {
        stderr($lang['takesignup_user_error'], $lang['takesignup_form_data']);
      }
      
      ${$x} = $_POST[ $x ];
    }
    
    if ($submitme != 'X')
    die('You Missed, You plonker !');
  
    if(empty($captchaSelection) || $_SESSION['simpleCaptchaAnswer'] != $captchaSelection){
        header('Location: login.php');
        exit();
    }


function validusername($username)
  {
    global $lang;
    
    if ($username == "")
      return false;
    
    $namelength = strlen($username);
    
    if( ($namelength < 3) OR ($namelength > 32) )
    {
      stderr($lang['takesignup_user_error'], $lang['takesignup_username_length']);
    }
    // The following characters are allowed in user names
    $allowedchars = $lang['takesignup_allowed_chars'];
    
    for ($i = 0; $i < $namelength; ++$i)
    {
	  if (strpos($allowedchars, $username[$i]) === false)
	    return false;
    }
    
    return true;
  }

    if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($passhint) || empty($hintanswer))
    stderr($lang['takesignup_user_error'], $lang['takesignup_blank']);

    if(!blacklist($wantusername))
    stderr($lang['takesignup_user_error'],sprintf($lang['takesignup_badusername'],htmlspecialchars($wantusername)));

    if ($wantpassword != $passagain)
      stderr($lang['takesignup_user_error'], $lang['takesignup_nomatch']);

    if (strlen($wantpassword) < 6)
      stderr($lang['takesignup_user_error'], $lang['takesignup_pass_short']);

    if (strlen($wantpassword) > 40)
      stderr($lang['takesignup_user_error'], $lang['takesignup_pass_long']);

    if ($wantpassword == $wantusername)
      stderr($lang['takesignup_user_error'], $lang['takesignup_same']);

    if (!validemail($email))
      stderr($lang['takesignup_user_error'], $lang['takesignup_validemail']);

    if (!validusername($wantusername))
      stderr($lang['takesignup_user_error'], $lang['takesignup_invalidname']);

    // make sure user agrees to everything...
    if ($_POST["rulesverify"] != "yes" || $_POST["faqverify"] != "yes" || $_POST["ageverify"] != "yes")
      stderr($lang['takesignup_failed'], $lang['takesignup_qualify']);

    // check if email addy is already in use
    $a = (@mysql_fetch_row(@sql_query("select count(*) from users where email='$email'"))) or die(mysql_error());
    if ($a[0] != 0)
    stderr($lang['takesignup_user_error'], $lang['takesignup_email_used']);
    
   //=== check if ip addy is already in use
   $c = (@mysql_fetch_row(@sql_query("select count(*) from users where ip='" . $_SERVER['REMOTE_ADDR'] . "'"))) or die(mysql_error());
   if ($c[0] != 0)
   stderr("Error", "The ip " . $_SERVER['REMOTE_ADDR'] . " is already in use. We only allow one account per ip address.");

    // TIMEZONE STUFF
    if(isset($_POST["user_timezone"]) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone']))
    {
    $time_offset = sqlesc($_POST['user_timezone']);
    }
    else
    { $time_offset = isset($TBDEV['time_offset']) ? sqlesc($TBDEV['time_offset']) : '0'; }
    // have a stab at getting dst parameter?
    $dst_in_use = localtime(time() + ($time_offset * 3600), true);
    // TIMEZONE STUFF END

    $secret = mksecret();
    $wantpasshash = make_passhash( $secret, md5($wantpassword) );
    $editsecret = ( !$arr[0] ? "" : make_passhash_login_key() );
    $wanthintanswer = md5($hintanswer);
    
    $ret = sql_query("INSERT INTO users (username, passhash, secret, editsecret, passhint, hintanswer, email, status, ". (!$arr[0]?"class, ":"") ."added, last_access, time_offset, dst_in_use) VALUES (" .
		implode(",", array_map("sqlesc", array($wantusername, $wantpasshash, $secret, $editsecret, $passhint, $wanthintanswer, $email, (!$arr[0]?'confirmed':'pending')))).
		", ". (!$arr[0]?UC_SYSOP.", ":""). "". time() ." ,". time() ." , $time_offset, {$dst_in_use['tm_isdst']})");
    
    $message = "Welcome New {$TBDEV['site_name']} Member : - " . htmlspecialchars($wantusername) . "";
   
    if (!$ret) 
    {
      if (mysql_errno() == 1062)
        stderr($lang['takesignup_user_error'], $lang['takesignup_user_exists']);
      stderr($lang['takesignup_user_error'], $lang['takesignup_fatal_error']);
    }

    $id = mysql_insert_id();
    write_log("User account $id ($wantusername) was created");

    $psecret = $editsecret; 
    autoshout($message);
    $body = str_replace(array('<#SITENAME#>', '<#USEREMAIL#>', '<#IP_ADDRESS#>', '<#REG_LINK#>'),
                        array($TBDEV['site_name'], $email, $_SERVER['REMOTE_ADDR'], "{$TBDEV['baseurl']}/confirm.php?id=$id&secret=$psecret"),
                        $lang['takesignup_email_body']);

    if($arr[0])
      mail($email, "{$TBDEV['site_name']} {$lang['takesignup_confirm']}", $body, "{$lang['takesignup_from']} {$TBDEV['site_email']}");
    else
      logincookie($id, $wantpasshash);

header("Refresh: 0; url=ok.php?type=". (!$arr[0]?"sysop":("signup&email=" . urlencode($email))));
?>