<?php
self::$vbulletin->input->clean_array_gpc('p', array(
	'editor' 		=> TYPE_ARRAY,
	'tabid' 		=> TYPE_STR,
	'type' 			=> TYPE_STR,
));

if (!self::$tabid)
{
	// Set tabid
	self::$tabid = (in_array(self::$vbulletin->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : self::$vbulletin->GPC['tabid']) . self::$instance['instanceid'];
}

if (empty(self::$vbulletin->GPC['type']))
{
	self::$vbulletin->GPC['type'] = 'shouts';
}

$type = self::$vbulletin->GPC['type'];

if (substr(self::$vbulletin->GPC['type'], 0, 8) == 'chatroom')
{
	// Fetch the chatroomid from the chatroom type
	$chatroomid = explode('_', self::$vbulletin->GPC['type']);
	$chatroomid = $chatroomid[1];
	
	// Override tabid for AOP purposes
	self::$tabid = 'chatroom_' . $chatroomid . '_' . self::$cache['chatroom'][$chatroomid]['instanceid'];
}

// Set shout styles array
$instanceid = self::$instance['instanceid'];
self::$vbulletin->GPC['editor']['color'] = preg_replace('/[^A-Za-z0-9 #(),]/', '', self::$vbulletin->GPC['editor']['color']);
self::$shoutstyle[self::$instance['instanceid']] = self::$vbulletin->GPC['editor'];

// Update the user's editor styles
self::$vbulletin->db->query_write("
	UPDATE " . TABLE_PREFIX . "user
	SET dbtech_vbshout_shoutstyle = " . self::$vbulletin->db->sql_prepare(trim(serialize(self::$shoutstyle))) . "
	WHERE userid = " . self::$vbulletin->userinfo['userid']
);

// Set the AOP
self::set_aop('shouts', self::$instance['instanceid'], false, true);

if (!(self::$instance['options']['activitytriggers'] & 128))
{
	// Un-idle us
	self::unIdle();
}

// Fetch the shouts again
self::fetch_shouts($args);
?>