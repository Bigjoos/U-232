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
require_once(INCL_DIR.'bbcode_functions.php');
dbconn();
loggedinorreturn();

$HTMLOUT='';


$lang = array_merge( load_language('global'));

$fileid = (int)$_GET['fileid'];

$res = sql_query("SELECT * FROM attachmentdownloads WHERE fileid=" . sqlesc($fileid."")) or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    die("Nothing found!");
else {
    
    $HTMLOUT.="<table border='1' width='100%' cellspacing='0' cellpadding='2'>
    <tr align='center'><td class='colhead' align='center'>File ID</td>
    <td class='colhead' align='center'>Filename</td>
    <td class='colhead' align='center'>Downloaded from</td>
    <td class='colhead' align='center'>Downloads</td>
    <td class='colhead' align='center'>Date</td></tr>\n";
    while ($arr = mysql_fetch_assoc($res)) {
    $HTMLOUT.="<tr><td align='center'>".$arr["fileid"]."</td><td align='center'>
    " . htmlspecialchars($arr["filename"]) . "</td>
    <td align='center'><a href=\"#\" onclick=\"opener.location=('userdetails.php?id=".$arr["userid"]."'); self.close();\">".$arr["username"]."</a></td>
    <td align='center'>".$arr["downloads"]."</td><td align='center'>".get_date($arr["date"], 'LONG',1,0)."</td></tr>";
    }
    $res = sql_query("SELECT downloads FROM attachments WHERE id=" . sqlesc($fileid."")) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    $HTMLOUT.="<tr><td colspan='5'><div class='error'><font color='blue'>Total Downloads: ".$arr["downloads"]."</font></div></td</tr>";
    $HTMLOUT.="</table>\n";
}
print stdhead("Who Downloaded") . $HTMLOUT . stdfoot();
?>
