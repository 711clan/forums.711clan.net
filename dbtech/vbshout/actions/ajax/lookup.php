<?php
if (!(self::$instance['options']['activitytriggers'] & 32))
{
	// Un-idle us
	self::unIdle();
}

do
{
	$username	= self::$vbulletin->input->clean_gpc('r', 'username',	TYPE_STR);
	
	// Do url decode
	$username = urldecode($username);	
	
	if (!self::$instance['options']['enablepms'])
	{
		self::$fetched['error'] = $vbphrase['dbtech_vbshout_pms_disabled'];
		break;
	}
	
	if ($username == self::$vbulletin->userinfo['username'])
	{
		self::$fetched['error'] = $vbphrase['dbtech_vbshout_invalid_username'];
		break;
	}
	
	if (!$userid = self::$vbulletin->db->query_first("
		SELECT userid
		FROM " . TABLE_PREFIX . "user
		WHERE username = " . self::$vbulletin->db->sql_prepare($username)
	))
	{
		self::$fetched['error'] = $vbphrase['dbtech_vbshout_invalid_username'];
		break;
	}
	
	// Return the userid
	self::$fetched['pmuserid'] = $userid['userid'];
}
while (false);
?>