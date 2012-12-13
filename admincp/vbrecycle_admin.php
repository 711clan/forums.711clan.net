<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBRecycle 3.0.x								    # ||
|| # ---------------------------------------------------------------- # ||
|| # Author: LNTT - Email: toai007@yahoo.com    		          # ||
|| # Website & Demo: http://www.FanFunVN.com/   		          # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'vbrecycle_admin');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('thread', 'threadmanage', 'forum', 'cpuser', 'logging', 'cron', 'language');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_vbrecycle.php');
require_once(DIR . '/includes/functions_databuild.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminlanguages'))
{
	print_cp_no_permission();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['vbr_coph']);

// ########################################################################
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'ms';

}

// ########################################################################
if ($_REQUEST['do'] == 'ms')
{
	$vbrconfig = $db->query_first("select * from " . TABLE_PREFIX . "vbr_config");

	print_form_header('vbrecycle_admin', 'doconfig');
	print_table_header($vbphrase['vbr_ms']);

	print_yes_no_row($vbphrase['vbr_ar'], 'active', $vbrconfig['active']);
	print_forum_chooser($vbphrase['vbr_fr'], 'forumid', $vbrconfig['forumid']);

	//print_select_row($vbphrase['vbr_lamdes'], 'linkam', array(1 => 'Return Forum Recycle', 2 => 'Return Previous Forum'), $vbrconfig['linkam']);
	print_select_row($vbphrase['vbr_tm'], 'typem', array(1 => Move, 2 => Movered, 3 => Copy), $vbrconfig['typem']);
	print_submit_row();
}

// ########################################################################
if ($_REQUEST['do'] == 'uninstall')
{
	print_form_header('vbrecycle_admin', 'dodelp');
	print_table_header($vbphrase['vbr_uninstall']);

	print_description_row($vbphrase['vbr_unstep1']);
	print_submit_row($vbphrase['vbr_rp'], 0);
}

// ########################################################################
if ($_REQUEST['do'] == 'dodelp')
{
	print_dots_start('<b>' . $vbphrase[vbr_dorp] . "</b>, $vbphrase[please_wait]", ':', 'dspan');

	if ($check1 = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "phrase WHERE varname LIKE '%recycle%'"))
	{
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname LIKE '%recycle%'");
	}
	print_dots_stop();

	print_form_header('vbrecycle_admin', 'dodelta');
	print_table_header($vbphrase['vbr_uninstall']);
	print_description_row($vbphrase['vbr_step2']);
	print_submit_row($vbphrase['vbr_deltable'], 0);
}

// ########################################################################
if ($_REQUEST['do'] == 'dodelta')
{
	print_dots_start('<b>' . $vbphrase[vbr_dodt] . "</b>, $vbphrase[please_wait]", ':', 'dspan');

	$db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "vbr_config");

	print_dots_stop();

	print_form_header('vbrecycle_admin', 'dodelpl');
	print_table_header($vbphrase['vbr_uninstall']);
	print_description_row($vbphrase['vbr_stepdelt']);
	print_submit_row($vbphrase['vbr_delp'], 0);
}


// ########################################################################
if ($_REQUEST['do'] == 'dodelpl')
{
	print_dots_start('<b>' . $vbphrase[vbr_dodelp] . "</b>, $vbphrase[please_wait]", ':', 'dspan');

	if ($check1 = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "plugin WHERE title LIKE '%recycle%'"))
	{
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "plugin WHERE title LIKE '%recycle%'");
	}
	print_dots_stop();

	print_form_header('vbrecycle_admin', 'doacp');
	print_table_header($vbphrase['vbr_uninstall']);
	print_description_row($vbphrase['vbr_step3']);
	print_submit_row($vbphrase['vbr_step4'], 0);
}


// ########################################################################
if ($_POST['do'] == 'doacp') 
{
	define('CP_REDIRECT', "index.php?");
	print_stop_message("redirecting_please_wait");
}

// ########################################################################
if ($_POST['do'] == "doconfig") 
{
	$vbrconfig=$db->query_first("select * from " . TABLE_PREFIX . "vbr_config");


	if ($_POST['active'] != $vbrconfig['active']) 
	{
		$newstate = $_POST['active'];
	
		// Do the product table
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "product
			SET active = $newstate
			WHERE productid = '" . $db->escape_string('_vbrecycle') . "'
		");

		vBulletinHook::build_datastore($db);
	}	

		// Update Database
		$db->query("update " . TABLE_PREFIX . "vbr_config set active='{$_POST['active']}', forumid='{$_POST['forumid']}', typem='{$_POST['typem']}' where oid='1'");
		$db->query("update " . TABLE_PREFIX . "forum set recycle = 1 where forumid='{$_POST['forumid']}'");

		define('CP_REDIRECT', "vbrecycle_admin.php?");
		print_stop_message("saved_settings_successfully");
}

