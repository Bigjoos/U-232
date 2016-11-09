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
require_once(INCL_DIR.'html_functions.php');
dbconn();
loggedinorreturn();

if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

$lang = array_merge( load_language('global'), load_language('ad_ipcheck') );

$HTMLOUT ="";

//==The actions
        if (isset($_POST["nowarned"]) && ($_POST["nowarned"] == "nowarned")) {
        if ($CURUSER["class"] < UC_MODERATOR)
        stderr("Sorry", "Access denied."); 
        {
        if (empty($_POST["usernw"]) && empty($_POST["usernlw"]) && empty($_POST["usernhnr"]) && empty($_POST["desact"]) && empty($_POST["delete"]))
         stderr("Error", "You Must Select A User To Edit.");
        if (!empty($_POST["usernw"])) {
            $msg = sqlesc("Your Warning Has Been Removed By: " . $CURUSER['username'] . ".");
            $added = sqlesc(time());
            $userid = implode(", ", array_map("sqlesc", $_POST['usernw']));
            //$userid = implode(", ", $_POST["usernw"]);
             sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (".sqlesc($TBDEV['bot_id']).", $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);                              
            $r = sql_query("SELECT modcomment FROM users WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernw'])) . ")") or sqlerr(__FILE__, __LINE__);
            $user = mysqli_fetch_assoc($r);
            $exmodcomment = htmlspecialchars($user["modcomment"]);
            $modcomment='';
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Warning Removed By " . $CURUSER['username'] . ".\n" . $modcomment . $exmodcomment;
            sql_query("UPDATE users SET modcomment=" . sqlesc($modcomment) . " WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernw'])) . ")") or sqlerr(__FILE__, __LINE__);
            $do = "UPDATE users SET warned='0' WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernw'])) . ")";
            $res = sql_query($do) or sqlerr(__FILE__, __LINE__); 
            header("Refresh: 3; url={$TBDEV['baseurl']}/warned.php");
            stderr("Success","Warning Removed - Redirecting in 3..2..1");
            }

        if (isset($_POST["noleechwarned"]) && ($_POST["noleechwarned"] == "noleechwarned")) {
        if (!empty($_POST["usernlw"])) {
            $msg = sqlesc("Your Leech Warning Has Been Removed By: " . $CURUSER['username'] . ".");
            $added = sqlesc(time());
            //$userid = implode(", ", $_POST["usernlw"]);
            $userid = implode(", ", array_map("sqlesc", $_POST['usernlw']));
             sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (".sqlesc($TBDEV['bot_id']).", $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);                              
            $r = sql_query("SELECT modcomment FROM users WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernlw'])) . ")")or sqlerr(__FILE__, __LINE__);
            $user = mysqli_fetch_assoc($r);
            $exmodcomment = $user["modcomment"];
            $modcomment='';
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Leech Warning Removed By " . $CURUSER['username'] . ".\n" . $modcomment . $exmodcomment;
            sql_query("UPDATE users SET modcomment=" . sqlesc($modcomment) . " WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernlw'])) . ")") or sqlerr(__FILE__, __LINE__);
            $do = "UPDATE users SET leechwarn='0' WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernlw'])) . ")";
            $res = sql_query($do) or sqlerr(__FILE__, __LINE__);
            header("Refresh: 3; url={$TBDEV['baseurl']}/warned.php");
            stderr("Success","Leech Warning Removed - Redirecting in 3..2..1");
            }

       if (isset($_POST["nohnrwarned"]) && ($_POST["nohnrwarned"] == "nohnrwarned")) {
        if (!empty($_POST["usernhnr"])) {
            $msg = sqlesc("Your Hit And Run Warning Has Been Removed By: " . $CURUSER['username'] . ".");
            $added = sqlesc(time());
            $userid = implode(", ", $_POST["usernhnr"]);
            $userid = implode(", ", array_map("sqlesc", $_POST['usernhnr']));
            sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (".sqlesc($TBDEV['bot_id']).", $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);                              
            $r = sql_query("SELECT modcomment FROM users WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernhnr'])) . ")")or sqlerr(__FILE__, __LINE__);
            $user = mysqli_fetch_assoc($r);
            $exmodcomment = $user["modcomment"];
            $modcomment='';
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Hit and Run Warning Removed By " . $CURUSER['username'] . ".\n" . $modcomment . $exmodcomment;
            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET modcomment=" . sqlesc($modcomment) . " WHERE id IN (" . implode(", ", $_POST["usernhnr"]) . ")") or sqlerr(__FILE__, __LINE__);
            $do = "UPDATE users SET hnrwarn='no' WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['usernhnr'])) . ")";
            $res = sql_query($do) or sqlerr(__FILE__, __LINE__);
            header("Refresh: 3; url={$TBDEV['baseurl']}/warned.php");
            stderr("Success","Hit And Run Warning Removed - Redirecting in 3..2..1");
            }

        if (!empty($_POST["desact"])) {                       
            $do = "UPDATE users SET enabled='no' WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['desact'])) . ")";
            $res = sql_query($do) or sqlerr(__FILE__, __LINE__);
            header("Refresh: 3; url={$TBDEV['baseurl']}/warned.php");
            stderr("Success","Member Disabled - Redirecting in 3..2..1");
            }
            }
            }
            }
            }
            //==End

