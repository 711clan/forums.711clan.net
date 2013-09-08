<?php
if ($_REQUEST['do'] == 'gb_video')
{
	$userid = $vbulletin->userinfo['userid'];
	
	$current_vid = $db->query_first("
		SELECT gb_profile_video
		FROM " . TABLE_PREFIX . "user
		WHERE userid = '$userid'
	");
	
	if ($current_vid['gb_profile_video'])
	{
		$video = $current_vid['gb_profile_video'];
	}
	
	$templatename = 'gb_usercp_profile';
	
	($hook = vBulletinHook::fetch_hook('usercp_nav_start')) ? eval($hook) : false;
}

if ($_POST['do'] == 'addprofilevideo')
{
	if (!($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canprofilepic']))
	{
		print_no_permission();
	}

	if (!$vbulletin->options['gb_enabled'])
	{
		print_no_permission();
	}

	$vbulletin->input->clean_gpc('p', 'gb_profile_video', TYPE_NOHTML);
	
	$userid = $vbulletin->userinfo['userid'];

	if ($vbulletin->GPC['gb_profile_video'])
	{
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user SET
				gb_profile_video = '" . $db->escape_string($vbulletin->GPC['gb_profile_video']) . "'
			WHERE userid = '$userid'
		");
	}
	
	else
	{
		$default = 'http://www.';
		
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user SET
				gb_profile_video = '" . $db->escape_string($default) . "'
			WHERE userid = '$userid'
		");
	}
	
	$vbulletin->url = 'profile.php?do=' . $vbulletin->session->vars['sessionurl'] . "gb_video";
	eval(print_standard_redirect('redirect_updatethanks', 1, 1));
}

?>