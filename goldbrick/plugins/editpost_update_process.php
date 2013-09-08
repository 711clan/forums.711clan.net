<?php 
/**
 * Creates goldbrick media for edit post
 * 
 * @active      	true
 * @execution   	1
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * $lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
if (
		$vbulletin->options['gb_enabled'] AND $foruminfo['gb_enabled']
		AND (
			$vbulletin->userinfo['permissions']['gb_permissions'] &
			$vbulletin->bf_ugp['gb_permissions']['canuse']
			)
	)
{
	require_once(DIR . '/goldbrick/includes/functions_public.php');
	
	$edit['message'] = goldbrick_process_post(
		$edit['message'], 
		$postinfo['userid'], 
		$postinfo['postid'],
		$posthash,
		$gb_options
	);
}
?>