<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once INCL_DIR.'bbcode_functions.php';
require_once INCL_DIR.'user_functions.php';
require_once INCL_DIR.'mood.php';

dbconn(false);

		$htmlout = '';
    $htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
    <meta name='generator' content='TBDev.net' />
	  <meta name='MSSmartTagsPreventParsing' content='TRUE' />
		<title>User Moods</title>
    <link rel='stylesheet' href='./templates/1/itunes.css' type='text/css' />
    </head>
    <body>";

    if (isset($_GET["mood"]) && (isset($_GET["id"]))) {
    $moodid = (isset($_GET['id'])?0 + $_GET['id']:'');
    $moodname = (isset($_GET['mood'])?htmlspecialchars($_GET['mood']):'');
    $moodhdr = str_replace('+', ' ', $moodname);
    mysql_query("UPDATE users SET mood={$moodid} WHERE id={$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
    $htmlout .= "<h3 align=\"center\">" . $CURUSER['username'] . "'s Mood has been changed to {$moodhdr}!</h3><table><tr><td>";
   
    $htmlout .= "<script type='text/javascript'>
    /*<![CDATA[*/
    opener.location.reload(true);
    self.close();
    /*]]>*/
    </script>";

    }

$htmlout .= "<h3 align=\"center\">" . $CURUSER['username'] . "'s Mood</h3><table><tr><td>";

foreach($mood as $key => $value) {
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $moodid = $change[$value['id']]['id'];
    $moodname = $change[$value['id']]['name'];
    $moodurl = str_replace(' ', '+', $moodname);
    $moodpic = $change[$value['id']]['image'];
    $htmlout .= "<a href='?mood=" . $moodurl . "&amp;id=" . $moodid . "'>
    <img src='" . $TBDEV['pic_base_url'] . "smilies/{$moodpic}' alt='{$moodname}' border='0' />{$moodname}</a>&nbsp;&nbsp;";
    }

$htmlout .= "<br /><a href=\"javascript:self.close();\"><font color=\"#FF0000\">Close window</font></a>";
$htmlout .= "</td></tr></table></body></html>";
print $htmlout;
?>
