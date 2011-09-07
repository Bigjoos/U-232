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
require_once INCL_DIR.'bbcode_functions.php';
require_once ROOT_DIR.'polls.php';

dbconn(true);

loggedinorreturn();


    $stdfoot = array(/** include js **/'js' => array('shout','java_klappe'));

    $lang = array_merge( load_language('global'), load_language('index') );
    //$lang = ;
    
    $HTMLOUT = '';
    
    ///////////09 Cached latest user
    if ($CURUSER) {
    $cache_newuser = "./cache/newuser.txt";
    $cache_newuser_life = 2 * 60 ; //2 min
    if (file_exists($cache_newuser) && is_array(unserialize(file_get_contents($cache_newuser))) && (time() - filemtime($cache_newuser)) < $cache_newuser_life)
    $arr = unserialize(@file_get_contents($cache_newuser));
    else {
    $r_new = sql_query("select id , username FROM users order by id desc limit 1 ") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($r_new);
    $handle = fopen($cache_newuser, "w+");
    fwrite($handle, serialize($arr));
    fclose($handle);
    }
    $new_user = "&nbsp;<a href=\"{$TBDEV['baseurl']}/userdetails.php?id={$arr["id"]}\">" . htmlspecialchars($arr["username"]) . "</a>\n";
    }
 
    //==Stats Begin
    $cache_stats = "./cache/stats.txt";
    $cache_stats_life = 5 * 60; // 5min
    if (file_exists($cache_stats) && is_array(unserialize(file_get_contents($cache_stats))) && (time() - filemtime($cache_stats)) < $cache_stats_life)
    $row = unserialize(@file_get_contents($cache_stats));
    else {
    $stats = sql_query("SELECT *, seeders + leechers AS peers, seeders / leechers AS ratio, unconnectables / (seeders + leechers) AS ratiounconn FROM stats WHERE id = '1' LIMIT 1") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_assoc($stats);
    $handle = fopen($cache_stats, "w+");
    fwrite($handle, serialize($row));
    fclose($handle);
    }

    $seeders = number_format($row['seeders']);
    $leechers = number_format($row['leechers']);
    $registered = number_format($row['regusers']);
    $unverified = number_format($row['unconusers']);
    $torrents = number_format($row['torrents']);
    $torrentstoday = number_format($row['torrentstoday']);
    $ratiounconn = $row['ratiounconn'];
    $unconnectables = $row['unconnectables'];
    $ratio = round(($row['ratio'] * 100));
    $peers = number_format($row['peers']);
    $numactive = number_format($row['numactive']);
    $donors = number_format($row['donors']);
    $forumposts = number_format($row['forumposts']);
    $forumtopics = number_format($row['forumtopics']);
    //==End
   
   //==09 Cached latest user
   $HTMLOUT .= "<div class='roundedCorners' style='text-align:center;width:80%;border:1px solid black;padding:5px;'><font class='small'>Welcome to our newest member, <b>$new_user</b>!</font></div><br />\n";
   
   // Announcement Code...
   $ann_subject = trim($CURUSER['curr_ann_subject']);
   $ann_body = trim($CURUSER['curr_ann_body']);
   if ((!empty($ann_subject)) AND (!empty($ann_body)))
   {
   $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_announce']}</span></div><br />
   <table width='100%' border='1' cellspacing='0' cellpadding='5'>
   <tr><td bgcolor='transparent'><b><font color='red'>Announcement&nbsp;: 
   ".htmlspecialchars($ann_subject)."</font></b></td></tr>
   <tr><td style='padding: 10px; background:lightgrey'>
   ".format_comment($ann_body)."
   <br /><hr /><br />
   Click <a href='{$TBDEV['baseurl']}/clear_announcement.php'>
   <i><b>here</b></i></a> to clear this announcement.</td></tr></table></div><br />\n";
   }
   
   // === shoutbox 09
   if ($CURUSER['show_shout'] === "yes") {
   $commandbutton = '';
   $refreshbutton = '';
   $smilebutton = '';
   $custombutton = '';
   if(get_smile() != '0')
   $custombutton .="<span style='float:right;'><a href=\"javascript:PopCustomSmiles('shbox','shbox_text')\">{$lang['index_shoutbox_csmilies']}</a></span>";
   if ($CURUSER['class'] >= UC_STAFF){
   $commandbutton = "<span style='float:right;'><a href=\"javascript:popUp('shoutbox_commands.php')\">{$lang['index_shoutbox_commands']}</a></span>\n";}
   $refreshbutton = "<span style='float:right;'><a href='shoutbox.php' target='sbox'>{$lang['index_shoutbox_refresh']}</a></span>\n";
   $smilebutton = "<span style='float:right;'><a href=\"javascript:PopMoreSmiles('shbox','shbox_text')\">{$lang['index_shoutbox_smilies']}</a></span>\n";
   $HTMLOUT .= "<form action='shoutbox.php' method='get' target='sbox' name='shbox' onsubmit='mysubmit()'>
   <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
	 <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_shout']}</span></div>
	 <br /><b>{$lang['index_shoutbox']}</b>&nbsp;[&nbsp;<a href='{$TBDEV['baseurl']}/shoutbox.php?show_shout=1&amp;show=no'><b>{$lang['index_shoutbox_close']}</b></a>&nbsp;]";
   if ($CURUSER['class'] >= UC_STAFF){
   $HTMLOUT .= "[&nbsp;<a href='{$TBDEV['baseurl']}/admin.php?action=shistory'><b>{$lang['index_shoutbox_history']}</b></a>&nbsp;]";
   }
   $HTMLOUT .= "<iframe src='{$TBDEV['baseurl']}/shoutbox.php' width='100%' height='200' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe>
   <br/>
   <br/>
	 <div align='center'>
   <b>{$lang['index_shoutbox_shout']}</b>
   <input type='text' maxlength='680' name='shbox_text' size='100' />
   <input class='button' type='submit' value='{$lang['index_shoutbox_send']}' />
   <input type='hidden' name='sent' value='yes' />
   <br />
	 <a href=\"javascript:SmileIT(':-)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/smile1.gif' alt='Smile' title='Smile' /></a> 
   <a href=\"javascript:SmileIT(':smile:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/smile2.gif' alt='Smiling' title='Smiling' /></a> 
   <a href=\"javascript:SmileIT(':-D','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/grin.gif' alt='Grin' title='Grin' /></a> 
   <a href=\"javascript:SmileIT(':lol:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/laugh.gif' alt='Laughing' title='Laughing' /></a> 
   <a href=\"javascript:SmileIT(':w00t:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /></a> 
   <a href=\"javascript:SmileIT(':blum:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blum.gif' alt='Rasp' title='Rasp' /></a> 
   <a href=\"javascript:SmileIT(';-)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/wink.gif' alt='Wink' title='Wink' /></a> 
   <a href=\"javascript:SmileIT(':devil:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/devil.gif' alt='Devil' title='Devil' /></a> 
   <a href=\"javascript:SmileIT(':yawn:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/yawn.gif' alt='Yawn' title='Yawn' /></a> 
   <a href=\"javascript:SmileIT(':-/','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/confused.gif' alt='Confused' title='Confused' /></a> 
   <a href=\"javascript:SmileIT(':o)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/clown.gif' alt='Clown' title='Clown' /></a> 
   <a href=\"javascript:SmileIT(':innocent:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/innocent.gif' alt='Innocent' title='innocent' /></a> 
   <a href=\"javascript:SmileIT(':whistle:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/whistle.gif' alt='Whistle' title='Whistle' /></a> 
   <a href=\"javascript:SmileIT(':unsure:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/unsure.gif' alt='Unsure' title='Unsure' /></a> 
   <a href=\"javascript:SmileIT(':blush:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blush.gif' alt='Blush' title='Blush' /></a> 
   <a href=\"javascript:SmileIT(':hmm:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/hmm.gif' alt='Hmm' title='Hmm' /></a> 
   <a href=\"javascript:SmileIT(':hmmm:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/hmmm.gif' alt='Hmmm' title='Hmmm' /></a> 
   <a href=\"javascript:SmileIT(':huh:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/huh.gif' alt='Huh' title='Huh' /></a> 
   <a href=\"javascript:SmileIT(':look:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/look.gif' alt='Look' title='Look' /></a> 
   <a href=\"javascript:SmileIT(':rolleyes:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/rolleyes.gif' alt='Roll Eyes' title='Roll Eyes' /></a> 
   <a href=\"javascript:SmileIT(':kiss:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/kiss.gif' alt='Kiss' title='Kiss' /></a> 
   <a href=\"javascript:SmileIT(':blink:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blink.gif' alt='Blink' title='Blink' /></a> 
   <a href=\"javascript:SmileIT(':baby:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/baby.gif' alt='Baby' title='Baby' /></a><br/>
	 </div>
	 <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;'>{$refreshbutton}</span></div>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;'>{$commandbutton}</span></div>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;'>{$smilebutton}</span></div>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;float:right'>{$custombutton}</span></div>
	 </div>
   </form><br />\n";
   }
   if ($CURUSER['show_shout'] === "no") {
   $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'><div style='background:transparent;height:25px;'><b>{$lang['index_shoutbox']}&nbsp;</b>[&nbsp;<a href='{$TBDEV['baseurl']}/shoutbox.php?show_shout=1&amp;show=yes'><b>{$lang['index_shoutbox_open']}&nbsp;]</b></a></div></div><br />";
   }
   //==end 09 shoutbox

    //==09 Cached News
    $news2  = '';
    $adminbutton = '';
    if ($CURUSER >= UC_STAFF)
    $adminbutton = "<span style='float:right;'><a href='admin.php?action=news'>News page</a></span>\n";
    $HTMLOUT.="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['news_title']}</span>{$adminbutton}</div>";
    $news_file = "{$TBDEV['cache']}/news.txt";
    $expire =  15 * 60; // 15min
    if (file_exists($news_file) && filemtime($news_file) > (time() - $expire)) {
    $news2 = unserialize(file_get_contents($news_file));
    } else {
    $prefix = 'ChangeMe';
    $res = sql_query("SELECT ".$prefix.".id, ".$prefix.".userid, ".$prefix.".added, ".$prefix.".title, ".$prefix.".body, ".$prefix.".sticky, u.username FROM news AS ".$prefix." LEFT JOIN users AS u ON u.id = ".$prefix.".userid WHERE ".$prefix.".added + ( 3600 *24 *45 ) > ".time()." ORDER BY sticky, ".$prefix.".added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($news1 = mysql_fetch_assoc($res) ) {
    $news2[] = $news1;
    }
    $output = serialize($news2);
    $fp = fopen($news_file,"w");
    fputs($fp, $output);
    fclose($fp);
    }
    $news_flag = 0;
    if ($news2)
    {
    foreach ($news2 as $array)
    {
    $button='';
    if ($CURUSER['class'] >= UC_STAFF)
    {
    $hash = md5('the@@saltto66??' . $array['id']. 'add' . '@##mu55y==');
    $button = "<br /><div style='float:right;'><a href='admin.php?action=news&amp;mode=edit&amp;newsid={$array['id']}&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'><img src='{$TBDEV['pic_base_url']}button_edit2.gif' border='0' alt=\"Edit news\"  title=\"Edit news\" /></a>&nbsp;<a href='admin.php?action=news&amp;mode=delete&amp;newsid={$array['id']}&amp;h=$hash&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'><img src='{$TBDEV['pic_base_url']}del.png' border='0' alt=\"Delete news\" title=\"Delete news\" /></a></div>";
    }
    $HTMLOUT .= "<div style='background:transparent;height:20px;'><span style='font-weight:bold;font-size:10pt;'>";
    if ($news_flag < 2) {
    $HTMLOUT .="<a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src='pic/plus.gif' id=\"pica".$array['id']."\" alt=\"Show/Hide\" />" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['title']}</a></span>{$button}</div>";
    $HTMLOUT .="<div id=\"ka".$array['id']."\" style=\"display:".($array["sticky"] == "yes" ? "" : "none").";margin-left:30px;margin-top:10px;\"> ".format_comment($array["body"],0)." </div><br /> ";
    $news_flag = ($news_flag + 1);
    }
    else {
    $HTMLOUT .="<a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src='pic/plus.gif' id=\"pica".$array['id']."\" alt=\"Show/Hide\" />" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['title']}</a></span>{$button}</div>";
    $HTMLOUT .="<div id=\"ka".$array['id']."\" style=\"display:".($array["sticky"] == "yes" ? "" : "none").";margin-left:30px;margin-top:10px;\"> ".format_comment($array["body"],0)." </div><br /> ";
    }
    $HTMLOUT .= "<div style='margin-top:10px;padding:5px;'></div><hr />\n";
    }
    $HTMLOUT .= "</div><br />\n";
    }
    if (empty($news2))
    $HTMLOUT .= "</div><br />\n";
    //==End
    
        //== Latest forum posts [set limit from config]
	      $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
	      <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['latestposts_title']}</span></div><br />";
        $page = 1;
        $num = 0;
        //== Latest posts query
        $topicres = sql_query("SELECT t.id, t.userid, t.anonymous AS top_anon, t.subject, t.locked, t.forumid, t.lastpost, t.sticky, t.views, t.forumid, f.minclassread, f.name ".
        ", (SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count ".
        ", p.userid AS puserid, p.anonymous AS pos_anon, p.added ".
        ", u.id AS uid, u.username ".
        ", u2.username AS u2_username ".
        "FROM topics AS t ".
        "LEFT JOIN forums AS f ON f.id = t.forumid ".
        "LEFT JOIN posts AS p ON p.id=(SELECT MAX(id) FROM posts WHERE topicid = t.id) ".
        "LEFT JOIN users AS u ON u.id=p.userid ".
        "LEFT JOIN users AS u2 ON u2.id=t.userid ".
        "WHERE f.minclassread <= ".$CURUSER['class']." ".
        "ORDER BY t.lastpost DESC LIMIT {$TBDEV['latest_posts_limit']}") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($topicres) > 0) {
        $HTMLOUT .= "<table width='100%' cellspacing='0' cellpadding='5'><tr>
        <td align='left' class='colhead'>{$lang['latestposts_topic_title']}</td>
        <td align='center' class='colhead'>{$lang['latestposts_replies']}</td>
        <td align='center' class='colhead'>{$lang['latestposts_views']}</td>
        <td align='center' class='colhead'>{$lang['latestposts_last_post']}</td></tr>";
	      while ($topicarr = mysql_fetch_assoc($topicres)) {

	      $topicid = 0+$topicarr['id'];
	      $topic_userid = 0+$topicarr['userid'];
 	      $perpage = $CURUSER['postsperpage'];;

 	      if (!$perpage)
 	      $perpage = 24;
 	      $posts = 0+$topicarr['p_count'];
 	      $replies = max(0, $posts - 1);
      	$first = ($page * $perpage) - $perpage + 1;
      	$last = $first + $perpage - 1;

 	      if ($last > $num)
 	      $last = $num;
 	      $pages = ceil($posts / $perpage);
 	      $menu = '';
 	      for ($i = 1; $i <= $pages; $i++) {
 	      if($i == 1 && $i != $pages){
 	      $menu .= "[ ";
 	      }
 	      if ($pages > 1){
 	      $menu .= "<a href='/forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>\n";
 	      }
 	      if ($i < $pages) {
 	      $menu .= "|\n";
 	      }
 	      if($i == $pages && $i > 1){
 	      $menu .= "]";
 	      }
 	      }

 	      $added = get_date($topicarr['added'],'',0,1);
	      if ($topicarr['pos_anon'] == 'yes') {
 	      if ($CURUSER['class'] < UC_MODERATOR && $CURUSER['id'] != $topicarr['puserid'])
 	      $username = "<i>Anonymous</i>";
 	      else
 	      $username = "<i>Anonymous</i><br />(".(!empty($topicarr['username']) ? "<a href='/userdetails.php?id=".(int)$topicarr['puserid']."'><b>".htmlspecialchars($topicarr['username'])."</b></a>" : "<i>Unknown[$topic_userid]</i>").")";
	      } else {
	      $username = (!empty($topicarr['username']) ? "<a href='/userdetails.php?id=".(int)$topicarr['puserid']."'><b>".htmlspecialchars($topicarr['username'])."</b></a>" : ($topic_userid == '0' ? "<i>System</i>" : "<i>Unknown[$topic_userid]</i>"));
	      }
	      if ($topicarr['top_anon'] == 'yes') {
 	      if ($CURUSER['class'] < UC_MODERATOR && $CURUSER['id'] != $topic_userid)
 	      $author = "<i>Anonymous</i>";
 	      else
        $author = "<i>Anonymous</i>(".(!empty($topicarr['u2_username']) ? "<a href='/userdetails.php?id=$topic_userid'><b>".htmlspecialchars($topicarr['u2_username'])."</b></a>" : "<i>Unknown[$topic_userid]</i>").")";
	      } else {
	      $author = (!empty($topicarr['u2_username']) ? "<a href='/userdetails.php?id=$topic_userid'><b>".htmlspecialchars($topicarr['u2_username'])."</b></a>" : ($topic_userid == '0' ? "<i>System</i>" : "<i>Unknown[$topic_userid]</i>"));
	      }
	      $staffimg = ($topicarr['minclassread'] >= UC_MODERATOR ? "<img src='".$TBDEV['pic_base_url']."staff.png' border='0' alt='Staff forum' title='Staff Forum' />" : '');
	      $stickyimg = ($topicarr['sticky'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."sticky.gif' border='0' alt='Sticky' title='Sticky Topic' />&nbsp;&nbsp;" : '');
	      $lockedimg = ($topicarr['locked'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."forumicons/locked.gif' border='0' alt='Locked' title='Locked Topic' />&nbsp;" : '');
 	      $subject = $lockedimg.$stickyimg."<a href='/forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#".(int)$topicarr['lastpost']."'><b>" . htmlspecialchars($topicarr['subject']) . "</b></a>&nbsp;&nbsp;$staffimg&nbsp;&nbsp;$menu<br /><font class='small'>in <a href='forums.php?action=viewforum&amp;forumid=".(int)$topicarr['forumid']."'>".htmlspecialchars($topicarr['name'])."</a>&nbsp;by&nbsp;$author&nbsp;&nbsp;($added)</font>";

        $HTMLOUT .="<tr><td>{$subject}</td><td align='center'>{$replies}</td><td align='center'>".number_format($topicarr['views'])."</td><td align='center'>{$username}</td></tr>";
        }
        $HTMLOUT .= "</table></div><br />\n";
        } else {
        //== If there are no posts...
        $HTMLOUT .= "<div class='roundedCorners' style='text-align:center;border:1px solid black;background:transparent;'><span style='font-weight:bold;font-size:10pt;'>{$lang['latestposts_no_posts']}</span></div></div><br />";
        }
        //== End latest forum posts
    
        //== 09 stats
        $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
        <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_stats_title']}</span></div><br />
        <table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td align='center'>
        <table class='main' border='1' cellspacing='0' cellpadding='5'>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_regged']}</td><td align='right'>{$registered}/{$TBDEV['maxusers']}</td>
	      <td class='rowhead'>{$lang['index_stats_online']}</td><td align='right'>{$numactive}</td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_uncon']}</td><td align='right'>{$unverified}</td>
	      <td class='rowhead'>{$lang['index_stats_donor']}</td><td align='right'>{$donors}</td>
        </tr>
        <tr>
	      <td colspan='4'> </td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_topics']}</td><td align='right'>{$forumtopics}</td>
	      <td class='rowhead'>{$lang['index_stats_torrents']}</td><td align='right'>{$torrents}</td>
        </tr>
        <tr>
        <td class='rowhead'>{$lang['index_stats_posts']}</td><td align='right'>{$forumposts}</td>
	      <td class='rowhead'>{$lang['index_stats_newtor']}</td><td align='right'>{$torrentstoday}</td>
        </tr>
        <tr>
        <td colspan='4'> </td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_peers']}</td><td align='right'>{$peers}</td>
	      <td class='rowhead'>{$lang['index_stats_unconpeer']}</td><td align='right'>{$unconnectables}</td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_seeders']}</td><td align='right'>{$seeders}</td>
	      <td class='rowhead' align='right'><b>{$lang['index_stats_unconratio']}</b></td><td align='right'><b>".round($ratiounconn * 100)."</b></td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_leechers']}</td><td align='right'>{$leechers}</td>
	      <td class='rowhead'>{$lang['index_stats_slratio']}</td><td align='right'>{$ratio}</td>
        </tr></table></td></tr></table></div><br />";
        //==End 09 stats
        
      //== 09 Active users on Index
      $active3 ="";
      $file = "./cache/active.txt";
      $expire = 30; // 30 seconds
      if (file_exists($file) && filemtime($file) > (time() - $expire)) {
      $active3 = unserialize(file_get_contents($file));
      } else {
      $dt = sqlesc(time() - 180);
      $active1 = sql_query("SELECT id, username, class, warned, chatpost, pirate, king, leechwarn, enabled, donor, added FROM users WHERE last_access >= $dt ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);
       while ($active2 = mysql_fetch_assoc($active1)) {
      $active3[] = $active2;
      }
      $OUTPUT = serialize($active3);
      $fp = fopen($file, "w");
      fputs($fp, $OUTPUT);
      fclose($fp);
      } // end else
      $activeusers = '';
      if (is_array($active3))
      foreach ($active3 as $arr) {
      if ($activeusers) $activeusers .= ",\n";
      $activeusers .= format_username($arr); 
      }

      if (!$activeusers)
      $activeusers = "{$lang['index_noactive']}";
      $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
      <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_active']}</span></div><br />
	    <table border='1' cellpadding='10' cellspacing='0' width='100%'>
		  <tr class='table'>
		  <td class='text'>{$activeusers}&nbsp;</td>   
      </tr></table></div><br />\n";
      //== End
     
      //== Cached Last24 by putyn
      function last24hours() {
	    global $TBDEV,$CURUSER;
	    $_last24 = (file_exists($TBDEV['last24cache']) ? unserialize(file_get_contents($TBDEV['last24cache'])) : array());
	    $_last24record = (file_exists($TBDEV['last24record']) ? unserialize(file_get_contents($TBDEV['last24record'])) : array('num'=>0,'date'=>0));
	    if(!isset($_last24[$CURUSER['id']]) || empty($_last24[$CURUSER['id']])) {
		  $_last24[$CURUSER['id']] = array($CURUSER['username'],$CURUSER['class']);
		  $_newcount = count($_last24);
		  if(isset($_last24record['num']) && $_last24record['num']<$_newcount) {
			$_last24record['num'] = $_newcount;
			$_last24record['date'] = time();
			file_put_contents($TBDEV['last24record'],serialize($_last24record));
		  }
		  file_put_contents($TBDEV['last24cache'],serialize($_last24));
	    }
      }
      //== Cached Last24 by putyn
      function las24hours_display() {
	    global $TBDEV, $lang, $CURUSER;
	    $_last24 = (file_exists($TBDEV['last24cache']) ? unserialize(file_get_contents($TBDEV['last24cache'])) : array());
	    $_last24record = (file_exists($TBDEV['last24record']) ? unserialize(file_get_contents($TBDEV['last24record'])) : array('num'=>0,'date'=>0));
	    $txt = '';
	    if(!is_array($_last24))
		  $txt = 'No 24hour record';
	    else {
		  $txt .= '<div class=\'roundedCorners\' style=\'text-align:left;width:80%;border:1px solid black;padding:5px;\'>
      <div style=\'background:transparent;height:25px;\'><span style=\'font-weight:bold;font-size:12pt;\'>'.$lang['index_active24'].'</span></div><br />
	    <table border=\'1\' cellpadding=\'10\' cellspacing=\'0\' width=\'100%\'>
		  <tr class=\'table\'>
		  <td class=\'text\'><span>';
		  $c = count($_last24);
		  $i =0;
		  foreach($_last24 as $id=>$username){
			$txt .= '<a href=\'./userdetails.php?id='.$id.'\'><font color=\'#'.get_user_class_color($username[1]).'\'><b>'.$username[0].'</b></font></a>'.(($c-1) == $i ? '' : ',')."\n";
			$i++;
		  }
	    $txt .= '</span></td></tr>';
		  $txt .= '
		  <tr class=\'table\'>
      <td class=\'text\'><span>'.$lang['index_most24'].' '.$_last24record['num'].' ' .$lang['index_member24']. ' : '.get_date($_last24record['date'],'DATE').' </span></td>
      </tr></table></div><br />';
	    }
	    return $txt;
      }
      last24hours();
      $HTMLOUT .= las24hours_display();
      //== End last24 by putyn
      
      //== Poll
      $HTMLOUT .= parse_poll();
    
    //== 09 Cached Donation progress - MelvinMeow
    $cache_funds = "./cache/funds.txt";
    $cache_funds_life = 1 * 60 * 60; // Hourly
    if (file_exists($cache_funds) && is_array(unserialize(file_get_contents($cache_funds))) && (time() - filemtime($cache_funds)) < $cache_funds_life)
    $row = unserialize(@file_get_contents($cache_funds));
    else {
    $funds = sql_query("SELECT sum(cash) as total_funds FROM funds") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_assoc($funds);
    $handle = fopen($cache_funds, "w+");
    fwrite($handle, serialize($row));
    fclose($handle);
    }
    $funds_so_far = $row["total_funds"];
    $totalneeded = 100;    //=== set this to your monthly wanted amount
    $funds_difference = $totalneeded - $funds_so_far;
    $Progress_so_far = number_format($funds_so_far / $totalneeded * 100, 1);
    if($Progress_so_far >= 100)
    $Progress_so_far = 100;

    $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_donations']}</span></div><br /><div align='center'><a href='{$TBDEV['baseurl']}/donate.php'>
    <img border='0' src='{$TBDEV['pic_base_url']}makedonation.gif' alt='Donate' title='Donate'  /></a><br /><br />
    <table width='140' style='height: 20%;' border='2'><tr>
    <td bgcolor='transparent' align='center' valign='middle' width='$Progress_so_far%'>$Progress_so_far%</td><td bgcolor='grey' align='center' valign='middle'></td></tr></table></div></div><br />";
    //end
    /*
    //== Windows Server Load
    $HTMLOUT .="
    <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_serverload']}</span></div>
    <br />
    <table width='100%' border='1' cellspacing='0' cellpadding='1'>
		<tr><td align='center'>
		<table class='main' border='0' width='402'>
    <tr><td style='padding: 0px; background-image: url({$TBDEV['pic_base_url']}loadbarbg.gif); background-repeat: repeat-x'>";
    $perc = get_server_load();
    $percent = min(100, $perc);
    if ($percent <= 70) $pic = "loadbargreen.gif";
    elseif ($percent <= 90) $pic = "loadbaryellow.gif";
    else $pic = "loadbarred.gif";
    $width = $percent * 4;
    $HTMLOUT .="<img height='15' width='$width' src=\"{$TBDEV['pic_base_url']}{$pic}\" alt='$percent%' /><br /></td></tr></table></td></tr></table></div><br />";
    //==End
    */
    /*
    //== Server Load linux
    $HTMLOUT .="
    <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_serverload']}</span></div>
    <br />
    <table width='100%' border='1' cellspacing='0' cellpadding='1'>
			<tr><td align='center'>
		    <table class='main' border='0' width='402'>
    			<tr><td style='padding: 0px; background-image: url({$TBDEV['pic_base_url']}loadbarbg.gif); background-repeat: repeat-x'>";
    $percent = min(100, round(exec('ps ax | grep -c apache') / 256 * 100));
    if ($percent <= 70) $pic = "loadbargreen.gif";
    elseif ($percent <= 90) $pic = "loadbaryellow.gif";
    else $pic = "loadbarred.gif";
    $width = $percent * 4;
    $HTMLOUT .="<img height='15' width='$width' src=\"{$TBDEV['pic_base_url']}{$pic}\" alt='$percent%' /><br /></td></tr></table></td></tr></table></div><br />";
    //==End
    */
    //== Disclaimer
    $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_disclaimer']}</span></div><br />";
    $HTMLOUT .= sprintf("<p><font class='small'>{$lang['foot_disclaimer']}</font></p>", $TBDEV['site_name']);
    $HTMLOUT .= "</div>";

print stdhead('Home') . $HTMLOUT . stdfoot($stdfoot);
?>