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
if ($_REQUEST['action'] == 'options' OR empty($_REQUEST['action']))
{
	require_once(DIR . '/includes/adminfunctions_misc.php');
	
	$vbulletin->input->clean_array_gpc('r', array(
		'varname' => TYPE_STR,
		'dogroup' => TYPE_STR,
	));
	
	// intercept direct call to do=options with $varname specified instead of $dogroup
	if ($_REQUEST['do'] == 'options' AND !empty($vbulletin->GPC['varname']))
	{
		if ($vbulletin->GPC['varname'] == '[all]')
		{
			// go ahead and show all settings
			$vbulletin->GPC['dogroup'] = '[all]';
		}
		else if ($group = $db->query_first("SELECT varname, grouptitle FROM " . TABLE_PREFIX . "setting WHERE varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "'"))
		{
			// redirect to show the correct group and use and anchor to jump to the correct variable
			exec_header_redirect('vbshout.php?' . $vbulletin->session->vars['sessionurl_js'] . "do=options&dogroup=$group[grouptitle]#$group[varname]");
		}
	}
	
	require_once(DIR . '/includes/adminfunctions_options.php');
	require_once(DIR . '/includes/functions_misc.php');
	
	// query settings phrases
	$settingphrase = array();
	$phrases = $db->query_read("
		SELECT varname, text
		FROM " . TABLE_PREFIX . "phrase
		WHERE fieldname = 'vbsettings' AND
			languageid IN(-1, 0, " . LANGUAGEID . ")
		ORDER BY languageid ASC
	");
	while($phrase = $db->fetch_array($phrases))
	{
		$settingphrase["$phrase[varname]"] = $phrase['text'];
	}
	
	print_cp_header($vbphrase['dbtech_vbshout_settings']);
	
	require_once(DIR . '/includes/adminfunctions_language.php');
	
	$vbulletin->input->clean_array_gpc('r', array(
		'advanced' => TYPE_BOOL,
		'expand'   => TYPE_BOOL,
	));
	
	echo '<script type="text/javascript" src="../clientscript/vbulletin_cpoptions_scripts.js?v=' . SIMPLE_VERSION . '"></script>';
	
	// display links to settinggroups and create settingscache
	$settingscache = array();
	$options = array('[all]' => '-- ' . $vbphrase['show_all_settings'] . ' --');
	$lastgroup = '';
	
	$settings = $db->query_read("
		SELECT setting.*, settinggroup.grouptitle
		FROM " . TABLE_PREFIX . "settinggroup AS settinggroup
		LEFT JOIN " . TABLE_PREFIX . "setting AS setting USING(grouptitle)
		WHERE settinggroup.product LIKE 'dbtech_vbshout%'
			AND settinggroup.displayorder <> 0
		ORDER BY settinggroup.displayorder, setting.displayorder
	");
	
	if (empty($vbulletin->GPC['dogroup']) AND $vbulletin->GPC['expand'])
	{
		while ($setting = $db->fetch_array($settings))
		{
			$settingscache["$setting[grouptitle]"]["$setting[varname]"] = $setting;
			if ($setting['grouptitle'] != $lastgroup)
			{
				$grouptitlecache["$setting[grouptitle]"] = $setting['grouptitle'];
				$grouptitle = $settingphrase["settinggroup_$setting[grouptitle]"];
			}
			$options["$grouptitle"]["$setting[varname]"] = $settingphrase["setting_$setting[varname]_title"];
			$lastgroup = $setting['grouptitle'];
		}
	
		$altmode = 0;
		$linktext =& $vbphrase['collapse_setting_groups'];
	}
	else
	{
		while ($setting = $db->fetch_array($settings))
		{
			$settingscache["$setting[grouptitle]"]["$setting[varname]"] = $setting;
			if ($setting['grouptitle'] != $lastgroup)
			{
				$grouptitlecache["$setting[grouptitle]"] = $setting['grouptitle'];
				$options["$setting[grouptitle]"] = $settingphrase["settinggroup_$setting[grouptitle]"];
			}
			$lastgroup = $setting['grouptitle'];
		}
	
		$altmode = 1;
		$linktext =& $vbphrase['expand_setting_groups'];
	}
	$db->free_result($settings);
	
	$optionsmenu = "\n\t<select name=\"" . iif($vbulletin->GPC['expand'], 'varname', 'dogroup') . "\" class=\"bginput\" tabindex=\"1\" " . iif(empty($vbulletin->GPC['dogroup']), 'ondblclick="this.form.submit();" size="20"', 'onchange="this.form.submit();"') . " style=\"width:350px\">\n" . construct_select_options($options, iif($vbulletin->GPC['dogroup'], $vbulletin->GPC['dogroup'], '[all]')) . "\t</select>\n\t";
	
	print_form_header('vbshout', 'options', 0, 1, 'groupForm', '90%', '', 1, 'get');
	
	$scriptpath = $vbulletin->scriptpath;
	$vbulletin->scriptpath = 'options.php';
	
	if (empty($vbulletin->GPC['dogroup'])) // show the big <select> with no options
	{
		print_table_header($vbphrase['vbulletin_options']);
		print_label_row($vbphrase['settings_to_edit'], $optionsmenu);
		print_submit_row($vbphrase['edit_settings'], 0);
	}
	else // show the small list with selected setting group(s) options
	{
		print_table_header("$vbphrase[setting_group] $optionsmenu <input type=\"submit\" value=\"$vbphrase[go]\" class=\"button\" tabindex=\"1\" />");
		print_table_footer();
	
		// show selected settings
		print_form_header('vbshout', 'options', false, true, 'optionsform', '90%', '', true, 'post" onsubmit="return count_errors()');
		construct_hidden_code('action', 'dooptions');
		construct_hidden_code('dogroup', $vbulletin->GPC['dogroup']);
		construct_hidden_code('advanced', $vbulletin->GPC['advanced']);
	
		if ($vbulletin->GPC['dogroup'] == '[all]') // show all settings groups
		{
			foreach ($grouptitlecache AS $curgroup => $group)
			{
				print_setting_group($curgroup, $vbulletin->GPC['advanced']);
				echo '<tbody>';
				print_description_row("<input type=\"submit\" class=\"button\" value=\" $vbphrase[save] \" tabindex=\"1\" title=\"" . $vbphrase['save_settings'] . "\" />", 0, 2, 'tfoot" style="padding:1px" align="right');
				echo '</tbody>';
				print_table_break(' ');
			}
		}
		else
		{
			print_setting_group($vbulletin->GPC['dogroup'], $vbulletin->GPC['advanced']);
		}
	
		print_submit_row($vbphrase['save']);
	
		?>
		<div id="error_output" style="font: 10pt courier new"></div>
		<script type="text/javascript">
		<!--
		var error_confirmation_phrase = "<?php echo $vbphrase['error_confirmation_phrase']; ?>";
		//-->
		</script>
		<script type="text/javascript" src="../clientscript/vbulletin_settings_validate.js?v=<?php echo SIMPLE_VERSION; ?>"></script>
		<?php
	}
	
	$vbulletin->scriptpath = $scriptpath;
	
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'dooptions')
{
	require_once(DIR . '/includes/adminfunctions_misc.php');
	
	$vbulletin->input->clean_array_gpc('r', array(
		'varname' => TYPE_STR,
		'dogroup' => TYPE_STR,
	));
	
	require_once(DIR . '/includes/adminfunctions_options.php');
	require_once(DIR . '/includes/functions_misc.php');
	
	// query settings phrases
	$settingphrase = array();
	$phrases = $db->query_read("
		SELECT varname, text
		FROM " . TABLE_PREFIX . "phrase
		WHERE fieldname = 'vbsettings' AND
			languageid IN(-1, 0, " . LANGUAGEID . ")
		ORDER BY languageid ASC
	");
	while($phrase = $db->fetch_array($phrases))
	{
		$settingphrase["$phrase[varname]"] = $phrase['text'];
	}
	
	$vbulletin->input->clean_array_gpc('p', array(
		'setting'  => TYPE_ARRAY,
		'advanced' => TYPE_BOOL
	));
	
	if (!empty($vbulletin->GPC['setting']))
	{
		save_settings($vbulletin->GPC['setting']);
	
		define('CP_REDIRECT', 'vbshout.php?do=options&amp;dogroup=' . $vbulletin->GPC['dogroup'] . '&amp;advanced=' . $vbulletin->GPC['advanced']);
		print_stop_message('saved_settings_successfully');
	}
	else
	{
		print_stop_message('nothing_to_do');
	}	
}