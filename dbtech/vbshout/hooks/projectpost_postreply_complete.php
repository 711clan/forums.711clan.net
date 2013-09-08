<?php
if (!$issuenotedata->condition)
{
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!((int)$instance['options']['notices'] & 16))
		{
			// We're not showing PT alerts
			continue;
		}
		
		if ($instance['bbcodepermissions_parsed']['bit'] & 64)
		{
			// We can use BBCode
			$notif1 = '[URL="' . $vbulletin->options['bburl'] . '/project.php?' . $vbulletin->session->vars['sessionurl'] . 'do=gotonote&issuenoteid=' . $issuenote['issuenoteid'] . '"]' . $issue['title'] . '[/URL]';
			$notif2 = '[URL="' . $vbulletin->options['bburl'] . '/project.php?' . $vbulletin->session->vars['sessionurl'] . 'projectid=' . $project['projectid'] . '"]' . $project['title'] . '[/URL]';
		}
		else
		{
			// We can't, so don't even bother
			$notif1 = $issue['title'];
			$notif2 = $project['title'];
		}
		
		// Init the Shout DM
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set_info('automated', true);
			$shout->set('message', construct_phrase(
				$vbphrase["dbtech_vbshout_notif_ptissuereply"],
				$notif1,
				$notif2
			))
			->set('type', VBSHOUT::$shouttypes['notif'])
			->set('instanceid', $instanceid);
		$shoutid = $shout->save();
		unset($shout);
	}
}
?>