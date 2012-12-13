<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.7 PL1 - Licence Number VBF2470E4F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2007 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'usercp');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('user', 'infractionlevel');

// get special data templates from the datastore
$specialtemplates = array(
	'iconcache',
	'noavatarperms',
	'smiliecache',
	'bbcodecache',
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'USERCP_SHELL',
	'USERCP',
	'usercp_nav_folderbit',
	// subscribed threads templates
	'threadbit',
	// subscribed forums templates
	'forumhome_forumbit_level1_post',
	'forumhome_forumbit_level1_nopost',
	'forumhome_forumbit_level2_post',
	'forumhome_forumbit_level2_nopost',
	'forumhome_subforumbit_nopost',
	'forumhome_subforumbit_post',
	'forumhome_subforumseparator_nopost',
	'forumhome_subforumseparator_post',
	'forumhome_lastpostby',
	'forumhome_moderator',
	'forumhome_markread_script',
	// private messages templates
	'pm_messagelistbit',
	'pm_messagelistbit_ignore',
	'pm_messagelistbit_user',
	// reputation templates
	'usercp_reputationbits',
	// infraction templates
	'userinfraction_infobit'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_forumlist.php');
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (!$vbulletin->userinfo['userid'] OR !($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
{
	print_no_permission();
}

// main page:

($hook = vBulletinHook::fetch_hook('usercp_start')) ? eval($hook) : false;

// ############################### start reputation ###############################

$show['reputation'] = false;
if ($vbulletin->options['reputationenable'] AND ($vbulletin->userinfo['showreputation'] OR !($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canhiderep'])))
{
	$vbulletin->options['showuserrates'] = intval($vbulletin->options['showuserrates']);
	$vbulletin->options['showuserraters'] = $permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canseeownrep'];
	$reputations = $db->query_read_slave("
		SELECT
			user.username, reputation.whoadded,
			reputation.postid as postid,
			reputation.reputation, reputation.reason,
			post.threadid as threadid,
			reputation.dateline as dateline,
			thread.title as title
		FROM " . TABLE_PREFIX . "reputation AS reputation
		LEFT JOIN " . TABLE_PREFIX . "post AS post ON(reputation.postid = post.postid)
		LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON(post.threadid = thread.threadid)
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = reputation.whoadded)
		WHERE reputation.userid = " . $vbulletin->userinfo['userid'] . "
		" . iif($vbulletin->options['showuserraters'] AND trim($vbulletin->userinfo['ignorelist']), " AND reputation.whoadded NOT IN (0," . str_replace(' ', ',', trim($vbulletin->userinfo['ignorelist'])). ")") . "
			AND thread.visible = 1
			AND post.visible = 1
		ORDER BY reputation.dateline DESC
		LIMIT 0, " . $vbulletin->options['showuserrates']
	);

	$reputationcommentbits = '';
	if ($vbulletin->options['showuserraters'])
	{
		$reputationcolspan = 5;
		$reputationbgclass = 'alt2';
	}
	else
	{
		$reputationcolspan = 4;
		$reputationbgclass = 'alt1';
	}

	require_once(DIR . '/includes/class_bbcode.php');
	$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

	while ($reputation = $db->fetch_array($reputations))
	{
		if ($reputation['reputation'] > 0)
		{
			$posneg = 'pos';
		}
		else if ($reputation['reputation'] < 0)
		{
			$posneg = 'neg';
		}
		else
		{
			$posneg = 'balance';
		}
		$reputation['timeline'] = vbdate($vbulletin->options['timeformat'], $reputation['dateline']);
		$reputation['dateline'] = vbdate($vbulletin->options['dateformat'], $reputation['dateline']);
		$reputation['reason'] = $bbcode_parser->parse($reputation['reason']);
		if (vbstrlen($reputation['title']) > 25)
		{
			$reputation['title'] = fetch_trimmed_title($reputation['title'], 24);
		}

		($hook = vBulletinHook::fetch_hook('usercp_reputationbit')) ? eval($hook) : false;

		eval('$reputationcommentbits .= "' . fetch_template('usercp_reputationbits') . '";');
		$show['reputation'] = true;
	}
	unset($bbcode_parser);
}

// ############################### start private messages ###############################

//get ignorelist info
//generates a hash, in the form of $ignore[(userid)]
//run checks to it by seeing if $ignore[###] returns anything
//if so, then user is ignored

$show['privatemessages'] = false;
if ($vbulletin->options['enablepms'] AND ($permissions['pmquota'] > 0 OR $vbulletin->userinfo['pmtotal']))
{
	$pms = $db->query_read_slave("
		SELECT pm.*, pmtext.*
		" . iif($vbulletin->options['privallowicons'], ',icon.iconpath, icon.title AS icontitle') . "
		FROM " . TABLE_PREFIX . "pm AS pm
		INNER JOIN " . TABLE_PREFIX . "pmtext AS pmtext ON(pmtext.pmtextid = pm.pmtextid)
		" . iif($vbulletin->options['privallowicons'], "LEFT JOIN " . TABLE_PREFIX . "icon AS icon ON(icon.iconid = pmtext.iconid)") . "
		WHERE pm.userid = " . $vbulletin->userinfo['userid'] . "
		AND pmtext.dateline > " . $vbulletin->userinfo['lastvisit'] . "
		AND pm.messageread = 0
	");
	if ($db->num_rows($pms))
	{
		// get ignored users
		if (empty($vbulletin->userinfo['ignorelist']))
		{
			$ignoreusers = array();
		}
		else
		{
			$ignoreusers = explode(' ', $vbulletin->userinfo['ignorelist']);
		}

		$messagelistbits = '';
		$show['pmcheckbox'] = false;
		$numpms = 0;

		require_once(DIR . '/includes/functions_bigthree.php');
		while ($pm = $db->fetch_array($pms))
		{
			($hook = vBulletinHook::fetch_hook('usercp_pmbit')) ? eval($hook) : false;

			if (in_coventry($pm['fromuserid']))
			{
				if (!can_moderate())
				{
					continue;
				}
				else
				{
					eval('$messagelistbits .= "' . fetch_template('pm_messagelistbit_ignore') . '";');
					$numpms ++;
					$show['privatemessages'] = true;
				}
			}
			else if (in_array($pm['fromuserid'], $ignoreusers))
			{
				eval('$messagelistbits .= "' . fetch_template('pm_messagelistbit_ignore') . '";');
				$numpms ++;
				$show['privatemessages'] = true;
			}
			else
			{
				$pm['senddate'] = vbdate($vbulletin->options['dateformat'], $pm['dateline'], 1);
				$pm['sendtime'] = vbdate($vbulletin->options['timeformat'], $pm['dateline']);
				$pm['statusicon'] = 'new';
				$userid =& $pm['fromuserid'];
				$username =& $pm['fromusername'];

				$show['pmicon'] = iif($pm['iconpath'], true, false);
				$show['unread'] = iif(!$pm['messageread'], true, false);

				eval('$userbit = "' . fetch_template('pm_messagelistbit_user') . '";');
				eval('$messagelistbits .= "' . fetch_template('pm_messagelistbit') . '";');
				$numpms ++;
				$show['privatemessages'] = true;
			}
		}
	}
}


// ############################### start subscribed forums ###############################

// get only subscribed forums
cache_ordered_forums(1, 0, $vbulletin->userinfo['userid']);
$show['forums'] = false;
foreach ($vbulletin->forumcache AS $forumid => $forum)
{
	if ($forum['subscribeforumid'] != '')
	{
		$show['forums'] = true;
	}
}
if ($show['forums'])
{
	if ($vbulletin->options['showmoderatorcolumn'])
	{
		cache_moderators();
	}
	else
	{
		cache_moderators($vbulletin->userinfo['userid']);
	}
	fetch_last_post_array();
	$forumbits = construct_forum_bit(-1, 0, 1);
	eval('$forumbits .= "' . fetch_template('forumhome_markread_script') . '";');
	if ($forumshown == 1)
	{
		$show['forums'] = true;
	}
	else
	{
		$show['forums'] = false;
	}
}

// ############################### start new subscribed to threads ###############################

$show['threads'] = false;
$numthreads = 0;

// query thread ids
if (!$vbulletin->options['threadmarking'])
{
	if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
	{
		$lastpost_info = ", IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastposts";

		$tachyjoin = "LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON " .
			"(tachythreadpost.threadid = subscribethread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ')';

		$lastpost_having = "HAVING lastposts > " . $vbulletin->userinfo['lastvisit'];
	}
	else
	{
		$lastpost_info = '';
		$tachyjoin = '';
		$lastpost_having = "AND lastpost > " . $vbulletin->userinfo['lastvisit'];
	}

	$getthreads = $db->query_read_slave("
		SELECT thread.threadid, thread.forumid, thread.postuserid, subscribethread.subscribethreadid
		$lastpost_info
		FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
		INNER JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid)
		$tachyjoin
		WHERE subscribethread.threadid = thread.threadid
			AND subscribethread.userid = " . $vbulletin->userinfo['userid'] . "
			AND thread.visible = 1
			AND subscribethread.canview = 1
		$lastpost_having
	");
}
else
{
	$readtimeout = TIMENOW - ($vbulletin->options['markinglimit'] * 86400);

	if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
	{
		$lastpost_info = ", IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastposts";

		$tachyjoin = "LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON " .
			"(tachythreadpost.threadid = subscribethread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ')';
	}
	else
	{
		$lastpost_info = ', thread.lastpost AS lastposts';
		$tachyjoin = '';
	}

	$getthreads = $db->query_read_slave("
		SELECT thread.threadid, thread.forumid, thread.postuserid,
			IF(threadread.readtime IS NULL, $readtimeout, IF(threadread.readtime < $readtimeout, $readtimeout, threadread.readtime)) AS threadread,
			IF(forumread.readtime IS NULL, $readtimeout, IF(forumread.readtime < $readtimeout, $readtimeout, forumread.readtime)) AS forumread,
			subscribethread.subscribethreadid
			$lastpost_info
		FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
		INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (subscribethread.threadid = thread.threadid)
		LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")
		LEFT JOIN " . TABLE_PREFIX . "forumread AS forumread ON (forumread.forumid = thread.forumid AND forumread.userid = " . $vbulletin->userinfo['userid'] . ")
		$tachyjoin
		WHERE subscribethread.userid = " . $vbulletin->userinfo['userid'] . "
			AND thread.visible = 1
			AND subscribethread.canview = 1
		HAVING lastposts > IF(threadread > forumread, threadread, forumread)
	");
}

if ($totalthreads = $db->num_rows($getthreads))
{
	$forumids = array();
	$threadids = array();
	$killthreads = array();
	while ($getthread = $db->fetch_array($getthreads))
	{
		$forumperms = fetch_permissions($getthread['forumid']);
		if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR ($getthread['postuserid'] != $vbulletin->userinfo['userid'] AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers'])))
		{
			$killthreads[] = $getthread['subscribethreadid'];
			continue;
		}
		$forumids["$getthread[forumid]"] = true;
		$threadids[] = $getthread['threadid'];
	}
	$threadids = implode(',', $threadids);
}
unset($getthread);
$db->free_result($getthreads);

if (!empty($killthreads))
{
	// Update thread subscriptions
	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "subscribethread
		SET canview = 0
		WHERE subscribethreadid IN (" . implode(', ', $killthreads) . ")
	");
}

// if there are some results to show, query the data
if (!empty($threadids))
{
	// get last read info for each thread
	$lastread = array();
	foreach (array_keys($forumids) AS $forumid)
	{
		if ($vbulletin->options['threadmarking'])
		{
			$lastread["$forumid"] = max($vbulletin->forumcache["$forumid"]['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
		}
		else
		{
			$lastread["$forumid"] = max(intval(fetch_bbarray_cookie('forum_view', $forumid)), $vbulletin->userinfo['lastvisit']);
		}
	}

	// get thread preview?
	if ($vbulletin->options['threadpreview'] > 0)
	{
		$previewfield = 'post.pagetext AS preview,';
		$previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = thread.firstpostid)";
	}
	else
	{
		$previewfield = '';
		$previewjoin = '';
	}

	if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
	{
		$lastpost_info = "IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastpost, " .
			"IF(tachythreadpost.userid IS NULL, thread.lastposter, tachythreadpost.lastposter) AS lastposter, " .
			"IF(tachythreadpost.userid IS NULL, thread.lastpostid, tachythreadpost.lastpostid) AS lastpostid";

		$tachyjoin = "LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON " .
			"(tachythreadpost.threadid = thread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ')';
	}
	else
	{
		$lastpost_info = 'thread.lastpost, thread.lastposter, thread.lastpostid';
		$tachyjoin = '';
	}

	$hook_query_fields = $hook_query_joins = $hook_query_where = '';
	($hook = vBulletinHook::fetch_hook('usercp_threads_query')) ? eval($hook) : false;

	$getthreads = $db->query_read_slave("
		SELECT $previewfield
			thread.threadid, thread.title AS threadtitle, forumid, pollid, open, replycount, postusername, postuserid,
			thread.dateline, views, thread.iconid AS threadiconid, notes, thread.visible, $lastpost_info
			" . ($vbulletin->options['threadmarking'] ? ", threadread.readtime AS threadread" : '') . "
			$hook_query_fields
		FROM " . TABLE_PREFIX . "thread AS thread
		" . ($vbulletin->options['threadmarking'] ? " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")" : '') . "
		$previewjoin
		$tachyjoin
		$hook_query_joins
		WHERE thread.threadid IN($threadids)
			$hook_query_where
		ORDER BY lastpost DESC
	");

	require_once(DIR . '/includes/functions_forumdisplay.php');

	// Get Dot Threads
	$dotthreads = fetch_dot_threads_array($threadids);

	// check to see if there are any threads to display. If there are, do so, otherwise, show message
	if ($totalthreads = $db->num_rows($getthreads))
	{
		$threads = array();
		while ($getthread = $db->fetch_array($getthreads))
		{
			// unset the thread preview if it can't be seen
			$forumperms = fetch_permissions($getthread['forumid']);
			if ($vbulletin->options['threadpreview'] > 0 AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
			{
				$getthread['preview'] = '';
			}
			$threads["$getthread[threadid]"] = $getthread;
		}
	}
	unset($getthread);
	$db->free_result($getthreads);

	$show['threadratings'] = false;

	if ($totalthreads)
	{
		$show['threadicons'] = true;

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

		$threadbits = '';
		foreach ($threads AS $threadid => $thread)
		{
			$thread = process_thread_array($thread, $lastread["$thread[forumid]"]);
			$show['unsubscribe'] = true;

			($hook = vBulletinHook::fetch_hook('threadbit_display')) ? eval($hook) : false;

			eval('$threadbits .= "' . fetch_template('threadbit') . '";');
			$numthreads ++;
		}

		$show['threads'] = true;
	}
}

require_once(DIR . '/includes/class_bbcode.php');
$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

$infractions = $db->query_read_slave("
	SELECT points, infraction.*, thread.title, thread.forumid, thread.postuserid, user.username,
	thread.visible AS thread_visible, post.visible, thread.postuserid, IF(ISNULL(post.postid) AND infraction.postid != 0, 1, 0) AS postdeleted
	FROM " . TABLE_PREFIX . "infraction AS infraction
	LEFT JOIN " . TABLE_PREFIX . "post AS post ON (infraction.postid = post.postid)
	LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (infraction.whoadded = user.userid)
	WHERE infraction.userid = " . $vbulletin->userinfo['userid'] . "
	ORDER BY infraction.dateline DESC
	LIMIT 5
");
while ($infraction = $db->fetch_array($infractions))
{
	$show['threadtitle'] = true;
	$show['postdeleted'] = false;
	if ($infraction['postid'] != 0)
	{
		if ($infraction['postdeleted'])
		{
			$show['postdeleted'] = true;
		}
		else if ((!$infraction['visible'] OR !$infraction['thread_visible']) AND !can_moderate($infraction['forumid'], 'canmoderateposts'))
		{
			$show['threadtitle'] = false;
		}
		else if (($infraction['visible'] == 2 OR $infraction['thread_visible'] == 2) AND !can_moderate($infraction['forumid'], 'candeleteposts'))
		{
			$show['threadtitle'] = false;
		}
		else
		{
			$forumperms = fetch_permissions($infraction['forumid']);
			if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
			{
				$show['threadtitle'] = false;
			}
			if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND ($infraction['postuserid'] != $vbulletin->userinfo['userid'] OR $vbulletin->userinfo['userid'] == 0))
			{
				$show['threadtitle'] = false;
			}
		}
	}
	$show['expired'] = $show['reversed'] = $show['neverexpires'] = false;
	$card = ($infraction['points'] > 0) ? 'redcard' : 'yellowcard';
	$infraction['timeline'] = vbdate($vbulletin->options['timeformat'], $infraction['dateline']);
	$infraction['dateline'] = vbdate($vbulletin->options['dateformat'], $infraction['dateline']);
	switch($infraction['action'])
	{
		case 0:
			if ($infraction['expires'] != 0)
			{
				$infraction['expires_timeline'] = vbdate($vbulletin->options['timeformat'], $infraction['expires']);
				$infraction['expires_dateline'] = vbdate($vbulletin->options['dateformat'], $infraction['expires']);
				$show['neverexpires'] = false;
			}
			else
			{
				$show['neverexpires'] = true;
			}
			break;
		case 1:
			$show['expired'] = true;
			break;
		case 2:
			$show['reversed'] = true;
			break;
	}
	if (vbstrlen($infraction['title']) > 25)
	{
		$infraction['title'] = fetch_trimmed_title($infraction['title'], 24);
	}
	$infraction['reason'] = !empty($vbphrase['infractionlevel' . $infraction['infractionlevelid'] . '_title']) ? $vbphrase['infractionlevel' . $infraction['infractionlevelid'] . '_title'] : ($infraction['customreason'] ? $infraction['customreason'] : $vbphrase['n_a']);
	($hook = vBulletinHook::fetch_hook('usercp_infractioninfobit')) ? eval($hook) : false;

	eval('$infractionbits .= "' . fetch_template('userinfraction_infobit') . '";');
	$show['infractions'] = true;
}
unset($bbcode_parser);

require_once(DIR . '/includes/functions_misc.php');

// check if user can be invisible and is invisible
if (!($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['caninvisible']) AND $vbulletin->userinfo['invisible'])
{
	// init user data manager
	$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
	$userdata->set_existing($vbulletin->userinfo);
	$userdata->set_bitfield('options', 'invisible', 0);
	$userdata->save();
}

// draw cp nav bar
construct_usercp_nav('usercp');

$frmjmpsel['usercp'] = 'class="fjsel" selected="selected"';
construct_forum_jump();

($hook = vBulletinHook::fetch_hook('usercp_complete')) ? eval($hook) : false;

eval('$HTML = "' . fetch_template('USERCP') . '";');

// build navbar
$navbits = construct_navbits(array('' => $vbphrase['user_control_panel']));
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('USERCP_SHELL') . '");');

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 18:52, Sat Jul 14th 2007
|| # CVS: $RCSfile$ - $Revision: 16039 $
|| ####################################################################
\*======================================================================*/
?>