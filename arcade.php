<?php
/*======================================================================*\
|| #################################################################### ||
|| # The Arcade	for vBulletin 3.5									  # ||
|| # Development header												  # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'arcade');

// ########################## DO PREPARATION #############################
if ($_REQUEST['sessdo'])
{
	$_REQUEST['do'] = $_REQUEST['sessdo'];
}

// Ajax functionality, so bypass the location update and PM popup.
switch ($_REQUEST['do'])
{
	case 'ajaxsearch':
	case 'userinfosearch':
	case 'dorating':
	define('LOCATION_BYPASS', 1);
	define('NOPMPOPUP', 1);
	break;
}

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('arcade');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
'arcade_headinclude',
'arcade_navbar'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
'none' => array(
'arcade_main',
'arcade_main_alt',
'arcade_main_user',
'arcade_news_bit',
'arcade_game_bit',
'arcade_game_bit_slim',
'arcade_category_bit',
'arcade_category_bit_active',
'arcade_ajaxsearch',
'arcade_welcomearea',
'arcade_challenge_minibit',
'arcade_challenge_bit'
),
'play' => array(
'arcade_play',
'arcade_play_challenge',
'arcade_v3game'
),
'burn' => array(
'arcade_scores',
'arcade_scorebit',
'arcade_scorebit_high',
'arcade_ajaxsearch',
'arcade_recommendation',
'arcade_commentform'
),
'userinfosearch' => array(
'arcade_miniuserinfo'
),
'scores' => array(
'arcade_ajaxsearch',
'arcade_scores',
'arcade_scorebit',
'arcade_scorebit_high'
),
'newchallenge' => array(
'arcade_newchallenge'
)
);


// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_arcade.php');
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

($hook = vBulletinHook::fetch_hook('arcade_global_start')) ? eval($hook) : false;

// Check basic permissions.
if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canviewarcade']))
{
	print_no_permission();
}

// If the Arcade is closed and you're not an admin, print the error.
if ($vbulletin->options['arcadeopen']==0 && $vbulletin->userinfo['usergroupid']!=6)
{
	standard_error($vbulletin->options['arcadeclosedmessage']);
}

// Add in Arcade user options.
$vbulletin->userinfo = array_merge(convert_bits_to_array($vbulletin->userinfo['arcadeoptions'], $vbulletin->bf_misc_arcadeoptions), $vbulletin->userinfo);

eval('$arcade_headinclude = "' . fetch_template('arcade_headinclude') . '";');

// Alternate navbar template.
if ($vbulletin->options['usealtnav'])
{
	$navbartemplate = 'arcade_navbar';
} else {
	$navbartemplate = 'navbar';
}

// Alternate opacity for popup menus.
$vbulletin->options['popupopacityalt'] = $vbulletin->options['popupopacity']/100;

// Just in case.
$bitfieldcheck = '';

$footer = construct_phrase($vbphrase['arcade_end'], $vbulletin->options['arcadeimages']) . $footer;

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// AJAX SEARCH
// Instant searching.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do']=='ajaxsearch')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'searchstring' => TYPE_STR
	));

	$searchstring = convert_urlencoded_unicode($vbulletin->GPC['searchstring']);

	// Search for games based on string.
	$games = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_games AS arcade_games WHERE title LIKE '%" . addslashes($searchstring) . "%' ORDER BY sessioncount DESC LIMIT " . $vbulletin->options['quicksearchresults']);

	// We'll use this array for an implosion later on.
	$temparray = array();

	while ($game = $db->fetch_array($games))
	{
		$temparray[] = "$game[gameid]|||$game[title]|||$game[miniimage]";
	}

	// And send it to the browser.
	echo implode('^^^', $temparray);
	exit;

}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DO RATING
// Ajax game ratings.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do']=='dorating')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'gameid' => TYPE_UINT,
	'rating' => TYPE_UINT
	));
	
	// We don't want guests to be able to rate games.
	if ($vbulletin->userinfo['userid']==0)
	{
		exit;
	}

	// Make sure the rating value is valid. (Between 1 and 5)
	if ($vbulletin->GPC['rating']>5 || $vbulletin->GPC['rating']<1)
	{
		exit;
	}

	if ($oldrating = $db->query_first("SELECT ratingid FROM " . TABLE_PREFIX . "arcade_ratings AS arcade_ratings WHERE userid=" . $vbulletin->userinfo['userid'] . " AND gameid=" . $vbulletin->GPC['gameid'] . ""))
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_ratings SET rating=" . $vbulletin->GPC['rating'] . " WHERE ratingid=" . $oldrating['ratingid']);
	} else {
		$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_ratings (userid, gameid, rating) VALUES (" . $vbulletin->userinfo['userid'] . ", " . $vbulletin->GPC['gameid'] . ", " . $vbulletin->GPC['rating'] . ")");
	}

	build_ratings($vbulletin->GPC['gameid']);

	// Provide something for the javascript to bite on.
	echo '1';
	exit;
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// PROCESS FAVORITES
// Flip the favorite for the current user's choice.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do']=='processfav')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'gameid' => TYPE_UINT
	));
	
	if ($vbulletin->userinfo['userid']==0 || $vbulletin->GPC['gameid']==0)
	{
		// Unregistered.
		echo 3;
		exit;
	}
	
	$favcache = unserialize($vbulletin->userinfo['favcache']);
	
	if ($favcache[$vbulletin->GPC['gameid']])
	{
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_favorites WHERE userid=" . $vbulletin->userinfo['userid'] . " AND gameid=" . $vbulletin->GPC['gameid']);
		
		// Favorite deleted.
		echo 2;
	} else {
		$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_favorites (userid, gameid) VALUES (" . $vbulletin->userinfo['userid'] . ", " . $vbulletin->GPC['gameid'] . ")");
		
		// Favorite added.
		echo 1;
	}
	build_favcache();
	
	exit;
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// USERINFO SEARCH
// Send back a neat little userinfo table.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do']=='userinfosearch')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'userid' => TYPE_UINT
	));

	if (!$vbulletin->GPC['userid'])
	{
		exit;
	}

	// Champion listings.
	$games = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_games AS arcade_games WHERE highscorerid=" . $vbulletin->GPC['userid'] . " ORDER BY title DESC");


	// Avatar processing.
	$user = fetch_userinfo($vbulletin->GPC['userid']);
	$user['avatar'] = fetch_avatar_url($user['userid']);
	$user['useravatar'] = $user['avatar'][0];
	$user['avdimensions'] = $user['avatar'][1];

	if (!$user['useravatar'])
	{
		$user['useravatar'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
		$user['avatar'] = true;
	}

	// Some data on timing and session counts.
	$timecheck = $db->query_first("SELECT SUM(start) AS startcount, SUM(finish) AS finishcount, COUNT(*) AS playcount, AVG(ping) AS avgping FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions WHERE start>0 AND finish>0 AND valid=1 AND userid=" . $vbulletin->GPC['userid']);

	$user['timeplayed'] = sec2hms($timecheck['finishcount']-$timecheck['startcount']);
	$user = array_merge($user, $timecheck);
	$user['avgping'] = round($user['avgping']);

	// Most played game check.
	$game = $db->query_first("SELECT COUNT(*) AS playcount, arcade_games.title FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON (arcade_games.gameid=arcade_sessions.gameid)
	WHERE start>0 AND finish>0 AND valid=1 AND userid=" . $vbulletin->GPC['userid'] . " GROUP BY arcade_sessions.gameid ORDER BY playcount DESC LIMIT 1");

	$game['pcplayed'] = round(($game['playcount']/$user['playcount'])*100);

	eval('print_output("' . fetch_template('arcade_miniuserinfo') . '");');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// BAR SWITCH
// Makes that front page more (or less) crowded.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if (isset($_GET['barsize']))
{
	$vbulletin->input->clean_array_gpc('r', array(
	'barsize' => TYPE_UINT,
	'categoryid' => TYPE_INT
	));

	$barsize = $vbulletin->GPC['barsize'];
	
	// Allows choosing the mini mode by default.
	if ($vbulletin->options['minibydefault'])
	{
		$barsize_c = iif($barsize==1, 0, 1);
	} else {
		$barsize_c = $vbulletin->GPC['barsize'];
	}
	
	// Set the cookie so the new value is remembered.
	vbsetcookie('barsize', $barsize_c);
	
} else {
	$vbulletin->input->clean_gpc('c', COOKIE_PREFIX . 'barsize', INT);

	// Simple variable names for cookies.
	$barsize = $vbulletin->GPC[COOKIE_PREFIX . 'barsize'];
	
	// Allows choosing the mini mode by default.
	if ($vbulletin->options['minibydefault'])
	{
		$barsize = iif($barsize==1, 0, 1);
	}

}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// ARCADE_MAIN
// There's no place like home.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if (!$_REQUEST['do'])
{
	($hook = vBulletinHook::fetch_hook('arcade_main_start')) ? eval($hook) : false;

	// Let's get the navbar out of the way.
	$navbits['arcade.php'] = $vbphrase['arcade'];
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template($navbartemplate) . '";');
	
	$favcache = unserialize($vbulletin->userinfo['favcache']);

	// AJAX game searching include code.
	eval('$ajaxinclude = "' . fetch_template('arcade_ajaxsearch') . '";');

	// Just to make things a little easier when userinfo starts flying everywhere. (Sorry Kier!)
	$player = $vbulletin->userinfo;

	// Format the player's join date.
	$player['joindate'] = vbdate('F j, Y', $player['joindate']);

	// Get the player's avatar URL.
	$avatarurl = fetch_avatar_url($player['userid']);
	if ($avatarurl == '')
	{
		$show['avatar'] = true;
		$player['avatarurl'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
	}
	else
	{
		$show['avatar'] = true;
		$player['avatarsize'] = $avatarurl[1];
		$player['avatarurl'] = $avatarurl[0];
	}
	eval('$playerbox = "' . fetch_template('arcade_main_user') . '";');
	
	// Get challenges for display.
	$challenges = $db->query_read("SELECT arcade_challenges.*, touser.username AS tousername, fromuser.username AS fromusername, arcade_games.miniimage, arcade_games.title FROM " . TABLE_PREFIX . "arcade_challenges AS arcade_challenges
	LEFT JOIN " . TABLE_PREFIX . "user AS touser ON (touser.userid=arcade_challenges.touserid)
	LEFT JOIN " . TABLE_PREFIX . "user AS fromuser ON (fromuser.userid=arcade_challenges.fromuserid)
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON (arcade_games.gameid=arcade_challenges.gameid)
	WHERE status=3 ORDER BY datestamp DESC LIMIT " . $vbulletin->options['frontminichallenges']);
	while ($challenge = $db->fetch_array($challenges))
	{
		exec_switch_bg();
		$challenge['toscore'] = sprintf((float)$challenge['toscore']);
		$challenge['fromscore'] = sprintf((float)$challenge['fromscore']);
		eval('$mcbits .= "' . fetch_template('arcade_challenge_minibit') . '";');
	}
	
	// Set the viewing mode status icons.
	$barstatus[(int)$barsize] = '_on';

	// We'll want to parse that BBCode in those news items.
	require_once('./includes/class_bbcode.php');
	$parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

	// Fetch the latest news.
	$newsbits = '';
	$newsquery = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_news AS arcade_news ORDER BY datestamp DESC LIMIT " . $vbulletin->options['quicksearchresults']);
	while ($news = $db->fetch_array($newsquery))
	{
		exec_switch_bg();

		// Format the news date/time.
		$news['date'] = vbdate($vbulletin->options['logdateformat'], $news['datestamp']);

		$news['newstext'] = unhtmlspecialchars($parser->do_parse($news['newstext'], false, true, true, false, true, false));

		eval('$newsbits .= "' . fetch_template('arcade_news_bit') . '";');
	}

	// Now, let's get the games.
	$vbulletin->input->clean_array_gpc('r', array(
	'categoryid' => TYPE_INT
	));
	$categoryid = $vbulletin->GPC['categoryid'];

	if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canoverridepermissions']))
	{
		// Game bitfield check. (Is Active.)
		$bitfieldcheck = '(arcade_games.gamepermissions & 1)';
	}

	// Let's take care of what we're viewing now. (We use this for remembering the last page and category we were viewing.)
	$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
	set_bbarray_cookie('arcade_viewdata', 'categoryid', $vbulletin->GPC['categoryid']);
	set_bbarray_cookie('arcade_viewdata', 'pagenumber', iif($pagenumber, $pagenumber, 1));

	$gamebits = '';
	if (!$vbulletin->GPC['categoryid'])
	{
		// No category id, so show a random selection of games.
		$games = $db->query_read("SELECT arcade_games.*, user.username, arcade_categories.catname FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (arcade_games.highscorerid=user.userid)
		LEFT JOIN " . TABLE_PREFIX . "arcade_categories AS arcade_categories ON (arcade_games.categoryid=arcade_categories.categoryid)
		" . iif($bitfieldcheck, "WHERE $bitfieldcheck") . "
		ORDER BY RAND() LIMIT ".$vbulletin->options['gamesperpage']);
	}
	else if ($vbulletin->GPC['categoryid']==2)
	{
		// Favorites
		$gamecount = count($favcache);
		
		if ($gamecount>0)
		{
			$perpage =  $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
			
			// set defaults
			sanitize_pageresults($gamecount, $pagenumber, $perpage, 100, $vbulletin->options['gamesperpage']);
	
			$start = (int)($perpage*$pagenumber)-$perpage;
	
			$pagenav = construct_page_nav($pagenumber, $perpage, $gamecount, 'arcade.php?' . $vbulletin->session->vars['sessionurl'], ""
			. (!empty($vbulletin->GPC['categoryid']) ? "&amp;categoryid=" . $vbulletin->GPC['categoryid'] : "")
			. (!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : "")
			);
			
			$gids = implode(',', (array)$favcache);
			if ($gids)
			{	
				$games = $db->query_read("SELECT arcade_games.*, user.username, arcade_categories.catname FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
				LEFT JOIN " . TABLE_PREFIX . "user AS user ON (arcade_games.highscorerid=user.userid)
				LEFT JOIN " . TABLE_PREFIX . "arcade_categories AS arcade_categories ON (arcade_games.categoryid=arcade_categories.categoryid)
				WHERE arcade_games.gameid IN ($gids)
				" . iif($bitfieldcheck, "AND $bitfieldcheck") . "
				LIMIT $start,".$vbulletin->options['gamesperpage']);
			}
		}
		
	} else	{
		// Get games only from the specified category id.

		// First of all, get a total game count.
		$gamecountquery = $db->query_read("SELECT arcade_games.gameid FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
		" . iif($vbulletin->GPC['categoryid']!=-1, "WHERE arcade_games.categoryid=" . $vbulletin->GPC['categoryid'] . iif($bitfieldcheck, " AND $bitfieldcheck"), iif($bitfieldcheck, "WHERE $bitfieldcheck")));
		$gamecount = $db->num_rows($gamecountquery);

		$perpage =  $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
		$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

		// set defaults
		sanitize_pageresults($gamecount, $pagenumber, $perpage, 100, $vbulletin->options['gamesperpage']);

		$start = (int)($perpage*$pagenumber)-$perpage;

		$pagenav = construct_page_nav($pagenumber, $perpage, $gamecount, 'arcade.php?' . $vbulletin->session->vars['sessionurl'], ""
		. (!empty($vbulletin->GPC['categoryid']) ? "&amp;categoryid=" . $vbulletin->GPC['categoryid'] : "")
		. (!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : "")
		);

		$games = $db->query_read("SELECT arcade_games.*, user.username, arcade_categories.catname FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (arcade_games.highscorerid=user.userid)
		LEFT JOIN " . TABLE_PREFIX . "arcade_categories AS arcade_categories ON (arcade_games.categoryid=arcade_categories.categoryid)
		" . iif($vbulletin->GPC['categoryid']!=-1, "WHERE arcade_games.categoryid=" . $vbulletin->GPC['categoryid'] . iif($bitfieldcheck, " AND $bitfieldcheck"), iif($bitfieldcheck, "WHERE $bitfieldcheck")) . "
		ORDER BY title ASC
		LIMIT $start, " . $vbulletin->options['gamesperpage']);
	}

	if ($games)
	{
		// Game cache contains all the data we need to populate the game row - image links, titles, descriptions, etc.
		$gamecache = array();

		// $gameids contains all of the game ids we're going to display. Yes, we could get data from $gamecache, but I personally prefer this way.
		$gameids = array();

		while ($game = $db->fetch_array($games))
		{
			/* Now, because we need to use this data to let us know which games to get score data for,
			we're caching it here rather than spitting out the game rows. */
			$gamecache[$game['gameid']] = $game;
			$gameids[] = $game['gameid'];
		}

		// And now we get personal best data, which is only worth doing if the user is registered.
		if ($gameids)
		{
			$gameids = implode(',', $gameids);
			
			// Only bother with high scores if the user is actually logged in.
			if ($vbulletin->userinfo['userid'])
			{
				$scoredata = $db->query_read("SELECT MAX(score) AS personalbest, MIN(score) AS personalbestr, gameid FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
				WHERE gameid IN ($gameids) AND userid=" . $vbulletin->userinfo['userid'] . " GROUP BY gameid");
				while ($score = $db->fetch_array($scoredata))
				{
					// Add this data to the $gamecache array
					$gamecache[$score['gameid']]['personalbest'] = sprintf((float)iif($gamecache[$score['gameid']]['isreverse'], $score['personalbestr'], $score['personalbest']));
				}
			}

			// Now we can use $gamecache to populate our game section.
			foreach ($gamecache as $key => $game)
			{
				exec_switch_bg();
				
				if ($game['votecount'])
				{
					$game['rating_acc'] = round(($game['votepoints']/$game['votecount']), 2);
					$game['rating'] = ceil($game['rating_acc']);
					$show['rating'] = true;
				} else {
					$show['rating'] = false;
				}
				
				if ($favcache[$game['gameid']])
				{
					$show['fav'] = true;
				} else {
					$show['fav'] = false;
				}
				
				($hook = vBulletinHook::fetch_hook('arcade_game_bit')) ? eval($hook) : false;
				
				$game['highscore'] = sprintf((float)$game['highscore']);
				eval('$gamebits .= "' . fetch_template('arcade_game_bit' . iif($barsize==1, '_slim')) . '";');
			}
		}

	}

	// Construct categories.
	$gamecats = array();
	$gamecats[-1] = $vbphrase['allcategories'];
	$gamecats[0] = $vbphrase['randomselection'];
	$categories = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_categories AS arcade_categories WHERE isactive=1 ORDER BY displayorder ASC");
	while ($category = $db->fetch_array($categories))
	{
		$gamecats[$category['categoryid']] = $category['catname'];
	}

	foreach ($gamecats as $id => $category)
	{
		exec_switch_bg();

		eval('$gamecategories .= "' . fetch_template('arcade_category_bit' . iif($vbulletin->GPC['categoryid']==$id, '_active')) . '";');

		// Snatch the catname if we're viewing a particular category.
		if ($vbulletin->GPC['categoryid'] AND ($id==$vbulletin->GPC['categoryid']))
		{
			$categoryname = $category;
		}

	}

	($hook = vBulletinHook::fetch_hook('arcade_main_complete')) ? eval($hook) : false;

	eval('$welcomearea = "' . fetch_template('arcade_welcomearea') . '";');
	
	// Challenges.
	$challenges = $db->query_read("SELECT arcade_challenges.*, touser.username AS tousername, fromuser.username AS fromusername, arcade_games.title FROM " . TABLE_PREFIX . "arcade_challenges AS arcade_challenges
	LEFT JOIN " . TABLE_PREFIX . "user AS touser ON (touser.userid=arcade_challenges.touserid)
	LEFT JOIN " . TABLE_PREFIX . "user AS fromuser ON (fromuser.userid=arcade_challenges.fromuserid)
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON (arcade_games.gameid=arcade_challenges.gameid)
	WHERE (status=0 AND touserid=" . $vbulletin->userinfo['userid'] . ") OR (status=1 AND touserid=" . $vbulletin->userinfo['userid'] . " AND tosessionid=0) OR (status=1 AND fromuserid=" . $vbulletin->userinfo['userid'] . " AND fromsessionid=0)");
	while ($challenge = $db->fetch_array($challenges))
	{
		// 1 = Challenger
		// 0 = Challenged
		$challenge['stance'] = iif($vbulletin->userinfo['userid']==$challenge['touserid'], 0, 1);
		
		$challenge['username'] = iif($challenge['stance']==0, $challenge['fromusername'], $challenge['tousername']);
		$challenge['userid'] = iif($challenge['stance']==0, $challenge['fromuserid'], $challenge['touserid']);
		eval('$challengebits .= "' . fetch_template('arcade_challenge_bit') . '";');
	}
	
	if ($vbulletin->options['fliparcade']==0)
	{
		$templatename = 'arcade_main';
	} else {
		$templatename = 'arcade_main_alt';
	}
	
	eval('print_output("' . fetch_template($templatename) . '");');
}


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// PLAY
// Playing a specific game.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do']=='play')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'gameid' => TYPE_UINT,
	'challengeid' => TYPE_UINT
	));
	
	if ($vbulletin->GPC['challengeid'])
	{
		// This is a challenge, so we'll use the game ID from there instead.
		if ($challenge = $db->query_first("SELECT arcade_challenges.* FROM " . TABLE_PREFIX . "arcade_challenges AS arcade_challenges WHERE challengeid=" . $vbulletin->GPC['challengeid']))
		{
			// 1 = Challenger
			// 0 = Challenged
			$challenge['stance'] = iif($vbulletin->userinfo['userid']==$challenge['touserid'], 0, 1);
			
			// A little security.
			if ($challenge['stance']==1 AND $vbulletin->userinfo['userid']!=$challenge['fromuserid'])
			{
				// This user has nothing to do with this challenge.
				print_no_permission();
			}
			
			if ($challenge['status']==0)
			{
				if ($challenge['stance']==1)
				{
					// The challenger has submitted a new challenge.
					$vbulletin->url = 'arcade.php';
					standard_redirect($vbphrase['challenge_submitted'], true);
				} else {
					// The challenged user has ACCEPTED, and is now playing.
					$challenge['status']=1;
					
					// Update the challenge record.
					$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_challenges SET status=1 WHERE challengeid=" . $challenge['challengeid']);
					
					$otheruser = fetch_userinfo($challenge['fromuserid']);
					if ($otheruser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['challengeaccepted'])
					{			
						if ($otheruser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['useemail'])
						{
							vbmail($otheruser['email'],
							construct_phrase($vbphrase['challenge_accepted_t'], $vbulletin->userinfo['username']),
							construct_phrase($vbphrase['challenge_accepted_e'], $vbulletin->userinfo['username'], $vbulletin->options['bburl'] . '/arcade.php?do=play&challengeid=' .$challenge['challengeid'])
							);
						}
			
						if ($otheruser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['usepms'])
						{
							// Override a potentially full inbox.
							$senderpermissions['adminpermissions'] = 2;
			
							$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
							$pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
							$pmdm->set('fromusername', $vbulletin->userinfo['username']);
							$pmdm->set('title', construct_phrase($vbphrase['challenge_accepted_t'], $vbulletin->userinfo['username']));
							$pmdm->set('message', construct_phrase($vbphrase['challenge_accepted_p'], $vbulletin->userinfo['username'], $vbulletin->options['bburl'] . '/arcade.php?do=play&challengeid=' .$challenge['challengeid']));
							$pmdm->set_recipients($otheruser['username'], $senderpermissions);
							$pmdm->set('dateline', TIMENOW);
			
							$pmdm->save();
						}
					}
					
				}
			}
			
			if ($challenge['status']==1)
			{
				// The user can play.
				if ($vbulletin->userinfo['challengecache']==$challenge['challengeid'])
				{
					// This user has tried to play this challenge before, and they're trying again. :\
					standard_error($vbphrase['challenge_cant_play_twice']);
				} else {
					// Save the challenge id. We need to know that their next score is going to be associated with this challenge. 
					// We're doing this in the database because we can't count on cookies being enabled.
					$db->query_write("UPDATE " . TABLE_PREFIX . "user SET challengecache='" . $challenge['challengeid'] . "' WHERE userid=" . $vbulletin->userinfo['userid']);
				}
				$vbulletin->GPC['gameid'] = $challenge['gameid'];
				$templatename = 'arcade_play_challenge';
				
				// Avatar processing.
				$challenger['avatar'] = fetch_avatar_url(iif($vbulletin->userinfo['userid']==$challenge['touserid'], $challenge['fromuserid'], $challenge['touserid']));
				$challenger['useravatar'] = $challenger['avatar'][0];
				$challenger['avdimensions'] = $challenger['avatar'][1];
				if (!$challenger['useravatar'])
				{
					$challenger['useravatar'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
				}
			}
		} else {
			print_no_permission();
		}
	} else {
		$templatename = 'arcade_play';
	}

	// Can this user play?
	if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canplayarcade']))
	{
		print_no_permission();
	}

	// Check to see if this user is all-powerful.
	if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canoverridepermissions']))
	{
		// Game bitfield check. (Is Active.)
		$bitfieldcheck = '(gamepermissions & 1)';
	}

	if (!$game = $db->query_first("SELECT arcade_games.*, user.username, user.userid AS ouserid FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON user.userid=" . iif($vbulletin->GPC['challengeid'], iif($vbulletin->userinfo['userid']==$challenge['touserid'], $challenge['fromuserid'], $challenge['touserid']), 'arcade_games.highscorerid') . "
	WHERE gameid=" . $vbulletin->GPC['gameid'] . iif($bitfieldcheck, " AND $bitfieldcheck")))
	{
		print_no_permission();
	}
	
	// This hook is before the user plays, but *not* if they're responding to a challenge.
	($hook = vBulletinHook::fetch_hook('arcade_play')) ? eval($hook) : false;
	
	$game['highscore'] = sprintf((float)$game['highscore']);

	// Again, check to see if this user is all-powerful.
	if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canoverridepermissions']))
	{
		if ($vbulletin->userinfo['userid']==0)
		{
			$vbulletin->userinfo['posts'] = 0;
		}
		
		// How long has the player been a member? 
		$game['membershiplength'] = (TIMENOW - $vbulletin->userinfo['joindate'])/86400;
		
		// Usergroup permission checks.
		// Minimum post check.
		if ($vbulletin->userinfo['posts'] < $vbulletin->userinfo['permissions']['minpoststoplay'])
		{
			standard_error(construct_phrase($vbphrase['not_enough_posts'], $vbulletin->userinfo['permissions']['minpoststoplay'], $vbulletin->userinfo['posts'], $game['title']));
		}
		// Minimum reputation check.
		if ($vbulletin->options['reputationenable'])
		{
			if ($vbulletin->userinfo['reputation'] < $vbulletin->userinfo['permissions']['minreptoplay'])
			{
				standard_error(construct_phrase($vbphrase['rep_too_low'], $vbulletin->userinfo['permissions']['minreptoplay'], $vbulletin->userinfo['reputation'], $game['title']));
			}
		}
		// Minimum membership length check.
		if (floor($game['membershiplength']) < $vbulletin->userinfo['permissions']['minreglengthtoplay'])
		{
			standard_error(construct_phrase($vbphrase['not_registered_for_long_enough'], $vbulletin->userinfo['permissions']['minreglengthtoplay'], floor($game['membershiplength']), $game['title']));
		}
		
		// Minimum post check.
		if ($vbulletin->userinfo['posts'] < $game['minpoststotal'])
		{
			standard_error(construct_phrase($vbphrase['not_enough_posts'], $game['minpoststotal'], $vbulletin->userinfo['posts'], $game['title']));
		}

		// Minimum average posts per day check.
		$game['postsperday'] = $vbulletin->userinfo['posts'] / $game['membershiplength'];
		if ($game['postsperday'] < $game['minpostsperday'])
		{
			$game['postsperday'] = round((float)$game['postsperday'], 2);
			standard_error(construct_phrase($vbphrase['not_enough_ppd'], $game['minpostsperday'], $game['postsperday'], $game['title']));
		}
		
		// Minimum posts today check.
		if ($game['minpoststhisday'])
		{
			$postcheck = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "post WHERE userid=" . $vbulletin->userinfo['userid'] . " AND dateline<" . TIMENOW . " AND dateline>" . (TIMENOW-(24*60*60)));
			if ($postcheck['count'] < $game['minpoststhisday'])
			{
				$game['postsperday'] = round((float)$game['postsperday'], 2);
				standard_error(construct_phrase($vbphrase['not_enough_posts_today'], $game['minpoststhisday'], $postcheck['count'], $game['title']));
			}
		}

		// Minimum membership length check.
		if (floor($game['membershiplength']) < $game['minreglength'])
		{
			standard_error(construct_phrase($vbphrase['not_registered_for_long_enough'], $game['minreglength'], floor($game['membershiplength']), $game['title']));
		}

		// Minimum reputation check.
		if ($vbulletin->options['reputationenable'])
		{
			if ($vbulletin->userinfo['reputation'] < $game['minrep'])
			{
				standard_error(construct_phrase($vbphrase['rep_too_low'], $game['minrep'], $vbulletin->userinfo['reputation'], $game['title']));
			}
		}
	}

	// Use the correct flash code depending on the game system.
	switch ($game['system'])
	{
		case 0:
		// v3 Arcade Legacy Title
		eval('$flashcode = "' . fetch_template('arcade_v3game') . '";');
		break;
		case 10:
		// iB Arcade Legacy Title (The same for the time being)
		eval('$flashcode = "' . fetch_template('arcade_v3game') . '";');
		break;
	}

	// Let's get the navbar out of the way.
	$navbits['arcade.php'] = $vbphrase['arcade'];
	$navbits[''] = $game['title'];
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template($navbartemplate) . '";');

	eval('print_output("' . fetch_template($templatename) . '");');
}


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// v3 Arcade 3.0.x LEGACY MODE
// It could be prettier. But for the sake of backwards compatibility, it
// has to be this way!
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// NEW SESSION
// Someone's playing!
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['sessdo'] == 'sessionstart')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'gamename' => TYPE_NOHTML
	));

	// A relic of insanity.
	if (!$game = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "arcade_games AS arcade_games WHERE shortname='" . addslashes($vbulletin->GPC['gamename']) . "'"))
	{
		exit;
	}

	// Random numbers and the current timestamp.
	$gamerand = rand(1,10);

	// Create an empty session record.
	$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_sessions (gameid,gamename,userid,start,sessiontype,challengeid) VALUES ('" . $game['gameid'] . "', '" . addslashes($vbulletin->GPC['gamename']) . "','" . $vbulletin->userinfo['userid'] . "', '" . TIMENOW . "', 1, 0)");

	// Fetch the ID number of the session we just inserted.
	$lastid = $db->insert_id();

	// Give Flash something to feast on.
	echo "&connStatus=1&initbar=$gamerand&gametime=" . TIMENOW . "&lastid=$lastid&result=OK";
	echo "<META HTTP-EQUIV=Refresh CONTENT=\"0; URL=" . $vbulletin->options['bburl'] . "/arcade.php\">";
	exit;
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// PERMISSION REQUEST
// Someone has a score to report.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['sessdo'] == 'permrequest')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'note' => TYPE_NOHTML,
	'id' => TYPE_UINT,
	'gametime' => TYPE_UINT,
	'score' => TYPE_NOHTML,
	'key' => TYPE_UINT,
	'fakekey' => TYPE_UINT
	));

	if (
	!$vbulletin->GPC['note'] ||
	!$vbulletin->GPC['id'] ||
	!$vbulletin->GPC['fakekey'] ||
	!$vbulletin->GPC['gametime']
	)
	{
		exit;
	}

	$ceilscore = ceil($vbulletin->GPC['score']);
	$noteid = $vbulletin->GPC['note']/($vbulletin->GPC['fakekey']*$ceilscore);

	if ($noteid != $vbulletin->GPC['id']) {
		echo "&validate=0";
		exit;
	}

	// Gets accurate timestamp
	$microone = getmicrotime();

	// Don't ask.
	if ($vbulletin->GPC['score']==-1)
	{
		$vbulletin->GPC['score'] = 0;
	}
	
	$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_sessions SET score='" . addslashes($vbulletin->GPC['score']) . "', finish='" . TIMENOW . "' WHERE sessionid=" . $vbulletin->GPC['id'] . " AND start=" . $vbulletin->GPC['gametime'] . " AND userid=" . $vbulletin->userinfo['userid']);
	echo "&validate=1&microone=$microone&result=OK";
	exit;
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// BURN SESSION
// So called because we're validating this session.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['sessdo'] == 'burn')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'id' => TYPE_UINT,
	'microone' => TYPE_NUM
	));

	if (!$game = $db->query_first("SELECT arcade_sessions.*, arcade_games.* FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON arcade_games.gameid=arcade_sessions.gameid
	WHERE sessionid=" . $vbulletin->GPC['id'] . "
	AND userid=" . $vbulletin->userinfo['userid']))
	{
		// Show them the door, since the session doesn't exist or it doesn't belong to them.
		print_no_permission();
	}

	// Get category and page history data.
	$catpagedata = findcatpage();

	if ($game['votecount'])
	{
		$game['rating_acc'] = round(($game['votepoints']/$game['votecount']), 2);
		$game['rating'] = ceil($game['rating_acc']);
	}
	
	($hook = vBulletinHook::fetch_hook('arcade_burn')) ? eval($hook) : false;

	// If someone is registered, let them rate games
	if ($vbulletin->userinfo['userid']!=0)
	{
		// Get this user's rating for the current game, if they voted.
		$show['rating'] = true;
		$show['favorites'] = true;
		
		if ($userrating = $db->query_first("SELECT rating  FROM " . TABLE_PREFIX . "arcade_ratings AS arcade_ratings
		WHERE gameid=$game[gameid] AND userid=" . $vbulletin->userinfo['userid']))
		{
			$game['initrating'] = 'starover(' . $userrating['rating'] . '); enablerating=0;';
		}
	}
	
	// It's important we only allow this code to be run once per game session.
	if ($game['ping'] > 0)
	{
		// There's already a ping, so what are we doing here?
		print_no_permission();
	}

	// Show context-sensitive elements.

	// Can this user post comments?
	if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canpostcomments'])
	{
		$show['commentbox'] = true;
	}

	$show['thanksforplaying'] = true;

	// We're going to need to parse the comment text.
	require_once('./includes/class_bbcode.php');
	$parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

	// Misc. variable setting.
	$placecounter = 1;
	$highscore = 0;
	$scorebits = '';
	$highnotify = '';
	$difference = sprintf('%.1f',(getmicrotime()-$vbulletin->GPC['microone'])/2*1000);
	
	// Did a guest just play this game?
	if ($vbulletin->userinfo['userid']==0)
	{
		$show['guestscore'] = true;
	} else {
		// A registered user scored something, so let's make it count!
		// Pings greater than 4500ms don't count.
		if ($difference > 4500) {
			$validate = 0;
			print_no_permission();
		} else {
			$validate = 1;
			// Increase the valid session count.
			$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET sessioncount=sessioncount+1 WHERE gameid=$game[gameid]");
		}
	
		// Save the session.
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_sessions SET ping='$difference', valid=$validate WHERE sessionid=" . $vbulletin->GPC['id'] . " AND userid=" . $vbulletin->userinfo['userid']);
	}
	
	// Is this a challenge?
	if ($vbulletin->userinfo['challengecache'])
	{
		$challenge = $db->query_first("SELECT arcade_challenges.*, touser.arcadeoptions AS toarcadeoptions, fromuser.arcadeoptions AS fromarcadeoptions, touser.email AS toemail, fromuser.email AS fromemail, touser.username AS tousername, fromuser.username AS fromusername FROM " . TABLE_PREFIX . "arcade_challenges AS arcade_challenges 
		LEFT JOIN " . TABLE_PREFIX . "user AS touser ON (touser.userid=arcade_challenges.touserid)
		LEFT JOIN " . TABLE_PREFIX . "user AS fromuser ON (fromuser.userid=arcade_challenges.fromuserid)
		WHERE challengeid=" . $vbulletin->userinfo['challengecache']);
		// 1 = Challenger
		// 0 = Challenged
		$challenge['stance'] = iif($vbulletin->userinfo['userid']==$challenge['touserid'], 0, 1);
	
		if ($challenge['gameid']==$game['gameid'])
		{
			// It's the same game, so it's valid. 
			if ($challenge['stance']==1)
			{
				$challenge['fromscore'] = $game['score'];
				$challenge['fromsessionid'] = $vbulletin->GPC['id'];

				$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_challenges SET fromsessionid=" . $vbulletin->GPC['id'] . ", fromscore='$game[score]' WHERE challengeid=" . $challenge['challengeid']);
			} else {
				$challenge['toscore'] = $game['score'];
				$challenge['tosessionid'] = $vbulletin->GPC['id'];
				
				$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_challenges SET tosessionid=" . $vbulletin->GPC['id'] . ", toscore='$game[score]' WHERE challengeid=" . $challenge['challengeid']);
			}
			
			// Clear the cache.
			$db->query_write("UPDATE " . TABLE_PREFIX . "user SET challengecache='' WHERE userid=" . $vbulletin->userinfo['userid']);
			
			if ($challenge['tosessionid'] AND $challenge['fromsessionid'])
			{
				// Both scores are in!
				if ($challenge['fromscore']>$challenge['toscore'])
				{
					$challenge['winnerid'] = iif($game['isreverse']==1, $challenge['touserid'], $challenge['fromuserid']);
					$challenge['loserid'] = iif($game['isreverse']!=1, $challenge['touserid'], $challenge['fromuserid']);
				} else {
					$challenge['winnerid'] = iif($game['isreverse']!=1, $challenge['touserid'], $challenge['fromuserid']);
					$challenge['loserid'] = iif($game['isreverse']==1, $challenge['touserid'], $challenge['fromuserid']);
				}
				
				// Challenge complete.
				$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_challenges SET winnerid=$challenge[winnerid], loserid=$challenge[loserid], status=3 WHERE challengeid=" . $challenge['challengeid']);

				$show['challengeresults'] = true;
				
				$otheruser['options'] = iif($vbulletin->userinfo['userid']==$challenge['touserid'], $challenge['fromarcadeoptions'], $challenge['toarcadeoptions']);
				$otheruser['email'] = iif($vbulletin->userinfo['userid']==$challenge['touserid'], $challenge['fromemail'], $challenge['toemail']);
				$otheruser['username'] = iif($vbulletin->userinfo['userid']==$challenge['touserid'], $challenge['fromusername'], $challenge['tousername']);
				
				if ($otheruser['options'] & $vbulletin->bf_misc_arcadeoptions['finishedchallenge'])
				{
					
					$challenge['fromscore'] = sprintf((float)$challenge['fromscore']);
					$challenge['toscore'] = sprintf((float)$challenge['toscore']);
					
					if ($otheruser['options'] & $vbulletin->bf_misc_arcadeoptions['useemail'])
					{
						vbmail($otheruser['email'],
						construct_phrase($vbphrase['challenge_results_t'], $challenge['fromusername'], $challenge['tousername']),
						construct_phrase($vbphrase['challenge_results_e'], $challenge['fromusername'], $challenge['fromscore'], $challenge['tousername'], $challenge['toscore'])
						);
					}
	
					if ($otheruser['options'] & $vbulletin->bf_misc_arcadeoptions['usepms'])
					{
						// Override a potentially full inbox.
						$senderpermissions['adminpermissions'] = 2;
	
						$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
						$pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
						$pmdm->set('fromusername', $vbulletin->userinfo['username']);
						$pmdm->set('title', construct_phrase($vbphrase['challenge_results_t'], $challenge['fromusername'], $challenge['tousername']));
						$pmdm->set('message', construct_phrase($vbphrase['challenge_results_e'], $challenge['fromusername'], $challenge['fromscore'], $challenge['tousername'], $challenge['toscore']));
						$pmdm->set_recipients($otheruser['username'], $senderpermissions);
						$pmdm->set('dateline', TIMENOW);
	
						$pmdm->save();
					}
				}

			} else {
				$show['challengepart'] = true;
			}
			
		}
		
		
	}
	
	// Let's get the navbar out of the way.
	$navbits['arcade.php'] = $vbphrase['arcade'];
	$navbits["arcade.php?do=play&amp;gameid=$game[gameid]"] = $game['title'];
	$navbits[] = "$game[title] $vbphrase[high_scores]";
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template($navbartemplate) . '";');

	// Time for the scores.
	$scores = $db->query_read("SELECT arcade_sessions.*, user.username, user.arcadeoptions" . iif($vbulletin->options['distinctscores'], ", " . iif($game['isreverse']==1, 'MIN', 'MAX') . "(arcade_sessions.score) AS score") . " FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=arcade_sessions.userid)
	WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid]
	" . iif($vbulletin->options['distinctscores'], "GROUP BY arcade_sessions.userid") . "
	ORDER BY score " . iif($game['isreverse']==1, 'ASC', 'DESC') . ", arcade_sessions.finish DESC
	LIMIT " . $vbulletin->options['scoresperpage']);
	
	if ($vbulletin->options['distinctscores'])
	{
		$scorecache = array();
		while ($score = $db->fetch_array($scores))
		{
			$scorecache[] = "(arcade_sessions.score=$score[score] AND arcade_sessions.userid=$score[userid])";
		}
		$scorecache = implode(' OR ', $scorecache);
		
		$scores = $db->query_read("SELECT arcade_sessions.*, user.username, user.arcadeoptions FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=arcade_sessions.userid)
		WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid] AND ($scorecache)
		ORDER BY score " . iif($game['isreverse']==1, 'ASC', 'DESC') . ", arcade_sessions.finish DESC
		LIMIT " . $vbulletin->options['scoresperpage']);
	}

	while ($score = $db->fetch_array($scores))
	{
		if ($placecounter == 1)
		{
			$score['avatar'] = fetch_avatar_url($score['userid']);
			$score['useravatar'] = $score['avatar'][0];
			$score['avdimensions'] = $score['avatar'][1];

			// If the user has no avatar, use the default placeholder.
			if (!$score['useravatar'])
			{
				$score['useravatar'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
			}

			$scorebittemplate = 'arcade_scorebit_high';
		} else {
			$scorebittemplate = 'arcade_scorebit';
		}

		// Values to be replaced with usergroup permissions.
		$score['comment'] = $parser->do_parse($score['comment'], 0, 1, 1, 0, 0);
		
		if ($vbulletin->options['distinctscores'] AND ($score['score']==$game['score']) AND ($score['userid']==$vbulletin->userinfo['userid']))
		{
			// It's not technically correct, but it saves some messy queries.
			$score['sessionid'] = $vbulletin->GPC['id'];
		}

		if ($score['sessionid'] == $vbulletin->GPC['id'])
		{
			// $highscore contains the rank of the new session.
			$highscore = $placecounter;
			
			// Congratulate the user on doing so well.
			$show['highscore'] = true;

			// A new champion.
			if ($highscore==1 && $vbulletin->userinfo['userid']!=$game['highscorerid'])
			{
				if ($vbulletin->options['neweventonhighscore'])
				{
					// Add a new news item, since there's a new champion for this game.
					$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_news (newstext, newstype, datestamp) VALUES ('" . addslashes(construct_phrase($vbphrase['x_is_the_new_champion'], $vbulletin->userinfo['username'], $game['title'], $game['gameid'])) . "', 'auto', " . TIMENOW . ")");
				}
				
				($hook = vBulletinHook::fetch_hook('arcade_new_champion')) ? eval($hook) : false;

				// Break the bad news the next guy/gal.
				$highnotify = true;
			}

			$page = 1;
			

			if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canpostcomments'])
			{
				// It's a new high score - time for the comment form.
				eval('$score[comment] = "' . fetch_template('arcade_commentform') . '";');
			}


		} else {
			$show['highscore'] = false;

			// Send new high score notifications.
			if ($highnotify==true && ($score['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['highscorebeaten']))
			{

				if ($score['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['useemail'])
				{
					vbmail($score['email'],
					construct_phrase($vbphrase['notify_highscorebeaten_t'], $game['title'], $vbulletin->userinfo['username']),
					construct_phrase($vbphrase['notify_highscorebeaten_e'], $game['title'], $vbulletin->userinfo['username'], $game['score'], $score['score'], $score['username'], $vbulletin->options['bburl'] . '/arcade.php')
					);
				}

				if ($score['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['usepms'])
				{
					// Override a potentially full inbox.
					$senderpermissions['adminpermissions'] = 2;

					$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
					$pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
					$pmdm->set('fromusername', $vbulletin->userinfo['username']);
					$pmdm->set('title', construct_phrase($vbphrase['notify_highscorebeaten_t'], $game['title'], $vbulletin->userinfo['username']));
					$pmdm->set('message', construct_phrase($vbphrase['notify_highscorebeaten'], $game['title'], $vbulletin->userinfo['username'], $game['score'], $score['score'], $score['username']));
					$pmdm->set_recipients($score['username'], $senderpermissions);
					$pmdm->set('dateline', TIMENOW);
 					if ($pmdm->errors) {
				        return $pmdm->errors;
				    }
				    
					$pmdm->save();
				}
			}
			
			$highnotify = false;

		}

		// Calculates the session's length.
		$score['sessionlength'] = $score['finish']-$score['start'];
		$score['date'] = vbdate($vbulletin->options['scoredateformat'], $score['finish']);

		// Switch the class for each row.
		exec_switch_bg();
		
		// Format the score.
		$score['score'] = sprintf((float)$score['score']);

		// Spit out the row.
		eval('$scorebits .= "' . fetch_template($scorebittemplate) . '";');

		$placecounter++;
	}

	// A final $bgclass switch.
	exec_switch_bg();

	if ($highscore == 1)
	{
		// There's a new high score.
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET highscorerid=" . $vbulletin->userinfo['userid'] . ", highscore=$game[score] WHERE gameid=$game[gameid]");
	}

	// If the score didn't make it to page one, find the actual rank.
	if (!$highscore)
	{
		if ($vbulletin->options['distinctscores'])
		{
			$check = $db->query_read("SELECT " . iif($game['isreverse']==1, 'MIN', 'MAX') . "(arcade_sessions.score) AS highscore, arcade_sessions.userid FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
			WHERE arcade_sessions.gameid=$game[gameid] AND score" . iif($game['isreverse']==1, '<', '>') . "$game[score]
			GROUP BY arcade_sessions.userid");
			$check2['rank'] = $db->num_rows($check)+1;
		} else {
			$check = $db->query_first("SELECT COUNT(*)+1 AS rank FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
			WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid] AND score" . iif($game['isreverse']==1, '<', '>') . "$game[score]");

			$check2 = $db->query_first("SELECT COUNT(*)+1 AS rank FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
			WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid] AND score='$game[score]' AND finish>$game[finish]");
		}
		$highscore = $check['rank'] + $check2['rank'];

		$page = ceil($highscore/$vbulletin->options['scoresperpage']);

		if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canpostcomments'])
		{
			eval('$game[comment] = "' . fetch_template('arcade_commentform') . '";');
		}
	}
	
	// If a guest just played, adjust the rank (because the session didn't get recorded, and doesn't count)
	if ($vbulletin->userinfo['userid']==0)
	{
		$highscore = $highscore - 1;
	}
			

	$game['ordinal'] = ordinal($highscore);
	$game['rank'] = $highscore;
	$game['sessioncount'] = $game['sessioncount']+1;


	// Quick Statistics.
	if ($vbulletin->options['showquickstats'])
	{
		$game['betterthan'] = round(((($game['sessioncount'] - $game['rank'])+1)/$game['sessioncount'])*100);
		$game['worsethan'] = 100 - $game['betterthan'];

		// A manual override for scores of zero, as a more recent loser outranks an older one.
		if ($game['score'] == 0)
		{
			$game['betterthan'] = 0;
			$game['worsethan'] = 100;
		}

		$show['quickstats'] = true;
	}
	
	$game['score'] = sprintf((float)$game['score']);

	// Score Feedback.
	if ($vbulletin->options['showscorefeedback'])
	{

		// Offer advice based on their score rank.
		switch (true)
		{
			case ($game['betterthan'] == 100):
			$vbphrase['arcadeadvise'] = $vbphrase['arcade_100'];
			break;
			case ($game['betterthan'] >= 80 && $game['betterthan'] < 100):
			$vbphrase['arcadeadvise'] = $vbphrase['arcade_80'];
			break;
			case ($game['betterthan'] >= 60 && $game['betterthan'] < 80):
			$vbphrase['arcadeadvise'] = $vbphrase['arcade_60'];
			break;
			case ($game['betterthan'] >= 40 && $game['betterthan'] < 60):
			$vbphrase['arcadeadvise'] = $vbphrase['arcade_40'];
			break;
			case ($game['betterthan'] >= 20 && $game['betterthan'] < 40):
			$vbphrase['arcadeadvise'] = $vbphrase['arcade_20'];
			break;
			case ($game['betterthan'] >= 0 && $game['betterthan'] < 20):
			$vbphrase['arcadeadvise'] = $vbphrase['arcade_0'];
			break;
		}

		$show['scorefeedback'] = true;
	}

	// Game Recommendations.
	if ($vbulletin->options['showrecommendations'])
	{
		if ($newgame = $db->query_first("SELECT gameid, miniimage, title FROM " . TABLE_PREFIX . "arcade_games WHERE categoryid=$game[categoryid] AND gameid<>$game[gameid] ORDER BY RAND() LIMIT 1"))
		{
			$show['recommended'] = true;
			eval('$recommended = "' . fetch_template('arcade_recommendation') . '";');
		}
	}

	// AJAX game searching include code.
	eval('$ajaxinclude = "' . fetch_template('arcade_ajaxsearch') . '";');

	eval('print_output("' . fetch_template('arcade_scores') . '");');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// SUBMIT COMMENT
// Accepts a user's comment post after playing a game.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'commentsave')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'sessionid' => TYPE_UINT,
	'page' => TYPE_UINT,
	'commenttext' => TYPE_NOCLEAN
	));

	// Can this user post comments?
	if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canpostcomments']))
	{
		print_no_permission();
	}

	if (!$gamesession = $db->query_first("SELECT sessionid, userid, comment, gameid FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions WHERE sessionid=" . $vbulletin->GPC['sessionid']))
	{
		// Session doesn't exist.
		print_no_permission();
	}

	// Permission checking.
	if (!$gamesession['comment'])
	{
		// There's no comment, so check to see if this user can post a new one.
		if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['canpostcomments']))
		{
			print_no_permission();
		}
	} else if ($gamesession['userid']==$vbulletin->userinfo['userid']) {
		// The user wants to edit their own comment - can they?
		if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['caneditowncomments']))
		{
			print_no_permission();
		}
	} else {
		// This crafty devil wants to edit someone else's comment.
		if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['caneditanycomments']))
		{
			print_no_permission();
		}
	}

	if (strlen($vbulletin->GPC['commenttext']) > $vbulletin->options['commentmaxlength'])
	{
		// If they used the original form, they shouldn't be able to submit anything longer than the max comment length.
		print_no_permission();
	}

	$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_sessions SET comment='" . addslashes($vbulletin->GPC['commenttext']) . "' WHERE sessionid=" . $vbulletin->GPC['sessionid']);

	$vbulletin->url = "arcade.php?do=scores&amp;gameid=$gamesession[gameid]&amp;page=" . $vbulletin->GPC['page'] . "&amp;sessionid=" . $gamesession['sessionid'] . iif($vbulletin->options['scrolltoscore'], "#session$gamesession[sessionid]");
	eval(print_standard_redirect('redirect_commentsaved'));
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// COMMENT FETCH
// Sending back data for editing purposes.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'commentfetch')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'sessionid' => TYPE_UINT,
	'page' => TYPE_UINT
	));

	if ($game = $db->query_first("SELECT sessionid, comment FROM " . TABLE_PREFIX . "arcade_sessions WHERE sessionid=" . $vbulletin->GPC['sessionid']))
	{
		$page = $vbulletin->GPC['page'];
		eval('print_output("' . fetch_template('arcade_editcommentform') . '");');
	} else {
		print_no_permission();
	}
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// COMMENT/SCORE DELETE
// Delete a game session.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'commentdelete')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'sessionid' => TYPE_UINT
	));

	// A quick definition.
	$newchampsql = '';

	if ($game = $db->query_first("SELECT arcade_sessions.sessionid, arcade_sessions.comment, arcade_sessions.gameid, arcade_sessions.score, arcade_sessions.userid, arcade_games.highscorerid, arcade_games.highscore FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON (arcade_games.gameid=arcade_sessions.gameid)
	WHERE sessionid=" . $vbulletin->GPC['sessionid']))
	{
		if ($game['userid'] == $vbulletin->userinfo['userid'])
		{
			// Okay, the current user is the owner of this score.
			if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['candeleteownscores']))
			{
				print_no_permission();
			}
		}  else {
			// So, can this user delete any score?
			if (!($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['candeleteanyscores']))
			{
				print_no_permission();
			}
		}

		// Well, we're still here. So:
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_sessions WHERE sessionid=" . $vbulletin->GPC['sessionid']);

		if ($game['userid']==$game['highscorerid'] && $game['highscore']==$game['score'])
		{
			// You know what? We just deleted the highest scorer for this game. Let's find the new champ.
			if ($champcheck = $db->query_first("SELECT userid, score FROM " . TABLE_PREFIX . "arcade_sessions WHERE valid=1 AND gameid=$game[gameid] ORDER BY score " . iif($game['isreverse']==1, 'ASC', 'DESC') . ", finish DESC LIMIT 1"))
			{
				$newchampsql = ", highscorerid=$champcheck[userid], highscore='$champcheck[score]'";
			} else {
				// Just in case you just deleted the last score for this game. You know, it could happen.
				$newchampsql = ", highscorerid=0, highscore=0";
			}
		}

		// Reduce the session count for the game.
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET sessioncount=sessioncount-1$newchampsql WHERE gameid=$game[gameid]");

		$vbulletin->url = "arcade.php?do=scores&amp;gameid=$game[gameid]";
		eval(print_standard_redirect('redirect_commentdeleted'));

	} else {
		print_no_permission();
	}
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// SCORES
// View scores for a game.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'scores')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'sessionid' => TYPE_UINT,
	'gameid' => TYPE_UINT,
	'perpage' => TYPE_UINT,
	'pagenumber' => TYPE_UINT
	));

	$favcache = unserialize($vbulletin->userinfo['favcache']);
	if ($favcache[$vbulletin->GPC['gameid']])
	{
		$show['fav'] = true;
	} else {
		$show['fav'] = false;
	}
	
	if (!$game = $db->query_first("SELECT arcade_games.* FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
	WHERE arcade_games.gameid=" . $vbulletin->GPC['gameid']))
	{
		// Show them the door, since the game doesn't exist.
		print_no_permission();
	}

	// Get category and page history data.
	$catpagedata = findcatpage();

	if ($game['votecount'])
	{
		$game['rating_acc'] = round(($game['votepoints']/$game['votecount']), 2);
		$game['rating'] = ceil($game['rating_acc']);
	}

	// Get this user's rating for the current game, if they voted.
	$show['rating'] = true;
	if ($userrating = $db->query_first("SELECT rating  FROM " . TABLE_PREFIX . "arcade_ratings AS arcade_ratings
	WHERE gameid=" . $vbulletin->GPC['gameid'] . " AND userid=" . $vbulletin->userinfo['userid']))
	{
		$game['initrating'] = 'starover(' . $userrating['rating'] . '); enablerating=0;';
	}

	// We're going to need to parse the comment text.
	require_once('./includes/class_bbcode.php');
	$parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

	// Let's get the navbar out of the way.
	$navbits['arcade.php'] = $vbphrase['arcade'];
	$navbits["arcade.php?do=play&amp;gameid=$game[gameid]"] = $game['title'];
	$navbits[] = "$game[title] $vbphrase[high_scores]";
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template($navbartemplate) . '";');

	$perpage = $vbulletin->GPC['perpage'];
	$pagenumber = $vbulletin->GPC['pagenumber'];

	if ($vbulletin->options['distinctscores'])
	{
		$gamecheck = $db->query_read("SELECT arcade_sessions.userid FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=arcade_sessions.userid)
		WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid]
		GROUP BY arcade_sessions.userid");
		$game['sessioncount'] = $db->num_rows($gamecheck);
	}

	// set defaults
	sanitize_pageresults($game['sessioncount'], $pagenumber, $perpage, 100, $vbulletin->options['scoresperpage']);

	$start = (int)($perpage*$pagenumber)-$perpage;

	// Defining some miscellaneous variables.
	$placecounter = $start+1;

	$pagenav = construct_page_nav($pagenumber, $perpage, $game['sessioncount'], 'arcade.php?do=scores' . $vbulletin->session->vars['sessionurl'], ""
	. (!empty($vbulletin->GPC['gameid']) ? "&amp;gameid=" . $vbulletin->GPC['gameid'] : "")
	. (!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : "")
	);

	// Time for the scores.
	$scores = $db->query_read("SELECT arcade_sessions.*, user.username" . iif($vbulletin->options['distinctscores'], ", " . iif($game['isreverse']==1, 'MIN', 'MAX') . "(arcade_sessions.score) AS score") . " FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=arcade_sessions.userid)
	WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid]
	" . iif($vbulletin->options['distinctscores'], "GROUP BY arcade_sessions.userid") . "
	ORDER BY score " . iif($game['isreverse']==1, 'ASC', 'DESC') . ", arcade_sessions.finish DESC
	LIMIT $start, " . $vbulletin->options['scoresperpage']);
	
	if ($vbulletin->options['distinctscores'])
	{
		$scorecache = array();
		while ($score = $db->fetch_array($scores))
		{
			$scorecache[] = "(arcade_sessions.score='$score[score]' AND arcade_sessions.userid=$score[userid])";
		}
		$scorecache = implode(' OR ', $scorecache);
		
		$scores = $db->query_read("SELECT arcade_sessions.*, user.username FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=arcade_sessions.userid)
		WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid] " . iif($scorecache, "AND ($scorecache)") . "
		ORDER BY score " . iif($game['isreverse']==1, 'ASC', 'DESC') . ", arcade_sessions.finish DESC
		LIMIT " . $vbulletin->options['scoresperpage']);
	}

	while ($score = $db->fetch_array($scores))
	{
		if ($placecounter == 1)
		{
			$score['avatar'] = fetch_avatar_url($score['userid']);
			$score['useravatar'] = $score['avatar'][0];
			$score['avdimensions'] = $score['avatar'][1];

			// If the user has no avatar, use the default placeholder.
			if (!$score['useravatar'])
			{
				$score['useravatar'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
			}

			$scorebittemplate = 'arcade_scorebit_high';
		} else {
			$scorebittemplate = 'arcade_scorebit';
		}

		if ($score['sessionid']==$vbulletin->GPC['sessionid'])
		{
			$show['newscore'] = true;
		} else {
			$show['newscore'] = false;
		}

		$show['deletebutton'] = false;
		$show['editbutton'] = false;

		// Permission checking for editing/deletion.
		if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['caneditanycomments'])
		{
			$show['editbutton'] = true;
		}
		if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['candeleteanyscores'])
		{
			$show['deletebutton'] = true;
		}

		if ($score['userid'] == $vbulletin->userinfo['userid'])
		{
			if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['caneditowncomments'])
			{
				$show['editbutton'] = true;
			}
			if ($permissions['arcadepermissions'] & $vbulletin->bf_ugp['arcadepermissions']['candeleteownscores'])
			{
				$show['deletebutton'] = true;
			}
		}

		// Values to be replaced with usergroup permissions.
		$score['comment'] = $parser->do_parse($score['comment'], $vbulletin->options['comments_allowhtml'], $vbulletin->options['comments_allowsmilies'], $vbulletin->options['comments_allowbbcode'], $vbulletin->options['comments_allowimgcode'], 0);

		// Calculates the session's length.
		$score['sessionlength'] = $score['finish']-$score['start'];
		$score['date'] = vbdate($vbulletin->options['scoredateformat'], $score['finish']);

		// Switch the class for each row.
		exec_switch_bg();
		
		// Format the score.
		$score['score'] = sprintf((float)$score['score']);

		// Spit out the row.
		eval('$scorebits .= "' . fetch_template($scorebittemplate) . '";');

		$placecounter++;
	}

	// Get this user's personal best and stats.
	if ($best = $db->query_read("SELECT sessionid, score, finish FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions WHERE valid=1 AND gameid=$game[gameid] AND userid=" . $vbulletin->userinfo['userid'] . " ORDER BY score " . iif($game['isreverse']==1, 'ASC', 'DESC') . ", arcade_sessions.finish DESC"))
	{
		if ($game['playcount'] = $db->num_rows($best))
		{
			$show['personalbest'] = true;

			$check = $db->fetch_array($best);
			$db->free_result($best);

			$game['pbscore'] = sprintf((float)$check['score']);

			if ($vbulletin->options['distinctscores'])
			{
				$check = $db->query_read("SELECT " . iif($game['isreverse']==1, 'MIN', 'MAX') . "(arcade_sessions.score) AS highscore, arcade_sessions.userid FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
				WHERE arcade_sessions.gameid=$game[gameid] AND score" . iif($game['isreverse']==1, '<', '>') . "$game[pbscore]
				GROUP BY arcade_sessions.userid");
				$check2['rank'] = $db->num_rows($check)+1;
			} else {
				// Unfortunately, we need another query to find out the rank.
				$check2 = $db->query_first("SELECT COUNT(*)+1 AS rank FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
				WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid] AND score" . iif($game['isreverse']==1, '<', '>') . "$game[pbscore]");

				$check3 = $db->query_first("SELECT COUNT(*) AS rank FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
				WHERE arcade_sessions.valid=1 AND arcade_sessions.gameid=$game[gameid] AND score='$game[pbscore]' AND finish>$check[finish]");
			}
			$game['pbrank'] = $check2['rank'] + $check3['rank'];
			$game['ordinal'] = ordinal($game['pbrank']);
		} else {
			$show['personalbest'] = false;
		}
	}

	$show['gameoverview'] = true;

	// AJAX game searching include code.
	eval('$ajaxinclude = "' . fetch_template('arcade_ajaxsearch') . '";');

	eval('print_output("' . fetch_template('arcade_scores') . '");');
}


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// CHALLENGE USER
// Challenge a user to a contest.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'newchallenge')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'userid' => TYPE_UINT
	));
	
	$touser = verify_id('user', $vbulletin->GPC['userid'], true, true, 2);
	
	if (!($touser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['allowchallenges']))
	{
		print_no_permission();
	}
	
	// Stop guests from challenging.
	if ($vbulletin->userinfo['userid']==0)
	{
		print_no_permission();
	}
	
	// Stop guests from being challenged.
	if ($vbulletin->GPC['userid']==0)
	{
		print_no_permission();
	}
	
	// Avatar processing.
	$touser['avatar'] = fetch_avatar_url($touser['userid']);
	$touser['useravatar'] = $touser['avatar'][0];
	$touser['avdimensions'] = $touser['avatar'][1];
	if (!$touser['useravatar'])
	{
		$touser['useravatar'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
	}
	
	$vbulletin->userinfo['avatar'] = fetch_avatar_url($vbulletin->userinfo['userid']);
	$vbulletin->userinfo['useravatar'] = $vbulletin->userinfo['avatar'][0];
	$vbulletin->userinfo['avdimensions'] = $vbulletin->userinfo['avatar'][1];
	if (!$vbulletin->userinfo['useravatar'])
	{
		$vbulletin->userinfo['useravatar'] = $vbulletin->options['arcadeimages'] . '/noavatar.gif';
	}
	
	// Fetch quick statistics. 
	$quickstats = array();
	$winners = $db->query_read("SELECT COUNT(*) AS count, winnerid FROM " . TABLE_PREFIX . "arcade_challenges WHERE winnerid IN (" . $vbulletin->userinfo['userid'] . ", $touser[userid]) GROUP BY winnerid");
	while (list($count, $userid) = $db->fetch_row($winners))
	{
		$quickstats[$userid]['won'] = $count;
	}
	
	$losers = $db->query_read("SELECT COUNT(*) AS count, loserid FROM " . TABLE_PREFIX . "arcade_challenges WHERE loserid IN (" . $vbulletin->userinfo['userid'] . ", $touser[userid]) GROUP BY loserid");
	while (list($count, $userid) = $db->fetch_row($losers))
	{
		$quickstats[$userid]['lost'] = $count;
	}
	
	// Adding the stats data into the user arrays. (Used to clear up zero values, too.)
	$vbulletin->userinfo['won'] = intval($quickstats[$vbulletin->userinfo['userid']]['won']);
	$vbulletin->userinfo['lost'] = intval($quickstats[$vbulletin->userinfo['userid']]['lost']);
	$touser['won'] = intval($quickstats[$touser['userid']]['won']);
	$touser['lost'] = intval($quickstats[$touser['userid']]['lost']);
	
	// Fetch valid games.
	$gameoptions = '';
	$games = $db->query_read("SELECT title, gameid FROM " . TABLE_PREFIX . "arcade_games WHERE (gamepermissions & 4) AND (gamepermissions & 1) AND system=0 ORDER BY title ASC");
	while (list($title, $gameid) = $db->fetch_row($games))
	{
		$gameoptions .= "<option value=\"$gameid\">$title</option>";
	}
	
	// Let's get the navbar out of the way.
	$navbits['arcade.php'] = $vbphrase['arcade'];
	$navbits[] = construct_phrase($vbphrase['challenging_x'], $touser['username']);
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template($navbartemplate) . '";');

	eval('print_output("' . fetch_template('arcade_newchallenge') . '");');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DO NEW CHALLENGE
