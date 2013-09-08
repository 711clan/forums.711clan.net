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
if ($_REQUEST['action'] == 'commandlog' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_vbshout_command_log']);
	
	// ###################### Start modify #######################
	$users = $db->query_read("
		SELECT DISTINCT commandlog.userid, IFNULL(user.username, commandlog.username) AS username
		FROM " . TABLE_PREFIX . "dbtech_vbshout_log AS commandlog
		LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
		ORDER BY username
	");
	$userlist = array('no_value' => $vbphrase['all_log_entries']);
	while ($user = $db->fetch_array($users))
	{
		$userlist[$user['userid']] = $user['username'];
	}
	
	print_form_header('vbshout', 'commandlog');
	construct_hidden_code('action', 'view');
	print_table_header($vbphrase['dbtech_vbshout_command_log_viewer']);
	print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_select_row($vbphrase['show_only_entries_generated_by'], 'userid', $userlist);
	print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
	print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
	print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username']), 'date');
	print_submit_row($vbphrase['view'], 0);
	
	print_form_header('vbshout', 'commandlog');
	construct_hidden_code('action', 'prune');
	print_table_header($vbphrase['prune_log_entries']);
	print_yes_no_row(construct_phrase($vbphrase['are_you_sure_you_want_to_prune_x_log_entries_from_moderator_log'], ''), 'doprune', 0);
	print_submit_row($vbphrase['prune_log_entries'], 0);	
}

// #############################################################################
if ($_POST['action'] == 'prune')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'doprune'    => TYPE_BOOL,
	));
	
	if (!$vbulletin->GPC['doprune'])
	{
		print_stop_message('nothing_to_do');
	}
	
	$db->query_first("TRUNCATE TABLE " . TABLE_PREFIX . "dbtech_vbshout_log");
	
	define('CP_REDIRECT', 'vbshout.php?do=commandlog');
	print_stop_message('pruned_moderator_log_successfully');	
}

