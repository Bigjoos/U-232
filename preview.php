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
require_once(INCL_DIR.'html_functions.php');
dbconn();
loggedinorreturn();

$lang = array_merge( load_language('global'));

$HTMLOUT ="";

$body = trim($_POST["body"]);

$HTMLOUT .= begin_main_frame();

$HTMLOUT .= begin_frame("Preview Post", true);

$HTMLOUT .="<form method='post' action='preview.php'>
<div align='center' style='border: 0;'>
<div align='center'>
<p>".format_comment($body)."</p>
</div>
</div>
<div align='center' style='border: 0;'>
<textarea name='body' cols='100' rows='10'>".htmlspecialchars($body)."</textarea><br />
</div>
<div align='center'>
<input type='submit' class='btn' value='Preview' />
</div></form>";

$HTMLOUT .= end_frame();

$HTMLOUT .= end_main_frame();
echo stdhead('Preview') . $HTMLOUT . stdfoot();
?>
