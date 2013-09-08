<?php
if (method_exists($dbtech_vbshout_nav, 'register'))
{
	// Register important variables
	$dbtech_vbshout_nav->register('navclass', 		$navclass);
	$dbtech_vbshout_nav->register('template_hook', 	$template_hook);
	
	$template_hook['usercp_navbar_bottom'] .= $dbtech_vbshout_nav->render();
}
?>