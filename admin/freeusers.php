<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
/** freeleech mod by pdq for TBDev.net 2009**/
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
	print $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');

if (!min_class(UC_ADMINISTRATOR)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$INSTALLER09['baseurl']}/index.php");

$HTMLOUT = '';

$remove = (isset($_GET['remove']) ? 0 + $_GET['remove'] : 0);

if ($remove)
{

    if (empty($remove))
        die('WTF!');


    $res = sql_query("SELECT id, username, class FROM users WHERE free_switch != 0 AND id = ".
        sqlesc($remove)) or sqlerr(__file__, __line__);
        
    $msgs_buffer = $users_buffer = array();
    
    if (mysqli_num_rows($res) > 0)
    { 
        $msg = sqlesc('Freeleech On All Torrents have been removed by '.$CURUSER['username'].'.');

        while ($arr = mysqli_fetch_assoc($res))
        {
            $modcomment = sqlesc(get_date(time(), 'DATE', 1).
            " - Freeleech On All Torrents removed by ".$CURUSER['username']." \n");
            
			$msgs_buffer[] = '(0,'.sqlesc($arr['id']).','.TIME_NOW.', '.sqlesc($msg).
			', \'Freeleech Notice!\')';
			
            $users_buffer[] = '('.sqlesc($arr['id']).',0,'.$modcomment.')';
            
            $username = htmlspecialchars($arr['username']);
        }
        if (sizeof($msgs_buffer) > 0)
        {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ".
                implode(', ', $msgs_buffer)) or sqlerr(__file__, __line__);
                
            sql_query("INSERT INTO users (id, free_switch, modcomment) VALUES ".
			implode(', ', $users_buffer)." ON DUPLICATE key 
			UPDATE free_switch=values(free_switch), 
			modcomment=concat(values(modcomment),modcomment)") or sqlerr(__file__, __line__);

            write_log("User account $remove ($username) 
			Freeleech On All Torrents have been removed by $CURUSER[username]");
        }
    } else
        die('That User has No Freeleech Status!');

}

$res2 = sql_query("SELECT id, username, class, free_switch FROM users WHERE free_switch != 0 ORDER BY username ASC") or
    sqlerr(__file__, __line__);

$count = mysqli_num_rows($res2);
$HTMLOUT .= "<h1>Freeleech Peeps ($count)</h1>";

if ($count == 0)
   $HTMLOUT .= '<p align="center"><b>Nothing here</b></p>';
else
{
    $HTMLOUT .= "<table border='1' width='50%' cellspacing='0' cellpadding='3'>
          <tr><td class='colhead'>UserName</td><td class='colhead'>Class</td>
          <td class='colhead'>Expires</td><td class='colhead'>Remove Freeleech</td></tr>";
          
    while ($arr2 = mysqli_fetch_assoc($res2))
    {

        $HTMLOUT .= "<tr><td><a href='userdetails.php?id=".intval($arr2['id'])."'>".htmlspecialchars($arr2['username']).
            "</a></td><td align='left'>".get_user_class_name($arr2['class']);
        if ($arr2['class'] > UC_ADMINISTRATOR && $arr2['id'] != $CURUSER['id'])
            $HTMLOUT .= "</td><td align='left'>Until ".get_date($arr2['free_switch'], 'DATE')." 
(".mkprettytime($arr2['free_switch'] - time())." to go)".
                "</td><td align='left'><font color='red'>Not Allowed</font></td>
</tr>";
        else
            $HTMLOUT .= "</td><td align='left'>Until ".get_date($arr2['free_switch'], 'DATE')." 
(".mkprettytime($arr2['free_switch'] - time())." to go)"."</td>
<td align='left'><a href='admin.php?action=freeusers&amp;remove=".(int)$arr2['id']."' onclick=\"return confirm('Are you sure you want to remove this users Freeleech Status?')\">Remove</a></td></tr>";
    }
    $HTMLOUT .= '</table>';
}

    echo stdhead('Freeleech Peeps') . $HTMLOUT . stdfoot();
    die;

?>
