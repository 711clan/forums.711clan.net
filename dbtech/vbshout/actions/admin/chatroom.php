<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################
if ($_REQUEST['action'] == 'chatroom' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_vbshout_chatroom_management']);
	
	// Table header
	$headings = array();
	$headings[] = $vbphrase['title'];
	$headings[] = $vbphrase['usergroups'];
	$headings[] = $vbphrase['dbtech_vbshout_instance'];
	$headings[] = $vbphrase['active'];
	$headings[] = $vbphrase['edit'];
	
	
	if (count(VBSHOUT::$cache['chatroom']))
	{
		print_form_header('vbshout', 'chatroom');	
		construct_hidden_code('action', 'modify');	
		print_table_header($vbphrase['dbtech_vbshout_chatroom_management'], count($headings));
		print_description_row($vbphrase['dbtech_vbshout_chatroom_management_descr'], false, count($headings));	
		print_cells_row($headings, 0, 'thead');
		
		foreach (VBSHOUT::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['membergroupids'])
			{
				// This is an on-the-fly chatroom
				continue;
			}
			
			$usergroups = array();
			foreach(explode(',', $chatroom['membergroupids']) as $usergroupid)
			{
				// Usergroup cache
				$usergroups[] = $vbulletin->usergroupcache["$usergroupid"]['title'];
			}
			
			// Table data
			$cell = array();
			$cell[] = $chatroom['title'];
			$cell[] = implode(', ', $usergroups);
			$cell[] = ($chatroom['instanceid'] ? VBSHOUT::$cache['instance'][$chatroom['instanceid']]['name'] : $vbphrase['dbtech_vbshout_all_instances']);
			$cell[] = ($chatroom['active'] ? $vbphrase['yes'] : '<span class="col-i"><strong>' . $vbphrase['no'] . '</strong></span>');
			$cell[] = construct_link_code($vbphrase['edit'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=chatroom&amp;action=modify&amp;chatroomid=' . $chatroomid);
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		print_submit_row($vbphrase['dbtech_vbshout_add_new_chatroom'], false, count($headings));	
	}
	else
	{
		print_form_header('vbshout', 'chatroom');	
		construct_hidden_code('action', 'modify');	
		print_table_header($vbphrase['dbtech_vbshout_chatroom_management'], count($headings));
		print_description_row($vbphrase['dbtech_vbshout_no_chatrooms'], false, count($headings));
		print_submit_row($vbphrase['dbtech_vbshout_add_new_chatroom'], false, count($headings));	
	}
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$instances = array();
	$instances[0] = $vbphrase['dbtech_vbshout_all_instances'];
	if (VBSHOUT::$isPro)
	{
		foreach (VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			// Store the instance
			$instances["$instanceid"] = $instance['name'];
		}	
	}
	asort($instances);
	
	$chatroomid = $vbulletin->input->clean_gpc('r', 'chatroomid', TYPE_UINT);
	$chatroom = ($chatroomid ? VBSHOUT::$cache['chatroom']["$chatroomid"] : false);
	
	if (!is_array($chatroom))
	{
		// Non-existing chatroom
		$chatroomid = 0;
	}
	
	if ($chatroomid)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_chatroom'], $chatroom['title'])));
		print_form_header('vbshout', 'chatroom');
		construct_hidden_code('action', 'update');
		construct_hidden_code('chatroomid', $chatroomid);
		print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_chatroom'], $chatroom['title']));
	}
	else
	{
		// Add
		print_cp_header($vbphrase['dbtech_vbshout_add_new_chatroom']);
		print_form_header('vbshout', 'chatroom');
		construct_hidden_code('action', 'update');
		construct_hidden_code('chatroom[creator]', $vbulletin->userinfo['userid']);
		print_table_header($vbphrase['dbtech_vbshout_add_new_chatroom']);
	}
	
	print_input_row($vbphrase['title'], 					'chatroom[title]', 					$chatroom['title']);
	print_yes_no_row($vbphrase['active'],					'chatroom[active]',					$chatroom['active']);
	print_membergroup_row($vbphrase['usergroups'], 			'chatroom[membergroupids]', 2, 		$chatroom);
	print_select_row($vbphrase['dbtech_vbshout_instance'], 	'chatroom[instanceid]', $instances,	$chatroom['instanceid']);
	
	print_submit_row(($chatroomid ? $vbphrase['save'] : $vbphrase['dbtech_vbshout_add_new_chatroom']));
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'chatroomid' 		=> TYPE_UINT,
		'chatroom' 			=> TYPE_ARRAY,
	));
	
	// init data manager
	$dm =& VBSHOUT::initDataManager('Chatroom', $vbulletin, ERRTYPE_CP);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['chatroomid'])
	{
		if (!$existing = VBSHOUT::$cache['chatroom']["{$vbulletin->GPC[chatroomid]}"])
		{
			// Couldn't find the chatroom
			print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_chatroom'], $vbulletin->GPC['chatroomid']);
		}
		
		// Set existing
		$dm->set_existing($existing);
		
		// Added
		$phrase = $vbphrase['dbtech_vbshout_edited'];
	}
	else
	{
		// Added
		$phrase = $vbphrase['dbtech_vbshout_added'];
	}
	
	// chatroom fields
	foreach ($vbulletin->GPC['chatroom'] AS $key => $val)
	{
		if (!$vbulletin->GPC['chatroomid'] OR $existing["$key"] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}
	
	// Save! Hopefully.
	$dm->save();
	
	define('CP_REDIRECT', 'vbshout.php?do=chatroom');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_chatroom'], $phrase);	
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'chatroomid', TYPE_UINT);
	
	print_cp_header(construct_phrase($vbphrase['dbtech_vbshout_delete_x'], $vbphrase['dbtech_vbshout_chatroom']));
	print_delete_confirmation('dbtech_vbshout_chatroom', $vbulletin->GPC['chatroomid'], 'vbshout', 'chatroom', 'dbtech_vbshout_chatroom', array('action' => 'kill'), '', 'title');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'chatroomid' => TYPE_UINT,
		'kill' 		 => TYPE_BOOL
	));
	
	if (!$existing = VBSHOUT::$cache['chatroom']["{$vbulletin->GPC[chatroomid]}"])
	{
		// Couldn't find the chatroom
		print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_chatroom'], $vbulletin->GPC['chatroomid']);
	}
	
	// init data manager
	$dm =& VBSHOUT::initDataManager('Chatroom', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'vbshout.php?do=chatroom');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_chatroom'], $vbphrase['dbtech_vbshout_deleted']);	
}

print_cp_footer();