// ########################################################################
if ($_REQUEST['do'] == 'linkam')
{
	$vbrconfig=$db->query_first("select * from " . TABLE_PREFIX . "vbr_config");

	print_form_header('vbrecycle_admin', 'dolinkam');
	print_table_header($vbphrase['vbr_lam']);
	print_select_row($vbphrase['vbr_lamst'], 'linkamst', array(1 => 'Return Forum Recycle', 2 => 'Return Previous Forum'), $vbrconfig['linkamst']);
	print_select_row($vbphrase['vbr_lamfd'], 'linkamfd', array(1 => 'Return Forum Recycle', 2 => 'Return Previous Forum'), $vbrconfig['linkamfd']);

	print_submit_row();

}

// ########################################################################
if ($_POST['do'] == "dolinkam") 
{

	// Update Database
	$db->query("update " . TABLE_PREFIX . "vbr_config set linkamfd='{$_POST['linkamfd']}', linkamst='{$_POST['linkamst']}' where oid='1'");

	define('CP_REDIRECT', "vbrecycle_admin.php?do=linkam");
	print_stop_message("saved_settings_successfully");
}


// ########################################################################
if ($_REQUEST['do'] == 'manager')
{

	print_form_header('vbrecycle_admin', 'create');
	print_table_header($vbphrase['vbr_crf'], 4);

	$vbrconfig=$db->query_first("select * from " . TABLE_PREFIX . "vbr_config");
	
	$forums = $db->query("select * from " . TABLE_PREFIX . "forum where recycle = 1");
	print_cells_row(array($vbphrase['vbr_rb'], $vbphrase['vbr_se'], $vbphrase['delete'], $vbphrase['default']), 1);


	while ($forum = $vbulletin->db->fetch_array($forums))
	{
		$cell = array();

		$cell[] .= "<a href=\"vbrecycle_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=editrecycle&amp;f=" . $forum['forumid'] . "\"><b>" . $forum[title] . "</b></a>";

		$cell[] .= "<a href=\"vbrecycle_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=editrecycle&amp;f=" . $forum['forumid'] . "\">" . $vbphrase['edit_settings'] . "</a>" . iif($vbrconfig[forumid] != $forum[forumid], '<br><a href="vbrecycle_admin.php?do=setforum&amp;f=' . $forum['forumid'] . '">' . $vbphrase['vbr_spf'] . '</a>');

		$cell[] .= "<a href=\"vbrecycle_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=deleterecycle&amp;f=" . $forum['forumid'] . "\">" . $vbphrase['delete']. "</a>";

		$cell[] .= "<input type=\"button\" class=\"button\" value=\"$vbphrase[set_default]\" tabindex=\"1\"" . iif($vbrconfig[forumid] == $forum[forumid], ' disabled="disabled"') . " onclick=\"window.location='vbrecycle_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=setdefault&amp;f=" . $forum['forumid'] . "';\" />";

		print_cells_row($cell, 0, '', -2);
	}
	
	print_table_footer(4, '<input type="submit" class="button" value="' . $vbphrase['vbr_anr'] . '" tabindex="1" />');
}
// ##########################################################################
if ($_REQUEST['do'] == 'setforum')
{
	$vbulletin->input->clean_array_gpc('r', array('forumid' => TYPE_UINT));

	if ($vbulletin->GPC['forumid'] == 0)
	{
		print_stop_message('vbr_invalid');
	}

	$info = $db->query_first("Select * from " . TABLE_PREFIX . "forum where forumid =" . $vbulletin->GPC['forumid']);



	print_form_header('vbrecycle_admin', 'dosetforum');
	print_table_header($vbphrase['vbr_spf'], 4);
	print_description_row(construct_phrase($vbphrase['vbr_spfdes'], $info['title']), 0, 2, 'thead', 'center');
	$forums = $db->query_read("Select * from " . TABLE_PREFIX . "forum where recycle != 1 ORDER BY forumid");
	while ($forum= $db->fetch_array($forums))
	{
		$title = htmlspecialchars_uni($forum['title']);
		print_label_row("
			<label for=\"cb$forum[forumid]\">
			<input type=\"checkbox\" id=\"cb$forum[forumid]\" name=\"setforum[]\" value=\"$forum[forumid]\" />$title

		");
	}

		printf('<input type="hidden" name="mainforum" value="' . $info[forumid] . '" /></label>');


	print_submit_row();


}
// ##########################################################################
if ($_REQUEST['do'] == 'dosetforum')
{
	$vbulletin->input->clean_array_gpc('r', array('forumid' => TYPE_UINT));
	$vbulletin->input->clean_array_gpc('p', array('setforum'=> TYPE_ARRAY_UINT));
	$vbulletin->input->clean_array_gpc('p', array('mainforum' => TYPE_UINT));

	if (!$vbulletin->GPC['setforum'])
	{
		print_stop_message('vbr_invalid');
	}


	$forums = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "forum WHERE forumid IN (" . implode(', ', $vbulletin->GPC['setforum']) . ")");

	while ($forum = $db->fetch_array($forums))
	{
	$db->query("UPDATE " . TABLE_PREFIX . "forum SET srecycle = " . $vbulletin->GPC['mainforum'] . " WHERE forumid = " . $forum['forumid']);
	}
	define('CP_REDIRECT', 'vbrecycle_admin.php?do=manager');
	print_stop_message('saved_settings_successfully');
}

