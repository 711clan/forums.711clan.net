<?php
/**
* Creates new thread or gives error and then redirects user
*
* @param	string	Title of thread
* @param	string	Message of post
* @param	integer ForumID for thread
* @param	boolean Allow guest posts
*/
function create_new_thread($title = 'Defauglt Title', $message = 'Defagult Message', $id = 3, $guest = false)
{
	// set some globals

	global $forumperms, $vbulletin, $vbphrase;

	// init some variables

	$fail = 0;
	$errors = array();
	$newpost = array();

	// init post information

	if ($guest AND $vbulletin->userinfo['userid'] == 0)
	{
		$newpost['username'] = $vbphrase['guest'];
	}
	
	$newpost['title']		= $title;
	$newpost['message']		= $message;
	$newpost['signature']	= '0';
	
	if ($vbulletin->userinfo['signature'] != '')
	{
		$newpost['signature'] = '1';
	}
	
	$newpost['parseurl'] = '1';
	$newpost['emailupdate'] = '9999';

	// attempt thread create

	$foruminfo = verify_id('forum', $id, 0, 1);
	
	if (!$foruminfo['forumid'])
	{
		$fail = 1;
	}
	
	$forumperms = fetch_permissions($foruminfo['forumid']);
	
	if (!function_exists('build_new_post'))
	{
		require_once(DIR . '/includes/functions_newpost.php');
	}
	
	build_new_post('thread', $foruminfo, array(), array(), $newpost, $errors);
	
	if (sizeof($errors) > 0)
	{
		$fail = 1;
	}

	// do redirection

	if (!$fail)
	{
		$vbulletin->url = $vbulletin->options['bburl'] . '/showthread.php?' . $vbulletin->session->vars['sessionurl'] . "p=".$newpost['postid']."#post".$newpost['postid'];
		
		eval(print_standard_redirect('redirect_postthanks'));
	}
	
	else
	{
		$vbulletin->url = $vbulletin->options['bburl'];
		
		eval(print_standard_redirect($vbphrase['error'].': '.$vbphrase['redirecting'],0,1));
	}
}

