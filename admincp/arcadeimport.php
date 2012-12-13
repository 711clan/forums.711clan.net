<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cpuser', 'arcade');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminforums'))
{
	print_cp_no_permission();
}

// ########################### IMPORT FUNCTIONS ############################
function print_next_page($delay = 1)
{
	global $vbulletin, $vbphrase;

	$vbulletin->GPC['startat'] = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	print_cp_redirect("arcadeimport.php?do=30ximport&perpage={$vbulletin->GPC['perpage']}&step={$vbulletin->GPC['step']}&startat={$vbulletin->GPC['startat']}#end", $delay);

	?>
	<form action="arcadeimport.php" method="get">
	<input type="hidden" name="step" value="<?php echo $vbulletin->GPC['step']; ?>" />
	<input type="hidden" name="do" value="30ximport" />
	<input type="hidden" name="startat" value="<?php echo $vbulletin->GPC['startat']; ?>" />
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="navbody" style="padding:4px; border:outset 2px;">
	<tr align="center">
		<td><b>Batch Complete</b><br />vBulletin &copy;2000 - <?php echo date('Y'); ?>, Jelsoft Enterprises Ltd.</td>
		<td><input type="submit" class="button" accesskey="s" value="<?php echo $vbphrase['next']; ?>" /></td>
	</tr>
	</table>
	</form>
	<?php

}


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// v3 ARCADE UPGRADE FROM 3.0.X
// Import games and scores
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if (!$_REQUEST['do'])
{
	print_cp_header($vbphrase['import_tools']);

	print_form_header('arcadeimport', '30ximport');
	print_table_header($vbphrase['v3_30ximport']);

	print_description_row($vbphrase['v3_30ximport_description']);
	construct_hidden_code('step', 1);

	print_hidden_fields();
	print_submit_row($vbphrase['start'], '');

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// v3 ARCADE UPGRADE FROM 3.0.X
// Import games and scores
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == '30ximport')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'step' => TYPE_UNIT,
	'perpage' => TYPE_UINT,
	'startat' => TYPE_UINT
	));

	print_cp_header($vbphrase['v3_30ximport']);

	// STEP 1
	// Clear our the 3.5 arcade tables.
	if ($vbulletin->GPC['step']==1)
	{
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_games");
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_sessions");
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_ratings");

		print_form_header('arcadeimport', '30ximport');
		print_table_header($vbphrase['deleting_35_data']);

		print_description_row($vbphrase['deleting_35_data_description']);
		construct_hidden_code('step', 2);
		construct_hidden_code('perpage', 100);
		construct_hidden_code('startat', 0);

		print_hidden_fields();
		print_submit_row($vbphrase['next_games'], '');
	}

	// STEP 2
	// Import games.
	if ($vbulletin->GPC['step']==2)
	{
		$games = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "games");

		if ($vbulletin->GPC['startat']<$games['count'])
		{
			print_dots_start($vbphrase['importing_games']);
			$games = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "games LIMIT {$vbulletin->GPC['startat']}, {$vbulletin->GPC['perpage']}");
			while ($game = $db->fetch_array($games))
			{
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_games (gameid, shortname, title, description, file, width, height, stdimage, miniimage, gamepermissions, categoryid, dateadded) VALUES
				('" . $game['gameid'] . "', '" . addslashes($game['shortname']) . "', '" . addslashes($game['title']) . "', '" . addslashes($game['descr']) . "', '" . addslashes($game['file']) . "', '" . $game['width'] . "', '" . $game['height'] . "', '" . addslashes($game['stdimage']) . "', '" . addslashes($game['miniimage']) . "', 7, 1, " . TIMENOW . ")
				");
			}
			print_dots_stop();
			print_next_page();
		} else {

			print_form_header('arcadeimport', '30ximport');
			print_table_header($vbphrase['importing_games']);

			print_description_row($vbphrase['importing_games_description']);
			construct_hidden_code('step', 3);
			construct_hidden_code('perpage', 1000);
			construct_hidden_code('startat', 0);

			print_hidden_fields();
			print_submit_row($vbphrase['next_sessions'], '');
		}
	}

	// STEP 3
	// Import sessions.
	if ($vbulletin->GPC['step']==3)
	{
		$games = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "gamesessions");

		if ($vbulletin->GPC['startat']<$games['count'])
		{
			print_dots_start($vbphrase['importing_sessions']);
			$games = $db->query_read("SELECT gamesessions.*, games.gameid FROM " . TABLE_PREFIX . "gamesessions AS gamesessions
			LEFT JOIN " . TABLE_PREFIX . "games AS games ON (gamesessions.gamename=games.shortname)
			LIMIT {$vbulletin->GPC['startat']}, {$vbulletin->GPC['perpage']}");
			while ($game = $db->fetch_array($games))
			{
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_sessions (gameid, gamename, sessionid, userid, start, finish, ping, comment, valid, score) VALUES
				('" . $game['gameid'] . "', '" . addslashes($game['gamename']) . "', '" . $game['sessionid'] . "', '" . $game['userid'] . "', '" . $game['start'] . "', '" . $game['finish'] . "', '" . $game['ping'] . "', '" . addslashes($game['comment']) . "', '" . $game['valid'] . "', '" . $game['score'] . "')");
			}
			print_dots_stop();
			print_next_page();
		} else {

			print_form_header('arcadeimport', '30ximport');
			print_table_header($vbphrase['importing_sessions']);

			print_description_row($vbphrase['importing_sessions_description']);
			construct_hidden_code('step', 4);

			print_hidden_fields();
			print_submit_row($vbphrase['next_rebuild'], '');
		}
	}

	// STEP 4
	// Rebuilding game data.
	if ($vbulletin->GPC['step']==4)
	{
		require_once(DIR . '/includes/functions_arcade.php');
		build_games();
		
		print_form_header('arcadeimport', '30ximport');
		print_table_header($vbphrase['data_rebuild_done']);

		print_description_row($vbphrase['data_rebuild_done_description']);

		print_table_footer();
	}

	print_cp_footer();
}
print_dots_start();
?>