<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cpuser', 'arcade');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminforums'))
{
	print_cp_no_permission();
}

if ($_REQUEST['do']=='fixdb')
{
	require_once(DIR . '/includes/class_database_analyse.php');
	
	require_once(DIR . '/includes/arcade_dd.php');

	$vdb = new v3Arcade_Database_Analyse($dd);
	
	// Cache all the table names from the database.
	$vdb->cachetables();
	
	// Add all the tables in the data dictionary, if they don't exist.
	$vdb->addtables();
	
	// Checks to make sure that the fields in the current tables are the same 
	// as the ones in the data dictionary, and corrects them if they're not.
	$vdb->syncfields();
	
	echo 'Database updated.';
	exit;
}

if ($_REQUEST['do']=='clean')
{
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "template WHERE title LIKE 'arcade%' AND version LIKE '3.0.%'");
	echo 'Old v3 Arcade templates removed.';
	exit;
}

?>