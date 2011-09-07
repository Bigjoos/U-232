<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
error_reporting(E_ALL);
define('SQL_DEBUG', 1);
/* Compare php version for date/time stuff etc! */
if (version_compare(PHP_VERSION, "5.1.0RC1", ">="))
date_default_timezone_set('Europe/London');
define('TIME_NOW', time());
$TBDEV['time_adjust'] =  0;
$TBDEV['time_offset'] = '0'; 
$TBDEV['time_use_relative'] = 1;
$TBDEV['time_use_relative_format'] = '{--}, h:i A';
$TBDEV['time_joined'] = 'j-F y';
$TBDEV['time_short'] = 'jS F Y - h:i A';
$TBDEV['time_long'] = 'M j Y, h:i A';
$TBDEV['time_tiny'] = '';
$TBDEV['time_date'] = '';
// DB setup
$TBDEV['mysql_host'] = '#mysql_host';
$TBDEV['mysql_user'] = '#mysql_user';
$TBDEV['mysql_pass'] = '#mysql_pass';
$TBDEV['mysql_db']   = '#mysql_db';
// Cookie setup
$TBDEV['cookie_prefix']  = '#cookie_prefix'; // This allows you to have multiple trackers, eg for demos, testing etc.
$TBDEV['cookie_path']    = '#cookie_path';   // ATTENTION: You should never need this unless the above applies eg: /tbdev
$TBDEV['cookie_domain']  = '#cookie_domain'; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
$TBDEV['site_online'] = 1;
$TBDEV['tracker_post_key'] = 'lsdflksfda4545frwe35@kk';
$TBDEV['max_torrent_size'] = 1000000;
$TBDEV['announce_interval'] = 60 * 30;
$TBDEV['signup_timeout'] = 86400 * 3;
$TBDEV['autoclean_interval'] = 900;
$TBDEV['autoslowclean_interval'] = 28800;
$TBDEV['autoslowclean_interval2'] = 57600;
$TBDEV['optimizedb_interval'] = 172800;
$TBDEV['minvotes'] = 1;
$TBDEV['max_dead_torrent_time'] = 6 * 3600;
$TBDEV['language'] = 'en';
$TBDEV['user_ratios'] = 1;
$TBDEV['bot_id'] = 2;
$TBDEV['coins'] = false;
$TBDEV['forums_online'] = 1;
$TBDEV['forums_autoshout_on'] = 1;
$TBDEV['forums_seedbonus_on'] = 1;
$TBDEV['maxsublength'] = 100; 
//latest posts limit
$TBDEV['latest_posts_limit'] = 5; //query limit for latest forum posts on index
/** settings **/
$TBDEV['reports']      = 1;// 1/0 on/off
$TBDEV['karma']        = 1;// 1/0 on/off
$TBDEV['textbbcode']   = 1;// 1/0 on/off
$TBDEV['max_slots'] = 1; // 1=On 0=Off
$TBDEV['user_slots'] = 20;
$TBDEV['p_user_slots'] = 30;
$TBDEV['user_ratio1_slots'] = 2;
$TBDEV['user_ratio2_slots'] = 3;
$TBDEV['user_ratio3_slots'] = 5;
$TBDEV['user_ratio4_slots'] = 10;
// Max users on site
$TBDEV['maxusers'] = 5000; // LoL Who we kiddin' here?
$TBDEV['invites'] = 3500; // LoL Who we kiddin' here?
$TBDEV['openreg'] = true; //==true=open, false = closed
$TBDEV['failedlogins'] = 5; // Maximum failed logins before ip ban
$TBDEV['flood_time'] = 900; //comment/forum/pm flood limit
$TBDEV['readpost_expiry'] = 14*86400; // 14 days
$TBDEV['expires']['latestuser'] = 0; // 0 = infinite
/** define dirs **/
define('INCL_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('ROOT_DIR', realpath(INCL_DIR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
define('ADMIN_DIR', ROOT_DIR.'admin'.DIRECTORY_SEPARATOR);
define('FORUM_DIR', ROOT_DIR.'forums'.DIRECTORY_SEPARATOR);
define('CACHE_DIR', ROOT_DIR.'cache'.DIRECTORY_SEPARATOR);
define('MODS_DIR', ROOT_DIR.'mods'.DIRECTORY_SEPARATOR);
define('LANG_DIR', ROOT_DIR.'lang'.DIRECTORY_SEPARATOR.$TBDEV['language'].DIRECTORY_SEPARATOR);  
define('TEMPLATE_DIR', ROOT_DIR.'templates'.DIRECTORY_SEPARATOR);
define('IMDB_DIR', ROOT_DIR.'imdb'.DIRECTORY_SEPARATOR);
$TBDEV["cache"] = ROOT_DIR.'cache';
$TBDEV['dictbreaker'] = ROOT_DIR.'dictbreaker';
$TBDEV['torrent_dir'] = ROOT_DIR.'torrents'; # must be writable for httpd user   
$TBDEV['bucket_dir'] = ROOT_DIR .'bitbucket'; # must be writable for httpd user 
$TBDEV['flood_file'] = INCL_DIR.'settings'.DIRECTORY_SEPARATOR.'limitfile.txt';
$TBDEV['nameblacklist'] = ROOT_DIR.'/cache/nameblacklist.txt';
# the first one will be displayed on the pages
$TBDEV['announce_urls'] = array();
$TBDEV['announce_urls'][] = '#announce_urls';
//$TBDEV['announce_urls'][] = "https://yoursite/announce.php";
//$TBDEV['announce_urls'] = "http://localhost:2710/announce";
//$TBDEV['announce_urls'] = "http://domain.com:83/announce.php";
if ($_SERVER["HTTP_HOST"] == "")
$_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];
$TBDEV['baseurl'] = "http://" . $_SERVER["HTTP_HOST"];
/*
## DO NOT UNCOMMENT THIS: IT'S FOR LATER USE!
$host = getenv( 'SERVER_NAME' );
$script = getenv( 'SCRIPT_NAME' );
$script = str_replace( "\\", "/", $script );

  if( $host AND $script )
  {
    $script = str_replace( '/index.php', '', $script );

    $TBDEV['baseurl'] = "http://{$host}{$script}";
  }
*/
// Email for sender/return path.
$TBDEV['site_email'] = '#site_email';
$TBDEV['site_name'] = '#site_name';
$TBDEV['language'] = 'en';
$TBDEV['msg_alert'] = 1; // saves a query when off
$TBDEV['report_alert'] = 1; // saves a query when off
$TBDEV['staffmsg_alert'] = 1; // saves a query when off
$TBDEV['uploadapp_alert'] = 1; // saves a query when off
$TBDEV['sql_error_log'] = ROOT_DIR.'logs'.DIRECTORY_SEPARATOR.'sql_err_'.date('M_D_Y').'.log';
$TBDEV['pic_base_url'] = "./pic/";
$TBDEV['stylesheet'] = "1";
//set this to size of user avatars
$TBDEV['av_img_height'] = 100;
$TBDEV['av_img_width'] = 100;
//set this to size of user signatures
$TBDEV['sig_img_height'] = 100;
$TBDEV['sig_img_width'] = 500;
$TBDEV['bucket_dir'] = ROOT_DIR . '/bitbucket'; # must be writable for httpd user  
$TBDEV['allowed_ext'] = array('image/gif', 'image/png', 'image/jpeg');
$TBDEV['bucket_maxsize'] = 500*1024; #max size set to 500kb
//last 24 users online
$TBDEV['last24cache'] = CACHE_DIR.'last24/'.date('dmy').'.txt';
$TBDEV['last24record'] = CACHE_DIR.'last24record.txt';
$TBDEV['happyhour'] = CACHE_DIR.'happyhour'.DIRECTORY_SEPARATOR.'happyhour.txt';
$TBDEV['crazy_title'] ="w00t It's Crazyhour!";
$TBDEV['crazy_message'] ="All torrents are FREE and upload stats are TRIPLED!";
// Set this to the line break character sequence of your system
//$TBDEV['linebreak'] = "\r\n"; // not used at present.
define ('UC_USER', 0);
define ('UC_POWER_USER', 1);
define ('UC_VIP', 2);
define ('UC_UPLOADER', 3);
define ('UC_MODERATOR', 4);
define ('UC_ADMINISTRATOR', 5);
define ('UC_SYSOP', 6);
//Do not modify -- versioning system
//This will help identify code for support issues at tbdev.net
define ('TBVERSION','TBDev_2009_svn');
?>