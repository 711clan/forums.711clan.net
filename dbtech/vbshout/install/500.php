<?php
if (self::$db_alter->fetch_table_info('dbtech_vbshout_chatroommember'))
{
	self::$db_alter->add_field(array(
		'name'       => 'invitedby',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'dbtech_vbshout_chatroommember');	
}

if (self::$db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	self::$db_alter->add_field(array(
		'name'       => 'sticky',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	self::$db_alter->add_field(array(
		'name'       => 'shoutsound',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	self::$db_alter->add_field(array(
		'name'       => 'invitesound',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	self::$db_alter->add_field(array(
		'name'       => 'pmsound',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_vbshout_instance
		CHANGE active active TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'
	");
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_vbshout_instance
		CHANGE autodisplay autodisplay TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'		
	");
	self::report('Altered Table', 'dbtech_vbshout_instance');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_pm',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'user');
}

self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_vbshout_shout
	CHANGE forumid forumid INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	CHANGE instanceid instanceid INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	CHANGE chatroomid chatroomid INT( 10 ) UNSIGNED NOT NULL DEFAULT '0'	
");
self::report('Altered Table', 'dbtech_vbshout_shout');
?>