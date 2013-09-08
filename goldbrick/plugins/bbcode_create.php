<?php
/**
 * Defines the behavior for the custom media tag.
 * 
 * @active      	true
 * @hook        	false
 * @version     	$Revision: 102 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-25 02:52:10 -0700 (Thu, 25 Oct 2007) $
 */

if ($this->registry->options['gb_enabled'])
{
	$forumid = $this->registry->GPC['forumid'];

	if (
			$this->registry->forumcache[$forumid]['gb_enabled'] OR in_array(THIS_SCRIPT, array('blog', 'adv_index'))
		AND (
			$this->registry->userinfo['permissions']['gb_permissions'] & 
			$this->registry->bf_ugp['gb_permissions']['canuse']
			)
		)

	{
		$tag = 'media';#$this->registry->options['gb_tag'];

		// [MEDIA]
		$this->tag_list['option'][$tag] = array(
			'callback'          => 'handle_external',
			'strip_empty'       => true,
			'stop_parse'        => true,
			'disable_smilies'   => true,
			'disable_wordwrap'  => true,
			'strip_space_after' => 1,
			'external_callback' => 'handle_bbcode_goldbrick'
		);

		$this->tag_list['no_option'][$tag] = $this->tag_list['option'][$tag];

		if (!function_exists('handle_bbcode_goldbrick'))
		{
			//$text = $this->registry->GPC['message'];
			/**
			 * Handles BBCode [media] (or whatever $tag is)
			 *
			 * @param	object		vB_BbCodeParser
			 * @param	string		Media URL or attachment ID
			 * @param	string		Custom media options
			 * 
			 * @return	string		Rendered media HTML
			 */
	
			function handle_bbcode_goldbrick(vB_BbCodeParser $parser, $text, $options = '')
			{
				global $vbphrase, $vbulletin;
				
				if (
					$parser->registry->userinfo['permissions']['gb_permissions'] & 
					$parser->registry->bf_ugp['gb_permissions']['canuse']
					)
				{
					$text = str_replace(array('&#91;', '&#93;'), array('[', ']'), $text);
					$text = strip_bbcode($text, true, true, false);

					if ($parser->is_wysiwyg()) 
					{
						return sprintf(
							'[%1$s%2$s]%3$s[/%1$s]',
							$parser->registry->options['gb_tag'],
							$options ? "&quot;$options&quot;" : '',
							$text
						);
					}
		
					require_once(DIR . '/goldbrick/includes/functions_public.php');
					
					//$goldbrick = new goldbrick_media($vbulletin);
					
					$media = goldbrick_start_delivery($text, $options);
					
					if ($media)
					{
						return $media;
					}
					
					else
					{
						$media = goldbrick_process_bbcode($text, $options);

						$info = goldbrick_start_delivery($text, $options);
						
						return $info;
					}
				}
	
				return $vbphrase['gb_no_permissions'];
			}
		}
	}	
}
?>