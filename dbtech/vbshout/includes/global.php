<?php
if (class_exists('VBSHOUT'))
{
	global $template_hook, $ad_location, $hook_location, $vbphrase, $show;
	
	if (!class_exists('vB_Template'))
	{
		// We need the template class
		require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
	}
	
	// Begin list of JS phrases
	$jsphrases = array(
		'dbtech_vbshout_idle' 							=> $vbphrase['dbtech_vbshout_idle'],
		'dbtech_vbshout_flagged_idle'					=> $vbphrase['dbtech_vbshout_flagged_idle'],
		'dbtech_vbshout_saving_shout' 					=> $vbphrase['dbtech_vbshout_saving_shout'],
		'dbtech_vbshout_editing_shout' 					=> $vbphrase['dbtech_vbshout_editing_shout'],
		'dbtech_vbshout_editing_sticky' 				=> $vbphrase['dbtech_vbshout_editing_sticky'],
		'dbtech_vbshout_deleting_shout' 				=> $vbphrase['dbtech_vbshout_deleting_shout'],
		'dbtech_vbshout_fetching_shouts' 				=> $vbphrase['dbtech_vbshout_fetching_shouts'],
		'dbtech_vbshout_fetching_shouts_in_x_seconds'	=> $vbphrase['dbtech_vbshout_fetching_shouts_in_x_seconds'],
		'dbtech_vbshout_no_active_users'				=> $vbphrase['dbtech_vbshout_no_active_users'],
		'dbtech_vbshout_saving_shout_styles'			=> $vbphrase['dbtech_vbshout_saving_shout_styles'],	
		'dbtech_vbshout_ajax_disabled'					=> $vbphrase['dbtech_vbshout_ajax_disabled'],
		'dbtech_vbshout_must_wait_x_seconds'			=> $vbphrase['dbtech_vbshout_must_wait_x_seconds'],
		'dbtech_vbshout_are_you_sure_banunban'			=> $vbphrase['dbtech_vbshout_are_you_sure_banunban'],
		'dbtech_vbshout_are_you_sure_silenceunsilence'	=> $vbphrase['dbtech_vbshout_are_you_sure_silenceunsilence'],
		'dbtech_vbshout_are_you_sure_ignoreunignore'	=> $vbphrase['dbtech_vbshout_are_you_sure_ignoreunignore'],
		'dbtech_vbshout_are_you_sure_pruneshouts'		=> $vbphrase['dbtech_vbshout_are_you_sure_pruneshouts'],
		'dbtech_vbshout_are_you_sure_chatremove'		=> $vbphrase['dbtech_vbshout_are_you_sure_chatremove'],
		'dbtech_vbshout_are_you_sure_chatjoin'			=> $vbphrase['dbtech_vbshout_are_you_sure_chatjoin'],
		'dbtech_vbshout_are_you_sure_chatleave'			=> $vbphrase['dbtech_vbshout_are_you_sure_chatleave'],
		'dbtech_vbshout_are_you_sure_shoutdelete' 		=> $vbphrase['dbtech_vbshout_are_you_sure_shoutdelete'],
		'dbtech_vbshout_everyone'						=> $vbphrase['dbtech_vbshout_everyone'],
	);
	
	// Escape them
	VBSHOUT::jsEscapeString($jsphrases);
	
	$escapedJsPhrases = '';
	foreach ($jsphrases as $varname => $value)
	{
		// Replace phrases with safe values
		$escapedJsPhrases .= "vbphrase['$varname'] = \"$value\"\n\t\t\t\t\t";
	}
	
	do
	{
		if (!$vbulletin->options['dbtech_vbshout_active'])
		{
			// Stop eet
			break;
		}

		// Begin important arrays
		$editorOptions 			= array();
		$instanceOptions 		= array();
		$instancePermissions 	= array();
		$bbcodePermissions 		= array();
		$userOptions 			= array();
		
		// Grab all the bitfields we can
		require_once(DIR . '/includes/class_bitfield_builder.php');
		$bitfields = vB_Bitfield_Builder::return_data();

		foreach (array(
			'dbtech_vbshout_general_settings' 	=> $bitfields['nocache']['dbtech_vbshout_general_settings'],
			'dbtech_vbshout_editor_settings' 	=> $bitfields['nocache']['dbtech_vbshout_editor_settings']
		) as $settinggroup => $settings)
		{
			foreach ($settings as $settingname => $bit)
			{
				if (VBSHOUT::$isPro)
				{
					// Pro, respect actual settings
					$userOptions[substr($settingname, strlen('dbtech_vbshout_'))] = ((int)$vbulletin->userinfo['dbtech_vbshout_settings'] & (int)$bit) ? 1 : 0;
				}
				else
				{
					// Lite, always on
					$userOptions[substr($settingname, strlen('dbtech_vbshout_'))] = (!in_array($settingName, array(
						'dbtech_vbshout_hidealtcolours', 
						'dbtech_vbshout_hideavatars', 
						'dbtech_vbshout_enableoverride', 
						'dbtech_vbshout_disableshoutbox'
					)) ? 1 : 0);
				}
			}
		}

		
		
		// Do detached check
		$userOptions['is_detached'] = (int)(VBSHOUT::$isPro AND THIS_SCRIPT == 'vbshout' AND $_REQUEST['do'] == 'detach');
		$userOptions['pmtime'] = $vbulletin->userinfo['dbtech_vbshout_pm'];
		
		if (VBSHOUT::$isPro)
		{
			// Only unserialize if it's not already an array
			$vbulletin->userinfo['dbtech_vbshout_soundsettings'] = (!is_array($vbulletin->userinfo['dbtech_vbshout_soundsettings']) ? 
				@unserialize($vbulletin->userinfo['dbtech_vbshout_soundsettings']) : 
				$vbulletin->userinfo['dbtech_vbshout_soundsettings']
			);
			
			foreach ((array)$vbulletin->userinfo['dbtech_vbshout_soundsettings'] as $instanceid => $soundsettings)
			{
				foreach ((array)$soundsettings as $tabid => $bool)
				{
					// Add the mute settings to the user options
					$userOptions['soundSettings'][$instanceid][$tabid] = intval($bool);
				}
			}
		}
		else
		{
			// Ensure this is on
			$vbulletin->userinfo['dbtech_vbshout_settings'] |= 16384;
			$vbulletin->userinfo['dbtech_vbshout_settings'] |= 32768;
			$vbulletin->userinfo['dbtech_vbshout_settings'] |= 65536;
		}

		if (!is_array($vbulletin->userinfo['dbtech_vbshout_invisiblesettings']))
		{
			$vbulletin->userinfo['dbtech_vbshout_invisiblesettings'] = @unserialize($vbulletin->userinfo['dbtech_vbshout_invisiblesettings']);
		}
		
		// Check for archive
		$userOptions['archive'] = ($_REQUEST['do'] == 'archive' AND THIS_SCRIPT == 'vbshout');		

		// Set vB version
		$userOptions['vbversion'] = intval($vbulletin->versionnumber);

		foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{	
			if (!$instance['active'])
			{
				// Inactive instance
				continue;
			}
					
			if ($vbulletin->userinfo['dbtech_vbshout_banned'])
			{
				// Banz!
				continue;
			}		
			
			if (!$instance['permissions_parsed']['canviewshoutbox'])
			{
				// Can't view this instance
				continue;
			}
	
			if ((int)$vbulletin->userinfo['posts'] < $instance['options']['minposts'] AND !VBSHOUT::$permissions['ismanager'])
			{
				// Too few posts
				continue;
			}

			if (!isset($vbulletin->userinfo['dbtech_vbshout_invisiblesettings'][$instanceid]))
			{
				// Set default invisibility
				$vbulletin->userinfo['dbtech_vbshout_invisiblesettings'][$instanceid] = 0;
			}

			// Set invis options
			$userOptions['invisible'][$instanceid] = $vbulletin->userinfo['dbtech_vbshout_invisiblesettings'][$instanceid];

			// To avoid JS hass
			$userOptions['idle'][$instanceid]['unIdle'] = false;
			$userOptions['idle'][$instanceid]['unPause'] = false;
			
			// ######################## Start Value Fallback #########################
			// Maximum Characters Per Shout
			$instance['options']['maxchars'] 		= ($instance['options']['maxchars'] 	> 0 	? $instance['options']['maxchars'] 	: $vbulletin->options['postmaxchars']);
			$instance['options']['maxchars'] 		= (VBSHOUT::$permissions['ismanager'] 	> 0 	? 0 								: $instance['options']['maxchars']);
			
			// Maximum Images Per Shout
			$instance['options']['maximages'] 		= ($instance['options']['maximages'] 	> 0 	? $instance['options']['maximages'] : $vbulletin->options['maximages']);
			
			// Flood check time
			$instance['options']['floodchecktime'] 	= (VBSHOUT::$permissions['ismanager'] 	> 0 	? 0 								: $instance['options']['floodchecktime']);
			
			if ($userOptions['is_detached'])
			{
				$instance['options']['height'] 		= $instance['options']['height_detached'];
				$instance['options']['maxshouts'] 	= $instance['options']['maxshouts_detached'];
				
				if ($vbulletin->userinfo['dbtech_vbshout_shoutboxsize_detached'])
				{
					// Override detached height
					$instance['options']['height'] = $vbulletin->userinfo['dbtech_vbshout_shoutboxsize_detached'];
				}				
			}
			else
			{
				
				if ($vbulletin->userinfo['dbtech_vbshout_shoutboxsize'])
				{
					// Override height
					$instance['options']['height'] = $vbulletin->userinfo['dbtech_vbshout_shoutboxsize'];
				}
			}
			//$instance['options']['maxchars'] 		= 256;
			
			if (!$userOptions['archive'])
			{
				// Render the shoutbox
				$rendered = VBSHOUT::render($instance);
			}
			
			if ($vbulletin->userinfo['userid'] AND $instance['permissions_parsed']['canshout'] AND $instance['options']['editors'])
			{			
				if ($instance['options']['editors'] & 1)
				{
					// Bold
					$editorOptions[$instanceid]['bold'] = VBSHOUT::$shoutstyle[$instanceid]['bold'];
				}
				
				if ($instance['options']['editors'] & 2)
				{
					// Italic
					$editorOptions[$instanceid]['italic'] = VBSHOUT::$shoutstyle[$instanceid]['italic'];
				}
				
				if ($instance['options']['editors'] & 4)
				{
					// Underline
					$editorOptions[$instanceid]['underline'] = VBSHOUT::$shoutstyle[$instanceid]['underline'];
				}
								
				if ($instance['options']['editors'] & 8)
				{
					// Color
					$editorOptions[$instanceid]['color'] = VBSHOUT::$shoutstyle[$instanceid]['color'];
				}						
								
				if ($instance['options']['editors'] & 16)
				{
					// Font
					$editorOptions[$instanceid]['font'] = VBSHOUT::$shoutstyle[$instanceid]['font'];
				}
				
				if ($instance['options']['editors'] & 256 AND VBSHOUT::$isPro)
				{
					// Font
					$editorOptions[$instanceid]['size'] = (isset(VBSHOUT::$shoutstyle[$instanceid]['size']) ? VBSHOUT::$shoutstyle[$instanceid]['size'] : '11px');
				}
			}
			
			
			
			// Set these per-instance arrays
			$instanceOptions[$instanceid] 		= $instance['options'];
			$instancePermissions[$instanceid] 	= $instance['permissions_parsed'];
			$bbcodePermissions[$instanceid] 	= $instance['bbcodepermissions_parsed'];
			
			if (THIS_SCRIPT == 'vbshout' OR defined('VBSHOUT_SKIP_AUTODISPLAY'))
			{
				// Don't need to do anything with this
				VBSHOUT::$rendered[$instance['instanceid']] = $rendered;
				continue;
			}
			
			switch ($instance['autodisplay'])
			{
				case 1:
					if (THIS_SCRIPT == 'index')
					{
						// Below Navbar
						if (intval($vbulletin->versionnumber) != 3)
						{
							// vB4 Location
							$ad_location['global_below_navbar'] .= $rendered;
						}
						else
						{
							// vB3 code
							$ad_location['ad_navbar_below'] .= $rendered;
						}
					}
					break;
					
				case 2:
					if (THIS_SCRIPT == 'index')
					{
						// Above Footer
						$template_hook['forumhome_below_forums'] .= $rendered;
					}
					break;
					
				default:
					// Disabled
					$show['vbshout_' . $instance['varname']] = $rendered;
					break;
			}
		}
		
		if (count($instanceOptions))
		{
			// We can see at least 1 instance
			$footer = VBSHOUT::js($escapedJsPhrases . '
				var vBShout = {
					editorOptions : ' . VBSHOUT::encodeJSON($editorOptions) . ',
					instanceOptions : ' . VBSHOUT::encodeJSON($instanceOptions) . ',
					instancePermissions : ' . VBSHOUT::encodeJSON($instancePermissions) . ',
					bbcodePermissions : ' . VBSHOUT::encodeJSON($bbcodePermissions) . ',
					userOptions : ' . VBSHOUT::encodeJSON($userOptions) . ',
					tabs : ' . VBSHOUT::encodeJSON(VBSHOUT::$tabs) . '
				};
			', false, false) . $footer;
		}
	}
	while (false);
}
?>