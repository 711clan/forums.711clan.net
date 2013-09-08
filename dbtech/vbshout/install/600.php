<?php
if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shouts_lifetime',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'user');
}

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_button` (
		`buttonid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(50) NOT NULL,
		`instanceid` int(10) unsigned NOT NULL DEFAULT '0',
		`active` tinyint(1) unsigned NOT NULL DEFAULT '1',
		`link` mediumtext,
		`image` mediumtext,
		PRIMARY KEY (`buttonid`),
		KEY `instanceid` (`instanceid`)
	)				 
");
self::report('Created Table', 'dbtech_vbshout_button');
?>