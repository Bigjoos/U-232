<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
    define('IN_TBDEV_ADMIN', TRUE);
    require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
    require_once(INCL_DIR.'user_functions.php');
    dbconn(false);

    loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('admin') );
  
    /** new way **/
    if (!min_class(UC_STAFF)) // or just simply: if (!min_class(UC_STAFF))
    header( "Location: {$TBDEV['baseurl']}/index.php");

    $action = isset($_GET["action"]) ? $_GET["action"] : '';
    $forum_pic_url = $TBDEV['pic_base_url'] . 'forumicons/';
  
    define( 'F_IMAGES', $TBDEV['pic_base_url'] . 'forumicons');
    define( 'POST_ICONS', F_IMAGES.'/post_icons');
    
    $ad_actions = array('bans'            => 'bans', 
                        'adduser'         => 'adduser', 
                        'stats'           => 'stats', 
                        'delacct'         => 'delacct', 
                        'testip'          => 'testip', 
                        'usersearch'      => 'usersearch', 
                        'mysql_overview'  => 'mysql_overview', 
                        'mysql_stats'     => 'mysql_stats',
                        'shistory'     => 'shistory', 
                        'categories'      => 'categories', 
                        'docleanup'       => 'docleanup',
                        'log'             => 'log',
                        'news'            => 'news',
                        'freeleech'       => 'freeleech',
                        'freeusers'       => 'freeusers',
                        'moforums'     => 'moforums',
                        'donations'     => 'donations',
                        'slotmanage'     => 'slotmanage',
                        'failedlogins'     => 'failedlogins',
                        'cheaters'     => 'cheaters',
                        'inviteadd'     => 'inviteadd',
                        'flush'     => 'flush',
                        'themes'		  => 'themes',
                        'editlog'		  => 'editlog',
                        'reset'		  => 'reset',
                        'ipcheck'		  => 'ipcheck',
                        'inactive'		  => 'inactive',
                        'snatched_torrents'	=> 'snatched_torrents',
                        'events'		  => 'events',
                        'bonusmanage'		  => 'bonusmanage',
                        'floodlimit'		  => 'floodlimit',
                        'stats_extra'     => 'stats_extra',
                        'polls_manager' => 'polls_manager',
                        'msubforums' => 'msubforums',
                        'findnotconnectable' 	=> 'findnotconnectable',
                        'namechanger' 	=> 'namechanger',
                        'backup' 	=> 'backup',
                        'pmview' => 'pmview',
                        'reports' => 'reports',
                        'nameblacklist'   => 'nameblacklist',
                        'system_view'   => 'system_view',
                        'datareset'   => 'datareset',
                        'forummanager' =>      'forummanager'
                        );
    
    if( in_array($action, $ad_actions) AND file_exists( "admin/{$ad_actions[ $action ]}.php" ) )
    {
      require_once "admin/{$ad_actions[ $action ]}.php";
    }
    else
    {
      require_once "staffpanel.php";
    }
    
?>