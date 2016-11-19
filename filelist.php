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
require_once(INCL_DIR.'html_functions.php');
dbconn(false);

loggedinorreturn();
$lang = array_merge( load_language('global'), load_language('filelist') );
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if (!is_valid_id($id))
      stderr('USER ERROR', 'Bad id');

    $HTMLOUT = '';
		$HTMLOUT .= "<a name='top'></a><table class='main' border='1' cellspacing='0' cellpadding='5'>\n";
		$subres = sql_query("SELECT * FROM files WHERE torrent = ".sqlesc($id)." ORDER BY id") or sqlerr(__FILE__, __LINE__);
                $HTMLOUT .= "<tr><td class='colhead'>{$lang["filelist_type"]}</td><td class='colhead'>{$lang["filelist_path"]}</td><td class='colhead' align='right'>{$lang["filelist_size"]}</td></tr>\n";
		  $counter = 0;
			while ($subrow = mysqli_fetch_assoc($subres)) {
			preg_match('/\\.([A-Za-z0-9]+)$/', $subrow["filename"], $ext);
			$ext = strtolower($ext[1]);
			if (!file_exists("pic/icons/".$ext.".png")) $ext = "Unknown";
			if($counter !== 0 && $counter % 10 == 0)
			$HTMLOUT .= "<tr><td colspan='2' align='right'><a href='#top'><img src='{$INSTALLER09['pic_base_url']}/top.gif' alt='' /></a></td></tr>";
			$HTMLOUT .= "<tr><td><img src='pic/icons/".htmlspecialchars($ext).".png' alt='".htmlspecialchars($ext)." file' title='".htmlspecialchars($ext)." file' /></td><td>" . htmlspecialchars($subrow["filename"]) ."</td><td align='right'>" . mksize($subrow["size"]) . "</td></tr>\n";
			$counter++;
			}
			$HTMLOUT .= "</table>\n";


    echo stdhead($lang["filelist_header"]) . $HTMLOUT . stdfoot();
?>
