<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBShout v2	Created By Zero Tolerance [http://gzevolution.net]	||
|| #################################################################### ||
\*======================================================================*/

// ---------------------------------------------------
// Start Set PHP Environment
// ---------------------------------------------------

error_reporting(E_ALL & ~E_NOTICE);

// ---------------------------------------------------
// End Set PHP Environment
// ---------------------------------------------------

// ---------------------------------------------------
// Start Define Important Constants
// ---------------------------------------------------

define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'vbshout');

// ---------------------------------------------------
// End Define Important Constants
// ---------------------------------------------------

// ---------------------------------------------------
// Start Cache Of Any Needed Templates/Phrase's
// ---------------------------------------------------

$phrasegroups = array();

$specialtemplates = array();

$actiontemplates = array(
		'archive' => array(
				'GENERIC_SHELL',
				'forumhome_vbshout_archive_shout',
				'forumhome_vbshout_archive',
				'forumhome_vbshout_archive_topshouter',
				),
);


$globaltemplates = array(
	'forumhome_vbshout_shout',
);

// ---------------------------------------------------
// End Cache Of Any Needed Templates/Phrase's
// ---------------------------------------------------



// ---------------------------------------------------
// Start Require Globalized Settings
// ---------------------------------------------------

require_once('./global.php');
require_once(DIR . '/includes/functions_user.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/class_bbcode.php');

$DB                       = $db; // What? Its easier to write ^_^;
$vbulletin->vbshout_parse =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

$perpage    = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
$page       = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

// ---------------------------------------------------
// End Require Globalized Settings
// ---------------------------------------------------

// ---------------------------------------------------
// Start Incorrect Page Navigation Set
// ---------------------------------------------------

// Navigation
$navbits     = array();
$navbits[""] = "";

// ---------------------------------------------------
// End Incorrect Page Navigation Set
// ---------------------------------------------------

// ---------------------------------------------------
// Start Does The Page Have An Action Specified?
// ---------------------------------------------------

if ((!$_GET['do'] || $_GET['do'] == '') && !$_POST['do'])
{
	$_GET['do'] = 'latest';
}

// ---------------------------------------------------
// End Does The Page Have An Action Specified?
// ---------------------------------------------------


function buildTime($time = TIMENOW)
{
	global $vbulletin;

        $Time  = vbdate($vbulletin->options['dateformat'], $time, $vbulletin->options['yestoday']);
	$Time .= ' ';
        $Time .= vbdate($vbulletin->options['timeformat'], $time, $vbulletin->options['yestoday']);

	return $Time;
}

function bbcodeparser($text = '', $striphtml = true)
{
	global $vbulletin;

	if ($striphtml)
	{
		$text = htmlspecialchars_uni(trim($text));
	}

	if ($vbulletin->options['shout_bbcode'])
	{
		return $vbulletin->vbshout_parse->parse(convert_url_to_bbcode($text), 'nonforum');
	}
	else
	{
		return $text;
	}
}

function isBanned($user)
{
	return (isBanned_Check($user['userid'], 'shout_banned_users') || isBanned_Check($user['usergroupid'], 'shout_banned_usergroups'));	
}

function isBanned_Check($bash, $against)
{
	global $vbulletin;
	return in_array($bash, iif($vbulletin->options[$against], explode(',', $vbulletin->options[$against]), array()));
}

function canCommand()
{
	global $vbulletin;

	return $vbulletin->options['shout_can_commnd'] && can_moderate();
}

function execCommand($Command)
{
	global $meShout;

	if (preg_match_all("#^/pruneshout(.*)$#", $Command, $Matches, PREG_SET_ORDER) && canCommand())
	{
		return execCommand_pruneshout($Matches);
	}
	else if (preg_match_all("#^/prune(.*)$#", $Command, $Matches, PREG_SET_ORDER) && canCommand())
	{
		return execCommand_prune($Matches);
	}
	else if (preg_match_all("#^/me(.*)$#", $Command, $Matches, PREG_SET_ORDER))
	{
		$meShout = 1;
		return trim($Matches[0][1]);
	}

	return $Command;
}

