<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.1 - Licence Number VBC2DDE4FB
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2013 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$today = date('m-d', TIMENOW);

$ids = '0';
foreach($vbulletin->usergroupcache AS $usergroupid => $usergroup)
{
	if ($usergroup['genericoptions'] & $vbulletin->bf_ugp_genericoptions['showbirthday'] AND $usergroup['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'] AND !in_array($usergroup['usergroupid'], array(1, 3, 4)))
	{
		$ids .= ",$usergroupid";
	}
}

$birthdays = $vbulletin->db->query_read("
	SELECT username, email, languageid
	FROM " . TABLE_PREFIX . "user
	WHERE birthday LIKE '$today-%' AND
	(options & " . $vbulletin->bf_misc_useroptions['adminemail'] . ") AND
	usergroupid IN ($ids)
");

vbmail_start();

while ($userinfo = $vbulletin->db->fetch_array($birthdays))
{
	$username = unhtmlspecialchars($userinfo['username']);
	eval(fetch_email_phrases('birthday', $userinfo['languageid']));
	vbmail($userinfo['email'], $subject, $message);
	$emails .= iif($emails, ', ');
	$emails .= $userinfo['username'];
}

vbmail_end();

if ($emails)
{
	log_cron_action($emails, $nextitem, 1);
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/
?>