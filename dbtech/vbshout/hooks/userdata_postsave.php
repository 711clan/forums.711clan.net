<?php
if (!$this->condition)
{
	global $vbphrase;
	
	$userstats = $this->dbobject->query_first("
		SELECT COUNT(*) AS numbermembers
		FROM " . TABLE_PREFIX . "user
		WHERE usergroupid NOT IN (3,4)		
	");	
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!$instance['options']['memberping_interval'])
		{
			// Not having notices here
			continue;
		}

		if (($userstats['numbermembers']) % $instance['options']['memberping_interval'] != 0)
		{
			// We only want matching intervals
			continue;
		}
		
		$shout = VBSHOUT::initDataManager('Shout', $this->registry, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);
			$shout->set('message', construct_phrase(
				$vbphrase['dbtech_vbshout_is_member_number_y'],
				$userstats['numbermembers']
			))
			->set('instanceid', $instanceid)				
			->set('userid', $this->fetch_field('userid'))				
			->set('type', VBSHOUT::$shouttypes['notif']);
		$shout->save();
		unset($shout);
	}
}
?>