// ##########################################################################
if ($_REQUEST['do'] == 'setdefault')
{
	$vbulletin->input->clean_array_gpc('r', array('forumid' => TYPE_UINT));

	if ($vbulletin->GPC['forumid'] == 0)
	{
		print_stop_message('vbr_invalid');
	}

	$db->query_write("UPDATE " . TABLE_PREFIX . "vbr_config SET forumid = " . $vbulletin->GPC['forumid']);

	define('CP_REDIRECT', 'vbrecycle_admin.php?do=manager');
	print_stop_message('vbr_sds');
}


// ########################################################################
if ($_REQUEST['do'] == 'deleterecycle')
{
	$vbulletin->input->clean_array_gpc('r', array('forumid' => TYPE_UINT));

	$vbrconfig=$db->query_first("select * from " . TABLE_PREFIX . "vbr_config");

	if ($vbulletin->GPC['forumid'] == $vbrconfig['forumid'])
	{
		print_stop_message('vbr_ndd');
	}

	print_delete_confirmation('forum', $vbulletin->GPC['forumid'], 'vbrecycle_admin', 'dodeleterecycle', 'forum', 0, $vbphrase['are_you_sure_you_want_to_delete_this_forum']);
}
// ########################################################################
if ($_POST['do'] == 'dodeleterecycle')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'forumid' => TYPE_UINT
	));

	$forumdata =& datamanager_init('Forum', $vbulletin, ERRTYPE_CP);
	$forumdata->set_condition("FIND_IN_SET('" . $vbulletin->GPC['forumid'] . "', parentlist)");
	$forumdata->delete();

	define('CP_REDIRECT', 'vbrecycle_admin.php?do=manager');
	print_stop_message('deleted_forum_successfully');
}


// ########################################################################
if ($_REQUEST['do'] == 'editrecycle')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'forumid'			=> TYPE_UINT,
		'defaultforumid'	=> TYPE_UINT,
		'parentid'			=> TYPE_UINT
	));


	if (!empty($vbulletin->GPC['defaultforumid']))
	{
		$newforum = fetch_foruminfo($vbulletin->GPC['defaultforumid']);
		foreach (array_keys($forum) AS $title)
		{
			$forum["$title"] = $newforum["$title"];
		}
	}

	if (!($forum = fetch_foruminfo($vbulletin->GPC['forumid'])))
	{
		print_stop_message('invalid_forum_specified');
	}

	print_form_header('forum', 'update');
	print_table_header(construct_phrase($vbphrase['x_y_id_z'], $vbphrase['forum'], $forum['title'], $forum['forumid']));
	construct_hidden_code('forumid', $vbulletin->GPC['forumid']);
	$forum['title'] = str_replace('&amp;', '&', $forum['title']);
	$forum['description'] = str_replace('&amp;', '&', $forum['description']);
	

	print_input_row($vbphrase['title'], 'forum[title]', $forum['title']);
	print_textarea_row($vbphrase['description'], 'forum[description]', $forum['description']);
	print_input_row($vbphrase['forum_link'], 'forum[link]', $forum['link']);
	print_input_row("$vbphrase[display_order]<dfn>$vbphrase[zero_equals_no_display]</dfn>", 'forum[displayorder]', $forum['displayorder']);
	//print_input_row($vbphrase['default_view_age'], 'forum[daysprune]', $forum['daysprune']);

	// make array for daysprune menu
	$pruneoptions = array(
		'1' => $vbphrase['show_threads_from_last_day'],
		'2' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 2),
		'7' => $vbphrase['show_threads_from_last_week'],
		'10' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 10),
		'14' => construct_phrase($vbphrase['show_threads_from_last_x_weeks'], 2),
		'30' => $vbphrase['show_threads_from_last_month'],
		'45' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 45),
		'60' => construct_phrase($vbphrase['show_threads_from_last_x_months'], 2),
		'75' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 75),
		'100' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 100),
		'365' => $vbphrase['show_threads_from_last_year'],
		'-1' => $vbphrase['show_all_threads']
	);

	print_select_row($vbphrase['default_view_age'], 'forum[daysprune]', $pruneoptions, $forum['daysprune']);



	if ($vbulletin->GPC['forumid'] != -1)
	{
		print_forum_chooser($vbphrase['parent_forum'], 'forum[parentid]', $forum['parentid'], $vbphrase['no_one']);
	}
	else
	{
		construct_hidden_code('parentid', 0);
	}
	print_yes_no_row($vbphrase['act_as_forum'], 'forum[options][cancontainthreads]', $forum['cancontainthreads']);

	print_yes_no_row($vbphrase['forum_is_active'], 'forum[options][active]', $forum['active']);


	print_table_header($vbphrase['moderation_options']);

	print_input_row($vbphrase['emails_to_notify_when_post'], 'forum[newpostemail]', $forum['newpostemail']);
	print_input_row($vbphrase['emails_to_notify_when_thread'], 'forum[newthreademail]', $forum['newthreademail']);

	print_yes_no_row($vbphrase['moderate_posts'] . ' <dfn>(' . $vbphrase['require_moderator_validation_before_new_posts_are_displayed'] . ')</dfn>', 'forum[options][moderatenewpost]', $forum['moderatenewpost']);
	print_yes_no_row($vbphrase['moderate_threads'] . ' <dfn>(' . $vbphrase['require_moderator_validation_before_new_threads_are_displayed'] . ')</dfn>', 'forum[options][moderatenewthread]', $forum['moderatenewthread']);
	print_yes_no_row($vbphrase['moderate_attachments'] . ' <dfn>(' . $vbphrase['require_moderator_validation_before_new_attachments_are_displayed'] . ')</dfn>', 'forum[options][moderateattach]', $forum['moderateattach']);
	print_yes_no_row($vbphrase['warn_administrators'], 'forum[options][warnall]', $forum['warnall']);

	print_table_header($vbphrase['style_options']);

	if ($forum['styleid'] == 0)
	{
		$forum['styleid'] = -1; // to get the "use default style" option selected
	}
	print_style_chooser_row('forum[styleid]', $forum['styleid'], $vbphrase['use_default_style'], $vbphrase['custom_forum_style'], 1);
	print_yes_no_row($vbphrase['override_style_choice'], 'forum[options][styleoverride]', $forum['styleoverride']);

	print_table_header($vbphrase['access_options']);

	print_input_row($vbphrase['forum_password'], 'forum[password]', $forum['password']);

	print_yes_no_row($vbphrase['apply_password_to_children'], 'applypwdtochild', iif($forum['password'], 0, 1));
	
	print_yes_no_row($vbphrase['can_have_password'], 'forum[options][canhavepassword]', $forum['canhavepassword']);

	print_submit_row($vbphrase['save']);
}

