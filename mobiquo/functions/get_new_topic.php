<?php
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
defined('CWD1') or exit;
require_once(CWD1."/include/functions_search.php");
chdir(CWD1);
chdir('../');
$phrasegroups = array();
define('THIS_SCRIPT', 'search');
define('CSRF_PROTECTION', false);
define('CSRF_SKIP_LIST', '');

$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

if(file_exists('./global.php'.SUFFIX)){
	require_once('./global.php'.SUFFIX);
} else {
	require_once('./global.php');
}
if(file_exists(DIR . '/includes/functions_search.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_search.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_search.php');
}
if(file_exists(DIR . '/includes/functions_forumlist.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_forumlist.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_forumlist.php');
}
if(file_exists(DIR . '/includes/functions_misc.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_misc.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_misc.php');
}
if(file_exists(DIR . '/includes/functions_bigthree.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_bigthree.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_bigthree.php');
}
if(file_exists(DIR . '/includes/functions_forumdisplay.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_forumdisplay.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_forumdisplay.php');
}

if(file_exists(DIR . '/includes/functions_user.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_user.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_user.php');
}
function get_new_topic_func($params){
	global $vbulletin,$db,$show;
	global $permissions;
	global $vbphrase, $stylevar;
	global $newthreads, $dotthreads, $perpage, $ignore;
	chdir(CWD1);
	chdir('../');
	$decode_params = php_xmlrpc_decode($params);


	if(isset($decode_params[0]) && $decode_params[0] && $decode_params[0] >= 0) {
		$start_num = $decode_params[0] ;
	}
	else{
		$start_num = 0;
	}
	if(isset($decode_params[1]) && $decode_params[1]){
		$end_num   = $decode_params[1];
	} else {
		$end_num = 19;
	}
	$thread_num = $end_num-$start_num+1;
	if($thread_num > 50){
		$thread_num = 50;
	}
	if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['cansearch']))
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);

	}



	$_REQUEST['do'] = 'getdaily';
	if ($vbulletin->GPC['days'] < 1)
	{
		$vbulletin->GPC['days'] = 1;
	}
	$datecut = TIMENOW - (24 * 60 * 60 * 3);



	$vbulletin->GPC['sortby'] = 'lastpost';
	$sortby = 'thread.lastpost DESC';


	// build search hash
	$searchhash = md5($vbulletin->userinfo['userid'] . IPADDRESS . $forumid . $vbulletin->GPC['days'] . $vbulletin->userinfo['lastvisit'] . $vbulletin->GPC['include'] . '|' . $vbulletin->GPC['exclude']);

	// start search timer
	$searchtime = microtime();



	$forumids = array_keys($vbulletin->forumcache);


	// set display terms
	$display = array(
		'words'     => array(),
		'highlight' => array(),
		'common'    => array(),
		'users'     => array(),
		'forums'    => $display['forums'],
		'options'   => array(
			'starteronly' => false,
			'childforums' => true,
			'action'      => $_REQUEST['do']
	),
	);



	// get moderator cache for forum password purposes
	if ($vbulletin->userinfo['userid'])
	{
		cache_moderators();
	}

	// get forum ids for all forums user is allowed to view
	foreach ($forumids AS $key => $forumid)
	{
		if (is_array($includearray) AND empty($includearray["$forumid"]))
		{
			unset($forumids["$key"]);
			continue;
		}

		$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];
		$forum =& $vbulletin->forumcache["$forumid"];

		if (!($fperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($fperms & $vbulletin->bf_ugp_forumpermissions['cansearch']) OR !verify_forum_password($forumid, $forum['password'], false))
		{
			unset($forumids["$key"]);
		}
	}

	if (empty($forumids))
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}


	$marking_join = '';
	$lastpost_where = "AND thread.lastpost >= $datecut";
	$post_lastpost_where = "AND post.dateline >= $datecut";



	#even though showresults would filter thread.visible=0, thread.visible remains in these 2 queries so that the 4 part index on thread can be used.
	$orderedids = array();


	$threads = $db->query_read_slave("
			SELECT thread.threadid
			FROM " . TABLE_PREFIX . "thread AS thread
			$marking_join
			WHERE thread.forumid IN(" . implode(', ', $forumids) . ")
			$lastpost_where
				AND thread.visible IN (0,1,2)
				AND thread.sticky IN (0,1)
				AND thread.open <> 10
			ORDER BY $sortby
			LIMIT ". intval($vbulletin->options['maxresults'])
			);

			//	$lastpost_where
			while ($thread = $db->fetch_array($threads))
			{
				$orderedids[] = $thread['threadid'];
			}



			$announcementids = array();
			if ($vbulletin->userinfo['userid'])
			{
				$basetime = TIMENOW;
				$mindate = $basetime - 2592000; // 30 days
				$announcements = $db->query_read_slave("
			SELECT announcement.announcementid
			FROM " . TABLE_PREFIX . "announcement AS announcement
			LEFT JOIN " . TABLE_PREFIX . "announcementread AS ar ON (announcement.announcementid = ar.announcementid AND ar.userid = " . $vbulletin->userinfo['userid'] . ")
			WHERE
				ISNULL(ar.userid) AND
				startdate < $basetime AND
				startdate > $mindate AND
				enddate > $basetime AND
				forumid IN(-1, " . implode(', ', $forumids) . ")
		");
				while ($announcement = $db->fetch_array($announcements))
				{
					$announcementids[] = $announcement['announcementid'];
				}
			}

			if (empty($orderedids) AND empty($announcementids))
			{

					
				return new xmlrpcresp(new xmlrpcval(array(),"array"));
					

			}

			$sql_ids = $db->escape_string(implode(',', $orderedids));
			$sql_aids = $db->escape_string(implode(',', $announcementids));
			unset($orderedids, $announcementids);

			// check for previous searches
			if ($search = $db->query_first("SELECT searchid FROM " . TABLE_PREFIX . "search AS search WHERE userid = " . $vbulletin->userinfo['userid'] . " AND searchhash = '" . $db->escape_string($searchhash) . "' AND orderedids = '$sql_ids' AND announceids = '$sql_aids' AND completed = 1"))
			{
				$searchid  = $search[searchid];
			} else{

				// end search timer
				$searchtime = number_format(fetch_microtime_difference($searchtime), 5, '.', '');


				/*insert query*/
				$db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "search (userid, showposts, ipaddress, personal, forumchoice, sortby, sortorder, searchtime, orderedids, announceids, dateline, displayterms, searchhash, completed)
				VALUES (" . $vbulletin->userinfo['userid'] . ", " . intval($vbulletin->GPC['showposts']) . ", '" . $db->escape_string(IPADDRESS) . "', 1, '" . $db->escape_string($foruminfo['forumid']) . "', '" . $db->escape_string($vbulletin->GPC['sortby']) . "', 'DESC', $searchtime, '$sql_ids', '$sql_aids', " . TIMENOW . ", '" . $db->escape_string(serialize($display)) . "', '" . $db->escape_string($searchhash) . "', 1)
			");
				$searchid = $db->insert_id();

			}

			return get_search_result($searchid,$start_num,$end_num,false,false);
}


?>
