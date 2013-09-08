<?php
global $vbulletin, $vbphrase;
if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND
	$this->fetch_field('state') == 'visible' AND
	!$this->condition
)
{
	$title = $vbulletin->db->query_first("SELECT title FROM " . TABLE_PREFIX . "blog WHERE blogid = " . intval($this->fetch_field('blogid')));
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!((int)$instance['options']['notices'] & 8))
		{
			// We're not showing blog alerts
			continue;
		}

		if ($instance['bbcodepermissions_parsed']['bit'] & 64)
		{
			$notif = '[URL="' . $vbulletin->options['bburl'] . '/blog.php?bt=' . intval($this->fetch_field('blogtextid')) . '#comment' . intval($this->fetch_field('blogtextid')) . '"]' . $title['title'] . '[/URL]';
		}
		else
		{
			// We can't, so don't even bother
			$notif = $title['title'];
		}
		
		$type = 'blogcomment';
		
		// Init the Shout DM
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);		
			$shout->set('message', construct_phrase(
				$vbphrase["dbtech_vbshout_notif_$type"], $notif
			))
			->set('type', VBSHOUT::$shouttypes['notif'])
			->set('instanceid', $instanceid);
		
		// Get the shout id
		$shoutid = $shout->save();
		
		unset($shout);
		
		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_notification')) ? eval($hook) : false;
	}
}
?>