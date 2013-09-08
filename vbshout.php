<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'vbshout');
define('IN_VBSHOUT', true);

if (isset($_REQUEST['do']) AND $_REQUEST['do'] == 'ajax')
{
	define('CSRF_PROTECTION', true);
	define('LOCATION_BYPASS', 1);
	define('NOPMPOPUP', 1);
	define('VB_ENTRY', 'ajax.php');
	define('SESSION_BYPASS', true);
	define('VB_ENTRY_TIME', microtime(true));
}

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('dbtech_vbshout', 'user', 'posting', 'album', 'messaging');

// get templates used by all actions
$globaltemplates = array(
	'dbtech_vbshout',
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'detach' => array(
		'dbtech_vbshout_menu'
	),
	'archive' => array(
		'dbtech_vbshout_css',
		'dbtech_vbshout_archive',
		'dbtech_vbshout-archive-ie'
	),
	'profile' => array(
		'USERCP_SHELL',
		'usercp_nav_folderbit',
	),
);

// get special data templates from the datastore
require_once('./dbtech/vbshout/includes/specialtemplates.php');
$specialtemplates = $extracache;

// ############################### default do value ######################
if (empty($_REQUEST['do']))
{
	//$_REQUEST['do'] = 'main';
	$_REQUEST['do'] = $_GET['do'] = 'archive';
}

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

if (!class_exists('vB_Template'))
{
	// Ensure we have this
	require_once(DIR . '/dbtech/vbsupport/includes/class_template.php');
}

if (!class_exists('VBSHOUT'))
{
	eval(standard_error($vbphrase['dbtech_vbshout_deactivated']));
}

if ($_REQUEST['do'] == 'devinfo' AND $_REQUEST['devkey'] == 'dbtech')
{
	VBSHOUT::outputJSON(array(
		'version' 		=> VBSHOUT::$version,
		'versionnumber' => VBSHOUT::$versionnumber,
		'pro'			=> VBSHOUT::$isPro,
		'vbversion'		=> $vbulletin->versionnumber
	));
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

if (!empty($_POST['do']))
{
	// $_POST requests take priority
	$action = $_POST['do'];
}
else if (!empty($_GET['do']))
{
	// We had a GET request instead
	$action = $_GET['do'];
}
else
{
	// No request
	$action = 'main';
}

// Strip non-valid characters
$action = preg_replace('/[^\w-]/i', '', $action);

// Core page template
$page_template = 'dbtech_vbshout';

if (!$vbulletin->options['dbtech_vbshout_active'])
{
	// Sb is shut off
	eval(standard_error($vbulletin->options['dbtech_vbshout_closedreason']));
}

// begin navbits
//$navbits = array('vbshout.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['dbtech_vbshout_shoutbox']);
$navbits = array();

$includecss = array(
	'vbulletin-chrome' 			=> 'vbulletin-chrome.css',
	'member' 					=> 'member.css',
	'dbtech_vbshout-archive' 	=> 'dbtech_vbshout-archive.css',
);	

if (!function_exists('fetch_tag_list'))
{
	require_once(DIR . '/includes/class_bbcode.php');
}

// Store all possible BBCode tags
VBSHOUT::$tag_list = fetch_tag_list('', true);

if (!file_exists(DIR . '/dbtech/vbshout/actions/' . $action . '.php'))
{
	if (!file_exists(DIR . '/dbtech/vbshout_pro/actions/' . $action . '.php'))
	{
		// Throw error from invalid action
		eval(standard_error(fetch_error('dbtech_vbshout_error_x', $vbphrase['dbtech_vbshout_invalid_action'])));
	}
	else
	{
		// Include the selected file
		include_once(DIR . '/dbtech/vbshout_pro/actions/' . $action . '.php');	
	}
}
else
{
	// Include the selected file
	include_once(DIR . '/dbtech/vbshout/actions/' . $action . '.php');	
}

if ($_REQUEST['do'] == 'archive')
{
	// Prepare IE6 CSS fixes
	$headinclude .= vB_Template::create('dbtech_vbshout-archive-ie')->render();
}

if (intval($vbulletin->versionnumber) == 3)
{
	// Create navbits
	$navbits = construct_navbits($navbits);	
	eval('$navbar = "' . fetch_template('navbar') . '";');	
}
else
{
	$navbar = render_navbar_template(construct_navbits($navbits));	
}

// Finish the main template
$templater = vB_Template::create($page_template);
	$templater->register_page_templates();
	$templater->register('navclass', 		$navclass);
	$templater->register('HTML', 			$HTML);
	$templater->register('navbar', 			$navbar);
	$templater->register('pagetitle', 		$pagetitle);
	$templater->register('pagedescription', $pagedescription);
	$templater->register('template_hook', 	$template_hook);
	$templater->register('includecss', 		$includecss);
	$templater->register('year',			date('Y'));
	$templater->register('jQueryVersion',	VBSHOUT::$jQueryVersion);
	$templater->register('jQueryPath',		VBSHOUT::jQueryPath());
	$templater->register('version',			VBSHOUT::$version);
	$templater->register('versionnumber', 	VBSHOUT::$versionnumber);
	$templater->register('headinclude',		$headinclude);
print_output($templater->render());