function execCommand_pruneshout($Data)
{
	global $vbulletin;

	$Data = trim($Data[0][1]);

	if (!empty($Data))
	{
		//Steve, look here! $vbulletin->db->query('insert into shout_bu select * from shout where s_me in(0,1) and s_shout = \''.addslashes($Data).'\' or s_shout = \''.addslashes(fetch_word_wrapped_string($Data)).'\'');
		$vbulletin->db->query('delete from '.TABLE_PREFIX.'shout where s_me in(0,1) and s_shout = \''.addslashes($Data).'\' or s_shout = \''.addslashes(fetch_word_wrapped_string($Data)).'\'');
	}

	return true;
}

function execCommand_prune($Data)
{
	global $vbulletin;

	$Data = trim($Data[0][1]);

	if (empty($Data))
	{
		//$vbulletin->db->query('insert into shout_bu select * from shout');
		$vbulletin->db->query('delete from '.TABLE_PREFIX.'shout');
	}
	else
	{
		if ($u = $vbulletin->db->query_first('select userid from '.TABLE_PREFIX.'user where username = \''.addslashes(htmlspecialchars_uni($Data)).'\''))
		{
			//$vbulletin->db->query('insert into shout_bu select * from shout where s_by = \'' . intval($u['userid']) . '\'');
			$vbulletin->db->query('delete from '.TABLE_PREFIX.'shout where s_by = \''.intval($u['userid']).'\'');
		}
	}

	return true;
}

// ---------------------------------------------------
// Grab Latest X Shouts
// ---------------------------------------------------

