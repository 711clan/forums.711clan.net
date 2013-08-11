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

$announcements = $vbulletin->db->query_write("
	SELECT announcementid
	FROM " . TABLE_PREFIX . "announcement
	WHERE enddate >= " . (TIMENOW -  864000) . "
");

$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "postlog WHERE dateline < " . (TIMENOW - ($vbulletin->options['postlog_maxage'] * 60 * 60 * 24)));

if ($vbulletin->options['tagcloud_searchhistory'])
{
	$vbulletin->db->query_write("
		DELETE FROM " . TABLE_PREFIX . "tagsearch
		WHERE dateline < " . (TIMENOW - ($vbulletin->options['tagcloud_searchhistory'] * 60 * 60 * 24))
	);
}

// ensure this setting makes sense
if ($vbulletin->options['profilemaxvisitors'] < 2)
{
		$vbulletin->options['profilemaxvisitors'] = 2;
}

// remove profile visits beyond the first $vbulletin->options['profilemaxvisitors']
$rebuild_db = $vbulletin->db->query_read_slave("
	SELECT userid
	FROM " . TABLE_PREFIX . "profilevisitor
	WHERE visible = 1
	GROUP BY userid
	HAVING COUNT(*) > " . $vbulletin->options['profilemaxvisitors'] . "
");

while ($user = $vbulletin->db->fetch_array($rebuild_db))
{
	$entry = $vbulletin->db->query_first("
		SELECT userid, dateline
		FROM " . TABLE_PREFIX . "profilevisitor
		WHERE userid = $user[userid] AND visible = 1
		ORDER BY dateline DESC
		LIMIT " . $vbulletin->options['profilemaxvisitors']. ", 1
	");

	if ($entry)
	{
		$vbulletin->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "profilevisitor
			WHERE userid = $entry[userid] AND visible IN (0,1) AND dateline < $entry[dateline]
		");
	}
}

if ($vbulletin->options['contentread_cleandays'])
{
	$clean_date = TIMENOW - ($vbulletin->options['contentread_cleandays'] * 86400);
	
	$vbulletin->db->query_write("
		DELETE contentread, ipdata
		FROM " . TABLE_PREFIX . "contentread AS contentread
		INNER JOIN " . TABLE_PREFIX . "ipdata AS ipdata USING (ipid)
		WHERE contentread.dateline < $clean_date
	");
}

// Remove expired activity
if ($vbulletin->options['as_expire'])
{
	$value = intval($vbulletin->options['as_expire']);
	if (!$value)
	{
		$value = 3;
	}
	if ($value > 180)
	{
		$value = 180;
	}
	$vbulletin->db->query_write("
		DELETE FROM " . TABLE_PREFIX . "activitystream
		WHERE dateline < " . (TIMENOW - ($value * 60 * 60 * 24))
	);
}

($hook = vBulletinHook::fetch_hook('cron_script_cleanup_daily')) ? eval($hook) : false;

log_cron_action('', $nextitem, 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 62620 $
|| ####################################################################
\*======================================================================*/
?>