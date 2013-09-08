<?php
$fetchtype = (VBSHOUT::$fetchtype == 'r' ? $_REQUEST : $_POST);

if (strpos($fetchtype['do'], 'dbtech_vbshout_') !== false)
{
	if (!$vbulletin->userinfo['dbtech_vbshout_banned'])
	{
		// Handle ajax request
		VBSHOUT::ajax_handler($fetchtype['do']);
	}
	else
	{
		VBSHOUT::outputXML(array(
			'error' => $vbphrase['dbtech_vbshout_banned']
		));
	}
}
?>