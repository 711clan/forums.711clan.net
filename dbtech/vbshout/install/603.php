<?php
if (self::$db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	self::$db_alter->add_field(array(
		'name'       => 'displayorder',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '10'
	));
	self::report('Altered Table', 'dbtech_vbshout_instance');
	
	if (class_exists('VBSHOUT_CACHE'))
	{
		VBSHOUT_CACHE::build('instance');
	}	
}
?>