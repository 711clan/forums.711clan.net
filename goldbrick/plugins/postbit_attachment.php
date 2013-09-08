<?php
/**
 * Enables attachments to use Goldbrick
 * 
 * @active      	true
 * @version     	$Revision: 87 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-21 22:21:30 -0700 (Sun, 21 Oct 2007) $
 */
$forumid = $this->registry->GPC['forumid'];

if ($this->registry->forumcache[$forumid]['gb_enabled'])
{
	if ($attachment['visible'] AND $this->registry->options['gb_enabled'] AND $this->registry->options['gb_attach'])
	{
		
		$this->registry->input->clean_array_gpc('r', array(
			'gb_options_on'		=> TYPE_BOOL,
			'gb_title'			=> TYPE_NOHTML,
			'gb_width'			=> TYPE_UINT,
			'gb_height'			=> TYPE_UINT,
			'gb_autoplay'		=> TYPE_BOOL,
			'gb_loop'			=> TYPE_BOOL
		));
		
		if ($this->registry->GPC['gb_options_on'])
		{
		
			$gb_options = array(
				'use'		=> $this->registry->GPC['gb_options_on'],
				'title'		=> $this->registry->GPC['gb_title'], 
				'width'		=> $this->registry->GPC['gb_width'], 
				'height'	=> $this->registry->GPC['gb_height'], 
				'autoplay'	=> $this->registry->GPC['gb_autoplay'], 
				'loop'		=> $this->registry->GPC['gb_loop']
			);
		}
		
		require_once(DIR . '/goldbrick/includes/functions_public.php');
		
		

		$info = goldbrick_process_attachment(
			$attachment['attachmentid'], 
			$this->registry->userinfo['userid'], 
			$post['postid'], 
			$attachment['attachmentextension'],
			$gb_options
		);
		
		eval('$attachment[goldbrick] = "'  . fetch_template('gb_player') . '";');
	}
}?>