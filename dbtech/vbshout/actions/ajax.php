<?php
$fetchtype = (VBSHOUT::$fetchtype == 'r' ? $_REQUEST : $_POST);

if (!$vbulletin->userinfo['dbtech_vbshout_banned'])
{
	// Handle ajax request
	VBSHOUT::ajax_handler($fetchtype['action']);
}
else
{
	VBSHOUT::outputXML(array(
		'error' => $vbphrase['dbtech_vbshout_banned']
	));
}
?>