<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
$foo = array(
'Database' =>array(
array('text'=>'Host','input'=>'mysql_host','info'=>'Usually this will be localhost.'),
array('text'=>'Username','input'=>'mysql_user','info'=>'Your mysql username.'),
array('text'=>'Password','input'=>'mysql_pass','info'=>'Your mysql password.'),
array('text'=>'Database','input'=>'mysql_db','info'=>'Your mysql database name.'),
array('text'=>'Announce Url','input'=>'announce_urls','info'=>'Your announce url.'),
array('text'=>'Site Email','input'=>'site_email','info'=>'Your site email address.'),
array('text'=>'Site Name','input'=>'site_name','info'=>'Your site name.'),
),
'Cookies'=>array(
array('text'=>'Prefix','input'=>'cookie_prefix','info'=>'Only required for sub-domain installs.'),
array('text'=>'Path','input'=>'cookie_path','info'=>'Only required for sub-domain installs.'),
array('text'=>'Domain','input'=>'cookie_domain','info'=>'Your domain name - note exclude http and www.'),
)
);

function foo($x)  {
	return '/\#'.$x.'/';
}

function createblock($fo,$foo) {
	$out = '
	<fieldset>
		<legend>'.$fo.'</legend>
		<table align="center">';
	foreach($foo as $bo)
	$out .= '<tr>
			<td class="input_text">'.$bo['text'].'</td>
			<td class="input_input"><input type="text" name="install['.$bo['input'].']" size="30"/></td>
			<td class="input_info">'.$bo['info'].'</td>
		  </tr>';
	$out .= '</table></fieldset>';
	return $out;
}

function printr($x) {
	print('<pre>'.print_r($x,1));
}
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$config = file_get_contents('config.sample.php');
	$keys   = array_map('foo',array_keys($_POST['install']));
	$values = array_values($_POST['install']);
	$config = preg_replace($keys,$values,$config);
	file_put_contents('../include/config.php',$config);
}

$foo1 = array(
'Announce'=>array(
array('text'=>'Username','input'=>'mysql_user','info'=>'Your mysql username.'),
array('text'=>'Password','input'=>'mysql_pass','info'=>'Your mysql password.'),
array('text'=>'Database','input'=>'mysql_db','info'=>'Your mysql database name.'),
array('text'=>'Domain','input'=>'baseurl','info'=>'Your domain name - note include http and www.'),
)
);

function foo1($x)  {
	return '/\#'.$x.'/';
}

function createblock1($fo1,$foo1) {
	$out = '
	<fieldset>
		<legend>'.$fo1.'</legend>
		<table align="center">';
	foreach($foo1 as $bo1)
	$out .= '<tr>
			<td class="input_text">'.$bo1['text'].'</td>
			<td class="input_input"><input type="text" name="install['.$bo1['input'].']" size="30"/></td>
			<td class="input_info">'.$bo1['info'].'</td>
		  </tr>';
	$out .= '</table></fieldset>';
	return $out;
}

function printr1($x) {
	print('<pre>'.print_r1($x,1));
}
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$announce = file_get_contents('announce.sample.php');
	$keys   = array_map('foo1',array_keys($_POST['install']));
	$values = array_values($_POST['install']);
	$announce = preg_replace($keys,$values,$announce);
	file_put_contents('../announce.php',$announce);
}

