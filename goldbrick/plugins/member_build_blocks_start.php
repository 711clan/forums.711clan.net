<?php
if (version_compare($vbulletin->options['templateversion'], '3.7.0 Alpha 1', '>='))
{
	if ($vbulletin->options['gb_enabled'])
	{
		$gb_member_media = $userinfo['gb_profile_video'];

		$title = 'My Video';

		require_once(DIR . '/goldbrick/includes/functions_public.php');

		$gb_member_display = goldbrick_process_profile(
			$gb_member_media, 
			$vbulletin->userinfo['userid'], 
			0, 
			0,
			0
		);

		$blocklist = array_merge($blocklist, array(
			'goldbrick' => array(
				'class' => 'goldbrick',
				'title' => 'My Media',
				'hook_location' => 'profile_left_last'
			)
		));
		
		class vB_ProfileBlock_Goldbrick extends vB_ProfileBlock
		{
			var $template_name = 'gb_profile_video';

			function confirm_empty_wrap()
			{
				return false;
			}

			function confirm_display()
			{
				return true;
			}

			function prepare_output($id = '', $options = array())
			{
				global $gb_member_media;

				$this->block_data['media']  = goldbrick_start_delivery($gb_member_media, $gb_options = null);
			}
		}	
	}
}
?>