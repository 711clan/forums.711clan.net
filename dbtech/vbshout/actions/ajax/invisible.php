<?php
self::$vbulletin->input->clean_array_gpc('p', array(
	'invisibility' => TYPE_BOOL,
));

// Set the new invis setting
self::$vbulletin->userinfo['dbtech_vbshout_invisiblesettings'][self::$instance['instanceid']] = self::$vbulletin->GPC['invisibility'];

// Update the user's editor styles
self::$vbulletin->db->query_write("
	UPDATE " . TABLE_PREFIX . "user
	SET dbtech_vbshout_invisiblesettings = " . self::$vbulletin->db->sql_prepare(trim(serialize(self::$vbulletin->userinfo['dbtech_vbshout_invisiblesettings']))) . "
	WHERE userid = " . intval(self::$vbulletin->userinfo['userid'])	
);

if (self::$vbulletin->GPC['invisibility'])
{
	// We're switching to stealth mode, ensure we don't have any sessions
	self::$db->delete('dbtech_vbshout_session', array(
		self::$vbulletin->userinfo['userid'],
		self::$instance['instanceid'],
	), 'WHERE userid = ? AND instanceid = ?');
}

if (!(self::$instance['options']['activitytriggers'] & 2048))
{
	// Un-idle us
	self::unIdle();
}
?>