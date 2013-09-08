<?php
// Drop
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->drop_field('dbtech_vbshoutadminperms');
	self::report('Reverted Table', 'administrator');	
}

self::$db->query_write("
	DELETE FROM `" . TABLE_PREFIX . "datastore` WHERE `title` LIKE 'dbtech_vbshout_%'
");
self::report('Reverted Table', 'datastore');

// Drop
if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db_alter->drop_field('dbtech_vbshout_newthread');
	self::$db_alter->drop_field('dbtech_vbshout_newreply');
	self::report('Reverted Table', 'forum');	
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->drop_field('dbtech_vbshout_banned');
	self::$db_alter->drop_field('dbtech_vbshout_settings');
	self::$db_alter->drop_field('dbtech_vbshout_shouts');
	self::$db_alter->drop_field('dbtech_vbshout_shouts_lifetime');
	self::$db_alter->drop_field('dbtech_vbshout_shoutstyle');
	self::$db_alter->drop_field('dbtech_vbshout_silenced');
	self::$db_alter->drop_field('dbtech_vbshout_shoutarea');	
	self::$db_alter->drop_field('dbtech_vbshout_pm');
	self::$db_alter->drop_field('dbtech_vbshout_soundsettings');
	self::$db_alter->drop_field('dbtech_vbshout_shoutboxsize');
	self::$db_alter->drop_field('dbtech_vbshout_shoutboxsize_detached');
	self::$db_alter->drop_field('dbtech_vbshout_displayorder');
	self::report('Reverted Table', 'user');	
}

if (self::$db_alter->fetch_table_info('usergroup'))
{
	self::$db_alter->drop_field('dbtech_vbshoutpermissions');
	self::report('Reverted Table', 'usergroup');	
}

foreach (array(
	'dbtech_vbshout_chatroom',
	'dbtech_vbshout_chatroommember',
	'dbtech_vbshout_deeplog',
	'dbtech_vbshout_ignorelist',
	'dbtech_vbshout_instance',
	'dbtech_vbshout_log',
	'dbtech_vbshout_report',
	'dbtech_vbshout_session',
	'dbtech_vbshout_shout',
) as $table)
{
	self::$db->query_write("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "{$table}`");
	self::report('Deleted Table', $table);
}
?>