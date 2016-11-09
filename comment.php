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
require_once(INCL_DIR . 'torrenttable_functions.php');
dbconn(false);

loggedinorreturn();

$lang = array_merge(load_language('global'), load_language('comment'));

flood_limit('comments');

$action = (isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '');

/** comment stuffs by pdq **/
$locale      = 'torrent';
$locale_link = 'details';
$extra_link  = '';
$sql_1       = 'name, owner, anonymous FROM torrents'; // , anonymous
$name        = 'name';
$table_type  = $locale . 's';

$_GET['type'] = (isset($_GET['type']) ? htmlspecialchars($_GET['type']) : (isset($_POST['locale']) ? htmlspecialchars($_POST['locale']) : ''));

if (isset($_GET['type'])) {
    $type_options = array(
        'torrent' => 'details',
        'request' => 'viewrequests'
    );
    
    if (isset($type_options[$_GET['type']])) {
        $locale_link = $type_options[$_GET['type']];
        $locale      = $_GET['type'];
    }
    switch ($_GET['type']) {
        case 'request':
            $sql_1      = 'request FROM requests';
            $name       = 'request';
            $extra_link = '&req_details';
            $table_type = $locale . 's';
            break;
        
        default:
            $sql_1      = 'name, owner, anonymous FROM torrents'; // , anonymous
            $name       = 'name';
            $table_type = $locale . 's';
            break;
    }
}
/** end comment stuffs by pdq **/

