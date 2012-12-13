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

define('THIS_SCRIPT', 'search');
define('CSRF_PROTECTION', false);
define('CSRF_SKIP_LIST', '');

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
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
if(file_exists(DIR . '/includes/functions_misc.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_misc.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_misc.php');
}
if(file_exists(DIR . '/includes/functions_forumlist.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_forumlist.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_forumlist.php');
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


// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
function get_user_topic_func($xmlrpc_params){
	return  get_user_topic_or_reply($xmlrpc_params,'user_topic');
}

function get_user_reply_post_func($xmlrpc_params){
	return  get_user_topic_or_reply($xmlrpc_params,'user_reply_post');
}




function get_user_topic_or_reply($xmlrpc_params,$mode){
	global $vbulletin;
	global $permissions;
	global $db;
	global $xmlrpcerruser;
	chdir(CWD1);
	chdir('../');
	$decode_params = php_xmlrpc_decode($xmlrpc_params);



	$return_list = array();
	if(!$decode_params[0])
	{
		$return = array(2,'no  user id param.');
		return return_fault($return);
	}

	$username = htmlspecialchars_uni(mobiquo_encode($decode_params[0],'to_local'));
	$q = "
		SELECT posts, userid, username
		FROM " . TABLE_PREFIX . "user AS user
		WHERE username " .  "= '" . $db->escape_string($username) . "'" ;


	$coventry = fetch_coventry();

	$users = $db->query_read_slave($q);
	if ($db->num_rows($users))
	{
		$userids = array();
		while ($user = $db->fetch_array($users))
		{
			$postsum += $user['posts'];
			$display['users']["$user[userid]"] = $user['username'];
			$userids[] = (in_array($user['userid'], $coventry) AND !can_moderate()) ? -1 : $user['userid'];
		}

		$userids = implode(', ', $userids);

		if ($vbulletin->GPC['starteronly'])
		{
			if ($vbulletin->GPC['showposts'])
			{
				$post_query_logic[50] = "post.userid IN($userids)";
			}
			$thread_query_logic[] = "thread.postuserid IN($userids)";
		}
		// add the userids to the $post_query_logic search conditions
		else
		{
			if ($vbulletin->GPC['showposts'])
			{
				$post_query_logic[50] = "post.userid IN($userids)";
			}
			else
			{	// use the (threadid, userid) index of post to limit the join
				$post_join_query_logic = " AND post.userid IN($userids)";
			}
		}
	}
	else
	{$return = array( 7,'invalid user id '.$decode_params[0]);
	return return_fault($return);

	}
	$vbulletin->GPC['userid'] = $userids;




	if(isset($decode_params[1]) && $decode_params[1] && $decode_params[1] >= 0) {
		$start_num = $decode_params[1] ;
	}
	else{
		$start_num = 0;
	}
	if(isset($decode_params[2]) && $decode_params[2] >= 0){
		$end_num   = $decode_params[2];
	} else {
		$end_num = 19;
	}



	if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['cansearch']))
	{
		$return = array( 20,'security error (user may not have permission to access this feature)');
		return return_fault($return);

	}



	// valid user id?
	if (!$vbulletin->GPC['userid'])
	{
		eval(standard_error(fetch_error('invalidid', $vbphrase['user'], $vbulletin->options['contactuslink'])));
	}

	// get user info
	if ($user = $db->query_first_slave("SELECT userid, username, posts FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC['userid']))
	{
		$searchuser =& $user['username'];
	}
	// could not find specified user
	else
	{
		eval(standard_error(fetch_error('invalidid', $vbphrase['user'], $vbulletin->options['contactuslink'])));
	}
	// #############################################################################
	// build search hash
	$query = '';
	$searchuser = $user['username'];
	$exactname = 1;
	if($mode == 'user_topic' OR $mode == 'participate') {
		$showposts = 0;
		$starteronly  = 1 ;
	}
	else{
		$starteronly = 0 ;
		$showposts = 1 ;
	}
	//$starteronly = (($mode == 'POST') ? 1 : 0);
	$forumchoice = $foruminfo['forumid'];
	$childforums = 1;
	$titleonly = 0;

	$searchdate = 0;
	$beforeafter = 'after';
	$replyless = 0;
	$replylimit = 0;
	$searchthreadid = $vbulletin->GPC['searchthreadid'];


	$searchhash = md5(TIMENOW . "||" . $vbulletin->userinfo['userid'] . "||" . strtolower($searchuser) . "||$exactname||$starteronly||$forumchoice||$childforums||$titleonly||$showposts||$searchdate||$beforeafter||$replyless||$replylimit||$searchthreadid");



	// start search timer
	$searchtime = microtime();

	$forumids = array();
	$noforumids = array();
	// #############################################################################
	// check to see if we should be searching in a particular forum or forums
	if ($vbulletin->GPC['searchthreadid'])
	{
		$showforms = false;
		$sql = "AND thread.threadid = " . $vbulletin->GPC['searchthreadid'];
	}
	else
	{
		if ($forumids = fetch_search_forumids($vbulletin->GPC['forumchoice'], $vbulletin->GPC['childforums']))
		{
			$showforums = true;

		}
		else
		{
			foreach ($vbulletin->forumcache AS $forumid => $forum)
			{
				$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];
				if (($fperms & $vbulletin->bf_ugp_forumpermissions['canview']))
				{
					$forumids[] = $forumid;
				}
			}
			$showforums = false;
		}

		if (empty($forumids))
		{
			return new xmlrpcresp(
			new xmlrpcval(
			array(), 'struct'
			)
			);


			//	eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
		}
		else
		{
			$sql = "AND thread.forumid IN(" . implode(',', $forumids) . ")";
		}
	}

	// query post ids in dateline DESC order...


	$orderedids = array();
	if ($starteronly)
	{
		$threads = $db->query_read_slave("
				SELECT thread.threadid
				FROM " . TABLE_PREFIX . "thread AS thread
				WHERE thread.postuserid = $user[userid]
				$sql
				LIMIT " . (20) . "
			");
				while ($thread = $db->fetch_array($threads))
				{
					$orderedids[] = $thread['threadid'];
				}
	}
	else
	{
		$posts = $db->query_read_slave("
			SELECT postid
			FROM " . TABLE_PREFIX . "post AS post
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)
			WHERE post.userid = $user[userid]
			$sql
			ORDER BY post.dateline DESC
			LIMIT " . ($vbulletin->options['maxresults']) . "
		");
			while ($post = $db->fetch_array($posts))
			{
				$orderedids[] = $post['postid'];
			}
	}
	$db->free_result($posts);
	// did we get some results?
	if (empty($orderedids))
	{
		return new xmlrpcresp(
		new xmlrpcval(
		array(), 'struct'
		)
		);

		//		eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
	}

	// set display terms
	$display = array(
		'words'     => array(),
		'highlight' => array(),
		'common'    => array(),
		'users'     => array($user['userid'] => $user['username']),
		'forums'    => iif($showforums, $display['forums'], 0),
		'options'   => array(
			'starteronly' => $starteronly,
			'childforums' => 1,
			'action'      => 'process'
			)
			);

			// end search timer
			$searchtime = number_format(fetch_microtime_difference($searchtime), 5, '.', '');

			$sort_order = ($showposts ? 'post.dateline' : 'lastpost');



			/*insert query*/
			$db->query_write("
		REPLACE INTO " . TABLE_PREFIX . "search
			(userid, ipaddress, personal,
			searchuser, forumchoice,
			sortby, sortorder, searchtime,
			showposts, orderedids, dateline,
			displayterms, searchhash, completed)
		VALUES
			(" . $vbulletin->userinfo['userid'] . ", '" . $db->escape_string(IPADDRESS) . "', 1,
			'" . $db->escape_string($user['username']) . "', '" . $db->escape_string($forumchoice) . "',
			'$sort_order', 'DESC', $searchtime,
			$showposts, '" . $db->escape_string(implode(',', $orderedids)) . "', " . TIMENOW . ",
			'" . $db->escape_string(serialize($display)) . "', '" . $db->escape_string($searchhash) . "', 1)
	");
			$searchid = $db->insert_id();

			//////////////////////////////////////////////////////
			if($showposts == 0){
				return get_search_result($searchid,0,20,false,false);
			} else {
				return get_search_result($searchid,0,20,false,true);
			}
}
?>
