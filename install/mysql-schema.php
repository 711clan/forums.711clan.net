<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.7 PL1 - Licence Number VBF2470E4F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2007 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);

define('SCHEMA', 'mysql');

if (!is_object($db))
{
	die('<strong>MySQL Schema</strong>: $db is not an instance of the vB Database class. This script requires the escape_string() method from the vB Database class.');
}

$enginetype = (version_compare(MYSQL_VERSION, '4.0.18', '<')) ? 'TYPE' : 'ENGINE';
$tabletype = (version_compare(MYSQL_VERSION, '4.1', '<')) ? 'HEAP' : 'MEMORY';

$phrasegroups = array();
$specialtemplates = array();

// Check userfield table is still used and how long the default length should be

$schema['CREATE']['query']['access'] = "
CREATE TABLE " . TABLE_PREFIX . "access (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	forumid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	accessmask SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY userid (userid, forumid)
)
";
$schema['CREATE']['explain']['access'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "access");

$schema['CREATE']['query']['adminhelp'] = "
CREATE TABLE " . TABLE_PREFIX . "adminhelp (
	adminhelpid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	script VARCHAR(50) NOT NULL DEFAULT '',
	action VARCHAR(25) NOT NULL DEFAULT '',
	optionname VARCHAR(100) NOT NULL DEFAULT '',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	volatile SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	product VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (adminhelpid),
	UNIQUE KEY phraseunique (script, action, optionname)
)
";
$schema['CREATE']['explain']['adminhelp'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "adminhelp");



$schema['CREATE']['query']['administrator'] = "
CREATE TABLE " . TABLE_PREFIX . "administrator (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	adminpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	navprefs MEDIUMTEXT,
	cssprefs VARCHAR(250) NOT NULL DEFAULT '',
	notes MEDIUMTEXT,
	dismissednews TEXT,
	languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (userid)
)
";
$schema['CREATE']['explain']['administrator'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "administrator");



