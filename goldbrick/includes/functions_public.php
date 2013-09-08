<?php

/**
 * goldbrick public functions.  This file is require_once'd in each plugin 
 * and calls the public function.  This helps keep all internals out of memory
 * until they are needed.
 * 
  */
 
################################################################################
 
/**
 * Takes a message body, and replaces all URLs with media bbcode after 
 * caching any necessary media information.  
 * 
 * For new posts, plugin code is injected into hook object to update with
 * postids based on the posthash.
 * 
 * @param	string		Message Body
 * @param	integer		Userid of poster
 * @param	integer		Post ID of message
 * 
 * @return	string		Updated Message Body
 */
function goldbrick_process_post($message, $userid, $postid = 0, $posthash, $gb_options = 0)
{
	global $vbulletin, $goldbrick;

	$matches = null;

	if (!preg_match_all('{\[url\](.+)\[\/url\]}i', $message, $matches) AND !preg_match_all('{\[media\](.+)\[\/media\]}i', $message, $matches))
	{
		return $message;
	}
	$find    = array();
	$replace = array();

	require_once(DIR . '/goldbrick/includes/class_goldbrick.php');

	foreach ($matches[1] as $url)
	{
		$goldbrick = new goldbrick_media($vbulletin);

		if ($media = $goldbrick->parse_url($url, $gb_options))
		{
			$find   [] = "{\[url\]" . preg_quote($url) . "\[\/url\]}i";
			$replace[] = '[media]' . $url . '[/media]';
			
			//Type of Media 0 = null,  1 = Post, 2 = profile, 3 = blog
			$flag = 1;
			
			$goldbrick->save($media, $userid, $postid, $posthash, $flag);
		}
	}
		
	$message = preg_replace($find, $replace, $message);
	
	if ($postid)
	{
		return $message;
	}
	
	$code  = '$goldbrick = new Goldbrick_Media($vbulletin);' . "\n";
	$code .= '$goldbrick->set_postid($post["postid"], $post["posthash"]);';
	
	goldbrick_inject_plugin('newpost_complete', $code);
	
	return $message;
}

/**
 * Takes a message body, and replaces all URLs with media bbcode after 
 * caching any necessary media information.  
 * 
 * For new posts, plugin code is injected into hook object to update with
 * postids based on the posthash.
 * 
 * @param	string		Message Body
 * @param	integer		Userid of poster
 * @param	integer		Post ID of message
 * 
 * @return	string		Updated Message Body
 */
function goldbrick_process_attachment($attach_id, $userid, $postid = 0, $attach_ext, $gb_options = 0)
{
	global $vbulletin;

	$id = array();

	require_once(DIR . '/goldbrick/includes/class_goldbrick.php');

	$goldbrick = new goldbrick_media($vbulletin);

	$url = $attach_id;

	if ($media = $goldbrick->is_valid_attachment($attach_ext))
		{
			$url = $vbulletin->options['bburl'] . '/attachment.php?attachmentid=' . $attach_id .'.' . $attach_ext;
			
			$info = $goldbrick->parse_url($url, $gb_options);
			$info['unique'] = $attach_id;
			
			//$goldbrick->save($info, $userid, $postid, $posthash);
		}
	
	return $info;
}



/**
 * Takes a message body, and replaces all URLs with media bbcode after 
 * caching any necessary media information.  
 * 
 * For new posts, plugin code is injected into hook object to update with
 * postids based on the posthash.
 * 
 * @param	string		Message Body
 * @param	integer		Userid of poster
 * @param	integer		Post ID of message
 * 
 * @return	string		Updated Message Body
 */