// #############################################################################
if ($_REQUEST['action'] == 'view')
{
	print_cp_header($vbphrase['dbtech_vbshout_command_log']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'    => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'userid'     => TYPE_UINT,
		'modaction'  => TYPE_STR,
		'orderby'    => TYPE_NOHTML,
		'product'    => TYPE_STR,
		'startdate'  => TYPE_UNIXTIME,
		'enddate'    => TYPE_UNIXTIME,
	));
	
	$sqlconds = array();
	$hook_query_fields = $hook_query_joins = '';
	
	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}
	
	if ($vbulletin->GPC['userid'] OR $vbulletin->GPC['modaction'])
	{
		if ($vbulletin->GPC['userid'])
		{
			$sqlconds[] = "commandlog.userid = " . $vbulletin->GPC['userid'];
		}
		if ($vbulletin->GPC['modaction'])
		{
			$sqlconds[] = "commandlog.command LIKE '%" . $db->escape_string_like($vbulletin->GPC['modaction']) . "%'";
		}
	}
	
	if ($vbulletin->GPC['startdate'])
	{
		$sqlconds[] = "commandlog.dateline >= " . $vbulletin->GPC['startdate'];
	}
	
	if ($vbulletin->GPC['enddate'])
	{
		$sqlconds[] = "commandlog.dateline <= " . $vbulletin->GPC['enddate'];
	}
	
	//($hook = vBulletinHook::fetch_hook('admin_modlogviewer_query')) ? eval($hook) : false;
	
	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "dbtech_vbshout_log AS commandlog
		" . (!empty($sqlconds) ? "WHERE " . implode("\r\n\tAND ", $sqlconds) : "") . "
	");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);
	
	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];
	
	switch($vbulletin->GPC['orderby'])
	{
		case 'user':
			$order = 'user.username ASC, dateline DESC';
			break;
		case 'modaction':
			$order = 'command ASC, dateline DESC';
			break;
		case 'date':
		default:
			$order = 'dateline DESC';
	}
	
	$logusers = array();
	$users = $db->query_read("
		SELECT user.userid, user.username
		FROM " . TABLE_PREFIX . "dbtech_vbshout_log AS commandlog
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = commandlog.comment)
		WHERE command IN('ban', 'unban', 'pruneuser', 'resetshoutsuser', 'silence', 'unsilence')
	");
	while ($user = $db->fetch_array($users))
	{
		// Cache the users mentioned in the command
		$logusers["$user[userid]"] = ($user['username'] ? $user['username'] : 'N/A');
	}
	
	$logs = $db->query_read("
		SELECT commandlog.*, commandlog.username AS cmdusername, user.username
			$hook_query_fields
		FROM " . TABLE_PREFIX . "dbtech_vbshout_log AS commandlog
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = commandlog.userid)
		$hook_join_fields
		" . (!empty($sqlconds) ? "WHERE " . implode("\r\n\tAND ", $sqlconds) : "") . "
		ORDER BY $order
		LIMIT $startat, " . $vbulletin->GPC['perpage'] . "
	");
	
	if ($db->num_rows($logs))
	{
		$vbulletin->GPC['modaction'] = htmlspecialchars_uni($vbulletin->GPC['modaction']);
	
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] . "\" tabindex=\"1\" onclick=\"window.location='vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] . "\" tabindex=\"1\" onclick=\"window.location='vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$prv'\">";
		}
	
		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] . " &gt;\" tabindex=\"1\" onclick=\"window.location='vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] . " &raquo;\" tabindex=\"1\" onclick=\"window.location='vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$totalpages'\">";
		}
		
		$headings = array();
		$headings[] = $vbphrase['id'];
		$headings[] = "<a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=user&page=" . $vbulletin->GPC['pagenumber'] . "\">" . str_replace(' ', '&nbsp;', $vbphrase['username']) . "</a>";
		$headings[] = "<a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=date&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['date'] . "</a>";
		$headings[] = "<a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog&action=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=modaction&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['action'] . "</a>";
		$headings[] = $vbphrase['info'];
		$headings[] = str_replace(' ', '&nbsp;', $vbphrase['ip_address']);		
	
		print_form_header('', '');
		print_description_row(construct_link_code($vbphrase['restart'], "vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=commandlog"), false, count($headings), 'thead', 'right');
		print_table_header(construct_phrase($vbphrase['dbtech_vbshout_command_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), count($headings));
		print_cells_row($headings, 1);
	
		while ($log = $db->fetch_array($logs))
		{
			// Ensure we got the proper log username
			//$log['username'] = ($log['username'] ? $log['username'] : ($log['cmdusername'] ? $log['cmdusername'] : 'N/A'));
			
			$cell = array();
			$cell[] = $log['logid'];
			if ($log['username'])
			{
				// This user still exists
				$cell[] = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$log[userid]\"><b>$log[username]</b></a>";
			}
			else
			{
				// User has been deleted
				$cell[] = ($log['cmdusername'] ? $log['cmdusername'] : 'N/A');
			}
			$cell[] = '<span class="smallfont">' . vbdate($vbulletin->options['logdateformat'], $log['dateline']) . '</span>';
			$cell[] = $vbphrase["dbtech_vbshout_logcommand_$log[command]"];
	
			//($hook = vBulletinHook::fetch_hook('admin_modlogviewer_query_loop')) ? eval($hook) : false;
			
			$celldata = '';
			switch ($log['command'])
			{
				case 'ban':
				case 'unban':
					$celldata = construct_phrase($vbphrase["dbtech_vbshout_log_$log[command]"], $logusers["$log[comment]"]);
					break;
					
				case 'shoutedit':
					$shouts = unserialize($log['comment']);
					$celldata = construct_phrase($vbphrase["dbtech_vbshout_log_$log[command]"], $shouts['old'], $shouts['new']);
					break;
				
				case 'shoutdelete':
					$celldata = construct_phrase($vbphrase["dbtech_vbshout_log_$log[command]"], $log['comment']);
					break;
			}
			
			

			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_admin_cmdlogviewer_query_loop')) ? eval($hook) : false;
	
			$cell[] = $celldata;
			$cell[] = '<span class="smallfont">' . iif($log['ipaddress'], "<a href=\"usertools.php?" . $vbulletin->session->vars['sessionurl'] . "do=gethost&ip=$log[ipaddress]\">$log[ipaddress]</a>", '&nbsp;') . '</span>';
	
			print_cells_row($cell, 0, 0, -4);
		}
	
		print_table_footer(count($headings), "$firstpage $prevpage &nbsp; $nextpage $lastpage");
	}
	else
	{
		print_stop_message('no_results_matched_your_query');
	}
}

print_cp_footer();