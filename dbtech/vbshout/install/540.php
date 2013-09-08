<?php
if (self::$db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	self::$db_alter->drop_field('deployment');	
	self::$db_alter->drop_field('templates');	
	self::$db_alter->add_field(array(
		'name'       => 'varname',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	self::$db_alter->add_field(array(
		'name'       => 'options',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::$db_alter->add_field(array(
		'name'       => 'forumids',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	self::report('Altered Table', 'dbtech_vbshout_instance');
	
	$instances_q = self::$db->query_read_slave("SELECT instanceid FROM " . TABLE_PREFIX . "dbtech_vbshout_instance");
	while ($instance = self::$db->fetch_array($instances_q))
	{
		self::$db->query_write("UPDATE " . TABLE_PREFIX . "dbtech_vbshout_instance SET varname = 'instance" . intval($instance['instanceid']) . "' WHERE instanceid = " . intval($instance['instanceid']));
	}
	self::$db->query_write("DELETE FROM " . TABLE_PREFIX . "datastore WHERE title = 'dbtech_vbshout_instance'");
	unset($instance);
	self::$db->free_result($instances_q);
}

?>