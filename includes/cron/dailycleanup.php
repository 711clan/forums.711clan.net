<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.7 PL1 - Licence Number VBF2470E4F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2007 Jelsoft Enterprises Ltd. All Rights Reserved. ||
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

// SELECT announcements that are active, will be active in the future or were active in the last ten days
$announcements = $vbulletin->db->query_read("
	SELECT announcementid
	FROM " . TABLE_PREFIX . "announcement
	WHERE enddate >= " . (TIMENOW -  864000) . "
");

$anns = array();
while ($ann = $vbulletin->db->fetch_array($announcements))
{
	$anns[] = $ann['announcementid'];
}

// Delete all read markers for announcements expired > 10 days
if (!empty($anns))
{
	$vbulletin->db->query_write("
		DELETE FROM " . TABLE_PREFIX . "announcementread
		WHERE announcementid NOT IN (" . implode(',', $anns) . ")
	");
}

($hook = vBulletinHook::fetch_hook('cron_script_cleanup')) ? eval($hook) : false;

log_cron_action('', $nextitem, 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 18:52, Sat Jul 14th 2007
|| # CVS: $RCSfile$ - $Revision: 15539 $
|| ####################################################################
\*======================================================================*/
?>