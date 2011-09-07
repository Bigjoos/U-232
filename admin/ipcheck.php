<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
if (!defined('IN_TBDEV_ADMIN'))
{
    echo '<h1>Incorrect access</h1>You cannot access this file directly.';
    exit();
}
require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'html_functions.php');

if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

$lang = array_merge( $lang, load_language('ad_ipcheck') );

$HTMLOUT ="";

$HTMLOUT.= begin_frame("", true);
$HTMLOUT.= begin_table();

 $res = sql_query("SELECT count(*) AS dupl, ip FROM users WHERE enabled = 'yes' AND ip <> '' AND ip <> '127.0.0.0' GROUP BY ip ORDER BY dupl DESC, ip") or sqlerr(__FILE__, __LINE__);
 $HTMLOUT.="<tr align='center'>
 <td class='colhead' width='90'>{$lang['ipcheck_user']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_email']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_regged']}</td>
 <td class='colhead' width='75'>{$lang['ipcheck_lastacc']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_dload']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_upped']}</td>
 <td class='colhead' width='45'>{$lang['ipcheck_ratio']}</td>
 <td class='colhead' width='125'>{$lang['ipcheck_ip']}</td>
 <td class='colhead' width='40'>{$lang['ipcheck_peer']}</td></tr>\n";
 $ip='';
 $uc = 0;
  while($ras = mysql_fetch_assoc($res)) {
        if ($ras["dupl"] <= 1)
          break;
        if ($ip <> $ras['ip']) {
          $ros = sql_query("SELECT id, username, class, email, chatpost, pirate, king, leechwarn, added, last_access, downloaded, uploaded, ip, warned, donor, enabled, (SELECT COUNT(*) FROM peers WHERE peers.ip = users.ip AND users.id = peers.userid) AS peer_count FROM users WHERE ip='".$ras['ip']."' ORDER BY id") or sqlerr(__FILE__, __LINE__);
          $num2 = mysql_num_rows($ros);
          if ($num2 > 1) {
                $uc++;
            while($arr = mysql_fetch_assoc($ros)) {
                  
                  
                  
                  if ($arr['added'] == '0')
                        $arr['added'] = '-';
                  if ($arr['last_access'] == '0')
                        $arr['last_access'] = '-';
                  if($arr["downloaded"] != 0)
                        $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
                  else
                        $ratio="---";

                  $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
                  $uploaded = mksize($arr["uploaded"]);
                  $downloaded = mksize($arr["downloaded"]);
                  $added = get_date($arr['added'], 'DATE', 1,0);
                  $last_access = get_date($arr['last_access'], '', 1,0);
                  
                  if ($uc%2 == 0)
                        $utc = "";
                  else
                        $utc = " bgcolor=\"ECE9D8\"";

                  $HTMLOUT.="<tr$utc><td align='left'><a href='userdetails.php?id=" . $arr['id'] . "'>" . format_username($arr) . "</a></td>
                                  <td align='center'>$arr[email]</td>
                                  <td align='center'>$added</td>
                                  <td align='center'>$last_access</td>
                                  <td align='center'>$downloaded</td>
                                  <td align='center'>$uploaded</td>
                                  <td align='center'>$ratio</td>
                                  <td align='center'><span style=\"font-weight: bold;\">$arr[ip]</span></td>\n<td align='center'>" .
                                  ($arr['peer_count'] > 0 ? "<span style=\"color: red; font-weight: bold;\">{$lang['ipcheck_no']}</span>" : "<span style=\"color: green; font-weight: bold;\">{$lang['ipcheck_yes']}</span>") . "</td></tr>\n";
                  $ip = $arr["ip"];
                }
          }
        }
  }

$HTMLOUT.= end_table();
$HTMLOUT.= end_frame();
print stdhead('Ip Check') . $HTMLOUT . stdfoot();
?>