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
dbconn();
loggedinorreturn();
$lang = array_merge(load_language('global'), load_language('getrss'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    function mkint($x)
    {
        return (int) $x;
    }
    $cats = isset($_POST['cats']) ? array_map('mkint', $_POST['cats']) : array();
    if (count($cats) == 0)
        stderr($lang['getrss_error'], $lang['getrss_nocat']);
    $feed = isset($_POST['feed']) && htmlspecialchars($_POST['feed']) == 'dl' ? 'dl' : 'web';
    
    $rsslink = $INSTALLER09['baseurl'] . '/rss.php?cats=' . join(',', $cats) . ($feed == 'dl' ? '&amp;type=dl' : '') . '&amp;passkey=' . $CURUSER['passkey'];
    $HTMLOUT = "<div align=\"center\"><h2>{$lang['getrss_result']}</h2><br/>
		<input type=\"text\" size=\"120\" readonly=\"readonly\" value=\"{$rsslink}\" onclick=\"select()\" />
	</div>";
    
    echo (stdhead($lang['getrss_head2']) . $HTMLOUT . stdfoot());
    
} else {
    $HTMLOUT = <<<HTML
<form action="{$_SERVER['PHP_SELF']}" method="post">
<table width="500" cellpadding="2" cellspacing="0" align="center">
<tr>
	<td colspan="2" align="center" class="colhead">{$lang['getrss_title']}</td>
</tr>
<tr>
	<td align="right" valign="top">{$lang['getrss_cat']}</td><td align="left" width="100%">
HTML;
    $q1 = sql_query('SELECT id,name,image FROM categories order by id') or sqlerr(__FILE__, __LINE__);
    $i = 0;
    while ($a = mysqli_fetch_assoc($q1)) {
        if ($i % 5 == 0 && $i > 0)
            $HTMLOUT .= "<br/>";
        $HTMLOUT .= "<label for=\"cat_{$a['id']}\"><img src=\"{$INSTALLER09['pic_base_url']}caticons/" . htmlspecialchars($a['image']) . "\" alt=\"" . htmlspecialchars($a['name']) . "\" title=\"" . htmlspecialchars($a['name']) . "\" /><input type=\"checkbox\" name=\"cats[]\" id=\"cat_" . intval($a['id']) . "\" value=\"" . intval($a['id']) . "\" /></label>\n";
        $i++;
    }
    $HTMLOUT .= <<<HTML
</td>
</tr>
<tr>
	<td align="right">{$lang['getrss_feed']}</td><td align="left"><input type="radio" checked="checked" name="feed" id="std" value="web"/><label for="std">{$lang['getrss_web']}</label><br/><input type="radio" name="feed" id="dl" value="dl"/><label for="dl">{$lang['getrss_dl']}</label></td>
 </tr>
 <tr><td colspan="2" align="center"><input type="submit" value="{$lang['getrss_btn']}" /></td></tr>
</table>
</form>
HTML;
    
    echo (stdhead($lang['getrss_head2']) . $HTMLOUT . stdfoot());
}
?>
