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

// Grab the chat room id
$chatroomid = $vbulletin->input->clean_gpc('r', 'chatroomid', TYPE_UINT);

// Shorthand
$chatroom = VBSHOUT::$cache['chatroom']["$chatroomid"];

if (!$chatroom)
{
	// Invalid chat room
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_chatroomid_specified')));
}

if (!$instance['options']['enableaccess'])
{
	// Gtfo.
	eval(standard_error(fetch_error('dbtech_vbshout_cannot_access_chatroom')));
}

// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_users_with_access_x'], VBSHOUT::$cache['chatroom']["$chatroomid"]['title']);

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_chataccess');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('permissions', $instance['permissions_parsed']);
	
if ($chatroom['membergroupids'])
{
	// Get all membergroupids
	$membergroupids = explode(',', $chatroom['membergroupids']);
	
	if (!is_member_of($vbulletin->userinfo, $membergroupids) AND !$instance['permissions_parsed']['canmodchat'])
	{
		// Gtfo.
		eval(standard_error(fetch_error('dbtech_vbshout_cannot_access_chatroom')));
	}
	
	$findsets = array('FIND_IN_SET(-1, membergroupids)');
	foreach ($membergroupids as $membergroupid)
	{
		// Store the set
		$findsets[] = 'FIND_IN_SET(' . $membergroupid . ', membergroupids)';
	}
	
	// Query active users
	$SQL = "
		SELECT COUNT(*) AS totalentries
		FROM " . TABLE_PREFIX . "user AS user
		WHERE usergroupid IN(" . $chatroom['membergroupids'] . ")
			OR (" . implode(' OR ', $findsets) . ")
		ORDER BY username ASC
	";
}
else
{
	// Get memberships
	$memberof = VBSHOUT::fetch_chatroom_memberships($vbulletin->userinfo, '1', $instance['instanceid']);
	
	if (!in_array($chatroomid, $memberof) AND !$instance['permissions_parsed']['canmodchat'])
	{
		// Gtfo.
		eval(standard_error(fetch_error('dbtech_vbshout_cannot_access_chatroom')));
	}	
	
	// Query active users
	$SQL = "
		SELECT COUNT(*) AS totalentries
		FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember AS vbshout
		WHERE chatroomid = " . intval($chatroomid)
	;
}

$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);

// Shorthands to faciliate easy copypaste
$pagenumber = ($pagenumber ? $pagenumber : 1);
$perpage = ($perpage ? $perpage : 25);

// Count number of entries
$entries = $db->query_first_slave($SQL);

// Ensure every result is as it should be
sanitize_pageresults($entries['totalentries'], $pagenumber, $perpage);

// Find out where to start
$startat = ($pagenumber - 1) * $perpage;

// Constructs the page navigation
$pagenav = construct_page_nav(
	$pagenumber,
	$perpage,
	$entries['totalentries'],
	'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . "do=chataccess",
	"&amp;instanceid=$instanceid&amp;chatroomid=$chatroomid&amp;perpage=$perpage"
);

// Page navigation registration
$page_templater->register('pagenav', $pagenav);

if ($chatroom['membergroupids'])
{
	$SQL = "
		SELECT
			user.userid,
			username,
			usergroupid,
			infractiongroupid,
			displaygroupid
			" . ($vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . "
		FROM " . TABLE_PREFIX . "user AS user
		WHERE usergroupid IN(" . $chatroom['membergroupids'] . ")
			OR (" . implode(' OR ', $findsets) . ")
		ORDER BY username ASC
		LIMIT $startat, " . $perpage		
	;
}
else
{
	$SQL = "
		SELECT
			user.userid,
			user.username,
			user.usergroupid,
			user.infractiongroupid,
			user.displaygroupid" . ($vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . ",
			
			inviter.userid AS inviteuserid,
			inviter.username AS inviteusername,
			inviter.usergroupid AS inviteusergroupid,
			inviter.infractiongroupid AS inviteinfractiongroupid,
			inviter.displaygroupid AS invitedisplaygroupid
			" . ($vbulletin->products['dbtech_vbshop'] ? ", inviter.dbtech_vbshop_purchase AS invitepurchase" : '') . "
		FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember AS vbshout
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
		LEFT JOIN " . TABLE_PREFIX . "user AS inviter ON(inviter.userid = vbshout.invitedby)
		WHERE vbshout.chatroomid = " . intval($chatroomid) . "
		ORDER BY username ASC
		LIMIT $startat, " . $perpage		
	;
}

// Array of all active users
$userbits = '';

// Fetch activeusers
$activeusers_q = $db->query_read_slave($SQL);
while ($activeusers_r = $db->fetch_array($activeusers_q))
{
	// fetch the markup-enabled username
	fetch_musername($activeusers_r);
	
	// Setup array for invited by user
	$inviteuser = array(
		'userid'					=> $activeusers_r['inviteuserid'],
		'username' 					=> $activeusers_r['inviteusername'],
		'usergroupid' 				=> $activeusers_r['inviteusergroupid'],
		'infractiongroupid' 		=> $activeusers_r['inviteinfractiongroupid'],
		'displaygroupid' 			=> $activeusers_r['invitedisplaygroupid'],
		'dbtech_vbshop_purchase' 	=> $activeusers_r['invitepurchase']
	);
	
	if ($inviteuser['userid'])
	{
		// fetch the markup-enabled username
		fetch_musername($inviteuser);
		
		// Fetch the SEO'd URL to a member's profile
		if (intval($vbulletin->versionnumber) == 3)
		{
			$inviteusers = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $inviteuser['userid'] . '" target="_blank">' . $inviteuser['username'] . '</a>';
		}
		else
		{
			$inviteusers = '<a href="' . fetch_seo_url('member', $inviteuser) . '" target="_blank">' . $inviteuser['musername'] . '</a>';
		}
	}
	else
	{
		// Didn't exist
		$inviteusers = 'N/A';
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
	
	$creator = false;
	if (!$chatroom['membergroupids'])
	{
		$creator = ($chatroom['creator'] == $vbulletin->userinfo['userid'] OR $instance['permissions_parsed']['canmodchat'] ? true : false);
	}
	
	$templater = vB_Template::create('dbtech_vbshout_chataccess_bit');
		$templater->register('users', $users);
		$templater->register('inviteusers', $inviteusers);
		$templater->register('userinfo', $activeusers_r);
		$templater->register('chatroom', $chatroom);
		$templater->register('instance', $instance);
		$templater->register('creator', $creator);
		$templater->register('removable', ($creator AND $activeusers_r['userid'] != $vbulletin->userinfo['userid'] AND $activeusers_r['userid'] != $chatroom['creator']));
	$userbits .= $templater->render();	
}
$db->free_result($activeusers_q);
unset($activeusers_r);		
	
	$page_templater->register('chatroom', $chatroom);
	$page_templater->register('creator', $creator);
	$page_templater->register('userbits', $userbits);
$HTML = $page_templater->render();