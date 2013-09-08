<?php
global $vbulletin, $vbphrase;
if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND
	$this->fetch_field('state') == 'visible' AND
	!$this->condition
)
{
	// Ensure we got this
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!((int)$instance['options']['notices'] & 8))
		{
			// We're not showing blog alerts
			continue;
		}
		
		if ($instance['bbcodepermissions_parsed']['bit'] & 64)
		{
			$notif = '[URL="' . $vbulletin->options['bburl'] . '/blog.php?bt=' . intval($this->fetch_field('firstblogtextid')) . '"]' . $this->fetch_field('title') . '[/URL]';
		}
		else
		{
			// We can't, so don't even bother
			$notif = $this->fetch_field('title');
		}
		
		$type = 'blog';
		
		// Init the Shout DM
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);
			$shout->set('message', construct_phrase(
				$vbphrase["dbtech_vbshout_notif_$type"], $notif
			))
			->set('type', VBSHOUT::$shouttypes['notif'])
			->set('instanceid', $instanceid);
		$shoutid = $shout->save();
		
		unset($shout);

		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_notification')) ? eval($hook) : false;
	}
	
	$currblogs = $vbulletin->db->query_first("
		SELECT COUNT(*) AS blogs
		FROM " . TABLE_PREFIX . "blog 
		WHERE postedby_userid = " . $vbulletin->userinfo['userid']
	);
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!$instance['options']['blogping_interval'])
		{
			// Not having notices here
			continue;
		}

		if ($currblogs['blogs'] % $instance['options']['blogping_interval'] != 0)
		{
			// We only want matching intervals
			continue;
		}
		
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);		
			$shout->set('message', construct_phrase(
				$vbphrase["dbtech_vbshout_has_reached_x_blog_entries"],
				$currblogs['blogs']
			))
			->set('instanceid', $instanceid)
			->set('type', VBSHOUT::$shouttypes['notif']);
		$shout->save();
		unset($shout);
	}	
}
?>