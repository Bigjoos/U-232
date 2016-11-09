<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
/** sitepot.php by pdq for tbdev.net **/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once(INCL_DIR . 'user_functions.php');
dbconn();
loggedinorreturn();

$lang = array_merge(load_language('global'));

/** Size of Pot**/
$potsize = 6000;

/** Site Pot **/
$Pot_query = sql_query("SELECT value_s, value_i, value_u FROM avps WHERE arg = 'sitepot'") or sqlerr(__FILE__, __LINE__);

$SitePot = mysqli_fetch_assoc($Pot_query) or stderr('ERROR', 'db error.');

if ($SitePot['value_u'] < TIME_NOW && $SitePot['value_s'] == '1') {
    sql_query("UPDATE avps SET value_i = 0, value_s = '0' WHERE arg = 'sitepot'") or sqlerr(__FILE__, __LINE__);
    header('Location: sitepot.php');
    die();
}

if ($SitePot['value_i'] == $potsize)
    stderr('Site Pot is Full', 'Freeleech ends at: ' . get_date($SitePot['value_u'], 'DATE') . ' (' . mkprettytime($SitePot['value_u'] - TIME_NOW) . ' to go).');

$want_pot = (isset($_POST['want_pot']) ? (int) $_POST['want_pot'] : '');

/** Valid amounts can give **/
$pot_options = array(
    1 => 1,
    5 => 5,
    10 => 10,
    25 => 25,
    50 => 50,
    100 => 100,
    500 => 500,
    1000 => 1000,
    2500 => 2500,
    5000 => 5000,
    10000 => 10000,
    50000 => 50000
);

if ($want_pot && (isset($pot_options[$want_pot]))) {
    
    if ($CURUSER['seedbonus'] < $want_pot)
        stderr('Error', 'Not enough karma.');
    
    $give = ($SitePot['value_i'] + $want_pot);
    
    if ($give > $potsize)
        $want_pot = ($potsize - $SitePot['value_i']);
    
    
    if (($SitePot['value_i'] + $want_pot) != $potsize) {
        $Remaining = $potsize - $give;
        
        sql_query("UPDATE users SET seedbonus = seedbonus - " . sqlesc($want_pot) . " 
                     WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        
        
        write_log("Site Pot " . $CURUSER['username'] . " has donated " . $want_pot . " karma points to the site pot. {$Remaining} karma points remaining.");
        
        sql_query("UPDATE avps SET value_i = value_i + " . sqlesc($want_pot) . " 
                     WHERE arg = 'sitepot'") or sqlerr(__FILE__, __LINE__);
        
        /** shoutbox announce **/
        
        require_once(INCL_DIR . 'bbcode_functions.php');
        $msg = $CURUSER['username'] . " put " . $want_pot . " karma point" . ($want_pot > 1 ? 's' : '') . " into the site pot! * Only [b]" . $Remaining . "[/b] more karma point" . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Site Pot:[/b][/color] [url={$TBDEV['baseurl']}/sitepot.php]" . $give . "/" . $potsize . '[/url]';
        autoshout($msg);
        
        
        header('Location: sitepot.php');
        die();
    } elseif (($SitePot['value_i'] + $want_pot) == $potsize) {

        sql_query("UPDATE users SET seedbonus = seedbonus - " . sqlesc($want_pot) . " 
                     WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        
        
        write_log("Site Pot " . $CURUSER['username'] . " has donated " . $want_pot . " karma points to the site pot.");
        
        sql_query("UPDATE avps SET value_i = value_i + " . sqlesc($want_pot) . ", 
                     value_u = '" . (86400 + TIME_NOW) . "', 
                     value_s = '1' WHERE arg = 'sitepot'") or sqlerr(__FILE__, __LINE__);
        write_log("24 HR FREELEECH is now active! It was started on " . get_date(TIME_NOW, 'DATE') . ".");
        
        
        /** shoutbox announce **/
        require_once(INCL_DIR . 'bbcode_functions.php');
        $msg = " [color=green][b]24 HR FREELEECH[/b][/color] is now active! It will end at " . get_date($SitePot['value_u'], 'DATE');
        autoshout($msg);
        header('Location: sitepot.php');
        die();
    } else
        stderr('Error', 'Something strange happened, reload the page and try again.');
    
}

$HTMLOUT = '';
$HTMLOUT .= "<table cellpadding='10' width='70%'>
      <tr><td align='center' colspan='3'>Once the Site Pot has <b>" . $potsize . "</b> karma points, 
      Freeleech will be turned on for everybody for 24 hours. 
      <p align='center'><font size='+1'>
      <b>Site Pot: " . $SitePot['value_i'] . "/" . $potsize . "</b>
      </font></p>You have <b>" . round($CURUSER['seedbonus'], 1) . "</b> karma points.<br />
      </td></tr>";

$HTMLOUT .= '<tr><td><b>Description</b></td><td><b>Amount</b></td><td><b>Exchange</b></td></tr>';

foreach ($pot_options as $Pot_option) {
    if (($CURUSER['seedbonus'] < $Pot_option)) {
        $disabled = 'true';
    } else {
        $disabled = 'false';
    }
    
    $HTMLOUT .= "<tr><td><b>Contribute " . $Pot_option . " Karma Points</b><br /></td>
          <td><strong>" . $Pot_option . "</strong></td>
          <td>
          <form action='' method='post'>
    
   	      <div class=\"buttons\">
	      <input name='want_pot' type='hidden' value='" . $Pot_option . "' />
          <button value='Exchange!' " . ($disabled == 'true' ? "disabled='disabled'" : '') . " type=\"submit\" class=\"positive\">
          <img src=\"pic/plus.gif\" alt=\"\" /> Exchange!
          </button>
          </div>

          </form>
          </td>
          </tr>";
}
$HTMLOUT .= '</table>';
echo stdhead('Site Pot') . $HTMLOUT . stdfoot();
?>
