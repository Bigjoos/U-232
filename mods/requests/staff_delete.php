<?php if (!defined('IN_REQUESTS')) exit('No direct script access allowed');
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
if ($CURUSER['class'] >= UC_MODERATOR) {
    
    if (empty($_POST['delreq']))
       stderr("{$lang['error_error']}", "{$lang['error_empty']}");
       
    sql_query("DELETE FROM requests WHERE id IN (".implode(", ", array_map("sqlesc",$_POST['delreq'])).")") or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM voted_requests WHERE requestid IN (".implode(", ", array_map("sqlesc",$_POST['delreq'])).")") or sqlerr(__FILE__,__LINE__);
    sql_query("DELETE FROM comments WHERE request IN (".implode(", ", array_map("sqlesc",$_POST['delreq'])).")") or sqlerr(__FILE__,__LINE__);
    header('Refresh: 0; url=viewrequests.php');
    die();
}
else
    stderr("{$lang['error_error']}", "{$lang['error_dee']}");
?>
