<?php
if (!(self::$instance['options']['activitytriggers'] & 1024))
{
	// Un-idle us
	self::unIdle();
}

if (self::$instance['options']['activeusers'])
{
	self::fetch_active_users(true, true);
	if ($args['chatroomid'])
	{
		// Array of all active users
		self::$fetched['activeusers']['usernames'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_chat_users']);
		if (self::$instance['options']['enableaccess'])
		{
			self::$fetched['activeusers']['usernames'] .= '<br /><br /><a href="vbshout.php?' . self::$vbulletin->session->vars['sessionurl'] . 'do=chataccess&amp;instanceid=' . self::$instance['instanceid'] . '&amp;chatroomid=' . $args['chatroomid'] . '" target="_blank"><b>' . $vbphrase['dbtech_vbshout_chat_access'] . '</b></a>';
		}

	}
	else
	{
		// Array of all active users
		self::$fetched['activeusers']['usernames'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
	}
}

// Fetch active users without menu or force
self::fetch_active_users();

self::$fetched['activeusers']['count'] = count(self::$activeusers);
?>