if ($action == 'add') {
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $id = (isset($_POST['tid']) ? intval($_POST['tid']) : 0);
        
        if (!is_valid_id($id))
            stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
        
        $res = sql_query("SELECT $sql_1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        
        $arr = mysqli_fetch_array($res,  MYSQLI_NUM);
        if (!$arr)
            stderr("{$lang['comment_error']}", "No $locale with that ID.");
        
        $text = (isset($_POST['text']) ? trim(htmlspecialchars($_POST['text'])) : '');
        
        if (!$text)
            stderr("{$lang['comment_error']}", "{$lang['comment_body']}");
        
        $owner            = (isset($arr['owner']) ? intval($arr['owner']) : 0);
        $arr['anonymous'] = (isset($arr['anonymous']) && $arr['anonymous'] == 'yes' ? 'yes' : 'no');
        
        if ($CURUSER['id'] == $owner && $arr['anonymous'] == 'yes' || (isset($_POST['anonymous']) && $_POST['anonymous'] == 'yes'))
            $anon = "'yes'";
        else
            $anon = "'no'";
        
        sql_query("INSERT INTO comments (user, $locale, added, text, ori_text, anonymous) VALUES (" . sqlesc($CURUSER["id"]) . "," . sqlesc($id) . ", " . sqlesc(time()) . ", " . sqlesc($text) . "," . sqlesc($text) . ", " . sqlesc($anon) . ")") or sqlerr(__FILE__, __LINE__);
        
        $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
        
        sql_query("UPDATE $table_type SET comments = comments + 1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        
        
        if ($TBDEV['karma'] && isset($CURUSER['seedbonus']))
            sql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        
        
        header("Refresh: 0; url=$locale_link.php?id=$id$extra_link&viewcomm=$newid#comm$newid");
        die;
    }
    
    $id = (isset($_GET['tid']) ? intval($_GET['tid']) : 0);
    if (!is_valid_id($id))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
    
    $res = sql_query("SELECT $sql_1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    
    $arr = mysqli_fetch_assoc($res);
    
    if (!$arr)
        stderr("{$lang['comment_error']}", "No $locale with that ID.");
    
    $HTMLOUT = '';
    
    $HTMLOUT .= "<h1>{$lang['comment_add']}'" . htmlspecialchars($arr[$name]) . "'</h1>
      <br /><form method='post' action='comment.php?action=add'>
      <input type='hidden' name='tid' value='{$id}'/>
      <input type='hidden' name='locale' value='$name' />";
    
    if ($TBDEV['textbbcode'] && function_exists('textbbcode'))
        $HTMLOUT .= textbbcode("add", "text", "");
    else
        $HTMLOUT .= "<textarea name='text' rows='10' cols='60'></textarea>";
    
    $HTMLOUT .= "<br />
      <label for='anonymous'>Tick this to post anonymously</label>
      <input id='anonymous' type='checkbox' name='anonymous' value='yes' />
      <br /><input type='submit' class='btn' value='{$lang['comment_doit']}' /></form>";
    
    $res = sql_query("SELECT comments.id, text, comments.added, comments.$locale, comments.anonymous, comments.editedby, comments.editedat, username, users.id as user, users.title, users.avatar, users.offavatar, users.av_w, users.av_h, users.class, users.reputation, users.donor, users.warned FROM comments LEFT JOIN users ON comments.user = users.id WHERE $locale = " . sqlesc($id) . " ORDER BY comments.id DESC LIMIT 5") or sqlerr(__FILE__, __LINE__);
    
    $allrows = array();
    while ($row = mysqli_fetch_assoc($res))
        $allrows[] = $row;
    
    if (count($allrows)) {
        require_once(INCL_DIR . 'html_functions.php');
        require_once(INCL_DIR . 'bbcode_functions.php');
        require_once(INCL_DIR . 'torrenttable_functions.php');
        $HTMLOUT .= "<h2>{$lang['comment_recent']}</h2>\n";
        $HTMLOUT .= commenttable($allrows, $locale);
    }
    
    echo stdhead("{$lang['comment_add']}'" . $arr[$name] . "'") . $HTMLOUT . stdfoot();
    die;
} elseif ($action == "edit") {
    
    $commentid = (isset($_GET['cid']) ? intval($_GET['cid']) : 0);
    
    if (!is_valid_id($commentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
    
    $res = sql_query("SELECT c.*, t.$name, t.id as tid FROM comments AS c LEFT JOIN $table_type AS t ON c.$locale = t.id WHERE c.id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    
    $arr = mysqli_fetch_assoc($res);
    
    if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}.");
    
    if ($arr["user"] != $CURUSER["id"] && $CURUSER['class'] < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $text = (isset($_POST['text']) ? htmlspecialchars($_POST['text']) : '');
        
        if ($text == '')
            stderr("{$lang['comment_error']}", "{$lang['comment_body']}");
        
        $editedat = time();
        
        if (isset($_POST['lasteditedby']) || $CURUSER['class'] < UC_MODERATOR)
            sql_query("UPDATE comments SET text=" . sqlesc($text) . ", editedat=" . sqlesc($editedat) . ", editedby=" . sqlesc($CURUSER['id']) . " WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        else
            sql_query("UPDATE comments SET text=" . sqlesc($text) . ", editedat=" . sqlesc($editedat) . ", editedby=0 WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        
        header("Refresh: 0; url=$locale_link.php?id=$arr[tid]$extra_link&viewcomm=$commentid#comm$commentid");
        die;
    }
    
    $HTMLOUT = '';
    $HTMLOUT .= "<h1>{$lang['comment_edit']}'" . htmlspecialchars($arr[$name]) . "'</h1>
      <form method='post' action='comment.php?action=edit&amp;cid=$commentid'>
      <input type='hidden' name='locale' value='$name' />
       <input type='hidden' name='tid' value='" . intval($arr['tid']) . "' />
      <input type='hidden' name='cid' value='$commentid' />";
    
    if ($TBDEV['textbbcode'] && function_exists('textbbcode'))
        $HTMLOUT .= textbbcode("edit", "text", $arr["text"]);
    else
        $HTMLOUT .= "<textarea name='text' rows='10' cols='60'>" . htmlspecialchars($arr["text"]) . "</textarea>";
    
    $HTMLOUT .= '
      <br />' . ($CURUSER['class'] >= UC_MODERATOR ? '<input type="checkbox" value="lasteditedby" checked="checked" name="lasteditedby" id="lasteditedby" /> Show Last Edited By<br /><br />' : '') . ' <input type="submit" class="btn" value="' . $lang['comment_doit'] . '" /></form>';
    
    echo stdhead("{$lang['comment_edit']}'" . htmlspecialchars($arr[$name]) . "'") . $HTMLOUT . stdfoot();
    die;
} elseif ($action == "delete") {
    if ($CURUSER['class'] < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");
    
    $commentid = (isset($_GET['cid']) ? intval($_GET['cid']) : 0);
    $tid       = (isset($_GET['tid']) ? intval($_GET['tid']) : 0);
    if (!is_valid_id($commentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
    
    $sure = isset($_GET["sure"]) ? intval($_GET["sure"]) : false;
    
    if (!$sure) {
        //$referer = $_SERVER["HTTP_REFERER"];
        stderr("{$lang['comment_delete']}", "{$lang['comment_about_delete']}\n" . "<a href='comment.php?action=delete&amp;cid=$commentid&amp;tid=$tid&amp;sure=1" . ($locale == 'request' ? '&amp;type=request' : '') . "'>
          here</a> {$lang['comment_delete_sure']}");
    }
    
    
    $res = sql_query("SELECT $locale FROM comments WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    
    $id = 0;
    if ($arr)
        $id = $arr[$locale];
    
    sql_query("DELETE FROM comments WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    if ($id && mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0)
        sql_query("UPDATE $table_type SET comments = comments - 1 WHERE id = " . sqlesc($id));
    
    if ($TBDEV['karma'] && isset($CURUSER['seedbonus']))
        sql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    
    header("Refresh: 0; url=$locale_link.php?id=$tid$extra_link");
    die;
} elseif ($action == "vieworiginal") {
    if ($CURUSER['class'] < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");
    
    $commentid = (isset($_GET['cid']) ? intval($_GET['cid']) : 0);
    
    if (!is_valid_id($commentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
    
    $res = sql_query("SELECT c.*, t.$name FROM comments AS c LEFT JOIN $table_type AS t ON c.$locale = t.id WHERE c.id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    
    if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']} $commentid.");
    
    $HTMLOUT = '';
    $HTMLOUT .= "<h1>{$lang['comment_original_content']}#$commentid</h1><p>
      <table width='500' border='1' cellspacing='0' cellpadding='5'>
      <tr><td class='comment'>
      " . htmlspecialchars($arr["ori_text"]) . "
      </td></tr></table>";
    
    $returnto = (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 0);
    
    if ($returnto)
        $HTMLOUT .= "<p>(<a href='$returnto'>back</a>)</p>\n";
    
    echo stdhead("{$lang['comment_original']}") . $HTMLOUT . stdfoot();
    die;
} else
    stderr("{$lang['comment_error']}", "{$lang['comment_unknown']}");
die;
?>
