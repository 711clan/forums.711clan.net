<?php
/**
 * Checks a submitted post for cacheable links, caches them, and then wraps them
 * in [media] tags.
 * 
 * @active      	true
 * @execution   	1
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * $lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
if ($vbulletin->options['gb_enabled'])
{
	if ($vbulletin->userinfo['permissions']['gb_permissions'] & $vbulletin->bf_ugp['gb_permissions']['canuse'])
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
		
		$gb_options = array(
			'title'		=> $vbulletin->GPC['gb_title'], 
			'width'		=> $vbulletin->GPC['gb_width'], 
			'height'	=> $vbulletin->GPC['gb_height'], 
			'autoplay'	=> $vbulletin->GPC['gb_autoplay'], 
			'loop'		=> $vbulletin->GPC['gb_loop']
		);
		
		$blog['message'] = goldbrick_process_blog(
			$blog['message'], 
			$vbulletin->userinfo['userid'], 
			0, 
			$posthash,
			$gb_options
		);
		
		$blogman->blog_text['pagetext'] = $blog['message'];
		
	}
}
?>