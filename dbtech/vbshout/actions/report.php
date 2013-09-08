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
$shoutid = $vbulletin->input->clean_gpc('r', 'shoutid', TYPE_UINT);

if (!$shoutinfo = $db->query_first("
	SELECT
		*,
		user.username,
		user.usergroupid,
		user.membergroupids,
		user.infractiongroupid,
		user.displaygroupid
		" . ($vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . "
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
	LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
	WHERE shoutid = " . $db->sql_prepare($shoutid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_shoutid_specified')));
}

if (!$instance = VBSHOUT::$cache['instance']["$shoutinfo[instanceid]"])
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

if (!$instance['permissions_parsed']['canviewshoutbox'])
{
	// Invalid chat room
	print_no_permission();
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

// fetch the markup-enabled username
fetch_musername($shoutinfo);

// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_reporting_shout'], $shoutinfo['shoutid']);

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_report');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('shoutinfo', $shoutinfo);
$HTML = $page_templater->render();