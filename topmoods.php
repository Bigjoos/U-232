<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'user_functions.php');
require_once(INCL_DIR . 'bbcode_functions.php');
require_once(INCL_DIR . 'mood.php');
dbconn(false);
loggedinorreturn();

$lang = array_merge(load_language('global'));

$HTMLOUT = "";

$HTMLOUT .= "<table><tr><td class='embedded'>
<small>You may select your mood by clicking on the smiley on forum posts.</small></td></tr></table>";

$query1 = "SELECT mood, COUNT(mood) as moodcount FROM users GROUP BY mood ORDER BY moodcount DESC";
$res = sql_query($query1) or sqlerr(__FILE__, __LINE__);

$HTMLOUT = "<h2>Top Moods</h2>" . "    <table border='1' cellspacing='0' cellpadding='5'>" . "<tr><td class='colhead' align='center'>Count</td><td class='colhead' align='center'>Mood</td><td class='colhead' align='center'>Icon</td></tr>\n";
while ($arr = mysqli_fetch_assoc($res)) {
    foreach ($mood as $key => $value)
        $change[$value['id']] = array(
            'id' => $value['id'],
            'name' => $value['name'],
            'image' => $value['image']
        );
    $mooduname = htmlspecialchars($change[$arr['mood']]['name']);
    $moodupic  = htmlspecialchars($change[$arr['mood']]['image']);
    $moodcount = 0 + $arr['moodcount'];
    
    $HTMLOUT .= "<tr><td align='center'>" . $moodcount . "</td><td align='center'>" . $mooduname . "</td><td align='center'><img src='" . $INSTALLER09['pic_base_url'] . "smilies/" . $moodupic . "' border='0' alt='" . $mooduname . "'  title='" . $mooduname . "'/></td></tr>\n";
}

$HTMLOUT .= "</table>\n";

echo stdhead('User Moods') . $HTMLOUT . stdfoot();
?>
