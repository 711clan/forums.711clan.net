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
@set_time_limit(0);
ignore_user_abort(1);

print_cp_header($vbphrase['dbtech_vbshout_maintenance']);

$vbulletin->input->clean_array_gpc('r', array(
	'perpage' => TYPE_UINT,
	'startat' => TYPE_UINT,
	'version' => TYPE_UINT
));

if (empty($vbulletin->GPC['perpage']))
{
	$vbulletin->GPC['perpage'] = 250;
}

echo '<p>Finalising Install...</p>';

if ($vbulletin->GPC['version'] == 530)
{
	// Ensure we got BBCode Parser
	require_once(DIR . '/includes/class_bbcode.php');
	if (!function_exists('convert_url_to_bbcode'))
	{
		require_once(DIR . '/includes/functions_newpost.php');
	}
	if (!function_exists('vbshout_fetch_tag_list'))
	{
		require_once(DIR . '/dbtech/vbshout/includes/functions.php');
	}	
	
	if (!$vbulletin->GPC['startat'])
	{
		// Initialise the parser (use proper BBCode)
		$parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
		
		foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			// Grab unparsed message
			$instance['sticky_raw'] = $instance['sticky'];
			
			if ($vbulletin->options['allowedbbcodes'] & 64)
			{
				// We can use the URL BBCode, so convert links
				$instance['sticky'] = convert_url_to_bbcode($instance['sticky']);
			}
			
			// BBCode parsing
			$instance['sticky'] = $parser->parse($instance['sticky'], 'nonforum');
			
			$db->query_write("
				UPDATE " . TABLE_PREFIX . "dbtech_vbshout_instance
				SET 
					sticky_raw = " . $db->sql_prepare($instance['sticky_raw']) . ",
					sticky = " . $db->sql_prepare($instance['sticky']) . "
				WHERE instanceid = $instanceid
			");	
		}
		
		// Build instance cache
		VBSHOUT_CACHE::build('instance');
	}
	
	// Store these settings
	$backup = array(
		'allowedbbcodes' 	=> $vbulletin->options['allowedbbcodes'],
		'allowhtml' 		=> $vbulletin->options['allowhtml'],
		'allowbbcode' 		=> $vbulletin->options['allowbbcode'],
		'allowsmilies' 		=> $vbulletin->options['allowsmilies'],
		'allowbbimagecode' 	=> $vbulletin->options['allowbbimagecode']
	);	
	
	$users = $db->query_read_slave("
		SELECT vbshout.*, user.*
		FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
		LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
		WHERE vbshout.shoutid >= " . $vbulletin->GPC['startat'] . "
		ORDER BY vbshout.shoutid
		LIMIT " . $vbulletin->GPC['perpage']
	);
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = $db->fetch_array($users))
	{
		// Shorthand
		$instance = VBSHOUT::$cache['instance']["$user[instanceid]"];
		
		// Grab unparsed message
		$user['message_raw'] = $user['message'];
		
		if ($instance)
		{
			// Initialise BBCode Permissions
			$permarray = array(
				'permissions_parsed' 		=> VBSHOUT::loadInstancePermissions($instance, $user),
				'bbcodepermissions_parsed' 	=> VBSHOUT::loadInstanceBbcodePermissions($instance, $user)
			);
			
			// Initialise the parser (use proper BBCode)
			$parser = new vB_BbCodeParser($vbulletin, vbshout_fetch_tag_list((array)VBSHOUT::$tag_list, $permarray));
			
			// Override allowed bbcodes
			$vbulletin->options['allowedbbcodes'] = $permarray['bbcodepermissions_parsed']['bit'];
			
			// Override the BBCode list
			$vbulletin->options['allowhtml'] 			= false;
			$vbulletin->options['allowbbcode'] 			= true;
			$vbulletin->options['allowsmilies'] 		= $instance['options']['allowsmilies'];
			$vbulletin->options['allowbbimagecode'] 	= ($permarray['bbcodepermissions_parsed']['bit'] & 1024);
			
			if ($permarray['bbcodepermissions_parsed']['bit'] & 64)
			{
				// We can use the URL BBCode, so convert links
				$user['message'] = convert_url_to_bbcode($user['message']);
			}
		}
		else
		{
			// Initialise the parser (use proper BBCode)
			$parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
			
			if ($vbulletin->options['allowedbbcodes'] & 64)
			{
				// We can use the URL BBCode, so convert links
				$user['message'] = convert_url_to_bbcode($user['message']);
			}	
		}
		
		// BBCode parsing
		$user['message'] = $parser->parse($user['message'], 'nonforum');
		
		// Shorthand
		$shoutid = intval($user['shoutid']);
		
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_shout
			SET 
				message_raw = " . $db->sql_prepare($user['message_raw']) . ",
				message = " . $db->sql_prepare($user['message']) . "
			WHERE shoutid = $shoutid
		");	
		
		echo construct_phrase($vbphrase['processing_x'], $user['shoutid']) . "<br />\n";
		vbflush();
	
		$finishat = ($user['shoutid'] > $finishat ? $user['shoutid'] : $finishat);
	}
	
	foreach ($backup as $vbopt => $val)
	{
		// Reset the settings
		$vbulletin->options["$vbopt"] = $val;
	}		
	
	$finishat++;
	
	if ($checkmore = $db->query_first_slave("SELECT shoutid FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&version=" . $vbulletin->GPC['version'] . "&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&amp;version=" . $vbulletin->GPC['version'] . "&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		define('CP_REDIRECT', 'index.php?loc=' . urlencode('plugin.php?do=product'));
		print_cp_message(
			'Product Install / Upgrade Complete',
			defined('CP_REDIRECT') ? CP_REDIRECT : NULL,
			1,
			defined('CP_BACKURL') ? CP_BACKURL : NULL,
			defined('CP_CONTINUE') ? true : false
		);
	}
}

print_cp_footer();