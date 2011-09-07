<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'html_functions.php');

$lang = array_merge( $lang, load_language('ad_docleanup') );
 /** new way **/
if (!min_class(UC_SYSOP)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

$HTMLOUT ='';

function calctime($val)
{
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    $secs = $val - ($mins * 60);
    return $days . " Days, " . $hours . " Hours, " . $mins . " Minutes, " . $secs . " Seconds";
}

if (!function_exists('memory_get_usage')) {
    function memory_get_usage()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $output = array();
                exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);

                return preg_replace('/[\D]/', '', $output[5]) * 1024;
            }
        } else {
            $pid = getmypid();
            exec("ps -eo%mem,rss,pid | grep $pid", $output);
            $output = explode(" ", $output[0]);
            return $output[1] * 1024;
        }
    }
}

$HTMLOUT .= begin_main_frame('Cleanups');
$HTMLOUT .= begin_table();

$HTMLOUT .="<tr><td class='colhead'>Cleanup Name</td>
<td class='colhead'>Last Run</td>
<td class='colhead'>Runs every</td>
<td class='colhead'>Scheduled to run</td>
</tr>";

$res = mysql_query("SELECT arg, value_u FROM avps");
while ($arr = mysql_fetch_assoc($res)) {
    switch ($arr['arg']) {
        case 'lastcleantime': $arg = $TBDEV['autoclean_interval'];
            break;
        case 'lastslowcleantime': $arg = $TBDEV['autoslowclean_interval'];
            break;
        case 'lastslowcleantime2': $arg = $TBDEV['autoslowclean_interval2'];
            break;
        case 'lastoptimizedbtime': $arg = $TBDEV['optimizedb_interval'];
            break;
    }

    $HTMLOUT .="<tr><td>".$arr['arg']."</td>
    <td>".get_date($arr['value_u'], 'DATE',1,0) . " (" .get_date($arr['value_u'], 'DATE',1,0) . ")</td>
    <td>" . calctime($arg) . "</td>
    <td>" . calctime($arr['value_u'] - (time() - $arg)) . "</td>
    </tr>";
}
$HTMLOUT .= end_table();


$HTMLOUT .="<form action='admin.php?action=docleanup' method='post'>
<table align='center'>
<tr>
<td class='table'>
<input type='checkbox' name='docleanup' />Do Cleanup
&nbsp;&nbsp;&nbsp;&nbsp;
<input type='checkbox' name='doslowcleanup' />Do Slow Cleanup
&nbsp;&nbsp;&nbsp;&nbsp;
<input type='checkbox' name='doslowcleanup2' />Do Slow Cleanup 2
&nbsp;&nbsp;&nbsp;&nbsp;
<input type='checkbox' name='dooptimization' />Do Optimization
<input type='submit' value='Submit' />
</td></tr></table>
</form>";

$now = time();
if (isset($_POST['docleanup'])) {
    mysql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastcleantime'") or sqlerr(__FILE__, __LINE__);
    require_once("include/cleanup.php");
    docleanup();
    header('Refresh: 2; url='.$TBDEV['baseurl'].'/admin.php?action=docleanup');
    $HTMLOUT .="<br /><h1>Cleanup Done</h1>";
}

if (isset($_POST['doslowcleanup'])) {
    mysql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastslowcleantime'") or sqlerr(__FILE__, __LINE__);
    require_once("include/cleanup.php");
    doslowcleanup();
    header('Refresh: 2; url='.$TBDEV['baseurl'].'/admin.php?action=docleanup');
   $HTMLOUT .="<br /><h1>Slow Cleanup Done</h1>";
}

if (isset($_POST['doslowcleanup2'])) {
    mysql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastslowcleantime2'") or sqlerr(__FILE__, __LINE__);
    require_once("include/cleanup.php");
    doslowcleanup2();
    header('Refresh: 2; url='.$TBDEV['baseurl'].'/admin.php?action=docleanup');
   $HTMLOUT .="<br /><h1>Slow Cleanup 2 Done</h1>";
}

if (isset($_POST['dooptimization'])) {
    mysql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastoptimizedbtime'") or sqlerr(__FILE__, __LINE__);
    require_once("include/cleanup.php");
    dooptimizedb();
    header('Refresh: 2; url='.$TBDEV['baseurl'].'/admin.php?action=docleanup');
    $HTMLOUT .="<br /><h1>Optimization Done</h1>";
}

$HTMLOUT .="Memory usage:" . memory_get_usage() . "<br /><br />";
$HTMLOUT .= end_main_frame();
print stdhead('Doclean Up') . $HTMLOUT . stdfoot();
?>