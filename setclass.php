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
dbconn(false);

loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('setclass') );
$HTMLOUT ="";
// The following line may need to be changed to UC_MODERATOR if you don't have Forum Moderators
if ($CURUSER['class'] < UC_MODERATOR) die(); // No acces to below this rank
if ($CURUSER['override_class'] != 255) die(); // No access to an overridden user class either - just in case

if (isset($_GET["action"]) && $_GET["action"] == "editclass") //Process the querystring - No security checks are done as a temporary class higher
{	
//then the actual class mean absoluetly nothing.
$newclass = 0 + $_GET['class'];
$returnto = $_GET['returnto'];
sql_query("UPDATE users SET override_class = ".sqlesc($newclass)." WHERE id = ".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__); // Set temporary class
header("Location: {$INSTALLER09['baseurl']}/".$returnto);
die();
}

// HTML Code to allow changes to current class

$HTMLOUT .="<br />
<font size='4'><b>{$lang['set_class_allow']}</b></font>
<br /><br />
<form method='get' action='{$INSTALLER09['baseurl']}/setclass.php'>
	<input type='hidden' name='action' value='editclass' />
	<input type='hidden' name='returnto' value='userdetails.php?id=".intval($CURUSER['id'])."' />
	<table width='150' border='2' cellspacing='5' cellpadding='5'>
	<tr>
	<td>Class</td>
	<td align='left'>
	<select name='class'>";
		 
		$maxclass = $CURUSER['class'] - 1;
		for ($i = 0; $i <= $maxclass; ++$i)
		if (trim(get_user_class_name($i)) != "") 
		$HTMLOUT .="<option value='$i" .  "'>" . get_user_class_name($i) . "</option>\n";
		$HTMLOUT .="</select></td></tr>
		<tr><td colspan='3' align='center'><input type='submit' class='btn' value='{$lang['set_class_ok']}' /></td></tr>
	</table>
</form>
<br />";

echo stdhead("{$lang['set_class_temp']}") . $HTMLOUT . stdfoot();
?>
