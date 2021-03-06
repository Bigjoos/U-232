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

$lang = array_merge( $lang, load_language('bonusmanager'));

$HTMLOUT="";
 
if (!min_class(UC_ADMINISTRATOR))
header( "Location: {$INSTALLER09['baseurl']}/index.php");
	
	  $res = sql_query("SELECT id, bonusname, points, pointspool, minpoints, description, art, menge, enabled FROM bonus") or sqlerr(__FILE__, __LINE__);
	  if(isset($_POST["id"]) || isset($_POST["points"]) || isset($_POST["pointspool"]) || isset($_POST["minpoints"]) || isset($_POST["description"]) || isset($_POST["enabled"])){
		$id = 0 + $_POST["id"];
		$points = 0 + $_POST["bonuspoints"];
		$pointspool = 0 + $_POST["pointspool"];
		$minpoints = 0 + $_POST["minpoints"];
		$descr = 	htmlspecialchars($_POST["description"]);
		$enabled = "yes";
		if(isset($_POST["enabled"]) == ''){
		$enabled = "no";
		}
		
		$sql = "UPDATE bonus SET points = ".sqlesc($points).", pointspool=".sqlesc($pointspool).", minpoints=".sqlesc($minpoints).", enabled = ".sqlesc($enabled).", description = ".sqlesc($descr)." WHERE id = ".sqlesc($id)."";
	  switch($id){
		case 1:
			makeithappen($sql);
		break;
		case 2:
			makeithappen($sql);
		break;
		case 3:
			makeithappen($sql);
		break;	
		case 4:
			makeithappen($sql);
		break;
		case 5:
			makeithappen($sql);
		break;
		case 6:
			makeithappen($sql);
		break;		
		case 7:
			makeithappen($sql);
		break;			
		case 8:
			makeithappen($sql);
		break;
		case 9:
			makeithappen($sql);
		break;
		case 10:
			makeithappen($sql);
		break;
		case 11:
			makeithappen($sql);
		break;	
		case 12:
			makeithappen($sql);
		break;	
		case 13:
			makeithappen($sql);
		break;
	  case 14:
			makeithappen($sql);
		break;	
		case 15:
			makeithappen($sql);
		break;	
		case 16:
			makeithappen($sql);
		break;
		case 17:
			makeithappen($sql);
		break;
		case 18:
			makeithappen($sql);
		break;
		case 19:
			makeithappen($sql);
		break;
		case 20:
			makeithappen($sql);
		break;
		case 21:
			makeithappen($sql);
		break;
		case 22:
			makeithappen($sql);
		break;
		case 23:
			makeithappen($sql);
		break;
		case 24:
			makeithappen($sql);
		break;
		case 25:
			makeithappen($sql);
		break;
		case 26:
			makeithappen($sql);
		break;
		case 27:
			makeithappen($sql);
		break;
		case 28:
			makeithappen($sql);
		break;
		case 29:
			makeithappen($sql);
		break;
	}
	}
	
while($arr = mysqli_fetch_assoc($res)) {
    $HTMLOUT .="<form name='bonusmanage' method='post' action='admin.php?action=bonusmanage'>
	  <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:#890537;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['bonusmanager_bm']}</span></div>
	  <table width='100%' border='2' cellpadding='8'>
	  <tr>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_id']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_enabled']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_bonus']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_points']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_pointspool']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_minpoints']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_description']}</td>
	  <td style='background:#890537;height:25px;'>{$lang['bonusmanager_type']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_quantity']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_action']}</td></tr> 
	  <tr><td>
		<input name='id' type='hidden' value='" . intval($arr["id"]) ."' />".intval($arr['id'])."</td>
		<td><input name='enabled' type='checkbox' ".($arr["enabled"] == "yes" ? " checked='checked'" : ""). " /></td>
		<td>".htmlspecialchars($arr['bonusname'])."</td>
		<td><input type='text' name='bonuspoints' value='" . intval($arr["points"]) ."' size='4' /></td>
		<td><input type='text' name='pointspool' value='" . intval($arr["pointspool"]) ."' size='4' /></td>
		<td><input type='text' name='minpoints' value='" . intval($arr["minpoints"]) ."' size='4' /></td>
		<td><textarea name='description' rows='4' cols='10'>" . htmlspecialchars($arr["description"]) . "</textarea></td>
		<td>".htmlspecialchars($arr['art'])."</td>
		<td>". (($arr["art"] == "traffic" || $arr["art"] == "traffic2" || $arr["art"] == "gift_1" || $arr["art"] == "gift_2") ? ($arr["menge"] / 1024 / 1024 / 1024) . " GB" : intval($arr["menge"])) ."</td>
		<td align='center'><input type='submit' value='{$lang['bonusmanager_submit']}' /></td>
		</tr></table></div></form>";
		}
		
  function makeithappen($sql){
  global $INSTALLER09;
	$done = sql_query($sql) or sqlerr(__FILE__, __LINE__);
	if($done){
	header("Location: {$INSTALLER09['baseurl']}/admin.php?action=bonusmanage");
	} else {
	stderr($lang['bonusmanager_oops'], "{$lang['bonusmanager_sql']}");
	}
  }
echo stdhead('Bonus Manager') . $HTMLOUT . stdfoot();
?>