function goldbrick_process_blog($message, $userid, $posthash, $gb_options = 0)
{
	global $vbulletin;

	$matches = null;
	if (!preg_match_all('{\[url\](.+)\[\/url\]}i', $message, $matches))
	{
		return $message;
	}
	
	$find    = array();
	$replace = array();
	
	require_once(DIR . '/goldbrick/includes/class_goldbrick.php');

	foreach ($matches[1] as $url)
	{
		$goldbrick = new goldbrick_media($vbulletin);

		if ($media = $goldbrick->parse_url($url, $gb_options))
		{
			$find   [] = "{\[url\]" . preg_quote($url) . "\[\/url\]}i";
			$replace[] = '[media]' . $url . '[/media]';
			
			$goldbrick->save($media, $userid, $postid, $posthash);
		}
	}
	
	$message = preg_replace($find, $replace, $message);
	
	return $message;
}

function goldbrick_process_bbcode($text, $options, $gb_options = 0)
{
	global $vbulletin, $post;
	
	require_once(DIR . '/goldbrick/includes/class_goldbrick.php');

	$text = unhtmlspecialchars($text);
	
	$goldbrick = new goldbrick_media($vbulletin);
	
	if ($media = $goldbrick->parse_url($text, $gb_options))
	{
		$goldbrick->save($media, $post['userid'], $post['postid'], $posthash = null);
	}

	return $text;
}

/**
 * Takes a message body, and replaces all URLs with media bbcode after 
 * caching any necessary media information.  
 * 
 * For new posts, plugin code is injected into hook object to update with
 * postids based on the posthash.
 * 
 * @param	string		Message Body
 * @param	integer		Userid of poster
 * @param	integer		Post ID of message
 * 
 * @return	string		Updated Message Body
 */
function goldbrick_process_profile($gb_member_media, $userid, $postid = 0, $posthash, $gb_options = 0)
{
	global $vbulletin, $goldbrick;

	require_once(DIR . '/goldbrick/includes/class_goldbrick.php');

	$goldbrick = new goldbrick_media($vbulletin);
	
	if ($media = $goldbrick->parse_url($gb_member_media, $gb_options))
	{
		$goldbrick->save($media, $userid, $postid, $posthash);
	}

	return $gb_member_media;
}

/**
 * Takes a list of postids and prepares delivery of all posted media items.
 * 
 * @param	array		postids containing media to deliver
 * @return	boolean		successful status
 */
function goldbrick_start_delivery($text, $options)
{
	global $vbulletin,$post, $stylevar;

	require_once(DIR . '/goldbrick/includes/class_goldbrick.php');
	
	$goldbrick = new Goldbrick_Media($vbulletin);
	
	$uniques = md5(unhtmlspecialchars($text));

	if (!$info = $goldbrick->set_uniques($uniques))
	{
		return null;
	}

	if (THIS_SCRIPT == 'member')
	{
		
		eval('$content = "' . fetch_template('gb_player_classic') . '";');
	}
		
	elseif ($vbulletin->options['gb_style'])
	{
		eval('$content = "' . fetch_template('gb_player') . '";');
	}
	
	else
	{
		eval('$content = "' . fetch_template('gb_player_classic') . '";');
	}
	
	return $content;
}

/**
 * Injects plugin code at run-time.
 * 
 * @param	string		Hook name
 * @param	string		Code to inject
 */
function goldbrick_inject_plugin($hook, $code)
{
	$hook_obj =& vBulletinHook::init();
	
	if (!isset($hook_obj->pluginlist[$hook]))
	{
		$hook_obj->pluginlist[$hook] = $code;
	}
	else
	{
		$hook_obj->pluginlist[$hook] .= "\n$code\n";
	}	
}

if (!function_exists('goldbrick_debug'))
{
	function goldbrick_debug($msg)
	{
		$args = func_get_args();
		array_shift($args);
		
		echo "<hr /><h2>$msg</h2>";
		call_user_func_array('var_dump', $args);
		echo "<hr />";
	}
}

/**
 * Runs the configuration file for a given site in a safe-scope environment.
 * This prevents the contents of the file modifying or reading anything from
 * the cache class.
 * 
 * @param	string		Site identifier
 * @param	array		List of safe variable keys to
 * 
 * @return	array		Assoc. array of the variables
 */
