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
dbconn();
loggedinorreturn();
parked();
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@ini_set('zlib.output_compression', 'Off');
@set_time_limit(0);
if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false) { // if output_handler = ob_gzhandler, turn it off and remove the header sent by PHP
    @ob_end_clean();
    header('Content-Encoding:');
}

if (empty($_REQUEST['attachmentid'])) {
    // return not found header
    httperr();
}

$id = (int) $_GET['attachmentid'];

$attachment_dir = ROOT_DIR . "forum_attachments";
$at = sql_query("SELECT * FROM attachments WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$resat    = mysqli_fetch_assoc($at);
$filename = $attachment_dir . '/' . $resat['filename'];

if (!$resat || !is_file($filename) || !is_readable($filename)) {
    // return not found header
    httperr();
}
if ($_GET['action'] == 'delete') {
    if (get_user_class() >= UC_MODERATOR) {
        @unlink($filename);
        sql_query("DELETE FROM attachments WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        sql_query("DELETE FROM attachmentdownloads WHERE fileid=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        die('<font color=\"red\">File successfull deleted...');
    } else {
        httperr();
    }
}
$file_extension = strtolower(substr(strrchr($filename, "."), 1));
switch ($file_extension) {
    case "pdf":
        $ctype = "application/pdf";
        break;
    case "exe":
        $ctype = "application/octet-stream";
        break;
    case "zip":
        $ctype = "application/zip";
        break;
    case "rar":
        $ctype = "application/zip";
        break;
    case "doc":
        $ctype = "application/msword";
        break;
    case "xls":
        $ctype = "application/vnd.ms-excel";
        break;
    case "ppt":
        $ctype = "application/vnd.ms-powerpoint";
        break;
    case "gif":
        $ctype = "image/gif";
        break;
    case "png":
        $ctype = "image/png";
        break;
    case "jpeg":
    case "jpg":
        $ctype = "image/jpg";
        break;
    default:
        $ctype = "application/force-download";
}

sql_query("UPDATE attachments SET downloads = downloads + 1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$res = sql_query("SELECT fileid FROM attachmentdownloads WHERE fileid=" . sqlesc($id) . " AND userid=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res) == "0")
    sql_query("INSERT INTO attachmentdownloads (filename,fileid,username,userid,date,downloads) VALUES (" . sqlesc($resat['filename']) . ", " . sqlesc($id) . ", " . sqlesc($CURUSER['username']) . ", " . sqlesc($CURUSER['id']) . ", " . sqlesc(get_date_time()) . ", 1)") or sqlerr(__FILE__, __LINE__);
else
    sql_query("UPDATE attachmentdownloads SET downloads = downloads + 1 WHERE fileid=" . sqlesc($id) . " AND userid=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false); // required for certain browsers
header("Content-Type: $ctype");
// change, added quotes to allow spaces in filenames, by Rajkumar Singh
header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($filename));
readfile("$filename");
exit();

?>
