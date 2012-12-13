<?php
defined('CWD1') or exit;
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/
function get_search_result($searchid,$start_num,$end_num,$include_topic_num = false , $return_post = false){
	global $xmlrpcuser;
	global $vbulletin,$db,$show;
	global $permissions;
	global $vbphrase, $stylevar;
	global $newthreads, $dotthreads, $perpage, $ignore;
	$return_list =array();

	if($end_num - $start_num > 50){
		$end_num = $start_num + 50;
	}


	// check for valid search result
	$gotsearch = false;
	if ($search =  $db->query_first("SELECT * FROM " . TABLE_PREFIX . "search AS search WHERE completed = 1 AND searchid = " . $searchid))
	{
		// is this search customized for one user?
		if ($search['personal'])
		{
			// if search was by guest, do ip addresses match?
			if ($search['userid'] == 0 AND $search['ipaddress'] == IPADDRESS)
			{
				$gotsearch = true;
			}
			// if search was by reg.user, is it bbuser?
			else if ($search['userid'] == $vbulletin->userinfo['userid'])
			{
				$gotsearch = true;
			}
		}
		// anyone can use this search result
		else
		{
			$gotsearch = true;
		}
	}
	if ($gotsearch == false)
	{
	 if($include_topic_num){
	 	return new xmlrpcresp(
	 	new xmlrpcval(
	 	array(
                                'total_topic_num' => new xmlrpcval(0,'int'),
                                'topics' => new xmlrpcval(array(),'array'),
	 	),
                        'struct'
                        )
                        );
	 } else {
	 	return new xmlrpcresp(new xmlrpcval(array(),"array"));
	 }

	}

	$search['showposts'] =  $return_post;

	// re-start the search timer
	$searchstart = microtime();

	// get the search terms that were used...
	$searchterms = unserialize($search['searchterms']);
	$searchquery = '';
	if (is_array($searchterms))
	{
		foreach ($searchterms AS $varname => $value)
		{
			if (is_array($value))
			{
				foreach ($value AS $value2)
				{
					$searchquery .= $varname . '[]=' . urlencode($value2) . '&amp;';
				}
			}
			else if ($value !== '')
			{
				$searchquery .= "$varname=" . urlencode($value) . '&amp;';
			}
		}
	}
	else
	{
		$searchquery = '';
	}

	// get the display stuff for the summary bar
	$display = unserialize($search['displayterms']);

	// $orderedids contains an ORDERED list of matching postids/threadids
	// EXCLUDING invisible and deleted items
	if (empty($search['orderedids']))
	{
		$orderedids = array('0');
	}
	else
	{
		$orderedids = explode(',', $search['orderedids']);
	}
	$numitems = sizeof($orderedids);

	// #############################################################################
	// #############################################################################

	// start the timer for the permissions check
	$go = microtime();

	// #############################################################################
	// don't retrieve tachy'd posts/threads
	if(file_exists(DIR . '/includes/functions_bigthree.php'.SUFFIX)){
		require_once(DIR . '/includes/functions_bigthree.php'.SUFFIX);
	} else {
		require_once(DIR . '/includes/functions_bigthree.php');
	}

	// query moderators for forum password purposes (and inline moderation)
	if ($vbulletin->userinfo['userid'])
	{
		cache_moderators();
	}

	// now check to see if the results can be viewed / searched etc.
	if ($search['showposts'])
	{
		// query posts
		$permQuery = "
			SELECT postid AS itemid, post.visible AS post_visible, thread.visible AS thread_visible, thread.forumid, thread.threadid, thread.postuserid, post.userid,
			IF(postuserid = " . $vbulletin->userinfo['userid'] . ", 'self', 'other') AS starter
			FROM " . TABLE_PREFIX . "post AS post
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)
			WHERE postid IN(" . implode(', ', $orderedids) . ")
			AND thread.open <> 10
		";

		$hook_query_fields = $hook_query_joins = '';


		// query post data
		$dataQuery = "
			SELECT post.postid, post.title AS posttitle, post.dateline AS postdateline,
				post.iconid AS posticonid, post.pagetext, post.visible, post.attach,
				IF(post.userid = 0, post.username, user.username) AS username,
				thread.threadid, thread.title AS threadtitle, thread.iconid AS threadiconid, thread.replycount,
				IF(thread.views=0, thread.replycount+1, thread.views) as views, thread.firstpostid, thread.prefixid, thread.taglist,
				thread.pollid, thread.sticky, thread.open, thread.lastpost, thread.forumid, thread.visible AS thread_visible,
				user.userid
				" . (can_moderate() ? ",pdeletionlog.userid AS pdel_userid, pdeletionlog.username AS pdel_username, pdeletionlog.reason AS pdel_reason" : "") . "
				" . (can_moderate() ? ",tdeletionlog.userid AS tdel_userid, tdeletionlog.username AS tdel_username, tdeletionlog.reason AS tdel_reason" : "") . "
				" . iif($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'], ', threadread.readtime AS threadread') . "
				$hook_query_fields
			FROM " . TABLE_PREFIX . "post AS post
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)

			" . (can_moderate() ?
			"LEFT JOIN " . TABLE_PREFIX . "deletionlog AS tdeletionlog ON(thread.threadid = tdeletionlog.primaryid AND tdeletionlog.type = 'thread')
			LEFT JOIN " . TABLE_PREFIX . "deletionlog AS pdeletionlog ON(post.postid = pdeletionlog.primaryid AND pdeletionlog.type = 'post')"
			: "") . "

			" . iif($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'], " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")") . "

			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = post.userid)
			$hook_query_joins
			WHERE post.postid IN";
	}
	else
	{
		// query threads
		$permQuery = "
			SELECT threadid AS itemid, forumid, visible AS thread_visible, postuserid,
			IF(postuserid = " . $vbulletin->userinfo['userid'] . ", 'self', 'other') AS starter
			FROM " . TABLE_PREFIX . "thread AS thread
			WHERE threadid IN(" . implode(', ', $orderedids) . ")
			AND thread.open <> 10
		";

		if ($vbulletin->options['threadpreview'] > 0)
		{
			$previewfield = "post.pagetext AS preview,";
			$previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = thread.firstpostid)";
		}
		else
		{
			$previewfield = "";
			$previewjoin = "";
		}

		if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
		{
			$tachyjoin = "
				LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON
					(tachythreadpost.threadid = thread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ")
				LEFT JOIN " . TABLE_PREFIX . "tachythreadcounter AS tachythreadcounter ON
					(tachythreadcounter.threadid = thread.threadid AND tachythreadcounter.userid = " . $vbulletin->userinfo['userid'] . ")
			";

			$tachycolumns = '
				IF(tachythreadcounter.userid IS NULL, thread.replycount, thread.replycount + tachythreadcounter.replycount) AS replycount,
				IF(views<=IF(tachythreadcounter.userid IS NULL, thread.replycount, thread.replycount + tachythreadcounter.replycount), IF(tachythreadcounter.userid IS NULL, thread.replycount, thread.replycount + tachythreadcounter.replycount)+1, views) AS views,
				IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastpost,
				IF(tachythreadpost.userid IS NULL, thread.lastposter, tachythreadpost.lastposter) AS lastposter,
				IF(tachythreadpost.userid IS NULL, thread.lastpostid, tachythreadpost.lastpostid) AS lastpostid
			';
		}
		else
		{
			$tachyjoin = '';

			$tachycolumns = '
				replycount, IF(views<=replycount, replycount+1, views) AS views,
				thread.lastpost, thread.lastposter, thread.lastpostid
			';
		}

		$hook_query_fields = $hook_query_joins = "";


		// query thread data
		$dataQuery = "
			SELECT $previewfield
				thread.threadid, thread.threadid AS postid, thread.title AS threadtitle, thread.iconid AS threadiconid, thread.dateline, thread.forumid,
				thread.sticky, thread.prefixid, thread.taglist, thread.pollid, thread.open, thread.lastpost AS postdateline, thread.visible,
				thread.hiddencount, thread.deletedcount, thread.attach, thread.postusername, thread.forumid,
				$tachycolumns,
				" . (can_moderate() ? "deletionlog.userid AS del_userid, deletionlog.username AS del_username, deletionlog.reason AS del_reason," : "") . "
				user.userid AS postuserid
				" . iif($vbulletin->options['threadsubscribed'] AND $vbulletin->userinfo['userid'], ", NOT ISNULL(subscribethread.subscribethreadid) AS issubscribed") . "
				" . iif($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'], ', threadread.readtime AS threadread') . "
				$hook_query_fields
			FROM " . TABLE_PREFIX . "thread AS thread
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = thread.postuserid)

			" . (can_moderate() ? "LEFT JOIN " . TABLE_PREFIX . "deletionlog AS deletionlog ON(thread.threadid = deletionlog.primaryid AND deletionlog.type = 'thread')" : "") . "
			" . iif($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'], " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")") . "
			" . iif($vbulletin->options['threadsubscribed'] AND $vbulletin->userinfo['userid'], " LEFT JOIN " . TABLE_PREFIX . "subscribethread AS subscribethread
				ON(subscribethread.threadid = thread.threadid AND subscribethread.userid = " . $vbulletin->userinfo['userid'] . " AND canview = 1)") . "
				$previewjoin
				$tachyjoin
				$hook_query_joins
			WHERE thread.threadid IN
		";
	}

	$Coventry_array = fetch_coventry();

	$tmp = array();
	$items = $db->query_read_slave($permQuery);
	unset($permQuery);
	while ($item = $db->fetch_array($items))
	{
		if (!can_moderate($item['forumid']) AND (in_array($item['userid'], $Coventry_array) OR in_array($item['postuserid'], $Coventry_array)))
		{
			continue;
		}

		if (!$search['showposts'])
		{
			// fake post_visible since we aren't looking for it in thread results
			$item['post_visible'] = 1;
		}

		if ((!$item['post_visible'] OR !$item['thread_visible']) AND !can_moderate($item['forumid'], 'canmoderateposts'))
		{	// post/thread is moderated and we don't have permission to see it
			continue;
		}
		else if (($item['post_visible'] == 2 OR $item['thread_visible'] == 2) AND !can_moderate($item['forumid']))
		{	// post/thread is deleted and we don't have permission to
			continue;
		}

		$tmp["$item[forumid]"]["$item[starter]"][] = $item['itemid'];
	}
	unset($item);
	$db->free_result($items);

	if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
	{
		// we need this for forum read times
		cache_ordered_forums(1);
	}

	foreach (array_keys($tmp) AS $forumid)
	{
		$forum =& $vbulletin->forumcache["$forumid"];
		if (!$forum)
		{
			// we don't know anything about this forum
			unset($tmp["$forumid"]);
			continue;
		}

		$fperms = $vbulletin->userinfo['forumpermissions']["$forumid"];

		$items = vb_number_format(sizeof($tmp["$forumid"]['self']) + sizeof($tmp["$forumid"]['other']));

		// check CANVIEW / CANSEARCH permission and forum password for current forum
		if (
		!($fperms & $vbulletin->bf_ugp_forumpermissions['canview'])
		OR !($fperms & $vbulletin->bf_ugp_forumpermissions['cansearch'])
		OR !verify_forum_password($forumid, $forum['password'], false)
		OR (
		(
		$vbulletin->options['fulltextsearch']
		AND !($vbulletin->bf_misc_forumoptions['indexposts'] & $vbulletin->forumcache["$forumid"]['options']))
		AND $display['options']['action'] != 'getnew' AND $display['options']['action'] != 'getdaily'
		)
		)
		{
			// cannot view / search this forum, or does not have forum password
			unset($tmp["$forumid"]);
		}
		else if (!($fperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) AND ($search['showposts'] OR ($display['options']['action'] != 'getnew' AND $display['options']['action'] != 'getdaily' AND !$search['titleonly'])))
		{
			unset($tmp["$forumid"]);
		}
		else
		{
			if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
			{
				$lastread["$forumid"] = max($forum['forumread'], (TIMENOW - ($vbulletin->options['markinglimit'] * 86400)));
			}
			else
			{
				$forumview = intval(fetch_bbarray_cookie('forum_view', $forumid));

				//use which one produces the highest value, most likely cookie
				$lastread["$forumid"] = ($forumview > $vbulletin->userinfo['lastvisit'] ? $forumview : $vbulletin->userinfo['lastvisit']);
			}

			// check CANVIEWOTHERS permission
			if (!($fperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']))
			{
				// cannot view others' threads
				unset($tmp["$forumid"]['other']);
			}
		}

		$items = vb_number_format(sizeof($tmp["$forumid"]['self']) + sizeof($tmp["$forumid"]['other']));
	}

	// now get all threadids that still remain...
	$remaining = array();
	$i = 1;
	foreach ($tmp AS $A)
	{
		foreach ($A AS $B)
		{
			foreach ($B AS $itemid)
			{
				$remaining["$itemid"] = $itemid;
			}
		}
	}
	unset($tmp, $A, $B);

	// remove all ids from $orderedids that do not exist in $remaining
	$orderedids = array_intersect($orderedids, $remaining);
	unset($remaining);

	// rebuild the $orderedids array so keys go from 0 to n with no gaps
	$orderedids = array_merge($orderedids, array());

	// count the number of items
	$numitems = sizeof($orderedids);

	// do we still have some results?
	if ($numitems == 0 AND empty($search['announceids']))
	{

	}
	else if ($numitems > 0)
	{
		$show['results'] = true;
	}

	DEVDEBUG('time to check permissions: ' . vb_number_format(fetch_microtime_difference($go), 4));

	// extra check to prevent DB error if someone sets it at 0
	if ($vbulletin->options['searchperpage'] < 1)
	{		// show the getnew message if there are no results, this might be due to permissions
		if ($display['options']['action'] == 'getnew')
		{
			if($include_topic_num){
				return new xmlrpcresp(
				new xmlrpcval(
				array(
		                                'total_topic_num' => new xmlrpcval(0,'int'),
		                                'topics' => new xmlrpcval(array(),'array'),
				),
		                        'struct'
		                        )
		                        );
			} else {
				return new xmlrpcresp(new xmlrpcval(array(),"array"));
			}
		}
		else
		{
			if ($display['options']['action'] != 'getdaily' AND $url = fetch_titleonly_url(unserialize($search['searchterms'])))
			{
				if($include_topic_num){
					return new xmlrpcresp(
					new xmlrpcval(
					array(
			                                'total_topic_num' => new xmlrpcval(0,'int'),
			                                'topics' => new xmlrpcval(array(),'array'),
					),
			                        'struct'
			                        )
			                        );
				} else {
					return new xmlrpcresp(new xmlrpcval(array(),"array"));
				}
			}
			else
			{
				if($include_topic_num){
					return new xmlrpcresp(
					new xmlrpcval(
					array(
			                                'total_topic_num' => new xmlrpcval(0,'int'),
			                                'topics' => new xmlrpcval(array(),'array'),
					),
			                        'struct'
			                        )
			                        );
				} else {
					return new xmlrpcresp(new xmlrpcval(array(),"array"));
				}
			}
		}
		$vbulletin->options['searchperpage'] = 20;
	}

	// trim results down to maximum $vbulletin->options[maxresults]
	if ($vbulletin->options['maxresults'] > 0 AND $numitems > $vbulletin->options['maxresults'])
	{
		$clippedids = array();
		for ($i = 0; $i < $vbulletin->options['maxresults']; $i++)
		{
			$clippedids[] = $orderedids["$i"];
		}
		$orderedids =& $clippedids;
		$numitems = $vbulletin->options['maxresults'];
	}

	// #############################################################################
	// #############################################################################

	// get page split...
	sanitize_pageresults($numitems, $vbulletin->GPC['pagenumber'], $vbulletin->GPC['perpage'], 200, $vbulletin->options['searchperpage']);

	// get list of thread to display on this page
	//	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];
	//	$endat = $startat + $vbulletin->GPC['perpage'];
	// $startat = $start_num;
	// $endat   = $end_num;
	$startat = $start_num;
	$endat  =  $end_num + 1;

	if($startat > sizeof($orderedids)  or $start_num > $end_num) {

		$return = array('faultCode' => 3,'faultString' =>'out of range');
		return return_fault($return);
	}

	$itemids = array();
	for ($i = $startat; $i < $endat; $i++)
	{
		if (isset($orderedids["$i"]))
		{
			$itemids["$orderedids[$i]"] = true;
		}
	}

	// #############################################################################
	// do data query
	if (!empty($itemids))
	{
		$ids = implode(', ', array_keys($itemids));
		$dataQuery .= '(' . $ids . ')';
		$items = $db->query_read_slave($dataQuery);
		$itemidname = iif($search['showposts'], 'postid', 'threadid');

		$dotthreads = fetch_dot_threads_array($ids);
	}

	// end search timer
	$searchtime = vb_number_format(fetch_microtime_difference($searchstart, $search['searchtime']), 2);

	if (!empty($itemids))
	{
		$managepost = $approvepost = $managethread = $approveattachment = $movethread = $deletethread = $approvethread = $openthread = array();
		while ($item = $db->fetch_array($items))
		{
			if ($search['showposts'])
			{
				if (can_moderate($item['forumid'], 'candeleteposts') OR can_moderate($item['forumid'], 'canremoveposts'))
				{
					$managepost["$item[postid]"] = 1;
					$show['managepost'] = true;
				}

				if (can_moderate($item['forumid'], 'canmoderateposts'))
				{
					$approvepost["$item[postid]"] = 1;
					$show['approvepost'] = true;
				}

				if (can_moderate($item['forumid'], 'canmanagethreads'))
				{
					$managethread["$item[postid]"] = 1;
					$show['managethread'] = true;
				}

				if (can_moderate($item['forumid'], 'canmoderateattachments') AND $item['attach'])
				{
					$approveattachment["$item[postid]"] = 1;
					$show['approveattachment'] = true;
				}
			}
			else
			{
				// unset the thread preview if it can't be seen
				$forumperms = fetch_permissions($item['forumid']);
				if ($vbulletin->options['threadpreview'] > 0 AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
				{
					$item['preview'] = '';
				}

				if (can_moderate($item['forumid'], 'canmanagethreads'))
				{
					$movethread["$item[threadid]"] = 1;
					$show['movethread'] = true;
				}

				if (can_moderate($item['forumid'], 'candeleteposts') OR can_moderate($item['forumid'], 'canremoveposts'))
				{
					$deletethread["$item[threadid]"] = 1;
					$show['deletethread'] = true;
				}

				if (can_moderate($item['forumid'], 'canmoderateposts'))
				{
					$approvethread["$item[threadid]"] = 1;
					$show['approvethread'] = true;
				}

				if (can_moderate($item['forumid'], 'canopenclose'))
				{
					$openthread["$item[threadid]"] = 1;
					$show['openthread'] = true;

				}
				if ($vbulletin->forumcache["$item[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['allowicons'])
				{
					$show['threadicons'] = true;
				}
			}
			$item['forumtitle'] = $vbulletin->forumcache["$item[forumid]"]['title'];
			$itemids["$item[$itemidname]"] = $item;
		}
		unset($item, $dataQuery);
		$db->free_result($items);
	}
	// #############################################################################

	if (!empty($managepost) OR !empty($approvepost) OR !empty($managethread) OR !empty($approveattachment) OR !empty($movethread) OR !empty($deletethread) OR !empty($approvethread) OR !empty($openthread))
	{
		$show['inlinemod'] = true;
		$show['spamctrls'] = ($show['deletethread'] OR $show['managepost']);
		$url = SCRIPTPATH;
	}
	else
	{
		$show['inlinemod'] = false;
		$url = '';
	}

	$threadcolspan = 7;
	$announcecolspan = 6;

	if ($show['inlinemod'])
	{
		$threadcolspan++;
		$announcecolspan++;
	}
	if (!$show['threadicons'])
	{
		$threadcolspan--;
		$announcecolspan--;
	}


	if (!empty($search['announceids']) AND $vbulletin->GPC['pagenumber'] == 1)
	{
		$announcements = $db->query_read_slave("
			SELECT announcementid, startdate, title, announcement.views, forumid,
				user.username, user.userid, user.usertitle, user.customtitle, user.usergroupid,
				IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
			FROM " . TABLE_PREFIX . "announcement AS announcement
			LEFT JOIN " . TABLE_PREFIX . "user AS user USING (userid)
			WHERE announcementid IN ($search[announceids])
			ORDER BY startdate DESC
		");
		while ($announcement = $db->fetch_array($announcements))
		{
			fetch_musername($announcement);
			$announcement['title'] = fetch_censored_text($announcement['title']);
			$announcement['postdate'] = vbdate($vbulletin->options['dateformat'], $announcement['startdate']);
			$announcement['statusicon'] = 'new';
			$announcement['views'] = vb_number_format($announcement['views']);
			$announcementidlink = "&amp;a=$announcement[announcementid]";
			$announcement['forumtitle'] = $vbulletin->forumcache["$announcement[forumid]"]['title'];
			$show['forumtitle'] = ($announcement['forumid'] == -1) ? false : true;

		}
	}

	// get highlight words
	if (!empty($display['highlight']))
	{
		$highlightwords = '&amp;highlight=' . urlencode(implode(' ', $display['highlight']));
	}
	else
	{
		$highlightwords = '';
	}

	// initialize counters and template bits
	$searchbits = '';
	$itemcount = $startat;
	$first = $itemcount + 1;

	if ($vbulletin->options['threadpreview'] AND $vbulletin->userinfo['ignorelist'])
	{
		// Get Buddy List
		$buddy = array();
		if (trim($vbulletin->userinfo['buddylist']))
		{
			$buddylist = preg_split('/( )+/', trim($vbulletin->userinfo['buddylist']), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($buddylist AS $buddyuserid)
			{
				$buddy["$buddyuserid"] = 1;
			}
		}
		DEVDEBUG('buddies: ' . implode(', ', array_keys($buddy)));
		// Get Ignore Users
		$ignore = array();
		if (trim($vbulletin->userinfo['ignorelist']))
		{
			$ignorelist = preg_split('/( )+/', trim($vbulletin->userinfo['ignorelist']), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($ignorelist AS $ignoreuserid)
			{
				if (!$buddy["$ignoreuserid"])
				{
					$ignore["$ignoreuserid"] = 1;
				}
			}
		}
		DEVDEBUG('ignored users: ' . implode(', ', array_keys($ignore)));
	}

	// initialize variable for inlinemod popup
	$threadadmin_imod_menu = '';



	$oldposts = false;

	// #############################################################################
	// show results as posts
	if ($search['showposts'])
	{
		foreach ($itemids AS $post)
		{
			// do post folder icon
			if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
			{
				// new if post hasn't been read or made since forum was last read
				$isnew = ($post['postdateline'] > $post['threadread'] AND $post['postdateline'] > $vbulletin->forumcache["$post[forumid]"]['forumread']);
			}
			else
			{
				$isnew = ($post['postdateline'] > $vbulletin->userinfo['lastvisit']);
			}

			if ($isnew)
			{
				$post['post_statusicon'] = 'new';
				$post['post_statustitle'] = $vbphrase['unread'];
			}
			else
			{
				$post['post_statusicon'] = 'old';
				$post['post_statustitle'] = $vbphrase['old'];
			}

			// allow icons?
			$post['allowicons'] = $vbulletin->forumcache["$post[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['allowicons'];

			// get POST icon from icon cache
			$post['posticonpath'] =& $vbulletin->iconcache["$post[posticonid]"]['iconpath'];
			$post['posticontitle'] =& $vbulletin->iconcache["$post[posticonid]"]['title'];

			// show post icon?
			if ($post['allowicons'])
			{
				// show specified icon
				if ($post['posticonpath'])
				{
					$post['posticon'] = true;
				}
				// show default icon
				else if (!empty($vbulletin->options['showdeficon']))
				{
					$post['posticon'] = true;
					$post['posticonpath'] = $vbulletin->options['showdeficon'];
					$post['posticontitle'] = '';
				}
				// do not show icon
				else
				{
					$post['posticon'] = false;
					$post['posticonpath'] = '';
					$post['posticontitle'] = '';
				}
			}
			// do not show post icon
			else
			{
				$post['posticon'] = false;
				$post['posticonpath'] = '';
				$post['posticontitle'] = '';
			}




			$post['pagetext'] = preg_replace('#\[quote(=(&quot;|"|\'|)??.*\\2)?\](((?>[^\[]*?|(?R)|.))*)\[/quote\]#siUe', "process_quote_removal('\\3', \$display['highlight'])", $post['pagetext']);

			// get first 200 chars of page text
			$post['pagetext'] = htmlspecialchars_uni(fetch_censored_text(trim(fetch_trimmed_title(strip_bbcode($post['pagetext'], 1), 200))));

			// get post title
			if ($post['posttitle'] == '')
			{
				$post['posttitle'] = fetch_trimmed_title($post['pagetext'], 50);
			}
			else
			{
				$post['posttitle'] = fetch_censored_text($post['posttitle']);
			}

			// format post text
			$post['pagetext'] = nl2br($post['pagetext']);

			// get highlight words
			$post['highlight'] =& $highlightwords;

			// get info from post
			$post = process_thread_array($post, $lastread["$post[forumid]"], $post['allowicons']);
			if ($vbulletin->userinfo['postorder'] == 0)
			{
				$postorder = '';
			}
			else
			{
				$postorder = 'DESC';
			}
			$post['replycount'] = preg_replace("/,/","",$post['replycount']);
			//   $getpagenum = $db->query_first("
			//		SELECT COUNT(*) AS posts
			//		FROM " . TABLE_PREFIX . "post AS post
			//		WHERE threadid = $post[threadid] AND visible = 1
			//		AND dateline " . iif(!$postorder, '<=', '>=') . " $post[postdateline]
			//	");
			$return_post =new xmlrpcval(  array('topic_id'=>new xmlrpcval($post['threadid'],"string"),
                                             'post_id'=>new xmlrpcval( $post['postid'],"string"),
                                             'reply_number' => new xmlrpcval($post['replycount'],"int"),
                                             'post_position' => new xmlrpcval(0,"int"),
                                             'post_title'=>new xmlrpcval( mobiquo_encode($post['posttitle']),"base64"),
                                             'topic_title'=>new xmlrpcval( mobiquo_encode($post['threadtitle']),"base64"),
                                             'short_content'=>new xmlrpcval( mobiquo_encode(mobiquo_chop($post['pagetext'])),"base64"),
                                             'post_author_id'=>new xmlrpcval( $post['userid'],"string"),
                                             'post_author_name'=>new xmlrpcval( mobiquo_encode($post['username']),"base64"),
                                             'post_time'=>new xmlrpcval(mobiquo_iso8601_encode( $post['postdateline']-$vbulletin->options['hourdiff'],$vbulletin->userinfo['tzoffset']),"dateTime.iso8601"),
                                             'forum_id'=>new xmlrpcval( $post['forumid'],"string"),
                            				 'can_delete' => new xmlrpcval($managepost["$post[postid]"],"boolean"),
			      							 'can_approve' =>new xmlrpcval($approvepost["$post[postid]"],"boolean"),
			      							 'can_move' => new xmlrpcval($managethread["$post[postid]"],"boolean"),
                                             'forum_name'=>new xmlrpcval(mobiquo_encode($post['forumtitle']),"base64")),"struct");


			array_push($return_list,$return_post);


			$show['disabled'] = ($managethread["$post[postid]"] OR $managepost["$post[postid]"] OR $approvepost["$post[postid]"] OR $approveattachment["$post[postid]"]) ? false : true;

			$show['moderated'] = (!$post['visible'] OR (!$post['thread_visible'] AND $post['postid'] == $post['firstpostid'])) ? true : false;

			if ($post['pdel_userid'])
			{
				$post['del_username'] =& $post['pdel_username'];
				$post['del_userid'] =& $post['pdel_userid'];
				$post['del_reason'] = fetch_censored_text($post['pdel_reason']);
				$show['deleted'] = true;
			}
			else if ($post['tdel_userid'])
			{
				$post['del_username'] =& $post['tdel_username'];
				$post['del_userid'] =& $post['tdel_userid'];
				$post['del_reason'] = fetch_censored_text($post['tdel_reason']);
				$show['deleted'] = true;
			}
			else
			{
				$show['deleted'] = false;
			}

			if ($post['prefixid'])
			{
				$post['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$post[prefixid]_title_plain"]);
				$post['prefix_rich'] = $vbphrase["prefix_$post[prefixid]_title_rich"];
			}
			else
			{
				$post['prefix_plain_html'] = '';
				$post['prefix_rich'] = '';
			}

			$itemcount ++;
			exec_switch_bg();



			if (($display['options']['action'] == 'getdaily' OR $display['options']['action'] == 'getnew') AND $search['sortby'] == 'lastpost' AND !$oldposts AND $post['postdateline'] <= $vbulletin->userinfo['lastvisit'] AND $vbulletin->userinfo['lastvisit'] != 0)
			{
				$oldposts = true;
			}

		}


	}
	// #############################################################################
	// show results as threads
	else
	{

		$show['forumlink'] = true;

		// threadbit_deleted conditionals
		$show['threadtitle'] = true;
		$show['viewthread'] = true;
		$show['managethread'] = true;

		foreach ($itemids AS $thread)
		{
			// add highlight words
			$thread['highlight'] =& $highlightwords;
			$thread_replycount = $thread[replycount];

			// get info from thread

			$thread = process_thread_array($thread, $lastread["$thread[forumid]"]);

			if($thread[lastpostid]){
				$last_topic = $db->query_first_slave("
								SELECT post.pagetext,post.userid
								FROM " . TABLE_PREFIX . "post AS post
								WHERE post.postid =$thread[lastpostid] 
									AND post.visible = 1
						         	");
			} else {
				$last_topic = $db->query_first_slave("
								SELECT post.pagetext,post.userid
								FROM " . TABLE_PREFIX . "post AS post
								WHERE post.threadid =$thread[threadid] 
									AND post.visible = 1
							    ORDER BY postid DESC
								LIMIT 1
						         	");
			}

			if($show['gotonewpost']){
				$mobiquo_new_post = 1;
			} else{
				$mobiquo_new_post = 0;
			}
			$fetch_userinfo_options = (
			FETCH_USERINFO_AVATAR
			);
			$userinfo = mobiquo_verify_id('user',$last_topic['userid'], 1, 1, $fetch_userinfo_options);
			if(!is_array($userinfo)){
				$userinfo = array();
			}
			fetch_avatar_from_userinfo($userinfo,true,false);

			if($userinfo[avatarurl]){
				if( preg_match('/^http/',$userinfo['avatarurl'])){
					$icon_url=unhtmlspecialchars($userinfo['avatarurl']);
				}
				else{
					$icon_url=$vbulletin->options[bburl].'/'.unhtmlspecialchars($userinfo['avatarurl']);
				}
			} else {
				$icon_url = '';
			}
			$forumperms = fetch_permissions($thread[forumid]);
			if($vbulletin->options['threadpreview'] == 0 ||!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])){
				$last_topic['pagetext'] = '';
			}

			$mobiquo_isclosed = iif($thread['open'], false, true);
			$mobiquo_attach = iif(($thread['attach']>0),1,0);
			$return_topic = new xmlrpcval(array( 'forum_id'=>new xmlrpcval($thread[forumid],"string"),
			                                      'forum_name'=>new xmlrpcval(mobiquo_encode($thread[forumtitle]),"base64"),
			                                      'topic_id'=>new xmlrpcval($thread[threadid],"string"),
			                                      'topic_title'=>new xmlrpcval(mobiquo_encode($thread[threadtitle]),"base64"),
			                                      'post_author_id' => new xmlrpcval($last_topic['userid'],"string"),
			                                      'post_author_name'=>new xmlrpcval(mobiquo_encode($thread[lastposter]),"base64"),
			       								  'last_reply_time'=>new xmlrpcval(mobiquo_iso8601_encode($thread[lastpost]-$vbulletin->options['hourdiff'],$vbulletin->userinfo['tzoffset']),"dateTime.iso8601"),
		    						 			  'last_reply_author_name' => new xmlrpcval(mobiquo_encode($thread[lastposter]),"base64"),
			                                      'reply_number'=>new xmlrpcval($thread_replycount,"int"),
			     								  'is_closed' => new xmlrpcval($mobiquo_isclosed,"boolean"),
			                                      'new_post' =>new xmlrpcval($mobiquo_new_post,'boolean'),
			                                      'icon_url'=>new xmlrpcval($icon_url ,"string"),
			                                      'short_content'=>new xmlrpcval(mobiquo_encode(mobiquo_chop(post_content_clean($last_topic['pagetext']))),"base64"),
			                                      'issubscribed' => new xmlrpcval($thread[issubscribed],'boolean'),
			                        		      'attachment' => new xmlrpcval($mobiquo_attach,'string'),	
               	                                  'is_subscribed' => new xmlrpcval($thread[issubscribed],'boolean'),
												  'can_subscribe' => new xmlrpcval(true,'boolean'),
			      								  'can_delete' => new xmlrpcval($deletethread["$thread[threadid]"],"boolean"),
			      							  	  'can_close' => new xmlrpcval($openthread["$thread[threadid]"] ,"boolean"),
			      	                    		  'can_sticky' => new xmlrpcval($movethread["$thread[threadid]"],"boolean"),
			      		 						   'is_sticky' => new xmlrpcval($thread[sticky],"boolean"),
			      								  'can_approve' => new xmlrpcval($approvethread["$thread[threadid]"],"boolean"),	
			      								  'can_move' => new xmlrpcval($movethread["$thread[threadid]"],"boolean"),
			      								  'is_approve' => new xmlrpcval($thread['visible'],"boolean"),
			                                      'post_time'=>new xmlrpcval(mobiquo_iso8601_encode($thread[lastpost]-$vbulletin->options['hourdiff'],$vbulletin->userinfo['tzoffset']),"dateTime.iso8601"))
			,"struct");

			array_push($return_list,$return_topic);

			// Inline Modrint_r($thread);eration
			$show['disabled'] = ($movethread["$thread[threadid]"] OR $deletethread["$thread[threadid]"] OR $approvethread["$thread[threadid]"] OR $openthread["$thread[threadid]"]) ? false : true;

			$itemcount++;
			exec_switch_bg();



			if (($display['options']['action'] == 'getdaily' OR $display['options']['action'] == 'getnew') AND $search['sortby'] == 'lastpost' AND !$oldposts AND $thread['lastpost'] <= $vbulletin->userinfo['lastvisit'] AND $vbulletin->userinfo['lastvisit'] != 0)
			{
				$oldposts = true;
				if ($display['options']['action'] == 'getnew')
				{
					$show['unread_posts'] = true;
				}
			}
			$forumperms = fetch_permissions($thread['forumid']);
			if ($thread['visible'] == 2)
			{
				$thread['deletedcount']++;
				$show['deletereason'] = (!empty($thread['del_reason'])) ?  true : false;
				$show['moderated'] = ($thread['hiddencount'] > 0 AND can_moderate($thread['forumid'], 'canmoderateposts')) ? true : false;
				$show['deletedthread'] = (can_moderate($thread['forumid']) OR $forumperms & $vbulletin->bf_ugp_forumpermissions['canseedelnotice']) ? true : false;
			}
			else
			{
				if (!$thread['visible'])
				{
					$thread['hiddencount']++;
				}
				$show['moderated'] = ($thread['hiddencount'] > 0 AND can_moderate($thread['forumid'], 'canmoderateposts')) ? true : false;
				$show['deletedthread'] = ($thread['deletedcount'] > 0 AND (can_moderate($thread['forumid']) OR $forumperms & $vbulletin->bf_ugp_forumpermissions['canseedelnotice'])) ? true : false;
			}
		}


	}
	// #############################################################################

	$last = $itemcount;

	$pagenav = construct_page_nav($vbulletin->GPC['pagenumber'], $vbulletin->GPC['perpage'], $numitems, 'search.php?' . $vbulletin->session->vars['sessionurl'] . 'searchid=' . $vbulletin->GPC['searchid'] . '&amp;pp=' . $vbulletin->GPC['perpage']);

	// #############################################################################
	// get the bits for the summary bar


	$show['no_prefix'] = false;
	if (!empty($display['prefixes']))
	{
		foreach ($display['prefixes'] AS $key => $prefixid)
		{
			if ($prefixid == '-1')
			{
				$show['no_prefix'] = true;
			}

			if (isset($vbphrase["prefix_{$prefixid}_title_plain"]))
			{
				$display['prefixes']["$key"] = '<b><u>' . htmlspecialchars_uni($vbphrase["prefix_{$prefixid}_title_plain"]) . '</u></b>';
			}
			else
			{
				unset($display['prefixes']["$key"]);
			}
		}
		$display_prefixes = implode(" $vbphrase[or] ", $display['prefixes']);
	}
	else
	{
		$display_prefixes = '';
	}

	$starteronly =& $display['options']['starteronly'];
	$childforums =& $display['options']['childforums'];
	$action =& $display['options']['action'];

	if ($vbulletin->options['fulltextsearch'])
	{
		DEVDEBUG('FULLTEXT Search');
	}
	else
	{
		DEVDEBUG('Default Search');
	}

	$searchminutes = floor((TIMENOW - $search['dateline']) / 60);
	if ($searchminutes >= 1)
	{
		$show['generated'] = true;
	}

	if ($display['options']['action'] != 'getnew' AND $display['options']['action'] != 'getdaily' AND $titlesearchurl = fetch_titleonly_url(unserialize($search['searchterms'])))
	{
		$show['titleonlysearch'] = true;
	}


	// add to the navbits
	if (defined('NOSHUTDOWNFUNC'))
	{
		exec_shut_down();
	}

	if($include_topic_num){
		return new xmlrpcresp(
		new xmlrpcval(
		array(
                                        'total_topic_num' => new xmlrpcval($numitems,'int'),
                                        'topics' => new xmlrpcval($return_list,'array'),
                                		'search_id' => new xmlrpcval($searchid,'string'),
		),
                                'struct'
                                )
                                );
	} else {
		return new xmlrpcresp(new xmlrpcval($return_list,"array"));
	}
}

?>