$schema['CREATE']['query']['adminlog'] = "
CREATE TABLE " . TABLE_PREFIX . "adminlog (
	adminlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	script VARCHAR(50) NOT NULL DEFAULT '',
	action VARCHAR(20) NOT NULL DEFAULT '',
	extrainfo VARCHAR(200) NOT NULL DEFAULT '',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
	PRIMARY KEY (adminlogid),
	KEY script_action (script, action)
)
";
$schema['CREATE']['explain']['adminlog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "adminlog");



$schema['CREATE']['query']['adminmessage'] = "
CREATE TABLE " . TABLE_PREFIX . "adminmessage (
	adminmessageid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	varname varchar(250) NOT NULL DEFAULT '',
	dismissable SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	script varchar(50) NOT NULL DEFAULT '',
	action varchar(20) NOT NULL DEFAULT '',
	execurl mediumtext NOT NULL,
	method enum('get','post') NOT NULL DEFAULT 'post',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	status enum('undone','done','dismissed') NOT NULL default 'undone',
	statususerid INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (adminmessageid),
	KEY script_action (script, action),
	KEY varname (varname)
)
";
$schema['CREATE']['explain']['adminmessage'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "adminmessage");



$schema['CREATE']['query']['adminutil'] = "
CREATE TABLE " . TABLE_PREFIX . "adminutil (
	title VARCHAR(50) NOT NULL DEFAULT '',
	text MEDIUMTEXT,
	PRIMARY KEY (title)
)
";
$schema['CREATE']['explain']['adminutil'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "adminutil");



$schema['CREATE']['query']['announcement'] = "
CREATE TABLE " . TABLE_PREFIX . "announcement (
	announcementid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(250) NOT NULL DEFAULT '',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	startdate INT UNSIGNED NOT NULL DEFAULT '0',
	enddate INT UNSIGNED NOT NULL DEFAULT '0',
	pagetext MEDIUMTEXT,
	forumid SMALLINT NOT NULL DEFAULT '0',
	views INT UNSIGNED NOT NULL DEFAULT '0',
	announcementoptions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (announcementid),
	KEY forumid (forumid),
	KEY startdate (enddate, forumid, startdate)
)
";
$schema['CREATE']['explain']['announcement'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "announcement");


$schema['CREATE']['query']['announcementread'] = "
CREATE TABLE " . TABLE_PREFIX . "announcementread (
	announcementid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY  (announcementid,userid),
	KEY userid (userid)
)
";
$schema['CREATE']['explain']['announcementread'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "announcementread");


$schema['CREATE']['query']['attachment'] = "
CREATE TABLE " . TABLE_PREFIX . "attachment (
	attachmentid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	thumbnail_dateline INT UNSIGNED NOT NULL DEFAULT '0',
	filename VARCHAR(100) NOT NULL DEFAULT '',
	filedata MEDIUMBLOB,
	visible SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	counter INT UNSIGNED NOT NULL DEFAULT '0',
	filesize INT UNSIGNED NOT NULL DEFAULT '0',
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	filehash CHAR(32) NOT NULL DEFAULT '',
	posthash CHAR(32) NOT NULL DEFAULT '',
	thumbnail MEDIUMBLOB,
	thumbnail_filesize INT UNSIGNED NOT NULL DEFAULT '0',
	extension VARCHAR(20) BINARY NOT NULL DEFAULT '',
	PRIMARY KEY (attachmentid),
	KEY filesize (filesize),
	KEY filehash (filehash),
	KEY userid (userid),
	KEY posthash (posthash, userid),
	KEY postid (postid),
	KEY visible (visible)
)
";
$schema['CREATE']['explain']['attachment'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "attachment");


$schema['CREATE']['query']['attachmentpermission'] = "
CREATE TABLE " . TABLE_PREFIX . "attachmentpermission (
  attachmentpermissionid INT UNSIGNED NOT NULL AUTO_INCREMENT,
  extension VARCHAR(20) BINARY NOT NULL DEFAULT '',
  usergroupid INT UNSIGNED NOT NULL,
  size INT UNSIGNED NOT NULL,
  width SMALLINT UNSIGNED NOT NULL,
  height SMALLINT UNSIGNED NOT NULL,
  attachmentpermissions INT UNSIGNED NOT NULL,
  PRIMARY KEY  (attachmentpermissionid),
  UNIQUE KEY extension (extension, usergroupid),
  KEY usergroupid (usergroupid)
)
";
$schema['CREATE']['explain']['attachmentpermission'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "attachmentpermission");


$schema['CREATE']['query']['attachmenttype'] = "
CREATE TABLE " . TABLE_PREFIX . "attachmenttype (
	extension CHAR(20) BINARY NOT NULL DEFAULT '',
	mimetype VARCHAR(255) NOT NULL DEFAULT '',
	size INT UNSIGNED NOT NULL DEFAULT '0',
	width SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	height SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	enabled SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	display SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	thumbnail SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	newwindow SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (extension),
	KEY enabled (enabled)
)
";
$schema['CREATE']['explain']['attachmenttype'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "attachmenttype");



$schema['CREATE']['query']['attachmentviews'] = "
CREATE TABLE " . TABLE_PREFIX . "attachmentviews (
	attachmentid INT UNSIGNED NOT NULL DEFAULT '0',
	KEY postid (attachmentid)
)
";
$schema['CREATE']['explain']['attachmentviews'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "attachmentviews");



$schema['CREATE']['query']['avatar'] = "
CREATE TABLE " . TABLE_PREFIX . "avatar (
	avatarid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(100) NOT NULL DEFAULT '',
	minimumposts INT UNSIGNED NOT NULL DEFAULT '0',
	avatarpath VARCHAR(100) NOT NULL DEFAULT '',
	imagecategoryid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (avatarid)
)
";
$schema['CREATE']['explain']['avatar'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "avatar");



$schema['CREATE']['query']['bbcode'] = "
CREATE TABLE " . TABLE_PREFIX . "bbcode (
	bbcodeid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	bbcodetag VARCHAR(200) NOT NULL DEFAULT '',
	bbcodereplacement MEDIUMTEXT,
	bbcodeexample VARCHAR(200) NOT NULL DEFAULT '',
	bbcodeexplanation MEDIUMTEXT,
	twoparams SMALLINT NOT NULL DEFAULT '0',
	title VARCHAR(100) NOT NULL DEFAULT '',
	buttonimage VARCHAR(250) NOT NULL DEFAULT '',
	PRIMARY KEY (bbcodeid),
	UNIQUE KEY uniquetag (bbcodetag, twoparams)
)
";
$schema['CREATE']['explain']['bbcode'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "bbcode");



$schema['CREATE']['query']['calendar'] = "
CREATE TABLE " . TABLE_PREFIX . "calendar (
	calendarid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL DEFAULT '',
	description VARCHAR(100) NOT NULL DEFAULT '',
	displayorder SMALLINT NOT NULL DEFAULT '0',
	neweventemail TEXT,
	moderatenew SMALLINT NOT NULL DEFAULT '0',
	startofweek SMALLINT NOT NULL DEFAULT '0',
	options INT UNSIGNED NOT NULL DEFAULT '0',
	cutoff SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	eventcount SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	birthdaycount SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	startyear SMALLINT UNSIGNED NOT NULL DEFAULT '2000',
	endyear SMALLINT UNSIGNED NOT NULL DEFAULT '2006',
	holidays INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (calendarid),
	KEY displayorder (displayorder)
)
";
$schema['CREATE']['explain']['calendar'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "calendar");



$schema['CREATE']['query']['calendarcustomfield'] = "
CREATE TABLE " . TABLE_PREFIX . "calendarcustomfield (
	calendarcustomfieldid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	calendarid INT UNSIGNED NOT NULL DEFAULT '0',
	title VARCHAR(255) NOT NULL DEFAULT '',
	description MEDIUMTEXT,
	options MEDIUMTEXT,
	allowentry SMALLINT NOT NULL DEFAULT '1',
	required SMALLINT NOT NULL DEFAULT '0',
	length SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (calendarcustomfieldid),
	KEY calendarid (calendarid)
)
";
$schema['CREATE']['explain']['calendarcustomfield'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "calendarcustomfield");



$schema['CREATE']['query']['calendarmoderator'] = "
CREATE TABLE " . TABLE_PREFIX . "calendarmoderator (
	calendarmoderatorid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	calendarid INT UNSIGNED NOT NULL DEFAULT '0',
	neweventemail SMALLINT NOT NULL DEFAULT '0',
	permissions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (calendarmoderatorid),
	KEY userid (userid, calendarid)
)
";
$schema['CREATE']['explain']['calendarmoderator'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "calendarmoderator");



$schema['CREATE']['query']['calendarpermission'] = "
CREATE TABLE " . TABLE_PREFIX . "calendarpermission (
	calendarpermissionid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	calendarid INT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	calendarpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (calendarpermissionid),
	KEY calendarid (calendarid),
	KEY usergroupid (usergroupid)
)
";
$schema['CREATE']['explain']['calendarpermission'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "calendarpermission");



$schema['CREATE']['query']['cpsession'] = "
CREATE TABLE " . TABLE_PREFIX . "cpsession (
		userid INT UNSIGNED NOT NULL DEFAULT '0',
		hash VARCHAR(32) NOT NULL DEFAULT '',
		dateline INT UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (userid, hash)
)
";
$schema['CREATE']['explain']['cpsession'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "cpsession");



$schema['CREATE']['query']['cron'] = "
CREATE TABLE " . TABLE_PREFIX . "cron (
	cronid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	nextrun INT UNSIGNED NOT NULL DEFAULT '0',
	weekday SMALLINT NOT NULL DEFAULT '0',
	day SMALLINT NOT NULL DEFAULT '0',
	hour SMALLINT NOT NULL DEFAULT '0',
	minute VARCHAR(100) NOT NULL DEFAULT '',
	filename CHAR(50) NOT NULL DEFAULT '',
	loglevel SMALLINT NOT NULL DEFAULT '0',
	active SMALLINT NOT NULL DEFAULT '1',
	varname VARCHAR(100) NOT NULL DEFAULT '',
	volatile SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	product VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (cronid),
	KEY nextrun (nextrun),
	UNIQUE KEY (varname)
)
";
$schema['CREATE']['explain']['cron'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "cron");



$schema['CREATE']['query']['cronlog'] = "
CREATE TABLE " . TABLE_PREFIX . "cronlog (
	cronlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	varname VARCHAR(100) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	description MEDIUMTEXT,
	type SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (cronlogid),
	KEY (varname)
)
";
$schema['CREATE']['explain']['cronlog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "cronlog");



$schema['CREATE']['query']['customavatar'] = "
CREATE TABLE " . TABLE_PREFIX . "customavatar (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	filedata MEDIUMBLOB,
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	filename VARCHAR(100) NOT NULL DEFAULT '',
	visible SMALLINT NOT NULL DEFAULT '1',
	filesize INT UNSIGNED NOT NULL DEFAULT '0',
	width SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	height SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (userid)
)
";
$schema['CREATE']['explain']['customavatar'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "customavatar");



$schema['CREATE']['query']['customprofilepic'] = "
CREATE TABLE " . TABLE_PREFIX . "customprofilepic (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	filedata MEDIUMBLOB,
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	filename VARCHAR(100) NOT NULL DEFAULT '',
	visible SMALLINT NOT NULL DEFAULT '1',
	filesize INT UNSIGNED NOT NULL DEFAULT '0',
	width SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	height SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (userid)
)
";
$schema['CREATE']['explain']['customprofilepic'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "customprofilepic");



$schema['CREATE']['query']['datastore'] = "
CREATE TABLE " . TABLE_PREFIX . "datastore (
	title CHAR(50) NOT NULL DEFAULT '',
	data MEDIUMTEXT,
	unserialize SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY (title)
)
";
$schema['CREATE']['explain']['datastore'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "datastore");

$schema['CREATE']['query']['deletionlog'] = "
CREATE TABLE " . TABLE_PREFIX . "deletionlog (
	primaryid INT UNSIGNED NOT NULL DEFAULT '0',
	type ENUM('post', 'thread') NOT NULL DEFAULT 'post',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	username VARCHAR(100) NOT NULL DEFAULT '',
	reason VARCHAR(125) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (primaryid, type),
	KEY type (type, dateline)
)
";
$schema['CREATE']['explain']['deletionlog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "deletionlog");



$schema['CREATE']['query']['editlog'] = "
CREATE TABLE " . TABLE_PREFIX . "editlog (
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	username VARCHAR(100) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	reason VARCHAR(200) NOT NULL DEFAULT '',
	PRIMARY KEY (postid)
)
";
$schema['CREATE']['explain']['editlog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "editlog");



$schema['CREATE']['query']['event'] = "
CREATE TABLE " . TABLE_PREFIX . "event (
	eventid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	event MEDIUMTEXT,
	title VARCHAR(250) NOT NULL DEFAULT '',
	allowsmilies SMALLINT NOT NULL DEFAULT '1',
	recurring SMALLINT NOT NULL DEFAULT '0',
	recuroption CHAR(6) NOT NULL DEFAULT '',
	calendarid INT UNSIGNED NOT NULL DEFAULT '0',
	customfields MEDIUMTEXT,
	visible SMALLINT NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	utc SMALLINT NOT NULL DEFAULT '0',
	dst SMALLINT NOT NULL DEFAULT '1',
	dateline_from INT UNSIGNED NOT NULL DEFAULT '0',
	dateline_to INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (eventid),
	KEY userid (userid),
	KEY calendarid (calendarid),
	KEY (visible),
	KEY daterange (dateline_to, dateline_from, visible, calendarid)
)
";
$schema['CREATE']['explain']['event'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "event");

$schema['CREATE']['query']['faq'] = "
CREATE TABLE " . TABLE_PREFIX . "faq (
	faqname VARCHAR(250) BINARY NOT NULL DEFAULT '',
	faqparent VARCHAR(50) NOT NULL DEFAULT '',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	volatile SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	product VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (faqname),
	KEY faqparent (faqparent)
)
";
$schema['CREATE']['explain']['faq'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "faq");

$schema['CREATE']['query']['externalcache'] = "
CREATE TABLE " . TABLE_PREFIX . "externalcache (
	cachehash CHAR(32) NOT NULL DEFAULT '',
	text MEDIUMTEXT,
	headers MEDIUMTEXT,
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	forumid INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (cachehash),
	KEY dateline (dateline, cachehash),
	KEY forumid (forumid)
)
";
$schema['CREATE']['explain']['externalcache'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "externalcache");

$schema['CREATE']['query']['forum'] = "
CREATE TABLE " . TABLE_PREFIX . "forum (
	forumid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	styleid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	title VARCHAR(100) NOT NULL DEFAULT '',
	title_clean VARCHAR(100) NOT NULL DEFAULT '',
	description TEXT,
	description_clean TEXT,
	options INT UNSIGNED NOT NULL DEFAULT '0',
	showprivate TINYINT UNSIGNED NOT NULL DEFAULT '0',
	displayorder SMALLINT NOT NULL DEFAULT '0',
	replycount INT UNSIGNED NOT NULL DEFAULT '0',
	lastpost INT NOT NULL DEFAULT '0',
	lastposter VARCHAR(100) NOT NULL DEFAULT '',
	lastpostid INT UNSIGNED NOT NULL DEFAULT '0',
	lastthread VARCHAR(250) NOT NULL DEFAULT '',
	lastthreadid INT UNSIGNED NOT NULL DEFAULT '0',
	lasticonid SMALLINT NOT NULL DEFAULT '0',
	threadcount mediumint UNSIGNED NOT NULL DEFAULT '0',
	daysprune SMALLINT NOT NULL DEFAULT '0',
	newpostemail TEXT,
	newthreademail TEXT,
	parentid SMALLINT NOT NULL DEFAULT '0',
	parentlist VARCHAR(250) NOT NULL DEFAULT '',
	password VARCHAR(50) NOT NULL DEFAULT '',
	link VARCHAR(200) NOT NULL DEFAULT '',
	childlist TEXT,
	defaultsortfield VARCHAR(50) NOT NULL DEFAULT 'lastpost',
	defaultsortorder ENUM('asc', 'desc') NOT NULL DEFAULT 'desc',
	PRIMARY KEY (forumid)
)
";
$schema['CREATE']['explain']['forum'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "forum");



$schema['CREATE']['query']['forumread'] = "
CREATE TABLE " . TABLE_PREFIX . "forumread (
	userid int(10) unsigned NOT NULL default '0',
	forumid smallint(5) unsigned NOT NULL default '0',
	readtime int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (forumid, userid),
	KEY readtime (readtime)
)
";
$schema['CREATE']['explain']['forumread'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "forumread");



$schema['CREATE']['query']['forumpermission'] = "
CREATE TABLE " . TABLE_PREFIX . "forumpermission (
	forumpermissionid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	forumid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	forumpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (forumpermissionid),
	UNIQUE KEY ugid_fid (usergroupid, forumid)
)
";
$schema['CREATE']['explain']['forumpermission'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "forumpermission");



$schema['CREATE']['query']['holiday'] = "
CREATE TABLE " . TABLE_PREFIX . "holiday (
	holidayid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	varname VARCHAR(100) NOT NULL DEFAULT '',
	recurring SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	recuroption CHAR(6) NOT NULL DEFAULT '',
	allowsmilies SMALLINT NOT NULL DEFAULT '1',
	PRIMARY KEY (holidayid),
	KEY varname (varname)
)
";
$schema['CREATE']['explain']['holiday'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "holiday");



$schema['CREATE']['query']['icon'] = "
CREATE TABLE " . TABLE_PREFIX . "icon (
	iconid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(100) NOT NULL DEFAULT '',
	iconpath VARCHAR(100) NOT NULL DEFAULT '',
	imagecategoryid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (iconid)
)
";
$schema['CREATE']['explain']['icon'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "icon");



$schema['CREATE']['query']['imagecategory'] = "
CREATE TABLE " . TABLE_PREFIX . "imagecategory (
	imagecategoryid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL DEFAULT '',
	imagetype SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (imagecategoryid)
)
";
$schema['CREATE']['explain']['imagecategory'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "imagecategory");



$schema['CREATE']['query']['imagecategorypermission'] = "
CREATE TABLE " . TABLE_PREFIX . "imagecategorypermission (
	imagecategoryid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	KEY imagecategoryid (imagecategoryid, usergroupid)
)
";
$schema['CREATE']['explain']['imagecategorypermission'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "imagecategorypermission");


$schema['CREATE']['query']['infraction'] = "
CREATE TABLE " . TABLE_PREFIX . "infraction (
	infractionid INT UNSIGNED NOT NULL AUTO_INCREMENT ,
	infractionlevelid INT UNSIGNED NOT NULL DEFAULT '0',
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	whoadded INT UNSIGNED NOT NULL DEFAULT '0',
	points INT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	note varchar(255) NOT NULL DEFAULT '',
	action SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	actiondateline INT UNSIGNED NOT NULL DEFAULT '0',
	actionuserid INT UNSIGNED NOT NULL DEFAULT '0',
	actionreason VARCHAR(255) NOT NULL DEFAULT '0',
	expires INT UNSIGNED NOT NULL DEFAULT '0',
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	customreason VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (infractionid),
	KEY expires (expires, action),
	KEY userid (userid, action),
	KEY infractonlevelid (infractionlevelid),
	KEY postid (postid),
	KEY threadid (threadid)
)
";
$schema['CREATE']['explain']['infraction'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "infraction");

$schema['CREATE']['query']['infractionban'] = "
CREATE TABLE " . TABLE_PREFIX . "infractionban (
  infractionbanid int unsigned NOT NULL auto_increment,
  usergroupid int NOT NULL DEFAULT '0',
  banusergroupid int unsigned NOT NULL DEFAULT '0',
  amount int unsigned NOT NULL DEFAULT '0',
  period char(5) NOT NULL DEFAULT '',
  method enum('points','infractions') NOT NULL default 'infractions',
  PRIMARY KEY (infractionbanid),
  KEY usergroupid (usergroupid)
)
";
$schema['CREATE']['explain']['infractionban'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "infractionban");

$schema['CREATE']['query']['infractiongroup'] = "
CREATE TABLE " . TABLE_PREFIX . "infractiongroup (
	infractiongroupid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	usergroupid INT NOT NULL DEFAULT '0',
	orusergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	pointlevel INT UNSIGNED NOT NULL DEFAULT '0',
	override SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (infractiongroupid),
	KEY usergroupid (usergroupid, pointlevel)
)
";
$schema['CREATE']['explain']['infractiongroup'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "infractiongroup");


$schema['CREATE']['query']['infractionlevel'] = "
CREATE TABLE " . TABLE_PREFIX . "infractionlevel (
	infractionlevelid INT UNSIGNED NOT NULL AUTO_INCREMENT ,
	points INT UNSIGNED NOT NULL DEFAULT '0',
	expires INT UNSIGNED NOT NULL DEFAULT '0',
	period ENUM('H','D','M','N') DEFAULT 'H' NOT NULL,
	warning SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	extend SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (infractionlevelid)
)
";
$schema['CREATE']['explain']['infractionlevel'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "infractionlevel");


$schema['CREATE']['query']['language'] = "
CREATE TABLE " . TABLE_PREFIX . "language (
	languageid smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(50) NOT NULL default '',
	userselect smallint(5) UNSIGNED NOT NULL default '1',
	options smallint(5) UNSIGNED NOT NULL default '1',
	languagecode VARCHAR(12) NOT NULL default '',
	charset VARCHAR(15) NOT NULL default '',
	imagesoverride VARCHAR(150) NOT NULL default '',
	dateoverride VARCHAR(50) NOT NULL default '',
	timeoverride VARCHAR(50) NOT NULL default '',
	registereddateoverride VARCHAR(50) NOT NULL default '',
	calformat1override VARCHAR(50) NOT NULL default '',
	calformat2override VARCHAR(50) NOT NULL default '',
	logdateoverride VARCHAR(50) NOT NULL default '',
	locale VARCHAR(20) NOT NULL default '',
	decimalsep CHAR(1) NOT NULL default '.',
	thousandsep CHAR(1) NOT NULL default ',',
	phrasegroup_global MEDIUMTEXT,
	phrasegroup_cpglobal MEDIUMTEXT,
	phrasegroup_cppermission MEDIUMTEXT,
	phrasegroup_forum MEDIUMTEXT,
	phrasegroup_calendar MEDIUMTEXT,
	phrasegroup_attachment_image MEDIUMTEXT,
	phrasegroup_style MEDIUMTEXT,
	phrasegroup_logging MEDIUMTEXT,
	phrasegroup_cphome MEDIUMTEXT,
	phrasegroup_promotion MEDIUMTEXT,
	phrasegroup_user MEDIUMTEXT,
	phrasegroup_help_faq MEDIUMTEXT,
	phrasegroup_sql MEDIUMTEXT,
	phrasegroup_subscription MEDIUMTEXT,
	phrasegroup_language MEDIUMTEXT,
	phrasegroup_bbcode MEDIUMTEXT,
	phrasegroup_stats MEDIUMTEXT,
	phrasegroup_diagnostic MEDIUMTEXT,
	phrasegroup_maintenance MEDIUMTEXT,
	phrasegroup_profilefield MEDIUMTEXT,
	phrasegroup_thread MEDIUMTEXT,
	phrasegroup_timezone MEDIUMTEXT,
	phrasegroup_banning MEDIUMTEXT,
	phrasegroup_reputation MEDIUMTEXT,
	phrasegroup_wol MEDIUMTEXT,
	phrasegroup_threadmanage MEDIUMTEXT,
	phrasegroup_pm MEDIUMTEXT,
	phrasegroup_cpuser MEDIUMTEXT,
	phrasegroup_accessmask MEDIUMTEXT,
	phrasegroup_cron MEDIUMTEXT,
	phrasegroup_moderator MEDIUMTEXT,
	phrasegroup_cpoption MEDIUMTEXT,
	phrasegroup_cprank MEDIUMTEXT,
	phrasegroup_cpusergroup MEDIUMTEXT,
	phrasegroup_holiday MEDIUMTEXT,
	phrasegroup_posting MEDIUMTEXT,
	phrasegroup_poll MEDIUMTEXT,
	phrasegroup_fronthelp MEDIUMTEXT,
	phrasegroup_register MEDIUMTEXT,
	phrasegroup_search MEDIUMTEXT,
	phrasegroup_showthread MEDIUMTEXT,
	phrasegroup_postbit MEDIUMTEXT,
	phrasegroup_forumdisplay MEDIUMTEXT,
	phrasegroup_messaging MEDIUMTEXT,
	phrasegroup_inlinemod MEDIUMTEXT,
	phrasegroup_plugins MEDIUMTEXT,
	phrasegroup_cprofilefield MEDIUMTEXT,
	phrasegroup_reputationlevel MEDIUMTEXT,
	phrasegroup_infraction MEDIUMTEXT,
	phrasegroup_infractionlevel MEDIUMTEXT,
	PRIMARY KEY  (languageid)
)
";


$schema['CREATE']['explain']['language'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "language");



$schema['CREATE']['query']['mailqueue'] = "
CREATE TABLE " . TABLE_PREFIX . "mailqueue (
	mailqueueid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	toemail MEDIUMTEXT,
	fromemail MEDIUMTEXT,
	subject MEDIUMTEXT,
	message MEDIUMTEXT,
	header MEDIUMTEXT,
	PRIMARY KEY (mailqueueid)
)
";
$schema['CREATE']['explain']['mailqueue'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "mailqueue");



$schema['CREATE']['query']['moderation'] = "
CREATE TABLE " . TABLE_PREFIX . "moderation (
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	type ENUM('thread', 'reply') NOT NULL DEFAULT 'thread',
	dateline INT UNSIGNED NOT NULl DEFAULT '0',
	PRIMARY KEY (postid, type),
	KEY type (type, dateline)
)
";
$schema['CREATE']['explain']['moderation'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "moderation");



$schema['CREATE']['query']['moderator'] = "
CREATE TABLE " . TABLE_PREFIX . "moderator (
	moderatorid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	forumid SMALLINT NOT NULL DEFAULT '0',
	permissions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (moderatorid),
	KEY userid (userid, forumid)
)
";
$schema['CREATE']['explain']['moderator'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "moderator");



$schema['CREATE']['query']['moderatorlog'] = "
CREATE TABLE " . TABLE_PREFIX . "moderatorlog (
	moderatorlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	forumid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	pollid INT UNSIGNED NOT NULL DEFAULT '0',
	attachmentid INT UNSIGNED NOT NULL DEFAULT '0',
	action VARCHAR(250) NOT NULL DEFAULT '',
	type SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	threadtitle VARCHAR(250) NOT NULL DEFAULT '',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
	PRIMARY KEY (moderatorlogid),
	KEY threadid (threadid)
)
";
$schema['CREATE']['explain']['moderatorlog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "moderatorlog");



$schema['CREATE']['query']['passwordhistory'] = "
CREATE TABLE " . TABLE_PREFIX . "passwordhistory (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	password VARCHAR(50) NOT NULL DEFAULT '',
	passworddate date NOT NULL DEFAULT '0000-00-00',
	KEY userid (userid)
)
";
$schema['CREATE']['explain']['passwordhistory'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "passwordhistory");



$schema['CREATE']['query']['paymentapi'] = "
CREATE TABLE " . TABLE_PREFIX . "paymentapi (
	paymentapiid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(250) NOT NULL DEFAULT '',
	currency VARCHAR(250) NOT NULL DEFAULT '',
	recurring SMALLINT NOT NULL DEFAULT '0',
	classname VARCHAR(250) NOT NULL DEFAULT '',
	active SMALLINT NOT NULL DEFAULT '0',
	settings MEDIUMTEXT,
	PRIMARY KEY (paymentapiid)
)
";
$schema['CREATE']['explain']['paymentapi'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "paymentapi");



$schema['CREATE']['query']['paymentinfo'] = "
CREATE TABLE " . TABLE_PREFIX . "paymentinfo (
	paymentinfoid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	hash VARCHAR(32) NOT NULL DEFAULT '',
	subscriptionid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	subscriptionsubid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	completed SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY (paymentinfoid),
	KEY hash (hash)
)
";
$schema['CREATE']['explain']['paymentinfo'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "paymentinfo");



$schema['CREATE']['query']['paymenttransaction'] = "
CREATE TABLE " . TABLE_PREFIX . "paymenttransaction (
	paymenttransactionid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	paymentinfoid INT UNSIGNED NOT NULL DEFAULT '0',
	transactionid VARCHAR(250) NOT NULL DEFAULT '',
	state SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	amount DOUBLE UNSIGNED NOT NULL DEFAULT '0',
	currency VARCHAR(5) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	paymentapiid INT UNSIGNED NOT NULL DEFAULT '0',
	request MEDIUMTEXT,
	reversed INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (paymenttransactionid),
	KEY dateline (dateline),
	KEY transactionid (transactionid),
	KEY paymentapiid (paymentapiid)
)
";
$schema['CREATE']['explain']['paymenttransaction'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "paymenttransaction");



$schema['CREATE']['query']['phrase'] = "
CREATE TABLE " . TABLE_PREFIX . "phrase (
	phraseid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	languageid SMALLINT NOT NULL DEFAULT '0',
	varname VARCHAR(250) BINARY NOT NULL DEFAULT '',
	fieldname VARCHAR(20) NOT NULL DEFAULT '',
	text MEDIUMTEXT,
	product VARCHAR(25) NOT NULL DEFAULT '',
	username VARCHAR(100) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	version VARCHAR(30) NOT NULL DEFAULT '',
	PRIMARY KEY  (phraseid),
	UNIQUE KEY name_lang_type (varname, languageid, fieldname),
	KEY languageid (languageid, fieldname)
)
";
$schema['CREATE']['explain']['phrase'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "phrase");



$schema['CREATE']['query']['phrasetype'] = "
CREATE TABLE " . TABLE_PREFIX . "phrasetype (
	fieldname CHAR(20) NOT NULL default '',
	title CHAR(50) NOT NULL DEFAULT '',
	editrows SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	product VARCHAR(25) NOT NULL DEFAULT '',
	special SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (fieldname)
)
";
$schema['CREATE']['explain']['phrasetype'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "phrasetype");



$schema['CREATE']['query']['plugin'] = "
CREATE TABLE " . TABLE_PREFIX . "plugin (
	pluginid INT unsigned NOT NULL AUTO_INCREMENT,
	title VARCHAR(250) NOT NULL DEFAULT '',
	hookname VARCHAR(250) NOT NULL DEFAULT '',
	phpcode TEXT,
	product VARCHAR(25) NOT NULL DEFAULT '',
	devkey VARCHAR(25) NOT NULL DEFAULT '',
	active SMALLINT(6) NOT NULL DEFAULT '0',
	executionorder SMALLINT UNSIGNED NOT NULL DEFAULT '5',
	PRIMARY KEY (pluginid),
	KEY active (active)
)
";
$schema['CREATE']['explain']['plugin'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "plugin");



$schema['CREATE']['query']['pm'] = "
CREATE TABLE " . TABLE_PREFIX . "pm (
	pmid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	pmtextid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	folderid SMALLINT NOT NULL DEFAULT '0',
	messageread SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (pmid),
	KEY pmtextid (pmtextid),
	KEY userid (userid, folderid)
)
";
$schema['CREATE']['explain']['pm'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "pm");



$schema['CREATE']['query']['pmreceipt'] = "
CREATE TABLE " . TABLE_PREFIX . "pmreceipt (
	pmid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	touserid INT UNSIGNED NOT NULL DEFAULT '0',
	tousername VARCHAR(100) NOT NULL DEFAULT '',
	title VARCHAR(250) NOT NULL DEFAULT '',
	sendtime INT UNSIGNED NOT NULL DEFAULT '0',
	readtime INT UNSIGNED NOT NULL DEFAULT '0',
	denied SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (pmid),
	KEY userid (userid),
	KEY touserid (touserid)
)
";
$schema['CREATE']['explain']['pmreceipt'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "pmreceipt");



$schema['CREATE']['query']['pmtext'] = "
CREATE TABLE " . TABLE_PREFIX . "pmtext (
	pmtextid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	fromuserid INT UNSIGNED NOT NULL DEFAULT '0',
	fromusername VARCHAR(100) NOT NULL DEFAULT '',
	title VARCHAR(250) NOT NULL DEFAULT '',
	message MEDIUMTEXT,
	touserarray MEDIUMTEXT,
	iconid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	showsignature SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	allowsmilie SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (pmtextid),
	KEY fromuserid (fromuserid, dateline)
)
";
$schema['CREATE']['explain']['pmtext'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "pmtext");


$schema['CREATE']['query']['podcast'] = "
CREATE TABLE " . TABLE_PREFIX . "podcast (
	forumid INT UNSIGNED NOT NULL DEFAULT '0',
	author VARCHAR(255) NOT NULL DEFAULT '',
	category VARCHAR(255) NOT NULL DEFAULT '',
	image VARCHAR(255) NOT NULL DEFAULT '',
	explicit SMALLINT NOT NULL DEFAULT '0',
	enabled SMALLINT NOT NULL DEFAULT '1',
	keywords VARCHAR(255) NOT NULL DEFAULT '',
	owneremail VARCHAR(255) NOT NULL DEFAULT '',
	ownername VARCHAR(255) NOT NULL DEFAULT '',
	subtitle VARCHAR(255) NOT NULL DEFAULT '',
	summary MEDIUMTEXT,
	categoryid SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY  (forumid)
)
";
$schema['CREATE']['explain']['podcast'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "podcast");


$schema['CREATE']['query']['podcastitem'] = "
CREATE TABLE " . TABLE_PREFIX . "podcastitem (
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	url VARCHAR(255) NOT NULL DEFAULT '',
	length INT UNSIGNED NOT NULL DEFAULT '0',
	explicit SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	keywords VARCHAR(255) NOT NULL DEFAULT '',
	subtitle VARCHAR(255) NOT NULL DEFAULT '',
	author VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY  (postid)
)
";
$schema['CREATE']['explain']['podcastitem'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "podcastitem");


$schema['CREATE']['query']['poll'] = "
CREATE TABLE " . TABLE_PREFIX . "poll (
	pollid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	question VARCHAR(100) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	options TEXT,
	votes TEXT,
	active SMALLINT NOT NULL DEFAULT '1',
	numberoptions SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	timeout SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	multiple SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	voters SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	public SMALLINT NOT NULL DEFAULT '0',
	lastvote INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (pollid)
)
";
$schema['CREATE']['explain']['poll'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "poll");



$schema['CREATE']['query']['pollvote'] = "
CREATE TABLE " . TABLE_PREFIX . "pollvote (
	pollvoteid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	pollid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	votedate INT UNSIGNED NOT NULL DEFAULT '0',
	voteoption INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (pollvoteid),
	KEY pollid (pollid, userid)
)
";
$schema['CREATE']['explain']['pollvote'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "pollvote");



$schema['CREATE']['query']['post'] = "
CREATE TABLE " . TABLE_PREFIX . "post (
	postid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	parentid INT UNSIGNED NOT NULL DEFAULT '0',
	username VARCHAR(100) NOT NULL DEFAULT '',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	title VARCHAR(250) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	pagetext MEDIUMTEXT,
	allowsmilie SMALLINT NOT NULL DEFAULT '0',
	showsignature SMALLINT NOT NULL DEFAULT '0',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
	iconid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	visible SMALLINT NOT NULL DEFAULT '0',
	attach SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	infraction SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	reportthreadid INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (postid),
	KEY userid (userid),
	KEY threadid (threadid, userid),
	FULLTEXT KEY title (title, pagetext)
) $enginetype=MyISAM
";
$schema['CREATE']['explain']['post'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "post");



$schema['CREATE']['query']['postindex'] = "
CREATE TABLE " . TABLE_PREFIX . "postindex (
	wordid INT UNSIGNED NOT NULL DEFAULT '0',
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	intitle SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	score SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	UNIQUE KEY wordid (wordid, postid)
)
";
$schema['CREATE']['explain']['postindex'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "postindex");



$schema['CREATE']['query']['postparsed'] = "
CREATE TABLE " . TABLE_PREFIX . "postparsed (
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	styleid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	hasimages SMALLINT NOT NULL DEFAULT '0',
	pagetext_html MEDIUMTEXT,
	PRIMARY KEY (postid, styleid, languageid),
	KEY dateline (dateline)
)
";
$schema['CREATE']['explain']['postparsed'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "postparsed");



$schema['CREATE']['query']['posthash'] = "
CREATE TABLE " . TABLE_PREFIX . "posthash (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	postid INT UNSIGNED NOT NULL DEFAULT '0',
	dupehash CHAR(32) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	KEY userid (userid, dupehash),
	KEY dateline (dateline)
)
";
$schema['CREATE']['explain']['posthash'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "posthash");



$schema['CREATE']['query']['product'] = "
CREATE TABLE " . TABLE_PREFIX . "product (
	productid VARCHAR(25) NOT NULL DEFAULT '',
	title VARCHAR(50) NOT NULL DEFAULT '',
	description VARCHAR(250) NOT NULL DEFAULT '',
	version VARCHAR(25) NOT NULL DEFAULT '',
	active SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	url VARCHAR(250) NOT NULL DEFAULT '',
	versioncheckurl VARCHAR(250) NOT NULL DEFAULT '',
	PRIMARY KEY (productid)
)
";
$schema['CREATE']['explain']['product'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "product");



$schema['CREATE']['query']['productcode'] = "
CREATE TABLE " . TABLE_PREFIX . "productcode (
	productcodeid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	productid VARCHAR(25) NOT NULL DEFAULT '',
	version VARCHAR(25) NOT NULL DEFAULT '',
	installcode MEDIUMTEXT,
	uninstallcode MEDIUMTEXT,
	PRIMARY KEY (productcodeid),
	KEY (productid)
)
";
$schema['CREATE']['explain']['productcode'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "productcode");



$schema['CREATE']['query']['productdependency'] = "
CREATE TABLE " . TABLE_PREFIX . "productdependency (
	productdependencyid INT NOT NULL AUTO_INCREMENT,
	productid varchar(25) NOT NULL DEFAULT '',
	dependencytype varchar(25) NOT NULL DEFAULT '',
	parentproductid varchar(25) NOT NULL DEFAULT '',
	minversion varchar(50) NOT NULL DEFAULT '',
	maxversion varchar(50) NOT NULL DEFAULT '',
	PRIMARY KEY (productdependencyid)
)
";
$schema['CREATE']['explain']['productdependency'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "productdependency");



$schema['CREATE']['query']['profilefield'] = "
CREATE TABLE " . TABLE_PREFIX . "profilefield (
	profilefieldid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	profilefieldcategoryid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	required SMALLINT NOT NULL DEFAULT '0',
	hidden SMALLINT NOT NULL DEFAULT '0',
	maxlength SMALLINT NOT NULL DEFAULT '250',
	size SMALLINT NOT NULL DEFAULT '25',
	displayorder SMALLINT NOT NULL DEFAULT '0',
	editable SMALLINT NOT NULL DEFAULT '1',
	type ENUM('input','select','radio','textarea','checkbox','select_multiple') NOT NULL DEFAULT 'input',
	data MEDIUMTEXT,
	height SMALLINT NOT NULL DEFAULT '0',
	def SMALLINT NOT NULL DEFAULT '0',
	optional SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	searchable SMALLINT NOT NULL DEFAULT '0',
	memberlist SMALLINT NOT NULL DEFAULT '0',
	regex VARCHAR(255) NOT NULL DEFAULT '',
	form SMALLINT NOT NULL DEFAULT '0',
	html SMALLINT NOT NULL DEFAULT '0',
	perline SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY (profilefieldid),
	KEY editable (editable),
	KEY profilefieldcategoryid (profilefieldcategoryid)
)
";
$schema['CREATE']['explain']['profilefield'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "profilefield");



$schema['CREATE']['query']['profilefieldcategory'] = "
CREATE TABLE " . TABLE_PREFIX . "profilefieldcategory (
	profilefieldcategoryid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	displayorder SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY (profilefieldcategoryid)
)
";
$schema['CREATE']['explain']['profilefieldcategory'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "profilefieldcategory");



$schema['CREATE']['query']['ranks'] = "
CREATE TABLE " . TABLE_PREFIX . "ranks (
	rankid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	minposts INT UNSIGNED NOT NULL DEFAULT '0',
	ranklevel SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	rankimg MEDIUMTEXT,
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	type SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	stack SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	display SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (rankid),
	KEY grouprank (usergroupid, minposts)
)
";
$schema['CREATE']['explain']['ranks'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "ranks");



$schema['CREATE']['query']['regimage'] = "
CREATE TABLE " . TABLE_PREFIX . "regimage (
	regimagehash CHAR(32) NOT NULL DEFAULT '',
	imagestamp CHAR(6) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	viewed SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	KEY regimagehash (regimagehash, dateline)
)
";
$schema['CREATE']['explain']['regimage'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "regimage");



$schema['CREATE']['query']['reminder'] = "
CREATE TABLE " . TABLE_PREFIX . "reminder (
	reminderid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	title VARCHAR(50) NOT NULL DEFAULT '',
	text MEDIUMTEXT,
	duedate INT UNSIGNED NOT NULL DEFAULT '0',
	adminonly SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	completedby INT UNSIGNED NOT NULL DEFAULT '0',
	completedtime INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (reminderid)
)
";
$schema['CREATE']['explain']['reminder'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "reminder");



$schema['CREATE']['query']['reputation'] = "
CREATE TABLE " . TABLE_PREFIX . "reputation (
	reputationid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	postid INT NOT NULL DEFAULT '1',
	userid INT NOT NULL DEFAULT '1',
	reputation INT NOT NULL DEFAULT '0',
	whoadded INT NOT NULL DEFAULT '0',
	reason VARCHAR(250) DEFAULT NULL DEFAULT '',
	dateline INT NOT NULL DEFAULT '0',
	PRIMARY KEY (reputationid),
	KEY userid (userid),
	KEY whoadded_postid (whoadded, postid),
	KEY multi (postid, userid),
	KEY dateline (dateline)
)
";
$schema['CREATE']['explain']['reputation'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "reputation");



$schema['CREATE']['query']['reputationlevel'] = "
CREATE TABLE " . TABLE_PREFIX . "reputationlevel (
	reputationlevelid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	minimumreputation INT NOT NULL DEFAULT '0',
	PRIMARY KEY (reputationlevelid),
	KEY reputationlevel (minimumreputation)
)
";
$schema['CREATE']['explain']['reputationlevel'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "reputationlevel");



$schema['CREATE']['query']['rssfeed'] = "
CREATE TABLE " . TABLE_PREFIX . "rssfeed (
	rssfeedid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(250) NOT NULL,
	url VARCHAR(250) NOT NULL,
	port SMALLINT UNSIGNED NOT NULL DEFAULT '80',
	ttl SMALLINT UNSIGNED NOT NULL DEFAULT '1500',
	maxresults SMALLINT NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL,
	forumid SMALLINT UNSIGNED NOT NULL,
	iconid SMALLINT UNSIGNED NOT NULL,
	titletemplate MEDIUMTEXT NOT NULL,
	bodytemplate MEDIUMTEXT NOT NULL,
	searchwords MEDIUMTEXT NOT NULL,
	itemtype ENUM('thread','announcement') NOT NULL DEFAULT 'thread',
	threadactiondelay SMALLINT UNSIGNED NOT NULL,
	endannouncement INT UNSIGNED NOT NULL,
	options INT UNSIGNED NOT NULL,
	lastrun INT UNSIGNED NOT NULL,
	PRIMARY KEY  (rssfeedid),
	KEY lastrun (lastrun)
)
";
$schema['CREATE']['explain']['rssfeed'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "rssfeed");



$schema['CREATE']['query']['rsslog'] = "
CREATE TABLE " . TABLE_PREFIX . "rsslog (
	rssfeedid INT UNSIGNED NOT NULL,
	itemid INT UNSIGNED NOT NULL,
	itemtype ENUM('thread','announcement') NOT NULL DEFAULT 'thread',
	uniquehash CHAR(32) NOT NULL,
	contenthash CHAR(32) NOT NULL,
	dateline INT UNSIGNED NOT NULL,
	threadactiontime INT UNSIGNED NOT NULL,
	threadactioncomplete TINYINT UNSIGNED NOT NULL,
	PRIMARY KEY (rssfeedid,itemid,itemtype),
	UNIQUE KEY uniquehash (uniquehash)
)
";
$schema['CREATE']['explain']['rsslog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "rsslog");



$schema['CREATE']['query']['search'] = "
CREATE TABLE " . TABLE_PREFIX . "search (
	searchid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
	personal SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	query VARCHAR(200) NOT NULL DEFAULT '',
	searchuser VARCHAR(200) NOT NULL DEFAULT '',
	forumchoice MEDIUMTEXT,
	sortby VARCHAR(200) NOT NULL DEFAULT '',
	sortorder VARCHAR(4) NOT NULL DEFAULT '',
	searchtime float NOT NULL DEFAULT '0',
	showposts SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	orderedids MEDIUMTEXT,
	announceids MEDIUMTEXT,
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	searchterms MEDIUMTEXT,
	displayterms MEDIUMTEXT,
	searchhash VARCHAR(32) NOT NULL DEFAULT '',
	titleonly SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	completed SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (searchid),
	UNIQUE KEY searchunique (searchhash, sortby, sortorder)
)
";
$schema['CREATE']['explain']['search'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "search");



$schema['CREATE']['query']['session'] = "
CREATE TABLE " . TABLE_PREFIX . "session (
	sessionhash CHAR(32) NOT NULL DEFAULT '',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	host CHAR(15) NOT NULL DEFAULT '',
	idhash CHAR(32) NOT NULL DEFAULT '',
	lastactivity INT UNSIGNED NOT NULL DEFAULT '0',
	location CHAR(255) NOT NULL DEFAULT '',
	useragent CHAR(100) NOT NULL DEFAULT '',
	styleid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	loggedin SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	inforum SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	inthread INT UNSIGNED NOT NULL DEFAULT '0',
	incalendar SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	badlocation SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	bypass TINYINT NOT NULL DEFAULT '0',
	profileupdate SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (sessionhash)
)
";
$schema['CREATE']['explain']['session'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "session");



$schema['CREATE']['query']['setting'] = "
CREATE TABLE " . TABLE_PREFIX . "setting (
	varname VARCHAR(100) NOT NULL DEFAULT '',
	grouptitle VARCHAR(50) NOT NULL DEFAULT '',
	value MEDIUMTEXT,
	defaultvalue MEDIUMTEXT,
	optioncode MEDIUMTEXT,
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	advanced SMALLINT NOT NULL DEFAULT '0',
	volatile SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	datatype ENUM('free', 'number', 'boolean', 'bitfield', 'username') NOT NULL DEFAULT 'free',
	product VARCHAR(25) NOT NULL DEFAULT '',
	validationcode TEXT,
	blacklist SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY (varname)
)
";
$schema['CREATE']['explain']['setting'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "setting");



$schema['CREATE']['query']['settinggroup'] = "
CREATE TABLE " . TABLE_PREFIX . "settinggroup (
	grouptitle CHAR(50) NOT NULL DEFAULT '',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	volatile SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	product VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (grouptitle)
)
";
$schema['CREATE']['explain']['settinggroup'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "settinggroup");



$schema['CREATE']['query']['sigparsed'] = "
CREATE TABLE " . TABLE_PREFIX . "sigparsed (
  userid INT UNSIGNED NOT NULL DEFAULT '0',
  styleid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
  languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
  signatureparsed MEDIUMTEXT,
  hasimages SMALLINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (userid, styleid, languageid)
)
";
$schema['CREATE']['explain']['sigparsed'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "sigparsed");



$schema['CREATE']['query']['smilie'] = "
CREATE TABLE " . TABLE_PREFIX . "smilie (
	smilieid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title CHAR(100) NOT NULL DEFAULT '',
	smilietext CHAR(20) NOT NULL DEFAULT '',
	smiliepath CHAR(100) NOT NULL DEFAULT '',
	imagecategoryid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (smilieid)
)
";
$schema['CREATE']['explain']['smilie'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "smilie");



$schema['CREATE']['query']['stats'] = "
CREATE TABLE " . TABLE_PREFIX . "stats (
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	nuser mediumint UNSIGNED NOT NULL DEFAULT '0',
	nthread mediumint UNSIGNED NOT NULL DEFAULT '0',
	npost mediumint UNSIGNED NOT NULL DEFAULT '0',
	ausers mediumint UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (dateline)
)
";
$schema['CREATE']['explain']['stats'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "stats");



$schema['CREATE']['query']['strikes'] = "
CREATE TABLE " . TABLE_PREFIX . "strikes (
	striketime INT UNSIGNED NOT NULL DEFAULT '0',
	strikeip CHAR(15) NOT NULL DEFAULT '',
	username VARCHAR(100) NOT NULL DEFAULT '',
	KEY striketime (striketime),
	KEY strikeip (strikeip)
)
";
$schema['CREATE']['explain']['strikes'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "strikes");



$schema['CREATE']['query']['style'] = "
CREATE TABLE " . TABLE_PREFIX . "style (
	styleid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(250) NOT NULL DEFAULT '',
	parentid SMALLINT NOT NULL DEFAULT '0',
	parentlist VARCHAR(250) NOT NULL DEFAULT '',
	templatelist MEDIUMTEXT,
	csscolors MEDIUMTEXT,
	css MEDIUMTEXT,
	stylevars MEDIUMTEXT,
	replacements MEDIUMTEXT,
	editorstyles MEDIUMTEXT,
	userselect SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (styleid)
)
";
$schema['CREATE']['explain']['style'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "style");



$schema['CREATE']['query']['subscribeevent'] = "
CREATE TABLE " . TABLE_PREFIX . "subscribeevent (
	subscribeeventid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	eventid INT UNSIGNED NOT NULL DEFAULT '0',
	lastreminder INT UNSIGNED NOT NULL DEFAULT '0',
	reminder INT UNSIGNED NOT NULL DEFAULT '3600',
	PRIMARY KEY (subscribeeventid),
	UNIQUE KEY subindex (userid, eventid),
	KEY eventid (eventid)
)
";
$schema['CREATE']['explain']['subscribeevent'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "subscribeevent");



$schema['CREATE']['query']['subscribeforum'] = "
CREATE TABLE " . TABLE_PREFIX . "subscribeforum (
	subscribeforumid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	forumid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	emailupdate SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (subscribeforumid),
	UNIQUE KEY subindex (userid, forumid),
	KEY forumid (forumid)
)
";
$schema['CREATE']['explain']['subscribeforum'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "subscribeforum");



$schema['CREATE']['query']['subscribethread'] = "
CREATE TABLE " . TABLE_PREFIX . "subscribethread (
	subscribethreadid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	emailupdate SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	folderid INT UNSIGNED NOT NULL DEFAULT '0',
	canview SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (subscribethreadid),
	UNIQUE KEY threadid (threadid, userid),
	KEY userid (userid, folderid)
)
";
$schema['CREATE']['explain']['subscribethread'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "subscribethread");



$schema['CREATE']['query']['subscription'] = "
CREATE TABLE " . TABLE_PREFIX . "subscription (
	subscriptionid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	varname VARCHAR(100) NOT NULL DEFAULT '',
	cost MEDIUMTEXT,
	forums MEDIUMTEXT,
	nusergroupid SMALLINT NOT NULL DEFAULT '0',
	membergroupids VARCHAR(255) NOT NULL DEFAULT '',
	active SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	options INT UNSIGNED NOT NULL DEFAULT '0',
	displayorder SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	adminoptions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (subscriptionid)
)
";
$schema['CREATE']['explain']['subscription'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "subscription");



$schema['CREATE']['query']['subscriptionlog'] = "
CREATE TABLE " . TABLE_PREFIX . "subscriptionlog (
	subscriptionlogid MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	subscriptionid SMALLINT NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	pusergroupid SMALLINT NOT NULL DEFAULT '0',
	status SMALLINT NOT NULL DEFAULT '0',
	regdate INT UNSIGNED NOT NULL DEFAULT '0',
	expirydate INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (subscriptionlogid),
	KEY userid (userid, subscriptionid),
	KEY subscriptionid (subscriptionid)
)
";
$schema['CREATE']['explain']['subscriptionlog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "subscriptionlog");


$schema['CREATE']['query']['subscriptionpermission'] = "
CREATE TABLE " . TABLE_PREFIX . "subscriptionpermission (
  subscriptionpermissionid INT UNSIGNED NOT NULL auto_increment,
  subscriptionid INT UNSIGNED NOT NULL default '0',
  usergroupid INT UNSIGNED NOT NULL default '0',
  PRIMARY KEY  (subscriptionpermissionid),
  UNIQUE KEY subscriptionid (subscriptionid,usergroupid),
  KEY usergroupid (usergroupid)
)
";
$schema['CREATE']['explain']['subscriptionpermission'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "subscriptionpermission");


$schema['CREATE']['query']['tachyforumpost'] = "
CREATE TABLE " . TABLE_PREFIX . "tachyforumpost (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	forumid INT UNSIGNED NOT NULL DEFAULT '0',
	lastpost INT UNSIGNED NOT NULL DEFAULT '0',
	lastposter VARCHAR(100) NOT NULL DEFAULT '',
	lastpostid INT UNSIGNED NOT NULL DEFAULT '0',
	lastthread VARCHAR(250) NOT NULL DEFAULT '',
	lastthreadid INT UNSIGNED NOT NULL DEFAULT '0',
	lasticonid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (userid, forumid),
	KEY (forumid)
)
";
$schema['CREATE']['explain']['tachyforumpost'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "tachyforumpost");



$schema['CREATE']['query']['tachythreadpost'] = "
CREATE TABLE " . TABLE_PREFIX . "tachythreadpost (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	lastpost INT UNSIGNED NOT NULL DEFAULT '0',
	lastposter VARCHAR(100) NOT NULL DEFAULT '',
	lastpostid INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (userid, threadid),
	KEY (threadid)
)
";
$schema['CREATE']['explain']['tachythreadpost'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "tachythreadpost");


// Update the template_temp table in adminfunctions_template.php whenever this table is altered
$schema['CREATE']['query']['template'] = "
CREATE TABLE " . TABLE_PREFIX . "template (
	templateid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	styleid SMALLINT NOT NULL DEFAULT '0',
	title VARCHAR(100) NOT NULL DEFAULT '',
	template MEDIUMTEXT,
	template_un MEDIUMTEXT,
	templatetype ENUM('template','stylevar','css','replacement') NOT NULL DEFAULT 'template',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	username VARCHAR(100) NOT NULL DEFAULT '',
	version VARCHAR(30) NOT NULL DEFAULT '',
	product VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (templateid),
	UNIQUE KEY title (title, styleid, templatetype)
)
";
$schema['CREATE']['explain']['template'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "template");

$schema['CREATE']['query']['templatehistory'] = "
CREATE TABLE " . TABLE_PREFIX . "templatehistory (
	templatehistoryid int(10) unsigned NOT NULL auto_increment,
	styleid smallint(5) unsigned NOT NULL default '0',
	title varchar(100) NOT NULL default '',
	template MEDIUMTEXT,
	dateline int(10) unsigned NOT NULL default '0',
	username varchar(100) NOT NULL default '',
	version varchar(30) NOT NULL default '',
	comment varchar(255) NOT NULL default '',
	PRIMARY KEY (templatehistoryid),
	KEY title (title, styleid)
)
";
$schema['CREATE']['explain']['templatehistory'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "templatehistory");



$schema['CREATE']['query']['thread'] = "
CREATE TABLE " . TABLE_PREFIX . "thread (
	threadid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(250) NOT NULL DEFAULT '',
	firstpostid INT UNSIGNED NOT NULL DEFAULT '0',
	lastpostid INT UNSIGNED NOT NULL DEFAULT '0',
	lastpost INT UNSIGNED NOT NULL DEFAULT '0',
	forumid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	pollid INT UNSIGNED NOT NULL DEFAULT '0',
	open SMALLINT NOT NULL DEFAULT '0',
	replycount INT UNSIGNED NOT NULL DEFAULT '0',
	hiddencount INT UNSIGNED NOT NULL DEFAULT '0',
	deletedcount INT UNSIGNED NOT NULL DEFAULT '0',
	postusername VARCHAR(100) NOT NULL DEFAULT '',
	postuserid INT UNSIGNED NOT NULL DEFAULT '0',
	lastposter CHAR(50) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	views INT UNSIGNED NOT NULL DEFAULT '0',
	iconid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	notes VARCHAR(250) NOT NULL DEFAULT '',
	visible SMALLINT NOT NULL DEFAULT '0',
	sticky SMALLINT NOT NULL DEFAULT '0',
	votenum SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	votetotal SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	attach SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	similar VARCHAR(55) NOT NULL DEFAULT '',
	PRIMARY KEY (threadid),
	KEY postuserid (postuserid),
	KEY pollid (pollid),
	KEY forumid (forumid, visible, sticky, lastpost),
	KEY lastpost (lastpost, forumid),
	KEY dateline (dateline),
	FULLTEXT KEY title (title)
) $enginetype=MyISAM
";
$schema['CREATE']['explain']['thread'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "thread");



$schema['CREATE']['query']['threadrate'] = "
CREATE TABLE " . TABLE_PREFIX . "threadrate (
	threadrateid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	vote SMALLINT NOT NULL DEFAULT '0',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
	PRIMARY KEY (threadrateid),
	KEY threadid (threadid, userid)
)
";
$schema['CREATE']['explain']['threadrate'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "threadrate");



$schema['CREATE']['query']['threadread'] = "
CREATE TABLE " . TABLE_PREFIX . "threadread (
	userid int(10) unsigned NOT NULL default '0',
	threadid int(10) unsigned NOT NULL default '0',
	readtime int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (userid, threadid),
	KEY readtime (readtime)
)
";
$schema['CREATE']['explain']['threadread'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "threadread");


$schema['CREATE']['query']['threadredirect'] = "
CREATE TABLE " . TABLE_PREFIX . "threadredirect (
	threadid INT UNSIGNED NOT NULL default '0',
	expires INT UNSIGNED NOT NULL default '0',
	PRIMARY KEY (threadid),
	KEY expires (expires)
)
";
$schema['CREATE']['explain']['threadredirect'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "threadredirect");


$schema['CREATE']['query']['threadviews'] = "
CREATE TABLE " . TABLE_PREFIX . "threadviews (
	threadid INT UNSIGNED NOT NULL DEFAULT '0',
	KEY threadid (threadid)
)
";
$schema['CREATE']['explain']['threadviews'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "threadviews");



$schema['CREATE']['query']['upgradelog'] = "
CREATE TABLE " . TABLE_PREFIX . "upgradelog (
	upgradelogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	script VARCHAR(50) NOT NULL DEFAULT '',
	steptitle VARCHAR(250) NOT NULL DEFAULT '',
	step smallint(5) UNSIGNED NOT NULL DEFAULT '0',
	startat INT UNSIGNED NOT NULL DEFAULT '0',
	perpage SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (upgradelogid)
)
";
$schema['CREATE']['explain']['upgradelog'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "upgradelog");



$schema['CREATE']['query']['user'] = "
CREATE TABLE " . TABLE_PREFIX . "user (
	userid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	membergroupids CHAR(250) NOT NULL DEFAULT '',
	displaygroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	username VARCHAR(100) NOT NULL DEFAULT '',
	password CHAR(32) NOT NULL DEFAULT '',
	passworddate date NOT NULL DEFAULT '0000-00-00',
	email CHAR(100) NOT NULL DEFAULT '',
	styleid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	parentemail CHAR(50) NOT NULL DEFAULT '',
	homepage CHAR(100) NOT NULL DEFAULT '',
	icq CHAR(20) NOT NULL DEFAULT '',
	aim CHAR(20) NOT NULL DEFAULT '',
	yahoo CHAR(32) NOT NULL DEFAULT '',
	msn CHAR(100) NOT NULL DEFAULT '',
	skype CHAR(32) NOT NULL DEFAULT '',
	showvbcode SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	showbirthday SMALLINT UNSIGNED NOT NULL DEFAULT '2',
	usertitle CHAR(250) NOT NULL DEFAULT '',
	customtitle SMALLINT NOT NULL DEFAULT '0',
	joindate INT UNSIGNED NOT NULL DEFAULT '0',
	daysprune SMALLINT NOT NULL DEFAULT '0',
	lastvisit INT UNSIGNED NOT NULL DEFAULT '0',
	lastactivity INT UNSIGNED NOT NULL DEFAULT '0',
	lastpost INT UNSIGNED NOT NULL DEFAULT '0',
	lastpostid INT UNSIGNED NOT NULL DEFAULT '0',
	posts INT UNSIGNED NOT NULL DEFAULT '0',
	reputation INT NOT NULL DEFAULT '10',
	reputationlevelid INT UNSIGNED NOT NULL DEFAULT '1',
	timezoneoffset CHAR(4) NOT NULL DEFAULT '',
	pmpopup SMALLINT NOT NULL DEFAULT '0',
	avatarid SMALLINT NOT NULL DEFAULT '0',
	avatarrevision INT UNSIGNED NOT NULL DEFAULT '0',
	profilepicrevision INT UNSIGNED NOT NULL DEFAULT '0',
	sigpicrevision INT UNSIGNED NOT NULL DEFAULT '0',
	options INT UNSIGNED NOT NULL DEFAULT '15',
	birthday CHAR(10) NOT NULL DEFAULT '',
	birthday_search DATE NOT NULL DEFAULT '0000-00-00',
	maxposts SMALLINT NOT NULL DEFAULT '-1',
	startofweek SMALLINT NOT NULL DEFAULT '1',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
	referrerid INT UNSIGNED NOT NULL DEFAULT '0',
	languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	emailstamp INT UNSIGNED NOT NULL DEFAULT '0',
	threadedmode SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	autosubscribe SMALLINT NOT NULL DEFAULT '-1',
	pmtotal SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	pmunread SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	salt CHAR(3) NOT NULL DEFAULT '',
	ipoints INT UNSIGNED NOT NULL DEFAULT '0',
	infractions INT UNSIGNED NOT NULL DEFAULT '0',
	warnings INT UNSIGNED NOT NULL DEFAULT '0',
	infractiongroupids VARCHAR (255) NOT NULL DEFAULT '',
	infractiongroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	adminoptions INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (userid),
	KEY usergroupid (usergroupid),
	KEY username (username),
	KEY birthday (birthday, showbirthday),
	KEY birthday_search (birthday_search)
)
";
$schema['CREATE']['explain']['user'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "user");



$schema['CREATE']['query']['useractivation'] = "
CREATE TABLE " . TABLE_PREFIX . "useractivation (
	useractivationid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	activationid bigint UNSIGNED NOT NULL DEFAULT '0',
	type SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	emailchange SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (useractivationid),
	UNIQUE KEY userid (userid, type)
)
";
$schema['CREATE']['explain']['useractivation'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "useractivation");



$schema['CREATE']['query']['userban'] = "
CREATE TABLE " . TABLE_PREFIX . "userban (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	displaygroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	usertitle VARCHAR(250) NOT NULL DEFAULT '',
	customtitle SMALLINT NOT NULL DEFAULT '0',
	adminid INT UNSIGNED NOT NULL DEFAULT '0',
	bandate INT UNSIGNED NOT NULL DEFAULT '0',
	liftdate INT UNSIGNED NOT NULL DEFAULT '0',
	reason VARCHAR(250) NOT NULL DEFAULT '',
	PRIMARY KEY (userid),
	KEY liftdate (liftdate)
)
";
$schema['CREATE']['explain']['userban'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "userban");



$schema['CREATE']['query']['userfield'] = "
CREATE TABLE " . TABLE_PREFIX . "userfield (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	temp MEDIUMTEXT,
	field1 MEDIUMTEXT,
	field2 MEDIUMTEXT,
	field3 MEDIUMTEXT,
	field4 MEDIUMTEXT,
	PRIMARY KEY (userid)
)
";
$schema['CREATE']['explain']['userfield'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "userfield");



$schema['CREATE']['query']['usergroup'] = "
CREATE TABLE " . TABLE_PREFIX . "usergroup (
	usergroupid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title CHAR(100) NOT NULL DEFAULT '',
	description VARCHAR(250) NOT NULL DEFAULT '',
	usertitle CHAR(100) NOT NULL DEFAULT '',
	passwordexpires SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	passwordhistory SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	pmquota SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	pmsendmax SMALLINT UNSIGNED NOT NULL DEFAULT '5',
	opentag CHAR(100) NOT NULL DEFAULT '',
	closetag CHAR(100) NOT NULL DEFAULT '',
	canoverride SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	ispublicgroup SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	forumpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	pmpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	calendarpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	wolpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	adminpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	genericpermissions INT UNSIGNED NOT NULL DEFAULT '0',
	genericoptions INT UNSIGNED NOT NULL DEFAULT '0',
	signaturepermissions INT UNSIGNED NOT NULL DEFAULT '0',
	attachlimit INT UNSIGNED NOT NULL DEFAULT '0',
	avatarmaxwidth SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	avatarmaxheight SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	avatarmaxsize INT UNSIGNED NOT NULL DEFAULT '0',
	profilepicmaxwidth SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	profilepicmaxheight SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	profilepicmaxsize INT UNSIGNED NOT NULL DEFAULT '0',
	sigpicmaxwidth SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	sigpicmaxheight SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	sigpicmaxsize INT UNSIGNED NOT NULL DEFAULT '0',
	sigmaximages SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	sigmaxsizebbcode SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	sigmaxchars SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	sigmaxrawchars SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	sigmaxlines SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (usergroupid)
)
";
$schema['CREATE']['explain']['usergroup'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "usergroup");



$schema['CREATE']['query']['usergroupleader'] = "
CREATE TABLE " . TABLE_PREFIX . "usergroupleader (
	usergroupleaderid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (usergroupleaderid),
	KEY ugl (userid, usergroupid)
)
";
$schema['CREATE']['explain']['usergroupleader'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "usergroupleader");



$schema['CREATE']['query']['usergrouprequest'] = "
CREATE TABLE " . TABLE_PREFIX . "usergrouprequest (
	usergrouprequestid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	usergroupid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	reason VARCHAR(250) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (usergrouprequestid),
	KEY usergroupid (usergroupid),
	UNIQUE KEY (userid, usergroupid)
)
";
$schema['CREATE']['explain']['usergrouprequest'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "usergrouprequest");



$schema['CREATE']['query']['userlist'] = "
CREATE TABLE " . TABLE_PREFIX . "userlist (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	relationid INT UNSIGNED NOT NULL DEFAULT '0',
	type ENUM('buddy', 'ignore') NOT NULL DEFAULT 'buddy',
	PRIMARY KEY (userid, relationid, type),
	KEY relationid (relationid)
)
";
$schema['CREATE']['explain']['userlist'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "userlist");



$schema['CREATE']['query']['usernote'] = "
CREATE TABLE " . TABLE_PREFIX . "usernote (
	usernoteid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	posterid INT UNSIGNED NOT NULL DEFAULT '0',
	username VARCHAR(100) NOT NULL DEFAULT '',
	dateline INT UNSIGNED NOT NULL DEFAULT '0',
	message MEDIUMTEXT,
	title VARCHAR(255) NOT NULL DEFAULT '',
	allowsmilies SMALLINT NOT NULL DEFAULT '0',
	PRIMARY KEY (usernoteid),
	KEY userid (userid)
)
";
$schema['CREATE']['explain']['usernote'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "usernote");



$schema['CREATE']['query']['userpromotion'] = "
CREATE TABLE " . TABLE_PREFIX . "userpromotion (
	userpromotionid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	usergroupid INT UNSIGNED NOT NULL DEFAULT '0',
	joinusergroupid INT UNSIGNED NOT NULL DEFAULT '0',
	reputation INT NOT NULL DEFAULT '0',
	date INT UNSIGNED NOT NULL DEFAULT '0',
	posts INT UNSIGNED NOT NULL DEFAULT '0',
	strategy SMALLINT NOT NULL DEFAULT '0',
	type SMALLINT NOT NULL DEFAULT '2',
	PRIMARY KEY (userpromotionid),
	KEY usergroupid (usergroupid)
)
";
$schema['CREATE']['explain']['userpromotion'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "userpromotion");



$schema['CREATE']['query']['usertextfield'] = "
CREATE TABLE " . TABLE_PREFIX . "usertextfield (
	userid INT UNSIGNED NOT NULL DEFAULT '0',
	subfolders MEDIUMTEXT,
	pmfolders MEDIUMTEXT,
	buddylist MEDIUMTEXT,
	ignorelist MEDIUMTEXT,
	signature MEDIUMTEXT,
	searchprefs MEDIUMTEXT,
	rank MEDIUMTEXT,
	PRIMARY KEY (userid)
)
";
$schema['CREATE']['explain']['usertextfield'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "usertextfield");



$schema['CREATE']['query']['usertitle'] = "
CREATE TABLE " . TABLE_PREFIX . "usertitle (
	usertitleid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	minposts INT UNSIGNED NOT NULL DEFAULT '0',
	title CHAR(250) NOT NULL DEFAULT '',
	PRIMARY KEY (usertitleid)
)
";
$schema['CREATE']['explain']['usertitle'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "usertitle");



$schema['CREATE']['query']['word'] = "
CREATE TABLE " . TABLE_PREFIX . "word (
	wordid INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title CHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (wordid),
	UNIQUE KEY title (title)
)
";
$schema['CREATE']['explain']['word'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "word");


$schema['CREATE']['query']['sigpic'] = "
CREATE TABLE " . TABLE_PREFIX . "sigpic (
	userid int(10) unsigned NOT NULL default '0',
	filedata mediumblob,
	dateline int(10) unsigned NOT NULL default '0',
	filename varchar(100) NOT NULL default '',
	visible smallint(6) NOT NULL default '1',
	filesize int(10) unsigned NOT NULL default '0',
	width smallint(5) unsigned NOT NULL default '0',
	height smallint(5) unsigned NOT NULL default '0',
	PRIMARY KEY  (userid)
)
";
$schema['CREATE']['explain']['sigpic'] = sprintf($vbphrase['create_table'], TABLE_PREFIX . "sigpic");

// ***************************************************************************************************************************

$altertabletype = array(
	'session'   => $tabletype,
	'cpsession' => $tabletype,

	// innodb tables are limited to about 10 varchar/blob/text fields http://bugs.mysql.com/bug.php?id=10035
	'language'  => 'MYISAM',
	'userfield' => 'MYISAM',
);

foreach ($altertabletype AS $table => $type)
{
	$schema['ALTER']['query']["$table"] = "ALTER TABLE " . TABLE_PREFIX . "$table $enginetype = $type";
	$schema['ALTER']['explain']["$table"] = sprintf($install_phrases['alter_table_type_x'], $table, $type);
}

// ***************************************************************************************************************************

$schema['INSERT']['query']['adminutil'] = "INSERT INTO " . TABLE_PREFIX . "adminutil (title, text) VALUES ('datastorelock', '0')";

$schema['INSERT']['explain']['adminutil'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "adminutil");



// Do not change this query, without modifying the datastore query below.
$schema['INSERT']['query']['attachmenttype'] = "
INSERT INTO " . TABLE_PREFIX . "attachmenttype (extension, mimetype, size, width, height, enabled, display, thumbnail) VALUES
('gif', '" . $db->escape_string(serialize(array('Content-type: image/gif'))) . "', '20000', '620', '280', '1', '0', '1'),
('jpeg', '" . $db->escape_string(serialize(array('Content-type: image/jpeg'))) . "', '20000', '620', '280', '1', '0', '1'),
('jpg', '" . $db->escape_string(serialize(array('Content-type: image/jpeg'))) . "', '100000', '0', '0', '1', '0', '1'),
('jpe', '" . $db->escape_string(serialize(array('Content-type: image/jpeg'))) . "', '20000', '620', '280', '1', '0', '1'),
('txt', '" . $db->escape_string(serialize(array('Content-type: plain/text'))) . "', '20000', '0', '0', '1', '2', '0'),
('png', '" . $db->escape_string(serialize(array('Content-type: image/png'))) . "', '20000', '620', '280', '1', '0', '1'),
('doc', '" . $db->escape_string(serialize(array('Content-type: application/msword'))) . "', '20000', '0', '0', '1', '0', '0'),
('pdf', '" . $db->escape_string(serialize(array('Content-type: application/pdf'))) . "', '20000', '0', '0', '1', '0', '1'),
('bmp', '" . $db->escape_string(serialize(array('Content-type: image/bitmap'))) . "', '20000', '620', '280', '1', '0', '0'),
('psd', '" . $db->escape_string(serialize(array('Content-type: unknown/unknown'))) . "', '20000', '0', '0', '1', '0', '1'),
('zip', '" . $db->escape_string(serialize(array('Content-type: application/zip'))) . "', '100000', '0', '0', '1', '0', '0')
";

$schema['INSERT']['explain']['attachmenttype'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "attachmenttype");



$schema['INSERT']['query']['attachmentcache'] = "INSERT INTO " . TABLE_PREFIX . "datastore (title, data, unserialize) VALUES ('attachmentcache', '" . $db->escape_string(serialize(array())) . "', 1)";
$schema['INSERT']['explain']['attachmentcache'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "datastore");



$schema['INSERT']['query']['calendar'] = "
INSERT INTO " . TABLE_PREFIX . "calendar (title, description, displayorder, neweventemail, moderatenew, startofweek, options, cutoff, eventcount, birthdaycount, startyear, endyear) VALUES
('" . $db->escape_string($install_phrases['default_calendar']) . "', '', 1, '" . serialize(array()) . "', 0, 1, 631, 40, 4, 4, " . (date('Y') - 3) . ", " . (date('Y') + 3) . ")
";

$schema['INSERT']['explain']['calendar'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "calendar");



$schema['INSERT']['query']['cron'] = "
INSERT INTO " . TABLE_PREFIX . "cron
	(nextrun, weekday, day, hour, minute, filename, loglevel, varname, volatile, product)
VALUES
(1053271660, -1, -1,  0, 'a:1:{i:0;i:1;}',           './includes/cron/birthday.php',        1, 'birthday',        1, 'vbulletin'),
(1053532560, -1, -1, -1, 'a:1:{i:0;i:56;}',          './includes/cron/threadviews.php',     0, 'threadviews',     1, 'vbulletin'),
(1053531900, -1, -1, -1, 'a:1:{i:0;i:25;}',          './includes/cron/promotion.php',       1, 'promotion',       1, 'vbulletin'),
(1053271720, -1, -1,  0, 'a:1:{i:0;i:2;}',           './includes/cron/digestdaily.php',     1, 'digestdaily',     1, 'vbulletin'),
(1053991800,  1, -1,  0, 'a:1:{i:0;i:30;}',          './includes/cron/digestweekly.php',    1, 'digestweekly',    1, 'vbulletin'),
(1053271820, -1, -1,  0, 'a:1:{i:0;i:2;}',           './includes/cron/subscriptions.php',   1, 'subscriptions',   1, 'vbulletin'),
(1053533100, -1, -1, -1, 'a:1:{i:0;i:5;}',           './includes/cron/cleanup.php',         0, 'cleanup',         1, 'vbulletin'),
(1053533200, -1, -1, -1, 'a:1:{i:0;i:10;}',          './includes/cron/attachmentviews.php', 0, 'attachmentviews', 1, 'vbulletin'),
(1053990180, -1, -1,  0, 'a:1:{i:0;i:3;}',           './includes/cron/activate.php',        1, 'activate',        1, 'vbulletin'),
(1053271600, -1, -1, -1, 'a:1:{i:0;i:15;}',          './includes/cron/removebans.php',      1, 'removebans',      1, 'vbulletin'),
(1053531600, -1, -1, -1, 'a:1:{i:0;i:20;}',          './includes/cron/cleanup2.php',        0, 'cleanup2',        1, 'vbulletin'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',           './includes/cron/stats.php',           0, 'stats',           1, 'vbulletin'),
(1053271600, -1, -1, -1, 'a:2:{i:0;i:25;i:1;i:55;}', './includes/cron/reminder.php',        0, 'reminder',        1, 'vbulletin'),
(1053533100, -1, -1,  0, 'a:1:{i:0;i:10;}',          './includes/cron/dailycleanup.php',    0, 'dailycleanup',    1, 'vbulletin'),
(1053271600, -1, -1, -1, 'a:2:{i:0;i:20;i:1;i:50;}', './includes/cron/infractions.php',     1, 'infractions',     1, 'vbulletin'),
(1053271600, -1, -1, -1, 'a:1:{i:0;i:10;}',          './includes/cron/ccbill.php',          1, 'ccbill',          1, 'vbulletin'),
(1053271600, -1, -1, -1, 'a:6:{i:0;i:0;i:1;i:10;i:2;i:20;i:3;i:30;i:4;i:40;i:5;i:50;}', './includes/cron/rssposter.php', 1, 'rssposter',1, 'vbulletin')
";

$schema['INSERT']['explain']['cron'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "cron");



$schema['INSERT']['query']['datastore'] = "INSERT INTO " . TABLE_PREFIX . "datastore (title, data, unserialize) VALUES ('products', '" . $db->escape_string(serialize(array('vbulletin' => '1'))) . "', 1)";

$schema['INSERT']['explain']['datastore'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "datastore");



$schema['INSERT']['query']['faq'] = "
INSERT INTO " . TABLE_PREFIX . "faq (faqname, faqparent, displayorder, volatile)
VALUES
('vb_faq', 'faqroot', 100, 1),
('vb_user_maintain', 'vb_faq', 10, 1),
('vb_why_register', 'vb_user_maintain', 1, 1),
('vb_use_cookies', 'vb_user_maintain', 2, 1),
('vb_clear_cookies', 'vb_user_maintain', 3, 1),
('vb_update_profile', 'vb_user_maintain', 4, 1),
('vb_sig_explain', 'vb_user_maintain', 5, 1),
('vb_lost_password', 'vb_user_maintain', 6, 1),
('vb_custom_status', 'vb_user_maintain', 7, 1),
('vb_avatar_how', 'vb_user_maintain', 8, 1),
('vb_buddy_explain', 'vb_user_maintain', 9, 1),
('vb_board_usage', 'vb_faq', 20, 1),
('vb_board_search', 'vb_board_usage', 1, 1),
('vb_email_member', 'vb_board_usage', 2, 1),
('vb_pm_explain', 'vb_board_usage', 3, 1),
('vb_memberlist_how', 'vb_board_usage', 4, 1),
('vb_calendar_how', 'vb_board_usage', 5, 1),
('vb_announce_explain', 'vb_board_usage', 6, 1),
('vb_thread_rate', 'vb_board_usage', 7, 1),
('vb_referrals_explain', 'vb_board_usage', 8, 1),
('vb_threadedmode', 'vb_board_usage', 9, 1),
('vb_rss_syndication', 'vb_board_usage', 10, 1),
('vb_read_and_post', 'vb_faq', 30, 1),
('vb_special_codes', 'vb_read_and_post', 1, 1),
('vb_smilies_explain', 'vb_read_and_post', 2, 1),
('vb_vbcode_toolbar', 'vb_read_and_post', 3, 1),
('vb_poll_explain', 'vb_read_and_post', 4, 1),
('vb_attachment_explain', 'vb_read_and_post', 5, 1),
('vb_message_icons', 'vb_read_and_post', 6, 1),
('vb_edit_posts', 'vb_read_and_post', 7, 1),
('vb_moderator_explain', 'vb_read_and_post', 8, 1),
('vb_censor_explain', 'vb_read_and_post', 9, 1),
('vb_email_notification', 'vb_read_and_post', 1, 1)
";
$schema['INSERT']['explain']['faq'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "faq");



$schema['INSERT']['query']['forum'] = "
INSERT INTO " . TABLE_PREFIX . "forum
	(forumid, styleid, title, description, options, displayorder, replycount, lastpost, lastposter,
	lastthread, lastthreadid, lasticonid, threadcount, daysprune, newpostemail, newthreademail,
	parentid, parentlist, password, link, childlist, title_clean, description_clean)
VALUES
	(1, 0, '" . $db->escape_string($install_phrases['category_title']) . "', '" . $db->escape_string($install_phrases['category_desc']) . "',
	'86017', '1', '0', '0', '', '', '0', '0', '0', '-1', '', '', '-1', '1,-1', '', '', '1,2,-1',
	'" . $db->escape_string($install_phrases['category_title']) . "', '" . $db->escape_string($install_phrases['category_desc']) . "'),

	(2, 0, '" . $db->escape_string($install_phrases['forum_title']) . "', '" . $db->escape_string($install_phrases['forum_desc']) . "',
	'89799', '1', '0', '0', '', '', '0', '0', '0', '-1', '', '', '1', '2,1,-1', '', '', '2,-1',
	'" . $db->escape_string($install_phrases['forum_title']) . "', '" . $db->escape_string($install_phrases['forum_desc']) . "')
";

$schema['INSERT']['explain']['forum'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "forum");


$schema['INSERT']['query']['icon'] = "
INSERT INTO " . TABLE_PREFIX . "icon (title, iconpath, imagecategoryid, displayorder) VALUES
('{$install_phrases['posticon_1']}', 'images/icons/icon1.gif', '2', '1'),
('{$install_phrases['posticon_2']}', 'images/icons/icon2.gif', '2', '1'),
('{$install_phrases['posticon_3']}', 'images/icons/icon3.gif', '2', '1'),
('{$install_phrases['posticon_4']}', 'images/icons/icon4.gif', '2', '1'),
('{$install_phrases['posticon_5']}', 'images/icons/icon5.gif', '2', '1'),
('{$install_phrases['posticon_6']}', 'images/icons/icon6.gif', '2', '1'),
('{$install_phrases['posticon_7']}', 'images/icons/icon7.gif', '2', '1'),
('{$install_phrases['posticon_8']}', 'images/icons/icon8.gif', '2', '1'),
('{$install_phrases['posticon_9']}', 'images/icons/icon9.gif', '2', '1'),
('{$install_phrases['posticon_10']}', 'images/icons/icon10.gif', '2', '1'),
('{$install_phrases['posticon_11']}', 'images/icons/icon11.gif', '2', '1'),
('{$install_phrases['posticon_12']}', 'images/icons/icon12.gif', '2', '1'),
('{$install_phrases['posticon_13']}', 'images/icons/icon13.gif', '2', '1'),
('{$install_phrases['posticon_14']}', 'images/icons/icon14.gif', '2', '1')
";

$schema['INSERT']['explain']['icon'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "icon");



$schema['INSERT']['query']['imagecategory'] = "
INSERT INTO " . TABLE_PREFIX . "imagecategory (title, imagetype, displayorder) VALUES
('{$install_phrases['generic_smilies']}', 3, 1),
('{$install_phrases['generic_icons']}', 2, 1),
('{$install_phrases['generic_avatars']}', 1, 1)
";
$schema['INSERT']['explain']['imagecategory'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "imagecategory");



$schema['INSERT']['query']['language'] = "INSERT INTO " . TABLE_PREFIX . "language (title, languagecode, charset, decimalsep, thousandsep) VALUES ('{$install_phrases['master_language_title']}', '{$install_phrases['master_language_langcode']}', '{$install_phrases['master_language_charset']}', '{$install_phrases['master_language_decimalsep']}', '{$install_phrases['master_language_thousandsep']}')";
$schema['INSERT']['explain']['language'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "language");



$schema['INSERT']['query']['paymentapi'] = "
INSERT INTO " . TABLE_PREFIX . "paymentapi (title, currency, recurring, classname, active, settings) VALUES
('Paypal', 'usd,gbp,eur,aud,cad', 1, 'paypal', 0, '" . $db->escape_string(serialize(array(
	'ppemail' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'primaryemail' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "'),
('NOCHEX', 'gbp', 0, 'nochex', 0, '" . $db->escape_string(serialize(array(
	'ncxemail' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "'),
('Worldpay', 'usd,gbp,eur', 1, 'worldpay', 0, '" . $db->escape_string(serialize(array(
	'worldpay_instid' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'worldpay_password' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "'),
('Authorize.Net', 'usd,gbp,eur', 0, 'authorizenet', 0, '" . $db->escape_string(serialize(array(
	'authorize_loginid' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'txnkey' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'authorize_md5secret' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "'),
('2Checkout', 'usd', 0, '2checkout', 0, '" . $db->escape_string(serialize(array(
	'twocheckout_id' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'number'
	),
	'secret_word' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "'),
('Moneybookers', 'usd,gbp', 0, 'moneybookers', 0, '" . $db->escape_string(serialize(array(
	'mbemail' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'mbsecret' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "'),
('CCBill', 'usd', 0, 'ccbill', 0, '" . $db->escape_string(serialize(array(
	'clientAccnum' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'clientSubacc' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'formName' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'secretword' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'username' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	),
	'password' => array(
		'type' => 'text',
		'value' => '',
		'validate' => 'string'
	)
))) . "')
";

$schema['INSERT']['explain']['paymentapi'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "paymentapi");



$schema['INSERT']['query']['profilefield'] = "
INSERT INTO " . TABLE_PREFIX . "profilefield (profilefieldid, required, hidden, maxlength, size, displayorder, editable, type, data, height, def, optional, searchable, memberlist, regex, form) VALUES
('1', '0', '0', '100', '25', '1', '1', 'input', '', '0', '0', '0', '1', '1', '', '0'),
('2', '0', '0', '100', '25', '2', '1', 'input', '', '0', '0', '0', '1', '1', '', '0'),
('3', '0', '0', '100', '25', '3', '1', 'input', '', '0', '0', '0', '1', '1', '', '0'),
('4', '0', '0', '100', '25', '4', '1', 'input', '', '0', '0', '0', '1', '1', '', '0')
";
$schema['INSERT']['explain']['profilefield'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "profilefield");

if (!empty($customphrases) AND is_array($customphrases))
{
	foreach ($customphrases AS $fieldname => $phrase)
	{
		foreach ($phrase AS $varname => $text)
		{
			$schema['INSERT']['query']["$varname"] = "
			INSERT INTO " . TABLE_PREFIX . "phrase (languageid, fieldname, varname, text, product) VALUES
			(0, '$fieldname', '$varname', '" . $db->escape_string($text) . "', 'vbulletin')
			";
			$schema['INSERT']['explain']["$varname"] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "phrase");
		}
	}
}

// *** MAKE THIS NICER ***
$schema['INSERT']['query']['phrasetype'] = "
	INSERT INTO " . TABLE_PREFIX . "phrasetype
		(fieldname, title, editrows, special)
	VALUES
		('global',           '{$phrasetype['global']}', 3, 0),
		('cpglobal',         '{$phrasetype['cpglobal']}', 3, 0),
		('cppermission',     '{$phrasetype['cppermission']}', 3, 0),
		('forum',            '{$phrasetype['forum']}', 3, 0),
		('calendar',         '{$phrasetype['calendar']}', 3, 0),
		('attachment_image', '{$phrasetype['attachment_image']}', 3, 0),
		('style',            '{$phrasetype['style']}', 3, 0),
		('logging',          '{$phrasetype['logging']}', 3, 0),
		('cphome',           '{$phrasetype['cphome']}', 3, 0),
		('promotion',        '{$phrasetype['promotion']}', 3, 0),
		('user',             '{$phrasetype['user']}', 3, 0),
		('help_faq',         '{$phrasetype['help_faq']}', 3, 0),
		('sql',              '{$phrasetype['sql']}', 3, 0),
		('subscription',     '{$phrasetype['subscription']}', 3, 0),
		('language',         '{$phrasetype['language']}', 3, 0),
		('bbcode',           '{$phrasetype['bbcode']}', 3, 0),
		('stats',            '{$phrasetype['stats']}', 3, 0),
		('diagnostic',       '{$phrasetype['diagnostics']}', 3, 0),
		('maintenance',      '{$phrasetype['maintenance']}', 3, 0),
		('cprofilefield',    '{$phrasetype['cprofilefield']}', 3, 0),
		('profilefield',     '{$phrasetype['profile']}', 3, 0),
		('thread',           '{$phrasetype['thread']}', 3, 0),
		('timezone',         '{$phrasetype['timezone']}', 3, 0),
		('banning',          '{$phrasetype['banning']}', 3, 0),
		('reputation',       '{$phrasetype['reputation']}', 3, 0),
		('wol',              '{$phrasetype['wol']}', 3, 0),
		('threadmanage',     '{$phrasetype['threadmanage']}', 3, 0),
		('pm',               '{$phrasetype['pm']}', 3, 0),
		('cpuser',           '{$phrasetype['cpuser']}', 3, 0),
		('accessmask',       '{$phrasetype['accessmask']}', 3, 0),
		('cron',             '{$phrasetype['cron']}', 3, 0),
		('moderator',        '{$phrasetype['moderator']}', 3, 0),
		('cpoption',         '{$phrasetype['cpoption']}', 3, 0),
		('cprank',           '{$phrasetype['cprank']}', 3, 0),
		('cpusergroup',      '{$phrasetype['cpusergroup']}', 3, 0),
		('holiday',          '{$phrasetype['holiday']}', 3, 0),
		('posting',          '{$phrasetype['posting']}', 3, 0),
		('poll',             '{$phrasetype['poll']}', 3, 0),
		('fronthelp',        '{$phrasetype['fronthelp']}', 3, 0),
		('register',         '{$phrasetype['register']}', 3, 0),
		('search',           '{$phrasetype['search']}', 3, 0),
		('showthread',       '{$phrasetype['showthread']}', 3, 0),
		('postbit',          '{$phrasetype['postbit']}', 3, 0),
		('forumdisplay',     '{$phrasetype['forumdisplay']}', 3, 0),
		('messaging',        '{$phrasetype['messaging']}', 3, 0),
		('plugins',          '{$phrasetype['plugins']}', 3, 0),
		('inlinemod',        '{$phrasetype['inlinemod']}', 3, 0),
		('reputationlevel',  '{$phrasetype['reputationlevel']}', 3, 0),
		('infraction',       '{$phrasetype['infraction']}', 3, 0),
		('infractionlevel',  '{$phrasetype['infractionlevel']}', 3, 0),
		('error',            '{$phrasetype['front_end_error']}', 8, 1),
		('frontredirect',    '{$phrasetype['front_end_redirect']}', 8, 1),
		('emailbody',        '{$phrasetype['email_body']}', 10, 1),
		('emailsubject',     '{$phrasetype['email_subj']}', 3, 1),
		('vbsettings',       '{$phrasetype['vbulletin_settings']}', 4, 1),
		('cphelptext',       '{$phrasetype['cp_help']}', 8, 1),
		('faqtitle',         '{$phrasetype['faq_title']}', 3, 1),
		('faqtext',          '{$phrasetype['faq_text']}', 10, 1),
		('cpstopmsg',        '{$phrasetype['stop_message']}', 8, 1)
";
// *** END MAKE THIS NICER ***
$schema['INSERT']['explain']['phrasetype'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "phrasetype");



$schema['INSERT']['query']['style'] = "INSERT INTO " . TABLE_PREFIX . "style (styleid, title, parentid, templatelist, css, replacements, userselect, displayorder) VALUES
(1, '{$install_phrases['default_style']}', -1, '1, -1', '', '', 1, 1)
";
$schema['INSERT']['explain']['style'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "style");


$schema['INSERT']['query']['infractionlevel'] = "INSERT INTO " . TABLE_PREFIX . "infractionlevel (infractionlevelid, points, expires, period, warning) VALUES
(1, 1, 10, 'D', 1),
(2, 1, 10, 'D', 1),
(3, 1, 10, 'D', 1),
(4, 1, 10, 'D', 1)
";

$schema['INSERT']['explain']['infractionlevel'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "infractionlevel");


$schema['INSERT']['query']['reputationlevel'] = "INSERT INTO " . TABLE_PREFIX . "reputationlevel (reputationlevelid, minimumreputation) VALUES
(1, -999999),
(2, -50),
(3, -10),
(4, 0),
(5, 10),
(6, 50),
(7, 150),
(8, 250),
(9, 350),
(10, 450),
(11, 550),
(12, 650),
(13, 1000),
(14, 1500),
(15, 2000)
";

$schema['INSERT']['explain']['reputationlevel'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "reputationlevel");


$schema['INSERT']['query']['smilie'] = "
INSERT INTO " . TABLE_PREFIX . "smilie (title, smilietext, smiliepath, imagecategoryid, displayorder) VALUES
('{$install_phrases['smilie_smile']}', ':)', 'images/smilies/smile.gif', '1', '1'),
('{$install_phrases['smilie_embarrass']}', ':o', 'images/smilies/redface.gif', '1', '1'),
('{$install_phrases['smilie_grin']}', ':D', 'images/smilies/biggrin.gif', '1', '1'),
('{$install_phrases['smilie_wink']}', ';)', 'images/smilies/wink.gif', '1', '1'),
('{$install_phrases['smilie_tongue']}', ':p', 'images/smilies/tongue.gif', '1', '1'),
('{$install_phrases['smilie_cool']}', ':cool:', 'images/smilies/cool.gif', '1', '5'),
('{$install_phrases['smilie_roll']}', ':rolleyes:', 'images/smilies/rolleyes.gif', '1', '3'),
('{$install_phrases['smilie_mad']}', ':mad:', 'images/smilies/mad.gif', '1', '1'),
('{$install_phrases['smilie_eek']}', ':eek:', 'images/smilies/eek.gif', '1', '7'),
('{$install_phrases['smilie_confused']}', ':confused:', 'images/smilies/confused.gif', '1', '1'),
('{$install_phrases['smilie_frown']}', ':(', 'images/smilies/frown.gif', '1', '1')
";

$schema['INSERT']['explain']['smilie'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "smilie");

// Load permissions to see what is given on new installs
require_once(DIR . '/includes/class_bitfield_builder.php');
if (vB_Bitfield_Builder::build(false) !== false)
{
	$myobj =& vB_Bitfield_Builder::init();
}
else
{
	echo "<strong>error</strong>\n";
	print_r(vB_Bitfield_Builder::fetch_errors());
}

$groupinfo = array();
foreach ($myobj->data['ugp'] AS $grouptitle => $perms)
{
	for ($x = 1; $x < 9; $x++)
	{
		$groupinfo["$x"]["$grouptitle"] = 0;
	}

	foreach ($perms AS $permtitle => $permvalue)
	{
		if (empty($permvalue['group']))
		{
			continue;
		}

		if (!empty($permvalue['install']))
		{
			foreach ($permvalue['install'] AS $gid)
			{
				$groupinfo["$gid"]["$grouptitle"] += $permvalue['value'];
			}
		}
	}
}

// Need to change the hard coded values to use the defined constants so we can easily see what permissions we are giving.
$schema['INSERT']['query']['usergroup'] = "
INSERT INTO " . TABLE_PREFIX . "usergroup
	(	usergroupid, title, description, usertitle,
		passwordexpires, passwordhistory, pmquota, pmsendmax, opentag, closetag, canoverride, ispublicgroup,
		forumpermissions, pmpermissions, calendarpermissions,
		wolpermissions, adminpermissions, genericpermissions,
		signaturepermissions, genericoptions,
		attachlimit, avatarmaxwidth, avatarmaxheight, avatarmaxsize,
		profilepicmaxwidth, profilepicmaxheight, profilepicmaxsize,
		sigmaxrawchars, sigmaxchars, sigmaxlines, sigmaxsizebbcode, sigmaximages,
		sigpicmaxwidth, sigpicmaxheight, sigpicmaxsize
	)
VALUES
	(	1, '{$install_phrases['usergroup_guest_title']}', '', '{$install_phrases['usergroup_guest_usertitle']}',
		0, 0, 50, 0, '', '', 0, 0,
		{$groupinfo[1]['forumpermissions']}, {$groupinfo[1]['pmpermissions']}, {$groupinfo[1]['calendarpermissions']},
		{$groupinfo[1]['wolpermissions']}, {$groupinfo[1]['adminpermissions']}, {$groupinfo[1]['genericpermissions']},
		{$groupinfo[1]['signaturepermissions']}, {$groupinfo[1]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	),
	(	2, '{$install_phrases['usergroup_registered_title']}', '', '',
		0, 0, 50, 5, '', '', 0, 0,
		{$groupinfo[2]['forumpermissions']}, {$groupinfo[2]['pmpermissions']}, {$groupinfo[2]['calendarpermissions']},
		{$groupinfo[2]['wolpermissions']}, {$groupinfo[2]['adminpermissions']}, {$groupinfo[2]['genericpermissions']},
		{$groupinfo[2]['signaturepermissions']}, {$groupinfo[2]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	),
	(	3, '{$install_phrases['usergroup_activation_title']}', '', '',
		0, 0, 50, 0, '', '', 0, 0,
		{$groupinfo[3]['forumpermissions']}, {$groupinfo[3]['pmpermissions']}, {$groupinfo[3]['calendarpermissions']},
		{$groupinfo[3]['wolpermissions']}, {$groupinfo[3]['adminpermissions']}, {$groupinfo[3]['genericpermissions']},
		{$groupinfo[3]['signaturepermissions']}, {$groupinfo[3]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	),
	(	4, '{$install_phrases['usergroup_coppa_title']}', '', '',
		0, 0, 50, 0, '', '', 0, 0,
		{$groupinfo[4]['forumpermissions']}, {$groupinfo[4]['pmpermissions']}, {$groupinfo[4]['calendarpermissions']},
		{$groupinfo[4]['wolpermissions']}, {$groupinfo[4]['adminpermissions']}, {$groupinfo[4]['genericpermissions']},
		{$groupinfo[4]['signaturepermissions']}, {$groupinfo[4]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	),
	(	5, '{$install_phrases['usergroup_super_title']}', '', '{$install_phrases['usergroup_super_usertitle']}',
		0, 0, 50, 0, '', '', 0, 0,
		{$groupinfo[5]['forumpermissions']}, {$groupinfo[5]['pmpermissions']}, {$groupinfo[5]['calendarpermissions']},
		{$groupinfo[5]['wolpermissions']}, {$groupinfo[5]['adminpermissions']}, {$groupinfo[5]['genericpermissions']},
		{$groupinfo[5]['signaturepermissions']}, {$groupinfo[5]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	),
	(	6, '{$install_phrases['usergroup_admin_title']}', '', '{$install_phrases['usergroup_admin_usertitle']}',
		180, 360, 50, 5, '', '', 0, 0,
		{$groupinfo[6]['forumpermissions']}, {$groupinfo[6]['pmpermissions']}, {$groupinfo[6]['calendarpermissions']},
		{$groupinfo[6]['wolpermissions']}, {$groupinfo[6]['adminpermissions']}, {$groupinfo[6]['genericpermissions']},
		{$groupinfo[6]['signaturepermissions']}, {$groupinfo[6]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		0, 0, 0, 7, 0,
		500, 100, 10000
	),
	(	7, '{$install_phrases['usergroup_mod_title']}', '', '{$install_phrases['usergroup_mod_usertitle']}',
		0, 0, 50, 5, '', '', 0, 0,
		{$groupinfo[7]['forumpermissions']}, {$groupinfo[7]['pmpermissions']}, {$groupinfo[7]['calendarpermissions']},
		{$groupinfo[7]['wolpermissions']}, {$groupinfo[7]['adminpermissions']}, {$groupinfo[7]['genericpermissions']},
		{$groupinfo[7]['signaturepermissions']}, {$groupinfo[7]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	),
	(	8, '{$install_phrases['usergroup_banned_title']}', '', '{$install_phrases['usergroup_banned_usertitle']}',
		0, 0, 0, 0, '', '', 0, 0,
		{$groupinfo[8]['forumpermissions']}, {$groupinfo[8]['pmpermissions']}, {$groupinfo[8]['calendarpermissions']},
		{$groupinfo[8]['wolpermissions']}, {$groupinfo[8]['adminpermissions']}, {$groupinfo[8]['genericpermissions']},
		{$groupinfo[8]['signaturepermissions']}, {$groupinfo[8]['genericoptions']},
		0, 80, 80, 20000,
		100, 100, 65535,
		1000, 500, 0, 7, 4,
		500, 100, 10000
	)
";

$schema['INSERT']['explain']['usergroup'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "usergroup");



$schema['INSERT']['query']['usertitle'] = "
INSERT INTO " . TABLE_PREFIX . "usertitle (minposts, title) VALUES
('0', '{$install_phrases['usertitle_jnr']}'),
('30', '{$install_phrases['usertitle_mbr']}'),
('100', '{$install_phrases['usertitle_snr']}')
";

$schema['INSERT']['explain']['usertitle'] = sprintf($vbphrase['default_data_type'], TABLE_PREFIX . "usertitle");



/*======================================================================*\
|| ####################################################################
|| # Downloaded: 18:52, Sat Jul 14th 2007
|| # CVS: $RCSfile$ - $Revision: 16859 $
|| ####################################################################
\*======================================================================*/
?>