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
	print $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');


    $lang = array_merge( $lang, load_language('ad_bans') );
    
    $doUpdate = false;

    $remove = isset($_GET['remove']) ? (int)$_GET['remove'] : 0;
    if (is_valid_id($remove))
    {
      sql_query("DELETE FROM bans WHERE id=".sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
      $removed = sprintf($lang['text_banremoved'], $remove);
      write_log("{$removed}".$CURUSER['id']." (".$CURUSER['username'].")");
      $doUpdate = true;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && $CURUSER['class'] >= UC_ADMINISTRATOR)
    {
      //we doing just a cache rewrite or an add & rewrite?
      if(isset($_POST['cacheit'])) 
      {
        $doUpdate = true;
      }
      else
      {
        $first = trim($_POST["first"]);
        $last = trim($_POST["last"]);
        $comment = trim($_POST["comment"]);
        if (!$first || !$last || !$comment)
          stderr("{$lang['stderr_error']}", "{$lang['text_missing']}");
        $first = ip2long($first);
        $last = ip2long($last);
        if ($first == -1 || $first === FALSE || $last == -1 || $last === FALSE)
          stderr("{$lang['stderr_error']}", "{$lang['text_badip.']}");
        $comment = sqlesc($comment);
        $added = sqlesc(time());

        sql_query("INSERT INTO bans (added, addedby, first, last, comment) 
                      VALUES($added, ".sqlesc($CURUSER['id']).", ".sqlesc($first).", ".sqlesc($last).", $comment)") or sqlerr(__FILE__, __LINE__);
        $doUpdate = true;
        //header("Location: {$INSTALLER09['baseurl']}/bans.php");
        //die;
      }
    }



    $res = sql_query("SELECT b.*, u.username FROM bans b LEFT JOIN users u on b.addedby = u.id ORDER BY added DESC") or sqlerr(__FILE__,__LINE__);

    $configfile="<"."?php\n\n\$bans = array(\n";

    $HTMLOUT = '';
    

    $HTMLOUT .= "<h1>{$lang['text_current']}</h1>\n";

    if (mysqli_num_rows($res) == 0)
    {
      $HTMLOUT .= "<p align='center'><b>{$lang['text_nothing']}</b></p>\n";
    }
    else
    {
      $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>\n";
      $HTMLOUT .= "<tr>
        <td class='colhead'>{$lang['header_added']}</td><td class='colhead' align='left'>{$lang['header_firstip']}</td>
        <td class='colhead' align='left'>{$lang['header_lastip']}</td>
        <td class='colhead' align='left'>{$lang['header_by']}</td>
        <td class='colhead' align='left'>{$lang['header_comment']}</td>
        <td class='colhead'>{$lang['header_remove']}</td>
      </tr>\n";
        


      while ($arr = mysqli_fetch_assoc($res))
      {
        if($doUpdate) 
        {
          $configfile .= "array('id'=> '{$arr['id']}', 'first'=> {$arr['first']}, 'last'=> {$arr['last']}),\n";
        }
        
        $arr["first"] = long2ip($arr["first"]);
        $arr["last"] = long2ip($arr["last"]);
        
        $HTMLOUT .= "<tr>
          <td>".get_date($arr['added'],'')."</td>
          <td align='left'>".htmlspecialchars($arr['first'])."</td>
          <td align='left'>".htmlspecialchars($arr['last'])."</td>
          <td align='left'><a href='userdetails.php?id=".intval($arr['addedby'])."'>".htmlspecialchars($arr['username'])."</a></td>
          <td align='left'>".htmlentities($arr['comment'], ENT_QUOTES)."</td>
          <td><a href='admin.php?action=bans&amp;remove=".intval($arr['id'])."'>{$lang['text_remove']}</a></td>
         </tr>\n";
      }
      
      $HTMLOUT .= "</table>\n";
      
    }

    if($doUpdate) 
    {
      $configfile .= "\n);\n\n?".">";
      $filenum = fopen ("cache/bans_cache.php","w");
      ftruncate($filenum, 0);
      fwrite($filenum, $configfile);
      fclose($filenum);
    }
          
    if ($CURUSER['class'] >= UC_ADMINISTRATOR)
    {
      $HTMLOUT .= "<h2>{$lang['text_addban']}</h2>
      <form method='post' action='admin.php?action=bans'>
      <table border='1' cellspacing='0' cellpadding='5'>
        <tr>
          <td class='rowhead'>{$lang['table_firstip']}</td>
          <td><input type='text' name='first' size='40' /></td>
        </tr>
        <tr>
          <td class='rowhead'>{$lang['table_lastip']}</td>
          <td><input type='text' name='last' size='40' /></td>
        </tr>
        <tr>
          <td class='rowhead'>{$lang['table_comment']}</td><td><input type='text' name='comment' size='40' /></td>
        </tr>
        <tr>
          <td colspan='2' align='center'><input type='submit' name='okay' value='{$lang['btn_add']}' class='btn' /></td>
        </tr>
        <tr>
          <td colspan='2' align='center'><input type='submit' name='cacheit' value='{$lang['btn_cache']}' class='btn' /></td>
        </tr>
      </table>
      </form>";
      
    }

    echo stdhead("{$lang['stdhead_adduser']}") . $HTMLOUT . stdfoot();

?>