// ########################################################################
if ($_REQUEST['do'] == 'create')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'forumid'			=> TYPE_UINT,
		'defaultforumid'	=> TYPE_UINT,
		'parentid'			=> TYPE_UINT
	));

	// Set Defaults;
	$forum = array(
		'displayorder' => 1,
		'daysprune' => 30,
		'parentid' => $vbulletin->GPC['parentid'],
		'styleid' => '',
		'styleoverride' => 0,
		'cancontainthreads' => 1,
		'active' => 1,
		'allowposting' => 0,
		'allowbbcode' => 1,
		'allowsmilies' => 1,
		'allowicons' => 1,
		'allowimages' => 1,
		'allowratings' => 1,
		'countposts' => 1,
		'indexposts' => 0,
		'showonforumjump' => 0,
		'warnall' => 0,
		'moderateattach' => 0,
		'moderatenewthread' => 0,
		'moderatenewpost' => 0,
		'newthreademail' => '',
		'newpostemail' => ''
	);

	if (!empty($vbulletin->GPC['defaultforumid']))
	{
		$newforum = fetch_foruminfo($vbulletin->GPC['defaultforumid']);
		foreach (array_keys($forum) AS $title)
		{
			$forum["$title"] = $newforum["$title"];
		}
	}

	print_form_header('vbrecycle_admin', 'docreate');
	print_table_header($vbphrase['vbr_acf']);

	if (!$forum['title']) $forum['title'] = 'Recycle Bin';

	print_input_row($vbphrase['title'], 'forum[title]', $forum['title']);
	print_textarea_row($vbphrase['description'], 'forum[description]', $forum['description']);
	print_input_row("$vbphrase[display_order]<dfn>$vbphrase[zero_equals_no_display]</dfn>", 'forum[displayorder]', $forum['displayorder']);
	//print_input_row($vbphrase['default_view_age'], 'forum[daysprune]', $forum['daysprune']);

	// make array for daysprune menu
	$pruneoptions = array(
		'1' => $vbphrase['show_threads_from_last_day'],
		'2' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 2),
		'7' => $vbphrase['show_threads_from_last_week'],
		'10' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 10),
		'14' => construct_phrase($vbphrase['show_threads_from_last_x_weeks'], 2),
		'30' => $vbphrase['show_threads_from_last_month'],
		'45' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 45),
		'60' => construct_phrase($vbphrase['show_threads_from_last_x_months'], 2),
		'75' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 75),
		'100' => construct_phrase($vbphrase['show_threads_from_last_x_days'], 100),
		'365' => $vbphrase['show_threads_from_last_year'],
		'-1' => $vbphrase['show_all_threads']
	);

	print_select_row($vbphrase['default_view_age'], 'forum[daysprune]', $pruneoptions, $forum['daysprune']);

	if ($vbulletin->GPC['forumid'] != -1)
	{
		print_forum_chooser($vbphrase['parent_forum'], 'forum[parentid]', $forum['parentid'], $vbphrase['no_one']);
	}
	else
	{
		construct_hidden_code('parentid', 0);
	}

	print_yes_no_row($vbphrase['act_as_forum'], 'forum[options][cancontainthreads]', $forum['cancontainthreads']);


	print_yes_no_row($vbphrase['forum_is_active'], 'forum[options][active]', $forum['active']);

	print_yes_no_row($vbphrase['vbr_sdfr'], 'setforumrecycle', 1);

	print_table_header($vbphrase['moderation_options']);

	print_input_row($vbphrase['emails_to_notify_when_post'], 'forum[newpostemail]', $forum['newpostemail']);
	print_input_row($vbphrase['emails_to_notify_when_thread'], 'forum[newthreademail]', $forum['newthreademail']);

	print_yes_no_row($vbphrase['moderate_posts'] . ' <dfn>(' . $vbphrase['require_moderator_validation_before_new_posts_are_displayed'] . ')</dfn>', 'forum[options][moderatenewpost]', $forum['moderatenewpost']);
	print_yes_no_row($vbphrase['moderate_threads'] . ' <dfn>(' . $vbphrase['require_moderator_validation_before_new_threads_are_displayed'] . ')</dfn>', 'forum[options][moderatenewthread]', $forum['moderatenewthread']);
	print_yes_no_row($vbphrase['moderate_attachments'] . ' <dfn>(' . $vbphrase['require_moderator_validation_before_new_attachments_are_displayed'] . ')</dfn>', 'forum[options][moderateattach]', $forum['moderateattach']);
	print_yes_no_row($vbphrase['warn_administrators'], 'forum[options][warnall]', $forum['warnall']);

	print_table_header($vbphrase['style_options']);

	if ($forum['styleid'] == 0)
	{
		$forum['styleid'] = -1; // to get the "use default style" option selected
	}
	print_style_chooser_row('forum[styleid]', $forum['styleid'], $vbphrase['use_default_style'], $vbphrase['custom_forum_style'], 1);
	print_yes_no_row($vbphrase['override_style_choice'], 'forum[options][styleoverride]', $forum['styleoverride']);

	print_table_header($vbphrase['access_options']);

	print_input_row($vbphrase['forum_password'], 'forum[password]', $forum['password']);

	print_yes_no_row($vbphrase['can_have_password'], 'forum[options][canhavepassword]', $forum['canhavepassword']);

	print_submit_row($vbphrase['save']);
}

