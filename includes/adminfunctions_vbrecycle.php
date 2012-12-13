<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBRecycle 3.0.x								    # ||
|| # ---------------------------------------------------------------- # ||
|| # Author: LNTT - Email: toai007@yahoo.com    		          # ||
|| # Website & Demo: http://www.FanFunVN.com/   		          # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);

// #############################################################################
function print_move_prune_rows()
{
	global $vbphrase;
	print_description_row($vbphrase['date_options'], 0, 2, 'thead', 'center');
		print_input_row($vbphrase['original_post_date_is_at_least_xx_days_ago'], 'thread[originaldaysolder]', 0, 1, 5);
		print_input_row($vbphrase['original_post_date_is_at_most_xx_days_ago'] . '<dfn>' . construct_phrase($vbphrase['note_leave_x_specify_no_limit'], '0') . '</dfn>', 'thread[originaldaysnewer]', 0, 1, 5);
		print_input_row($vbphrase['last_post_date_is_at_least_xx_days_ago'], 'thread[lastdaysolder]', 0, 1, 5);
		print_input_row($vbphrase['last_post_date_is_at_most_xx_days_ago'] . '<dfn>' . construct_phrase($vbphrase['note_leave_x_specify_no_limit'], '0') . '</dfn>', 'thread[lastdaysnewer]', 0, 1, 5);

	print_description_row($vbphrase['view_options'], 0, 2, 'thead', 'center');
		print_input_row($vbphrase['thread_has_at_least_xx_replies'], 'thread[repliesleast]', 0, 1, 5);
		print_input_row($vbphrase['thread_has_at_most_xx_replies'] . '<dfn>' . construct_phrase($vbphrase['note_leave_x_specify_no_limit'], '-1') . '</dfn>', 'thread[repliesmost]', -1, 1, 5);
		print_input_row($vbphrase['thread_has_at_least_xx_views'], 'thread[viewsleast]', 0, 1, 5);
		print_input_row($vbphrase['thread_has_at_most_xx_views'] . '<dfn>' . construct_phrase($vbphrase['note_leave_x_specify_no_limit'], '-1') . '</dfn>', 'thread[viewsmost]', -1, 1, 5);

	print_description_row($vbphrase['status_options'], 0, 2, 'thead', 'center');
		print_yes_no_other_row($vbphrase['thread_is_sticky'], 'thread[issticky]', $vbphrase['either'], 0);

		$state = array(
			'visible' => $vbphrase['visible'],
			'moderation' => $vbphrase['awaiting_moderation'],
			'deleted' => $vbphrase['deleted'],
			'any' => $vbphrase['any']
		);
		print_radio_row($vbphrase['thread_state'], 'thread[state]', $state, 'any');

		$status = array(
			'open' => $vbphrase['thread_open'],
			'closed' => $vbphrase['thread_closed'],
			'redirect' => $vbphrase['redirect'],
			'not_redirect' => $vbphrase['not_redirect'],
			'any' => $vbphrase['any']
		);
		print_radio_row($vbphrase['thread_status'], 'thread[status]', $status, 'not_redirect');

	print_description_row($vbphrase['other_options'], 0, 2, 'thead', 'center');
		print_input_row($vbphrase['username'], 'thread[posteduser]');
		print_input_row($vbphrase['title'], 'thread[titlecontains]');
		print_forum_chooser($vbphrase['forum'], 'thread[forumid]', -1, $vbphrase['all_forums'], true);
		print_yes_no_row($vbphrase['include_child_forums'], 'thread[subforums]');
}

