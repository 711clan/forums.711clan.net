<?php
/*======================================================================*\
|| # ipInfo v. 1.2 for vBulletin 3.5                                  # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2004 Jordi Romkema. All Rights Reserved.              # ||
|| # This file may not be redistributed in whole or significant part. # ||
==========================================================================
      Ported and updated: Zachariah - http://www.gzhq.net
\*======================================================================*/


// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'ipinfo');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
  'ipinfo',
  'ipinfo_sharingip',
  'ipinfo_otherip'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

function construct_ipinfo_nav($foruminfo, $threadinfo)
{
	global $session, $forumcache, $vbphrase;
	$navbits = array();
	$parentlist = array_reverse(explode(',', substr($foruminfo['parentlist'], 0, -3)));
	foreach ($parentlist AS $forumID)
	{
		$forumTitle = $forumcache["$forumID"]['title'];
		$navbits["forumdisplay.php?$session[sessionurl]f=$forumID"] = $forumTitle;
	}
	$navbits["showthread.php?$session[sessionurl]t=$threadinfo[threadid]"] = $threadinfo['title'];
  $navbits[''] = $vbphrase['user_ipinfo'];

	return construct_navbits($navbits);
}

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// Get post & thread information
$postid = verify_id('post', $_REQUEST['postid']);

// Is user allowed to view ips?
if (!can_moderate($threadinfo['forumid'], 'canviewips'))
{
	print_no_permission();
}

// IP for this post
$postinfo['hostaddress'] = @gethostbyaddr($postinfo['ipaddress']);

//print("IP FOR THIS POST:<br/>");
//print($postinfo['ipaddress'] . " (" . $postinfo['hostaddress'] . ")<br/><br/>");


//print("USERS THAT HAVE POSTED WITH THE SAME IP:<br/>");

// Users that have posted with the same IP
$sameipusers = array();
$datecut = TIMENOW - (86400*$vbulletin->options['ipinfo']);
// Search posts first
$users = $db->query("
  SELECT post.username, post.userid
  FROM " . TABLE_PREFIX . "post AS post
  WHERE post.ipaddress = '" . $postinfo['ipaddress'] . "'
  AND post.userid != '" . $postinfo['userid'] . "'
  AND dateline >= $datecut
  GROUP BY post.username
  ORDER BY post.username ASC
");

while ($sameipinfo = $db->fetch_array($users))
{
  $sameipusers[$sameipinfo['userid']] = $sameipinfo['username'];
}

$db->free_result($users);

// Search user accounts
$users = $db->query("
  SELECT user.username, user.userid
  FROM " . TABLE_PREFIX . "user AS user
  WHERE user.ipaddress = '" . $postinfo['ipaddress'] . "'
  AND user.userid != '" . $postinfo['userid'] . "'
  ORDER BY user.username ASC
");

while ($sameipinfo = $db->fetch_array($users))
{
  $sameipusers[$sameipinfo['userid']] = $sameipinfo['username'];
}

$db->free_result($users);

if (empty($sameipusers)) {
  $show['sharingips'] = false;
} else {
  $show['sharingips'] = true;

  $sameipusers = array_unique($sameipusers);
  asort($sameipusers);

  $sharingipbits = '';
  foreach ($sameipusers as $ip_userid => $ip_username)
  {
    eval('$sharingipbits .= "' . fetch_template('ipinfo_sharingip') . '";');
  }
}

// IPs the user has posted with
// $datecut = TIMENOW - (($vbulletin->GPC['days']*24*60*60) + ($vbulletin->GPC['hours']*60*60) + ($vbulletin->GPC['minutes']*60) + $vbulletin->GPC['seconds']);
//$datecut = ".TIMENOW - (86400*$vbulletin->options['info']);

//print("<br/>OTHER IPS THE USER POSTED WITH:<br/>");
$otherips = $db->query("
  SELECT post.ipaddress, COUNT(*) AS ipcount
  FROM " . TABLE_PREFIX . "post AS post
  WHERE post.userid = '" . $postinfo['userid'] . "'
  AND dateline >= $datecut
  GROUP BY post.ipaddress
  ORDER BY ipcount DESC
");

while ($otheripinfo = $db->fetch_array($otherips))
{
  //print($otheripinfo['ipcount'] . ": " . $otheripinfo['ipaddress'] . "<br/>");
  eval('$otheripbits .= "' . fetch_template('ipinfo_otherip') . '";');
}

$db->free_result($otherips);

$navbits = construct_ipinfo_nav($foruminfo, $threadinfo);
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('ipinfo') . '");');

?>
