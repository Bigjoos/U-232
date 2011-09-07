<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');

  $id = 0 + $_GET["id"];
  if (!is_numeric($id) || $id < 1 || floor($id) != $id)
    die;

  $type = $_GET["type"];

  dbconn(false);
  
  loggedinorreturn();
  
  $lang = array_merge( load_language('global'), load_language('deletemessage') );
  
  if ($type == 'in')
  {
  	// make sure message is in CURUSER's Inbox
	  $res = sql_query("SELECT receiver, location FROM messages WHERE id=" . sqlesc($id)) or die("barf");
	  $arr = mysql_fetch_assoc($res) or die("{$lang['deletemessage_bad_id']}");
	  if ($arr["receiver"] != $CURUSER["id"])
	    die("{$lang['deletemessage_dont_do']}");
    if ($arr["location"] == 'in')
	  	sql_query("DELETE FROM messages WHERE id=" . sqlesc($id)) or die("{$lang['deletemessage_code1']}");
    else if ($arr["location"] == 'both')
			sql_query("UPDATE messages SET location = 'out' WHERE id=" . sqlesc($id)) or die("{$lang['deletemessage_code2']}");
    else
    	die("{$lang['deletemessage_not_inbox']}");
  }
	elseif ($type == 'out')
  {
   	// make sure message is in CURUSER's Sentbox
	  $res = sql_query("SELECT sender, location FROM messages WHERE id=" . sqlesc($id)) or die("barf");
	  $arr = mysql_fetch_assoc($res) or die("{$lang['deletemessage_bad_id']}");
	  if ($arr["sender"] != $CURUSER["id"])
	    die("{$lang['deletemessage_dont_do']}");
    if ($arr["location"] == 'out')
	  	sql_query("DELETE FROM messages WHERE id=" . sqlesc($id)) or die("{$lang['deletemessage_code3']}");
    else if ($arr["location"] == 'both')
			sql_query("UPDATE messages SET location = 'in' WHERE id=" . sqlesc($id)) or die("{$lang['deletemessage_code4']}");
    else
    	die("{$lang['deletemessage_sentbox']}");
  }
  else
  	die("{$lang['deletemessage_unknown']}");
  header("Location: {$TBDEV['baseurl']}/inbox.php".($type == 'out'?"?out=1":""));
?>