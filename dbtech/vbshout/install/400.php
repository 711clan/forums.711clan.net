<?php
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_ignorelist` (
	  `userid` INT( 10 ) UNSIGNED NOT NULL ,
	  `ignoreuserid` INT( 10 ) UNSIGNED NOT NULL ,
	  PRIMARY KEY ( `userid` , `ignoreuserid` )
	)
");
self::report('Created Table', 'dbtech_vbshout_ignorelist');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_log` (
	  `logid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	  `username` VARCHAR( 100 ) NOT NULL ,	  
	  `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	  `ipaddress` CHAR( 15 ) NOT NULL ,
	  `command` VARCHAR( 50 ) NOT NULL ,
	  `comment` MEDIUMTEXT NULL DEFAULT NULL ,
	  PRIMARY KEY (`logid`)
	)				 
");
self::report('Created Table', 'dbtech_vbshout_log');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_shout` (
	  `shoutid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `userid` INT( 10 ) NOT NULL DEFAULT '0' ,
	  `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `message` MEDIUMTEXT NULL ,
	  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' ,
	  `id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `notification` ENUM( '', 'thread', 'reply' ) NOT NULL DEFAULT '' ,
	  PRIMARY KEY (`shoutid`),
	  KEY `type` (`type`,`id`),
	  KEY `userid` (`userid`),
	  KEY `dateline` (`dateline`)
	)
");
self::report('Created Table', 'dbtech_vbshout_shout');


// Altered Tables

// Add the administrator field
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshoutadminperms',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'administrator');	
}

// Add the usergroup field
if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_newthread',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_newreply',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'forum');	
}

// Add the usergroup field
if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_banned',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_settings',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '4095'
	));
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shouts',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutstyle',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
	));	
	self::report('Altered Table', 'user');	
}

if (self::$db_alter->fetch_table_info('usergroup'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshoutpermissions',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'usergroup');	
}


// Populated Tables

// Populate the datastore table
self::$db->query_write("
	REPLACE INTO " . TABLE_PREFIX . "datastore
		(title, data)
	VALUES 
		('dbtech_vbshout_aoptime', 0),
		('dbtech_vbshout_sticky', 'Welcome to DragonByte Tech: vBShout!')
");
self::report('Populated Table', 'datastore');

self::$db->query_write("
	INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_shout
		(userid, dateline, message)
	VALUES 
		('-1', " . TIMENOW . ", 'Congratulations on your installation of DragonByte Tech: vBShout! We have taken the liberty of setting some default options for you, but you should enter the AdminCP and familiarise yourself with the various settings. Use the /prune command to get rid of this message, or double-click it and click the Delete button. Enjoy your new Shoutbox!')
");
self::report('Populated Table', 'dbtech_vbshout_shout');
?>