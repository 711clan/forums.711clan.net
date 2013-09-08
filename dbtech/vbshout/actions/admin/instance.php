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

	
// Store the bitfield defs we need
$bitfields = array(
	'logging' 			=> 'nocache|dbtech_vbshout_commands',
	'editors' 			=> 'nocache|dbtech_vbshout_editors',
	'notices' 			=> 'nocache|dbtech_vbshout_notices',
	'shoutboxtabs' 		=> 'nocache|dbtech_vbshout_shoutboxtabs',
	
);

// #############################################################################
if ($_REQUEST['action'] == 'instance' OR empty($_REQUEST['action']))
{
	$displays = array(
		0 => $vbphrase['disabled'],
		1 => $vbphrase['dbtech_vbshout_after_navbar'],
		2 => $vbphrase['dbtech_vbshout_above_footer']
	);
	
	print_cp_header($vbphrase['dbtech_vbshout_instance_management']);
	
	print_form_header('', '');
	print_table_header($vbphrase['dbtech_vbshout_additional_functions']);
	print_description_row("<b>
		<a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=instance&amp;action=permissions\">" . $vbphrase['dbtech_vbshout_view_instance_permissions'] . "</a>
		" . (VBSHOUT::$isPro ? "| <a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=instance&amp;action=masspermissions\">" . $vbphrase['dbtech_vbshout_quick_instance_permission_setup'] . "</a>" : '') . "
	</b>", 0, 2, '', 'center');
	print_table_footer();
	
	// Table header
	$headings = array();
	$headings[] = $vbphrase['varname'];
	$headings[] = $vbphrase['title'];
	$headings[] = $vbphrase['description'];
	$headings[] = $vbphrase['active'];
	$headings[] = $vbphrase['display_order'];
	$headings[] = $vbphrase['dbtech_vbshout_sticky'];
	$headings[] = $vbphrase['dbtech_vbshout_auto_display'];
	//$headings[] = $vbphrase['dbtech_vbshout_deployment'];
	//$headings[] = $vbphrase['templates'];
	//$headings[] = $vbphrase['dbtech_vbshout_sounds_shout'];
	//$headings[] = $vbphrase['dbtech_vbshout_sounds_invite'];
	//$headings[] = $vbphrase['dbtech_vbshout_sounds_pm'];
	$headings[] = $vbphrase['edit'];
	$headings[] = $vbphrase['delete'];
	
	if (count(VBSHOUT::$cache['instance']))
	{
		print_form_header('vbshout', 'instance');
		construct_hidden_code('action', 'displayorder');
		print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
		print_description_row($vbphrase['dbtech_vbshout_instance_management_descr'], false, count($headings));	
		
		print_cells_row($headings, 0, 'thead');
		
		foreach (VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			// Table data
			$cell = array();
			$cell[] = $instance['varname'];
			$cell[] = $instance['name'];
			$cell[] = $instance['description'];
			$cell[] = ($instance['active'] ? $vbphrase['yes'] : '<span class="col-i"><strong>' . $vbphrase['no'] . '</strong></span>');
			$cell[] = "<input type=\"text\" class=\"bginput\" name=\"order[$instanceid]\" value=\"$instance[displayorder]\" tabindex=\"1\" size=\"3\" title=\"" . $vbphrase['edit_display_order'] . "\" />";
			$cell[] = $instance['sticky'];
			$cell[] = $displays["$instance[autodisplay]"];
			//$cell[] = $instance['deployment'];
			//$cell[] = $instance['templates'];
			//$cell[] = $instance['shoutsound'];
			//$cell[] = $instance['invitesound'];
			//$cell[] = $instance['pmsound'];
			$cell[] = construct_link_code($vbphrase['edit'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=instance&amp;action=modify&amp;instanceid=' . $instanceid);
			$cell[] = ($instanceid != 1 ? construct_link_code($vbphrase['delete'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=instance&amp;action=delete&amp;instanceid=' . $instanceid) : '[N/A]');
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		print_submit_row($vbphrase['save_display_order'], false, count($headings), false, "<input type=\"button\" id=\"addnew\" class=\"button\" value=\"" . str_pad($vbphrase['dbtech_vbshout_add_new_instance'], 8, ' ', STR_PAD_BOTH) . "\" tabindex=\"1\" onclick=\"window.location = 'vbshout.php?do=instance&amp;action=modify'\" />");	
	}
	else
	{
		print_form_header('vbshout', 'instance');
		construct_hidden_code('action', 'modify');
		print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
		print_description_row($vbphrase['dbtech_vbshout_no_instances'], false, count($headings));
		print_submit_row($vbphrase['dbtech_vbshout_add_new_instance'], false, count($headings));	
	}
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	require_once(DIR . '/dbtech/vbshout/includes/adminfunctions.php');

	$instanceid = $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
	$instance = ($instanceid ? VBSHOUT::$cache['instance']["$instanceid"] : false);
	
	if (!is_array($instance))
	{
		// Non-existing instance
		$instanceid = 0;
	}
	
	$permissions = array();
	$bbcodepermissions = array();
	
	foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
	{
		$bit = 10300;
		$bit2 = 67;
		if ($usergroup['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
		{
			// Admin
			$bit = 61436;
		}
		else if ($usergroup['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator'])
		{
			// SMod
			$bit = 61436;
		}
		else if ($usergroupid == 5)
		{
			$bit = 61308;
		}
		else if (!($usergroup['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']) OR in_array($usergroupid, array(1, 3, 4)))
		{
			// Banned, guest, email conf, COPPA
			$bit 	= 0;
			$bit2 	= 0;
		}
		
		$permissions["$usergroupid"] 		= $bit;		
		$bbcodepermissions["$usergroupid"] 	= $bit2;		
	}
	
	$defaults = array(
		'varname' 			=> 'shoutbox',
		'name' 				=> 'Shoutbox',
		'description' 		=> 'A shoutbox instance.',
		'sticky' 			=> 'The defauly sticky note.',
		'active' 			=> 1,
		'autodisplay'		=> 1,
		'permissions'		=> $permissions,
		'bbcodepermissions'	=> $bbcodepermissions,
		'options'			=> array(),
	);
	
	$displays = array(
		0 => $vbphrase['disabled'],
		1 => $vbphrase['dbtech_vbshout_after_navbar'],
	);
	
	if (intval($vbulletin->versionnumber) > 3)
	{
		$displays[2] = $vbphrase['dbtech_vbshout_above_footer'];
	}
	
	$soundfiles = array('' => $vbphrase['none']);
	$d = dir(DIR . '/dbtech/vbshout/sounds');
	while (false !== ($file = $d->read()))
	{
		if ($file != '.' AND $file != '..' AND $file != 'index.html')
		{
			// Store the icon
			$soundfiles[$file] = $file;
		}
	}
	$d->close();
	
	// Sort the array as a string
	asort($soundfiles, SORT_STRING);
	
	if (extension_loaded('suhosin') AND (ini_get('suhosin.post.max_vars') > 0) AND (ini_get('suhosin.post.max_vars') < 2048))
	{
		$hasSuhosin = '
			You appear to have <strong>Suhosin</strong> installed on your server and configured to be too restrictive for vBulletin and vBShout to work correctly.<br />
			<br />
			Please note that if you have a large number of usergroups, certain parts of this page may not work as intended.<br />
			<br />
			If you encounter this issue, you can work around this by adding the following code to your .htaccess file:<br />
			<br />
<pre>php_flag suhosin.cookie.encrypt Off
php_value suhosin.request.max_vars 2048
php_value suhosin.get.max_vars 2048
php_value suhosin.post.max_vars 2048</pre>
			<br />
			These are the values vBulletin Support Staff suggest.';
	}
	if (version_compare(PHP_VERSION, '5.3.9') >= 0 AND ini_get('max_input_vars') < 10000)
	{
		$hasSuhosin = '
			You appear to have <strong>PHP 5.3.9</strong> or higher installed on your server and configured to be too restrictive for vBulletin and vBShout to work correctly.<br />
			<br />
			Please note that if you have a large number of usergroups, certain parts of this page may not work as intended.<br />
			<br />
			If you encounter this issue, you may be able to work around this by adding the following code to your .htaccess file:<br />
			<br />
<pre>php_value max_input_vars 10000</pre>
			<br />
			or altering your php.ini file to increase max_input_vars to 10000.';
	}	
	
	if ($instanceid)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_instance'], $instance['name'])));
		print_form_header('vbshout', 'instance');
		construct_hidden_code('action', 'update');
		construct_hidden_code('instanceid', $instanceid);
		if ($hasSuhosin)
		{
			print_table_start();
			print_description_row($hasSuhosin);
			print_table_break();
		}
		print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_instance'], $instance['name']));
	}
	else
	{
		// Add
		print_cp_header($vbphrase['dbtech_vbshout_add_new_instance']);
		print_form_header('vbshout', 'instance');
		construct_hidden_code('action', 'update');
		if ($hasSuhosin)
		{
			print_table_start();
			print_description_row($hasSuhosin);
			print_table_break();
		}
		print_table_header($vbphrase['dbtech_vbshout_add_new_instance']);
		
		$instance = $defaults;
		
		// Ensure we always have the default options
		VBSHOUT::loadDefaultInstanceOptions($instance);
	}
	
	print_description_row($vbphrase['dbtech_vbshout_main_settings'], false, 2, 'optiontitle');	
	if ($instanceid)
	{
		print_label_row($vbphrase['dbtech_vbshout_varname'], 																		$instance['varname']);
	}
	else
	{
		print_input_row($vbphrase['dbtech_vbshout_varname'], 				'instance[varname]', 									$instance['varname']);
	}
	print_input_row($vbphrase['title'], 									'instance[name]', 										$instance['name']);
	print_textarea_row($vbphrase['description'],							'instance[description]',								$instance['description']);
	print_yes_no_row($vbphrase['active'],									'instance[active]',										$instance['active']);
	print_input_row($vbphrase['display_order'],								'instance[displayorder]',								$instance['displayorder']);
	print_textarea_row($vbphrase['dbtech_vbshout_sticky'],					'instance[sticky_raw]',									$instance['sticky_raw']);	
	print_description_row($vbphrase['dbtech_vbshout_display_settings'], false, 2, 'optiontitle');	
	print_select_row($vbphrase['dbtech_vbshout_auto_display_descr'],		'instance[autodisplay]', 		$displays, 				$instance['autodisplay']);
	print_description_row($vbphrase['dbtech_vbshout_sound_settings'], false, 2, 'optiontitle');	
	if ($vbulletin->options['dbtech_vbshout_html5_audio'])
	{
		print_yes_no_row($vbphrase['dbtech_vbshout_enable_shout_sound'],	'instance[options][enableshoutsound]', 					$instance['options']['enableshoutsound']);
		print_yes_no_row($vbphrase['dbtech_vbshout_enable_invite_sound'],	'instance[options][enableinvitesound]', 				$instance['options']['enableinvitesound']);
		print_yes_no_row($vbphrase['dbtech_vbshout_enable_pm_sound'],		'instance[options][enablepmsound]', 					$instance['options']['enablepmsound']);
	}
	else
	{
		print_select_row($vbphrase['dbtech_vbshout_shout_shound_descr'],	'instance[shoutsound]', 		$soundfiles,			$instance['shoutsound']);
		print_select_row($vbphrase['dbtech_vbshout_invite_sound_descr'],	'instance[invitesound]', 		$soundfiles,			$instance['invitesound']);
		print_select_row($vbphrase['dbtech_vbshout_pm_sound_descr'],		'instance[pmsound]', 			$soundfiles,			$instance['pmsound']);
	}
	print_description_row($vbphrase['options'], false, 2, 'optiontitle');	
	print_bitfield_row($vbphrase['dbtech_vbshout_logging_descr'], 			'instance[options][logging]', 	$bitfields['logging'], 	$instance['options']['logging']);
	print_bitfield_row($vbphrase['dbtech_vbshout_editors_descr'], 			'instance[options][editors]', 	$bitfields['editors'], 	$instance['options']['editors']);
	print_bitfield_row($vbphrase['dbtech_vbshout_notices_descr'], 			'instance[options][notices]', 	$bitfields['notices'], 	$instance['options']['notices']);
	print_yes_no_row($vbphrase['dbtech_vbshout_optimisation_descr'], 		'instance[options][optimisation]', 						$instance['options']['optimisation']);
	print_yes_no_row($vbphrase['dbtech_vbshout_allowsmilies_descr'], 		'instance[options][allowsmilies]', 						$instance['options']['allowsmilies']);
	print_yes_no_row($vbphrase['dbtech_vbshout_activeusers_descr'], 		'instance[options][activeusers]', 						$instance['options']['activeusers']);
	print_yes_no_row($vbphrase['dbtech_vbshout_sounds_descr'], 				'instance[options][sounds]', 							$instance['options']['sounds']);
	//print_yes_no_row($vbphrase['dbtech_vbshout_enablemenu_descr'], 			'instance[options][enablemenu]', 						$instance['options']['enablemenu']);
	print_yes_no_row($vbphrase['dbtech_vbshout_altshouts_descr'], 			'instance[options][altshouts]', 						$instance['options']['altshouts']);
	print_yes_no_row($vbphrase['dbtech_vbshout_enableaccess_descr'], 		'instance[options][enableaccess]', 						$instance['options']['enableaccess']);
	print_yes_no_row($vbphrase['dbtech_vbshout_anonymise_descr'], 			'instance[options][anonymise]', 						$instance['options']['anonymise']);
	print_yes_no_row($vbphrase['dbtech_vbshout_allcaps_descr'], 			'instance[options][allcaps]', 							$instance['options']['allcaps']);
	print_input_row($vbphrase['dbtech_vbshout_maxshouts_descr'], 			'instance[options][maxshouts]', 						$instance['options']['maxshouts']);
	print_input_row($vbphrase['dbtech_vbshout_maxarchiveshouts_descr'], 	'instance[options][maxarchiveshouts]', 					$instance['options']['maxarchiveshouts']);
	print_input_row($vbphrase['dbtech_vbshout_height_descr'], 				'instance[options][height]', 							$instance['options']['height']);
	print_input_row($vbphrase['dbtech_vbshout_floodchecktime_descr'], 		'instance[options][floodchecktime]', 					$instance['options']['floodchecktime']);
	print_input_row($vbphrase['dbtech_vbshout_maxchars_descr'], 			'instance[options][maxchars]', 							$instance['options']['maxchars']);
	print_input_row($vbphrase['dbtech_vbshout_maximages_descr'], 			'instance[options][maximages]', 						$instance['options']['maximages']);
	print_input_row($vbphrase['dbtech_vbshout_idletimeout_descr'], 			'instance[options][idletimeout]', 						$instance['options']['idletimeout']);
	print_input_row($vbphrase['dbtech_vbshout_refresh_descr'], 				'instance[options][refresh]', 							$instance['options']['refresh']);
	print_input_row($vbphrase['dbtech_vbshout_maxchats_descr'], 			'instance[options][maxchats]', 							$instance['options']['maxchats']);
	print_select_row($vbphrase['dbtech_vbshout_shoutorder_descr'],			'instance[options][shoutorder]', array(
																												'DESC' 	=> $vbphrase['dbtech_vbshout_newest_first'],
																												'ASC' 	=> $vbphrase['dbtech_vbshout_oldest_first']
																											),						$instance['options']['shoutorder']);
	print_select_row($vbphrase['dbtech_vbshout_maxsize_descr'],				'instance[options][maxsize]', 	array(
																												3 => 3,
																												4 => 4,
																												5 => 5,
																												6 => 6,
																												7 => 7
																											),						$instance['options']['maxsize']);
	print_description_row($vbphrase['dbtech_vbshout_forum_milestones'], false, 2, 'optiontitle');	
	print_input_row($vbphrase['dbtech_vbshout_postping_interval_descr'], 		'instance[options][postping_interval]', 			$instance['options']['postping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_threadping_interval_descr'], 		'instance[options][threadping_interval]', 			$instance['options']['threadping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_memberping_interval_descr'], 		'instance[options][memberping_interval]', 			$instance['options']['memberping_interval']);

	
	
	($hook = vBulletinHook::fetch_hook('dbtech_vbshout_modifyinstance')) ? eval($hook) : false;
	
	if ($hasSuhosin)
	{
		print_submit_row(($instanceid ? $vbphrase['save'] : $vbphrase['dbtech_vbshout_add_new_instance']), false, count($headings));
	}
	else
	{
		print_table_break();
	}
	
	if ($instanceid OR !$hasSuhosin)
	{
		// Bitfields
		$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
		
		// Table header
		$headings = array();
		$headings[] = $vbphrase['usergroup'];
		foreach ((array)$permissions as $permissionname => $bit)
		{
			$headings[] = $vbphrase["dbtech_vbshout_permission_{$permissionname}"];
		}
		
		if ($hasSuhosin)
		{
			print_form_header('vbshout', 'instance');
			construct_hidden_code('action', 'updateinstancepermissions');
			construct_hidden_code('instanceid', $instanceid);
		}
		print_table_header($vbphrase['dbtech_vbshout_instance_permissions'], count($headings));
		print_cells_row($headings, 0, 'thead');
		
		foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
		{
			// Table data
			$cell = array();
			$cell[] = $usergroup['title'];	
			foreach ((array)$permissions as $permissionname => $bit)
			{
				$cell[] = '<center>
					<input type="hidden" name="permissions[' . $usergroupid . '][' . $permissionname . ']" value="0" />
					<input type="checkbox" name="permissions[' . $usergroupid . '][' . $permissionname . ']" value="1"' . ($instance['permissions']["$usergroupid"] & $bit ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;permissions[' . $usergroupid . '][' . $permissionname . ']&quot;"' : '') . '/>
				</center>';
			}
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		if (!VBSHOUT::$isPro)
		{
			//print_description_row('These permissions will apply to ALL instances!', false, count($headings));
		}	
		if ($hasSuhosin)
		{
			print_submit_row(($instanceid ? $vbphrase['save'] : $vbphrase['dbtech_vbshout_add_new_instance']), false, count($headings));
		}
		else
		{
			print_table_break();
		}
		
		// Bitfields
		$permissions = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
		
		// Table header
		$headings = array();
		$headings[] = $vbphrase['usergroup'];
		foreach ((array)$permissions as $permissionname => $bit)
		{
			$headings[] = $vbphrase["{$permissionname}"];
		}
		
		if ($hasSuhosin)
		{
			print_form_header('vbshout', 'instance');
			construct_hidden_code('action', 'updatebbcodepermissions');
			construct_hidden_code('instanceid', $instanceid);
		}
		print_table_header($vbphrase['dbtech_vbshout_bbcode_permissions'], count($headings));
		print_cells_row($headings, 0, 'thead');
		
		foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
		{
			// Table data
			$cell = array();
			$cell[] = $usergroup['title'];	
			foreach ((array)$permissions as $permissionname => $bit)
			{
				$cell[] = '<center>
					<input type="hidden" name="bbcodepermissions[' . $usergroupid . '][' . $permissionname . ']" value="0" />
					<input type="checkbox" name="bbcodepermissions[' . $usergroupid . '][' . $permissionname . ']" value="1"' . ($instance['bbcodepermissions']["$usergroupid"] & $bit ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;bbcodepermissions[' . $usergroupid . '][' . $permissionname . ']&quot;"' : '') . '/>
				</center>';
			}
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		if (!VBSHOUT::$isPro)
		{
			//print_description_row('These permissions will apply to ALL instances!', false, count($headings));
		}	
		if ($hasSuhosin)
		{
			print_submit_row(($instanceid ? $vbphrase['save'] : $vbphrase['dbtech_vbshout_add_new_instance']), false, count($headings));
		}
		else
		{
			print_table_break();
		}
	}
	
	if (!$hasSuhosin)
	{
		print_submit_row(($instanceid ? $vbphrase['save'] : $vbphrase['dbtech_vbshout_add_new_instance']), false);
	}
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'instanceid' 		=> TYPE_UINT,
		'instance' 			=> TYPE_ARRAY,
		'permissions' 		=> TYPE_ARRAY,
		'bbcodepermissions' => TYPE_ARRAY,
	));
	
	// Store raw sticky
	$sticky_raw = $vbulletin->GPC['instance']['sticky'] = $vbulletin->GPC['instance']['sticky_raw'];
	
	// Ensure we got BBCode Parser
	require_once(DIR . '/includes/class_bbcode.php');
	if (!function_exists('convert_url_to_bbcode'))
	{
		require_once(DIR . '/includes/functions_newpost.php');
	}
	
	// Initialise the parser (use proper BBCode)
	$parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
	
	if ($vbulletin->options['allowedbbcodes'] & 64)
	{
		// We can use the URL BBCode, so convert links
		$vbulletin->GPC['instance']['sticky'] = convert_url_to_bbcode($vbulletin->GPC['instance']['sticky_raw']);
	}	
	
	// BBCode parsing
	$vbulletin->GPC['instance']['sticky'] = $parser->parse($vbulletin->GPC['instance']['sticky'], 'nonforum');		
		
	foreach ($bitfields as $key => $fieldname)
	{
		$bit = 0;
		foreach ((array)$vbulletin->GPC['instance']['options']["$key"] as $value)
		{
			// Update the options array
			$bit += $value;
		}
		$vbulletin->GPC['instance']['options']["$key"] = $bit;
	}
	
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	$bitfields = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
	$bitfields2 = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
	
	if (count($vbulletin->GPC['permissions']))
	{
		$permarray = array();
		foreach ((array)$vbulletin->GPC['permissions'] as $usergroupid => $permvalues)
		{
			
			$permarray["$usergroupid"] = 0;
			foreach ((array)$permvalues as $bitfield => $bit)
			{
				// Update the permissions array
				$permarray["$usergroupid"] += ($bit ? $bitfields["$bitfield"] : 0);
			}
		}
	
		// Set the perm array
		$vbulletin->GPC['instance']['permissions'] = $permarray;
	}
	
	if (count($vbulletin->GPC['bbcodepermissions']))
	{
		$permarray = array();
		foreach ((array)$vbulletin->GPC['bbcodepermissions'] as $usergroupid => $permvalues)
		{
			$permarray["$usergroupid"] = 0;
			foreach ((array)$permvalues as $bitfield => $bit)
			{
				// Update the permissions array
				$permarray["$usergroupid"] += ($bit ? $bitfields2["$bitfield"] : 0);
			}
		}
		
		// Set the perm array
		$vbulletin->GPC['instance']['bbcodepermissions'] = $permarray;
	}
	
	// init data manager
	$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['instanceid'])
	{
		if (!$existing = VBSHOUT::$cache['instance']["{$vbulletin->GPC[instanceid]}"])
		{
			// Couldn't find the instance
			print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_instance'], $vbulletin->GPC['instanceid']);
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
	
	// instance fields
	foreach ($vbulletin->GPC['instance'] AS $key => $val)
	{
		if (!$vbulletin->GPC['instanceid'] OR $existing["$key"] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}
	
	// Save! Hopefully.
	$dm->save();
	
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $phrase);	
}

// #############################################################################
if ($_POST['action'] == 'displayorder')
{
	$vbulletin->input->clean_array_gpc('p', array('order' => TYPE_ARRAY));
	
	if (is_array($vbulletin->GPC['order']))
	{
		foreach ($vbulletin->GPC['order'] as $instanceid => $displayorder)
		{
			if (!$existing = VBSHOUT::$cache['instance'][$instanceid])
			{
				// Couldn't find the instance
				continue;
			}
			
			if ($existing['displayorder'] == $displayorder)
			{
				// No change
				continue;
			}
			
			// init data manager
			$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
				$dm->set_existing($existing);
				$dm->set('displayorder', $displayorder);
			$dm->save();
			unset($dm);	
		}
	}
	
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('saved_display_order_successfully');	
}

// #############################################################################
if ($_POST['action'] == 'updateinstancepermissions')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'instanceid'		=> TYPE_UINT,
		'permissions' 		=> TYPE_ARRAY,
	));
	
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	$bitfields = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
	
	$permarray = array();
	foreach ((array)$vbulletin->GPC['permissions'] as $usergroupid => $permvalues)
	{
		
		$permarray["$usergroupid"] = 0;
		foreach ((array)$permvalues as $bitfield => $bit)
		{
			// Update the permissions array
			$permarray["$usergroupid"] += ($bit ? $bitfields["$bitfield"] : 0);
		}
	}
	
	// Set the perm array
	$vbulletin->GPC['instance']['permissions'] = $permarray;
	
	// init data manager
	$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['instanceid'])
	{
		if (!$existing = VBSHOUT::$cache['instance']["{$vbulletin->GPC[instanceid]}"])
		{
			// Couldn't find the instance
			print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_instance'], $vbulletin->GPC['instanceid']);
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
	
	// instance fields
	foreach ($vbulletin->GPC['instance'] AS $key => $val)
	{
		if (!$vbulletin->GPC['instanceid'] OR $existing["$key"] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}
	
	// Save! Hopefully.
	$dm->save();
			
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $vbphrase['dbtech_vbshout_edited']);	
}


// #############################################################################
if ($_POST['action'] == 'updatebbcodepermissions')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'instanceid'		=> TYPE_UINT,
		'bbcodepermissions' => TYPE_ARRAY,
	));
	
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	$bitfields = fetch_bitfield_definitions('nocache|allowedbbcodesfull');

	$permarray = array();
	foreach ((array)$vbulletin->GPC['bbcodepermissions'] as $usergroupid => $permvalues)
	{
		$permarray["$usergroupid"] = 0;
		foreach ((array)$permvalues as $bitfield => $bit)
		{
			// Update the permissions array
			$permarray["$usergroupid"] += ($bit ? $bitfields["$bitfield"] : 0);
		}
	}
	
	// Set the perm array
	$vbulletin->GPC['instance']['bbcodepermissions'] = $permarray;
	
	// init data manager
	$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['instanceid'])
	{
		if (!$existing = VBSHOUT::$cache['instance']["{$vbulletin->GPC[instanceid]}"])
		{
			// Couldn't find the instance
			print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_instance'], $vbulletin->GPC['instanceid']);
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
	
	// instance fields
	foreach ($vbulletin->GPC['instance'] AS $key => $val)
	{
		if (!$vbulletin->GPC['instanceid'] OR $existing["$key"] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}
	
	// Save! Hopefully.
	$dm->save();
			
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $vbphrase['dbtech_vbshout_edited']);	
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
	
	print_cp_header(construct_phrase($vbphrase['dbtech_vbshout_delete_x'], $vbphrase['dbtech_vbshout_instance']));
	print_delete_confirmation('dbtech_vbshout_instance', $vbulletin->GPC['instanceid'], 'vbshout', 'instance', 'dbtech_vbshout_instance', array('action' => 'kill'), '', 'name');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'instanceid' => TYPE_UINT,
		'kill' 		 => TYPE_BOOL
	));
	
	if (!$existing = VBSHOUT::$cache['instance']["{$vbulletin->GPC[instanceid]}"])
	{
		// Couldn't find the instance
		print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_instance'], $vbulletin->GPC['instanceid']);
	}
	
	// init data manager
	$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $vbphrase['dbtech_vbshout_deleted']);	
}