// ########################################################################
if ($_POST['do'] == 'docreate')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'forumid'			=> TYPE_UINT,
		'applypwdtochild'	=> TYPE_BOOL,
		'forum'				=> TYPE_ARRAY
	));


	$forumdata =& datamanager_init('Forum', $vbulletin, ERRTYPE_CP);
	if ($vbulletin->GPC['forumid'])
	{
		$forumdata->set_existing($vbulletin->forumcache[$vbulletin->GPC['forumid']]);
		$forumdata->set_info('applypwdtochild', $vbulletin->GPC['applypwdtochild']);
	}
	foreach ($vbulletin->GPC['forum'] AS $varname => $value)
	{
		if ($varname == 'options')
		{
			foreach ($value AS $key => $val)
			{
				$forumdata->set_bitfield('options', $key, $val);
			}
		}
		else
		{
			$forumdata->set($varname, $value);
		}
	}

	$forumid = $forumdata->save();
	if (!$vbulletin->GPC['forumid'])
	{
		$vbulletin->GPC['forumid'] = $forumid;
	}

	if($_POST['setforumrecycle']==1)
	{
		// Update Database
		$db->query("update " . TABLE_PREFIX . "vbr_config set forumid='{$forumid}' where oid='1'");
		$db->query("update " . TABLE_PREFIX . "forum set recycle='1' where forumid='{$forumid}'");
	}

	define('CP_REDIRECT', "vbrecycle_admin.php?do=manager");
	print_stop_message('saved_forum_x_successfully', $vbulletin->GPC['forum']['title']);
}

// ########################################################################
if ($_REQUEST['do'] == 'cron')
{
	$checkcron = $db->query_first("select * from " . TABLE_PREFIX . "cron WHERE varname = 'vbrecycle_clean' AND filename = './includes/cron/vbrecycle_clean.php'");

	if (!$checkcron)
	{
		print_form_header('vbrecycle_admin', 'addcron');
		print_table_header($vbphrase['vbr_scr']);

		print_description_row($vbphrase['vbr_cst']);
		print_submit_row($vbphrase['vbr_anst'], 0);
	} else {

		$vbulletin->input->clean_array_gpc('r', array(
			'cronid' => TYPE_INT
		));

		print_form_header('vbrecycle_admin', 'doeditcron');

		$vbulletin->GPC['cronid'] = $checkcron['cronid'];


		$cron = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "cron WHERE cronid = " . intval($vbulletin->GPC['cronid']));
		if (is_numeric($cron['minute']))
		{
			$cron['minute'] = array(0 => $cron['minute']);
		}
		else
		{
			$cron['minute'] = unserialize($cron['minute']);
		}
		print_table_header($vbphrase['vbr_enst']);
		construct_hidden_code('cronid' , $cron['cronid']);


		$weekdays = array(-1 => '*', 0 => $vbphrase['sunday'], $vbphrase['monday'], $vbphrase['tuesday'], $vbphrase['wednesday'], $vbphrase['thursday'], $vbphrase['friday'], $vbphrase['saturday']);
		$hours = array(-1 => '*', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);
		$days = array(-1 => '*', 1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31);
		$minutes = array(-1 => '*');
		for ($x = 0; $x < 60; $x++)
		{
			$minutes[] = $x;
		}

		print_select_row($vbphrase['day_of_week'], 'weekday', $weekdays, $cron['weekday']);
		print_select_row($vbphrase['day_of_month'], 'day', $days, $cron['day']);
		print_select_row($vbphrase['hour'], 'hour', $hours, $cron['hour']);
	
		$selects = '';
		for($x = 0; $x < 4; $x++)
		{
			if ($x == 1)
			{
				$minutes = array(-2 => '-') + $minutes;
				unset($minutes[-1]);
			}
			if (!isset($cron['minute'][$x]))
			{
				$cron['minute'][$x] = -2;
			}
			$selects .= "<select name=\"minute[$x]\" tabindex=\"1\" class=\"bginput\">\n";
			$selects .= construct_select_options($minutes, $cron['minute'][$x]);
			$selects .= "</select>\n";
		}
		print_label_row($vbphrase['minute'], $selects, '', 'top', 'minute');
		print_yes_no_row($vbphrase['log_entries'], 'loglevel', $cron['loglevel']);
		print_yes_no_row($vbphrase['vbr_dnst'], 'deletecron', 0);
		print_submit_row($vbphrase['save']);
	}
}