function basic_query()
{
	$sql_lines = implode(' ', file(dirname(__FILE__) . '/install.sql'));
	$sql_lines = explode("\n", $sql_lines);
	require_once('../include/config.php');
	if( !($GLOBALS["___mysqli_ston"] = mysqli_connect($INSTALLER09['mysql_host'], $INSTALLER09['mysql_user'], $INSTALLER09['mysql_pass'])) )
	{
	die('Cant connect to databaseserver');
	}
	if( !((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE {$INSTALLER09['mysql_db']}")) )
	{
	die('Cant select database');
	}

	// Execute the SQL.
	$current_statement = '';
	$failures = array();
	$exists = array();
	foreach ($sql_lines as $count => $line)
	{
		// No comments allowed!
		if (substr($line, 0, 1) != '#')
			$current_statement .= "\n" . rtrim($line);
		// Is this the end of the query string?
		if (empty($current_statement) || (preg_match('~;[\s]*$~s', $line) == 0 && $count != count($sql_lines)))
			continue;
		// Does this table already exist?  If so, don't insert more data into it!
		if (preg_match('~^\s*INSERT INTO ([^\s\n\r]+?)~', $current_statement, $match) != 0 && in_array($match[1], $exists))
		{
			$current_statement = '';
			continue;
		}

if (!mysqli_query($GLOBALS["___mysqli_ston"], $current_statement))
{
 $error_message = 'wooOOpsie';
			if (strpos($error_message, 'already exists') === false)
				$failures[$count] = $error_message;
			elseif (preg_match('~^\s*CREATE TABLE ([^\s\n\r]+?)~', $current_statement, $match) != 0)
				$exists[] = $match[1];
		}

		$current_statement = '';
	}
}
function basic_chmod()
{
   $ehome = file_exists("".ROOT_PATH ."/index.php"); 
   $econfig = file_exists("".ROOT_PATH ."/include/config.php"); 
   $eannounce = file_exists("".ROOT_PATH ."/announce.php"); 
   $estats = file_exists("".ROOT_PATH ."/admin/stats.php"); 
   $etbsql = file_exists("".ROOT_PATH ."/install/install.sql");
   $wconfig = is_writable("".ROOT_PATH ."/install/config.sample.php"); 
   $wannounce = is_writable("".ROOT_PATH ."/install/announce.sample.php"); 
   $wtors = is_writable("".ROOT_PATH ."/torrents"); 
   $wcache = is_writable("".ROOT_PATH ."/cache"); 
   $wlogs = is_writable("".ROOT_PATH ."/logs");
   
   echo "<fieldset>
    <table align='left'>
      <tr>
        <td>
          <h2>Random Checks</h2>
          Exists: index.php <div class='".($ehome ? "ok":"fail")."'></div><br />
          Exists: announce.php <div class='".($eannounce ? "ok":"fail")."'></div><br />
          Exists: include/config.php <div class='".($econfig ? "ok":"fail")."'></div><br />
          Exists: admin/stats.php <div class='".($estats ? "ok":"fail")."'></div><br />
          Exists: install/install.sql <div class='".($etbsql ? "ok":"fail")."'></div><br /><br />
          Writable: install/announce.sample.php <div class='".($wannounce ? "ok":"fail")."'></div><br />
          Writable: install/config.sample.php <div class='".($wconfig ? "ok":"fail")."'></div><br />
          Writable: torrents <div class='".($wtors ? "ok":"fail")."'></div><br />
          Writable: cache <div class='".($wcache ? "ok":"fail")."'></div><br />
          Writable: logs <div class='".($wlogs ? "ok":"fail")."'></div><br /></td>
      </tr>
    </table>
    </fieldset>";
          }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Installer 09</title>
<style type="text/css">
body {
	background:#dadada;
	font-family: "Trebuchet MS";
	color:#666666;
	font-size:12px;
	margin:0;
	padding:0;
}
#wrapper {
	margin:50px auto;
	width:600px;
	position:relative;
}
#wrapper1 {
	margin:50px auto;
	width:600px;
	position:relative;
}
fieldset, legend {
	border:1px solid #222;
	-moz-border-radius:5px;
	border-bottom-width:2px;
}
fieldset {
	margin-bottom:10px;
}
legend {
	font-weight:bold;
	font-size:14px;
}
.input_text {
	white-space:nowrap;
	text-align:right;
	font-weight:bold;
	vertical-align:top;
}
td.input_input input {
	-moz-border-radius:3px;
	padding:2px;
	border:1px #ccc solid;
	vertical-align:top;
}
.input_info, tr:hover .input_info {
	width:100%;
	visibility:hidden;
}
tr:hover .input_info {
	visibility:visible;
}
.ok { 
display: inline-block; 
margin: 0px; 
padding: 0px; 
clear: none; 
height: 16px; 
width: 16px; 
background-image: url(../pic/okay.png); 
}
.fail { 
display: inline-block; 
margin: 0px; 
padding: 0px; 
clear: none; 
height: 16px; 
width: 16px; 
background-image: url(../pic/no.png); 
}
</style>
</head>
<body>
<img src='installer09_logo.png' alt='' />
<div id="wrapper">
<form action="index.php" method="post">
<?php
foreach($foo as $fo=>$fooo)
print(createblock($fo,$fooo));

foreach($foo1 as $fo1=>$fooo1)
print(createblock($fo1,$fooo1));

basic_chmod();

if (isset($_POST['install'])) {
if ($_POST['install'] || $_GET['install']) {
basic_query();
}
?>
<fieldset>
<table align="center">
<tr>
<td>
<h2>Install completed all tasks successfully !!</h2><br />
<font size="2">You may now login</font><br /><br />
<a href="../login.php"><font size="3" color="#ff0000">HERE</font></a></td></tr></table></fieldset>
<?php
}

?>
<fieldset>
<table align="center">
<tr>
<td><input type="submit" value="Save"  /></td></tr></table></fieldset></form>
</div>
</body>
</html>