// #############################################################################
if ($_REQUEST['action'] == 'permissions')
{
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
	
	print_cp_header($vbphrase['dbtech_vbshout_instance_permissions']);
	
	// Table header
	$headings = array();
	$headings[] = $vbphrase['usergroup'];
	foreach ((array)$permissions as $permissionname => $bit)
	{
		$headings[] = $vbphrase["dbtech_vbshout_permission_{$permissionname}"];
	}
	$headings[] = $vbphrase['edit'];
	
	if (count(VBSHOUT::$cache['instance']))
	{
		print_form_header('', '');	
		print_table_header($vbphrase['dbtech_vbshout_instance_permissions'], count($headings));
		print_cells_row($headings, 0, 'thead');
		
		foreach (VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			print_description_row($instance['name'] . ' - ' . $instance['description'], false, count($headings), 'optiontitle');
			
			foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
			{
				// Table data
				$cell = array();
				$cell[] = $usergroup['title'];
				foreach ((array)$permissions as $permissionname => $bit)
				{
					$cell[] = ($instance['permissions']["$usergroupid"] & $bit ? $vbphrase['yes'] : '<span class="col-i"><strong>' . $vbphrase['no'] . '</strong></span>');
				}
				$cell[] = construct_link_code($vbphrase['edit'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=instance&amp;action=modify&amp;instanceid=' . $instanceid);
				
				// Print the data
				print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
			}
		}
		
		print_table_footer();
	}
	else
	{
		print_form_header('vbshout', 'instance');	
		construct_hidden_code('action', 'modify');
		print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
		print_description_row($vbphrase['dbtech_vbshout_no_instances'], false, count($headings));
		print_submit_row($vbphrase['dbtech_vbshout_add_new_instance'], false, count($headings));	
	}
}

// #############################################################################
if ($_REQUEST['action'] == 'masspermissions' AND VBSHOUT::$isPro)
{
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
	$permissions2 = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
	
	$usergrouplist = array();
	foreach($vbulletin->usergroupcache AS $usergroup)
	{
		$usergrouplist[] = "<input type=\"checkbox\" name=\"usergrouplist[$usergroup[usergroupid]]\" value=\"1\" /> $usergroup[title]";
	}
	$usergrouplist = implode("<br />\n", $usergrouplist);
	
	print_cp_header($vbphrase['dbtech_vbshout_instance_permissions']);
	
	if (count(VBSHOUT::$cache['instance']))
	{
		// Table header
		$headings = array();
		$headings[] = $vbphrase['dbtech_vbshout_instance'];
		foreach ((array)$permissions as $permissionname => $bit)
		{
			$headings[] = $vbphrase["dbtech_vbshout_permission_{$permissionname}"];
		}
		
		print_form_header('vbshout', 'instance');	
		construct_hidden_code('action', 'updatepermissions');
		print_table_header($vbphrase['dbtech_vbshout_instance_permissions'], count($headings));
		print_cells_row($headings, 0, 'thead');
		
		foreach (VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			// Table data
			$cell = array();
			$cell[] = $instance['name'];
			foreach ((array)$permissions as $permissionname => $bit)
			{
				$cell[] = '<center>
					<input type="hidden" name="permissions[' . $instanceid . '][' . $permissionname . ']" value="0" />
					<input type="checkbox" name="permissions[' . $instanceid . '][' . $permissionname . ']" value="1"/>
				</center>';
			}
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		print_table_header($vbphrase['dbtech_vbshout_permission_targets'], count($headings));
		$class = fetch_row_bgclass();
		echo "<tr valign=\"top\">
		<td class=\"$class\"" . ($dowidth ? " width=\"$left_width%\"" : '') . ">" . $vbphrase['dbtech_vbshout_copy_permissions_to_groups'] . "</td>
		<td class=\"$class\"" . ($dowidth ? " width=\"$right_width%\"" : '') . " colspan=\"" . (count($headings) - 1) . "\"><span class=\"smallfont\">$usergrouplist</span></td>\n</tr>\n";
		print_submit_row($vbphrase['save'], false, count($headings));
		
		
		
		// Table header
		$headings = array();
		$headings[] = $vbphrase['dbtech_vbshout_instance'];
		foreach ((array)$permissions2 as $permissionname => $bit)
		{
			$headings[] = $vbphrase["{$permissionname}"];
		}
		
		print_form_header('vbshout', 'instance');	
		construct_hidden_code('action', 'updatepermissions');
		print_table_header($vbphrase['dbtech_vbshout_bbcode_permissions'], count($headings));
		print_cells_row($headings, 0, 'thead');
		
		foreach (VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			// Table data
			$cell = array();
			$cell[] = $instance['name'];
			foreach ((array)$permissions2 as $permissionname => $bit)
			{
				$cell[] = '<center>
					<input type="hidden" name="bbcodepermissions[' . $instanceid . '][' . $permissionname . ']" value="0" />
					<input type="checkbox" name="bbcodepermissions[' . $instanceid . '][' . $permissionname . ']" value="1"/>
				</center>';
			}
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		print_table_header($vbphrase['dbtech_vbshout_permission_targets'], count($headings));
		$class = fetch_row_bgclass();
		echo "<tr valign=\"top\">
		<td class=\"$class\"" . ($dowidth ? " width=\"$left_width%\"" : '') . ">" . $vbphrase['dbtech_vbshout_copy_permissions_to_groups'] . "</td>
		<td class=\"$class\"" . ($dowidth ? " width=\"$right_width%\"" : '') . " colspan=\"" . (count($headings) - 1) . "\"><span class=\"smallfont\">$usergrouplist</span></td>\n</tr>\n";
		print_submit_row($vbphrase['save'], false, count($headings));
	}
	else
	{
		$formvar = '';
		//($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instance')) ? eval($hook) : false;
		
		print_form_header('vbshout', 'instance');	
		construct_hidden_code('action', 'modify');
		print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
		print_description_row($vbphrase['dbtech_vbshout_no_instances'], false, count($headings));
		print_submit_row($vbphrase['dbtech_vbshout_add_new_instance'], false, count($headings));	
	}
}

// #############################################################################
if ($_POST['action'] == 'updatepermissions' AND VBSHOUT::$isPro)
{
	$vbulletin->input->clean_array_gpc('p', array(
		'usergrouplist' 	=> TYPE_ARRAY,
		'permissions' 		=> TYPE_ARRAY,
		'bbcodepermissions' => TYPE_ARRAY,
	));
	
	// Ensure we can fetch bitfields
	require_once(DIR . '/includes/adminfunctions_options.php');
	$bitfields = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
	$bitfields2 = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
	
	foreach ($vbulletin->GPC['permissions'] as $instanceid => $permissions)
	{
		if (!$existing = VBSHOUT::$cache['instance']["$instanceid"])
		{
			// Editing ID doesn't exist
			continue;
		}
		
		// Begin array of permissions
		$permarray = $existing['permissions'];
		
		$permvalue = 0;
		foreach ($permissions as $bitfield => $bit)
		{
			// Update the permissions array
			$permvalue += ($bit ? $bitfields["$bitfield"] : 0);
		}
		
		foreach ($vbulletin->GPC['usergrouplist'] as $usergroupid => $onoff)
		{
			if (!$onoff)
			{
				// We're not moving to this
				continue;
			}

			// Now store the permissions array
			$permarray["$usergroupid"] = $permvalue;
		}
		
		if (!count($permarray))
		{
			// Not doing any UGs
			continue;
		}
		
		// init data manager
		$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
			$dm->set_existing($existing);
			$dm->set('permissions', $permarray);
		$dm->save();
		unset($dm);		
	}
	
	foreach ($vbulletin->GPC['bbcodepermissions'] as $instanceid => $permissions)
	{
		if (!$existing = VBSHOUT::$cache['instance']["$instanceid"])
		{
			// Editing ID doesn't exist
			continue;
		}
		
		// Begin array of permissions
		$permarray = $existing['bbcodepermissions'];
		
		$permvalue = 0;
		foreach ($permissions as $bitfield => $bit)
		{
			// Update the permissions array
			$permvalue += ($bit ? $bitfields2["$bitfield"] : 0);
		}
		
		foreach ($vbulletin->GPC['usergrouplist'] as $usergroupid => $onoff)
		{
			if (!$onoff)
			{
				// We're not moving to this
				continue;
			}
			
			// Now store the permissions array
			$permarray["$usergroupid"] = $permvalue;
		}
		
		if (!count($permarray))
		{
			// Not doing any UGs
			continue;
		}
	
		// init data manager
		$dm =& VBSHOUT::initDataManager('Instance', $vbulletin, ERRTYPE_CP);
			$dm->set_existing($existing);
			$dm->set('bbcodepermissions', $permarray);
		$dm->save();
		unset($dm);	
	}
		
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $vbphrase['edited']);	
}

print_cp_footer();