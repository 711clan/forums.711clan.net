<?php
$type = self::$vbulletin->input->clean_gpc('p', 	'type', 	TYPE_NOHTML);
$title = self::$vbulletin->input->clean_gpc('p', 'title', TYPE_NOHTML);

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

if (!(self::$instance['options']['activitytriggers'] & 1))
{
	// Un-idle us
	self::unIdle();
}

// Init the Shout DM
$shout = self::initDataManager('Shout', self::$vbulletin, ERRTYPE_ARRAY);
	$shout->set_info('instance', self::$instance);
	$shout->set('instanceid', self::$instance['instanceid']);
	$shout->set('chatroomid', self::$chatroom['chatroomid']);
	$shout->set('message', '/createchat ' . $title);
$shout->save();

?>