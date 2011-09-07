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
require_once INCL_DIR.'pager_functions.php';
require_once INCL_DIR.'torrenttable_functions.php';
require_once INCL_DIR.'html_functions.php';
dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('mytorrents') ); 
    $lang = array_merge( $lang, load_language( 'torrenttable_functions' ));
    
    $HTMLOUT = '';

    $where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes'";
    $res = sql_query("SELECT COUNT(*) FROM torrents $where");
    $row = mysql_fetch_array($res,MYSQL_NUM);
    $count = $row[0];

    if (!$count) 
    {

      $HTMLOUT .= "{$lang['mytorrents_no_torrents']}";
      $HTMLOUT .= "{$lang['mytorrents_no_uploads']}";

    }
    else 
    {
      $pager = pager(20, $count, "mytorrents.php?");

      $res = sql_query("SELECT torrents.type, torrents.sticky, torrents.nuked, torrents.descr, torrents.nukereason, torrents.free, torrents.comments, torrents.leechers, torrents.seeders, IF(torrents.numratings < {$TBDEV['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, save_as, numfiles, added, size, views, visible, hits, times_completed, category, freeslots.tid, freeslots.uid, freeslots.free AS freeslot, freeslots.double AS doubleup FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN freeslots ON (torrents.id=freeslots.tid)$where ORDER BY id DESC ".$pager['limit']);

      $HTMLOUT .= $pager['pagertop'];

      $HTMLOUT .= torrenttable($res, "mytorrents");

      $HTMLOUT .= $pager['pagerbottom'];
    }

    print stdhead($CURUSER["username"] . "'s torrents") . $HTMLOUT . stdfoot();

?>