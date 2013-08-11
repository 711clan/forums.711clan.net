<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.1 - Licence Number VBC2DDE4FB
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2013 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);


// #############################################################################
/**
* Caches social bookmark site data to the datastore
*/
function build_bookmarksite_datastore()
{
	global $vbulletin;
	
	$vbulletin->bookmarksitecache = array();
	
	$bookmarksitelist = $vbulletin->db->query_read("
		SELECT *  
		FROM " . TABLE_PREFIX . "bookmarksite AS bookmarksite
		WHERE active = 1
		ORDER BY displayorder ASC, bookmarksiteid ASC
	");
	if ($bookmarksitelist)
	{
		while ($bookmarksite = $vbulletin->db->fetch_array($bookmarksitelist))
		{
			$vbulletin->bookmarksitecache["$bookmarksite[bookmarksiteid]"] = $bookmarksite;
		}
	}

	// store the cache array into the database
	build_datastore('bookmarksitecache', serialize($vbulletin->bookmarksitecache), 1);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/
?>