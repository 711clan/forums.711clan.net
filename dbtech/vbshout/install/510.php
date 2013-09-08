<?php
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_report`
	(
		`reportid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`reportuserid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`shoutid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`instanceid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`shout` MEDIUMTEXT NULL DEFAULT NULL,
		`reportreason` MEDIUMTEXT NULL DEFAULT NULL,
		`modnotes` MEDIUMTEXT NULL DEFAULT NULL,
		`handled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'		
	)
");
self::report('Created Table', 'dbtech_vbshout_report');

if (self::$db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	self::$db_alter->add_field(array(
		'name'       => 'bbcodepermissions',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	self::$db_alter->add_field(array(
		'name'       => 'notices',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	self::$db->query_write("UPDATE " . TABLE_PREFIX . "dbtech_vbshout_instance SET bbcodepermissions = 'a:4:{i:6;i:95;i:5;i:95;i:2;i:95;i:7;i:95;}'");
	
	if (class_exists('VBSHOUT_CACHE'))
	{
		VBSHOUT_CACHE::build('instance');
	}
	self::report('Altered Table', 'dbtech_vbshout_instance');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_soundsettings',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::report('Altered Table', 'user');
}
?>