// #############################################################################
function fetch_thread_move_prune_sql($thread)
{
	global $db, $vbphrase;

	$query = '1=1';

	// original post
	if (intval($thread['originaldaysolder']))
	{
		$query .= ' AND thread.dateline <= ' . (TIMENOW - ($thread['originaldaysolder'] * 86400));
	}
	if (intval($thread['originaldaysnewer']))
	{
		$query .= ' AND thread.dateline >= ' . (TIMENOW - ($thread['originaldaysnewer'] * 86400));
	}

	// last post
	if (intval($thread['lastdaysolder']))
	{
		$query .= ' AND thread.lastpost <= ' . (TIMENOW - ($thread['lastdaysolder'] * 86400));
	}
	if (intval($thread['lastdaysnewer']))
	{
		$query .= ' AND thread.lastpost >= ' . (TIMENOW - ($thread['lastdaysnewer'] * 86400));
	}

	// replies
	if (intval($thread['repliesleast']) > 0)
	{
		$query .= ' AND thread.replycount >= ' . intval($thread['repliesleast']);
	}
	if (intval($thread['repliesmost']) > -1)
	{
		$query .= ' AND thread.replycount <= ' . intval($thread['repliesmost']);
	}

	// views
	if (intval($thread['viewsleast']) > 0)
	{
		$query .= ' AND thread.views >= ' . intval($thread['viewsleast']);
	}
	if (intval($thread['viewsmost']) > -1)
	{
		$query .= ' AND thread.views <= ' . intval($thread['viewsmost']);
	}

	// sticky
	if ($thread['issticky'] == 1)
	{
		$query .= ' AND thread.sticky = 1';
	}
	else if ($thread['issticky'] == 0)

	{
		$query .= ' AND thread.sticky = 0';
	}

	// state
	switch ($thread['state'])
	{
		case 'visible':
			$query .= ' AND thread.visible = 1';
			break;

		case 'moderation':
			$query .= ' AND thread.visible = 0';
			break;

		case 'deleted':
			$query .= ' AND thread.visible = 2';
			break;
	}

	//status
	switch ($thread['status'])
	{
		case 'open':
			$query .= ' AND thread.open = 1';
			break;

		case 'closed':
			$query .= ' AND thread.open = 0';
			break;

		case 'redirect':
			$query .= ' AND thread.open = 10';
			break;

		case 'not_redirect':
			$query .= ' AND thread.open <> 10';
			break;
	}

	// posted by
	if ($thread['posteduser'])
	{
		$user = $db->query_first("
			SELECT userid
			FROM " . TABLE_PREFIX . "user
			WHERE username = '" . $db->escape_string(htmlspecialchars_uni($thread['posteduser'])) . "'
		");
		if (!$user)
		{
			print_stop_message('invalid_username_specified');
		}
		$query .= " AND thread.postuserid = $user[userid]";
	}

	// title contains
	if ($thread['titlecontains'])
	{
		$query .= " AND thread.title LIKE '%" . $db->escape_string_like(htmlspecialchars_uni($thread['titlecontains'])) . "%'";
	}

	// forum
	if ($thread['forumid'] != -1)
	{
		if ($thread['subforums'])
		{
			$query .= " AND (thread.forumid = $thread[forumid] OR forum.parentlist LIKE '%,$thread[forumid],%')";
		}
		else
		{
			$query .= " AND thread.forumid = $thread[forumid]";
		}
	}

	return $query;
}

// #############################################################################
function cache_styles($getids = false, $styleid = -1, $depth = 0)
{
	global $vbulletin, $stylecache, $count;
	static $i, $cache;

	// check to see if we have already got the results from the database
	if (empty($cache))
	{
		$styles = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "style ORDER BY displayorder");
		define('STYLECOUNT', $vbulletin->db->num_rows($styles));
		while ($style = $vbulletin->db->fetch_array($styles))
		{
			$cache["$style[parentid]"]["$style[displayorder]"]["$style[styleid]"] = $style;
		}
	}

	// database has already been queried
	if (is_array($cache["$styleid"]))
	{
		foreach ($cache["$styleid"] AS $holder)
		{
			foreach ($holder AS $style)
			{
				$stylecache["$style[styleid]"] = $style;
				$stylecache["$style[styleid]"]['depth'] = $depth;
				cache_styles($getids, $style['styleid'], $depth + 1);

			} // end foreach ($holder AS $style)
		} // end foreach ($tcache["$styleid"] AS $holder)
	} // end if (found $tcache["$styleid"])

}

// #############################################################################
function print_style_chooser_row($name = 'parentid', $selectedid = -1, $topname = NULL, $title = NULL, $displaytop = true)
{
	global $stylecache, $vbphrase;

	if ($topname === NULL)
	{
	   $topname = $vbphrase['no_parent_style'];
	}
	if ($title === NULL)
	{
	   $title = $vbphrase['parent_style'];
	}

	cache_styles();

	$styles = array();

	if ($displaytop)
	{
		$styles['-1'] = $topname;
	}

	foreach($stylecache AS $style)
	{
		$styles["$style[styleid]"] = construct_depth_mark($style['depth'], '--', iif($displaytop, '--')) . " $style[title]";
	}

	print_select_row($title, $name, $styles, $selectedid);
}


/*======================================================================*\
|| #################################################################### ||
|| # End vBRecycle 3.0.x {adminfunctions_vbrecycle.php}		    # ||
|| #################################################################### ||
\*======================================================================*/
?>