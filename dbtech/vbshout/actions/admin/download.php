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
if ($_REQUEST['action'] == 'download' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_vbshout_download_archive']);
	
	$instances = array();
	$instances[0] = $vbphrase['dbtech_vbshout_all_instances'];
	foreach (VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		// Store the instance
		$instances["$instanceid"] = $instance['name'];
	}	
	asort($instances);
	
	print_form_header('vbshout', 'download');
	construct_hidden_code('action', 'file');
	print_table_header($vbphrase['dbtech_vbshout_download_archive']);
	print_select_row($vbphrase['dbtech_vbshout_file_format'], 'format', array('csv' => 'CSV', 'xml' => 'XML', 'txt' => 'TXT'), 'txt');
	print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
	print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
	print_yes_no_row($vbphrase['dbtech_vbshout_include_bbcode'], 'bbcode', 1);
	print_select_row($vbphrase['dbtech_vbshout_instance'], 'instanceid', $instances, 0);
	print_submit_row($vbphrase['dbtech_vbshout_download_archive'], 0);
}

// #############################################################################
if ($_POST['action'] == 'file')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'startdate'  => TYPE_UNIXTIME,
		'enddate'    => TYPE_UNIXTIME,
		'instanceid' => TYPE_STR,
		'format'     => TYPE_STR,
		'bbcode'	 => TYPE_BOOL
	));
	
	$sqlconds = array();
	$hook_query_fields = $hook_query_joins = '';
	
	if ($vbulletin->GPC['startdate'])
	{
		$sqlconds[] = "vbshout.dateline >= " . $vbulletin->GPC['startdate'];
	}
	
	if ($vbulletin->GPC['enddate'])
	{
		$sqlconds[] = "vbshout.dateline <= " . $vbulletin->GPC['enddate'];
	}
	
	if ($vbulletin->GPC['instanceid'])
	{
		$sqlconds[] = "vbshout.instanceid = " . $vbulletin->GPC['instanceid'];
	}
	
	//($hook = vBulletinHook::fetch_hook('admin_modlogviewer_query')) ? eval($hook) : false;
	
	$logs = $db->query_read("
		SELECT vbshout.*, user.username
			$hook_query_fields
		FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = vbshout.userid)
		$hook_join_fields
		" . (!empty($sqlconds) ? "WHERE " . implode("\r\n\tAND ", $sqlconds) : "") . "
		ORDER BY instanceid ASC, dateline ASC
	");
	
	if (!$db->num_rows($logs))
	{
		print_cp_header($vbphrase['dbtech_vbshout_command_log']);	
		print_stop_message('no_results_matched_your_query');
		print_cp_footer();	
	}
	
	switch ($vbulletin->GPC['format'])
	{
		case 'txt':
		case 'csv':
			$output = '';
			break;
			
		case 'xml':
			require_once(DIR . '/includes/class_xml.php');
			$xml = new vB_XML_Builder($vbulletin);
			$xml->add_group('archive');
			break;
	}
	
	$lastinstanceid = -1;
	while ($log = $db->fetch_array($logs))
	{
		if ($log['userid'] == -1)
		{
			// System user
			$log['username'] = $vbphrase['dbtech_vbshout_system'];
		}
		
		if ($log['type'] == VBSHOUT::$shouttypes['pm'])
		{
			// Add PM flag
			$log['username'] .= ' (' . $vbphrase['dbtech_vbshout_pm'] . ')';
		}
		
		// Strip bbcode if needed
		$log['message'] = (!$vbulletin->GPC['bbcode'] ? strip_bbcode($log['message']) : $log['message']);
		
		$time = vbdate($vbulletin->options['dateformat'], 	$log['dateline'], $vbulletin->options['yestoday']) . ' ' .
				vbdate($vbulletin->options['timeformat'], 	$log['dateline'], $vbulletin->options['yestoday']);
		
		switch ($vbulletin->GPC['format'])
		{
			case 'txt':
				if ($log['instanceid'] != $lastinstanceid)
				{
					$lastinstanceid = $log['instanceid'];
					$output .= "\t\t" . (VBSHOUT::$cache['instance']["$log[instanceid]"]['name'] ? VBSHOUT::$cache['instance']["$log[instanceid]"]['name'] : 'N/A') . "\n";
				}
				$output .= "[$time] $log[username]: $log[message]\n";
				break;
			
			case 'csv':
				$output .= (VBSHOUT::$cache['instance']["$log[instanceid]"]['name'] ? VBSHOUT::$cache['instance']["$log[instanceid]"]['name'] : 'N/A') . "\t$time\t$log[username]\t$log[message]\n";
				break;
			
			case 'xml':
				$xml->add_group('shout');
					$xml->add_tag('instance', (VBSHOUT::$cache['instance']["$log[instanceid]"]['name'] ? VBSHOUT::$cache['instance']["$log[instanceid]"]['name'] : 'N/A'));
					$xml->add_tag('timestamp', $time);
					$xml->add_tag('username', $log['username']);
					$xml->add_tag('message', $log['message']);
				$xml->close_group();
				break;
		}
	}
	
	switch ($vbulletin->GPC['format'])
	{
		case 'txt':
			$mimetype = 'text/plain';
			$extension = 'txt';
			break;
			
		case 'csv':
			$mimetype = 'text/csv';
			$extension = 'csv';
			break;
			
		case 'xml':
			$mimetype = 'text/xml';
			$extension = 'xml';
		
			$xml->close_group();
			
			$output = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";
			$output .= $xml->output();
			
			$xml = null;
			break;
	}
	
	require_once(DIR . '/includes/functions_file.php');
	file_download($output, 'shoutbox-archive.' . $extension, $mimetype);
}

print_cp_footer();