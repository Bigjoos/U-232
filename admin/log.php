<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
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
	echo $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');
  
    $lang = array_merge( $lang, load_language('ad_log') );
    
    // delete items older than a week
    $secs = 24 * 60 * 60;
    
    sql_query("DELETE FROM sitelog WHERE " . time() . " - added > $secs") or sqlerr(__FILE__, __LINE__);
    
    $res = sql_query("SELECT added, txt FROM sitelog ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);
    
    $HTMLOUT = "<h1>{$lang['text_sitelog']}</h1>\n";
    
    if (mysqli_num_rows($res) == 0)
    {
      $HTMLOUT .= "<b>{$lang['text_logempty']}</b>\n";
    }
    else
    {
      $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>
      <tr>
        <td class='colhead' align='left'>{$lang['header_date']}</td>
        <td class='colhead' align='left'>{$lang['header_time']}</td>
        <td class='colhead' align='left'>{$lang['header_event']}</td>
      </tr>\n";
      
      while ($arr = mysqli_fetch_assoc($res))
      {
        $date = explode( ',', get_date( $arr['added'], 'LONG' ) );
        $HTMLOUT .= "<tr><td>{$date[0]}</td>
        <td>{$date[1]}</td>
        <td align='left'>".htmlentities($arr['txt'], ENT_QUOTES)."</td>
        </tr>\n";
      }
      
      $HTMLOUT .= "</table>\n";
    }
    $HTMLOUT .= "<p>{$lang['text_times']}</p>\n";
    
    echo stdhead("{$lang['stdhead_log']}") . $HTMLOUT . stdfoot();

?>
