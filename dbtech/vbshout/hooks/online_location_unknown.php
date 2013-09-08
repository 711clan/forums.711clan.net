<?php
if (strpos($userinfo['activity'], 'dbtech_vbshout_') === 0)
{
	$handled = true;	
	switch ($userinfo['activity'])
	{
		case 'dbtech_vbshout_archive':
			// Archive HO
			$userinfo['action'] = $vbphrase['dbtech_vbshout_viewing_archive'];
			break;
			
		default:
			$handled = false;
			break;
	}
}
?>