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
|   $Date$
|   $Revision$
|   $Author$ x0r,Bigjoos
|   $URL$
+------------------------------------------------
*/
require_once(INCL_DIR.'bittorrent.php');

function deadtime() {
    global $TBDEV;
    return time() - floor($TBDEV['announce_interval'] * 1.3);
}

function docleanup() {
	global $TBDEV, $queries, $C_queries;
   set_time_limit(1200);
   $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
   while ($row = mysqli_fetch_array($result)) {
   if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
   $sql = "kill " . $row["Id"] . "";
   sql_query($sql) or sqlerr(__FILE__, __LINE__);
   }
   }
   ignore_user_abort(1);

	do {
		$res = sql_query("SELECT id FROM torrents");
		$ar = array();
		while ($row = mysqli_fetch_array($res, MYSQLI_NUM)) {
			$id = $row[0];
			$ar[$id] = 1;
		}

		if (!count($ar))
			break;

		$dp = opendir($TBDEV['torrent_dir']);
		if (!$dp)
			break;

		$ar2 = array();
		while (($file = readdir($dp)) !== false) {
			if (!preg_match('/^(\d+)\.torrent$/', $file, $m))
				continue;
			$id = $m[1];
			$ar2[$id] = 1;
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$ff = $TBDEV['torrent_dir'] . "/$file";
			unlink($ff);
		}
		closedir($dp);

		if (!count($ar2))
			break;

		$delids = array();
		foreach (array_keys($ar) as $k) {
			if (isset($ar2[$k]) && $ar2[$k])
				continue;
			$delids[] = $k;
			unset($ar[$k]);
		}
		if (count($delids))
			sql_query("DELETE FROM torrents WHERE id IN (" . join(",", $delids) . ")");

		$res = sql_query("SELECT torrent FROM peers GROUP BY torrent");
		$delids = array();
		while ($row = mysqli_fetch_array($res, MYSQLI_NUM)) {
			$id = $row[0];
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			sql_query("DELETE FROM peers WHERE torrent IN (" . join(",", $delids) . ")");

		$res = sql_query("SELECT torrent FROM files GROUP BY torrent");
		$delids = array();
		while ($row = mysqli_fetch_array($res, MYSQLI_NUM)) {
			$id = $row[0];
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			sql_query("DELETE FROM files WHERE torrent IN (" . join(",", $delids) . ")");
	} while (0);

	$deadtime = deadtime();
	sql_query("DELETE FROM peers WHERE last_action < $deadtime");

	$deadtime -= $TBDEV['max_dead_torrent_time'];
	sql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND last_action < $deadtime");

	$deadtime = time() - $TBDEV['signup_timeout'];
	sql_query("DELETE FROM users WHERE status = 'pending' AND added < $deadtime AND last_login < $deadtime AND last_access < $deadtime");

	/** sync torrent counts - pdq **/
  $tsql = 'SELECT t.id, t.seeders, (
  SELECT COUNT(*)
  FROM peers
  WHERE torrent = t.id AND seeder = "yes"
) AS seeders_num,
t.leechers, (
  SELECT COUNT(*)
  FROM peers
  WHERE torrent = t.id
  AND seeder = "no"
) AS leechers_num,
t.comments, (
  SELECT COUNT(*)
  FROM comments
  WHERE torrent = t.id
) AS comments_num
FROM torrents AS t
ORDER BY t.id ASC';

$updatetorrents = array();

$tq = sql_query($tsql);
	while ($t = mysqli_fetch_assoc($tq)) {
 
    if ($t['seeders'] != $t['seeders_num'] || $t['leechers'] != $t['leechers_num'] || $t['comments'] != $t['comments_num'])
        $updatetorrents[] = '('.$t['id'].', '.$t['seeders_num'].', '.$t['leechers_num'].', '.$t['comments_num'].')';
}
((mysqli_free_result($tq) || (is_object($tq) && (get_class($tq) == "mysqli_result"))) ? true : false);

if (count($updatetorrents))
    sql_query('INSERT INTO torrents (id, seeders, leechers, comments) VALUES '.implode(', ', $updatetorrents).
        ' ON DUPLICATE KEY UPDATE seeders = VALUES(seeders), leechers = VALUES(leechers), comments = VALUES(comments)');
unset($updatetorrents);
  //=== Update karma seeding bonus... made nicer by devinkray :D
      //==   Updated and optimized by pdq :)
      //=== Using this will work for multiple torrents UP TO 5!... change the 5 to whatever... 1 to give the karma for only 1 torrent at a time, or 100 to make it unlimited (almost) your choice :P
      ///====== Seeding bonus per torrent
      $res = sql_query('SELECT COUNT(torrent) As tcount, userid FROM peers WHERE seeder =\'yes\' GROUP BY userid') or sqlerr(__FILE__, __LINE__);
      if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            if ($arr['tcount'] >= 1000)
                $arr['tcount'] = 5;
            $users_buffer[] = '(' . $arr['userid'] . ',0.225 * ' . $arr['tcount'] . ')';
        }
        if (sizeof($users_buffer) > 0) {
            sql_query("INSERT INTO users (id,seedbonus) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE seedbonus=seedbonus+values(seedbonus)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup - " . $count / 2 . " users received seedbonus");
        }
        unset ($users_buffer);
    }
  //== End
   // === Update coins by Bigjoos
      // === using this will work for multiple torrents UP TO 5!... change the 5 to whatever... 1 to give 1 Coin for only 1 torrent at a time, or 100 to make it unlimited (almost) your choice :P
      ///====== Coins per torrent
      if($TBDEV['coins']){
      $res = sql_query('SELECT COUNT(torrent) As tcount, userid FROM peers WHERE seeder =\'yes\' GROUP BY userid') or sqlerr(__FILE__, __LINE__);
      if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            if ($arr['tcount'] >= 1000)
                $arr['tcount'] = 5;
            $users_buffer[] = '(' . $arr['userid'] . ',0.500 * ' . $arr['tcount'] . ')';
        }
        if (sizeof($users_buffer) > 0) {
            sql_query("INSERT INTO users (id,coins) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE coins=coins+values(coins)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup - " . $count / 2 . " users received coins");
        }
        unset ($users_buffer);
    }
    }
   //== 09 Stats
   $registered = get_row_count('users');
   $unverified = get_row_count('users', "WHERE status='pending'");
   $torrents = get_row_count('torrents');
   $seeders = get_row_count('peers', "WHERE seeder='yes'");
   $leechers = get_row_count('peers', "WHERE seeder='no'");
   $torrentstoday = get_row_count('torrents', 'WHERE added > '.time().' - 86400'); 
   $donors = get_row_count('users', "WHERE donor='yes'");
   $unconnectables = get_row_count("peers", " WHERE connectable='no'");
   $forumposts = get_row_count("posts");
   $forumtopics = get_row_count("topics");
   $dt = sqlesc(time() - 300); // Active users last 5 minutes
   $numactive = get_row_count("users", "WHERE last_access >= $dt");
   sql_query("UPDATE stats SET regusers = '$registered', unconusers = '$unverified', torrents = '$torrents', seeders = '$seeders', leechers = '$leechers', unconnectables = '$unconnectables', torrentstoday = '$torrentstoday', donors = '$donors', forumposts = '$forumposts', forumtopics = '$forumtopics', numactive = '$numactive' WHERE id = '1' LIMIT 1");
   //== Cf's update forum post/topic count
   $forums = sql_query("SELECT t.forumid, count( DISTINCT p.topicid ) AS topics, count( * ) AS posts FROM posts p LEFT JOIN topics t ON t.id = p.topicid LEFT JOIN forums f ON f.id = t.forumid GROUP BY t.forumid");
   while ($forum = mysqli_fetch_assoc($forums)) {
   sql_query("update forums set postcount={$forum['posts']}, topiccount={$forum['topics']} where id={$forum['forumid']}");
   }
   write_log("Autoclean-------------------- Auto cleanup Complete using $queries queries --------------------");
   }
    
  function doslowcleanup()
  {
  global $TBDEV, $queries, $C_queries;
  set_time_limit(1200);
  $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
  while ($row = mysqli_fetch_array($result)) {
  if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
  $sql = "kill " . $row["Id"] . "";
  sql_query($sql) or sqlerr(__FILE__, __LINE__);
  }
  }
  ignore_user_abort(1);
  //== Delete expired announcements and processors
  sql_query("DELETE announcement_process FROM announcement_process LEFT JOIN users ON announcement_process.user_id = users.id WHERE users.id IS NULL");
  sql_query("DELETE FROM announcement_main WHERE expires < ".sqlesc(time()));
  sql_query("DELETE announcement_process FROM announcement_process LEFT JOIN announcement_main ON announcement_process.main_id = announcement_main.main_id WHERE announcement_main.main_id IS NULL");
  // Remove expired readposts...
  $dt = time() - $TBDEV["readpost_expiry"];
  sql_query("DELETE readposts FROM readposts "."LEFT JOIN posts ON readposts.lastpostread = posts.id "."WHERE posts.added < $dt");
  //==Putyns HappyHour
  $f = $TBDEV['happyhour'];
  $happy = unserialize(file_get_contents($f));
  $happyHour = strtotime($happy["time"]);
  $curDate = time();
  $happyEnd = $happyHour + 3600;
  if ($happy["status"] == 0) {
  write_log("Happy hour was @ " . get_date($happyHour, 'LONG',1,0) . " and Catid " . $happy["catid"] . " ");
  happyFile("set");
  } elseif (($curDate > $happyEnd) && $happy["status"] == 1)
  happyFile("reset");
  //== End 
  //== Delete iplog
  $dt = sqlesc(time() - 5 * 86400);
  sql_query("DELETE FROM iplog WHERE access < $dt");
  //=== Updated remove custom smilies by Bigjoos:)
    $res = sql_query("SELECT id FROM users WHERE smile_until < ".TIME_NOW." AND smile_until <> '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $subject = "Custom smilies expired.";
        $msg = "Your Custom smilies have timed out and has been auto-removed by the system. If you would like to have them again, exchange some Karma Bonus Points again. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Custom smilies Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ','.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, smile_until, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE smile_until=values(smile_until),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
        $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        write_log("Cleanup - Removed Custom smilies from " . $count / 2 . " members");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    //=== Updated remove karma vip by Bigjoos - change class number '1' in the users_buffer to whatever is under your vip class number
    $res = sql_query("SELECT id, modcomment FROM users WHERE vip_added='yes' AND vip_until < ".TIME_NOW."") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
         $subject = "VIP status expired.";
         $msg = "Your VIP status has timed out and has been auto-removed by the system. Become a VIP again by donating to {$TBDEV['site_name']} , or exchanging some Karma Bonus Points. Cheers !\n";
         while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Vip status Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ','.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ',1, \'no\', \'0\' , ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, vip_added, vip_until, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),vip_added=values(vip_added),vip_until=values(vip_until),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
        $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        write_log("Cleanup - Karma Vip status expired on - " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
        status_change($arr['id']); //== For Retros announcement mod
    }
  //=== Anonymous profile by Bigjoos:)
    $res = sql_query("SELECT id FROM users WHERE anonymous_until < ".TIME_NOW." AND anonymous_until <> '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $subject = "Anonymous profile expired.";
        $msg = "Your Anonymous profile has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Anonymous profile Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ','.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', \'no\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, anonymous_until, anonymous, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE anonymous_until=values(anonymous_until),anonymous=values(anonymous), modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
        $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        write_log("Cleanup - Removed Anonymous profile from " . $count / 2 . " members");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    //==End
	//== Delete old torrents
	$days = 28;
	$dt = (time() - ($days * 86400));
	$res = sql_query("SELECT id, name FROM torrents WHERE added < $dt AND seeders='0'");
	while ($arr = mysqli_fetch_assoc($res))
	{
		@unlink("{$TBDEV['torrent_dir']}/{$arr['id']}.torrent");
		sql_query("DELETE FROM torrents WHERE id={$arr['id']}");
		sql_query("DELETE FROM snatched WHERE torrentid ={$arr['id']}");
		sql_query("DELETE FROM bookmarks WHERE torrentid ={$arr['id']}");
		sql_query("DELETE FROM coins WHERE torrentid={$arr['id']}");
		sql_query("DELETE FROM peers WHERE torrent={$arr['id']}");
		sql_query("DELETE FROM comments WHERE torrent={$arr['id']}");
		sql_query("DELETE FROM files WHERE torrent={$arr['id']}");
		write_log("Torrent {$arr['id']} ({$arr['name']}) was deleted by system (older than $days days and no seeders)");
	}
	// === Clear funds after one month
    $secs = 30 * 86400;
    $dt = sqlesc(time() - $secs);
    sql_query("DELETE FROM funds WHERE added < $dt");
    if (is_file("cache/funds.txt")) 
    unlink("cache/funds.txt"); 
    // ===End
    //== Donation Progress Mod Updated For Tbdev 2009/2010 by Bigjoos
    $res = sql_query("SELECT id, modcomment, vipclass_before FROM users WHERE donor='yes' AND donoruntil < ".TIME_NOW." AND donoruntil <> '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $subject = "Donor status removed by system.";
        $msg = "Your Donor status has timed out and has been auto-removed by the system, and your Vip status has been removed. We would like to thank you once again for your support to {$TBDEV['site_name']}. If you wish to re-new your donation, Visit the site paypal link. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Donation status Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ','.time().', ' . sqlesc($msg) . ',' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ','.$arr['vipclass_before'].',\'no\',\'0\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, donor, donoruntil, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),donor=values(donor),donoruntil=values(donoruntil),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: Donation status expired - " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
        status_change($arr['id']); //== For Retros announcement mod
    }
    //===End===//
	  //== 09 Auto leech warn by Bigjoos
    //== Updated/modified autoleech warning script 
    $minratio = 0.3; // ratio < 0.4
    $downloaded = 10 * 1024 * 1024 * 1024; // + 10 GB
    $length = 3 * 7; // Give 3 weeks to let them sort there shit
    $res = sql_query("SELECT id FROM users WHERE enabled='yes' AND class = ".UC_USER." AND leechwarn = '0' AND uploaded / downloaded < $minratio AND downloaded >= $downloaded AND immunity = '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $dt = sqlesc(time());
        $subject = "Auto leech warned";
        $msg = "You have been warned and your download rights have been removed due to your low ratio. You need to get a ratio of 0.5 within the next 3 weeks or your Account will be disabled.";
        $leechwarn = sqlesc(time() + ($length * 86400));
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Automatically Leech warned and downloads disabled By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ', '.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ',' . $leechwarn . ',\'0\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, leechwarn, downloadpos, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE leechwarn=values(leechwarn),downloadpos=values(downloadpos),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: System applied auto leech Warning(s) to  " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    //End
    //== 09 Auto leech warn by Bigjoos
    //== Updated/Modified autoleech warn system - Remove warning and enable downloads
    $minratio = 0.5; // ratio > 0.5
    $res = sql_query("SELECT id FROM users WHERE downloadpos = '0' AND leechwarn > '1' AND uploaded / downloaded >= $minratio") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
       $subject = "Auto leech warning removed";
        $msg = "Your warning for a low ratio has been removed and your downloads enabled. We highly recommend you to keep your ratio positive to avoid being automatically warned again.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Leech warn removed and download enabled By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ','.time().', ' . sqlesc($msg) . ',  ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', \'1\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, leechwarn, downloadpos, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE leechwarn=values(leechwarn),downloadpos=values(downloadpos),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: System removed auto leech Warning(s) and renabled download(s) - " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    //==End
    //== 09 Auto leech warn by Bigjoos
    //== Disabled expired leechwarns
    $res = sql_query("SELECT id FROM users WHERE leechwarn > '1' AND leechwarn < ".TIME_NOW." AND leechwarn <> '0' ") or sqlerr(__FILE__, __LINE__);
    $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - User disabled - Low ratio\n");
            $users_buffer[] = '(' . $arr['id'] . ' , \'0\', \'no\', ' . $modcomment . ')';
        }
        if (sizeof($users_buffer) > 0) {
            sql_query("INSERT INTO users (id, leechwarn, enabled, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),leechwarn=values(leechwarn),enabled=values(enabled),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: Disabled " . $count / 2 . " Member(s) - Leechwarns expired");
        }
        unset ($users_buffer);
    }
    //==End
  //== 09 Auto invite by Bigjoos
	$ratiocheck  =  1.0;
	$joined = (time() - 86400*90);
    $res = sql_query("SELECT id, uploaded, downloaded FROM users WHERE invites='1' AND class = ".UC_USER." AND uploaded / downloaded <= $ratiocheck AND enabled='yes' AND added < $joined") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $subject ="Auto Invites";
        $msg = "Congratulations, your user group met a set out criteria therefore you have been awarded 2 invites  :)\n Please use them carefully. Cheers ".$TBDEV['site_name']." staff.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Awarded 2 bonus invites by System (UL=" . mksize($arr['uploaded']) . ", DL=" . mksize($arr['downloaded']) . ", R=" . $ratio . ") \n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ', '.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', 2, ' . $modcomment . ')'; //== 2 in the user_buffer is award amount :)
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, invites, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE invites = invites+values(invites), modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: Awarded 2 bonus invites to " . $count / 2 . " member(s) ");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
  write_log("Slowautoclean -------------------- Delayed cleanup Complete using $queries queries --------------------");
  }

  function doslowcleanup2()
  {
    global $TBDEV, $queries, $C_queries;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
    if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
    $sql = "kill " . $row["Id"] . "";
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }
    }
    ignore_user_abort(1);
    //===09 hnr by sir_snugglebunny
    //=== Hit and run part... after 3 days, add the mark of Cain...
	  $secs = 3 * 86400; //== set to 3 days - 3 * 86400
    //$secs = 1 * 60; //== set to 60 secs for testing - 1 * 60
    $hnr = time() - $secs;
	  $res = sql_query('SELECT id FROM snatched WHERE hit_and_run <> \'0\' AND hit_and_run < '.sqlesc($hnr).'') or sqlerr(__FILE__, __LINE__);	
	  while ($arr = mysqli_fetch_assoc($res))
	  {
	  sql_query('UPDATE snatched SET mark_of_cain = \'yes\' WHERE id='.sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);
	  }
    //=== Hit and run... disable Downloading rights if they have 3 marks of cain if there not immune
	  $res_fuckers = sql_query('SELECT count(*) AS poop, snatched.userid, users.username, users.modcomment, users.hit_and_run_total, users.downloadpos FROM snatched LEFT JOIN users ON snatched.userid = users.id WHERE snatched.mark_of_cain = \'yes\' AND users.hnrwarn = \'no\' AND users.immunity = \'0\' GROUP BY snatched.userid') or sqlerr(__FILE__, __LINE__);	
	  while ($arr_fuckers = mysqli_fetch_assoc($res_fuckers))
	  {
		if ($arr_fuckers['poop'] > 10 && $arr_fuckers['downloadpos'] == 1)
		{
		//=== Set them to no DLs
		$subject = sqlesc('Download disabled by System');
		$msg = sqlesc("Sorry ".$arr_fuckers['username'].",\n Because you have 10 or more torrents that have not been seeded to either a 1:1 ratio, or for the expected seeding time, your downloading rights have been disabled by the Auto system !\nTo get your Downloading rights back is simple,\n just start seeding the torrents in your profile [ click your username, then click your [url=".$TBDEV['baseurl']."/userdetails.php?id=".$arr_fuckers['userid']."&completed=1]Completed Torrents[/url] link to see what needs seeding ] and your downloading rights will be turned back on by the Auto system after the next clean-time [ updates 4 times per hour ].\n\nDownloads are disabled after a member has three or more torrents that have not been seeded to either a 1 to 1 ratio, OR for the required seed time [ please see the [url=".$TBDEV['baseurl']."/faq.php]FAQ[/url] or [url=".$TBDEV['baseurl']."/rules.php]Site Rules[/url] for more info ]\n\nIf this message has been in error, or you feel there is a good reason for it, please feel free to PM a staff member with your concerns.\n\n we will do our best to fix this situation.\n\nBest of luck!\n ".$TBDEV['site_name']." staff.\n");
		$modcomment = htmlspecialchars($arr_fuckers['modcomment']);
		$modcomment =  get_date( time(), 'DATE', 1 ) . " - Download rights removed for H and R - AutoSystem.\n". $modcomment;
		$modcom =  sqlesc($modcomment);
		sql_query("INSERT INTO messages (sender, receiver, added, msg, subject, poster) VALUES(0, $arr_fuckers[userid], ".sqlesc(time()).", $msg, $subject, 0)") or sqlerr(__FILE__, __LINE__);	
		sql_query('UPDATE users SET hit_and_run_total = hit_and_run_total + '.$arr_fuckers['poop'].', downloadpos = \'0\', hnrwarn = \'yes\', modcomment = '.$modcom.'  WHERE downloadpos = \'1\' AND id='.sqlesc($arr_fuckers['userid'])) or sqlerr(__FILE__, __LINE__);
		}
	  }
    //=== Hit and run... turn their DLs back on if they start seeding again
    $res_good_boy = sql_query('SELECT id, username, modcomment FROM users WHERE hnrwarn = \'yes\' AND downloadpos = \'0\'') or sqlerr(__FILE__, __LINE__);
    while ($arr_good_boy = mysqli_fetch_assoc($res_good_boy))
	  {
	  $res_count = sql_query('SELECT count(*) FROM snatched WHERE userid = '.sqlesc($arr_good_boy['id']).' AND mark_of_cain = \'yes\'') or sqlerr(__FILE__, __LINE__);
	  $arr_count = mysqli_fetch_row($res_count);
		if ($arr_count[0] < 10)
		{
		//=== Set them to yes DLs
		$subject = sqlesc('Download restored by System');
		$msg = sqlesc("Hi ".$arr_good_boy['username'].",\n Congratulations ! Because you have seeded the torrents that needed seeding, your downloading rights have been restored by the Auto System !\n\nhave fun !\n ".$TBDEV['site_name']." staff.\n");
		$modcomment = htmlspecialchars($arr_good_boy['modcomment']);
		$modcomment =  get_date( time(), 'DATE', 1 ) . " - Download rights restored from H and R - AutoSystem.\n". $modcomment;
		$modcom =  sqlesc($modcomment);
		sql_query("INSERT INTO messages (sender, receiver, added, msg, subject, poster) VALUES(0, ".sqlesc($arr_good_boy['id']).", ".sqlesc(time()).", $msg, $subject, 0)") or sqlerr(__FILE__, __LINE__);
		sql_query('UPDATE users SET downloadpos = \'1\', hnrwarn = \'no\', modcomment = '.$modcom.'  WHERE id = '.sqlesc($arr_good_boy['id'])) or sqlerr(__FILE__, __LINE__);
		}
	  }
	  //==End
  sql_query("UPDATE `freeslots` SET `double` = 0 WHERE `double` != 0 AND `double` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__); 
  sql_query("UPDATE `freeslots` SET `free` = 0 WHERE `free` != 0 AND `free` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__); 
  sql_query("DELETE FROM `freeslots` WHERE `double` = 0 AND `free` = 0") or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `free_switch` = 0 WHERE `free_switch` > 1 AND `free_switch` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `torrents` SET `free` = 0 WHERE `free` > 1 AND `free` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `downloadpos` = 1 WHERE `downloadpos` > 1 AND `downloadpos` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `uploadpos` = 1 WHERE `uploadpos` > 1 AND `uploadpos` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `forumpost` = 1 WHERE `forumpost` > 1 AND `forumpost` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `chatpost` = 1 WHERE `chatpost` > 1 AND `chatpost` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `avatarpos` = 1 WHERE `avatarpos` > 1 AND `avatarpos` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `immunity` = 0 WHERE `immunity` > 1 AND `immunity` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `leechwarn` = 0 WHERE `leechwarn` > 1 AND `leechwarn` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `warned` = 0 WHERE `warned` > 1 AND `warned` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `pirate` = 0 WHERE `pirate` > 1 AND `pirate` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  sql_query("UPDATE `users` SET `king` = 0 WHERE `king` > 1 AND `king` < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
  //== Delete old backup's
  $days = 7;
  $backup_dir = 'C://AppServ/www/include/backup'; //-- path to the backup folder
  $res = sql_query("SELECT id, name FROM dbbackup WHERE added < ".sqlesc(time() - ($days * 86400))) or sqlerr(__FILE__, __LINE__);
  if (mysqli_num_rows($res) > 0)
  {
  $ids = array();
  while ($arr = mysqli_fetch_assoc($res))
  {
  $ids[] = (int)$arr['id'];
  $filename = $backup_dir.'/'.$arr['name'];
  if (is_file($filename))
  unlink($filename);
  }
  sql_query('DELETE FROM dbbackup WHERE id IN ('.implode(', ', $ids).')') or sqlerr(__FILE__, __LINE__);
  }
  //== end
  //== Delete inactive user accounts
	$secs = 50*86400;
	$dt = (time() - $secs);
	$maxclass = UC_SYSOP;
	sql_query("DELETE FROM users WHERE parked='no' AND status='confirmed' AND class <= $maxclass AND last_access < $dt");
	 //== Delete parked user accounts
	 $secs = 175*86400; // change the time to fit your needs
	 $dt = (time() - $secs);
	 $maxclass = UC_SYSOP;
	 sql_query("DELETE FROM users WHERE parked='yes' AND status='confirmed' AND class <= $maxclass AND last_access < $dt");
	//== Delete shout
  $secs = 2 * 86400;
  $dt = sqlesc(time() - $secs);
  sql_query("DELETE FROM shoutbox WHERE " . time() . " - date > $secs") or sqlerr(__FILE__, __LINE__);
  //== Updated promote power users
  $limit = 25*1024*1024*1024;
	$minratio = 1.05;
	$maxdt = (time() - 86400*28);
    $res = sql_query("SELECT id, uploaded, downloaded FROM users WHERE class = 0 AND uploaded >= $limit AND uploaded / downloaded >= $minratio AND enabled='yes' AND added < $maxdt") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $subject ="Auto Promotion";
        $msg = "Congratulations, you have been Auto-Promoted to [b]Power User[/b]. :)\n You get one extra invite.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Promoted to Power User by System (UL=" . mksize($arr['uploaded']) . ", DL=" . mksize($arr['downloaded']) . ", R=" . $ratio . ") \n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ', '.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', 1, 1, ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, invites, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class), invites = invites+values(invites), modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: Promoted " . $count / 2 . " member(s) from User to Power User");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
        status_change($arr['id']); //== For Retros announcement mod
    }
    //== Updated demote power users
    $minratio = 0.85;
    $res = sql_query("SELECT id, uploaded, downloaded FROM users WHERE class = 1 AND uploaded / downloaded < $minratio") or sqlerr(__FILE__, __LINE__);
    $subject ="Auto Demotion";
    $msgs_buffer = $users_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        $msg = "You have been auto-demoted from [b]Power User[/b] to [b]User[/b] because your share ratio has dropped below  $minratio.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = sqlesc(get_date( time(), 'DATE', 1 ) . " - Demoted To User by System (UL=" . mksize($arr['uploaded']) . ", DL=" . mksize($arr['downloaded']) . ", R=" . $ratio . ") \n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ', '.time().', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', 0, ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
            write_log("Cleanup: Demoted " . $count / 2 . " member(s) from Power User to User");
            status_change($arr['id']);
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
        status_change($arr['id']); //== For Retros announcement mod
    }
  //==End
  write_log("Slowautoclean2 -------------------- Delayed cleanup 2 Complete using $queries queries--------------------");
  }
  
  function dooptimizedb()
  {
  global $TBDEV, $queries, $C_queries;
  set_time_limit(1200);
  $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
  while ($row = mysqli_fetch_array($result)) {
  if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
  $sql = "kill " . $row["Id"] . "";
  sql_query($sql) or sqlerr(__FILE__, __LINE__);
  }
  }
  ignore_user_abort(1);
  $alltables = sql_query("SHOW TABLES") or sqlerr(__FILE__, __LINE__);
  while ($table = mysqli_fetch_assoc($alltables)) {
  foreach ($table as $db => $tablename) {
  $sql = "OPTIMIZE TABLE $tablename";
  /* Preg match the sql incase it was hijacked somewhere!(will use CHECK|ANALYZE|REPAIR|later) */
  if (preg_match('@^(CHECK|ANALYZE|REPAIR|OPTIMIZE)[[:space:]]TABLE[[:space:]]' . $tablename . '$@i', $sql))
   sql_query($sql) or die("<b>Something was not right!</b>.\n<br />Query: " . $sql . "<br />\nError: (" . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) . ") " . htmlspecialchars(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
   }
   }
   @((mysqli_free_result($alltables) || (is_object($alltables) && (get_class($alltables) == "mysqli_result"))) ? true : false);
   write_log("Auto-optimizedb--------------------Auto Optimization Complete using $queries queries --------------------");
   }
?>