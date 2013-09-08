<?php

// Much of this is taken from the excellent Login Log plugin
// from here http://www.vbulletin.org/forum/showthread.php?t=124907
// Many thanks Abe1

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

print_cp_header($vbphrase['vbstopforumspam_log']);

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
		'orderby'    => TYPE_STR,
	));

	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}

	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "vbstopforumspam_log AS logs
	");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

	switch($vbulletin->GPC['orderby'])
	{
		case 'ip':
			$order = 'ip ASC, date DESC';
			break;
		case 'email':
			$order = 'email ASC, date DESC';
			break;
		case 'username':
			$order = 'username ASC, date DESC';
			break;			
		case 'date':
		default:
			$order = 'date DESC';
	}

	$logs = $db->query_read("
		SELECT * FROM " . TABLE_PREFIX . "vbstopforumspam_log AS logs
		$sqlconds
		ORDER BY $order
		LIMIT $startat, " . $vbulletin->GPC['perpage'] . "
	");

	if ($db->num_rows($logs))
	{
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] . "\" tabindex=\"1\" onclick=\"window.location='vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] .  "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] . "\" tabindex=\"1\" onclick=\"window.location='vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$prv'\">";
		}

		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] . " &gt;\" tabindex=\"1\" onclick=\"window.location='vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] . " &raquo;\" tabindex=\"1\" onclick=\"window.location='vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&modaction=" . $vbulletin->GPC['modaction'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$totalpages'\">";
		}

		print_form_header('vbstopforumspam', 'remove');
		print_description_row(construct_link_code($vbphrase['restart'], "vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . ""), 0, 6, 'thead', $stylevar['right']);
		print_table_header(construct_phrase($vbphrase['vbstopforumspam_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);

		$headings = array();
		
		$headings[] = "<a href=\"vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&pp=" . $vbulletin->GPC['perpage'] . "&orderby=username&page=" . $vbulletin->GPC['pagenumber'] . "\">" . str_replace(' ', '&nbsp;', $vbphrase['username']) . "</a>";
		$headings[] = "<a href=\"vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&pp=" . $vbulletin->GPC['perpage'] . "&orderby=date&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['date'] . "</a>";
		$headings[] = "<a href=\"vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&pp=" . $vbulletin->GPC['perpage'] . "&orderby=email&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['email'] . "</a>";
		$headings[] = "<a href=\"vbstopforumspam.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&pp=" . $vbulletin->GPC['perpage'] . "&orderby=ip&page=" . $vbulletin->GPC['pagenumber'] . "\">IP Address</a>";

		$headings[] = str_replace(' ', '&nbsp;', 'Message');
		
		print_cells_row($headings, 1);

		while ($log = $db->fetch_array($logs))
		{


			$cell = array();
			// if user_id > 0 then the registration was allowed and the log was updated with the users id number, so here, we allow the admin to edit the user
			if ($log['user_id'] > 0) { 
					$cell[] = '<span class="smallfont"><a href="user.php?do=edit&u=' . $log['user_id'] . '"><b>' . $log['username'] . '</b></a></span>';
			} else {
					$cell[] = '<span class="smallfont"><b>' . $log['username'] . '</b></span>';
			}
			
			$cell[] = '<span class="smallfont">' . $log['date'] . '</span>';
			$cell[] = '<span class="smallfont">' . $log['email'] . '</span>';
			$cell[] = '<span class="smallfont">' . $log['ipaddress'] . '</span>';
			$cell[] = '<span class="smallfont">' . $log['message'] . '</span>';

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
		'modaction' => TYPE_STR
	));




	//$datecut = TIMENOW - (86400 * $vbulletin->GPC['daysprune']);
	$datecut = $vbulletin->GPC['daysprune'];
	$query = "SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "vbstopforumspam_log WHERE date < DATE_SUB(NOW(), INTERVAL $datecut DAY);";


	$logs = $db->query_first($query);
	if ($logs['total'])
	{
		print_form_header('vbstopforumspam', 'doprunelog');
		construct_hidden_code('datecut', $datecut);
	
		print_table_header($vbphrase['prune_vbstopforumspam_logs']);
		print_description_row(construct_phrase("Are you sure you wish to delete " . vb_number_format($logs['total']) . " records from the logs"));
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
		
	));

	$sqlconds = ' ';
	
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "vbstopforumspam_log  WHERE date < DATE_SUB(NOW(), INTERVAL ". $vbulletin->GPC['datecut'] . " DAY);" );
	

	define('CP_REDIRECT', 'vbstopforumspam.php?do=choose');
	print_stop_message('pruned_vbstopforumspam_log_successfully');
}

// ###################### Start modify #######################
if ($_REQUEST['do'] == 'choose')
{
	print_form_header('vbstopforumspam', 'view');
	print_table_header($vbphrase['vbstopforumspam_log_viewer']);
	print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username'], 'email'  => $vbphrase['email'], 'date' => $vbphrase['date']));
	print_submit_row($vbphrase['view'], 0);

	if (can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, ''))
	{
		print_form_header('vbstopforumspam', 'prunelog');
		print_table_header($vbphrase['prune_vbstopforumspam_logs']);
		print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
		print_submit_row($vbphrase['prune_log_entries'], 0);
	}

}

print_cp_footer();
?>
