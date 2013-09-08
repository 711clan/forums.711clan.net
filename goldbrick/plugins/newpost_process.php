<?php
/**
* Checks a submitted post for cacheable links, caches them, and then wraps them
 * in [media] tags.
 * 
 **/

if ($vbulletin->options['gb_enabled'])
{
	if ($foruminfo['gb_enabled'] AND
		($vbulletin->userinfo['permissions']['gb_permissions'] & $vbulletin->bf_ugp['gb_permissions']['canuse']))
	{
		require_once(DIR . '/goldbrick/includes/functions_public.php');

		$vbulletin->input->clean_array_gpc('r', array(
			'gb_options_on'		=> TYPE_BOOL,
			'gb_title'			=> TYPE_NOHTML,
			'gb_width'			=> TYPE_UINT,
			'gb_height'			=> TYPE_UINT,
			'gb_autoplay'		=> TYPE_BOOL,
			'gb_loop'			=> TYPE_BOOL
		));
		
		if ($vbulletin->GPC['gb_options_on'])
		{
		
			$gb_options = array(
				'use'		=> $vbulletin->GPC['gb_options_on'],
				'title'		=> $vbulletin->GPC['gb_title'], 
				'width'		=> $vbulletin->GPC['gb_width'], 
				'height'	=> $vbulletin->GPC['gb_height'], 
				'autoplay'	=> $vbulletin->GPC['gb_autoplay'], 
				'loop'		=> $vbulletin->GPC['gb_loop']
			);
		}

		$post['message'] = goldbrick_process_post(
			$post['message'], 
			$vbulletin->userinfo['userid'], 
			0, 
			$post['posthash'],
			$gb_options
		);
	}
}
?>