function goldbrick_cache_load_config($identifier, $safe_variables, $gb_options)
{
	$gb_options = process_options($gb_options);

	require(DIR . "/goldbrick/includes/sites/$identifier.php");
	
	if (!is_array($info))
	{
		trigger_error("\$info needs to set in $identifier.php", E_USER_ERROR);
		exit;
	}

	return compact($safe_variables);
}

/**
 * Runs the configuration file for a given extensions in a safe-scope environment.
 * This prevents the contents of the file modifying or reading anything from
 * the cache class.
 * 
 * @param	string		Extension identifier
 * @param	array		List of safe variable keys to
 * 
 * @return	array		Assoc. array of the variables
 */
function goldbrick_cache_load_ext_config($identifier, $safe_variables, $gb_options, $url)
{
	global $vbulletin;

	$gb_options = process_options($gb_options);

	require(DIR . "/goldbrick/includes/extensions/$identifier[1].php");

	if (!is_array($info))
	{
		trigger_error("\$info needs to set in $valid_ext.php", E_USER_ERROR);
		exit;
	}
	
	return compact($safe_variables);
}

/**
 * Loop through vbulletin options and create a GoldBrick options array
 * 
 * 
 * @return	array		Assoc. array of the variables
 */
function create_gboptions_array()
{
	global $vbulletin;
	
	$gb_options = array();
	
}

/**
 * Process Media options or Admin Set options
 *
 * @return void
 * 
 **/
function process_options($gb_options)
{
	global $vbulletin;
	
	if ($gb_options)
	{
		return $gb_options;
		
	} else {
		
		$gb_options = array(
			'title'		=> $vbulletin->options['gb_title'], 
			'width'		=> $vbulletin->options['gb_width'], 
			'height'	=> $vbulletin->options['gb_height'], 
			'autoplay'	=> $vbulletin->options['gb_autoplay'], 
			'loop'		=> $vbulletin->options['gb_loop']
		);

		return $gb_options;
	}
}

/**
 * Fetches all old content from the database, and checks if it's still active.
 * If it's not, it will be reverted to its [url] form.
 * 
 * @param	integer		Expiration period
 */
function goldbrick_exec_cleanup($hashes)
{
	global $vbulletin;
	
	require_once(DIR . '/goldbrick/includes/class_goldbrick_cache.php');
	$goldbrick = new Goldbrick_Cache($vbulletin);
	
	$media = $goldbrick->fetch_expired_media($hashes);
	
	$to_revert = array();
	$to_remove = array();
	$to_bump   = array();
	
	foreach ($media as $link)
	{
		if (empty($link['postids']))
		{
			$to_remove[] = $link['hash'];
			continue;
		}
		
		if ($goldbrick->is_inactive($link['url']))
		{
			$to_revert[] = $link;
			$to_remove[] = $link['hash'];
			continue;
		}
		
		$to_bump[] = $link;
	}
	
	if (defined('GOLDBRICK_DEBUG_CLEANUP'))
	{
		goldbrick_debug('revert, remove, bump', $to_revert, $to_remove, $to_bump);
	}
	
	if (!empty($to_revert))
	{
		$goldbrick->revert_posts($to_revert);
	}
	
	if (!empty($to_remove))
	{
		$goldbrick->remove($to_remove);
	}
	
	if (!empty($to_bump))
	{
		$goldbrick->bump($to_bump);
	}
}
/**
 * undocumented function
 *
 * @return $value
 **/
function multiarrayearch($needle, $haystack)
{
	$value = false;
    $x = 0;
    
	foreach ($haystack as $temp => $k)
	{
		$search = array_search($needle, $k);
		if (strlen($search) > 0 && $search >= 0)
		{
			$value[0] = $x;
			$value[1] = $search;
			$found = array($k[$search], $temp);
         }

		$x++;
	}
	
	return $found;
}

?>