// ########################################################################
if ($_POST['do'] == 'doeditcron')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'filename' 	=> TYPE_STR,
		'title' 	=> TYPE_STR,
		'weekday' 	=> TYPE_STR,
		'day' 		=> TYPE_STR,
		'hour' 		=> TYPE_STR,
		'minute' 	=> TYPE_ARRAY,
		'cronid' 	=> TYPE_INT,
		'filename' 	=> TYPE_STR,
		'loglevel' 	=> TYPE_INT
	));

	$checkcrondelete = $db->query_first("select * from " . TABLE_PREFIX . "cron WHERE varname = 'vbrecycle_clean' AND filename = './includes/cron/vbrecycle_clean.php'");

	if ($_POST['deletecron']==1)
	{
		$escaped_varname = $db->escape_string($checkcrondelete['varname']);
		$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "phrase
			WHERE fieldname = 'cron' AND
				varname IN ('task_{$escaped_varname}_title', 'task_{$escaped_varname}_desc', 'task_{$escaped_varname}_log')
		");
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "cron WHERE cronid = " . $checkcrondelete['cronid']);
		
		require_once(DIR . '/includes/adminfunctions_language.php');
		build_language();
		
		define('CP_REDIRECT', 'vbrecycle_admin.php?do=cron');
		print_stop_message('deleted_scheduled_task_successfully');
	} else {
		$vbulletin->GPC['varname'] = 'vbrecycle_clean';
		$vbulletin->GPC['filename'] = './includes/cron/vbrecycle_clean.php';
		if ($vbulletin->GPC['filename'] == '' OR $vbulletin->GPC['filename'] == './includes/cron/.php')
		{
			print_stop_message('invalid_filename_specified');
		}
		if ($vbulletin->GPC['varname'] == '')
		{
			print_stop_message('invalid_title_specified');
		}

		$vbulletin->GPC['weekday'] 	= str_replace('*', '-1', $vbulletin->GPC['weekday']);
		$vbulletin->GPC['day']		= str_replace('*', '-1', $vbulletin->GPC['day']);
		$vbulletin->GPC['hour']		= str_replace('*', '-1', $vbulletin->GPC['hour']);

		sort($vbulletin->GPC['minute'], SORT_NUMERIC);
		$newminute = array();

	
		foreach ($vbulletin->GPC['minute'] AS $time)
		{
			$newminute["$time"] = true;
		}

		unset($newminute["-2"]);
		if ($newminute["-1"])
		{ 
			$newminute = array(0 => -1);
		}
		else
		{ 
			$newminute = array_keys($newminute);
		}


		// update
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "cron
			SET varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "',
			loglevel = " . intval($vbulletin->GPC['loglevel']) . ",
			weekday = " . intval($vbulletin->GPC['weekday']) . ",
			day = " . intval($vbulletin->GPC['day']) . ",
			hour = " . intval($vbulletin->GPC['hour']) . ",
			minute = '" . $db->escape_string(serialize($newminute)) . "',
			filename = '" . $db->escape_string($vbulletin->GPC['filename']) . "'
			WHERE cronid = " . intval($vbulletin->GPC['cronid'])
		);

		require_once(DIR . '/includes/functions_cron.php');
		build_cron_item($vbulletin->GPC['cronid']);
		build_cron_next_run();


		define('CP_REDIRECT', 'vbrecycle_admin.php?do=cron');
		print_stop_message('saved_scheduled_task_x_successfully', $vbulletin->GPC['varname']);
	}
}