if ($_GET['do'] == 'latest')
{
	$Output = array();
	$Shouts = $DB->query('
			select s.*, u.username, u.usergroupid from '.TABLE_PREFIX.'shout s
			left join '.TABLE_PREFIX.'user u on (u.userid = s.s_by)
			order by s.sid desc limit ' . $vbulletin->options['shout_display']);

	while ($Shout = $DB->fetch_array($Shouts))
	{
		$Shout['time']     = buildTime($Shout['s_time']);
		$Shout['s_shout']  = bbcodeparser($Shout['s_shout']);
		$Shout['style']    = '';
		$Shout['data']     = unserialize($Shout['s_data']);
		$Shout['username'] = fetch_musername($Shout, 'usergroupid');

		if ($Shout['data']['color'])
		{
			$Shout['style'] .= 'color:'.$Shout['data']['color'].';';
		}

		if ($Shout['data']['font'])
		{
			$Shout['style'] .= 'font-family:'.$Shout['data']['font'].';';
		}

		if ($Shout['data']['bold'])
		{
			$Shout['style'] .= 'font-weight:'.$Shout['data']['bold'].';';
		}

		if ($Shout['data']['underline'])
		{
			$Shout['style'] .= 'text-decoration:'.$Shout['data']['underline'].';';
		}

		if ($Shout['data']['italic'])
		{
			$Shout['style'] .= 'font-style:'.$Shout['data']['italic'].';';
		}

		if ($Shout['style'])
		{
			$Shout['s_shout'] = '<font style="'.$Shout['style'].'">'.$Shout['s_shout'].'</font>';
		}
		
		/*if (!isset($i)) {
			
			$i = 1;
		}
		
		if (!isset($up)) {
			
			$up = true;
		}
		
		if ($i == 50) {
			
			$up = false;
		}
		else if ($i == 1) {
			
			$up = true;
		}
		
		if ($up) {
				
			$Shout['s_shout'] = str_repeat(".", $i);
			$i++;
		}
		else {
				
			$Shout['s_shout'] = str_repeat(".", $i);
			$i--;
		}*/
		
/*

		if ($Shout['s_by'] == 1858)
		{
		
			$Shout['s_shout'] = 'I liek pickles';
		}
*/

		eval('$Output[] .= "' . fetch_template('forumhome_vbshout_shout') . '";');
	}

	if (isBanned($vbulletin->userinfo) && $vbulletin->options['shout_banned_perms'] > 0)
	{
		$Output = '';
		$Shout  = array(
				'time'     => buildTime(),
				'username' => 'System Reponse',
				's_shout'    => 'You are currently banned from the shoutbox',
			);

		eval('$Output .= "' . fetch_template('forumhome_vbshout_shout') . '";');
	}

	if (empty($Output))
	{
		$Output = '';
		$Shout  = array(
				'time'     => buildTime(),
				'username' => '7~11 Shoutbox Master',
				's_shout'    => 'Odie was too annoying so we had to clear the shoutbox',
			);

		eval('$Output .= "' . fetch_template('forumhome_vbshout_shout') . '";');
	}
	else
	{
		if ($vbulletin->options['shout_messages_order'])
		{
			$Output = array_reverse($Output);
		}

		$Shouts = $Output;
		$Output = '';

		foreach ($Shouts as $Shout)
		{
			$Output .= $Shout;
		}
	}

	unset($Shouts, $Shout);
	
	echo $Output;
	exit;
}

// ---------------------------------------------------
// End Latest X Shouts
// ---------------------------------------------------

// ---------------------------------------------------
// Shout
// ---------------------------------------------------

if ($_POST['do'] == 'shout')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'shout'	         => TYPE_STR,
		'color'          => TYPE_NOHTML,
		'fontFamily'     => TYPE_NOHTML,
		'fontWeight'     => TYPE_NOHTML,
		'fontStyle'      => TYPE_NOHTML,
		'textDecoration' => TYPE_NOHTML,
	));

	$meShout = 0;

	if (!empty($vbulletin->GPC['shout']) && $vbulletin->userinfo['userid'] > 0 && !isBanned($vbulletin->userinfo))
	{

		$ShoutData = addslashes(serialize(array(
				'color'      => addslashes(convert_urlencoded_unicode($vbulletin->GPC['color'])),
				'font'       => addslashes(convert_urlencoded_unicode($vbulletin->GPC['fontFamily'])),
				'bold'       => addslashes(convert_urlencoded_unicode($vbulletin->GPC['fontWeight'])),
				'italic'     => addslashes(convert_urlencoded_unicode($vbulletin->GPC['fontStyle'])),
				'underline'  => addslashes(convert_urlencoded_unicode($vbulletin->GPC['textDecoration'])),
		)));

		$vbulletin->GPC['shout'] = convert_urlencoded_unicode($vbulletin->GPC['shout']);
		
		if (substr($vbulletin->GPC['shout'], 0, 6) == "/prune") {
			$DB->query("
				insert into shout_log
				(s_time, s_by, s_shout)
				values
				(".TIMENOW.", {$vbulletin->userinfo['userid']}, '".addslashes($vbulletin->GPC['shout'])."')
			");
		}

		if (($vbulletin->GPC['shout'] = execCommand($vbulletin->GPC['shout'])) !== true)
		{
			$DB->query("
				insert into shout_bu
				(s_time, s_by, s_shout, s_data, s_me)
				values
				(".TIMENOW.", {$vbulletin->userinfo['userid']}, '".addslashes($vbulletin->GPC['shout'])."', '{$ShoutData}', $meShout)
			");
			$DB->query("
				insert into ".TABLE_PREFIX."shout
				(s_time, s_by, s_shout, s_data, s_me)
				values
				(".TIMENOW.", {$vbulletin->userinfo['userid']}, '".addslashes($vbulletin->GPC['shout'])."', '{$ShoutData}', $meShout)
			");
		}
	}

	exit;
}

// ---------------------------------------------------
// End Shout
// ---------------------------------------------------

// ---------------------------------------------------
// Display Shout Archive
// ---------------------------------------------------

if ($_GET['do'] == 'archive')
{
	$navbits     = array("vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=archive" => 'Archive');
	$navbits[""] = 'Viewing Shoutbox Archive';

	$TopTen = '';

	$TS   = $DB->query_first("select count(*) as `ts` from " . TABLE_PREFIX . "shout");
	$TS_D = $TS['ts'];
	$TS   = vb_number_format($TS['ts']);

	$T4 = $DB->query_first("select count(*) as `T4` from " . TABLE_PREFIX . "shout where s_time > " . (TIMENOW - (60 * 60 * 24)));
	$T4 = vb_number_format($T4['T4']);

	$TY = $DB->query_first("select count(*) as `TY` from " . TABLE_PREFIX . "shout where s_by = '{$vbulletin->userinfo['userid']}'");
	$TY = vb_number_format($TY['TY']);

	$TT = $DB->query('
			select s.*, count(s.sid) as `TS`, u.username, u.usergroupid from '.TABLE_PREFIX.'shout s
			left join '.TABLE_PREFIX.'user u on (u.userid = s.s_by)
			group by s.s_by having TS > 0
			order by TS desc limit 10');

	while ($TTS = $DB->fetch_array($TT))
	{
		$TTS['username'] = fetch_musername($TTS, 'usergroupid');
		eval('$TopTen .= "' . fetch_template('forumhome_vbshout_archive_topshouter') . '";');
	}

	sanitize_pageresults($TS_D, $page, $perpage, 40, 10);

	$limitlower = ($page - 1) * $perpage + 1;
	if ($limitlower <= 0)
	{
		$limitlower = 1;
	}

	$Output = array();
	$Shouts = $DB->query('
			select s.*, u.username, u.usergroupid from '.TABLE_PREFIX.'shout s
			left join '.TABLE_PREFIX.'user u on (u.userid = s.s_by)
			order by s.sid desc limit ' . ($limitlower - 1) . ',' . $perpage);

	while ($Shout = $DB->fetch_array($Shouts))
	{
		$Shout['msg_safe'] = $Shout['s_shout'];
		$Shout['time']     = buildTime($Shout['s_time']);
		$Shout['s_shout']  = bbcodeparser($Shout['s_shout']);
		$Shout['style']    = '';
		$Shout['data']     = unserialize($Shout['s_data']);
		$Shout['username'] = fetch_musername($Shout, 'usergroupid');

		if ($Shout['data']['color'])
		{
			$Shout['style'] .= 'color:'.$Shout['data']['color'].';';
		}

		if ($Shout['data']['font'])
		{
			$Shout['style'] .= 'font-family:'.$Shout['data']['font'].';';
		}

		if ($Shout['data']['bold'])
		{
			$Shout['style'] .= 'font-weight:'.$Shout['data']['bold'].';';
		}

		if ($Shout['data']['underline'])
		{
			$Shout['style'] .= 'text-decoration:'.$Shout['data']['underline'].';';
		}

		if ($Shout['data']['italic'])
		{
			$Shout['style'] .= 'font-style:'.$Shout['data']['italic'].';';
		}

		if ($Shout['style'])
		{
			$Shout['s_shout'] = '<font style="'.$Shout['style'].'">'.$Shout['s_shout'].'</font>';
		}

		eval('$Output[] .= "' . fetch_template('forumhome_vbshout_archive_shout') . '";');
	}

	if (isBanned($vbulletin->userinfo) && $vbulletin->options['shout_banned_perms'] > 0)
	{
		$Output = '';
		$Shout  = array(
				'time'     => buildTime(),
				'username' => 'System Reponse',
				's_shout'    => 'You are currently banned from the shoutbox',
			);

		eval('$Output .= "' . fetch_template('forumhome_vbshout_archive_shout') . '";');
	}

	if (empty($Output))
	{
		$Output = '';
		$Shout  = array(
				'time'     => buildTime(),
				'username' => 'System Reponse',
				's_shout'    => 'No Current Shouts',
			);

		eval('$Output .= "' . fetch_template('forumhome_vbshout_archive_shout') . '";');
	}
	else
	{
		$Shouts = $Output;
		$Output = '';

		foreach ($Shouts as $Shout)
		{
			$Output .= $Shout;
		}
	}

	unset($Shouts, $Shout);

	$pagenav = construct_page_nav($page, $perpage, $TS_D, 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=archive', ''
		. (!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : "")
	);

	eval('$HTML = "' . fetch_template('forumhome_vbshout_archive') . '";');
}

// ---------------------------------------------------
// Display Shout Archive
// ---------------------------------------------------

// ---------------------------------------------------
// AJAX Edit Shout
// ---------------------------------------------------

if ($_POST['do'] == 'editshout')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'id'	         => TYPE_UNIT,
		'shout'          => TYPE_STR,
	));

	if (!$Shout = $DB->query_first("select * from " . TABLE_PREFIX . "shout where sid = '{$vbulletin->GPC['id']}'"))
	{
		echo $vbulletin->GPC['shout'];
		exit;
	}

	if ($Shout['sid'] != $vbulletin->userinfo['userid'] && !can_moderate())
	{
		echo $vbulletin->GPC['shout'];
		exit;
	}

	$vbulletin->GPC['shout'] = convert_urlencoded_unicode($vbulletin->GPC['shout']);

	$DB->query("update " . TABLE_PREFIX . "shout set s_shout = '".addslashes($vbulletin->GPC['shout'])."' where sid = $Shout[sid]");

	$Shout = $DB->query_first("select * from " . TABLE_PREFIX . "shout where sid = '{$vbulletin->GPC['id']}'");

	$Shout['s_shout']  = bbcodeparser($Shout['s_shout']);
	$Shout['style']    = '';
	$Shout['data']     = unserialize($Shout['s_data']);

	if ($Shout['data']['color'])
	{
		$Shout['style'] .= 'color:'.$Shout['data']['color'].';';
	}

	if ($Shout['data']['font'])
	{
		$Shout['style'] .= 'font-family:'.$Shout['data']['font'].';';
	}

	if ($Shout['data']['bold'])
	{
		$Shout['style'] .= 'font-weight:'.$Shout['data']['bold'].';';
	}

	if ($Shout['data']['underline'])
	{
		$Shout['style'] .= 'text-decoration:'.$Shout['data']['underline'].';';
	}

	if ($Shout['data']['italic'])
	{
		$Shout['style'] .= 'font-style:'.$Shout['data']['italic'].';';
	}

	if ($Shout['style'])
	{
		$Shout['s_shout'] = '<font style="'.$Shout['style'].'">'.$Shout['s_shout'].'</font>';
	}

	echo $Shout['s_shout'];
	exit;
}

// ---------------------------------------------------
// AJAX Edit Shout
// ---------------------------------------------------

// ---------------------------------------------------
// AJAX Delete Shout
// ---------------------------------------------------

if ($_POST['do'] == 'deleteshout')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'id' => TYPE_UNIT,
	));

	if (!$Shout = $DB->query_first("select * from " . TABLE_PREFIX . "shout where sid = '{$vbulletin->GPC['id']}'"))
	{
		echo 'false';
		exit;
	}

	if ($Shout['sid'] != $vbulletin->userinfo['userid'] && !can_moderate())
	{
		echo 'false';
		exit;
	}

	$vbulletin->GPC['shout'] = convert_urlencoded_unicode($vbulletin->GPC['shout']);

	$DB->query("delete from " . TABLE_PREFIX . "shout where sid = $Shout[sid]");
	
	echo 'true';
	exit;
}

// ---------------------------------------------------
// AJAX Delete Shout
// ---------------------------------------------------

// ---------------------------------------------------
// Start Page Output
// ---------------------------------------------------


$navbits = construct_navbits($navbits);
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('GENERIC_SHELL') . '");');


// ---------------------------------------------------
// End Page Output
// ---------------------------------------------------
?>