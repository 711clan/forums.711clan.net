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

if (!$vbulletin->userinfo['userid'])
{
	print_no_permission();
}

// Grab the shout id
$shoutid = $vbulletin->input->clean_gpc('p', 'shoutid', TYPE_UINT);

// Grab the report reason
$reportreason = $vbulletin->input->clean_gpc('p', 'reportreason', TYPE_NOHTML);

if (!$shoutinfo = $db->query_first("
	SELECT userid, message, instanceid
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
	WHERE shoutid = " . $db->sql_prepare($shoutid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_shoutid_specified')));
}

// Shorthand
$instance = VBSHOUT::$cache['instance']["$shoutinfo[instanceid]"];

if (!$instance AND $shoutinfo['instanceid'] != 0)
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

if ($exists = $db->query_first("
	SELECT shoutid
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report
	WHERE shoutid = " . $db->sql_prepare($shoutid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_already_reported')));
}

$db->query_write("
	INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_report
		(userid, reportuserid, shoutid, shout, reportreason, instanceid)
	VALUES (
		" . $db->sql_prepare($shoutinfo['userid']) . ",
		" . $db->sql_prepare($vbulletin->userinfo['userid']) . ",
		" . $db->sql_prepare($shoutid) . ",
		" . $db->sql_prepare($shoutinfo['message']) . ",
		" . $db->sql_prepare($reportreason) . ",
		" . $db->sql_prepare($shoutinfo['instanceid']) . "
	)
");

$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=archive&instanceid=' . $shoutinfo['instanceid'];
eval(print_standard_redirect('redirect_dbtech_vbshout_report_added'));