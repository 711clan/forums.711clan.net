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

// Grab the instance id
$instanceid = $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);

// Shorthand
$instance = VBSHOUT::$cache['instance']["$instanceid"];

if (!$instance)
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

if (!$instance['permissions_parsed']['canmodchat'])
{
	// Invalid chat room
	print_no_permission();
}

// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_reports_in_x'], $instance['name']);

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_reportlist');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('permissions', $instance['permissions_parsed']);
	
$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);

// Shorthands to faciliate easy copypaste
$pagenumber = ($pagenumber ? $pagenumber : 1);
$perpage = ($perpage ? $perpage : 25);

// Count number of entries
$entries = $db->query_first_slave("
	SELECT COUNT(*) AS totalentries
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report
	WHERE instanceid IN(0, $instanceid)
");

// Ensure every result is as it should be
sanitize_pageresults($entries['totalentries'], $pagenumber, $perpage);

// Find out where to start
$startat = ($pagenumber - 1) * $perpage;

// Constructs the page navigation
$pagenav = construct_page_nav(
	$pagenumber,
	$perpage,
	$entries['totalentries'],
	'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . "do=reportlist",
	"&amp;instanceid=$instanceid&amp;perpage=$perpage"
);

// Page navigation registration
$page_templater->register('pagenav', $pagenav);

// Array of all active users
$userbits = '';

// Fetch activeusers
$activeusers_q = $db->query_read_slave("
	SELECT
		vbshout.*,
	
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
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report AS vbshout
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
	LEFT JOIN " . TABLE_PREFIX . "user AS reporter ON(reporter.userid = vbshout.reportuserid)
	WHERE vbshout.instanceid = " . intval($instanceid) . "
	ORDER BY handled ASC
	LIMIT $startat, " . $perpage
);

while ($activeusers_r = $db->fetch_array($activeusers_q))
{
	// Setup array for reportd by user
	$reportuser = array(
		'userid'					=> $activeusers_r['reportuserid'],
		'username' 					=> $activeusers_r['reportusername'],
		'usergroupid' 				=> $activeusers_r['reportusergroupid'],
		'infractiongroupid' 		=> $activeusers_r['reportinfractiongroupid'],
		'displaygroupid' 			=> $activeusers_r['reportdisplaygroupid'],
		'dbtech_vbshop_purchase' 	=> $activeusers_r['reportpurchase']
	);
	
	// Initialise BBCode Permissions
	$permarray = array(
		'permissions_parsed' 		=> VBSHOUT::loadInstancePermissions($instance, $reportuser),
		'bbcodepermissions_parsed' 	=> VBSHOUT::loadInstanceBbcodePermissions($instance, $reportuser)
	);
	
	// By default, we can't pm or edit
	$canpm = $canedit = false;

	// fetch the markup-enabled username
	fetch_musername($activeusers_r);
	
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
		$users = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $activeusers_r['userid'] . '" target="_blank">' . $activeusers_r['username'] . '</a>';
	}
	else
	{
		$users = '<a href="' . fetch_seo_url('member', $activeusers_r) . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
	}
	
	$templater = vB_Template::create('dbtech_vbshout_reportlist_bit');
		$templater->register('users', $reportusers);
		$templater->register('reportusers', $users);
		$templater->register('info', $activeusers_r);
	$userbits .= $templater->render();	
}
$db->free_result($activeusers_q);
unset($activeusers_r);		
	
	$page_templater->register('userbits', $userbits);
$HTML = $page_templater->render();