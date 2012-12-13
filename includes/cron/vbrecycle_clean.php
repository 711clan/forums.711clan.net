<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBRecycle 3.0.x								    # ||
|| # ---------------------------------------------------------------- # ||
|| # Author: LNTT - Email: toai007@yahoo.com    		          # ||
|| # Website & Demo: http://www.FanFunVN.com/   		          # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################## REQUIRE BACK-END ############################
require_once(DIR . '/includes/functions_log_error.php');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$vbrconfig=$vbulletin->db->query_first("select * from " . TABLE_PREFIX . "vbr_config");

if ($vbrconfig['active']==1)
{
	$vbulletin->GPC['type'] = 'prune';
	$vbulletin->GPC['thread']['forumid'] = $vbrconfig['forumid'];

	$thread = unserialize($vbulletin->GPC['criteria']);

	$fullquery = "
		SELECT *
		FROM " . TABLE_PREFIX . "thread AS thread
		LEFT JOIN " . TABLE_PREFIX . "forum AS forum ON (forum.forumid = thread.forumid)
		WHERE thread.forumid = $vbrconfig[forumid]
	";
	$threads = $vbulletin->db->query_read($fullquery);

		while ($thread = $vbulletin->db->fetch_array($threads))
		{
			$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
			$threadman->set_existing($thread);
			$threadman->delete(0);
			unset($threadman);
			require_once(DIR . '/includes/adminfunctions.php');
			vbflush();
		}
	log_cron_action('vBRecycle Cleanup Completed', $nextitem);
}

/*======================================================================*\
|| #################################################################### ||
|| # End vBRecycle 3.0.x {vbrecycle_clean.php}				    # ||
|| #################################################################### ||
\*======================================================================*/
?>