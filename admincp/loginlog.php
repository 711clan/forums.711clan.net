<?php

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('logging');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_log_error.php');

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['log_logins_hack']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'choose';
}

// ###################### Start view #######################
if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'    => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'userid'     => TYPE_UINT,
		'orderby'    => TYPE_STR,
	));

	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}

	if ($vbulletin->GPC['userid'])
	{
		$sqlconds = "WHERE userid = " . $vbulletin->GPC['userid'] . " ";
	}
	else
	{
		$sqlconds = '';
	}

	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "logins AS logins
		$sqlconds
	");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

	switch($vbulletin->GPC['orderby'])
	{
		case 'user':
			$order = 'username ASC, phpdate DESC';
			break;
		case 'logintype':
			$order = 'logintype ASC, phpdate DESC';
			break;
		case 'date':
		default:
			$order = 'date DESC';
	}

	$logs = $db->query_read("
		SELECT * FROM " . TABLE_PREFIX . "logins AS logins
		$sqlconds
		ORDER BY $order
		LIMIT $startat, " . $vbulletin->GPC['perpage'] . "
	");

	if ($db->num_rows($logs))
	{
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] . "\" tabindex=\"1\" onclick=\"window.location='loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] . "\" tabindex=\"1\" onclick=\"window.location='loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$prv'\">";
		}

		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] . " &gt;\" tabindex=\"1\" onclick=\"window.location='loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] . " &raquo;\" tabindex=\"1\" onclick=\"window.location='loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$totalpages'\">";
		}

		print_form_header('loginlog', 'remove');
		print_description_row(construct_link_code($vbphrase['restart'], "loginlog.php?" . $vbulletin->session->vars['sessionurl'] . ""), 0, 6, 'thead', $stylevar['right']);
		print_table_header(construct_phrase($vbphrase['login_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);

		$headings = array();
		$headings[] = $vbphrase['id'];
		$headings[] = "<a href=\"loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=user&page=" . $vbulletin->GPC['pagenumber'] . "\">" . str_replace(' ', '&nbsp;', $vbphrase['username']) . "</a>";
		$headings[] = "<a href=\"loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=date&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['date'] . "</a>";
		$headings[] = "<a href=\"loginlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=logintype&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['logintype'] . "</a>";
		$headings[] = str_replace(' ', '&nbsp;', $vbphrase['ip_address']);
		print_cells_row($headings, 1);

		while ($log = $db->fetch_array($logs))
		{

            if ($log['logintype'] == 'cplogin')
            {
                $log['logintype'] = $vbphrase['admin_login'];
            }
            else if ($log['logintype'] == 'modcplogin')
            {
                $log['logintype'] = $vbphrase['mod_login'];
            }
            else
            {
                $log['logintype'] = $vbphrase['reg_login'];
            }
            
			$cell = array();
			$cell[] = $log['logid'];
			$cell[] = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$log[userid]\"><b>$log[username]</b></a>";
			$cell[] = '<span class="smallfont">' . vbdate($vbulletin->options['logdateformat'], $log['phpdate']) . '</span>';
			$cell[] = '<span class="smallfont">' . $log['logintype'] . '</span>';
			$cell[] = '<span class="smallfont">' . iif($log['ipaddress'], "<a href=\"usertools.php?" . $vbulletin->session->vars['sessionurl'] . "do=gethost&ip=$log[ipaddress]\">$log[ipaddress]</a>", '&nbsp;') . '</span>';

			print_cells_row($cell, 0, 0, -4);
		}

		print_table_footer(6, "$firstpage $prevpage &nbsp; $nextpage $lastpage");
	}
	else
	{
		print_stop_message('no_results_matched_your_query');
	}
}

// ###################### Start prune log #######################
if ($_REQUEST['do'] == 'prunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>'))
{
	$vbulletin->input->clean_array_gpc('r', array(
		'daysprune' => TYPE_UINT,
		'userid'    => TYPE_UINT,
		'modaction' => TYPE_STR
	));

	$datecut = TIMENOW - (86400 * $vbulletin->GPC['daysprune']);
	$query = "SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "logins WHERE phpdate < $datecut";

	if ($vbulletin->GPC['userid'])
	{
		$query .= "\nAND userid = " . $vbulletin->GPC['userid'];
	}

	$logs = $db->query_first($query);
	if ($logs['total'])
	{
		print_form_header('loginlog', 'doprunelog');
		construct_hidden_code('datecut', $datecut);
		construct_hidden_code('userid', $vbulletin->GPC['userid']);
		print_table_header($vbphrase['prune_logins_logs']);
		print_description_row(construct_phrase($vbphrase['are_you_sure_you_want_to_prune_x_log_entries_from_logins_log'], vb_number_format($logs['total'])));
		print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
	}
	else
	{
		print_stop_message('no_logs_matched_your_query');
	}

}

// ###################### Start do prune log #######################
if ($_POST['do'] == 'doprunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>'))
{
	$vbulletin->input->clean_array_gpc('p', array(
		'datecut'   => TYPE_UINT,
		'userid'    => TYPE_UINT,
	));

	$sqlconds = ' ';
	if (!empty($vbulletin->GPC['userid']))
	{
		$sqlconds .= " AND userid = " . $vbulletin->GPC['userid'];
	}

	$db->query_write("DELETE FROM " . TABLE_PREFIX . "logins WHERE phpdate < " . $vbulletin->GPC['datecut'] . " $sqlconds");

	define('CP_REDIRECT', 'loginlog.php?do=choose');
	print_stop_message('pruned_logins_log_successfully');
}

// ###################### Start modify #######################
if ($_REQUEST['do'] == 'choose')
{
	print_form_header('loginlog', 'view');
	print_table_header($vbphrase['logins_log_viewer']);
	print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_input_row($vbphrase['logins_show_only'], 'userid');
	print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username'], 'logintype'  => $vbphrase['logintype']), 'date');
	print_submit_row($vbphrase['view'], 0);

	if (can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, ''))
	{
		print_form_header('loginlog', 'prunelog');
		print_table_header($vbphrase['prune_logins_logs']);
		print_input_row($vbphrase['logins_show_only'], 'userid');
		print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
		print_submit_row($vbphrase['prune_log_entries'], 0);
	}

}

print_cp_footer();
?>
