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

$lang = array_merge(load_language('global'), load_language('coins'));

$HTMLOUT = "";

if (!$TBDEV['coins'])
    stderr($lang['err'], $lang['nocoin']);

$FREE['id']    = 1;
$FREE['name']  = "1 day freeleech";
$FREE['desc']  = "Allows you to download for one day free";
$FREE['prize'] = 7500;

$FREE2['id']    = 2;
$FREE2['name']  = "1 day freeleech";
$FREE2['desc']  = "Allows you to download for one day free";
$FREE2['prize'] = 7500;

$FREE3['id']    = 3;
$FREE3['name']  = "1 day freeleech";
$FREE3['desc']  = "Allows you to download for one day free";
$FREE3['prize'] = 7500;

$FREE4['id']    = 4;
$FREE4['name']  = "1 day freeleech";
$FREE4['desc']  = "Allows you to download for one day free";
$FREE4['prize'] = 7500;

$prizes = array(
    $FREE,
    $FREE2,
    $FREE3,
    $FREE4
);

function kaupa($PRI)
{
    global $CURUSER, $lang;
    if ($PRI > $CURUSER['coins']) {
        stderr($lang['shop_error'], $lang['shop_notenn']);
    } else {
        sql_query("UPDATE users SET coins=coins-" . sqlesc($PRI) . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
}

if (isset($_GET['kaupa'])) {
    $id = intval($_GET['kaupa']);
    if ($id == 1) {
        if ($CURUSER["class"] >= UC_VIP)
            kaupa($FREE['prize'] / 2);
        else
            kaupa($FREE['prize']);
        $free_switch = (1 * 86400 + time());
        if ($CURUSER['free_switch'] != 0)
            stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
        sql_query("UPDATE users SET free_switch=" . sqlesc($frees_switch) . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $HTMLOUT .= "<h2>" . $FREE['name'] . " {$lang['shop_boughed']}</h2>";
    } else {
        stderr($lang['shop_error'], $lang['shop_noprize']);
    }
}

if (isset($_GET['kaupa'])) {
    $id = intval($_GET['kaupa']);
    if ($id == 2) {
        if ($CURUSER["class"] >= UC_VIP)
            kaupa($FREE2['prize'] / 2);
        else
            kaupa($FREE2['prize']);
        $free_switch = (1 * 86400 + time());
        if ($CURUSER['free_switch'] != 0)
            stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
        sql_query("UPDATE users SET free_switch=" . sqlesc($frees_switch) . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $HTMLOUT .= "<h2>" . $FREE['name'] . " {$lang['shop_boughed']}</h2>";
    } else {
        stderr($lang['shop_error'], $lang['shop_noprize']);
    }
}

if (isset($_GET['kaupa'])) {
    $id = intval($_GET['kaupa']);
    if ($id == 3) {
        if ($CURUSER["class"] >= UC_VIP)
            kaupa($FREE3['prize'] / 2);
        else
            kaupa($FREE3['prize']);
        $free_switch = (1 * 86400 + time());
        if ($CURUSER['free_switch'] != 0)
            stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
        sql_query("UPDATE users SET free_switch=" . sqlesc($frees_switch) . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $HTMLOUT .= "<h2>" . $FREE['name'] . " {$lang['shop_boughed']}</h2>";
    } else {
        stderr($lang['shop_error'], $lang['shop_noprize']);
    }
}

if (isset($_GET['kaupa'])) {
    $id = intval($_GET['kaupa']);
    if ($id == 4) {
        if ($CURUSER["class"] >= UC_VIP)
            kaupa($FREE4['prize'] / 2);
        else
            kaupa($FREE4['prize']);
        $free_switch = (1 * 86400 + time());
        if ($CURUSER['free_switch'] != 0)
            stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
        sql_query("UPDATE users SET free_switch=" . sqlesc($frees_switch) . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $HTMLOUT .= "<h2>" . $FREE['name'] . " {$lang['shop_boughed']}</h2>";
    } else {
        stderr($lang['shop_error'], $lang['shop_noprize']);
    }
}


if ($CURUSER["class"] >= UC_VIP)
    $HTMLOUT .= "{$lang['fifty_off']} Under construction !!!<br />";

$HTMLOUT .= "
		<table width='80%'>
		<tr>
		<td class='colhead' width='35px'>$lang[pr_verd]</td>
		<td class='colhead'>{$lang['pr_nafn']}</td>
		<td class='colhead'>{$lang['pr_lys']}</td>
		<td class='colhead' width='125px'>{$lang['pr_kaupa']}</td>
		</tr>";

foreach ($prizes as $P) {
    if ($CURUSER["class"] >= UC_VIP)
        $VERD = ($P['prize'] / 2);
    else
        $VERD = $P['prize'];
    $HTMLOUT .= "<tr>
			<td>$VERD</td>
			<td>" . $P['name'] . "</td>
			<td>" . $P['desc'] . "</td>
			<td><a href='{$TBDEV['baseurl']}/coins.php?kaupa=" . intval($P['id']) . "'><input type='submit' value='{$lang['pr_kaupa']}' /></a></td>
			</tr>";
}

$HTMLOUT .= "</table>";
echo stdhead($lang['head_verslun']) . $HTMLOUT . stdfoot();
?>
