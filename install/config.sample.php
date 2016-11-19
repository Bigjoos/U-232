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
$INSTALLER09['time_adjust'] =  0;
$INSTALLER09['time_offset'] = '0'; 
$INSTALLER09['time_use_relative'] = 1;
$INSTALLER09['time_use_relative_format'] = '{--}, h:i A';
$INSTALLER09['time_joined'] = 'j-F y';
$INSTALLER09['time_short'] = 'jS F Y - h:i A';
$INSTALLER09['time_long'] = 'M j Y, h:i A';
$INSTALLER09['time_tiny'] = '';
$INSTALLER09['time_date'] = '';
// DB setup
$INSTALLER09['mysql_host'] = '#mysql_host';
$INSTALLER09['mysql_user'] = '#mysql_user';
$INSTALLER09['mysql_pass'] = '#mysql_pass';
$INSTALLER09['mysql_db']   = '#mysql_db';
// Cookie setup
$INSTALLER09['cookie_prefix']  = '#cookie_prefix'; // This allows you to have multiple trackers, eg for demos, testing etc.
$INSTALLER09['cookie_path']    = '#cookie_path';   // ATTENTION: You should never need this unless the above applies eg: /tbdev
$INSTALLER09['cookie_domain']  = '#cookie_domain'; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
$INSTALLER09['site_online'] = 1;
$INSTALLER09['tracker_post_key'] = 'lsdflksfda4545frwe35@kk';
$INSTALLER09['max_torrent_size'] = 1000000;
$INSTALLER09['announce_interval'] = 60 * 30;
$INSTALLER09['signup_timeout'] = 86400 * 3;
$INSTALLER09['autoclean_interval'] = 900;
$INSTALLER09['autoslowclean_interval'] = 28800;
$INSTALLER09['autoslowclean_interval2'] = 57600;
$INSTALLER09['optimizedb_interval'] = 172800;
$INSTALLER09['minvotes'] = 1;
$INSTALLER09['max_dead_torrent_time'] = 6 * 3600;
$INSTALLER09['language'] = 'en';
$INSTALLER09['user_ratios'] = 1;
$INSTALLER09['bot_id'] = 2;
$INSTALLER09['coins'] = false;
$INSTALLER09['forums_online'] = 1;
$INSTALLER09['forums_autoshout_on'] = 1;
$INSTALLER09['forums_seedbonus_on'] = 1;
$INSTALLER09['maxsublength'] = 100; 
//latest posts limit
$INSTALLER09['latest_posts_limit'] = 5; //query limit for latest forum posts on index
/** settings **/
$INSTALLER09['reports']      = 1;// 1/0 on/off
$INSTALLER09['karma']        = 1;// 1/0 on/off
$INSTALLER09['textbbcode']   = 1;// 1/0 on/off
$INSTALLER09['max_slots'] = 1; // 1=On 0=Off
$INSTALLER09['user_slots'] = 20;
$INSTALLER09['p_user_slots'] = 30;
$INSTALLER09['user_ratio1_slots'] = 2;
$INSTALLER09['user_ratio2_slots'] = 3;
$INSTALLER09['user_ratio3_slots'] = 5;
$INSTALLER09['user_ratio4_slots'] = 10;
// Max users on site
$INSTALLER09['maxusers'] = 5000; // LoL Who we kiddin' here?
$INSTALLER09['invites'] = 3500; // LoL Who we kiddin' here?
$INSTALLER09['openreg'] = true; //==true=open, false = closed
$INSTALLER09['failedlogins'] = 5; // Maximum failed logins before ip ban
$INSTALLER09['flood_time'] = 900; //comment/forum/pm flood limit
$INSTALLER09['readpost_expiry'] = 14*86400; // 14 days
$INSTALLER09['expires']['latestuser'] = 0; // 0 = infinite
/** define dirs **/
define('INCL_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('ROOT_DIR', realpath(INCL_DIR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
define('ADMIN_DIR', ROOT_DIR.'admin'.DIRECTORY_SEPARATOR);
define('FORUM_DIR', ROOT_DIR.'forums'.DIRECTORY_SEPARATOR);
define('CACHE_DIR', ROOT_DIR.'cache'.DIRECTORY_SEPARATOR);
define('MODS_DIR', ROOT_DIR.'mods'.DIRECTORY_SEPARATOR);
define('LANG_DIR', ROOT_DIR.'lang'.DIRECTORY_SEPARATOR.$INSTALLER09['language'].DIRECTORY_SEPARATOR);  
define('TEMPLATE_DIR', ROOT_DIR.'templates'.DIRECTORY_SEPARATOR);
define('IMDB_DIR', ROOT_DIR.'imdb'.DIRECTORY_SEPARATOR);
$INSTALLER09["cache"] = ROOT_DIR.'cache';
$INSTALLER09['dictbreaker'] = ROOT_DIR.'dictbreaker';
$INSTALLER09['torrent_dir'] = ROOT_DIR.'torrents'; # must be writable for httpd user   
$INSTALLER09['bucket_dir'] = ROOT_DIR .'bitbucket'; # must be writable for httpd user 
$INSTALLER09['flood_file'] = INCL_DIR.'settings'.DIRECTORY_SEPARATOR.'limitfile.txt';
$INSTALLER09['nameblacklist'] = ROOT_DIR.'/cache/nameblacklist.txt';
# the first one will be displayed on the pages
$INSTALLER09['announce_urls'] = array();
$INSTALLER09['announce_urls'][] = '#announce_urls';
//$INSTALLER09['announce_urls'][] = "https://yoursite/announce.php";
//$INSTALLER09['announce_urls'] = "http://localhost:2710/announce";
//$INSTALLER09['announce_urls'] = "http://domain.com:83/announce.php";
if ($_SERVER["HTTP_HOST"] == "")
$_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];
$INSTALLER09['baseurl'] = "http://" . $_SERVER["HTTP_HOST"];
/*
## DO NOT UNCOMMENT THIS: IT'S FOR LATER USE!
$host = getenv( 'SERVER_NAME' );
$script = getenv( 'SCRIPT_NAME' );
$script = str_replace( "\\", "/", $script );

  if( $host AND $script )
  {
    $script = str_replace( '/index.php', '', $script );

    $INSTALLER09['baseurl'] = "http://{$host}{$script}";
  }
*/
// Email for sender/return path.
$INSTALLER09['site_email'] = '#site_email';
$INSTALLER09['site_name'] = '#site_name';
$INSTALLER09['language'] = 'en';
$INSTALLER09['msg_alert'] = 1; // saves a query when off
$INSTALLER09['report_alert'] = 1; // saves a query when off
$INSTALLER09['staffmsg_alert'] = 1; // saves a query when off
$INSTALLER09['uploadapp_alert'] = 1; // saves a query when off
$INSTALLER09['sql_error_log'] = ROOT_DIR.'logs'.DIRECTORY_SEPARATOR.'sql_err_'.date('M_D_Y').'.log';
$INSTALLER09['pic_base_url'] = "./pic/";
$INSTALLER09['stylesheet'] = "1";
//set this to size of user avatars
$INSTALLER09['av_img_height'] = 100;
$INSTALLER09['av_img_width'] = 100;
//set this to size of user signatures
$INSTALLER09['sig_img_height'] = 100;
$INSTALLER09['sig_img_width'] = 500;
$INSTALLER09['bucket_dir'] = ROOT_DIR . '/bitbucket'; # must be writable for httpd user  
$INSTALLER09['allowed_ext'] = array('image/gif', 'image/png', 'image/jpeg');
$INSTALLER09['bucket_maxsize'] = 500*1024; #max size set to 500kb
//last 24 users online
$INSTALLER09['last24cache'] = CACHE_DIR.'last24/'.date('dmy').'.txt';
$INSTALLER09['last24record'] = CACHE_DIR.'last24record.txt';
$INSTALLER09['happyhour'] = CACHE_DIR.'happyhour'.DIRECTORY_SEPARATOR.'happyhour.txt';
$INSTALLER09['crazy_title'] ="w00t It's Crazyhour!";
$INSTALLER09['crazy_message'] ="All torrents are FREE and upload stats are TRIPLED!";
// Set this to the line break character sequence of your system
//$INSTALLER09['linebreak'] = "\r\n"; // not used at present.
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