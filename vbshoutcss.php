<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.0 Patch Level 2 - Licence Number VBS9D7F856
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2012 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'css');
define('CSRF_PROTECTION', true);
define('NOPMPOPUP', 1);
define('NOCOOKIES', 1);
define('NONOTICES', 1);
define('NOHEADER', 1);
define('NOSHUTDOWNFUNC', 1);
define('LOCATION_BYPASS', 1);

define('NOCHECKSTATE', 1);
define('SKIP_SESSIONCREATE', 1);

// Immediately send back the 304 Not Modified header if this css is cached, don't load global.php
if ((!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) OR !empty($_SERVER['HTTP_IF_NONE_MATCH'])))
{
	$sapi_name = php_sapi_name();
	if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi')
	{
		header('Status: 304 Not Modified');
	}
	else
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
	}
	// remove the content-type and X-Powered headers to emulate a 304 Not Modified response as close as possible
	header('Content-Type:');
	header('X-Powered-By:');
	exit;
}

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// vbshout css files
$vbshoutcss = array(
	'dbtech_vbshout.css',
	'dbtech_vbshout_colours.css',
	'dbtech_vbshout_editor.css',
);

// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// get global templates - hack to avoid erasure
$globaltemplates = $vbshoutcss;

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

header('Content-Type: text/css');

if (empty($vbshoutcss))
{
	exec_shut_down();
	
	echo "/* Unable to find css sheet */";
}
else
{
	$templates = '';
	$count = 0;
	foreach ($vbshoutcss AS $template)
	{
		if ($count > 0)
		{
			$templates .= "\r\n\r\n";
		}

		$template = vB_Template::create($template)->render(true);
		if ($count > 0)
		{
			$template = preg_replace("#@charset .*#i", "", $template);
		}
		$templates .= $template;
		$count++;
	}

	exec_shut_down();

	header('Pragma:');
	header('Cache-control: max-age=31536000');
	header('Expires: ' . gmdate("D, d M Y H:i:s", TIMENOW + 31536000) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $style['dateline']) . ' GMT');

	echo $templates;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 09:05, Fri Jul 27th 2012
|| # CVS: $RCSfile$ - $Revision: 30573 $
|| ####################################################################
\*======================================================================*/