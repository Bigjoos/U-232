<?php 
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
//== 09 Sub forummanage convert :)
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
	echo $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'html_functions.php');

if (!min_class(UC_ADMINISTRATOR)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$INSTALLER09['baseurl']}/index.php");

$lang = array_merge( $lang );

$select='';
$HTMLOUT='';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    mkglobal("subforum:descr:place:minclassread:minclasswrite:minclasscreate");
    if (empty($subforum) || empty($descr) || empty($place))
        stderr("Err", "You missed something !");
    else {
        sql_query("INSERT INTO forums(`name`,`description` ,`minclassread` ,`minclasswrite` ,`minclasscreate`,`place`,`forid`) VALUES(" . join(",", array_map("sqlesc", array($subforum, $descr, $minclassread, $minclasswrite, $minclasscreate, $place, $place))) . ")")or sqlerr(__FILE__, __LINE__);
        if (((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res)) {
            header('Refresh: 3; url='.$INSTALLER09['baseurl'].'/admin.php?action=msubforums');
            stderr("Success", "Forum added");
        } else
            header('Refresh: 2; url='.$INSTALLER09['baseurl'].'/admin.php?action=msubforums');
            stderr("Err", "Something was wrong");
    }
} else {
  
    $HTMLOUT .= begin_frame();
    //== First build the list with all the subforums
    $r_list = sql_query("SELECT f.id as parrentid , f.name as parrentname , f2.id as subid , f2.name as subname, f2.minclassread, f2.minclasswrite, f2.minclasscreate, f2.description FROM forums as f LEFT JOIN forums as f2 ON f2.place=f.id WHERE f2.place !=-1 ORDER BY f.id ASC") or sqlerr(__FILE__, __LINE__);

 
	$HTMLOUT .="<table width='60%' cellpadding='4' cellspacing='0' border='1' align='center' style=' border-collapse:collapse'>
              <tr>
    	        <td width='100%' align='left' rowspan='2' class='colhead'>Subforum</td>
              <td nowrap='nowrap' align='center' rowspan='2' class='colhead'>Parrent forum</td>
              <td colspan='3' align='center' class='colhead'>Permissions</td>
              <td align='center' rowspan='2' class='colhead'>Modify</td>
              </tr>
              <tr>
    	        <td nowrap='nowrap' class='colhead'>read</td>
              <td nowrap='nowrap' class='colhead'>write</td>
              <td nowrap='nowrap' class='colhead'>create</td>
              </tr>";


    while ($a = mysqli_fetch_assoc($r_list)) {

        
		$HTMLOUT .="<tr>
    <td width='100%' align='left' ><a href='{$INSTALLER09['baseurl']}/forums.php?action=viewforum&amp;forumid=".intval($a["subid"])."' >".htmlspecialchars($a["subname"])."</a><br/>".htmlspecialchars($a["description"])."</td>
    <td nowrap='nowrap' align='center'><a href='{$INSTALLER09['baseurl']}/forums.php?action=viewforum&amp;forumid=".intval($a["parrentid"])."' >".htmlspecialchars($a["parrentname"])."</a></td>
    <td nowrap='nowrap'>".(get_user_class_name($a['minclassread']))."</td>
    <td nowrap='nowrap'>".(get_user_class_name($a['minclasswrite']))."</td>
    <td nowrap='nowrap'>".(get_user_class_name($a['minclasscreate']))."</td>
		<td align='center' nowrap='nowrap' ><a href='{$INSTALLER09['baseurl']}/forums.php?action=deleteforum&amp;forumid=".intval($a['subid'])."'>
		<img src='{$INSTALLER09['pic_base_url']}del.png' alt='Delete Forum' title='Delete Forum' style='border:none;padding:2px;' /></a>
		<a href='{$INSTALLER09['baseurl']}/forums.php?action=editforum&amp;forumid=".intval($a['subid'])."'><img src='{$INSTALLER09['pic_base_url']}edit.png' alt='Edit Forum' title='Edit Forum' style='border:none;padding:2px;' /></a></td>
    </tr>";
    }
    
    $HTMLOUT .="</table>";
    $HTMLOUT .= end_frame();
    $HTMLOUT .= begin_frame('Add new subforum');
	  $HTMLOUT .="<form action='admin.php?action=msubforums' method='post'>
	  <table width='60%' cellpadding='4' cellspacing='0' border='1' align='center' style='border-collapse:collapse'>
	  <tr>
		<td align='right' class='colhead'>subforum in</td>
		<td nowrap='nowrap' colspan='3' align='left' >";
    $select .="<select name=\"place\"><option value=\"\">Select</option>\n";
    $r = sql_query("SELECT id,name FROM forums WHERE place=-1 ORDER BY name ASC") or sqlerr(__FILE__, __LINE__);
    while ($ar = mysqli_fetch_assoc($r))
    $select .= "<option value=\"" . intval($ar["id"]) . "\">" . htmlspecialchars($ar["name"]) . "</option>\n";
    $select .= "</select>\n";
    $HTMLOUT .=($select);
    
		$HTMLOUT .="</td>
	  </tr>
	  <tr>
		<td align='right' class='colhead'>Subforum</td>
		<td nowrap='nowrap' colspan='3' align='left' >
		<input type='text' name='subforum' size='60' /></td>
	  </tr>
	  <tr>
		<td align='right' class='colhead'>Description</td>
		<td nowrap='nowrap' colspan='3' align='left'>
		<textarea name='descr' rows='4' cols='60'></textarea></td>
	  </tr>
	  <tr>
		<td align='right' class='colhead'>Permisions</td>
		<td align='center'>";
		$HTMLOUT .="<select name='minclasscreate'>
		<option value='minclasscreate'>Create</option>";
    $maxclass = $CURUSER["class"];
    for ($i = 0; $i <= $maxclass; ++$i)
    $HTMLOUT .="<option value=\"$i\">" . get_user_class_name($i) . "</option>\n";
    $HTMLOUT .=" </select></td>
		<td align='center'><select name='minclasswrite'>
		<option value='minclasswrite'>Write</option>";
    $maxclass = $CURUSER["class"];
    for ($i = 0; $i <= $maxclass; ++$i)
    $HTMLOUT .="<option value=\"$i\">" . get_user_class_name($i) . "</option>\n";
    $HTMLOUT .="</select></td>
	  <td align='center'><select name='minclassread'>
		<option value='minclassread'>Read</option>";
    $maxclass = $CURUSER["class"];
    for ($i = 0; $i <= $maxclass; ++$i)
    $HTMLOUT .="<option value=\"$i\">" . get_user_class_name($i) . "</option>\n";
    $HTMLOUT .="</select></td>
	  </tr>
	  <tr>
	  <td align='center' colspan='4' class='colhead'>
	  <input type='submit' value='add Subforum'/></td></tr>
	  </table>
	  </form>";

    $HTMLOUT .= end_frame();
     echo stdhead("Sub Forum Manage") . $HTMLOUT . stdfoot();
}

?>
