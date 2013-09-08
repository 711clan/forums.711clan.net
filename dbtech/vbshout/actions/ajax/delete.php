<?php
if (!(self::$instance['options']['activitytriggers'] & 2))
{
	// Un-idle us
	self::unIdle();
}

do
{
	self::$vbulletin->input->clean_array_gpc('p', array(
		'shoutid' 		=> TYPE_INT,
		'type' 			=> TYPE_STR,
		'userid' 		=> TYPE_UINT,					
		'tabid' 		=> TYPE_STR,					
		'chatroomid' 	=> TYPE_UINT,					
	));
	
	if (!self::$tabid)
	{
		// Set tabid
		self::$tabid = (in_array(self::$vbulletin->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : self::$vbulletin->GPC['tabid']) . self::$instance['instanceid'];
	}
	
	// Wonder if this is needed...
	self::$vbulletin->GPC['message'] = urldecode(self::$vbulletin->GPC['message']);
	
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
	
	// Make sure it's set
	$shouttype = (self::$shouttypes["$type"] ? $type : 'shout');
	
	if (empty(self::$vbulletin->GPC['type']))
	{
		self::$vbulletin->GPC['type'] = 'shouts';
	}

	// Init the Shout DM
	$shout = self::initDataManager('Shout', self::$vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('instance', self::$instance);
	
	if (!self::$vbulletin->GPC['shoutid'])
	{
		// Invalid Shout ID
		break;
	}
	
	if (!self::$vbulletin->GPC['shoutinfo'] = self::$vbulletin->db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . self::$vbulletin->db->sql_prepare(self::$vbulletin->GPC['shoutid'])))
	{
		// Shout didn't exist
		break;
	}
	
	// Set the existing data
	$shout->set_existing(self::$vbulletin->GPC['shoutinfo']);
	
	// Delete
	$shout->delete();
	
	// Fetch the file in question
	require(DIR . '/dbtech/vbshout/actions/ajax/fetch.php');
}
while (false);
?>