// Process the new challenge. 
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'donewchallenge')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'userid' => TYPE_UINT,
	'gameid' => TYPE_UINT
	));
	
	// Make sure there isn't already a challenge like this.
	if ($challenge = $db->query_first("SELECT challengeid, title, username FROM " . TABLE_PREFIX . "arcade_challenges AS arcade_challenges 
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON (arcade_challenges.gameid=arcade_games.gameid)
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (arcade_challenges.touserid=user.userid)
	WHERE fromuserid=" . $vbulletin->userinfo['userid'] . " AND touserid=" . $vbulletin->GPC['userid'] . " AND arcade_challenges.gameid=" . $vbulletin->GPC['gameid'] . " AND status<2 LIMIT 1"))
	{
		standard_error(construct_phrase($vbphrase['challenge_already_exists'], $challenge['username'], $challenge['title']));
	} else {
		// Go ahead with the challenge.
		$touser = verify_id('user', $vbulletin->GPC['userid'], true, true, 2);
	
		if (!($touser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['allowchallenges']))
		{
			print_no_permission();
		}
		
		// Check that the game exists.
		if (!$game = $db->query_first("SELECT gameid, title FROM " . TABLE_PREFIX . "arcade_games WHERE gameid=" . $vbulletin->GPC['gameid']))
		{
			print_no_permission();
		}
		
		if ($touser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['autoaccept'])
		{
			// The challenged user wants to automatically accept all challenges.
			$status = 1;
		} else {
			$status = 0;
		}
		
	
		$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_challenges (fromuserid, touserid, gameid, datestamp, status) VALUES (" . $vbulletin->userinfo['userid'] . ", " . $touser['userid'] . ", " . $vbulletin->GPC['gameid'] . ", " . TIMENOW . ", '$status')");
		$challengeid = $db->insert_id();
		
		if ($touser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['newchallenge'])
		{			
			if ($touser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['useemail'])
			{
				vbmail($touser['email'],
				construct_phrase($vbphrase['new_challenge_t'], $vbulletin->userinfo['username']),
				construct_phrase($vbphrase['new_challenge_e'], $vbulletin->userinfo['username'], $vbulletin->options['bburl'] . '/arcade.php?do=play&challengeid=' . $challengeid, $game['title'], $vbulletin->options['bburl'] . '/arcade.php?do=declinechallenge&challengeid=' . $challengeid)
				);
			}

			if ($touser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['usepms'])
			{
				// Override a potentially full inbox.
				$senderpermissions['adminpermissions'] = 2;

				$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
				$pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
				$pmdm->set('fromusername', $vbulletin->userinfo['username']);
				$pmdm->set('title', construct_phrase($vbphrase['new_challenge_t'], $vbulletin->userinfo['username']));
				$pmdm->set('message', construct_phrase($vbphrase['new_challenge_p'], $vbulletin->userinfo['username'], $vbulletin->options['bburl'] . '/arcade.php?do=play&challengeid=' . $challengeid, $game['title'], $vbulletin->options['bburl'] . '/arcade.php?do=declinechallenge&challengeid=' . $challengeid));
				$pmdm->set_recipients($touser['username'], $senderpermissions);
				$pmdm->set('dateline', TIMENOW);

				$pmdm->save();
			}
		}
		
		$vbulletin->url = "arcade.php?do=play&gameid=$game[gameid]&challengeid=$challengeid";
		standard_redirect($vbphrase['your_challenge_has_been_submitted']);
	}
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DECLINE CHALLENGE
// Decline someone's challenge.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'declinechallenge')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'challengeid' => TYPE_UINT
	));
	
	if (!$challenge = $db->query_first("SELECT fromuserid, touserid FROM " . TABLE_PREFIX . "arcade_challenges AS arcade_challenges WHERE challengeid=" . $vbulletin->GPC['challengeid']))
	{
		print_no_permission();
	} else {
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_challenges SET status=2 WHERE challengeid=" . $vbulletin->GPC['challengeid']);
		

		$otheruser = fetch_userinfo($challenge['fromuserid']);
		
		if ($otheruser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['challengedeclined'])
		{			
			if ($otheruser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['useemail'])
			{
				vbmail($otheruser['email'],
				construct_phrase($vbphrase['challenge_declined_t'], $otheruser['username'], $vbulletin->userinfo['username']),
				construct_phrase($vbphrase['challenge_declined_e'], $otheruser['username'], $vbulletin->userinfo['username'])
				);
			}

			if ($otheruser['arcadeoptions'] & $vbulletin->bf_misc_arcadeoptions['usepms'])
			{
				// Override a potentially full inbox.
				$senderpermissions['adminpermissions'] = 2;

				$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);
				$pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
				$pmdm->set('fromusername', $vbulletin->userinfo['username']);
				$pmdm->set('title', construct_phrase($vbphrase['challenge_declined_t'], $otheruser['username'], $vbulletin->userinfo['username']));
				$pmdm->set('message', construct_phrase($vbphrase['challenge_declined_e'], $otheruser['username'], $vbulletin->userinfo['username']));
				$pmdm->set_recipients($otheruser['username'], $senderpermissions);
				$pmdm->set('dateline', TIMENOW);

				$pmdm->save();
			}
		}
		
		$vbulletin->url = 'arcade.php';
		standard_redirect($vbphrase['challenge_declined']);
	}
}

($hook = vBulletinHook::fetch_hook('arcade_global_complete')) ? eval($hook) : false;

?>