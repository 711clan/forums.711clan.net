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
if ($_REQUEST['action'] == 'banned' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_vbshout_ban_management']);
	
	$users = $db->query_read("
		SELECT
			commandlog.comment,
			commandlog.userid AS banneruserid,	
			commandlog.dateline,
			commandlog.username AS cmdusername,
			banneduser.userid,
			banneduser.dbtech_vbshout_shouts AS shouts,
			banneduser.username,
			bannedusergroup.title,		
			banneruser.username AS bannerusername,
			bannerusergroup.title AS bannertitle		
		FROM " . TABLE_PREFIX . "dbtech_vbshout_log AS commandlog
		LEFT JOIN " . TABLE_PREFIX . "user AS banneduser ON(banneduser.userid = commandlog.comment)
		LEFT JOIN " . TABLE_PREFIX . "user AS banneruser ON(banneruser.userid = commandlog.userid)
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS bannedusergroup ON(bannedusergroup.usergroupid = banneduser.usergroupid)
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS bannerusergroup ON(bannerusergroup.usergroupid = banneruser.usergroupid)
		WHERE commandlog.command = 'ban'
			AND banneduser.dbtech_vbshout_banned = 1
		ORDER BY banneduser.username ASC
	");
	
	if ($numusers = $db->num_rows($users))
	{
		// Begin ugly hack
		$loguser = array();
		while ($user = $db->fetch_array($users))
		{
			if (!$loguser["$user[userid]"] OR $loguser["$user[userid]"]['dateline'] < $user['dateline'])
			{
				// Overwrite array as needed
				$loguser["$user[userid]"] = $user;
			}
		}
		$numusers = count($loguser);
		// End ugly hack
		
		print_form_header('vbshout', 'banned');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['showing_users_x_to_y_of_z'], 1, $numusers, $numusers), 6);
		print_cells_row(array(
			$vbphrase['userid'],
			$vbphrase['username'],
			$vbphrase['banned_by'],
			$vbphrase['banned_on'],
			$vbphrase['dbtech_vbshout_shout_count'],
			'<input type="checkbox" name="allbox" onclick="js_check_all(this.form)" title="' . $vbphrase['check_all'] . '" />'
		), 1);
	
		foreach ($loguser as $user)
		{
			$cell = array();
			$cell[] = $user['userid'];
			$cell[] = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$user[userid]\" target=\"_blank\">$user[username]</a><br /><span class=\"smallfont\">$user[title]</span>";
			$cell[] = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$user[banneruserid]\" target=\"_blank\">$user[bannerusername]</a><br /><span class=\"smallfont\">$user[bannertitle]</span>";
			$cell[] = vbdate($vbulletin->options['dateformat'], $user['dateline']);
			$cell[] = vb_number_format($user['shouts']);
			$cell[] = "<input type=\"checkbox\" name=\"users[$user[userid]]\" value=\"1\" tabindex=\"1\" />";
			print_cells_row($cell);
		}
		print_submit_row($vbphrase['lift_ban'], false, 6);
	}
	else
	{
		print_stop_message('no_users_matched_your_query');
	}
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Fetch the list of users
	$users = $vbulletin->input->clean_gpc('p', 'users', TYPE_ARRAY_UINT);
	
	if (!count($users))
	{
		// We weren't unbanning anything
		print_stop_message('nothing_to_do');
	}
	
	// Grab all the userids
	$users = array_keys($users);
	
	// Unban all the users
	$db->query_write("
		UPDATE " . TABLE_PREFIX . "user
		SET dbtech_vbshout_banned = 0
		WHERE userid IN(" . implode(',', $users) . ")
	");
	
	foreach ($users as $userid)
	{
		// Log the unbanning
		VBSHOUT::log_command('unban', $userid);
	}
	
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		// We've changed shit
		VBSHOUT::set_aop('shouts', $instanceid, false);
		VBSHOUT::set_aop('shoutnotifs', $instanceid, false, true);
	}
	
	define('CP_REDIRECT', 'vbshout.php?do=banned');
	print_stop_message('dbtech_vbshout_users_unbanned');
}

print_cp_footer();