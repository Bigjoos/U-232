<?php if (!defined('TBVERSION')) exit('No direct script access allowed');
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
/** freeleech/doubleseed slots mod by pdq for TBDev.net 2009**/
$slot = isset($_GET['slot']) ? $_GET['slot'] : '';
if ($slot)
{ 

    if ($CURUSER['freeslots'] < 1)
        stderr('USER ERROR', 'No freeleech slots available.');

    $slot_options = array('free' => 1, 'double' => 2);
    if (!isset($slot_options[$slot]))
        stderr('Error', 'Invalid Command!');

    switch ($slot)
    {
        case 'free':
            $value_3 = 'double';
            break;

        case 'double':
            $value_3 = 'free';
            break;
    }

    $added = (TIME_NOW + 14*86400);
    $r = sql_query("SELECT * FROM `freeslots` WHERE tid = ".sqlesc($id)." AND uid = ".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $a = mysqli_fetch_assoc($r);

    if ($a['tid'] == $id && $a['uid'] == $CURUSER['id'] && (($a['free'] != 0 && $slot === 'free') || ($a['double'] != 0 && $slot === 'double')))
        stderr('Doh!', ($slot != 'free' ? 'Doubleseed' : 'Freeleech').' slot already in use.');

    sql_query("UPDATE users SET freeslots = (".sqlesc($CURUSER['freeslots'])."-1) WHERE id = ".sqlesc($CURUSER['id'])." && ".sqlesc($CURUSER['freeslots']).">=1") or sqlerr(__FILE__, __LINE__);

   if ($a['tid'] == $id && $a['uid'] == $CURUSER['id'] && ($a['free'] != 0 || $a['double'] != 0))
        sql_query("UPDATE `freeslots` SET ".sqlesc($slot)." = ".sqlesc($added)."  WHERE `tid` = ".sqlesc($id)." AND `uid` = ".sqlesc($CURUSER['id'])." AND ".sqlesc($value_3)." != 0") or sqlerr(__FILE__, __LINE__);
    else
       sql_query("INSERT INTO `freeslots` (`tid`, `uid`, `".$slot."`) VALUES (".sqlesc($id).", ".sqlesc($CURUSER['id']).", ".sqlesc($added).")") or sqlerr(__FILE__, __LINE__);   
}
?>
