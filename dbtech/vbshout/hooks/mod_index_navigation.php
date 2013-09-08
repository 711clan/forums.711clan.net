<?php
if (VBSHOUT::$permissions['ismanager'])
{
	construct_nav_option($vbphrase['dbtech_vbshout_instance_management'], 	'vbshout.php?do=instance');
	construct_nav_option($vbphrase['dbtech_vbshout_chatroom_management'], 	'vbshout.php?do=chatroom');		
	construct_nav_option($vbphrase['dbtech_vbshout_view_banned_users'], 	'vbshout.php?do=viewbanned');	
	construct_nav_option($vbphrase['dbtech_vbshout_command_log'], 			'vbshout.php?do=commandlog');

	
	
	($hook = vBulletinHook::fetch_hook('dbtech_vbshout_mod_index_navigation')) ? eval($hook) : false;
	
	construct_nav_option($vbphrase['dbtech_vbshout_notifications'], 		'vbshout.php?do=permissions');		
	construct_nav_option($vbphrase['dbtech_vbshout_settings'],				'vbshout.php?do=options');	
	
	construct_nav_group($vbphrase['dbtech_vbshout']);
}
?>