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
require_once INCL_DIR.'html_functions.php';
require_once INCL_DIR.'bbcode_functions.php';
require_once INCL_DIR.'page_verify.php';
dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('upload') );
    $newpage = new page_verify(); 
    $newpage->create('taud');
    $HTMLOUT = '';
    $HTMLOUT .="<script type=\"text/javascript\" src=\"./scripts/shout.js\"></script>";
    
   if ($CURUSER['class'] < UC_USER OR $CURUSER["uploadpos"] == 0|| $CURUSER["uploadpos"] > 1 )
   stderr($lang['upload_sorry'], $lang['upload_no_auth']);
   
    $HTMLOUT .= "<div align='center'>
    <form name='compose' enctype='multipart/form-data' action='./takeupload.php' method='post'>
    <input type='hidden' name='MAX_FILE_SIZE' value='{$TBDEV['max_torrent_size']}' />
    <p>{$lang['upload_announce_url']} <b>{$TBDEV['announce_urls'][0]}</b></p>";


    $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='10'>
    <tr>
    <td class='heading' valign='top' align='right'>{$lang['upload_imdb_url']}</td>
    <td valign='top' align='left'><input type='text' name='url' size='80' /><br />{$lang['upload_imdb_tfi']}{$lang['upload_imdb_rfmo']}</td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right'>{$lang['upload_poster']}</td>
      <td valign='top' align='left'><input type='text' name='poster' size='80' /><br />{$lang['upload_poster1']}</td>
      </tr>
    <tr>
      <td class='heading' valign='top' align='right'>{$lang['upload_torrent']}</td>
      <td valign='top' align='left'><input type='file' name='file' size='80' /></td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right'>{$lang['upload_name']}</td>
      <td valign='top' align='left'><input type='text' name='name' size='80' /><br />({$lang['upload_filename']})</td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right'>{$lang['upload_nfo']}</td>
      <td valign='top' align='left'><input type='file' name='nfo' size='80' /><br />({$lang['upload_nfo_info']})</td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right'>{$lang['upload_description']}</td>
      <td valign='top' align='left'>". textbbcode("compose","descr")."
      <br />({$lang['upload_html_bbcode']})</td>
    </tr>";

    $s = "<select name='type'>\n<option value='0'>({$lang['upload_choose_one']})</option>\n";

    $cats = genrelist();
    
    foreach ($cats as $row)
    {
      $s .= "<option value='{$row["id"]}'>" . htmlspecialchars($row["name"]) . "</option>\n";
    }
    
    $s .= "</select>\n";
    $HTMLOUT .= tr("{$lang['upload_anonymous']}", "<input type='checkbox' name='uplver' value='yes' />{$lang['upload_anonymous1']}", 1);
    $HTMLOUT .= tr("{$lang['upload_comment']}", "<input type='checkbox' name='allow_commentd' value='yes' />{$lang['upload_discom1']}", 1);
    $HTMLOUT .= tr("Strip ASCII", "<input type='checkbox' name='strip' value='strip' checked='checked' /><a href='http://en.wikipedia.org/wiki/ASCII_art' target='_blank'>What is this ?</a>", 1);
    $HTMLOUT .= "<tr>
        <td class='heading' valign='top' align='right'>{$lang['upload_type']}</td>
        <td valign='top' align='left'>$s</td>
      </tr>";
      
      if ($CURUSER['class'] >= UC_UPLOADER){
      $HTMLOUT .= "<tr>
        <td class='heading' valign='top' align='right'>Free Leech</td>
        <td valign='top' align='left'>
    <select name='free_length'>
    <option value='0'>Not Free</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select></td>
      </tr>";
      }

      $HTMLOUT .= "<tr>
        <td align='center' colspan='2'><input type='submit' class='btn' value='{$lang['upload_submit']}' /></td>
      </tr>
    </table>
    </form>
    </div>";
   
////////////////////////// HTML OUTPUT //////////////////////////

    print stdhead($lang['upload_stdhead']) . $HTMLOUT . stdfoot();

?>