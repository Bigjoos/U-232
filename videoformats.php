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

    $lang = array_merge( load_language('global'), load_language('videoformats') );
    
    $HTMLOUT = '';
    
    $HTMLOUT .= "<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
    {$lang['videoformats_body']}
    </td></tr></table>
    </td></tr></table>
    <br />";

    print stdhead("{$lang['videoformats_header']}") . $HTMLOUT . stdfoot();
?>