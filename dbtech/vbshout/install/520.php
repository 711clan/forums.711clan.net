<?php
if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutboxsize',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutboxsize_detached',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_displayorder',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::report('Altered Table', 'user');
}
?>