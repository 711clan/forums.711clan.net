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

// ########################## REQUIRE BACK-END ############################
require_once(DIR . '/includes/functions_digest.php');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

// send daily digest of new posts in threads and threads in forums
exec_digest(2);

log_cron_action('', $nextitem, 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/
?>