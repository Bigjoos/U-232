-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 25, 2010 at 01:29 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `09source`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `announcement_main`
-- 

CREATE TABLE `announcement_main` (
  `main_id` int(10) unsigned NOT NULL auto_increment,
  `owner_id` int(10) unsigned NOT NULL default '0',
  `created` int(11) NOT NULL default '0',
  `expires` int(11) NOT NULL default '0',
  `sql_query` text NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`main_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `announcement_main`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `announcement_process`
-- 

CREATE TABLE `announcement_process` (
  `process_id` int(10) unsigned NOT NULL auto_increment,
  `main_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`process_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `announcement_process`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `attachmentdownloads`
-- 

CREATE TABLE `attachmentdownloads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fileid` int(10) NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `userid` int(10) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `downloads` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fileid_userid` (`fileid`,`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `attachmentdownloads`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `attachments`
-- 

CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `topicid` int(10) unsigned NOT NULL default '0',
  `postid` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL default '',
  `size` bigint(20) unsigned NOT NULL default '0',
  `owner` int(10) unsigned NOT NULL default '0',
  `downloads` int(10) unsigned NOT NULL default '0',
  `added` int(11) NOT NULL default '0',
  `type` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `topicid` (`topicid`),
  KEY `postid` (`postid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `attachments`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `avps`
-- 

CREATE TABLE `avps` (
  `arg` varchar(20) collate utf8_unicode_ci NOT NULL,
  `value_s` text collate utf8_unicode_ci NOT NULL,
  `value_i` int(11) NOT NULL default '0',
  `value_u` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`arg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `avps`
-- 

INSERT INTO `avps` VALUES ('lastcleantime', '', 0, 0);
INSERT INTO `avps` VALUES ('lastslowcleantime', '', 0, 0);
INSERT INTO `avps` VALUES ('loadlimit', '2.6919-1275592591', 0, 0);
INSERT INTO `avps` VALUES ('inactivemail', '0', 0, 0);
INSERT INTO `avps` VALUES ('lastoptimizedbtime', '', 0, 0);
INSERT INTO `avps` VALUES ('sitepot', '0', 0, 0);
INSERT INTO `avps` VALUES ('lastslowcleantime2', '', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `bans`
-- 

CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `added` int(11) NOT NULL,
  `addedby` int(10) unsigned NOT NULL default '0',
  `comment` varchar(255) collate utf8_unicode_ci NOT NULL,
  `first` int(11) default NULL,
  `last` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `first_last` (`first`,`last`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `bans`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `blocks`
-- 

CREATE TABLE `blocks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `blockid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userfriend` (`userid`,`blockid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `blocks`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `bonus`
-- 

CREATE TABLE `bonus` (
  `id` int(5) NOT NULL auto_increment,
  `bonusname` varchar(50) NOT NULL default '',
  `points` decimal(10,1) NOT NULL default '0.0',
  `description` text NOT NULL,
  `art` varchar(10) NOT NULL default 'traffic',
  `menge` bigint(20) unsigned NOT NULL default '0',
  `pointspool` decimal(10,1) NOT NULL default '1.0',
  `enabled` enum('yes','no') NOT NULL default 'yes' COMMENT 'This will determined a switch if the bonus is enabled or not! enabled by default',
  `minpoints` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `bonus`
-- 

INSERT INTO `bonus` VALUES (1, '1.0GB Uploaded', 275.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 1073741824, 1.0, 'no', 275);
INSERT INTO `bonus` VALUES (2, '2.5GB Uploaded', 350.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 2684354560, 1.0, 'no', 350);
INSERT INTO `bonus` VALUES (3, '5GB Uploaded', 550.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 5368709120, 1.0, 'no', 550);
INSERT INTO `bonus` VALUES (4, '3 Invites', 650.0, 'With enough bonus points acquired, you are able to exchange them for a few invites. The points are then removed from your Bonus Bank and the invitations are added to your invites amount.', 'invite', 3, 1.0, 'yes', 650);
INSERT INTO `bonus` VALUES (5, 'Custom Title!', 50.0, 'For only 50.0 Karma Bonus Points you can buy yourself a custom title. the only restrictions are no foul or offensive language or userclass can be entered. The points are then removed from your Bonus Bank and your special title is changed to the title of your choice', 'title', 1, 1.0, 'yes', 50);
INSERT INTO `bonus` VALUES (6, 'VIP Status', 5000.0, 'With enough bonus points acquired, you can buy yourself VIP status for one month. The points are then removed from your Bonus Bank and your status is changed.', 'class', 1, 1.0, 'yes', 5000);
INSERT INTO `bonus` VALUES (7, 'Give A Karma Gift', 100.0, 'Well perhaps you dont need the upload credit, but you know somebody that could use the Karma boost! You are now able to give your Karma credits as a gift! The points are then removed from your Bonus Bank and added to the account of a user of your choice!\r\n\r\nAnd they recieve a PM with all the info as well as who it came from...', 'gift_1', 1073741824, 1.0, 'no', 100);
INSERT INTO `bonus` VALUES (8, 'Custom Smilies', 300.0, 'With enough bonus points acquired, you can buy yourself a set of custom smilies for one month! The points are then removed from your Bonus Bank and with a click of a link, your new smilies are available whenever you post or comment!', 'smile', 1, 1.0, 'yes', 300);
INSERT INTO `bonus` VALUES (9, 'Remove Warning', 1000.0, 'With enough bonus points acquired... So you''ve been naughty... tsk tsk :P Yep now for the Low Low price of only 1000 points you can have that warning taken away lol.!', 'warning', 1, 1.0, 'yes', 1000);
INSERT INTO `bonus` VALUES (10, 'Ratio Fix', 500.0, 'With enough bonus points acquired, you can bring the ratio of one torrent to a 1 to 1 ratio! The points are then removed from your Bonus Bank and your status is changed.', 'ratio', 1, 1.0, 'yes', 500);
INSERT INTO `bonus` VALUES (11, 'FreeLeech', 30000.0, 'The Ultimate exchange if you have over 30000 Points - Make the tracker freeleech for everyone for 3 days: Upload will count but no download.\r\nIf you don&#039;t have enough points you can donate certain amount of your points until it accumulates. Everybody&#039;s karma counts!', 'freeleech', 1, 0.0, 'yes', 1);
INSERT INTO `bonus` VALUES (12, 'Doubleupload', 30000.0, 'The ultimate exchange if you have over 30000 points - Make the tracker double upload for everyone for 3 days: Upload will count double.\r\nIf you don&#039;t have enough points you can donate certain amount of your points until it accumulates. Everybody&#039;s karma counts!', 'doubleup', 1, 0.0, 'yes', 1);
INSERT INTO `bonus` VALUES (13, 'Halfdownload', 30000.0, 'The ultimate exchange if you have over 30000 points - Make the tracker Half Download for everyone for 3 days: Download will count only half.\r\nIf you don&#039;t have enough points you can donate certain amount of your points until it accumulates. Everybody&#039;s karma counts!', 'halfdown', 1, 0.0, 'yes', 1);
INSERT INTO `bonus` VALUES (14, '1.0GB Download Removal', 150.0, 'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.', 'traffic2', 1073741824, 1.0, 'no', 150);
INSERT INTO `bonus` VALUES (15, '2.5GB Download Removal', 300.0, 'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.', 'traffic2', 2684354560, 1.0, 'no', 300);
INSERT INTO `bonus` VALUES (16, '5GB Download Removal', 500.0, 'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.', 'traffic2', 5368709120, 1.0, 'yes', 500);
INSERT INTO `bonus` VALUES (17, 'Anonymous Profile', 750.0, 'With enough bonus points acquired, you are able to exchange them for Anonymous profile for 14 days. The points are then removed from your Bonus Bank and the Anonymous switch will show on your profile.', 'anonymous', 1, 1.0, 'yes', 750);
INSERT INTO `bonus` VALUES (18, 'Freeleech for 1 Year', 80000.0, 'With enough bonus points acquired, you are able to exchange them for Freelech for one year for yourself. The points are then removed from your Bonus Bank and the freeleech will be enabled on your account.', 'freeyear', 1, 1.0, 'yes', 80000);
INSERT INTO `bonus` VALUES (19, '3 Freeleech Slots', 1000.0, 'With enough bonus points acquired, you are able to exchange them for some Freeleech Slots. The points are then removed from your Bonus Bank and the slots are added to your free slots amount.', 'freeslots', 3, 0.0, 'yes', 1000);
INSERT INTO `bonus` VALUES (20, '200 Bonus Points - Invite trade-in', 1.0, 'If you have 1 invite and dont use them click the button to trade them in for 200 Bonus Points.', 'itrade', 200, 0.0, 'no', 0);
INSERT INTO `bonus` VALUES (21, 'Freeslots - Invite trade-in', 1.0, 'If you have 1 invite and dont use them click the button to trade them in for 2 Free Slots.', 'itrade2', 2, 0.0, 'no', 0);
INSERT INTO `bonus` VALUES (22, 'Pirate Rank for 2 weeks', 50000.0, 'With enough bonus points acquired, you are able to exchange them for Pirates status and Freeleech for 2 weeks. The points are then removed from your Bonus Bank and the Pirate icon will be displayed throughout, freeleech will then be enabled on your account.', 'pirate', 1, 1.0, 'yes', 50000);
INSERT INTO `bonus` VALUES (23, 'King Rank for 1 month', 70000.0, 'With enough bonus points acquired, you are able to exchange them for Kings status and Freeleech for 1 month. The points are then removed from your Bonus Bank and the King icon will be displayed throughout,  freeleech will then be enabled on your account.', 'king', 1, 1.0, 'yes', 70000);
INSERT INTO `bonus` VALUES (24, '10GB Uploaded', 1000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 10737418240, 0.0, 'no', 1000);
INSERT INTO `bonus` VALUES (25, '25GB Uploaded', 2000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 26843545600, 0.0, 'no', 2000);
INSERT INTO `bonus` VALUES (26, '50GB Uploaded', 4000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 53687091200, 0.0, 'no', 4000);
INSERT INTO `bonus` VALUES (27, '100GB Uploaded', 8000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 107374182400, 0.0, 'no', 8000);
INSERT INTO `bonus` VALUES (28, '520GB Uploaded', 40000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 558345748480, 0.0, 'no', 40000);
INSERT INTO `bonus` VALUES (29, '1TB Uploaded', 80000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 1099511627776, 0.0, 'yes', 80000);

-- --------------------------------------------------------

-- 
-- Table structure for table `bonuslog`
-- 

CREATE TABLE `bonuslog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `donation` decimal(10,1) NOT NULL,
  `type` varchar(44) collate utf8_unicode_ci NOT NULL,
  `added_at` int(11) NOT NULL,
  KEY `id` (`id`),
  KEY `added_at` (`added_at`),
  FULLTEXT KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='log of contributors towards freeleech etc...';

-- 
-- Dumping data for table `bonuslog`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `bookmarks`
-- 

CREATE TABLE `bookmarks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `torrentid` int(10) unsigned NOT NULL default '0',
  `private` enum('yes','no') character set utf8 NOT NULL default 'yes',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `bookmarks`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `categories`
-- 

CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) collate utf8_unicode_ci NOT NULL,
  `image` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cat_desc` varchar(255) collate utf8_unicode_ci NOT NULL default 'No Description',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `categories`
-- 

INSERT INTO `categories` VALUES (14, 'Apps', 'cat_misc.gif', 'Apps&Mis');
INSERT INTO `categories` VALUES (2, 'Games/PC ISO', 'cat_games.gif', 'No Description');
INSERT INTO `categories` VALUES (3, 'Movies/SVCD', 'cat_screeners.gif', 'No Description');
INSERT INTO `categories` VALUES (4, 'Music', 'cat_mp3.gif', 'No Description');
INSERT INTO `categories` VALUES (5, 'Episodes', 'cat_episode.gif', 'No Description');
INSERT INTO `categories` VALUES (6, 'XXX', 'cat_xxx.gif', 'No Description');
INSERT INTO `categories` VALUES (7, 'Games/GBA', 'cat_games.gif', 'No Description');
INSERT INTO `categories` VALUES (8, 'Games/PS2', 'cat_games.gif', 'No Description');
INSERT INTO `categories` VALUES (9, 'Anime', 'cat_anime.gif', 'No Description');
INSERT INTO `categories` VALUES (10, 'Movies/XviD', 'cat_xvid.gif', 'No Description');
INSERT INTO `categories` VALUES (11, 'Movies/DVD-R', 'cat_dvdr.gif', 'No Description');
INSERT INTO `categories` VALUES (12, 'Games/PC Rips', 'cat_games.gif', 'No Description');
INSERT INTO `categories` VALUES (13, 'Appz/misc', 'cat_misc.gif', 'No Description');
INSERT INTO `categories` VALUES (1, 'Apps', 'cat_misc.gif', 'Apps-Mis');

-- --------------------------------------------------------

-- 
-- Table structure for table `cheaters`
-- 

CREATE TABLE `cheaters` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `added` int(11) NOT NULL,
  `userid` int(10) NOT NULL default '0',
  `torrentid` int(10) NOT NULL default '0',
  `client` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `rate` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `beforeup` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `upthis` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `timediff` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `userip` varchar(15) collate utf8_unicode_ci NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `cheaters`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `coins`
-- 

CREATE TABLE `coins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `torrentid` int(10) unsigned NOT NULL default '0',
  `points` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `coins`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `comments`
-- 

CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `torrent` int(10) unsigned NOT NULL default '0',
  `added` int(11) NOT NULL,
  `text` text collate utf8_unicode_ci NOT NULL,
  `ori_text` text collate utf8_unicode_ci NOT NULL,
  `editedby` int(10) unsigned NOT NULL default '0',
  `editedat` int(11) NOT NULL,
  `anonymous` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `request` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `comments`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `countries`
-- 

CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_unicode_ci default NULL,
  `flagpic` varchar(50) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `countries`
-- 

INSERT INTO `countries` VALUES (1, 'Sweden', 'sweden.gif');
INSERT INTO `countries` VALUES (2, 'United States of America', 'usa.gif');
INSERT INTO `countries` VALUES (3, 'Russia', 'russia.gif');
INSERT INTO `countries` VALUES (4, 'Finland', 'finland.gif');
INSERT INTO `countries` VALUES (5, 'Canada', 'canada.gif');
INSERT INTO `countries` VALUES (6, 'France', 'france.gif');
INSERT INTO `countries` VALUES (7, 'Germany', 'germany.gif');
INSERT INTO `countries` VALUES (8, 'China', 'china.gif');
INSERT INTO `countries` VALUES (9, 'Italy', 'italy.gif');
INSERT INTO `countries` VALUES (10, 'Denmark', 'denmark.gif');
INSERT INTO `countries` VALUES (11, 'Norway', 'norway.gif');
INSERT INTO `countries` VALUES (12, 'United Kingdom', 'uk.gif');
INSERT INTO `countries` VALUES (13, 'Ireland', 'ireland.gif');
INSERT INTO `countries` VALUES (14, 'Poland', 'poland.gif');
INSERT INTO `countries` VALUES (15, 'Netherlands', 'netherlands.gif');
INSERT INTO `countries` VALUES (16, 'Belgium', 'belgium.gif');
INSERT INTO `countries` VALUES (17, 'Japan', 'japan.gif');
INSERT INTO `countries` VALUES (18, 'Brazil', 'brazil.gif');
INSERT INTO `countries` VALUES (19, 'Argentina', 'argentina.gif');
INSERT INTO `countries` VALUES (20, 'Australia', 'australia.gif');
INSERT INTO `countries` VALUES (21, 'New Zealand', 'newzealand.gif');
INSERT INTO `countries` VALUES (22, 'Spain', 'spain.gif');
INSERT INTO `countries` VALUES (23, 'Portugal', 'portugal.gif');
INSERT INTO `countries` VALUES (24, 'Mexico', 'mexico.gif');
INSERT INTO `countries` VALUES (25, 'Singapore', 'singapore.gif');
INSERT INTO `countries` VALUES (67, 'India', 'india.gif');
INSERT INTO `countries` VALUES (62, 'Albania', 'albania.gif');
INSERT INTO `countries` VALUES (26, 'South Africa', 'southafrica.gif');
INSERT INTO `countries` VALUES (27, 'South Korea', 'southkorea.gif');
INSERT INTO `countries` VALUES (28, 'Jamaica', 'jamaica.gif');
INSERT INTO `countries` VALUES (29, 'Luxembourg', 'luxembourg.gif');
INSERT INTO `countries` VALUES (30, 'Hong Kong', 'hongkong.gif');
INSERT INTO `countries` VALUES (31, 'Belize', 'belize.gif');
INSERT INTO `countries` VALUES (32, 'Algeria', 'algeria.gif');
INSERT INTO `countries` VALUES (33, 'Angola', 'angola.gif');
INSERT INTO `countries` VALUES (34, 'Austria', 'austria.gif');
INSERT INTO `countries` VALUES (35, 'Yugoslavia', 'yugoslavia.gif');
INSERT INTO `countries` VALUES (36, 'Western Samoa', 'westernsamoa.gif');
INSERT INTO `countries` VALUES (37, 'Malaysia', 'malaysia.gif');
INSERT INTO `countries` VALUES (38, 'Dominican Republic', 'dominicanrep.gif');
INSERT INTO `countries` VALUES (39, 'Greece', 'greece.gif');
INSERT INTO `countries` VALUES (40, 'Guatemala', 'guatemala.gif');
INSERT INTO `countries` VALUES (41, 'Israel', 'israel.gif');
INSERT INTO `countries` VALUES (42, 'Pakistan', 'pakistan.gif');
INSERT INTO `countries` VALUES (43, 'Czech Republic', 'czechrep.gif');
INSERT INTO `countries` VALUES (44, 'Serbia', 'serbia.gif');
INSERT INTO `countries` VALUES (45, 'Seychelles', 'seychelles.gif');
INSERT INTO `countries` VALUES (46, 'Taiwan', 'taiwan.gif');
INSERT INTO `countries` VALUES (47, 'Puerto Rico', 'puertorico.gif');
INSERT INTO `countries` VALUES (48, 'Chile', 'chile.gif');
INSERT INTO `countries` VALUES (49, 'Cuba', 'cuba.gif');
INSERT INTO `countries` VALUES (50, 'Congo', 'congo.gif');
INSERT INTO `countries` VALUES (51, 'Afghanistan', 'afghanistan.gif');
INSERT INTO `countries` VALUES (52, 'Turkey', 'turkey.gif');
INSERT INTO `countries` VALUES (53, 'Uzbekistan', 'uzbekistan.gif');
INSERT INTO `countries` VALUES (54, 'Switzerland', 'switzerland.gif');
INSERT INTO `countries` VALUES (55, 'Kiribati', 'kiribati.gif');
INSERT INTO `countries` VALUES (56, 'Philippines', 'philippines.gif');
INSERT INTO `countries` VALUES (57, 'Burkina Faso', 'burkinafaso.gif');
INSERT INTO `countries` VALUES (58, 'Nigeria', 'nigeria.gif');
INSERT INTO `countries` VALUES (59, 'Iceland', 'iceland.gif');
INSERT INTO `countries` VALUES (60, 'Nauru', 'nauru.gif');
INSERT INTO `countries` VALUES (61, 'Slovenia', 'slovenia.gif');
INSERT INTO `countries` VALUES (63, 'Turkmenistan', 'turkmenistan.gif');
INSERT INTO `countries` VALUES (64, 'Bosnia Herzegovina', 'bosniaherzegovina.gif');
INSERT INTO `countries` VALUES (65, 'Andorra', 'andorra.gif');
INSERT INTO `countries` VALUES (66, 'Lithuania', 'lithuania.gif');
INSERT INTO `countries` VALUES (68, 'Netherlands Antilles', 'nethantilles.gif');
INSERT INTO `countries` VALUES (69, 'Ukraine', 'ukraine.gif');
INSERT INTO `countries` VALUES (70, 'Venezuela', 'venezuela.gif');
INSERT INTO `countries` VALUES (71, 'Hungary', 'hungary.gif');
INSERT INTO `countries` VALUES (72, 'Romania', 'romania.gif');
INSERT INTO `countries` VALUES (73, 'Vanuatu', 'vanuatu.gif');
INSERT INTO `countries` VALUES (74, 'Vietnam', 'vietnam.gif');
INSERT INTO `countries` VALUES (75, 'Trinidad & Tobago', 'trinidadandtobago.gif');
INSERT INTO `countries` VALUES (76, 'Honduras', 'honduras.gif');
INSERT INTO `countries` VALUES (77, 'Kyrgyzstan', 'kyrgyzstan.gif');
INSERT INTO `countries` VALUES (78, 'Ecuador', 'ecuador.gif');
INSERT INTO `countries` VALUES (79, 'Bahamas', 'bahamas.gif');
INSERT INTO `countries` VALUES (80, 'Peru', 'peru.gif');
INSERT INTO `countries` VALUES (81, 'Cambodia', 'cambodia.gif');
INSERT INTO `countries` VALUES (82, 'Barbados', 'barbados.gif');
INSERT INTO `countries` VALUES (83, 'Bangladesh', 'bangladesh.gif');
INSERT INTO `countries` VALUES (84, 'Laos', 'laos.gif');
INSERT INTO `countries` VALUES (85, 'Uruguay', 'uruguay.gif');
INSERT INTO `countries` VALUES (86, 'Antigua Barbuda', 'antiguabarbuda.gif');
INSERT INTO `countries` VALUES (87, 'Paraguay', 'paraguay.gif');
INSERT INTO `countries` VALUES (89, 'Thailand', 'thailand.gif');
INSERT INTO `countries` VALUES (88, 'Union of Soviet Socialist Republics', 'ussr.gif');
INSERT INTO `countries` VALUES (90, 'Senegal', 'senegal.gif');
INSERT INTO `countries` VALUES (91, 'Togo', 'togo.gif');
INSERT INTO `countries` VALUES (92, 'North Korea', 'northkorea.gif');
INSERT INTO `countries` VALUES (93, 'Croatia', 'croatia.gif');
INSERT INTO `countries` VALUES (94, 'Estonia', 'estonia.gif');
INSERT INTO `countries` VALUES (95, 'Colombia', 'colombia.gif');
INSERT INTO `countries` VALUES (96, 'Lebanon', 'lebanon.gif');
INSERT INTO `countries` VALUES (97, 'Latvia', 'latvia.gif');
INSERT INTO `countries` VALUES (98, 'Costa Rica', 'costarica.gif');
INSERT INTO `countries` VALUES (99, 'Egypt', 'egypt.gif');
INSERT INTO `countries` VALUES (100, 'Bulgaria', 'bulgaria.gif');
INSERT INTO `countries` VALUES (101, 'Scotland', 'scotland.gif');
INSERT INTO `countries` VALUES (102, 'United Arab Emirates', 'uae.gif');
-- --------------------------------------------------------

-- 
-- Table structure for table `dbbackup`
-- 

CREATE TABLE `dbbackup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL,
  `added` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `dbbackup`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `events`
-- 

CREATE TABLE `events` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `overlayText` text collate utf8_unicode_ci NOT NULL,
  `displayDates` tinyint(1) NOT NULL,
  `freeleechEnabled` tinyint(1) NOT NULL,
  `duploadEnabled` tinyint(1) NOT NULL,
  `hdownEnabled` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `startTime` (`startTime`,`endTime`),
  FULLTEXT KEY `overlayText` (`overlayText`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `events`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `failedlogins`
-- 

CREATE TABLE `failedlogins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `added` int(11) NOT NULL,
  `banned` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `attempts` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `failedlogins`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `files`
-- 

CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `torrent` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `size` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `torrent` (`torrent`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `files`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `forums`
-- 

CREATE TABLE `forums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `description` varchar(200) default NULL,
  `sort` tinyint(3) unsigned NOT NULL default '0',
  `forid` tinyint(4) default '0',
  `postcount` int(10) unsigned NOT NULL default '0',
  `topiccount` int(10) unsigned NOT NULL default '0',
  `minclassread` tinyint(3) unsigned NOT NULL default '0',
  `minclasswrite` tinyint(3) unsigned NOT NULL default '0',
  `minclasscreate` tinyint(3) unsigned NOT NULL default '0',
  `place` int(10) NOT NULL default '-1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `forums`
-- 

INSERT INTO `forums` VALUES (1, 'Test forum 1', 'test description', 0, 1, 0, 0, 0, 0, 0, -1);

-- --------------------------------------------------------

-- 
-- Table structure for table `forum_mods`
-- 

CREATE TABLE `forum_mods` (
  `id` int(10) NOT NULL auto_increment,
  `uid` int(10) NOT NULL default '0',
  `fid` int(10) NOT NULL default '0',
  `user` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`,`fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `forum_mods`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `freeslots`
-- 

CREATE TABLE `freeslots` (
  `tid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `double` int(10) unsigned NOT NULL default '0',
  `free` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `tid_uid` (`tid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `freeslots`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `friends`
-- 

CREATE TABLE `friends` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `friendid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userfriend` (`userid`,`friendid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `friends`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `funds`
-- 

CREATE TABLE `funds` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cash` decimal(8,2) NOT NULL default '0.00',
  `user` int(10) unsigned NOT NULL default '0',
  `added` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `funds`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `happyhour`
-- 

CREATE TABLE `happyhour` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `torrentid` int(10) NOT NULL default '0',
  `multiplier` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`,`torrentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `happyhour`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `happylog`
-- 

CREATE TABLE `happylog` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `torrentid` int(10) NOT NULL default '0',
  `multi` float NOT NULL default '0',
  `date` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`,`torrentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `happylog`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `invite_codes`
-- 

CREATE TABLE `invite_codes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sender` int(10) unsigned NOT NULL default '0',
  `receiver` varchar(32) NOT NULL default '0',
  `code` varchar(32) NOT NULL default '',
  `invite_added` int(10) NOT NULL,
  `status` enum('Pending','Confirmed') NOT NULL default 'Pending',
  PRIMARY KEY  (`id`),
  KEY `sender` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `invite_codes`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `iplog`
-- 

CREATE TABLE `iplog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ip` int(12) default '0',
  `userid` int(10) default NULL,
  `access` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `iplog`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `messages`
-- 

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sender` int(10) unsigned NOT NULL default '0',
  `receiver` int(10) unsigned NOT NULL default '0',
  `added` int(11) NOT NULL,
  `subject` varchar(30) collate utf8_unicode_ci NOT NULL default 'No Subject',
  `msg` text collate utf8_unicode_ci,
  `unread` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `poster` bigint(20) unsigned NOT NULL default '0',
  `location` smallint(6) NOT NULL default '1',
  `saved` enum('no','yes') collate utf8_unicode_ci NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `receiver` (`receiver`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `messages`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `news`
-- 

CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `added` int(11) NOT NULL default '0',
  `body` text character set latin1 NOT NULL,
  `title` varchar(255) character set latin1 NOT NULL default '',
  `sticky` enum('yes','no') character set latin1 NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `news`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `notconnectablepmlog`
-- 

CREATE TABLE `notconnectablepmlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `date` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `notconnectablepmlog`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `overforums`
-- 

CREATE TABLE `overforums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `description` varchar(200) default NULL,
  `minclassview` tinyint(3) unsigned NOT NULL default '0',
  `forid` tinyint(3) unsigned NOT NULL default '1',
  `sort` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `overforums`
-- 

INSERT INTO `overforums` VALUES (1, 'Test', 'Test description', 0, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `peers`
-- 

CREATE TABLE `peers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `torrent` int(10) unsigned NOT NULL default '0',
  `passkey` varchar(32) collate utf8_unicode_ci NOT NULL,
  `peer_id` varchar(20) character set utf8 collate utf8_bin NOT NULL,
  `ip` varchar(64) collate utf8_unicode_ci NOT NULL,
  `port` smallint(5) unsigned NOT NULL default '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `to_go` bigint(20) unsigned NOT NULL default '0',
  `seeder` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `started` int(11) NOT NULL,
  `last_action` int(11) NOT NULL,
  `connectable` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `userid` int(10) unsigned NOT NULL default '0',
  `agent` varchar(60) collate utf8_unicode_ci NOT NULL,
  `finishedat` int(10) unsigned NOT NULL default '0',
  `downloadoffset` bigint(20) unsigned NOT NULL default '0',
  `uploadoffset` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `torrent_peer_id` (`torrent`,`peer_id`),
  KEY `torrent` (`torrent`),
  KEY `torrent_seeder` (`torrent`,`seeder`),
  KEY `last_action` (`last_action`),
  KEY `connectable` (`connectable`),
  KEY `userid` (`userid`),
  KEY `passkey` (`passkey`),
  KEY `torrent_connect` (`torrent`,`connectable`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `peers`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `pmboxes`
-- 

CREATE TABLE `pmboxes` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `boxnumber` tinyint(4) NOT NULL default '2',
  `name` varchar(15) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pmboxes`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `polls`
-- 

CREATE TABLE `polls` (
  `pid` mediumint(8) NOT NULL auto_increment,
  `start_date` int(10) default NULL,
  `choices` mediumtext character set utf8 collate utf8_unicode_ci,
  `starter_id` mediumint(8) NOT NULL default '0',
  `starter_name` varchar(30) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `votes` smallint(5) NOT NULL default '0',
  `poll_question` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `polls`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `poll_voters`
-- 

CREATE TABLE `poll_voters` (
  `vid` int(10) NOT NULL auto_increment,
  `ip_address` varchar(16) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `vote_date` int(10) NOT NULL default '0',
  `poll_id` int(10) NOT NULL default '0',
  `user_id` varchar(32) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`vid`),
  KEY `poll_id` (`poll_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `poll_voters`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `postpollanswers`
-- 

CREATE TABLE `postpollanswers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pollid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `selection` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `postpollanswers`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `postpolls`
-- 

CREATE TABLE `postpolls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `added` int(11) NOT NULL default '0',
  `question` text NOT NULL,
  `option0` varchar(40) NOT NULL default '',
  `option1` varchar(40) NOT NULL default '',
  `option2` varchar(40) NOT NULL default '',
  `option3` varchar(40) NOT NULL default '',
  `option4` varchar(40) NOT NULL default '',
  `option5` varchar(40) NOT NULL default '',
  `option6` varchar(40) NOT NULL default '',
  `option7` varchar(40) NOT NULL default '',
  `option8` varchar(40) NOT NULL default '',
  `option9` varchar(40) NOT NULL default '',
  `option10` varchar(40) NOT NULL default '',
  `option11` varchar(40) NOT NULL default '',
  `option12` varchar(40) NOT NULL default '',
  `option13` varchar(40) NOT NULL default '',
  `option14` varchar(40) NOT NULL default '',
  `option15` varchar(40) NOT NULL default '',
  `option16` varchar(40) NOT NULL default '',
  `option17` varchar(40) NOT NULL default '',
  `option18` varchar(40) NOT NULL default '',
  `option19` varchar(40) NOT NULL default '',
  `sort` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `postpolls`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `posts`
-- 

CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `topicid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `added` int(22) default '0',
  `body` longtext collate utf8_bin,
  `editedby` int(10) unsigned NOT NULL default '0',
  `editedat` int(11) default '0',
  `post_history` mediumtext collate utf8_bin NOT NULL,
  `posticon` int(2) NOT NULL default '0',
  `anonymous` enum('yes','no') collate utf8_bin NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Dumping data for table `posts`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `ratings`
-- 

CREATE TABLE `ratings` (
  `torrent` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `rating` tinyint(3) unsigned NOT NULL default '0',
  `added` int(11) NOT NULL default '0',
  `topic` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`torrent`,`user`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `ratings`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `readposts`
-- 

CREATE TABLE `readposts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `topicid` int(10) unsigned NOT NULL default '0',
  `lastpostread` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topicid` (`topicid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `readposts`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `reports`
-- 

CREATE TABLE `reports` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `reported_by` int(10) unsigned NOT NULL default '0',
  `reporting_what` int(10) unsigned NOT NULL default '0',
  `reporting_type` enum('User','Comment','Request_Comment','Offer_Comment','Request','Offer','Torrent','Hit_And_Run','Post') character set utf8 NOT NULL default 'Torrent',
  `reason` text character set utf8 NOT NULL,
  `who_delt_with_it` int(10) unsigned NOT NULL default '0',
  `delt_with` tinyint(1) NOT NULL default '0',
  `added` int(11) NOT NULL default '0',
  `how_delt_with` text character set utf8 NOT NULL,
  `2nd_value` int(10) unsigned NOT NULL default '0',
  `when_delt_with` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `delt_with` (`delt_with`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 
-- Dumping data for table `reports`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `reputation`
-- 

CREATE TABLE `reputation` (
  `reputationid` int(11) unsigned NOT NULL auto_increment,
  `reputation` int(10) NOT NULL default '0',
  `whoadded` int(10) NOT NULL default '0',
  `reason` varchar(250) collate utf8_unicode_ci default NULL,
  `dateadd` int(10) NOT NULL default '0',
  `locale` enum('posts','comments','torrents','users') collate utf8_unicode_ci NOT NULL default 'posts',
  `postid` int(10) NOT NULL default '0',
  `userid` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`reputationid`),
  KEY `userid` (`userid`),
  KEY `whoadded` (`whoadded`),
  KEY `multi` (`postid`,`userid`),
  KEY `dateadd` (`dateadd`),
  KEY `locale` (`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `reputation`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `reputationlevel`
-- 

CREATE TABLE `reputationlevel` (
  `reputationlevelid` int(11) unsigned NOT NULL auto_increment,
  `minimumreputation` int(10) NOT NULL default '0',
  `level` varchar(250) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`reputationlevelid`),
  KEY `reputationlevel` (`minimumreputation`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `reputationlevel`
-- 

INSERT INTO `reputationlevel` VALUES (1, -999999, 'is infamous around these parts');
INSERT INTO `reputationlevel` VALUES (2, -50, 'can only hope to improve');
INSERT INTO `reputationlevel` VALUES (3, -10, 'has a little shameless behaviour in the past');
INSERT INTO `reputationlevel` VALUES (4, 0, 'is an unknown quantity at this point');
INSERT INTO `reputationlevel` VALUES (5, 15, 'is on a distinguished road');
INSERT INTO `reputationlevel` VALUES (6, 50, 'will become famous soon enough');
INSERT INTO `reputationlevel` VALUES (7, 150, 'has a spectacular aura about');
INSERT INTO `reputationlevel` VALUES (8, 250, 'is a jewel in the rough');
INSERT INTO `reputationlevel` VALUES (9, 350, 'is just really nice');
INSERT INTO `reputationlevel` VALUES (10, 450, 'is a glorious beacon of light');
INSERT INTO `reputationlevel` VALUES (11, 550, 'is a name known to all');
INSERT INTO `reputationlevel` VALUES (12, 650, 'is a splendid one to behold');
INSERT INTO `reputationlevel` VALUES (13, 1000, 'has much to be proud of');
INSERT INTO `reputationlevel` VALUES (14, 1500, 'has a brilliant future');
INSERT INTO `reputationlevel` VALUES (15, 2000, 'has a reputation beyond repute');

-- --------------------------------------------------------

-- 
-- Table structure for table `requests`
-- 

CREATE TABLE `requests` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `request` varchar(225) default NULL,
  `descr` text NOT NULL,
  `added` int(11) unsigned NOT NULL default '0',
  `comments` int(11) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `cat` int(10) unsigned NOT NULL default '0',
  `filledby` int(10) unsigned NOT NULL,
  `torrentid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `id_added` (`id`,`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `requests`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `searchcloud`
-- 

CREATE TABLE `searchcloud` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `searchedfor` varchar(50) collate utf8_unicode_ci NOT NULL,
  `howmuch` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `searchedfor` (`searchedfor`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `searchcloud`
-- 

INSERT INTO `searchcloud` VALUES (1, 'teste', 22);
INSERT INTO `searchcloud` VALUES (2, 'test', 37);
INSERT INTO `searchcloud` VALUES (3, 'look', 2);

-- --------------------------------------------------------

-- 
-- Table structure for table `shoutbox`
-- 

CREATE TABLE `shoutbox` (
  `id` bigint(10) NOT NULL auto_increment,
  `userid` bigint(6) NOT NULL default '0',
  `to_user` int(10) NOT NULL default '0',
  `username` varchar(25) NOT NULL default '',
  `date` int(11) NOT NULL default '0',
  `text` text NOT NULL,
  `text_parsed` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `for` (`to_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `shoutbox`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `sitelog`
-- 

CREATE TABLE `sitelog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `added` int(11) NOT NULL,
  `txt` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `sitelog`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `snatched`
-- 

CREATE TABLE `snatched` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `torrentid` int(10) unsigned NOT NULL default '0',
  `ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `port` smallint(5) unsigned NOT NULL default '0',
  `connectable` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `agent` varchar(60) collate utf8_unicode_ci NOT NULL default '',
  `peer_id` varchar(20) collate utf8_unicode_ci NOT NULL default '',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `upspeed` bigint(20) NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `downspeed` bigint(20) NOT NULL default '0',
  `to_go` bigint(20) unsigned NOT NULL default '0',
  `seeder` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `seedtime` int(11) unsigned NOT NULL default '0',
  `leechtime` int(11) unsigned NOT NULL default '0',
  `start_date` int(11) NOT NULL,
  `last_action` int(11) NOT NULL,
  `complete_date` int(11) NOT NULL,
  `timesann` int(10) unsigned NOT NULL default '0',
  `hit_and_run` int(11) NOT NULL,
  `mark_of_cain` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `finished` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `tr_usr` (`torrentid`,`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `snatched`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `staffmessages`
-- 

CREATE TABLE `staffmessages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sender` int(10) unsigned NOT NULL default '0',
  `added` int(11) default '0',
  `msg` text collate utf8_unicode_ci,
  `subject` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `answeredby` int(10) unsigned NOT NULL default '0',
  `answered` int(1) NOT NULL default '0',
  `answer` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `answeredby` (`answeredby`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `staffmessages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `staffpanel`
-- 

CREATE TABLE `staffpanel` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_name` varchar(80) collate utf8_unicode_ci NOT NULL,
  `file_name` varchar(80) collate utf8_unicode_ci NOT NULL,
  `description` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `av_class` tinyint(3) unsigned NOT NULL default '0',
  `added_by` int(10) unsigned NOT NULL default '0',
  `added` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `file_name` (`file_name`),
  KEY `av_class` (`av_class`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `staffpanel`
-- 

INSERT INTO `staffpanel` VALUES (1, 'Flood Control', 'admin.php?action=floodlimit', 'Manage flood limits', 5, 1, 1277910147);
INSERT INTO `staffpanel` VALUES (2, 'Coders Log', 'admin.php?action=editlog', 'Coders site file edit log', 6, 1, 1277909868);
INSERT INTO `staffpanel` VALUES (3, 'Bonus Manager', 'admin.php?action=bonusmanage', 'Site karma bonus manager', 5, 1, 1277910813);
INSERT INTO `staffpanel` VALUES (4, 'High Speeds', 'admin.php?action=cheaters', 'Detect possible ratio cheats', 4, 1, 1277911147);
INSERT INTO `staffpanel` VALUES (5, 'Non Connectables', 'admin.php?action=findnotconnectable', 'Find - Pm non-connectable users', 4, 1, 1277911274);
INSERT INTO `staffpanel` VALUES (6, 'Manual Cleanup', 'admin.php?action=docleanup', 'Manually run site cleanup cycles', 6, 1, 1277911477);
INSERT INTO `staffpanel` VALUES (7, 'Edit Events', 'admin.php?action=events', 'Edit - Add Freeleech/doubleseed/halfdownload events', 6, 1, 1277911847);
INSERT INTO `staffpanel` VALUES (8, 'Site Log', 'admin.php?action=log', 'View site log', 4, 1, 1277912694);
INSERT INTO `staffpanel` VALUES (9, 'Poll Manager', 'admin.php?action=polls_manager', 'Add - Edit site polls', 4, 1, 1277912814);
INSERT INTO `staffpanel` VALUES (10, 'Ban Ips', 'admin.php?action=bans', 'Cached ip ban manager', 4, 1, 1277912935);
INSERT INTO `staffpanel` VALUES (11, 'Add user', 'admin.php?action=adduser', 'Add new users from site', 5, 1, 1277912999);
INSERT INTO `staffpanel` VALUES (12, 'Extra Stats', 'admin.php?action=stats_extra', 'View graphs of site stats', 5, 1, 1277913051);
INSERT INTO `staffpanel` VALUES (13, 'Templates', 'admin.php?action=themes', 'Site template manager', 6, 1, 1277913213);
INSERT INTO `staffpanel` VALUES (14, 'Tracker Stats', 'admin.php?action=stats', 'View uploader and category activity', 4, 1, 1277913435);
INSERT INTO `staffpanel` VALUES (15, 'Shoutbox History', 'admin.php?action=shistory', 'View shout history', 4, 1, 1277913521);
INSERT INTO `staffpanel` VALUES (16, 'Backup Db', 'admin.php?action=backup', 'Mysql Database Back Up', 6, 1, 1277913720);
INSERT INTO `staffpanel` VALUES (17, 'Usersearch', 'admin.php?action=usersearch', 'Mass pm and Mass announcement system', 5, 1, 1277913916);
INSERT INTO `staffpanel` VALUES (18, 'Manual optimize', 'admin.php?action=mysql_overview', 'Mysql overview', 6, 1, 1277914491);
INSERT INTO `staffpanel` VALUES (19, 'Mysql Stats', 'admin.php?action=mysql_stats', 'Mysql server stats', 6, 1, 1277914654);
INSERT INTO `staffpanel` VALUES (20, 'Failed Logins', 'admin.php?action=failedlogins', 'Clear Failed Logins', 4, 1, 1277914881);
INSERT INTO `staffpanel` VALUES (21, 'Invite Manager', 'admin.php?action=inviteadd', 'Manage site invites', 5, 1, 1277915658);
INSERT INTO `staffpanel` VALUES (22, 'Inactive Users', 'admin.php?action=inactive', 'Manage inactive users', 4, 1, 1277915991);
INSERT INTO `staffpanel` VALUES (23, 'Dupe Ip Check', 'admin.php?action=ipcheck', 'Check duplicate ips', 4, 1, 1277916066);
INSERT INTO `staffpanel` VALUES (24, 'Reset Passwords', 'admin.php?action=reset', 'Reset lost passwords', 4, 1, 1277916104);
INSERT INTO `staffpanel` VALUES (25, 'Forum Manager', 'admin.php?action=forummanager', 'Forum admin and management', 5, 1, 1277916172);
INSERT INTO `staffpanel` VALUES (26, 'Overforum Manager', 'admin.php?action=moforums', 'Over Forum admin and management', 5, 1, 1277916240);
INSERT INTO `staffpanel` VALUES (27, 'Sub Forum Manager', 'admin.php?action=msubforums', 'Sub Forum admin and management', 5, 1, 1277916278);
INSERT INTO `staffpanel` VALUES (28, 'Edit Categories', 'admin.php?action=categories', 'Manage site categories', 6, 1, 1277916351);
INSERT INTO `staffpanel` VALUES (29, 'Reputation Admin', 'reputation_ad.php', 'Reputation system admin', 6, 1, 1277916398);
INSERT INTO `staffpanel` VALUES (30, 'Reputation Settings', 'reputation_settings.php', 'Manage reputation settings', 6, 1, 1277916443);
INSERT INTO `staffpanel` VALUES (31, 'News Admin', 'admin.php?action=news', 'Add - Edit site news', 4, 1, 1277916501);
INSERT INTO `staffpanel` VALUES (32, 'Freeslot Manage', 'admin.php?action=slotmanage', 'Manage site freeslots', 5, 1, 1277916560);
INSERT INTO `staffpanel` VALUES (33, 'Freeleech Manage', 'admin.php?action=freeleech', 'Manage site wide freeleech', 5, 1, 1277916603);
INSERT INTO `staffpanel` VALUES (34, 'Freeleech Users', 'admin.php?action=freeusers', 'View freeleech users', 4, 1, 1277916636);
INSERT INTO `staffpanel` VALUES (35, 'Site Donations', 'admin.php?action=donations', 'View all - current site donations', 6, 1, 1277916690);
INSERT INTO `staffpanel` VALUES (36, 'View Reports', 'admin.php?action=reports', 'Respond to site reports', 4, 1, 1278323407);
INSERT INTO `staffpanel` VALUES (37, 'Delete', 'admin.php?action=delacct', 'Delete user accounts', 4, 1, 1278456787);
INSERT INTO `staffpanel` VALUES (38, 'Username change', 'admin.php?action=namechanger', 'Change usernames here.', 6, 1, 1278886954);
INSERT INTO `staffpanel` VALUES (39, 'Blacklist', 'admin.php?action=nameblacklist', 'Control username blacklist.', 4, 1, 1279054005);
INSERT INTO `staffpanel` VALUES (40, 'System Overview', 'admin.php?action=system_view', 'Monitor load averages and view phpinfo', 6, 1, 1280755500);
INSERT INTO `staffpanel` VALUES (41, 'Snatched Overview', 'admin.php?action=snatched_torrents', 'View all snatched torrents', 4, 1, 1280832713);
INSERT INTO `staffpanel` VALUES (42, 'Pm Overview', 'admin.php?action=pmview', 'Pm overview - For monitoring only !!!', 6, 1, 1280846156);
INSERT INTO `staffpanel` VALUES (43, 'Data Reset', 'admin.php?action=datareset', 'Reset download stats for nuked torrents', 5, 1, 1281095119);

-- --------------------------------------------------------

-- 
-- Table structure for table `stats`
-- 

CREATE TABLE `stats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `regusers` int(10) unsigned NOT NULL default '0',
  `unconusers` int(10) unsigned NOT NULL default '0',
  `torrents` int(10) unsigned NOT NULL default '0',
  `seeders` int(10) unsigned NOT NULL default '0',
  `leechers` int(10) unsigned NOT NULL default '0',
  `torrentstoday` int(10) unsigned NOT NULL default '0',
  `donors` int(10) unsigned NOT NULL default '0',
  `unconnectables` int(10) unsigned NOT NULL default '0',
  `forumtopics` int(10) unsigned NOT NULL default '0',
  `forumposts` int(10) unsigned NOT NULL default '0',
  `numactive` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `stats`
-- 

INSERT INTO `stats` VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `stylesheets`
-- 

CREATE TABLE `stylesheets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uri` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(64) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `stylesheets`
-- 

INSERT INTO `stylesheets` VALUES (1, 'itunes.css', '(Default)');
INSERT INTO `stylesheets` VALUES (2, '2.css', 'Tbdev');
INSERT INTO `stylesheets` VALUES (3, '3.css', 'KidVision 2.0');
INSERT INTO `stylesheets` VALUES (4, '4.css', 'KidVision 1.0');
INSERT INTO `stylesheets` VALUES (5, '5.css', 'UniquePixels1');
INSERT INTO `stylesheets` VALUES (6, '6.css', 'Digitized');
INSERT INTO `stylesheets` VALUES (7, '7.css', 'KidVision 3.0');

-- --------------------------------------------------------

-- 
-- Table structure for table `subscriptions`
-- 

CREATE TABLE `subscriptions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `topicid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `subscriptions`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `thanks`
-- 

CREATE TABLE `thanks` (
  `id` int(11) NOT NULL auto_increment,
  `torrentid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `thanks`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `topics`
-- 

CREATE TABLE `topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `subject` varchar(40) default NULL,
  `locked` enum('yes','no') NOT NULL default 'no',
  `forumid` int(10) unsigned NOT NULL default '0',
  `lastpost` int(10) unsigned NOT NULL default '0',
  `sticky` enum('yes','no') NOT NULL default 'no',
  `views` int(10) unsigned NOT NULL default '0',
  `pollid` int(10) unsigned NOT NULL default '0',
  `anonymous` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `subject` (`subject`),
  KEY `lastpost` (`lastpost`),
  KEY `locked_sticky` (`locked`,`sticky`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `topics`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `torrents`
-- 

CREATE TABLE `torrents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `info_hash` varchar(40) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `save_as` varchar(255) collate utf8_unicode_ci NOT NULL,
  `search_text` text collate utf8_unicode_ci NOT NULL,
  `descr` text collate utf8_unicode_ci NOT NULL,
  `ori_descr` text collate utf8_unicode_ci NOT NULL,
  `category` int(10) unsigned NOT NULL default '0',
  `size` bigint(20) unsigned NOT NULL default '0',
  `added` int(11) NOT NULL,
  `type` enum('single','multi') collate utf8_unicode_ci NOT NULL default 'single',
  `numfiles` int(10) unsigned NOT NULL default '0',
  `comments` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `times_completed` int(10) unsigned NOT NULL default '0',
  `leechers` int(10) unsigned NOT NULL default '0',
  `seeders` int(10) unsigned NOT NULL default '0',
  `last_action` int(11) NOT NULL,
  `visible` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `banned` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `owner` int(10) unsigned NOT NULL default '0',
  `numratings` int(10) unsigned NOT NULL default '0',
  `ratingsum` int(10) unsigned NOT NULL default '0',
  `nfo` text collate utf8_unicode_ci NOT NULL,
  `client_created_by` char(50) collate utf8_unicode_ci NOT NULL default 'unknown',
  `free` int(11) unsigned NOT NULL default '0',
  `sticky` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `anonymous` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `url` varchar(80) collate utf8_unicode_ci default NULL,
  `checked_by` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `points` int(10) NOT NULL default '0',
  `allow_comments` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `poster` varchar(255) character set utf8 collate utf8_bin NOT NULL default 'pic/noposter.png',
  `nuked` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `nukereason` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `last_reseed` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `owner` (`owner`),
  KEY `visible` (`visible`),
  KEY `category_visible` (`category`,`visible`),
  FULLTEXT KEY `ft_search` (`search_text`,`ori_descr`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `torrents`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `uploadapp`
-- 

CREATE TABLE `uploadapp` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `applied` int(11) NOT NULL default '0',
  `speed` varchar(20) collate utf8_unicode_ci NOT NULL default '',
  `offer` longtext collate utf8_unicode_ci NOT NULL,
  `reason` longtext collate utf8_unicode_ci NOT NULL,
  `sites` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `sitenames` varchar(150) collate utf8_unicode_ci NOT NULL default '',
  `scene` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `creating` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `seeding` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `connectable` enum('yes','no','pending') collate utf8_unicode_ci NOT NULL default 'pending',
  `status` enum('accepted','rejected','pending') collate utf8_unicode_ci NOT NULL default 'pending',
  `moderator` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `comment` varchar(200) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `users` (`userid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `uploadapp`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(40) collate utf8_unicode_ci NOT NULL,
  `passhash` varchar(32) collate utf8_unicode_ci NOT NULL,
  `secret` varchar(20) collate utf8_unicode_ci NOT NULL,
  `passkey` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(80) collate utf8_unicode_ci NOT NULL,
  `status` enum('pending','confirmed') collate utf8_unicode_ci NOT NULL default 'pending',
  `added` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  `last_access` int(11) NOT NULL,
  `curr_ann_last_check` int(10) unsigned NOT NULL default '0',
  `curr_ann_id` int(10) unsigned NOT NULL default '0',
  `editsecret` varchar(32) collate utf8_unicode_ci NOT NULL,
  `privacy` enum('strong','normal','low') collate utf8_unicode_ci NOT NULL default 'normal',
  `stylesheet` int(10) default '1',
  `info` text collate utf8_unicode_ci,
  `acceptpms` enum('yes','friends','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `ip` varchar(15) collate utf8_unicode_ci NOT NULL,
  `class` tinyint(3) unsigned NOT NULL default '0',
  `override_class` tinyint(3) unsigned NOT NULL default '255',
  `language` varchar(32) collate utf8_unicode_ci NOT NULL default 'en',
  `avatar` varchar(100) collate utf8_unicode_ci NOT NULL,
  `av_w` smallint(3) unsigned NOT NULL default '0',
  `av_h` smallint(3) unsigned NOT NULL default '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(30) collate utf8_unicode_ci NOT NULL,
  `country` int(10) unsigned NOT NULL default '0',
  `notifs` varchar(100) collate utf8_unicode_ci NOT NULL,
  `modcomment` text collate utf8_unicode_ci NOT NULL,
  `enabled` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `donor` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `warned` int(11) NOT NULL default '0',
  `torrentsperpage` int(3) unsigned NOT NULL default '0',
  `topicsperpage` int(3) unsigned NOT NULL default '0',
  `postsperpage` int(3) unsigned NOT NULL default '0',
  `deletepms` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `savepms` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `reputation` int(10) NOT NULL default '10',
  `time_offset` varchar(5) collate utf8_unicode_ci NOT NULL default '0',
  `dst_in_use` tinyint(1) NOT NULL default '0',
  `auto_correct_dst` tinyint(1) NOT NULL default '1',
  `show_shout` enum('yes','no') character set utf8 collate utf8_bin NOT NULL default 'yes',
  `shoutboxbg` enum('1','2','3') character set utf8 collate utf8_bin NOT NULL default '1',
  `chatpost` int(11) NOT NULL default '1',
  `smile_until` int(10) NOT NULL default '0',
  `seedbonus` decimal(10,1) NOT NULL default '200.0',
  `bonuscomment` text collate utf8_unicode_ci,
  `vip_added` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `vip_until` int(10) NOT NULL default '0',
  `freeslots` int(11) unsigned NOT NULL default '5',
  `free_switch` int(11) unsigned NOT NULL default '0',
  `invites` int(10) unsigned NOT NULL default '1',
  `invitedby` int(10) unsigned NOT NULL default '0',
  `invite_rights` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `anonymous` enum('yes','no') collate utf8_unicode_ci default NULL,
  `uploadpos` int(11) NOT NULL default '1',
  `forumpost` int(11) NOT NULL default '1',
  `downloadpos` int(11) NOT NULL default '1',
  `immunity` int(11) NOT NULL default '0',
  `leechwarn` int(11) NOT NULL default '0',
  `disable_reason` text character set utf8 collate utf8_bin NOT NULL,
  `clear_new_tag_manually` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `last_browse` int(11) NOT NULL default '0',
  `sig_w` smallint(3) unsigned NOT NULL default '0',
  `sig_h` smallint(3) unsigned NOT NULL default '0',
  `signatures` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `signature` varchar(225) collate utf8_unicode_ci NOT NULL default '',
  `forum_access` int(11) NOT NULL default '0',
  `mood` int(10) NOT NULL default '1',
  `highspeed` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `hnrwarn` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `hit_and_run_total` int(9) default '0',
  `donoruntil` int(11) unsigned NOT NULL default '0',
  `donated` decimal(8,2) NOT NULL default '0.00',
  `total_donated` decimal(8,2) NOT NULL default '0.00',
  `vipclass_before` int(10) NOT NULL default '0',
  `parked` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `passhint` int(10) unsigned NOT NULL,
  `hintanswer` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `avatarpos` int(11) NOT NULL default '1',
  `support` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `supportfor` text collate utf8_unicode_ci NOT NULL,
  `sendpmpos` int(11) NOT NULL default '1',
  `invitedate` int(11) NOT NULL default '0',
  `invitees` varchar(100) character set utf8 collate utf8_bin NOT NULL default '',
  `invite_on` enum('yes','no') character set utf8 collate utf8_bin NOT NULL default 'yes',
  `coins` decimal(10,1) unsigned NOT NULL default '100.0',
  `forum_mod` enum('yes','no') character set utf8 collate utf8_bin NOT NULL default 'no',
  `forums_mod` varchar(320) character set utf8 collate utf8_bin NOT NULL default '',
  `subscription_pm` enum('yes','no') character set utf8 collate utf8_bin NOT NULL default 'no',
  `gender` enum('Male','Female','N/A') collate utf8_unicode_ci NOT NULL default 'N/A',
  `anonymous_until` int(10) NOT NULL default '0',
  `viewscloud` enum('yes','no') character set utf8 collate utf8_bin NOT NULL default 'yes',
  `tenpercent` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `forcessl` enum('yes','no') character set utf8 collate utf8_bin NOT NULL default 'no',
  `avatars` enum('all','some','none') collate utf8_unicode_ci NOT NULL default 'all',
  `offavatar` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `pirate` int(11) unsigned NOT NULL default '0',
  `king` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `ip` (`ip`),
  KEY `uploaded` (`uploaded`),
  KEY `downloaded` (`downloaded`),
  KEY `country` (`country`),
  KEY `last_access` (`last_access`),
  KEY `enabled` (`enabled`),
  KEY `warned` (`warned`),
  KEY `pkey` (`passkey`),
  KEY `free_switch` (`free_switch`),
  KEY `iphistory` (`ip`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `users`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `voted_requests`
-- 

CREATE TABLE `voted_requests` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `requestid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`id`),
  KEY `userid` (`userid`),
  KEY `requestid_userid` (`requestid`,`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `voted_requests`
--