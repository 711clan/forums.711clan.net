<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.2 Patch Level 2 - Licence Number VBC2DDE4FB
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2013 Jelsoft Enterprises Ltd. All Rights Reserved. ||
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
|| # Downloaded: 20:54, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 13893 $
|| ####################################################################
\*======================================================================*/
?>