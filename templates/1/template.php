<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
function stdhead($title = "", $msgalert = true, $stdhead = false) {
    global $CURUSER, $TBDEV, $lang, $free, $_NO_COMPRESS, $mc;
    if (!$TBDEV['site_online'])
      die("Site is down for maintenance, please check back again later... thanks<br />");
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Language content="en-us"');
    if ($title == "")
        $title = $TBDEV['site_name'] .(isset($_GET['tbv'])?" (".TBVERSION.")":'');
    else
        $title = $TBDEV['site_name'].(isset($_GET['tbv'])?" (".TBVERSION.")":''). " :: " . htmlspecialchars($title);        
    if ($CURUSER)
    {
    $TBDEV['stylesheet'] = isset($CURUSER['stylesheet']) ? "{$CURUSER['stylesheet']}.css" : $TBDEV['stylesheet'];
    }
    if ($TBDEV['msg_alert'] && $msgalert && $CURUSER)
    {
      $res = sql_query("SELECT count(id) FROM messages WHERE receiver=" . $CURUSER["id"] . " && unread='yes'") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_row($res);
      $unread = $arr[0];
    }
    /** ZZZZZZZZZZZZZZZZZZZZZZZZZZip it! **/
    if (!isset($_NO_COMPRESS))
    if (!ob_start('ob_gzhandler'))
    ob_start();
    /** include js files needed only for the page being used by pdq **/
    $js_incl = '<!-- javascript goes here or in footer -->';
    if ($stdhead['js'] != false) {
    foreach ($stdhead['js'] as $JS)
    $js_incl .= "<script type='text/javascript' src='".$TBDEV['baseurl']."/scripts/".$JS.".js'></script>";
    }
	  $htmlout="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
 	  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
 	  <html xmlns='http://www.w3.org/1999/xhtml'>
 	  <head>
 		<meta name='generator' content='TBDev.net' />
 		<meta http-equiv='Content-Language' content='en-us' />
 		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
 		<meta name='MSSmartTagsPreventParsing' content='TRUE' />
    <title>{$title}</title>
	  <link rel='stylesheet' href='templates/1/itunes.css' type='text/css' />
   	<script type='text/javascript' src='./scripts/jquery.js'></script>
		<script type='text/javascript' src='image-resize/core-resize.js'></script>
    <link type='text/css' rel='stylesheet' href='image-resize/resize.css'  />
		<link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='./rss.php?passkey={$CURUSER['passkey']}' />
		".$js_incl."
		</head> 
    <body>
    <div class='itunes'>
	  <table class='maincont'>
	  <tr><td class='haus' colspan='2'>
	  <div class='play'>
	  <div class='play1'></div>
	  <div class='play3'></div>
	  <div class='play2'>{$TBDEV['site_name']}</div>
	  </div>
	  </td></tr>";
	  $htmlout.=StatusBar();
    
    $htmlout .="
	  <tr valign='top'><td class='hlid'>";
	  if ($CURUSER) 
    {
	  $htmlout .="<ul class='nav'>
	  <li><a href='index.php'>{$lang['gl_home']}</a></li>
	  <li><a href='browse.php'>{$lang['gl_browse']}</a></li>
	  <li><a href='upload.php'>{$lang['gl_upload']}</a></li>
	  <li><a href='getrss.php'>{$lang['gl_getrss']}</a></li>
	  ".(isset($CURUSER)?"<li><a href='usercp.php'>{$lang['gl_profile']}</a></li>":"<li><a href='login.php'>{$lang['gl_login']}</a></li><li><a href='signup.php'>{$lang['gl_signup']}</a></li>")."
	  <li><a href='forums.php'>{$lang['gl_forums']}</a></li>
	  <li><a href='staff.php'>{$lang['gl_staff']}</a></li>
	  <li><a href='contactstaff.php'>{$lang['gl_help']}</a></li>
	  <li><a href='donate.php'>{$lang['gl_donate']}</a></li>
	  <li><a href='topten.php'>{$lang['gl_top_10']}</a></li>
	  <li><a href='catalogue.php'>{$lang['gl_catalogue']}</a></li>
	  <li><a href='viewrequests.php'>{$lang['gl_request']}</a></li>
	  ".(isset($CURUSER) && $CURUSER['class']>=UC_MODERATOR ? "<li><a href='staffpanel.php'>{$lang['gl_admin']}</a></li>":"")."
	  </ul>";
	  }
	  $htmlout .="</td><td class='sida' align='center'>";
	  
	//=== free addon start
    if ($CURUSER) { 
    if (isset($free))
    {
    foreach ($free as $fl)
    {
        switch ($fl['modifier'])
        {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            default:
                $mode = 0;
        }
        
     $htmlout .= ($fl['modifier'] != 0 && $fl['expires'] > TIME_NOW ? '<table width="50%"><tr>
     <td class="colhead" colspan="3" align="center">'.$fl['title'].'<br />'.$mode.'</td>
   </tr>
   <tr>
     <td width="42" align="center">
     <img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" /></td>
     <td align="center">'.$fl['message'].' set by '.$fl['setby'].'<br />'.($fl['expires'] != 1 ? 
'Until '.get_date($fl['expires'], 'DATE').' ('.mkprettytime($fl['expires'] - time()).' to go)' : '').'</td>
     <td width="42" align="center">
     <img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" /></td>
</tr></table>
<br />' : '');
}
}
}
//=== free addon end
   //==Temp demotion
   if ($CURUSER['override_class'] != 255 && $CURUSER) // Second condition needed so that this box isn't displayed for non members/logged out members.
   {
   $htmlout .= "<table border='0' cellspacing='0' cellpadding='10' bgcolor='green'>
   <tr><td style='padding: 10px; background: green'><b><a href='./restoreclass.php'>
   <font color='white'>{$lang['gl_tempdemotion']}</font></a></b></td></tr></table><br />\n";
   }
   //==End
    if ($TBDEV['msg_alert'] && isset($unread) && !empty($unread))
    {
      $htmlout .= "<table border='0' cellspacing='0' cellpadding='10' bgcolor='red'>
                  <tr><td style='padding: 10px; background: red'>\n
                  <b><a href='./messages.php'><font color='white'>".sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? "s" : "") . "!</font></a></b>
                  </td></tr></table><br />\n";
    }
   //==Big red staffmess thingy box:
   if($TBDEV['staffmsg_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
		$num = mysql_result(sql_query('SELECT count(id) FROM staffmessages WHERE answeredby = 0'),0);
		if($num > 0)
		$htmlout .= "<table border='0' cellspacing='0' cellpadding='10'>
                  <tr><td style='padding: 10px; background: #ccc'>\n
                  <b><a href='staffbox.php'>".sprintf($lang['gl_staffmsg_alert'], $num). "!</a></b>
                  </td></tr></table><br />";
	}
  //==End
  //==Big red report thingy box:
   if($TBDEV['report_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
		$num = mysql_result(sql_query('SELECT COUNT(id) FROM reports WHERE delt_with = 0'),0);
		if($num > 0)
		$htmlout .= "<table border='0' cellspacing='0' cellpadding='10'>
                  <tr><td style='padding: 10px; background: #ccc'>\n
                  <b><a href='admin.php?action=reports'>".sprintf($lang['gl_reportmsg_alert'], $num). "!</a></b>
                  </td></tr></table><br />";
	}
	//==End
	//Big red uploadapp thingy box:
   if($TBDEV['uploadapp_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
		$num = mysql_result(sql_query('SELECT count(id) FROM uploadapp WHERE status = "pending"'),0);
		if($num > 0)
		$htmlout .= "<table border='0' cellspacing='0' cellpadding='10'>
                  <tr><td style='padding: 10px; background: #ccc'>\n
                  <b><a href='uploadapps.php'>".sprintf($lang['gl_uploadapp_alert'], $num). "!</a></b>
                  </td></tr></table><br />";
	}
	//==End
   // happy hour
    if ( $CURUSER ) {
    if ( happyHour( "check" ) ) {
        $htmlout.="<table border='0' cellspacing='0' cellpadding='10'  ><tr><td align='center' style=\"background:#CCCCCC;color:#222222; padding:10px\">\n
        <b>Hey its now happy hour ! " . ( ( happyCheck( "check" ) == 255 ) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=" . happyCheck( "check" ) . "\">this category</a> is free this happy hour" ) . "<br /><font color='red'>" . happyHour( "time" ) . " </font> remaining from this happy hour!</b>";
       $htmlout.="</td></tr></table><br />\n";
    }
   }
   
   //==pdq crazyhour
	 if (isset($CURUSER)) {
   $transfer_filename  = $TBDEV['cache'].'/transfer_crazyhour.txt';
   $crazyhour_filename = $TBDEV['cache'].'/crazy_hour.txt';
   $crazyhour_cache = fopen($crazyhour_filename,'r+');
   $crazyhour_var = fread($crazyhour_cache, filesize($TBDEV['cache'].'/crazy_hour.txt'));
   fclose($crazyhour_cache);
   $cimg = '<img src=\''.$TBDEV["pic_base_url"].'cat_free.gif\' alt=\'FREE!\' />';
   if ($crazyhour_var >= TIME_NOW && $crazyhour_var < TIME_NOW + 3600) { // is crazyhour
       $htmlout .="<table width='50%'><tr><td class='colhead' colspan='3' align='center'>
       ".$TBDEV['crazy_title']." Ends in ".mkprettytime($crazyhour_var - TIME_NOW)."</td></tr>
       <tr><td width='42px' align='center' valign='middle'>". $cimg."</td>
       <td><div align='center'>". $TBDEV['crazy_message']."</div></td>
       <td width='42px' align='center' valign='middle'>".$cimg."</td></tr></table><br />";
        if (is_file($transfer_filename))
            unlink($transfer_filename);
    }
    elseif ($crazyhour_var < TIME_NOW + 3600 && !is_file($transfer_filename)) { //== crazyhour over
        $transfer_file_created = fopen($transfer_filename, 'w') or die('no perms?');
        fclose($transfer_file_created);
        $crazyhour['crazyhour_new']       = mktime(23, 59, 59, date('m'), date('d'), date('y'));
        $crazyhour['crazyhour']['var']    = mt_rand($crazyhour['crazyhour_new'], ($crazyhour['crazyhour_new'] + 86400));
        $fp = fopen($crazyhour_filename, 'w');
        fwrite($fp, $crazyhour['crazyhour']['var']);
        fclose($fp); 
        write_log('Next Crazyhour is at '.date('F j, g:i a T', $crazyhour['crazyhour'] ['var'])); 
        $htmlout .="<table cellpadding='3'><tr><td class='colhead' colspan='3' align='center'>"." Crazyhour will be ".get_date($crazyhour['crazyhour']['var'], '')."  ".mkprettytime($crazyhour['crazyhour']['var'] - TIME_NOW)." remaining till Crazyhour</td></tr></table><br />";
        }
        else // make date look prettier with countdown etc even :]
        $htmlout .="<table cellpadding='3'><tr><td class='colhead' colspan='3' align='center'>"." Crazyhour will be ".get_date($crazyhour_var, '')."  ".mkprettytime($crazyhour_var - TIME_NOW)." remaining till Crazyhour</td></tr></table><br />";
        }
	      // crazyhour end
	return $htmlout;
}
     
    function stdfoot($stdfoot = false) {
    global $querytime, $CURUSER, $TBDEV, $q, $queries, $query_stat;
    $queries = (!empty($queries) ? $queries : 0);
    $q['debug']       = array(1); //==Add ids
    $q['seconds']     = (microtime(true) - $q['start']);
    $q['phptime']     = $q['seconds'] - $q['querytime'];
    $q['percentphp']  = number_format(($q['phptime'] / $q['seconds']) * 100, 2);
    $q['percentsql']  = number_format(($q['querytime'] / $q['seconds']) * 100, 2);
    $q['howmany']     = ($queries != 1 ? 's ' : ' ');
    $q['serverkillers'] = $queries > 6 ? '<br />'.($queries/2).' Server killers ran to show you this page :) ! =[' : '=]';
    
    $htmlfoot='';
    if(isset($CURUSER)){
    $htmlfoot = "<br /><div class='roundedCorners' style=\"text-align:center;width:80%;border:1px solid black;padding:5px;\">
    <div style=\"text-align:left;background:transparent;height:25px;\"><span style=\"font-weight:bold;font-size:12pt;\">Query stats</span></div>The {$TBDEV['site_name']}
    Server killers generated this page in ".(round($q['seconds'], 4))." seconds and then took a nap.<br /> 
    They had to raid the server ".$queries." time'".$q['howmany']."using&nbsp;:&nbsp;<b>".$q['percentphp']."</b>&nbsp;&#37;&nbsp;php&nbsp;&#38;&nbsp;<b>".$q['percentsql']."</b>&nbsp;&#37;&nbsp;sql ".$q['serverkillers'].".<br /><br /></div>";
    
    if (SQL_DEBUG && in_array($CURUSER['id'], $q['debug'])) { 
    if ($q['query_stat']) {
    $htmlfoot .= "<br />
	  <div class='roundedCorners' style=\"text-align:left;width:80%;border:1px solid black;padding:5px;\">
    <div style=\"background:transparent;height:25px;\"><span style=\"font-weight:bold;font-size:12pt;\">Querys</span></div>
	  <table width=\"100%\" align=\"center\" cellspacing=\"5\" cellpadding=\"5\" border=\"0\">
		<tr>
		<td class=\"colhead\" width=\"5%\"  align=\"center\">ID</td>
		<td class=\"colhead\" width=\"10%\" align=\"center\">Query Time</td>
		<td class=\"colhead\" width=\"85%\" align=\"left\">Query String</td>
		</tr>";
    foreach ($q['query_stat'] as $key => $value) {
    $htmlfoot  .= "<tr>
		<td align=\"center\">".($key + 1)."</td>
		<td align=\"center\"><b>". ($value['seconds'] > 0.01 ?
		"<font color=\"red\" title=\"You should optimize this query.\">".
    $value['seconds']."</font>" : "<font color=\"green\" title=\"Query good.\">".
	  $value['seconds']."</font>")."</b></td>
		<td align=\"left\">".htmlspecialchars($value['query'])."<br /></td>
		</tr>";	   		   
    }
    $htmlfoot .='</table></div><br />';
    }
    }
    }
    $htmlfoot .="<p align='center'>
    <!-- It's not accepted that you remove any credit here... --> 
    <a href='http://tbdev.net'><img src='./pic/tbdev_power.png' alt='TBDEV'/></a>
    <a href='http://validator.w3.org'><img src='./pic/xhtml_valid.png' alt='Xhtml valid'/></a>&nbsp;&nbsp;<a href='http://jigsaw.w3.org/css-validator/check/'><img src='./pic/css_valid.png' alt='Xhtml valid' /></a></p>
    </td></tr><tr><td class='fotur' colspan='2'>Design by TerraNova</td></tr></table>";
    /** query stats **/
    /** include js files needed only for the page being used by pdq **/
    $htmlfoot .= '<!-- javascript goes here -->';
    if ($stdfoot['js'] != false) {
    foreach ($stdfoot['js'] as $JS)
    $htmlfoot .= '<script type="text/javascript" src="'.$TBDEV['baseurl'].'/scripts/'.$JS.'.js"></script>';
    }
    $htmlfoot .= "</div></body></html>\n";
    return $htmlfoot;
    }

    function stdmsg($heading, $text)
    {
    $htmlout = "<table class='main' width='750' border='0' cellpadding='0' cellspacing='0'>
    <tr><td class='embedded'>\n";
    if ($heading)
    $htmlout .= "<h2>$heading</h2>\n";
    $htmlout .= "<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>\n";
    $htmlout .= "{$text}</td></tr></table></td></tr></table>\n";
    return $htmlout;
    }

function StatusBar() {
	global $CURUSER, $TBDEV, $lang, $rep_is_on, $mc;
	if (!$CURUSER)
		return "";
	if(!$TBDEV['coins']){
	$upped = mksize($CURUSER['uploaded']);
	$downed = mksize($CURUSER['downloaded']);
  $ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded'] / $CURUSER['downloaded'] : 0;
  $ratio = number_format($ratio, 2);
  $color = get_ratio_color($ratio);
  if ($color)
  $ratio = "<font color='$color'>$ratio</font>";  
  }
  $res1 = @sql_query("SELECT count(id) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND unread='yes'") or sqlerr(__LINE__,__FILE__);
	$arr1 = mysql_fetch_row($res1);
	$unread = $arr1[0];
	$inbox = ($unread == 1 ? "$unread&nbsp;{$lang['gl_msg_singular']}" : "$unread&nbsp;{$lang['gl_msg_plural']}");
	$res2 = @sql_query("SELECT seeder, count(*) AS pCount FROM peers WHERE userid=".$CURUSER['id']." GROUP BY seeder") or sqlerr(__LINE__,__FILE__);
	$seedleech = array('yes' => '0', 'no' => '0');
	while( $row = mysql_fetch_assoc($res2) ) {
		if($row['seeder'] == 'yes')
			$seedleech['yes'] = $row['pCount'];
		else
			$seedleech['no'] = $row['pCount'];
	}
  /////////////// REP SYSTEM /////////////
  $member_reputation = get_reputation($CURUSER);
   ////////////// REP SYSTEM END //////////
	  if ($CURUSER['class'] < UC_VIP && $TBDEV['max_slots']) {
    $ratioq = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 1);
    if ($ratioq < 0.95) {
	  switch (true) {
		case ($ratioq < 0.5):
		$max = 2;
		break;
		case ($ratioq < 0.65):
		$max = 3;
		break;
		case ($ratioq < 0.8):
		$max = 5;
		break;
		case ($ratioq < 0.95):
		$max = 10;
		break;
		default:
	  $max = 10;
	  }
    }
    else {
    switch ($CURUSER['class']) {
		case UC_USER:
		$max = 20;
		break;
		case UC_POWER_USER:
		$max = 30;
		break;
		default:
	  $max = 99;
	  }	
    }   
    }
    else
    $max = 999;
	  $usrclass="";
    if ($CURUSER['override_class'] != 255) $usrclass = "&nbsp;<b>(".get_user_class_name($CURUSER['class']).")</b>&nbsp;";
    elseif($CURUSER['class'] >= UC_MODERATOR) 
    $usrclass = "&nbsp;<a href='{$TBDEV['baseurl']}/setclass.php'><b>(".get_user_class_name($CURUSER['class']).")</b></a>&nbsp;";
	  $StatusBar = '';
		$StatusBar = "<tr>".
		"<td colspan='2' style='padding: 2px;'>".
		"<div id='statusbar'>".
		"<div style='float:left;color:black;'>{$lang['gl_msg_welcome']}, 
		".format_username($CURUSER)."&nbsp;{$usrclass} 
		"."&nbsp;$member_reputation".
		"&nbsp;|&nbsp;Invites:&nbsp;<a href='{$TBDEV['baseurl']}/invite.php'>{$CURUSER['invites']}</a>&nbsp;|"."
		&nbsp;Bonus:&nbsp;<a href='{$TBDEV['baseurl']}/mybonus.php'>{$CURUSER['seedbonus']}</a>&nbsp;|&nbsp;<a href='logout.php'>[{$lang['gl_logout']}]</a>";
		if(!$TBDEV['coins'])
		$StatusBar .= "
		<br />{$lang['gl_ratio']}:$ratio".
		"&nbsp;|&nbsp;{$lang['gl_uploaded']}:$upped".
		"&nbsp;|&nbsp;{$lang['gl_downloaded']}:$downed";
		if($TBDEV['coins'])$StatusBar .= "&nbsp;|&nbsp;{$lang['gl_coins']}:<a href='{$TBDEV['baseurl']}/coins.php'>{$CURUSER['coins']}</a>&nbsp;&nbsp;";
		$StatusBar.="&nbsp;|&nbsp;{$lang['gl_act_torrents']}:&nbsp;<img alt='{$lang['gl_seed_torrents']}' title='{$lang['gl_seed_torrents']}' src='{$TBDEV['pic_base_url']}up.png' />&nbsp;{$seedleech['yes']}".
		"&nbsp;&nbsp;<img alt='{$lang['gl_leech_torrents']}' title='{$lang['gl_leech_torrents']}' src='{$TBDEV['pic_base_url']}dl.png' />&nbsp;".($TBDEV['max_slots'] ? "<a title='I have ".$max." Download Slots'>{$seedleech['no']}/".$max."</a>" : $seedleech['no'])."</div>".
		"<div><p style='text-align:right;'>".date(DATE_RFC822)."<br />".
    "<a href='./messages.php'>$inbox</a></p></div>".
    "</div></td></tr>";
	return $StatusBar;
}

?>