$warned = number_format(get_row_count("users", "WHERE warned >=1"));
$leechwarn = number_format(get_row_count("users", "WHERE leechwarn >=1"));
$hnrwarn = number_format(get_row_count("users", "WHERE hnrwarn ='yes'"));

$HTMLOUT .= begin_frame("Warned Users: (".intval($warned).") - Leech Warned Users: (".intval($leechwarn).") - Hit And Run Warned: (".intval($hnrwarn).")", true);

$res = sql_query("SELECT * FROM users WHERE warned >=1 OR leechwarn >=1 OR hnrwarn ='yes' AND enabled='yes' ORDER BY (users.uploaded/users.downloaded)") or sqlerr(__FILE__, __LINE__);
$num = mysqli_num_rows($res);
$HTMLOUT .="<form action='{$_SERVER["PHP_SELF"]}' method='post'>
<table border='1' width='750' cellspacing='0' cellpadding='2'>
<tr align='center'><td class='colhead' width='90'>User Name</td>
<td class='colhead' width='70'>Registered</td>
<td class='colhead' width='75'>Last access</td>
<td class='colhead' width='75'>User Class</td>
<td class='colhead' width='70'>Downloaded</td>
<td class='colhead' width='70'>UpLoaded</td>
<td class='colhead' width='45'>Ratio</td>
<td class='colhead' width='110'>End<br />Of Warning</td>
<td class='colhead' width='110'>End<br />Of Leech Warning</td>
<td class='colhead' width='110'>Hnr Warned</td>
<td class='colhead' width='65'>Remove<br />Warning</td>
<td class='colhead' width='65'>Remove<br />Leech Warning</td>
<td class='colhead' width='65'>Remove<br />HnR Warning</td>
<td class='colhead' width='65'>Disable<br />Account</td></tr>\n";
for ($i = 1; $i <= $num; $i++) {
    $arr = mysqli_fetch_assoc($res);
    if ($arr['added'] == 0)
        $arr['added'] = '-';
    if ($arr['last_access'] == 0)
        $arr['last_access'] = '-';

    if ($arr["downloaded"] != 0) {
        $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
    } else {
        $ratio = "---";
    }
    $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
    $uploaded = mksize($arr["uploaded"]);
    $downloaded = mksize($arr["downloaded"]);
    $added = get_date($arr['added'], 'LONG', 1,0);
    $last_access = get_date($arr['last_access'], 'LONG', 1,0);
    $class = get_user_class_name($arr["class"]);

$HTMLOUT .="<tr><td align='left'><a href='{$TBDEV['baseurl']}/userdetails.php?id=".intval($arr['id'])."'><b>".htmlspecialchars($arr['username'])."</b></a>" . ($arr["donor"] == "yes" ? "<img src='/pic/star.gif' border='0' alt='Donor' />" : "") . "</td>
<td align='center'>$added</td>
<td align='center'>$last_access</td>
<td align='center'>$class</td>
<td align='center'>$downloaded</td>
<td align='center'>$uploaded</td>
<td align='center'>$ratio</td>
<td align='center'>".mkprettytime($arr['warned'] - time()). "</td>
<td align='center'>".mkprettytime($arr['leechwarn'] - time()). "</td>
<td align='center'>".$arr['hnrwarn']. "</td>
<td bgcolor=\"#008000\" align=\"center\"><input type=\"checkbox\" name=\"usernw[]\" value=\"".intval($arr['id'])."\" /></td>
<td bgcolor=\"#008000\" align=\"center\"><input type=\"checkbox\" name=\"usernlw[]\" value=\"".intval($arr['id']."\" /></td>
<td bgcolor=\"#008000\" align=\"center\"><input type=\"checkbox\" name=\"usernhnr[]\" value=\"".intval($arr['id']."\" /></td>
<td bgcolor=\"#FF0000\" align=\"center\"><input type=\"checkbox\" name=\"desact[]\" value=\"".intval($arr['id']."\" /></td>
</tr>\n";
}
if ($CURUSER["class"] >= UC_ADMINISTRATOR) {
    $HTMLOUT .="<tr><td colspan='14' align='right'><input type=\"submit\" name=\"submit\" value=\"Apply Changes\" /><input type=\"hidden\" name=\"nowarned\" value=\"nowarned\" /><input type=\"hidden\" name=\"noleechwarned\" value=\"noleechwarned\" /><input type=\"hidden\" name=\"nohnrwarned\" value=\"nohnrwarned\" /></td></tr>\n";
    $HTMLOUT .="</table></form>\n";
}


$HTMLOUT .= end_frame();

echo stdhead('Warned') . $HTMLOUT . stdfoot();
?>
