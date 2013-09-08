<?php
if (!class_exists('vB_Template'))
{
	// Ensure we have this
	require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
}

// Create our nav template
$dbtech_vbshout_nav = vB_Template::create('dbtech_vbshout_usercp_nav_link');

// We're not banned and shoutbox is active
$cells[] = 'dbtech_vbshout_options';
$cells[] = 'dbtech_vbshout_ignorelist';
$cells[] = 'dbtech_vbshout_customcommands';
?>