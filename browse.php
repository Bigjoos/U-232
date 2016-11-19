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
require_once INCL_DIR.'torrenttable_functions.php';
require_once INCL_DIR.'pager_functions.php';
dbconn(false);
loggedinorreturn();

if (isset($_GET['clear_new']) && $_GET['clear_new'] == '1'){
sql_query("UPDATE users SET last_browse=".TIME_NOW." where id=".$CURUSER['id']);
header("Location: {$INSTALLER09['baseurl']}/browse.php");
}

    $stdfoot = array(/** include js **/'js' => array('java_klappe','wz_tooltip'));
    $lang = array_merge( load_language('global'), load_language('browse'), load_language('torrenttable_functions') );

    parked();
    $HTMLOUT = '';
    
    $cats = genrelist();

    if(isset($_GET["search"])) 
    {
      $searchstr = unesc($_GET["search"]);
      $cleansearchstr = searchfield($searchstr);
      if (empty($cleansearchstr))
        unset($cleansearchstr);
    }
    if (isset($_GET['sort']) && isset($_GET['type'])) {
    $column = '';
    $ascdesc = '';

    switch (htmlspecialchars($_GET['sort'])) {
        case '1': $column = "name";
            break;
        case '2': $column = "numfiles";
            break;
        case '3': $column = "comments";
            break;
        case '4': $column = "added";
            break;
        case '5': $column = "size";
            break;
        case '6': $column = "times_completed";
            break;
        case '7': $column = "seeders";
            break;
        case '8': $column = "leechers";
            break;
        case '9': $column = "owner";
            break;
        default: $column = "id";
            break;
    }

    switch (htmlspecialchars($_GET['type'])) {
        case 'asc': $ascdesc = "ASC";
            $linkascdesc = "asc";
            break;
        case 'desc': $ascdesc = "DESC";
            $linkascdesc = "desc";
            break;
        default: $ascdesc = "DESC";
            $linkascdesc = "desc";
            break;
    }

    $orderby = "ORDER BY torrents." . $column . " " . $ascdesc;
    $pagerlink = "sort=" . intval($_GET['sort']) . "&amp;type=" . $linkascdesc . "&amp;";
    } else {
    $orderby = "ORDER BY torrents.sticky ASC, torrents.id DESC";
    $pagerlink = "";
    }

    $addparam = "";
    $wherea = array();
    $wherecatina = array();

    if (isset($_GET["incldead"]) &&  $_GET["incldead"] == 1)
    {
      $addparam .= "incldead=1&amp;";
      if (!isset($CURUSER) || $CURUSER["class"] < UC_ADMINISTRATOR)
        $wherea[] = "banned != 'yes'";
    }
    else
    {
      if (isset($_GET["incldead"]) && $_GET["incldead"] == 2)
      {
      $addparam .= "incldead=2&amp;";
        $wherea[] = "visible = 'no'";
      }
      else
        $wherea[] = "visible = 'yes'";
    }
   
  
    $category = (isset($_GET["cat"])) ? (int)$_GET["cat"] : false;

    $all = isset($_GET["all"]) ? $_GET["all"] : false;

    if (!$all)
    {
      if (!$_GET && $CURUSER["notifs"])
      {
        $all = True;
        foreach ($cats as $cat)
        {
          $all &= $cat['id'];
          if (strpos($CURUSER["notifs"], "[cat" . $cat['id'] . "]") !== False)
          {
            $wherecatina[] = $cat['id'];
            $addparam .= "c{$cat['id']}=1&amp;";
          }
        }
      }
      elseif ($category)
      {
        if (!is_valid_id($category))
          stderr("{$lang['browse_error']}", "{$lang['browse_invalid_cat']}");
        $wherecatina[] = $category;
        $addparam .= "cat=$category&amp;";
      }
      else
      {
        $all = True;
        foreach ($cats as $cat)
        {
          $all &= isset($_GET["c{$cat['id']}"]);
          if (isset($_GET["c{$cat['id']}"]))
          {
            $wherecatina[] = $cat['id'];
            $addparam .= "c{$cat['id']}=1&amp;";
          }
        }
      }
    }
    
    if ($all)
    {
      $wherecatina = array();
      $addparam = "";
    }

    if (count($wherecatina) > 1)
      $wherecatin = implode(",",$wherecatina);
    elseif (count($wherecatina) == 1)
      $wherea[] = "category = $wherecatina[0]";

    $wherebase = $wherea;

    if (isset($cleansearchstr))
    {
      $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
      //$wherea[] = "0";
      $addparam .= "search=" . urlencode($searchstr) . "&amp;";
      $orderby = "";
      
      /////////////// SEARCH CLOUD MALARKY //////////////////////

        $searchcloud = sqlesc($cleansearchstr);
        sql_query("INSERT INTO searchcloud (searchedfor, howmuch) VALUES ($searchcloud, 1)
                    ON DUPLICATE KEY UPDATE howmuch=howmuch+1");
      /////////////// SEARCH CLOUD MALARKY END ///////////////////
    }

    $where = implode(" AND ", $wherea);
    
    if (isset($wherecatin))
      $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";

    if ($where != "")
      $where = "WHERE $where";

    $res = sql_query("SELECT COUNT(*) FROM torrents $where") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    $row = mysqli_fetch_array($res, MYSQLI_NUM);
    $count = $row[0];

    if (!$count && isset($cleansearchstr)) 
    {
      $wherea = $wherebase;
      $orderby = "ORDER BY id DESC";
      $searcha = explode(" ", $cleansearchstr);
      $sc = 0;
      foreach ($searcha as $searchss) 
      {
        if (strlen($searchss) <= 1)
          continue;
        $sc++;
        if ($sc > 5)
          break;
        $ssa = array();
        foreach (array("search_text", "ori_descr") as $sss)
          $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
        $wherea[] = "(" . implode(" OR ", $ssa) . ")";
      }
    
      if ($sc) 
      {
        $where = implode(" AND ", $wherea);
        if ($where != "")
          $where = "WHERE $where";
        $res = sql_query("SELECT COUNT(*) FROM torrents $where");
        $row = mysqli_fetch_array($res, MYSQLI_NUM);
        $count = $row[0];
      }
    }

    $torrentsperpage = $CURUSER["torrentsperpage"];
    if (!$torrentsperpage)
      $torrentsperpage = 15;

    if ($count)
    {
      if ($addparam != "") {
            if ($pagerlink != "") {
                if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
                    $addparam = $addparam . "&" . $pagerlink;
                } else {
                    $addparam = $addparam . $pagerlink;
                }
            }
        } else {
            $addparam = $pagerlink;
        }
      $pager = pager($torrentsperpage, $count, "browse.php?" . $addparam);
      
    $query = "SELECT torrents.id, torrents.category, torrents.leechers, torrents.seeders, torrents.name, torrents.descr, torrents.times_completed, torrents.size, torrents.added, torrents.type, torrents.free, torrents.poster, torrents.comments, torrents.numfiles, torrents.filename, torrents.anonymous, torrents.sticky, torrents.nuked, torrents.nukereason, torrents.owner, torrents.checked_by, IF(torrents.nfo <> '', 1, 0) as nfoav," .
    //"IF(torrents.numratings < {$INSTALLER09['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, categories.name AS cat_name, categories.image AS cat_pic, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
    "categories.name AS cat_name, categories.image AS cat_pic, users.username, freeslots.tid, freeslots.uid, freeslots.free AS freeslot, freeslots.double AS doubleup FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN freeslots ON (torrents.id=freeslots.tid AND freeslots.uid={$CURUSER['id']}) $where $orderby {$pager['limit']}";
    $res = sql_query($query) or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    }
    else
    {
      unset($res);
    }
    
    if (isset($cleansearchstr))
      $title = "{$lang['browse_search']}\"$searchstr\"";
    else
      $title = '';

    if ($CURUSER['viewscloud'] === "yes") {
    $HTMLOUT .= "<div id='wrapper' style='width:80%;border:1px solid black;background-color:pink;'>";
    //print out the tag cloud
    require_once "include/searchcloud_functions.php";
    $HTMLOUT .= cloud() . "
    </div>";
    }
    
    $HTMLOUT .= "<br /><br />
    <form method='get' action='browse.php'>
    <table class='bottom'>
    <tr>
    <td class='bottom'>
    <table class='bottom'>
    <tr>";

    $i = 0;
    $catsperrow = 7;
    foreach ($cats as $cat)
    {
      $HTMLOUT .= ($i && $i % $catsperrow == 0) ? "</tr><tr>" : "";
      $HTMLOUT .= "<td class='bottom' style=\"padding-bottom: 2px;padding-left: 7px\">
      <input name='c".$cat['id']."' type=\"checkbox\" " . (in_array($cat['id'],$wherecatina) ? "checked='checked' " : "") . "value='1' /><a class='catlink' href='browse.php?cat={$cat['id']}'><img src='{$INSTALLER09['pic_base_url']}caticons/" . htmlspecialchars($cat['image']) . "' alt='" . htmlspecialchars($cat['name']) . "' title='" . htmlspecialchars($cat['name']) . "' /></a></td>\n";
      $i++;
    }
    $alllink = "<div align='left'>(<a href='./browse.php?all=1'><b>{$lang['browse_show_all']}</b></a>)</div>";

    $ncats = count($cats);
    $nrows = ceil($ncats/$catsperrow);
    $lastrowcols = $ncats % $catsperrow;

    if ($lastrowcols != 0)
    {
      if ($catsperrow - $lastrowcols != 1)
        {
          $HTMLOUT .= "<td class='bottom' rowspan='" . ($catsperrow  - $lastrowcols - 1) . "'>&nbsp;</td>";
        }
      $HTMLOUT .= "<td class='bottom' style=\"padding-left: 5px\">$alllink</td>\n";
    }

    $selected = (isset($_GET["incldead"])) ? (int)$_GET["incldead"] : "";

    $HTMLOUT .= "</tr>
    </table>
    </td>

    <td class='bottom'>
    <table class='main'>
      <tr>
        <td class='bottom' style='padding: 1px;padding-left: 10px'>
          <select name='incldead'>
    <option value='0'>{$lang['browse_active']}</option>
    <option value='1'".($selected == 1 ? " selected='selected'" : "").">{$lang['browse_inc_dead']}</option>
    <option value='2'".($selected == 2 ? " selected='selected'" : "").">{$lang['browse_dead']}</option>
          </select>
        </td>";
        
        
    if ($ncats % $catsperrow == 0)
    {
      $HTMLOUT .= "<td class='bottom' style='padding-left: 15px' rowspan='$nrows' valign='middle' align='right'>$alllink</td>\n";
    }

    $HTMLOUT .= "</tr>
      <tr>
        <td class='bottom' style='padding: 1px;padding-left: 10px'>
        <div align='center'>
          <input type='submit' class='btn' value='{$lang['browse_go']}' />
        </div>
        </td>
      </tr>
      </table>
    </td>
    </tr>
    </table>
    </form>";

    // clear new tag manually
    if ($CURUSER['clear_new_tag_manually'] == 'yes') {     
    $HTMLOUT .="<a href='?clear_new=1'><input type='submit' value='clear new tag' class='button' /></a>";
    } else {     
    // clear new tag automatically 
    sql_query("UPDATE users SET last_browse=".TIME_NOW." where id=".$CURUSER['id']);
    }
    
    $HTMLOUT .= "<table width='750' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
    <form method='get' action='browse.php'>
    <p align='center'>
    {$lang['search_search']}
    <input type='text' name='search' size='40' value='' />
    {$lang['search_in']}
    <select name='cat'>
    <option value='0'>{$lang['search_all_types']}</option>";
    $cats = genrelist();
    $catdropdown = "";
    foreach ($cats as $cat) {
    $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
    $getcat = (isset($_GET["cat"])?$_GET["cat"]:'');
    if ($cat["id"] == $getcat)
    $catdropdown .= " selected='selected'";
    $catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
    }

    $deadchkbox = "<input type='checkbox' name='incldead' value='1'";
    if (isset($_GET["incldead"]))
    $deadchkbox .= " checked='checked'";
    $deadchkbox .= " /> {$lang['search_inc_dead']}";
    $HTMLOUT .= $catdropdown;
    $HTMLOUT .= "</select>
    $deadchkbox
    <input type='submit' value='{$lang['search_search_btn']}' class='btn' />
    </p>
    </form>
    </td></tr></table>";
    
    
    if (isset($cleansearchstr))
    {
      $HTMLOUT .= "<h2>{$lang['browse_search']}\"" . htmlentities($searchstr, ENT_QUOTES) . "\"</h2>\n";
    }
    
    if ($count) 
    {
      $HTMLOUT .= $pager['pagertop'];

      $HTMLOUT .= torrenttable($res);

      $HTMLOUT .= $pager['pagerbottom'];
    }
    else 
    {
      if (isset($cleansearchstr)) 
      {
        $HTMLOUT .= "<h2>{$lang['browse_not_found']}</h2>\n";
        $HTMLOUT .= "<p>{$lang['browse_tryagain']}</p>\n";
      }
      else 
      {
        $HTMLOUT .= "<h2>{$lang['browse_nothing']}</h2>\n";
        $HTMLOUT .= "<p>{$lang['browse_sorry']}(</p>\n";
      }
    }

/////////////////////// HTML OUTPUT //////////////////////////////

    print stdhead($title) . $HTMLOUT . stdfoot($stdfoot);

?>
