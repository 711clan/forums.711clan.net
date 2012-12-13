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

function search_topic_func($xmlrpc_params){

	return	search_topic_or_post($xmlrpc_params,false);
}

function search_post_func($xmlrpc_params){

	return	search_topic_or_post($xmlrpc_params,true);
}

function search_topic_or_post($xmlrpc_params,$ispost){
	global $vbulletin;
	global $permissions;
	global $db;
	global $xmlrpcerruser;
	chdir(CWD1);
	chdir('../');
	$decode_params = php_xmlrpc_decode($xmlrpc_params);
	$search_query = $decode_params[0];

	if(isset($decode_params[1]) && $decode_params[1] && $decode_params[1] >= 0) {
		$start_num = $decode_params[1] ;
	}
	else{
		$start_num = 0;
	}
	if(isset($decode_params[2]) && $decode_params[2]){
		$end_num   = $decode_params[2];
	} else {
		$end_num = 19;
	}

	if(isset($decode_params[3]) && $decode_params[3]){
		$mobqiuo_search_id   = $decode_params[3];
	}

	//	if ($vbulletin->userinfo['userid'] == 0)
	//	{
	//		 $return = array(20,'security error (user may not have permission to access this feature)');
	//		 return return_fault($return);
	//	}

	if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['cansearch']))
	{
	 $return = array(20,'security error (user may not have permission to access this feature)');
	 return return_fault($return);
	}

	if (!$vbulletin->options['enablesearches'])
	{
	 $return = array(20,'security error (user may not have permission to access this feature)');
	 return return_fault($return);
	}
	if($mobqiuo_search_id){
		return get_search_result($mobqiuo_search_id,$start_num,$end_num,true,$ispost);
	}
	// #############################################################################

	$globals = array(
	'query'          => TYPE_STR,
	'searchuser'     => TYPE_STR,
	'exactname'      => TYPE_BOOL,
	'starteronly'    => TYPE_BOOL,
	'tag'            => TYPE_STR, // TYPE_STR, because that's what the error cond for intro expects
	'forumchoice'    => TYPE_ARRAY,
	'prefixchoice'   => TYPE_ARRAY_NOHTML,
	'childforums'    => TYPE_BOOL,
	'titleonly'      => TYPE_BOOL,
	'showposts'      => TYPE_BOOL,
	'searchdate'     => TYPE_NOHTML,
	'beforeafter'    => TYPE_NOHTML,
	'sortby'         => TYPE_NOHTML,
	'sortorder'      => TYPE_NOHTML,
	'replyless'      => TYPE_UINT,
	'replylimit'     => TYPE_UINT,
	'searchthreadid' => TYPE_UINT,
	'saveprefs'      => TYPE_BOOL,
	'quicksearch'    => TYPE_BOOL,

	'exclude'        => TYPE_NOHTML,
	'nocache'        => TYPE_BOOL,
	'ajax'           => TYPE_BOOL,
	'humanverify'    => TYPE_ARRAY,
	'userid'         => TYPE_UINT,
	);

	if ($vbulletin->options['fulltextsearch'])
	{
		if ($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['cansearchft_bool'])
		{
			// use boolean when user has boolean, ignore NL
			$vbulletin->GPC['searchtype'] = 1;
		}
		else
		{
			// user only has permission to use nl search
			$vbulletin->GPC['searchtype'] = 0;
		}
	}

	$errors = array();



	$vbulletin->input->clean_array_gpc('r', $globals);
	($hook = vBulletinHook::fetch_hook('search_process_start')) ? eval($hook) : false;
	if (!$vbulletin->options['threadtagging'])
	{
		//  tagging disabled, don't let them search on it
		$vbulletin->GPC['tag'] = '';
	}

	// #############################################################################
	// start search timer
	$searchstart = microtime();




	$vbulletin->GPC['query'] = $search_query;
	$vbulletin->GPC['showposts']  = $ispost;
	$have_search_limit = (
	$vbulletin->GPC['query']
	OR $vbulletin->GPC['searchuser']
	OR $vbulletin->GPC['replyless']
	OR $vbulletin->GPC['tag']
	OR $have_prefix_limit
	);

	if (!$have_search_limit)
	{
		$errors[] = 'searchspecifyterms';
	}
	if (is_array($errors)) {
			
		if ($errors['sphinx']) {
			($hook = vBulletinHook::fetch_hook('search_start')) ? eval($hook) : false;
			if(isset($searchid)){
				unset($orderedids,$res,$cl);
				return get_search_result($searchid,$start_num,$end_num,true,$ispost);
			} else {
				return new xmlrpcval(
				array(
				                                'total_topic_num' => new xmlrpcval(0,'int'),
				                                'topics' => new xmlrpcval(array(),'array'),
				),
				                        'struct'
				                        );
			}
		}
	}

	if (empty($errors))
	{
		// #############################################################################

		// if searching for only a tag, we must show results as threads
		if ($vbulletin->GPC['tag'] AND empty($vbulletin->GPC['query']) AND empty($vbulletin->GPC['searchuser']))
		{
			$vbulletin->GPC['showposts'] = false;
		}

		// #############################################################################
		// make array of search terms for back referencing
		$searchterms = array();
		foreach ($globals AS $varname => $value)
		{
			if ($varname == 'forumchoice' AND is_array($vbulletin->GPC['forumchoice']))
			{
				$searchterms["$varname"] = $vbulletin->GPC['forumchoice'];
			}
			else
			{
				$searchterms["$varname"] = $vbulletin->GPC["$varname"];
			}
		}

		// #############################################################################
		// if query string is specified, check syntax and replace common syntax errors
		if ($vbulletin->GPC['query'])
		{
			// are we using FT and boolean search?
			if ($vbulletin->options['fulltextsearch'] AND $vbulletin->GPC['searchtype'])
			{
				// look for entire words that consist of "&#1234;". MySQL boolean
				// search will tokenize them seperately. Wrap them in quotes if they're
				// not already to emulate search for exactly that word.
				$query = explode('"', $vbulletin->GPC['query']);
				$query_part_count = count($query);

				$vbulletin->GPC['query'] = '';
				for ($i = 0; $i < $query_part_count; $i++)
				{
					// exploding by " means the 0th, 2nd, 4th... entries in the array
					// are outside of quotes
					if ($i % 2 == 1)
					{
						// 1st, 3rd.. entry = in quotes
						$vbulletin->GPC['query'] .= '"' . $query["$i"] . '"';
					}
					else
					{
						// look for words that are contain &#1234;, ., or - and quote them (more logical behavior, 24676)
						$query_parts = '';
						$space_skipped = false;

						foreach (preg_split('#[ \r\n\t]#s', $query["$i"]) AS $query_part)
						{
							if ($space_skipped)
							{
								$query_parts .= ' ';
							}
							$space_skipped = true;

							if (preg_match('/(&#[0-9]+;|\.|-)/s', $query_part))
							{
								$query_parts .= '"' . $query_part . '"';
							}
							else
							{
								$query_parts .= $query_part;
							}
						}

						$vbulletin->GPC['query'] .= $query_parts;
					}
				}

				$vbulletin->GPC['query'] = preg_replace(
					'#"([^"]+)"#sie',
					"stripslashes(str_replace(' ' , '*', '\\0'))",
				$vbulletin->GPC['query']
				);
				// what about replacement words??
			}
			$vbulletin->GPC['query'] = sanitize_search_query($vbulletin->GPC['query'], $errors);
		}

		if (empty($errors))
		{

			// #############################################################################
			// get forums in which to search
			$forumchoice = implode(',', fetch_search_forumids($vbulletin->GPC['forumchoice'], $vbulletin->GPC['childforums']));

			// get prefixes
			if (in_array('', $vbulletin->GPC['prefixchoice']) OR empty($vbulletin->GPC['prefixchoice']))
			{
				// any prefix
				$vbulletin->GPC['prefixchoice'] = array();
				$prefixchoice = '';
				$display_prefixes = array();
			}
			else
			{
				$vbulletin->GPC['prefixchoice'] = array_unique($vbulletin->GPC['prefixchoice']);
				$prefixchoice = implode(',', $vbulletin->GPC['prefixchoice']);
				$display_prefixes = $vbulletin->GPC['prefixchoice'];
			}

			// #############################################################################
			// get correct sortby value
			$vbulletin->GPC['sortby'] = strtolower($vbulletin->GPC['sortby']);
			switch($vbulletin->GPC['sortby'])
			{
				// sort variables that don't need changing
				case 'title':
				case 'views':
				case 'lastpost':
				case 'replycount':
				case 'postusername':
				case 'rank':
					break;

					// sort variables that need changing
				case 'forum':
					$vbulletin->GPC['sortby'] = 'forum.title';
					break;

				case 'threadstart':
					$vbulletin->GPC['sortby'] = 'thread.dateline';
					break;

					// set default sortby if not specified or unrecognized
				default:
					$vbulletin->GPC['sortby'] = 'lastpost';
			}

			// #############################################################################
			// if showing results as posts, translate the $sortby variable
			if ($vbulletin->GPC['showposts'])
			{
				switch($vbulletin->GPC['sortby'])
				{
					case 'title':
						$vbulletin->GPC['sortby'] = 'thread.title';
						break;
					case 'lastpost':
						$vbulletin->GPC['sortby'] = 'post.dateline';
						break;
					case 'postusername':
						$vbulletin->GPC['sortby'] = 'username';
						break;
				}
			}

			// #############################################################################
			// get correct sortorder value
			$vbulletin->GPC['sortorder'] = strtolower($vbulletin->GPC['sortorder']);
			switch($vbulletin->GPC['sortorder'])
			{
				case 'ascending':
					$vbulletin->GPC['sortorder'] = 'ASC';
					break;

				default:
					$vbulletin->GPC['sortorder'] = 'DESC';
					break;
			}

			// #############################################################################
			// build search hash
			$searchhash = md5(strtolower($vbulletin->GPC['query']) . "||" . strtolower($vbulletin->GPC['searchuser']) . '||' . strtolower($vbulletin->GPC['tag']) . '||' . $vbulletin->GPC['exactname'] . '||' . $vbulletin->GPC['starteronly'] . "||$forumchoice||$prefixchoice||" . $vbulletin->GPC['childforums'] . '||' . $vbulletin->GPC['titleonly'] . '||' . $vbulletin->GPC['showposts'] . '||' . $vbulletin->GPC['searchdate'] . '||' . $vbulletin->GPC['beforeafter'] . '||' . $vbulletin->GPC['replyless'] . '||' . $vbulletin->GPC['replylimit'] . '||' . $vbulletin->GPC['searchthreadid'] . '||' . $vbulletin->GPC['exclude'] . iif($vbulletin->options['fulltextsearch'], '||' . $vbulletin->GPC['searchtype']));

			// #############################################################################
			// search for already existing searches...
			if (!$vbulletin->GPC['nocache'])
			{
				$getsearches = $db->query_read("
					SELECT * FROM " . TABLE_PREFIX . "search AS search
					WHERE searchhash = '" . $db->escape_string($searchhash) . "'
						AND userid = " . $vbulletin->userinfo['userid'] . "
						AND completed = 1
				");
				if ($numsearches = $db->num_rows($getsearches))
				{
					$highScore = 0;
					while ($getsearch = $db->fetch_array($getsearches))
					{
						// is $sortby the same?
						if ($getsearch['sortby'] == $vbulletin->GPC['sortby'])
						{
							if ($getsearch['sortorder'] == $vbulletin->GPC['sortorder'])
							{
								// search matches exactly
								$search = $getsearch;
								$highScore = 3;
							}
							else if ($highScore < 2)
							{
								// search matches but needs order reversed
								$search = $getsearch;
								$highScore = 2;
							}
						}
						// $sortby is different
						else if ($highScore < 1)
						{
							// search matches but needs total re-ordering
							$search = $getsearch;
							$highScore = 1;
						}
					}
					unset($getsearch);
					$db->free_result($getsearches);

					// check our results and decide what to do
					switch ($highScore)
					{
						// #############################################################################
						// found a saved search that matches perfectly
						case 3:

							$searchtime = fetch_microtime_difference($searchstart);

							// redirect to saved search

							break;

							// #############################################################################
							// found a saved search and just need to reverse sort order
						case 2:
							// reverse sort order
							$search['orderedids'] = array_reverse(explode(',', $search['orderedids']));
							// stop search timer
							$searchtime = number_format(fetch_microtime_difference($searchstart), 5, '.', '');

							// insert new search into database
							/*insert query*/
							$db->query_write("
								REPLACE INTO " . TABLE_PREFIX . "search
									(userid, titleonly, ipaddress, personal, query, searchuser, forumchoice, prefixchoice,
									sortby, sortorder, searchtime, showposts, orderedids, dateline, searchterms,
									displayterms, searchhash, completed)
								VALUES
									(" . $vbulletin->userinfo['userid'] . ",
									" . intval($vbulletin->GPC['titleonly']) . ",
									'" . $db->escape_string(IPADDRESS) . "',
									" . ($vbulletin->options['searchsharing'] ? 0 : 1) . ",
									'" . $db->escape_string($search['query']) . "',
									'" . $db->escape_string($search['searchuser']) . "',
									'" . $db->escape_string($search['forumchoice']) . "',
									'" . $db->escape_string($search['prefixchoice']) . "',
									'" . $db->escape_string($search['sortby']) . "',
									'" . $db->escape_string($vbulletin->GPC['sortorder']) . "',
									$searchtime,
									" . intval($vbulletin->GPC['showposts']) . ",
									'" . implode(',', $search['orderedids']) . "',
									" . TIMENOW . ",
									'" . $db->escape_string($search['searchterms']) . "',
									'" . $db->escape_string($search['displayterms']) . "',
									'" . $db->escape_string($searchhash) . "',
									1)
							");
									// redirect to new search result
									break;

									// #############################################################################
									// Found a search with correct query conditions, but ORDER BY clause needs to be totally redone
						case 1:
							if ($vbulletin->GPC['sortby'] == 'rank' OR $search['sortby'] == 'rank')
							{
								// if we are changing to or from a relevancy search, we need to re-do the search
								break;
							}
							else
							{
								// re order search items
								$search['orderedids'] = iif($search['showposts'], 'postid', 'threadid') . " IN($search[orderedids])";
								$search['orderedids'] = sort_search_items($search['orderedids'], $search['showposts'], $vbulletin->GPC['sortby'], $vbulletin->GPC['sortorder']);
								// stop search timer
								$searchtime = number_format(fetch_microtime_difference($searchstart), 5, '.', '');

								// insert new search into database
								/*insert query*/
								$db->query_write("
									REPLACE INTO " . TABLE_PREFIX . "search (userid, titleonly, ipaddress, personal, query, searchuser, forumchoice, sortby, sortorder, searchtime, showposts, orderedids, dateline, searchterms, displayterms, searchhash, completed)
									VALUES (
										" . $vbulletin->userinfo['userid'] . ",
										" . intval($vbulletin->GPC['titleonly']) . ",
										'" . $db->escape_string(IPADDRESS) . "',
										" . ($vbulletin->options['searchsharing'] ? 0 : 1) . ",
										'" . $db->escape_string($search['query']) . "',
										'" . $db->escape_string($search['searchuser']) . "',
										'" . $db->escape_string($search['forumchoice']) . "',
										'" . $db->escape_string($vbulletin->GPC['sortby']) . "',
										'" . $db->escape_string($vbulletin->GPC['sortorder']) .
										"', $searchtime,
										$search[showposts],
										'" . implode(',', $search['orderedids']) . "',
										" . TIMENOW . ",
										'" . $db->escape_string(serialize($searchterms)) . "',
										'" . $db->escape_string($search['displayterms']) . "',
										'" . $db->escape_string($searchhash) . "',
										1
									)
								");
											
										break;
							}
					}
				}
			}

			// now we know this will be a unique search, put a placeholder in
			// for the floodcheck
			/*insert query*/
			$db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "search
					(userid, titleonly, ipaddress, personal, query, searchuser, forumchoice, prefixchoice, sortby, sortorder, searchtime, showposts, orderedids, dateline, searchterms, displayterms, searchhash, completed)
				VALUES (
					" . $vbulletin->userinfo['userid'] . ",
					" . intval($vbulletin->GPC['titleonly']) . " ,
					'" . $db->escape_string(IPADDRESS) . "',
					" . ($vbulletin->options['searchsharing'] ? 0 : 1) . ",
					'" . $db->escape_string($vbulletin->GPC['query']) . "',
					'" . $db->escape_string($vbulletin->GPC['searchuser']) . "',
					'" . $db->escape_string($forumchoice) . "',
					'" . $db->escape_string($prefixchoice) . "',
					'" . $db->escape_string($vbulletin->GPC['sortby']) . "',
					'" . $db->escape_string($vbulletin->GPC['sortorder']) . "',
					0,
					" . intval($vbulletin->GPC['showposts']) . ",
					'',
					" . TIMENOW . ",
					'" . $db->escape_string(serialize($searchterms)) . "',
					'',
					'" . $db->escape_string($searchhash) . "',
					0
				)
			");

			// #############################################################################
			// #############################################################################
			// if we got this far we need to do a full search
			// #############################################################################
			// #############################################################################

			// $post_query_logic stores all the SQL conditions for our search in posts
			$post_query_logic = array();

			// $thread_query_logic stores all SQL conditions for the search in threads
			$thread_query_logic = array();

			// $words stores all the search words with their word IDs
			$words = array(
				'AND' => array(),
				'OR' => array(),
				'NOT' => array(),
				'COMMON' => array()
			);

			// $queryWords provides a way to talk to words within the $words array
			$queryWords = array();

			// $display - stores a list of things searched for
			$display = array(
				'words' => array(),
				'highlight' => array(),
				'common' => array(),
				'users' => array(),
				'forums' => $display['forums'],
				'prefixes' => $display_prefixes,
				'tag' => htmlspecialchars_uni($vbulletin->GPC['tag']),
				'options' => array(
					'starteronly' => $vbulletin->GPC['starteronly'],
					'childforums' => $vbulletin->GPC['childforums'],
					'action' => $_REQUEST['do']
			)
			);

			$postscores = array();


			// #############################################################################
			// ####################### START USER QUERY LOGIC ##############################
			// #############################################################################
			$postsum = 0;
		}

		$tag_join = '';
		if ($vbulletin->GPC['tag'])
		{
			$verified_tag = $db->query_first_slave("
				SELECT tagid, tagtext
				FROM " . TABLE_PREFIX . "tag
				WHERE tagtext = '" . $db->escape_string(htmlspecialchars_uni($vbulletin->GPC['tag'])) . "'
			");
			if (!$verified_tag)
			{
				$errors[] = 'invalid_tag_specified';
			}
			else
			{
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "tagsearch (tagid, dateline) VALUES (" . $verified_tag['tagid'] . ", " . TIMENOW . ")");

				$tag_join = "INNER JOIN " . TABLE_PREFIX . "tagthread AS tagthread ON (tagthread.tagid = $verified_tag[tagid] AND tagthread.threadid = thread.threadid)";
			}
		}

		if (empty($errors))
		{
			// #############################################################################
			// ########################## START WORD QUERY LOGIC ###########################
			// #############################################################################
			if ($vbulletin->GPC['query'] AND (!$vbulletin->options['fulltextsearch'] OR ($vbulletin->options['fulltextsearch'] AND $vbulletin->GPC['searchtype'])))
			{
				$querysplit = $vbulletin->GPC['query'];
				// split string into seperate words and back again, this will deal with MB languages without space delimiters
				$querysplit = implode(' ', split_string($querysplit));

				// #############################################################################
				// if we are doing a relevancy sort, use all AND and OR words as OR
				if ($vbulletin->GPC['sortby'] == 'rank')
				{
					$not = '';
					while (preg_match_all('# -(.*) #siU', " $querysplit ", $regs))
					{
						foreach ($regs[0] AS $word)
						{
							$not .= ' ' . trim($word);
							$querysplit = trim(str_replace($word, ' ', " $querysplit "));
						}
					}
					$querysplit = preg_replace('# (OR )*#si', ' OR ', $querysplit) . $not;
				}
				// #############################################################################

				// strip out common words from OR clauses pt1
				if (preg_match_all('#OR ([^\s]+) #sU', "$querysplit ", $regs))
				{
					foreach ($regs[1] AS $key => $word)
					{
						if (!verify_word_allowed($word))
						{
							$display['common'][] = $word;
							$querysplit = trim(str_replace($regs[0]["$key"], '', "$querysplit "));
						}
					}
				}
				// strip out common words from OR clauses pt2
				if (preg_match_all('# ([^\s]+) OR#sU', " $querysplit", $regs))
				{
					foreach ($regs[1] AS $key => $word)
					{
						if (!verify_word_allowed($word))
						{
							$display['common'][] = $word;
							$querysplit = trim(str_replace($regs[0]["$key"], ' ', " $querysplit "));
						}
					}
				}

				// regular expressions to match query syntax
				$syntax = array(
					'NOT' => '/( -[^\s]+)/si',
					'OR' => '#( ([^\s]+)(( OR [^\s]+)+))#si',
					'AND' => '/(\s|\+)+/siU'
					);

					// #############################################################################
					// find NOT clauses
					if (preg_match_all($syntax['NOT'], " $querysplit", $regs))
					{
						foreach ($regs[0] AS $word)
						{
							$word = substr(trim($word), 1);
							if (verify_word_allowed($word))
							{
								// word is okay - add it to the list of NOT words to be queried
								$words['NOT']["$word"] = 'NOT';
								$queryWords["$word"] =& $words['NOT']["$word"];
							}
							else
							{
								// word is bad or unindexed - add to list of common words
								$display['common'][] = $word;
							}
						}
						$querysplit = preg_replace($syntax['NOT'], ' ', " $querysplit");
					}

					// #############################################################################
					// find OR clauses
					if (preg_match_all($syntax['OR'], " $querysplit", $regs))
					{
						foreach ($regs[0] AS $word)
						{
							$word = trim($word);
							$orBits = explode(' OR ', $word);
							$checkwords = array();
							foreach ($orBits AS $orBit)
							{
								if (verify_word_allowed($orBit))
								{
									// word is okay - add it to the list of OR words for this clause
									$checkwords[] = $orBit;
								}
								else
								{
									// word is bad or unindexed - add to list of common words
									$display['common'][] = $orBit;
								}
							}

							// check to see how many words we have in the current OR clause
							switch(sizeof($checkwords))
							{
								case 0:
									// all words were bad or not indexed
									if (sizeof($display['common']) > 0)
									{
										$displayCommon = "<p>$vbphrase[words_very_common] : <b>" . implode('</b>, <b>', htmlspecialchars_uni($display['common'])) . '</b></p>';
									}
									else
									{
										$displayCommon = '';
									}
									$errors[] = array('searchnoresults', $displayCommon);
									break;

								case 1:
									// just one word is okay - use it as an AND word instead of an OR
									$word = implode('', $checkwords);
									$words['AND']["$word"] = 'AND';
									$queryWords["$word"] =& $words['AND']["$word"];
									break;

								default:
									// two or more words were okay - use them as an OR clause
									foreach ($checkwords AS $checkword)
									{
										$words['OR']["$word"]["$checkword"] = 'OR';
										$queryWords["$checkword"] =& $words['OR']["$word"]["$checkword"];
									}
									break;
							}
						}
						$querysplit = preg_replace($syntax['OR'], '', " $querysplit");
					}

					// #############################################################################
					// other words must be required (AND)
					foreach (preg_split($syntax['AND'], $querysplit, -1, PREG_SPLIT_NO_EMPTY) AS $word)
					{
						if (verify_word_allowed($word))
						{
							// word is okay - add it to the list of AND words to be queried
							$words['AND']["$word"] = 'AND';
							$queryWords["$word"] =& $words['AND']["$word"];
						}
						else
						{
							// word is bad or unindexed - add to list of common words
							$display['common'][] = $word;
						}
					}

					if (sizeof($display['common']) > 0)
					{
						$displayCommon = "<p>$vbphrase[words_very_common] : <b>" . implode('</b>, <b>', htmlspecialchars_uni($display['common'])) . '</b></p>';
					}
					else
					{
						$displayCommon = '';
					}

					// now that we've checked all the words, are there still some terms to search with?
					if (empty($queryWords) AND empty($display['users']))
					{
						// all search words bad or unindexed
						$errors[] = array('searchnoresults', $displayCommon);
					}

					if (empty($errors))
					{
						if (!$vbulletin->options['fulltextsearch'])
						{
							// #############################################################################
							// get highlight words (part 1)
							foreach ($queryWords AS $word => $wordtype)
							{
								if ($wordtype != 'NOT')
								{
									$display['highlight'][] = $word;
								}
							}

							// #############################################################################
							// query words from word and postindex tables to get post ids
							// #############################################################################
							foreach ($queryWords AS $word => $wordtype)
							{
								// should remove characters just like we do when we insert into post index
								$queryword = preg_replace('#[()"\'!\#{};]|\\\\|:(?!//)#s', '', $word);

								// make sure word is safe to insert into the query
								$queryword = sanitize_word_for_sql($queryword);

								if ($vbulletin->options['allowwildcards'])
								{
									$queryword = str_replace('*', '%', $queryword);
								}
								$getwords = $db->query_read_slave("
								SELECT wordid, title FROM " . TABLE_PREFIX . "word
								WHERE title LIKE('$queryword')
							");
								if ($db->num_rows($getwords))
								{
									// found some results for current word
									$wordids = array();
									while ($getword = $db->fetch_array($getwords))
									{
										$wordids[] = $getword['wordid'];
									}
									// query post ids for current word...
									// if $titleonly is specified, also get the value of postindex.intitle
									$postmatches = $db->query_read_slave("
									SELECT postid" . iif($vbulletin->GPC['titleonly'], ', intitle') . iif($vbulletin->GPC['sortby'] == 'rank', ", score AS origscore,
										CASE intitle
											WHEN 1 THEN score + " . $vbulletin->options['posttitlescore'] . "
											WHEN 2 THEN score + " . ($vbulletin->options['posttitlescore'] + $vbulletin->options['threadtitlescore']) . "
											ELSE score
										END AS score") . "
									FROM " . TABLE_PREFIX . "postindex
									WHERE wordid IN(" . implode(',', $wordids) . ") " . ($vbulletin->GPC['titleonly'] ? " AND intitle = 2" : "") . "
								");
									if ($db->num_rows($postmatches) == 0)
									{
										if ($wordtype == 'AND')
										{
											// could not find any posts containing required word
											$errors[] = array('searchnoresults', $displayCommon);
											break;
										}
										else
										{
											// Could not find any posts containing word
											// remove this word from the $queryWords array so we don't use it in the posts query
											unset($queryWords["$word"]);
										}
									}
									else
									{
										// reset the $queryWords entry for current word
										$queryWords["$word"] = array();

										// check that word exists in the title
										if ($vbulletin->GPC['titleonly'])
										{
											while ($postmatch = $db->fetch_array($postmatches))
											{
												if ($postmatch['intitle'])
												{
													$bonus = iif(isset($postscores["$postmatch[postid]"]), $vbulletin->options['multimatchscore'], 0);
													$postscores["$postmatch[postid]"] += $postmatch['score'] + $bonus;
													$queryWords["$word"][] = $postmatch['postid'];
												}
											}
										}
										// don't bother checking that word exists in the title
										else
										{
											while ($postmatch = $db->fetch_array($postmatches))
											{
												$bonus = iif(isset($postscores["$postmatch[postid]"]), $vbulletin->options['multimatchscore'], 0);
												$postscores["$postmatch[postid]"] += $postmatch['score'] + $bonus;
												$queryWords["$word"][] = $postmatch['postid'];
											}
										}
									}
									// free SQL memory for postids query
									unset($postmatch);
									$db->free_result($postmatches);
								}
								else
								{
									if ($wordtype == 'AND')
									{
										// could not find required word in the database
										$errors[] = array('searchnoresults', $displayCommon);
										break;
									}
									else
									{
										// Could not find word in the database
										// remove this word from the $queryWords array so we don't use it in the posts query
										unset($queryWords["$word"]);
									}
								}
								unset($getword);
								$db->free_result($getwords);
							}

							if (empty($errors))
							{
								// #############################################################################
								// get highlight words (part 2);
								foreach ($display['highlight'] AS $key => $word)
								{
									if (!isset($queryWords["$word"]))
									{
										unset($display['highlight']["$key"]);
									}
								}

								// #############################################################################
								// get posts with logic
								$requiredposts = array();

								// if we are searching in a thread, the required posts MUST come from the thread we are searching!
								if ($vbulletin->GPC['searchthreadid'])
								{
									$q = "
									SELECT postid FROM " . TABLE_PREFIX . "post
									WHERE threadid = " . $vbulletin->GPC['searchthreadid'] . "
								";
									$posts = $db->query_read_slave($q);
									if ($db->num_rows($posts) == 0)
									{
										$errors[] = array('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink']);
									}
									else
									{
										while ($post = $db->fetch_array($posts))
										{
											$requiredposts[0][] = $post['postid'];
										}
										unset($post);
									}
									$db->free_result($posts);
								}

								// #############################################################################
								// get AND clauses
								if (!empty($words['AND']) AND empty($errors))
								{
									// intersect the post ids for all AND words - Note: array_intersect() IS BROKEN IN PHP 4.0.4
									foreach (array_keys($words['AND']) AS $word)
									{
										$requiredposts[] =& $queryWords["$word"];
									}
								}

								// #############################################################################
								// get OR clauses
								if (!empty($words['OR']) AND empty($errors))
								{
									$or = array();
									// run through each OR clause
									foreach ($words['OR'] AS $orClause => $orWords)
									{
										// get the post ids for each OR word
										$checkwords = array();
										foreach (array_keys($orWords) AS $word)
										{
											if (isset($queryWords["$word"]))
											{
												$checkwords[] = $queryWords["$word"];
											}
										}

										// check to see that we still have valid OR clauses
										switch(sizeof($checkwords))
										{
											case 0:
												// no matches for any of the OR words in current clause - show no matches error
												$errors[] = array('searchnoresults', $displayCommon);
												break 2;

											case 1:
												// found only one matching word from the current OR clause - translate this OR into an AND#
												$requiredposts[] = $checkwords[0];
												break;

											default:
												// found matches for two or more terms in the OR clause - process it as an OR
												foreach ($checkwords AS $checkword)
												{
													if (!empty($checkword))
													{
														$postids[] = implode(', ', $checkword);
													}
												}
												if (sizeof($postids) > 0)
												{
													$or[] = '(postid IN(' . implode(') OR postid IN(', $postids) . '))';
												}
												break;
										}
									}

									// now add the remaining OR terms to the query if there are any
									if (!empty($or))
									{
										$post_query_logic = array_merge($post_query_logic, $or);
									}

									// clean up variables
									unset($or, $orClause, $orWords, $word, $checkwords, $postids);
								}

								// #############################################################################
								// now stick together the AND words and any OR words where there was only one word found
								if (!empty($requiredposts) AND empty($errors))
								{
									// intersect all required post ids to get a definitive list of posts
									// that MUST be returned by the posts query
									$ANDs = false;

									foreach ($requiredposts AS $postids)
									{
										if (is_array($ANDs))
										{
											// intersect the existing AND postids with the postids for the next clause
											$ANDs = array_intersect($ANDs, $postids);
										}
										else
										{
											// this is the first time we have looped, so make $ANDs into an array
											$ANDs = $postids;
										}
									}

									// if there are no postids left, no matches were made from posts
									if (empty($ANDs))
									{
										// no posts matched the query
										$errors[] = array('searchnoresults', $displayCommon);
									}
									else
									{
										$post_query_logic[100] = 'post.postid IN(' . implode(',', $ANDs) . ')';
									}

									// clean up variables
									unset($requiredposts, $postids, $ANDs);
								}

								// #############################################################################
								// get NOT clauses
								if (!empty($words['NOT']) AND empty($errors))
								{
									// merge the post ids for all NOT words to get a definitive list of posts
									// that MUST NOT be returned by the posts query
									$postids = array();

									foreach (array_keys($words['NOT']) AS $word)
									{
										if (isset($queryWords["$word"]))
										{
											$postids = array_merge($postids, $queryWords["$word"]);
										}
									}

									// remove duplicate post ids to make a smaller query
									if (!empty($postids))
									{
										$postids = array_unique($postids);
										$post_query_logic[200] =  'post.postid NOT IN(' . implode(',', $postids) . ')';
									}

									// clean up variables
									unset($postids);
								}

								if ($vbulletin->GPC['titleonly'] AND !$vbulletin->GPC['starteronly'])
								{
									$fetchusers = '';
									if ($post_query_logic[50])
									{
										$fetchusers = $post_query_logic[50];
										unset($post_query_logic[50]);
									}

									if (!empty($post_query_logic))
									{
										$threadids = array();
										$threads = $db->query_read_slave("
										SELECT threadid
										FROM " . TABLE_PREFIX . "post AS post
										WHERE " . implode(" AND ", $post_query_logic) . "
									");
										while ($thread = $db->fetch_array($threads))
										{
											$threadids[] = $thread['threadid'];
										}

										if (!empty($threadids))
										{
											$postids = array();
											$posts = $db->query_read_slave("
											SELECT postid
											FROM " . TABLE_PREFIX . "post AS post
											WHERE threadid IN (" . implode(',', $threadids) . ")
										");
											while ($post = $db->fetch_array($posts))
											{
												$postids[] = $post['postid'];
											}
											unset($post_query_logic[100]);
											unset($post_query_logic[200]);

											if (!empty($postids))
											{
												$post_query_logic[] = 'post.postid IN(' . implode(',', $postids) . ')';
											}
										}
									}

									if ($fetchusers)
									{
										$post_query_logic[50] = $fetchusers;
									}
								}

								// check that we don't have only NOT words
								if (empty($words['AND']) AND empty($words['OR']) AND !empty($words['NOT']) AND empty($errors))
								{
									// user has ONLY specified a 'NOT' word... this would be bad
									$errors[] = array('searchnoresults', $displayCommon);
								}
							}
						}
						else
						{
							// Fulltext ...
							foreach ($queryWords AS $word => $wordtype)
							{
								// Need something here to strip odd characters out of words that fulltext is probably not indexing

								$queryword = preg_replace('#"([^"]+)"#sie', "stripslashes(str_replace('*', ' ', '\\0'))", $word);

								if ($wordtype != 'NOT')
								{
									$display['highlight'][] = htmlspecialchars_uni(preg_replace('#"(.+)"#si', '\\1', $queryword));
								}

								// make sure word is safe to insert into the query
								$unsafeword = $queryword;
								$queryword = sanitize_word_for_sql($queryword);

								if (!$vbulletin->options['allowwildcards'])
								{
									# Don't allow wildcard searches so remove any *
									$queryword = str_replace('*', '', $queryword);
								}

								$wordlist = iif($wordlist, "$wordlist ", $wordlist);
								switch ($wordtype)
								{
									case 'AND':
										$wordlist .= "+$queryword";
										break;
									case 'OR':
										$wordlist .= $queryword;
										break;
									case 'NOT':
										$wordlist .= "-$queryword";
										break;
								}
							}

							// if we are searching in a thread, the required posts MUST come from the thread we are searching!
							if ($vbulletin->GPC['searchthreadid'])
							{
								$thread_query_logic[] = "thread.threadid = " . $vbulletin->GPC['searchthreadid'];
								$userid_index = " USE INDEX (threadid)";
							}

							if ($vbulletin->GPC['titleonly'])
							{
								$thread_query_logic[] = "MATCH(thread.title) AGAINST ('$wordlist' IN BOOLEAN MODE)";
							}
							else
							{
								$post_query_logic[] = "MATCH(post.title, post.pagetext) AGAINST ('$wordlist' IN BOOLEAN MODE)";
							}
						}
					}
			}
			else if ($vbulletin->options['fulltextsearch'] AND !$vbulletin->GPC['searchtype'])
			{
				// if we are searching in a thread, the required posts MUST come from the thread we are searching!
				if ($vbulletin->GPC['searchthreadid'])
				{
					$thread_query_logic[] = "thread.threadid = " . $vbulletin->GPC['searchthreadid'];
				}

				if ($vbulletin->GPC['query'])
				{
					if ($vbulletin->GPC['titleonly'])
					{
						if ($vbulletin->GPC['sortby'] == 'rank')
						{
							$rank_select_logic = "MATCH(thread.title) AGAINST ('" . $db->escape_string($vbulletin->GPC['query']) . "') AS score";
						}
						$thread_query_logic[] = "MATCH(thread.title) AGAINST ('" . $db->escape_string($vbulletin->GPC['query']) . "')";
					}
					else
					{
						if ($vbulletin->GPC['sortby'] == 'rank')
						{
							$rank_select_logic = "MATCH(post.title, post.pagetext) AGAINST ('" . $db->escape_string($vbulletin->GPC['query']) . "') AS score";
						}
						$post_query_logic[] = "MATCH(post.title, post.pagetext) AGAINST ('" . $db->escape_string($vbulletin->GPC['query']) . "')";
					}

					$nl_query_limit = 'LIMIT ' . $vbulletin->options['maxresults'];

					// Limit forums that are searched since we are going to return a very small result set in most cases.
					foreach ($vbulletin->userinfo['forumpermissions'] AS $forumid => $fperms)
					{
						if (!($fperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($fperms & $vbulletin->bf_ugp_forumpermissions['cansearch']) OR !verify_forum_password($forumid, $vbulletin->forumcache["$forumid"]['password'], false) OR !($vbulletin->forumcache["$forumid"]['options'] & $vbulletin->bf_misc_forumoptions['indexposts']))
						{
							$excludelist .= ",$forumid";
						}
						else if ((!$vbulletin->GPC['titleonly'] OR $vbulletin->GPC['showposts']) AND !($fperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
						{	// exclude forums that have canview but no canviewthreads if this is a post search
							$excludelist .= ",$forumid";
						}
					}

					if ($excludelist != '')
					{
						$thread_query_logic[] = "thread.forumid NOT IN (0$excludelist)";
					}

					$words = array();
					$display['words'] = array($vbulletin->GPC['query']);
					$display['common'] = array();
					$display['highlight'][] = htmlspecialchars_uni(preg_replace('#"(.+)"#si', '\\1', $vbulletin->GPC['query']));

				}
				else
				{
					// this means we are searching just on username/tag...
				}
			}
			else if ($vbulletin->GPC['searchthreadid'])
			{
				if ($vbulletin->options['fulltextsearch'])
				{
					$thread_query_logic[] = "thread.threadid = " . $vbulletin->GPC['searchthreadid'];
					$userid_index = " USE INDEX (threadid)";
				}
				else
				{
					$requiredposts = array();
					$q = "
						SELECT postid FROM " . TABLE_PREFIX . "post
						WHERE threadid = " . $vbulletin->GPC['searchthreadid'] . "
					";
					$posts = $db->query_read_slave($q);
					if ($db->num_rows($posts) == 0)
					{
						$errors[] = array('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink']);
					}
					else
					{
						while ($post = $db->fetch_array($posts))
						{
							$requiredposts[] = $post['postid'];
						}
						unset($post);
					}
					$db->free_result($posts);

					if (!empty($requiredposts))
					{
						$post_query_logic[] = "post.postid IN(" . implode(',', $requiredposts) . ")";
					}
				}
			}

			if (empty($errors))
			{
				// #############################################################################
				// ######################### END WORD QUERY LOGIC ##############################
				// #############################################################################

				// #############################################################################
				// check if we are searching for posts from a specific time period
				if ($vbulletin->GPC['searchdate'] != 'lastvisit')
				{
					$vbulletin->GPC['searchdate'] = intval($vbulletin->GPC['searchdate']);
				}
				if ($vbulletin->GPC['searchdate'])
				{
					switch($vbulletin->GPC['searchdate'])
					{
						case 'lastvisit':
							// get posts from before/after last visit
							$datecut = $vbulletin->userinfo['lastvisit'];
							break;

						case 0:
							// do not specify a time period
							$datecut = 0;
							break;

						default:
							// get posts from before/after specified time period
							$datecut = TIMENOW - $vbulletin->GPC['searchdate'] * 86400;
					}
					if ($datecut)
					{
						switch($vbulletin->GPC['beforeafter'])
						{
							// get posts from before $datecut
							case 'before':
								$post_query_logic[] = "post.dateline < $datecut";
								break;

								// get posts from after $datecut
							default:
								$post_query_logic[] = "post.dateline > $datecut";
						}
					}
					unset($datecut);
				}

				// #############################################################################
				// check to see if there are conditions attached to number of thread replies
				if ($vbulletin->GPC['replyless'] OR $vbulletin->GPC['replylimit'] > 0)
				{
					if ($vbulletin->GPC['replyless'] == 1)
					{
						// get threads with at *most* $replylimit replies
						$thread_query_logic[] = "thread.replycount <= " . $vbulletin->GPC['replylimit'];
					}
					else
					{
						// get threads with at *least* $replylimit replies
						$thread_query_logic[] = "thread.replycount >= " . $vbulletin->GPC['replylimit'];
					}
				}

				// #############################################################################
				// check to see if we should be searching in a particular forum or forums
				if ($forumchoice)
				{
					$thread_query_logic[] = "thread.forumid IN($forumchoice)";
				}

				if ($vbulletin->GPC['exclude'])
				{
					$excludelist = explode(',', $vbulletin->GPC['exclude']);
					$excludearray = array();
					foreach ($excludelist AS $key => $excludeid)
					{
						if ($excludeforum = intval($excludeid))
						{
							$excludearray[] = $excludeforum;
						}
					}
					if (!empty($excludearray))
					{
						$thread_query_logic[] = "thread.forumid NOT IN (" . implode(',', $excludearray) . ")";
					}
				}

				// match prefixes
				if ($prefixchoice)
				{
					$prefix_sql = array();
					foreach (explode(',', $prefixchoice) AS $prefixid)
					{
						if ($prefixid == '-1')
						{
							// no prefix
							$prefix_sql[] = "''";
						}
						else
						{
							$prefix_sql[] = "'" . $db->escape_string($prefixid) . "'";
						}
					}
					$thread_query_logic[] = "thread.prefixid IN (" . implode(',', $prefix_sql) . ")";
				}

				// #############################################################################
				// show results as threads
				// #############################################################################
				$querylogic = array_merge($post_query_logic, $thread_query_logic);

				if (!$vbulletin->GPC['showposts'])
				{
					// create new threadscores array to store scores for threads
					$threadscores = array();

					// #############################################################################
					$threadids = array();
					$thread_select_logic = array();

					// Natural Language
					if ($vbulletin->options['fulltextsearch'] AND !$vbulletin->GPC['searchtype'])
					{
						$thread_select_logic[] = "DISTINCT thread.threadid";
						if ($rank_select_logic)
						{
							$thread_select_logic[] = $rank_select_logic;
						}
					}
					else
					{
						$thread_select_logic[] = "thread.threadid";
						if ($vbulletin->GPC['sortby'] == 'rank')
						{
							if (!empty($post_query_logic))
							{
								$thread_select_logic[] = "post.postid";
							}
							$thread_select_logic[] = "IF(views <= replycount, replycount + 1, views) as views, replycount, votenum, votetotal, thread.lastpost";
						}
					}

					$Coventry = array();

					if (!empty($post_query_logic))
					{
						// don't retrieve tachy'd posts/threads
						require_once(DIR . '/includes/functions_bigthree.php');
						if ($Coventry = fetch_coventry())
						{
							$thread_select_logic[] = "thread.forumid, post.userid";
						}
					}

					require_once(DIR . '/includes/functions_forumlist.php');
					cache_moderators();

					if ((!empty($post_query_logic) OR !empty($post_join_query_logic)))
					{
						$hidden = array();
						$deleted = array();
						$allhidden = true;
						$alldeleted = true;
						foreach($vbulletin->forumcache AS $forumid => $forum)
						{
							if (can_moderate($forumid))
							{
								$deleted["$forumid"] = $forumid;
							}
							else
							{
								$alldeleted = false;
							}
							if (can_moderate($forumid, 'canmoderateposts'))
							{
								$hidden["$forumid"] = $forumid;
							}
							else
							{
								$allhidden = false;
							}
						}
						$modlogic = array();
						if (!empty($hidden) OR !empty($deleted))
						{
							if (!$allhidden AND !$alldeleted)
							{
								if ($allhidden)
								{
									$modlogic[] = "post.visible IN (0,1)";
								}
								else if ($alldeleted)
								{
									$modlogic[] = "post.visible IN (1,2)";
								}
								else
								{
									$modlogic[] = "post.visible = 1";
								}

								if (!$allhidden AND !empty($hidden))
								{
									$modlogic[] = "(post.visible = 0 AND forumid IN (" . implode(',', $hidden) . "))";
								}

								if (!$alldeleted AND !empty($deleted))
								{
									$modlogic[] = "(post.visible = 2 AND forumid IN (" . implode(',', $deleted) . "))";
								}

								$querylogic[] = "(" . implode(" OR ", $modlogic) . ")";
							}
						}
						else
						{
							$querylogic[] = "post.visible = 1";
						}
					}

					$threads = $db->query_read_slave("
						SELECT
						" . implode(', ', $thread_select_logic) . "
						FROM " . TABLE_PREFIX . "thread AS thread $userid_index
						$tag_join
						" . ((!empty($post_query_logic) OR !empty($post_join_query_logic)) ? "INNER JOIN " . TABLE_PREFIX . "post AS post ON(thread.threadid = post.threadid $post_join_query_logic)" : "") . "
						" . (!empty($querylogic) ? "WHERE " . implode(" AND ", $querylogic) : "") . "
						$nl_query_limit
					");

						$itemscores = array();
						$datescores = array();
						$mindate = TIMENOW;
						$maxdate = 0;
						while ($thread = $db->fetch_array($threads))
						{
							if (!can_moderate($thread['forumid']) AND in_array($thread['userid'], $Coventry))
							{
								continue;
							}

							if ($vbulletin->GPC['sortby'] == 'rank')
							{
								$threadscores["$thread[threadid]"] += ($rank_select_logic) ? $thread['score'] : $postscores["$thread[postid]"];
								if ($mindate > $thread['lastpost'])
								{
									$mindate = $thread['lastpost'];
								}
								if ($maxdate < $thread['lastpost'])
								{
									$maxdate = $thread['lastpost'];
								}
								$datescores["$thread[threadid]"] = $thread['lastpost'];
								$itemids["$thread[threadid]"] = $thread;
							}
							else
							{
								$itemids["$thread[threadid]"] = true;
							}
						}
						unset($threadscores);
						unset($thread);
						$db->free_result($threads);

						if (!empty($itemids))
						{
							if ($vbulletin->GPC['sortby'] == 'rank')
							{
								foreach ($itemids AS $threadid => $thread)
								{
									$itemscores["$threadid"] = fetch_search_item_score($thread, $threadscores["$thread[threadid]"]);
								}
							}

							unset($postscores);
						}

						// #############################################################################
						// end show results as threads
						// #############################################################################
				}
				else
				{
					// #############################################################################
					// show results as posts
					// #############################################################################

					// #############################################################################
					// get post ids from post table
					$post_select_logic = array();

					if ($vbulletin->options['fulltextsearch'] AND $vbulletin->GPC['titleonly'])
					{
						#$querylogic[] = $thread_query_logic[] = "post.postid = thread.firstpostid";
					}

					$do_thread_join = (!empty($thread_query_logic) OR !empty($tag_join) OR ($vbulletin->GPC['sortby'] == 'rank' AND !$rank_select_logic));

					$posts = $db->query_read_slave("
						SELECT postid, post.dateline
						" . iif($vbulletin->GPC['sortby'] == 'rank' AND !$rank_select_logic, ', IF(thread.views=0, thread.replycount+1, thread.views) as views, thread.replycount, thread.votenum, thread.votetotal') . "
						" . (!empty($rank_select_logic) ? ", $rank_select_logic" : "") . "
						FROM " . TABLE_PREFIX . "post AS post $userid_index
						" . ($do_thread_join ? "INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)" : '') . "
						$tag_join
						" . (!empty($querylogic) ? "WHERE " . implode(" AND ", $querylogic) : "") . "
						$nl_query_limit
					");

						if ($vbulletin->GPC['sortby'] == 'rank')
						{
							$itemscores = array();
							$datescores = array();
							$mindate = TIMENOW;
							$maxdate = 0;

							while ($post = $db->fetch_array($posts))
							{
								if ($rank_select_logic)
								{
									$postscores["$post[postid]"] = $post['score'];
								}
								else
								{
									if ($mindate > $post['dateline'])
									{
										$mindate = $post['dateline'];
									}
									if ($maxdate < $post['dateline'])
									{
										$maxdate = $post['dateline'];
									}
									$datescores["{$post['postid']}"] = $post['dateline'];
								}

								$itemscores["{$post['postid']}"] = fetch_search_item_score($post, $postscores["{$post['postid']}"]);
							}
							unset($postscores);
						}
						else
						{
							$itemids = array();
							while ($post = $db->fetch_array($posts))
							{
								$itemids["{$post['postid']}"] = true;
							}
						}
						unset($post);
						$db->free_result($posts);

				}
				// #############################################################################
				// end show results as posts
				// #############################################################################


				// #############################################################################
				// now sort the results into order
				// #############################################################################

				// sort by relevance
				if ($vbulletin->GPC['sortby'] == 'rank')
				{
					if (empty($itemscores))
					{
						$errors[] = array('searchnoresults', $displayCommon);
					}
					else
					{
						// add in date scores
						fetch_search_date_scores($datescores, $itemscores, $mindate, $maxdate);

						// sort the score results
						$sortfunc = iif($vbulletin->GPC['sortorder'] == 'asc', 'asort', 'arsort');
						$sortfunc($itemscores);

						// create the final result set
						$orderedids = array_keys($itemscores);
					}
				}
				// sort by database field
				else
				{
					if (empty($itemids))
					{
						$errors[] = array('searchnoresults', $displayCommon);
					}
					else
					{
						// remove dupes and make query condition
						$itemids = iif($vbulletin->GPC['showposts'], 'postid', 'threadid') . ' IN(' . implode(',', array_keys($itemids)) . ')';

						// sort the results and create the final result set
						$orderedids = sort_search_items($itemids, $vbulletin->GPC['showposts'], $vbulletin->GPC['sortby'], $vbulletin->GPC['sortorder']);
					}
				}

				// #############################################################################
				// end sort the results into order
				// #############################################################################

				if (empty($errors))
				{
					// get rid of unwanted gubbins
					unset($itemids, $threadids, $postids, $postscores, $threadscores, $itemscores, $datescores);

					// final check to see if we've actually got some results
					if (empty($orderedids))
					{
						if (defined('NOSHUTDOWNFUNC'))
						{
							exec_shut_down();
						}
						return new xmlrpcresp(
						new xmlrpcval(
						array(
	                                        'total_topic_num' => new xmlrpcval(0,'int'),
	                                        'topics' => new xmlrpcval(array(),'array'),
						),
	                                'struct'
	                                )
	                                );
					}
					else
					{
						// #############################################################################
						// finish search timer
						$searchtime = number_format(fetch_microtime_difference($searchstart), 5, '.', '');

						// #############################################################################
						// go through search words to build the display words for the results page summary bar

							

						if ($vbulletin->options['fulltextsearch'])
						{
							$display['words'] = preg_replace('#"([^"]+)"#sie', "stripslashes(str_replace('*', ' ', '\\0'))", $display['words']);
						}

						// make sure we have no duplicate entries in our $display array
							
							
						// insert search results into search cache
						/*insert query*/
						$db->query_write("
							REPLACE INTO " . TABLE_PREFIX . "search
								(userid, titleonly, ipaddress, personal, query, searchuser, forumchoice, prefixchoice, sortby, sortorder, searchtime, showposts, orderedids, dateline, searchterms, displayterms, searchhash, completed)
							VALUES
								(" . $vbulletin->userinfo['userid'] . ",
								" . intval($vbulletin->GPC['titleonly']) . ",
								'" . $db->escape_string(IPADDRESS) . "',
								" . ($vbulletin->options['searchsharing'] ? 0 : 1) . ",
								'" . $db->escape_string($vbulletin->GPC['query']) . "',
								'" . $db->escape_string($vbulletin->GPC['searchuser']) . "',
								'" . $db->escape_string($forumchoice) . "',
								'" . $db->escape_string($prefixchoice) . "',
								'" . $db->escape_string($vbulletin->GPC['sortby']) . "',
								'" . $db->escape_string($vbulletin->GPC['sortorder']) . "',
								$searchtime, " . intval($vbulletin->GPC['showposts']) . ",
								'" . implode(',', $orderedids) . "',
								" . time() . ",
								'" . $db->escape_string(serialize($searchterms)) . "',
								'" . $db->escape_string(serialize($display)) . "',
								'" . $db->escape_string($searchhash) . "',
								1)
						");

								$searchid = $db->insert_id();
								unset($itemids, $threadids, $postids, $postscores, $threadscores, $itemscores, $datescores,$orderedids);

								return get_search_result($searchid,$start_num,$end_num,true,$ispost);
					}
				} else {


					$errorlist = '';
					foreach(array_map('fetch_error', $errors) AS $error)
					{
						$errorlist .= "$error";
					}
					if (defined('NOSHUTDOWNFUNC'))
					{
						exec_shut_down();
					}
					return new xmlrpcval(
					array(
				                                'total_topic_num' => new xmlrpcval(0,'int'),
				                   				'search_id' => new xmlrpcval("","string"),
				                                'topics' => new xmlrpcval(array(),'array'),
					),
				                        'struct'
				                        );

				}
			}
		}
		return new xmlrpcval(
		array(
				                                'total_topic_num' => new xmlrpcval(0,'int'),
				                   				'search_id' => new xmlrpcval("","string"),
				                                'topics' => new xmlrpcval(array(),'array'),
		),
				                        'struct'
				                        );
	}
}
?>