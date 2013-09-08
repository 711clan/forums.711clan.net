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

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// Grab the shout id
$reportid = $vbulletin->input->clean_gpc('r', 'reportid', TYPE_UINT);

if (!$reportinfo = $db->query_first("
	SELECT
		report.*,
		vbshout.message,
	
		user.username,
		user.usergroupid,
		user.infractiongroupid,
		user.displaygroupid" . ($vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . ",
		
		reporter.userid AS reportuserid,
		reporter.username AS reportusername,
		reporter.usergroupid AS reportusergroupid,
		reporter.infractiongroupid AS reportinfractiongroupid,
		reporter.displaygroupid AS reportdisplaygroupid
		" . ($vbulletin->products['dbtech_vbshop'] ? ", reporter.dbtech_vbshop_purchase AS reportpurchase" : '') . "
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report AS report
	LEFT JOIN " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout USING(shoutid)
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = report.userid)
	LEFT JOIN " . TABLE_PREFIX . "user AS reporter ON(reporter.userid = report.reportuserid)
	WHERE reportid = " . $db->sql_prepare($reportid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_shoutid_specified')));
}

// fetch the markup-enabled username
fetch_musername($reportinfo);

// Setup array for reportd by user
$reportuser = array(
	'userid'					=> $reportinfo['reportuserid'],
	'username' 					=> $reportinfo['reportusername'],
	'usergroupid' 				=> $reportinfo['reportusergroupid'],
	'infractiongroupid' 		=> $reportinfo['reportinfractiongroupid'],
	'displaygroupid' 			=> $reportinfo['reportdisplaygroupid'],
	'dbtech_vbshop_purchase' 	=> $reportinfo['reportpurchase']
);

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

// Store these settings
$backup = array(
	'allowedbbcodes' 	=> $vbulletin->options['allowedbbcodes'],
	'allowhtml' 		=> $vbulletin->options['allowhtml'],
	'allowbbcode' 		=> $vbulletin->options['allowbbcode'],
	'allowsmilies' 		=> $vbulletin->options['allowsmilies'],
	'allowbbimagecode' 	=> $vbulletin->options['allowbbimagecode']
);

// Shorthand
if (!$instance = VBSHOUT::$cache['instance']["$reportinfo[instanceid]"])
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

if (!$instance['permissions_parsed']['canmodchat'])
{
	// Invalid chat room
	print_no_permission();
}

if ($reportuser['userid'])
{
	// fetch the markup-enabled username
	fetch_musername($reportuser);
	
	// Fetch the SEO'd URL to a member's profile
	if (intval($vbulletin->versionnumber) == 3)
	{
		$reportusers = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $reportuser['userid'] . '" target="_blank">' . $reportuser['username'] . '</a>';
	}
	else
	{
		$reportusers = '<a href="' . fetch_seo_url('member', $reportuser) . '" target="_blank">' . $reportuser['musername'] . '</a>';
	}
}
else
{
	// Didn't exist
	$reportusers = 'N/A';
}

// Fetch the SEO'd URL to a member's profile	
if (intval($vbulletin->versionnumber) == 3)
{
	$users = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $reportinfo['userid'] . '" target="_blank">' . $reportinfo['username'] . '</a>';
}
else
{
	$users = '<a href="' . fetch_seo_url('member', $reportinfo) . '" target="_blank">' . $reportinfo['musername'] . '</a>';
}

// Is handled?
$reportinfo['ishandled'] = ($reportinfo['handled'] ? ' checked="checked"' : '');

// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_handling_report'], $reportinfo['reportid']);



($hook = vBulletinHook::fetch_hook('dbtech_vbshout_handlereport')) ? eval($hook) : false;

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_viewreport');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('reportinfo', $reportinfo);
	$page_templater->register('users', $users);
	$page_templater->register('reportusers', $reportusers);	
	$page_templater->register('template_hook', $template_hook);	
$HTML = $page_templater->render();