<?php
if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND
	$post['visible']
)
{
	if (!VBSHOUT::$isPro)
	{
		// Lite-only shit
		$parentlist = explode(',', $foruminfo['parentlist']);
		if ($parentlist[0] == -1)
		{
			// This forum
			$noticeforum = $foruminfo['forumid'];		
		}
		else
		{
			$key = (count($parentlist) - 2);
			$noticeforum = $parentlist["$key"];
		}
	}
	else
	{
		// This forum
		$noticeforum = $foruminfo['forumid'];
	}
	
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if ($type == 'thread')
		{
			if (!((int)$instance['options']['notices'] & 1) OR
				!((int)$instance['notices']["$noticeforum"] & 1)
			)
			{
				// Not showing this
				continue;
			}
		}
		else if ($type == 'reply')
		{
			if (!((int)$instance['options']['notices'] & 2) OR
				!((int)$instance['notices']["$noticeforum"] & 2)
			)
			{
				// Not showing this
				continue;
			}
		}
		
		if ($instance['bbcodepermissions_parsed']['bit'] & 64)
		{
			// We can use BBCode
			switch ($type)
			{
				case 'thread':
					$notif = '[thread=' . $threadinfo['threadid'] . ']' . $threadinfo['title'] . '[/thread]';
					break;
					
				case 'reply':
					$notif = '[post=' . $post['postid'] . ']' . $threadinfo['title'] . '[/post]';
					break;
			}		
		}
		else
		{
			// We can't, so don't even bother
			$notif = $threadinfo['title'];
		}
		
		// Init the Shout DM
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);		
			$shout->set('message', construct_phrase(
				$vbphrase["dbtech_vbshout_notif_$type"], $notif
			))
			->set('type', VBSHOUT::$shouttypes['notif'])
			->set('instanceid', $instanceid)
			->set('forumid', $foruminfo['forumid']);
		$shoutid = $shout->save();
		unset($shout);

		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_notification')) ? eval($hook) : false;
	}
}

if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND	
	$post['visible'] AND	
	$foruminfo['countposts']
)
{
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!$instance['options']['postping_interval'])
		{
			// Not having notices here
			continue;
		}

		if (($vbulletin->userinfo['posts'] + 1) % $instance['options']['postping_interval'] != 0)
		{
			// We only want matching intervals
			continue;
		}
		
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);		
			$shout->set('message', construct_phrase(
				$vbphrase["dbtech_vbshout_has_reached_x_posts"],
				($vbulletin->userinfo['posts'] + 1)
			))
			->set('instanceid', $instanceid)				
			->set('type', VBSHOUT::$shouttypes['notif']);
		$shout->save();
		unset($shout);
	}
	
	if ($type == 'thread')
	{
		$currthreads = $vbulletin->db->query_first("
			SELECT COUNT(*) AS threads
			FROM " . TABLE_PREFIX . "thread 
			WHERE postuserid = " . $vbulletin->userinfo['userid']
		);
		foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
		{
			if (!$instance['options']['threadping_interval'])
			{
				// Not having notices here
				continue;
			}
	
			if (($currthreads['threads'] + 1) % $instance['options']['threadping_interval'] != 0)
			{
				// We only want matching intervals
				continue;
			}
			
			$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
				$shout->set_info('automated', true);
				$shout->set('message', construct_phrase(
					$vbphrase["dbtech_vbshout_has_reached_x_threads"],
					($currthreads['threads'] + 1)
				))
				->set('instanceid', $instanceid)				
				->set('type', VBSHOUT::$shouttypes['notif']);
			$shout->save();
			unset($shout);
		}
	}
}
?>