<?php
/*======================================================================*\
|| #################################################################### ||
|| # The Arcade	for vBulletin 3.5									  # ||
|| # Development header												  # ||
|| #################################################################### ||
\*======================================================================*/

function getmicrotime() {
	list($usec, $sec) = explode(" ",microtime());
	$accutime = $usec + $sec;
	return sprintf('%.4f', $accutime);
}

function ordinal($number) {

	if ($number % 100 > 10 && $number %100 < 14)
	{
		$suffix = "th";
	} else {
		switch($number % 10) {

			case 0:
			$suffix = "th";
			break;

			case 1:
			$suffix = "st";
			break;

			case 2:
			$suffix = "nd";
			break;

			case 3:
			$suffix = "rd";
			break;

			default:
			$suffix = "th";
			break;
		}
	}

	return "<SUP>$suffix</SUP>";
}

function sec2hms ($sec)
{
	global $vbphrase;
	$hms = '';

	$hours = intval(intval($sec) / 3600);
	$hms .= $hours. ' ' . $vbphrase['hours'] . ', ';

	$minutes = intval(($sec / 60) % 60);

	$hms .= $minutes . ' ' . $vbphrase['minutes'] . ', ';

	$seconds = intval($sec % 60);

	$hms .= $seconds . ' ' . $vbphrase['seconds'];

	return $hms;
}

function build_games($rebuild=0)
{
	global $db, $vbulletin;
	
	$gamecache = array();
	
	// Cache games.
	$games = $db->query("SELECT * FROM " . TABLE_PREFIX . "arcade_games");
	while ($game = $db->fetch_array($games))
	{
		$gamecache[$game['gameid']] = $game;
	}
	
	$sessioncounts = $db->query_read("SELECT arcade_sessions.gameid, COUNT(*) AS sessioncount, arcade_games.isreverse FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions
	LEFT JOIN " . TABLE_PREFIX . "arcade_games AS arcade_games ON (arcade_games.gameid=arcade_sessions.gameid)
	WHERE arcade_sessions.valid=1
	GROUP BY arcade_sessions.gameid");
	while ($gamesession = $db->fetch_array($sessioncounts))
	{
		if (($gamesession['sessioncount']!=$gamecache[$gamesession['gameid']]['sessioncount']) OR ($rebuild==1))
		{
			$order = iif($gamesession['isreverse']==0, 'DESC', 'ASC');
			// Sessioncount mismatch, recalculate high scorer.
			if ($champcheck = $db->query_first("SELECT userid, score FROM " . TABLE_PREFIX . "arcade_sessions AS arcade_sessions WHERE valid=1 AND gameid=$gamesession[gameid] ORDER BY score $order, finish DESC LIMIT 1"))
			{
				$newchampsql = ", highscorerid=$champcheck[userid], highscore='$champcheck[score]'";
			}
			
			$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET sessioncount=" . $gamesession['sessioncount'] . "$newchampsql WHERE gameid=" . $gamesession['gameid']);
		}
		
		// Unsets the game key, this *should* leave an empty $gamecache array...
		unset($gamecache[$gamesession['gameid']]);
	}

	// ... but it might not. So, time for some more updating.
	foreach ((array)$gamecache as $id => $arr)
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET sessioncount=0, highscorerid=0, highscore=0 WHERE gameid=" . $id);
	}
}

function build_ratings($gameid)
{
	global $db, $vbulletin;
	
	if ($ratingcount = $db->query_first("SELECT SUM(rating) AS votepoints, COUNT(*) AS votecount FROM " . TABLE_PREFIX . "arcade_ratings WHERE gameid=$gameid"))
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET votepoints=" . $ratingcount['votepoints'] . ", votecount=" . $ratingcount['votecount'] . " WHERE gameid=$gameid");
	}
}

function build_favcache()
{
	global $db, $vbulletin;
	
	$favcache = array();
	
	$fcq = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_favorites WHERE userid=" . $vbulletin->userinfo['userid']);
	while ($fc = $db->fetch_array($fcq))
	{
		$favcache[$fc['gameid']] = $fc['gameid'];
	}
	
	$favcache_o = serialize($favcache);
	$db->query_write("UPDATE " . TABLE_PREFIX . "user SET favcache='" . addslashes($favcache_o) . "' WHERE userid=" . $vbulletin->userinfo['userid']);

}

function findcatpage()
{
	global $vbulletin;
	
	$categoryid = intval(fetch_bbarray_cookie('arcade_viewdata', 'categoryid'));
	$pagenumber = intval(fetch_bbarray_cookie('arcade_viewdata', 'pagenumber'));
	
	if ($categoryid && $pagenumber)
	{
		return "categoryid=$categoryid&pagenumber=$pagenumber";
	} else {
		return '';
	}
}


?>