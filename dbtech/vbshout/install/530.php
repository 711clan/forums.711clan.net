<?php
if (self::$db_alter->fetch_table_info('dbtech_vbshout_chatroom'))
{
	self::$db_alter->add_field(array(
		'name'       => 'members',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::report('Altered Table', 'dbtech_vbshout_chatroom');
}
if (self::$db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	self::$db_alter->add_field(array(
		'name'       => 'sticky_raw',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::report('Altered Table', 'dbtech_vbshout_instance');
}
if (self::$db_alter->fetch_table_info('dbtech_vbshout_shout'))
{
	self::$db_alter->add_field(array(
		'name'       => 'message_raw',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::report('Altered Table', 'dbtech_vbshout_shout');
}

define('CP_REDIRECT', 'vbshout.php?do=finalise&version=530');
define('DISABLE_PRODUCT_REDIRECT', true);
?>