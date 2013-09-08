<?php
do
{
	// Find out all the tabs we're at
	$tabs = self::$vbulletin->input->clean_gpc('r', 'tabs', TYPE_ARRAY_BOOL);
	
	$chatrooms = self::$db->fetchAllSingleKeyed('
		SELECT chatroomid, user.username
		FROM $dbtech_vbshout_chatroommember AS chatroommember
		LEFT JOIN $user AS user ON(user.userid = chatroommember.invitedby)
		WHERE chatroommember.userid = ?
			AND status = 0
	', 'chatroomid', 'username', array(
		self::$vbulletin->userinfo['userid']
	));
	foreach ($chatrooms as $chatroomid => $username)
	{
		$chatroom = self::$cache['chatroom'][$chatroomid];
		if (!$chatroom['active'] OR ($chatroom['instanceid'] != self::$instance['instanceid'] AND $chatroom['instanceid'] != 0))
		{
			// Inactive chat room
			continue;
		}
		
		// Store information regarding the chatroom
		self::$fetched['chatrooms'][] = array(
			'chatroomid' 	=> $chatroomid,
			'instanceid' 	=> $chatroom['instanceid'],
			'title' 		=> $chatroom['title'],
			'username' 		=> ($username ? $username : 'N/A')
		);
	}		
	
	foreach ($tabs as $tabid => $enabled)
	{
		if ($tabid == 'activeusers')
		{
			// Shouldn't happen
			continue;
		}
		
		if (substr($tabid, 0, 8) == 'chatroom')
		{
			// Get the chatroom id
			$chatroomid = explode('_', $tabid);
			$chatroomid = $chatroomid[1];
			
			// Already set
			$instanceid = '';
		}
		else if (substr($tabid, 0, 2) == 'pm')
		{
			// Already set
			$instanceid = '';
		}
		else
		{
			// Just use the normal instance id
			$instanceid = self::$instance['instanceid'];
		}
		
		// File system
		$mtime = intval(@file_get_contents(DIR . '/dbtech/vbshout/aop/markread-' . $tabid . $instanceid . '.txt'));
		
		if ($mtime)
		{
			// Send back AOP times
			self::$fetched['aoptimes'][] = array(
				'aoptime' 	=> $mtime,
				'tabid' 	=> $tabid,
				'nosound' 	=> 0,
			);
		}
	}
	
	$pmtime = self::$vbulletin->input->clean_gpc('r', 'pmtime', TYPE_UINT);
	if (self::$vbulletin->userinfo['dbtech_vbshout_pm'] > $pmtime)
	{
		// Set new PM time
		self::$fetched['pmtime'] = self::$vbulletin->userinfo['dbtech_vbshout_pm'];
	}
	
	// Find out why we're here
	$type = self::$vbulletin->input->clean_gpc('r', 'type', TYPE_STR);
	
	if (!self::$tabid)
	{
		// Set tabid
		self::$tabid = (in_array($type, array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : $type) . self::$instance['instanceid'];
	}
	
	if (substr($type, 0, 2) == 'pm')
	{
		// Fetch AOP time
		self::fetch_aop($type, '');
		
		// Fetch the userid from the PM type
		$userid = explode('_', $type);
		$userid = $userid[1];
		
		// Set shout args to only include shouts made between self and result of substr
		//$args['userids'] 	= array(self::$vbulletin->userinfo['userid'], $userid);
		$args['types']		= self::$shouttypes['pm'];
		$args['onlyuser']	= $userid;
		
		// Override type
		$type = 'shouts';
	}	
	
	if (substr($type, 0, 8) == 'chatroom')
	{
		// Fetch the chatroomid from the chatroom type
		$chatroomid = explode('_', $type);
		$chatroomid = $chatroomid[1];
		
		// Set shout args to only include shouts posted to said chat room
		$args['chatroomid']	= $chatroomid;
		
		if (!self::$chatroom = self::$cache['chatroom']["$chatroomid"])
		{
			// Wrong chatroom
			self::$fetched['error'] = 'disband_' . $chatroomid;
		}	
		else
		{
			if (!self::$chatroom['membergroupids'])
			{
				$userid = self::$vbulletin->userinfo['userid'];
				
				// This is not a members-only group
				if (!isset(self::$chatroom['members']["$userid"]))
				{
					self::$fetched['error'] = 'disband_' . $chatroomid;
					unset($args['chatroomid']);
				}
			}
			else
			{
				// Override tabid for AOP purposes
				self::$tabid = 'chatroom_' . $chatroomid . '_' . self::$chatroom['instanceid'];
				
				if (!is_member_of(self::$vbulletin->userinfo, explode(',', self::$chatroom['membergroupids'])) OR !self::$chatroom['active'])
				{
					// Usergroup no longer a member
					self::$fetched['error'] = 'disband_' . $chatroomid;
					unset($args['chatroomid']);
				}			
			}
		}
		
		// Fetch AOP time
		self::fetch_aop('chatroom_' . $chatroomid . '_', self::$chatroom['instanceid']);
		
		$type = 'shouts';
	}							
	
	if ((
		!isset(self::$instance['options']['shoutboxtabs']) OR (self::$instance['options']['shoutboxtabs'] & 4)) AND
		self::$instance['permissions_parsed']['canmodchat']
	)
	{
		$unhandledreports = self::$vbulletin->db->query_first_slave("
			SELECT COUNT(*) AS numunhandled
			FROM " . TABLE_PREFIX . "dbtech_vbshout_report
			WHERE handled = 0
				AND instanceid = " . self::$vbulletin->db->sql_prepare(self::$instance['instanceid'])
		);
		self::$fetched['activereports'] = $unhandledreports['numunhandled'];
	}
	
	($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler_fetch')) ? eval($hook) : false;
	
	if ($type == 'shoutnotifs')
	{
		// Fetch AOP time
		self::fetch_aop($type, self::$instance['instanceid']);
		
		$args['types']		= self::$shouttypes['notif'];
		
		// Override type
		$type = 'shouts';
	}
	
	if ($type == 'systemmsgs')
	{
		// Fetch AOP time
		self::fetch_aop($type, self::$instance['instanceid']);
		
		$args['types']		= self::$shouttypes['system'];
		
		// Override type
		$type = 'shouts';
	}
	
	if ($type == 'shouts' OR self::$fetched['pmtime'])
	{
		// Fetch AOP time
		self::fetch_aop('shouts', self::$instance['instanceid']);
		
		// Fetch shouts
		self::fetch_shouts($args);
	}
	
	if ($type == 'shout')
	{
		// What shout we want to be editing
		$shoutid 	= self::$vbulletin->input->clean_gpc('r', 'shoutid', TYPE_INT);
		
		if (!$exists = self::$vbulletin->db->query_first_slave("
			SELECT userid, message
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
			WHERE shoutid = " . intval($shoutid)
		))
		{
			// The shout doesn't exist
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_invalid_shout'];
			break;
		}
		
		if ($exists['userid'] == self::$vbulletin->userinfo['userid'] AND !self::$instance['permissions_parsed']['caneditown'])
		{
			// We can't edit our own shouts
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_may_not_edit_own'];
			break;
		}
		
		if ($exists['userid'] != self::$vbulletin->userinfo['userid'] AND !self::$instance['permissions_parsed']['caneditothers'])
		{
			// We don't have permission to edit others' shouts
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_may_not_edit_others'];
			break;
		}					
		
		// Set the editor content
		self::$fetched['editor'] = $exists['message'];
	}
	
	if ($type == 'activeusers')
	{
		// Array of all active users
		self::fetch_active_users(true, true);
		
		// Finally set the content
		self::$fetched['content'] = (count(self::$activeusers) ? implode(', ', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
		
		// Query for active users
		self::$fetched['activeusers']['count'] = count(self::$activeusers);
		
		if (self::$instance['options']['separate_activeusers'])
		{
			// Array of all active users
			self::$fetched['activeusers2'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
		}
	}
}
while (false);
?>