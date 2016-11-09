<?php if (!defined('IN_REQUESTS')) exit('No direct script access allowed');
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
$res = sql_query('SELECT * FROM voted_requests WHERE requestid = '.sqlesc($id).' and userid = '.sqlesc($CURUSER['id'])) or sqlerr(__FILE__,__LINE__);
$arr = mysqli_fetch_assoc($res);

if ($arr) {
    $HTMLOUT .= "
<h3>{$lang['reset_success']}</h3>
<p style='text-decoration:underline;'>{$lang['vote_allowed']}</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>{$lang['vote_details']}</b></a> | 
<a class='altlink' href='viewrequests.php'><b>{$lang['vote_all']}</b></a></p>
<br /><br />";
}
else {
    sql_query('UPDATE requests SET hits = hits+1 WHERE id='.sqlesc($id)) or sqlerr(__FILE__,__LINE__);
    if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        sql_query('INSERT INTO voted_requests VALUES(0, '.sqlesc($id).', '.sqlesc($CURUSER['id']).')') or sqlerr(__FILE__,__LINE__);
        $HTMLOUT .=  "
<h3>Vote accepted</h3>
<p style='text-decoration:underline;'>{$lang['vote_success']}$id</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>{$lang['vote_details']}</b></a> |
<a class='altlink' href='viewrequests.php'><b>{$lang['vote_all']}</b></a></p>
<br /><br />";
    } else {
        $HTMLOUT .=  "
<h3>Error</h3>
<p style='text-decoration:underline;'>{$lang['vote_no_id']}$id</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>{$lang['vote_details']}</b></a> |
<a class='altlink' href='viewrequests.php'><b>{$lang['vote_all']}</b></a></p>
<br /><br />"; 
    }
}

/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Vote').$HTMLOUT.stdfoot();
?>