// ########################################################################
if ($_REQUEST['do'] == 'addcron')
{
	print_form_header('vbrecycle_admin', 'doaddcron');
	print_table_header($vbphrase['vbr_anst']);
	print_description_row($vbphrase['vbr_anstdes'], 0, 2, 'thead', 'center');

	if (!empty($vbulletin->GPC['cronid']))
	{
		$cron = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "cron WHERE cronid = " . intval($vbulletin->GPC['cronid']));
		if (is_numeric($cron['minute']))
		{
			$cron['minute'] = array(0 => $cron['minute']);
		}
		else
		{
			$cron['minute'] = unserialize($cron['minute']);
		}
		construct_hidden_code('cronid' , $cron['cronid']);
	}
	else
	{
		$cron = array(
			'cronid' => '',
			'weekday' => 1,
			'day' => -1,
			'hour' => -1,
			'minute' => array (0 => -1),
			'filename' => './includes/cron/vbrecycle_clean.php',
			'loglevel' => 0
		);
	}

	$weekdays = array(-1 => '*', 0 => $vbphrase['sunday'], $vbphrase['monday'], $vbphrase['tuesday'], $vbphrase['wednesday'], $vbphrase['thursday'], $vbphrase['friday'], $vbphrase['saturday']);
	$hours = array(-1 => '*', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);
	$days = array(-1 => '*', 1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31);
	$minutes = array(-1 => '*');
	for ($x = 0; $x < 60; $x++)
	{
		$minutes[] = $x;
	}

	print_select_row($vbphrase['day_of_week'], 'weekday', $weekdays, $cron['weekday']);
	print_select_row($vbphrase['day_of_month'], 'day', $days, $cron['day']);
	print_select_row($vbphrase['hour'], 'hour', $hours, $cron['hour']);

	$selects = '';
	for($x = 0; $x < 4; $x++)
	{
		if ($x == 1)
		{
			$minutes = array(-2 => '-') + $minutes;
			unset($minutes[-1]);
		}
		if (!isset($cron['minute'][$x]))
		{
			$cron['minute'][$x] = -2;
		}
		$selects .= "<select name=\"minute[$x]\" tabindex=\"1\" class=\"bginput\">\n";
		$selects .= construct_select_options($minutes, $cron['minute'][$x]);
		$selects .= "</select>\n";
	}
	print_label_row($vbphrase['minute'], $selects, '', 'top', 'minute');
	print_yes_no_row($vbphrase['log_entries'], 'loglevel', $cron['loglevel']);

	print_submit_row($vbphrase['save']);
}


// ########################################################################
if ($_POST['do'] == 'doaddcron')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'filename' 	=> TYPE_STR,
		'title' 	=> TYPE_STR,
		'weekday' 	=> TYPE_STR,
		'day' 		=> TYPE_STR,
		'hour' 		=> TYPE_STR,
		'minute' 	=> TYPE_ARRAY,
		'cronid' 	=> TYPE_INT,
		'filename' 	=> TYPE_STR,
		'loglevel' 	=> TYPE_INT
	));

	$vbulletin->GPC['varname'] = 'vbrecycle_clean';
	$vbulletin->GPC['filename'] = './includes/cron/vbrecycle_clean.php';

	if ($vbulletin->GPC['filename'] == '' OR $vbulletin->GPC['filename'] == './includes/cron/.php')
	{
		print_stop_message('invalid_filename_specified');
	}
	if ($vbulletin->GPC['varname'] == '')
	{
		print_stop_message('invalid_title_specified');
	}

	$vbulletin->GPC['weekday'] 	= str_replace('*', '-1', $vbulletin->GPC['weekday']);
	$vbulletin->GPC['day']		= str_replace('*', '-1', $vbulletin->GPC['day']);
	$vbulletin->GPC['hour']		= str_replace('*', '-1', $vbulletin->GPC['hour']);

	// need to deal with minute properly :)
	sort($vbulletin->GPC['minute'], SORT_NUMERIC);
	$newminute = array();


	foreach ($vbulletin->GPC['minute'] AS $time)
	{
		$newminute["$time"] = true;
	}
	// removed duplicates now lets remove -2

	unset($newminute["-2"]);
	if ($newminute["-1"])
	{ // its run every minute so lets just ignore every other entry
		$newminute = array(0 => -1);
	}
	else
	{ // array keys please :)
		$newminute = array_keys($newminute);
	}

	if (empty($vbulletin->GPC['cronid']))
	{
		// add new
		/*insert query*/
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "cron
			(
				weekday,
				day,
				hour,
				minute,
				filename,
				loglevel,
				varname,
				volatile,
				product
			)
			VALUES
			(
				" . intval($vbulletin->GPC['weekday']) . " ,
				" . intval($vbulletin->GPC['day']) . " ,
				" . intval($vbulletin->GPC['hour']) . " ,
				'" . $db->escape_string(serialize($newminute)) . "' ,
				'" . $db->escape_string($vbulletin->GPC['filename']) . "',
				" . $vbulletin->GPC['loglevel'] . ",
				'" . $db->escape_string($vbulletin->GPC['varname']) . "',
				1,
				'"._vbrecycle."')
		");
		// build new title, desc
		/*insert_query*/
		$new_languageid = -1;
		$vbulletin->GPC['title']="vBRecycle Clean";
		$vbulletin->GPC['description']="Help you to clean recycle automatically.";
		$product_version="";
		$escaped_product=$db->escape_string($vbulletin->GPC['varname']);
		
		$vbulletin->GPC['cronid'] = $db->insert_id();
		
		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, fieldname, varname, text, product, username, dateline, version)
			VALUES
				(
					-1,
					'cron',
					'task_" . $vbulletin->GPC['varname'] . "_title',
					'" . $db->escape_string($vbulletin->GPC['title']) . "',
					'$escaped_product',
					'Computer_Angel',
					" . TIMENOW . ",
					'" . $db->escape_string($product_version) . "'
				),
				(
					-1,
					'cron',
					'task_" . $vbulletin->GPC['varname'] . "_desc',
					'" . $db->escape_string($vbulletin->GPC['description']) . "',
					'$escaped_product',
					'Computer_Angel',
					" . TIMENOW . ",
					'" . $db->escape_string($product_version) . "'
				),
				(
					-1,
					'cron',
					'task_" . $vbulletin->GPC['varname'] . "_log',
					'" . $db->escape_string($vbulletin->GPC['logphrase']) . "',
					'$escaped_product',
					'Computer_Angel',
					" . TIMENOW . ",
					'" . $db->escape_string($product_version) . "'
				)
		");
		require_once(DIR . '/includes/adminfunctions_language.php');
		build_language();
		
	}
	else
	{
		// update
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "cron
			SET varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "',
			loglevel = " . intval($vbulletin->GPC['loglevel']) . ",
			weekday = " . intval($vbulletin->GPC['weekday']) . ",
			day = " . intval($vbulletin->GPC['day']) . ",
			hour = " . intval($vbulletin->GPC['hour']) . ",
			minute = '" . $db->escape_string(serialize($newminute)) . "',
			filename = '" . $db->escape_string($vbulletin->GPC['filename']) . "'
			WHERE cronid = " . intval($vbulletin->GPC['cronid'])
		);
	}

	require_once(DIR . '/includes/functions_cron.php');
	build_cron_item($vbulletin->GPC['cronid']);
	build_cron_next_run();

	define('CP_REDIRECT', 'vbrecycle_admin.php?do=cron');
	print_stop_message('saved_scheduled_task_x_successfully', $vbulletin->GPC['varname']);
}


