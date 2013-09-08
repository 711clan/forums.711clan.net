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
if ($_REQUEST['action'] == 'notifications' OR empty($_REQUEST['action']))
{
	$forumids = array();
	foreach ((array)$vbulletin->forumcache as $forumid => $forum)
	{
		if (!VBSHOUT::$isPro AND $forum['parentid'] != -1)
		{
			// This forum isn't a parent forum
			continue;
		}
		
		$forumids[] = $forumid;
	}
	
	
	$headings = array();
	$headings[] = $vbphrase['forum'];
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		$headings[] = $instance['name'];
	}
	
	print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['permissions'], $vbphrase['dbtech_vbshout_instance'])));
	print_form_header('vbshout', 'notifications');
	construct_hidden_code('action', 'update');
	print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['permissions'], $vbphrase['dbtech_vbshout_instance']), count($headings));
	print_cells_row($headings, 0, 'thead');
	
	foreach ((array)$forumids as $forumid)
	{
		// Shorthand
		$forum = $vbulletin->forumcache["$forumid"];
		$cell = array();
		$cell[] = construct_depth_mark($forum['depth'],'- - ') . $forum['title'];
		foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			$cell[] = '
				<center>
					<input type="hidden" name="forum[' . $instanceid . '][' . $forumid . '][newthread]" value="0" />
					<label for="cb_forum_' . $instanceid . '_' . $forumid . '_newthread">
						<input type="checkbox" name="forum[' . $instanceid . '][' . $forumid . '][newthread]" id="cb_forum_' . $instanceid . '_' . $forumid . '_newthread" value="1"' . (($instance['notices']["$forumid"] & 1) ? ' checked="checked"' : '') . '/>
						' . $vbphrase['dbtech_vbshout_new_thread'] . '
					</label>
					
					<input type="hidden" name="forum[' . $instanceid . '][' . $forumid . '][newreply]" value="0" />
					<label for="cb_forum_' . $instanceid . '_' . $forumid . '_newreply">
						<input type="checkbox" name="forum[' . $instanceid . '][' . $forumid . '][newreply]" id="cb_forum_' . $instanceid . '_' . $forumid . '_newreply" value="2"' . (($instance['notices']["$forumid"] & 2) ? ' checked="checked"' : '') . '/>
						' . $vbphrase['dbtech_vbshout_new_reply'] . '
					</label>
				</center>
			';
		}
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);	
	}
	print_submit_row($vbphrase['save'], false, count($headings));
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'forum' 	=> TYPE_ARRAY,
	));
	
	foreach ($vbulletin->GPC['forum'] as $instanceid => $forum)
	{
		if (!$existing = VBSHOUT::$cache['instance'][$instanceid])
		{
			// Invalid isntance
			continue;
		}
		
		$SQL = array();	
		foreach ($forum as $forumid => $config)
		{
			foreach ($config as $val)
			{
				// Notice flag
				$SQL[$forumid] += $val;
			}
		}
		
		// init data manager
		$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
			$dm->set_existing($existing);
			$dm->set('notices', $SQL);
		$dm->save();
		unset($dm);
	}
	
	define('CP_REDIRECT', 'vbshout.php?do=notifications');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_permissions'], $vbphrase['dbtech_vbshout_edited']);
}

print_cp_footer();