/*
The function that draws the rating bar.
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
Licensed under a Creative Commons Attribution 3.0 License.
http://creativecommons.org/licenses/by/3.0/
See readme.txt for full credit details.
Made to work with Vbulletin by Nix
--------------------------------------------------------- */
function rating_bar($id, $units = null, $static = null)
{
	global $vbulletin;
	
	$rating_unitwidth = 30;
	
	$userid = $vbulletin->userinfo['userid'];
	
	if (!$units)
	{
		$units = 10;
	}
	
	if (!$static)
	{
		$static = FALSE;
	}

	// get votes, values, users for the current rating bar
	
	$query = $vbulletin->db->query_read("
		SELECT total_votes, total_values, userids FROM " . TABLE_PREFIX . "gb_rating
		WHERE id='$id'
	");

	// insert the id in the DB if it doesn't exist already
	// see: http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/#comment-121
	
	if ($vbulletin->db->num_rows($query) == 0)
	{
		$sql = "
		INSERT INTO " . TABLE_PREFIX . "gb_rating
		(`id`,`total_votes`, `total_values`, `userids`) VALUES
		('$id', '0', '0', '0')";
		
		$result = $vbulletin->db->query_read($sql);
	}
	
	$numbers = $vbulletin->db->fetch_array($query);
	
	$numbers['userids'] = unserialize($numbers['userids']);

	if ($numbers['total_votes'] < 1)
	{
		$count = 0;
	}
	
	else
	{
		$count = $numbers['total_votes']; //how many votes total
	}
	
	$current_rating = $numbers['total_values']; //total number of rating added together and stored
	
	$tense = ($count==1) ? "vote" : "votes"; //plural form votes/vote

	// determine whether the user has voted, so we know how to draw the ul/li
	//$voted=mysql_num_rows($vbulletin->db->query_read("SELECT userids FROM " . TABLE_PREFIX . "anymedia_rating WHERE userids=$userid AND id=$id"));

	// now draw the rating bar
	
	$rating_width	= @number_format($current_rating/$count,2)*$rating_unitwidth;
	$rating1		= @number_format($current_rating/$count,1);
	$rating2		= @number_format($current_rating/$count,2);
	
	$current = $rating2 / $units;
	
	$ratewidth = $rating_width . 'px';
	$width = $rating_unitwidth * $units . 'px';
	
	if ($static)
	{
		eval('$vidrat .= "' . fetch_template('gbs_rating_static') . '";');

		return $vidrat;
	}
	
	else
	{
		for ($ncount = 1; $ncount <= $units; $ncount++)
		{
			if (!in_array_multi($userid, $numbers['userids']))
			{
				$li.='<li><a href="goldbrick.php?do=rate&amp;vote='.$ncount.'&amp;id='.$id.'&amp;userid='.$userid.'&amp;units='.$units.'" title="'.$ncount.' out of '.$units.'" class="r'.$ncount.'-unit rater" rel="nofollow">'.$ncount.'</a></li>';
			}
		}
		
		$ncount=0; // resets the count

		if ($voted)
		{
			$rater.=' class="voted"';
		}

		eval('$vidrat .= "' . fetch_template('gbs_rating_vote') . '";');

		return $vidrat;
	}
}

function in_array_multi ($needle, $haystack)
{
   if (!is_array($haystack)) return $needle == $haystack;
   foreach($haystack as $value) if(in_array_multi($needle, $value)) return true;
   return false;
}


function quickReply ()
{
	global $vbulletin;
	// *********************************************************************************
	// build quick reply if appropriate
	if ($show['quickreply'])
	{
		require_once(DIR . '/includes/functions_editor.php');

		$show['wysiwyg'] = ($forum['allowbbcode'] ? is_wysiwyg_compatible() : 0);
		$istyles_js = construct_editor_styles_js();

		// set show signature hidden field
		$showsig = iif($vbulletin->userinfo['signature'], 1, 0);

		// set quick reply initial id
		if ($threadedmode == 1)
		{
			$qrpostid = $curpostid;
			$show['qr_require_click'] = 0;
		}
		else if ($vbulletin->options['quickreply'] == 2)
		{
			$qrpostid = 0;
			$show['qr_require_click'] = 1;
		}
		else
		{
			$qrpostid = 'who cares';
			$show['qr_require_click'] = 0;
		}

		$editorid = construct_edit_toolbar('', 0, $foruminfo['forumid'], ($foruminfo['allowsmilies'] ? 1 : 0), 1, false, 'qr');
		$messagearea = "
			<script type=\"text/javascript\">
			<!--
				var threaded_mode = $threadedmode;
				var require_click = $show[qr_require_click];
				var is_last_page = $show[allow_ajax_qr]; // leave for people with cached JS files
				var allow_ajax_qr = $show[allow_ajax_qr];
				var ajax_last_post = " . intval($effective_lastpost) . ";
			// -->
			</script>
			$messagearea
		";

		if (is_browser('mozilla') AND $show['wysiwyg'] == 2)
		{
			// Mozilla WYSIWYG can't have the QR collapse button,
			// so remove that and force QR to be expanded
			$show['quickreply_collapse'] = false;

			unset(
				$vbcollapse["collapseobj_quickreply"],
				$vbcollapse["collapseimg_quickreply"],
				$vbcollapse["collapsecel_quickreply"]
			);
		}
		else
		{
			$show['quickreply_collapse'] = true;
		}
	}
	else if ($show['ajax_js'])
	{
		require_once(DIR . '/includes/functions_editor.php');

		$vBeditJs = construct_editor_js_arrays();
		eval('$vBeditTemplate[\'clientscript\'] = "' . fetch_template('editor_clientscript') . '";');
	}
	
}

function insert_tags($vid, $tag, $count)
{
	global $vbulletin;

	$vbulletin->db->query_write("
		INSERT INTO " . TABLE_PREFIX . "gb_tags
		(vid, tag, count) VALUES
		('$vid', '" .$tag . "', '1')
	");
}

function get_tag_cloud()
{
	global $vbulletin;
	
	// Default font sizes
	$min_font_size = 13;
	$max_font_size = 26;
	
	// Pull in tag data
	$tags = get_tag_data();
	
	if ($tags)
	{
		$minimum_count = min(array_values($tags));
		$maximum_count = max(array_values($tags));
		
		$spread = $maximum_count - $minimum_count;

		if ($spread == 0)
		{
			$spread = 1;
		}

		$cloud_html = '';

		$cloud_tags = array(); // create an array to hold tag code

		$url = $vbulletin->options['bburl'] . '/goldbrick.php?do=' . $vbulletin->session->vars['sessionurl'] . "search&id=";

		foreach ($tags as $tag => $count)
		{
			$size = $min_font_size + ($count - $minimum_count) * ($max_font_size - $min_font_size) / $spread;

			$cloud_tags[] = '<a style="font-size: '. floor($size) . 'px' 
				. '" class="tag_cloud" href="' . $url . $tag 
				. '" title="\'' . $tag	. '\' returned a count of ' . $count . '">' 
				. htmlspecialchars(stripslashes($tag)) . '</a>';
		}

		$cloud_html = join("\n", $cloud_tags) . "\n";

		return $cloud_html;
	}
	
	else
	{
		return false;
	}
}

function get_tag_data()
{
	global $vbulletin, $stylevar;
	
	$tag = $vbulletin->db->query_read("
		SELECT *, COUNT(id) as quanity FROM " . TABLE_PREFIX . "gb_tags
		GROUP BY tag
		ORDER BY tag DESC
	");
	
	if ($tag)
	{
		while ($tags = $vbulletin->db->fetch_array($tag))
		{
			$arr[$tags['tag']] = $tags['quanity'];
		} 

		if (is_array($arr))
		{
			ksort($arr);
		} 

		return $arr;
	}
	
	else
	{
		return false;
	}
	
}

function makesites()
{
	global $vbulletin;
	
	$site = $vbulletin->db->query_read("
		SELECT site FROM " . TABLE_PREFIX . "gb_cache
	");
	
	while ($sites = $vbulletin->db->fetch_array($site))
	{
		foreach ($sites as $tag)
		{
			insert_tags('', $tag, '1');
			echo $tag;
		}
	}
	return $sites;
}

?>