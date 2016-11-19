<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
dbconn(false);
loggedinorreturn();

$query1 = sprintf('UPDATE users SET curr_ann_id = 0, curr_ann_last_check = \'0\' '.
 	 'WHERE id = %s AND curr_ann_id != 0',
 		 sqlesc($CURUSER['id']));

sql_query($query1) or sqlerr(__FILE__, __LINE__);

header("Location: {$INSTALLER09['baseurl']}/index.php");
?>