// ########################################################################
if ($_REQUEST['do'] == 'empty')
{
	?>
	<script type="text/javascript">
	<!--
	function js_confirm_empty(tform)
	{
			return confirm("<?php echo ($vbphrase['vbr_cer']); ?>");
		return true;
	}
	//-->
	</script>
	<?php

	print_form_header('vbrecycle_admin', 'doempty', 1, 1, 'confirm_empty" onsubmit="return js_confirm_empty(this);');
	print_table_header($vbphrase['vbr_er']);

	print_yes_no_row($vbphrase['vbr_cep'], 'confirmempty', 0);
	construct_hidden_code('type', 'prune');
	print_submit_row($vbphrase['vbr_er']);
}


// ########################################################################
if ($_POST['do'] == 'doempty')
{
	if ($_POST['confirmempty']!= '1') 
	{
		print_stop_message('vbr_notconfirm');
	}

	require_once(DIR . '/includes/functions_log_error.php');

	$vbulletin->input->clean_array_gpc('p', array(
		'type'        => TYPE_NOHTML,
		'criteria'    => TYPE_STR,
		'destforumid' => TYPE_INT,
	));


	$vbrconfig=$db->query_first("select * from " . TABLE_PREFIX . "vbr_config");
	
	$vbulletin->GPC['type'] = 'prune';
	$vbulletin->GPC['thread']['forumid'] = $vbrconfig[forumid];

	$thread = unserialize($vbulletin->GPC['criteria']);
	//$whereclause = fetch_thread_move_prune_sql($thread);

	$fullquery = "
		SELECT *
		FROM " . TABLE_PREFIX . "thread AS thread
		LEFT JOIN " . TABLE_PREFIX . "forum AS forum ON (forum.forumid = thread.forumid)
		WHERE thread.forumid = $vbrconfig[forumid]
	";
	$threads = $db->query_read($fullquery);

	if ($vbulletin->GPC['type'] == 'prune')
	{
		echo '<p><b>' . $vbphrase['deleting_threads'] . '</b>';
		while ($thread = $db->fetch_array($threads))
		{
			$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
			$threadman->set_existing($thread);
			$threadman->delete(0);
			unset($threadman);

			echo ". \n";
			require_once(DIR . '/includes/adminfunctions.php');
			vbflush();
		}
		echo ' ' . $vbphrase['done'] . '</p>';

		//define('CP_REDIRECT', 'thread.php?do=prune');
		print_stop_message('pruned_threads_successfully');
	}
	else if ($vbulletin->GPC['type'] == 'move')
	{
		$threadslist = '0';
		while ($thread = $db->fetch_array($threads))
		{
			$threadslist .= ",$thread[threadid]";
		}

		$db->query_write("
			UPDATE " . TABLE_PREFIX . "thread SET
				forumid = " . $vbulletin->GPC['destforumid'] . "
			WHERE threadid IN ($threadslist)
		");

		require_once(DIR . '/includes/functions_databuild.php');
		build_forum_counters($vbulletin->GPC['destforumid']);

		//define('CP_REDIRECT', 'thread.php?do=move');
		print_stop_message('moved_threads_successfully');
	}

}

// ########################################################################
printf($vbphrase['vbr_copf']);

/*======================================================================*\
|| #################################################################### ||
|| # End vBRecycle 3.0.x							    # ||
|| #################################################################### ||
\*======================================================================*/
?>