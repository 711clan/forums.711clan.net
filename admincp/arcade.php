<?php
// ######################################################################
// ibProArcade Administrator Control Panel for v2.7.2+
// completely adapted to vBulletin 3.x.x & 4.x.x (C) by MrZeropage
// 
// This script may only be spread on official vBulletin-Forums
// (www.vbulletin.org / www.vbulletin-germany.org)
// any other distribution is prohibited, you need to verify your
// vBulletin-license in order to get this script
// ######################################################################

$DUPECHECK = 0;		// set this to 1 to activate checking for duplicate games

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('GET_EDIT_TEMPLATES', true);
define('THIS_SCRIPT', 'ibproarcade_admin');
$EMULATE_VBPLAZA = 0;	// set this to 1 for emulating existing vbPlaza

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();
$actiontemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/functions_misc.php');
include "./includes/config.php";

define( ROOT_PATH , "./" );
define( FUNCTIONS_PATH, "./arcade/functions/");
define( MODULE_PATH , "./arcade/modules/" );

require FUNCTIONS_PATH."functions.php";
$std   = new FUNC;

// ######################### DATABASE CONNECTION ##########################
$sql_driver = FUNCTIONS_PATH . "dbclass.php";
require $sql_driver;
$DB = new db_driver;

// automatic vBulletin-Version-Detection by MrZeropage
$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 or 3.5
if ($vbversion == "3.0")
{
	// DBaccess for vBulletin 3.0.x
	$DB->obj['sql_database']	= $dbname;
	$DB->obj['sql_user']         	= $dbusername;
	$DB->obj['sql_pass']         	= $dbpassword;
	$DB->obj['sql_host']         	= $servername;
	$DB->obj['sql_port']         	= $port;
	$DB->obj['sql_tbl_prefix']   	= $tableprefix;
}
else
{
	// DBaccess for vBulletin 3.5.x and later
	$DB->obj['sql_database']     	= $config['Database']['dbname'];
	$DB->obj['sql_user']         	= $config['MasterServer']['username'];
	$DB->obj['sql_pass']         	= $config['MasterServer']['password'];
	$DB->obj['sql_host']         	= $config['MasterServer']['servername'];
	$DB->obj['sql_port']         	= $config['MasterServer']['port'];
	$DB->obj['sql_tbl_prefix']   	= $config['Database']['tableprefix'];
}

$DB->connect();

// read settings
$DB->query("SELECT * FROM ibf_games_settings");
$arcade = $DB->fetch_row();
$arcade['league_scores'] = explode("," , $arcade['league_scores'] );
$show_active = unserialize($arcade['show_active']);

if ($vbversion == "3.0")
{
$ibforums->member['id'] = $bbuserinfo['userid'];
}
else
{
$ibforums->member['id'] = $vbulletin->userinfo['userid'];
}


// load languagepack
$languageid = $arcade['arcade_language'];
$langfile = "lang_Arcade_".$languageid;
$ibforums->lang = $std->load_words($ibforums->lang, $langfile, $ibforums->lang_id );

// check if file is valid and compatible
if ($ibforums->lang['acp_on'] == "")
{
	// incompatible language-file, load english (default)
	$ibforums->lang = $std->load_words($ibforums->lang, 'lang_Arcade_en', $ibforums->lang_id );
}

if ($EMULATE_VBPLAZA==1)
{
	$vbulletin->options['vbbux_enabled'] = 1;
	$vbulletin->options['vbbux_arcadeintegration'] = 1;
}

// parse all incoming data
$IN = $std->parse_incoming();
foreach($_POST as $key => $value)
{
	$IN[$key] = $value;
}
foreach($_GET as $key => $value)
{
	$IN[$key] = $value;
}

$action = $_GET['code'];
if ($action == "")
{
	$action = "gamelist";
}

switch ($_POST['do'])
{
	case "updatesettings":	
	$action = "updatesettings";	
	break;

	case "jumpsettings":	
	$action = "settings";	
	break;

	case "updategroups":	
	$action = "updategroups";	
	break;

	case "sort_games":	
	$action = "sortgames";	
	break;

	case "edit_categories":	
	$action = "do_cat_stuff";
	$IN['do'] = "edit";
	break;

	case "cat":	
	$action = "cat";
	break;

	case "add_category":	
	$action = "do_cat_stuff";
	$IN['do'] = "add";
	break;

	case "usermanager":	
	$action = "usermanager";	
	break;

	case "save_user":	
	$action = "save_user";	
	break;

	case "save_ipban":	
	$action = "save_ipban";	
	break;

	case "score_tools":	
	$action = "score_tools";	
	break;

	case "tool_scores":	
	$action = "tool_scores";	
	break;

	case "tool_multi_del":	
	$action = "tool_multi_del";	
	break;

	case "tool_actnames":	
	$action = "tool_actnames";	
	break;

	case "tool_champs":	
	$action = "tool_champs";	
	break;

	case "tool_best":	
	$action = "tool_best";	
	break;

	case "tool_league":	
	$action = "tool_league";	
	break;

	case "games_setmulticat":	
	$action = "games_setmulticat";	
	break;

	case "user_search":	
	$action = "user_search";	
	break;

	case "prunet":	
	$action = "tourney_stuff";
	$IN['do'] = "prunet";	
	break;

	case "newt":	
	$action = "tourney_stuff";
	$IN['do'] = "newt";	
	break;

	case "add_t_confirm":	
	$action = "tourney_stuff";
	$IN['do'] = "add_t_confirm";	
	break;

	case "do_add_t":	
	$action = "tourney_stuff";
	$IN['do'] = "do_add_t";	
	break;

	case "confirm_r":	
	$action = "tourney_stuff";
	$IN['do'] = "confirm_r";	
	break;

	case "viewtourney":	
	$action = "tourney_stuff";
	$IN['do'] = "view";	
	break;

	case "do_replace":	
	$action = "tourney_stuff";
	$IN['do'] = "do_replace";	
	break;

	case "add_game":	
	$action = "add_game";
	break;

	case "del":	
	$action = "del";
	break;

	case "do_edit":	
	$action = "do_editgame";
	break;

	case "edit_comment":
	$action = "edit_comment";
	break;

	case "do_add":	
	$action = "do_addgame";
	break;

	case "do_add_sql":	
	$action = "do_add_sql";
	break;

	case "tourneysettings":	
	$action = "tourneysettings";
	break;

	case "savemessages":	
	$action = "savemessages";
	break;
}

// ##### some functionality for some following operations #####

function update_cat_game_nums()
{
	global $DB;

        	$new_amounts = array();

        	$this_query = $DB->query("SELECT c_id, show_all FROM ibf_games_cats ORDER BY c_id");
        	while( $CAT = $DB->fetch_row($this_query) )
        	{
        		$query_extra = "";
        		if( !$CAT['show_all'] )
            		{
			$query_extra = "AND gcat=".$CAT['c_id'];
            		}
        		$DB->query("SELECT COUNT(gid) AS amount FROM ibf_games_list WHERE active=1 ".$query_extra);
            		$the = $DB->fetch_row();
            		$new_amounts[ $CAT['c_id'] ] = $the['amount'];
        	}

        	foreach( $new_amounts as $cat=>$amount )
        	{
            		$db_string = $DB->compile_db_update_string( array ( 'num_of_games' => $amount ) );
            		$DB->query("UPDATE ibf_games_cats SET ".$db_string." WHERE c_id='".$cat."'");
        	}
}


function do_champ_update($auto_run = 0)
{
	global $IN, $DB;

        	$query_extra = "";
        	if( !$auto_run )
        	{
        		if( !in_array("0" , $IN['in_game']) )
        		{
        			$query_extra .= "WHERE gid IN (".implode("," , $IN['in_game']).")";
        		}
        	}

        	$the_champs = array();

        	if( $query_extra == "" )
        	{
        		$DB->query("DELETE FROM ibf_games_champs");
        	}

	$game_query = $DB->query("SELECT gid, highscore_type, gtitle FROM ibf_games_list ".$query_extra." ORDER by gtitle");
	while( $game = $DB->fetch_row($game_query) )
	{
        		$order = ($game['highscore_type'] == "high") ? "DESC" : "ASC";

    		$DB->query("SELECT s.mid, s.gid, s.name, s.datescored, s.score, g.gtitle
    				FROM ibf_games_scores AS s, ibf_games_list AS g
                			WHERE s.gid=g.gid AND s.gid=".$game['gid']."
                			ORDER BY score ".$order.", timespent ASC");
            		if( $DB->get_num_rows() )
            		{
    			$champ = $DB->fetch_row();
    			$the_champs[] = array(	'gid'		=>	$champ['gid'],
    						'gtitle'	=>	$champ['gtitle'],
                            			'mid'		=>	$champ['mid'],
                            			'name'      	=>	$champ['name'],
                            			'date'		=>	$champ['datescored'],
                            			'score'		=>	$champ['score'] );
            		}
	}

	foreach( $the_champs as $this_champ )
	{
            		if( $query_extra != "" )
            		{
            			$db_string = $DB->compile_db_update_string( array ( 	'champ_gid'     => $this_champ['gid'],
                                                                					'champ_gtitle'  => $this_champ['gtitle'],
                                                                					'champ_mid'     => $this_champ['mid'],
                                                                 					'champ_name'    => $this_champ['name'],
                                                                 					'champ_date'    => $this_champ['date'],
                                                                    					'champ_score'	=> $this_champ['score']
									) );
			$DB->query("UPDATE ibf_games_champs SET ".$db_string." WHERE champ_gid=".$this_champ['gid']);
            		}
            		else
            		{
        			$db_string = $DB->compile_db_insert_string( array ( 	'champ_gid'     => $this_champ['gid'],
                                                               						'champ_gtitle'  => $this_champ['gtitle'],
                                                               						'champ_mid'     => $this_champ['mid'],
                                                               						'champ_name'    => $this_champ['name'],
                                                               						'champ_date'    => $this_champ['date'],
                                                               						'champ_score'	=> $this_champ['score']
									) );
			$DB->query("INSERT INTO ibf_games_champs (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
            		}
	}

        	if( !$auto_run )
        	{
/*
        		if( $arcade['log'] )
        		{
        			$ADMIN->save_log("Highscores aktualisiert");
        		}
*/
		define('CP_REDIRECT', 'arcade.php?code=score_tools');
		print_stop_message('saved_settings_successfully');
        	}
}


function do_league_update($auto_run = 0)
{
	global $IN, $DB;

	$DB->query("DELETE FROM ibf_games_league");

	$game_query = $DB->query("SELECT * FROM ibf_games_list WHERE active=1");
	while ($ginfo = $DB->fetch_row($game_query))
	{
		$ordering = ($ginfo['highscore_type'] == "high") ? "DESC" : "ASC";
		$ctr = 1;
		$this_query = $DB->query("SELECT mid FROM ibf_games_scores WHERE gid='".$ginfo['gid']."' ORDER BY score ".$ordering.", timespent ASC LIMIT 0,10");
		if ($DB->get_num_rows($this_query))
		{
			while($lboard = $DB->fetch_row($this_query))
			{
				switch($ctr)
				{
					case 1: $points = $arcade['league_scores'][0];
					break;
					case 2: $points = $arcade['league_scores'][1];
					break;
					case 3: $points = $arcade['league_scores'][2];
					break;
					case 4: $points = $arcade['league_scores'][3];
					break;
					case 5: $points = $arcade['league_scores'][4];
					break;
					case 6: $points = $arcade['league_scores'][5];
					break;
					case 7: $points = $arcade['league_scores'][6];
					break;
					case 8: $points = $arcade['league_scores'][7];
					break;
					case 9: $points = $arcade['league_scores'][8];
					break;
					case 10: $points = $arcade['league_scores'][9];
					break;
					default: $points = $arcade['league_scores'][10];
				}

				if ($points > 0)
				{
					extract($ginfo);
					$lid = $lboard['mid'];
					$db_string = $DB->compile_db_insert_string( array (     	'mid'   => $lid,
											'gid'  => $gid,
											'position'   => $ctr,
											'points'    => $points,
											'cat'	    => $gcat, 
											) );
					$DB->query("INSERT INTO ibf_games_league (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
				}
				$ctr++;
			}
		}
	}

	if (!$auto_run)
	{
/*
		if( $arcade['log'] )
		{
			$ADMIN->save_log("Rangliste aktualisiert");
		}
*/
		define('CP_REDIRECT', 'arcade.php?code=score_tools');
		print_stop_message('saved_settings_successfully');
	}
}


function confirm($game_id = "")
{
	global $IN, $DB, $ibforums;

        	$game_id = $IN['gid'];

        	$DB->query("SELECT gtitle, gname FROM ibf_games_list WHERE gid=".$game_id);
        	$GAME = $DB->fetch_row();

        	$existsf = ( file_exists($INFO['base_dir']."arcade/".$GAME['gname'].".swf") ) ? "<b>".$ibforums->lang['acp_yes']."</b>" : "<span style='color: #FF0000;'><b>".$ibforums->lang['acp_no']."</b></span>";
        	$existsi1 = ( file_exists($INFO['base_dir']."arcade/images/".$GAME['gname']."1.gif") ) ? "<b>".$ibforums->lang['acp_yes']."</b>" : "<span style='color: #FF0000;'><b>".$ibforums->lang['acp_no']."</b></span>";
        	$existsi2 = ( file_exists($INFO['base_dir']."arcade/images/".$GAME['gname']."2.gif") ) ? "<b>".$ibforums->lang['acp_yes']."</b>" : "<span style='color: #FF0000;'><b>".$ibforums->lang['acp_no']."</b></span>";
        	$removablef = ( is_writable($INFO['base_dir']."arcade/".$GAME['gname'].".swf") ) ? "<b>".$ibforums->lang['acp_yes']."</b>" : "<span style='color: #FF0000;'><b>".$ibforums->lang['acp_no']."</b></span>";
        	$removablei1 = ( is_writable($INFO['base_dir']."arcade/images/".$GAME['gname']."1.gif") ) ? "<b>".$ibforums->lang['acp_yes']."</b>" : "<span style='color: #FF0000;'><b>".$ibforums->lang['acp_no']."</b></span>";
        	$removablei2 = ( is_writable($INFO['base_dir']."arcade/images/".$GAME['gname']."2.gif") ) ? "<b>".$ibforums->lang['acp_yes']."</b>" : "<span style='color: #FF0000;'><b>".$ibforums->lang['acp_no']."</b></span>";

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'del');

	$header = array();
	$header[] = "<div align='center'>".$ibforums->lang['acp_game_tar_confirm1']."</div>";
	$header[] = $ibforums->lang['acp_game_tar_confirm2'];
	$header[] = "<div align='center'>".$ibforums->lang['acp_game_tar_confirm3']."</div>";
	$colspan = sizeof($header);
	print_table_header($ibforums->lang['acp_game_tar_removehead'].$GAME['gtitle'], $colspan);
	print_cells_row($header, 1);

	$cell = array();
	$cell[] = "<div align='center'><b>".$GAME['gname'].".swf</b></div>";
	$cell[] = "<div align='center'>".$existsf."</div>";
	$cell[] = "<div align='center'>".$removablef."</div>";
	print_cells_row($cell);

	$cell = array();
	$cell[] = "<div align='center'><b>".$GAME['gname']."1.gif</b></div>";
	$cell[] = "<div align='center'>".$existsi1."</div>";
	$cell[] = "<div align='center'>".$removablei1."</div>";
	print_cells_row($cell);

	$cell = array();
	$cell[] = "<div align='center'><b>".$GAME['gname']."2.gif</b></div>";
	$cell[] = "<div align='center'>".$existsi2."</div>";
	$cell[] = "<div align='center'>".$removablei2."</div>";
	print_cells_row($cell);

	print_table_break('', "90%");

	print_select_row($ibforums->lang['acp_game_tar_select'], 'confirm', array('0' => $ibforums->lang['acp_game_tar_select1'], '1' => $ibforums->lang['acp_game_tar_select2'], '2' => $ibforums->lang['acp_game_tar_select3']), 0);
	construct_hidden_code('gid', $game_id);
	print_submit_row($ibforums->lang['acp_save_settings'], 0);
	print_cp_footer();
	exit;
}


function timeoutput($temptime)
// show correct date/time information, by MrZeropage
{
	global $languageid;

	$bbuserinfo['tzoffset'] = $bbuserinfo['timezoneoffset'];

	if ($bbuserinfo['dstonoff'])
	{
		$bbuserinfo['tzoffset']++;
		if (substr($bbuserinfo['tzoffset'], 0, 1) != '-')
		{
			// recorrect so that it has + sign, if necessary
			$bbuserinfo['tzoffset'] = '+' . $bbuserinfo['tzoffset'];
		}
	}

	if ($bbuserinfo['tzoffset'])
	{
		if ($bbuserinfo['tzoffset'] > 0 AND strpos($bbuserinfo['tzoffset'], '+') === false)
		{
			$bbuserinfo['tzoffset'] = '+' . $bbuserinfo['tzoffset'];
		}
	}

	if (substr($bbuserinfi['tzoffset'],1,0) == "+")
	{
		$temptime = $temptime + strval(substr($bbuserinfo['tzoffset'],1)) * 3600;
	}
	else
	{
		$temptime = $temptime - strval(substr($bbuserinfo['tzoffset'],1)) * 3600;
	}

	if ($languageid == "de")
	{
		$dateformat = "d.m.Y H:i";
	}
	else
	{
		$dateformat = "Y-m-d h:i a";
	}

	$output = vbdate($dateformat, $temptime);
	return $output;
}


// ##############################
// notifications / messages
// ##############################
if ($action == "messages")
{
	$commoncodes = "	<b>%NAME%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_NAME']."<br />
				<b>%GAME%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_GAME'];

	$additional1 = "	<b>%CHAMP%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_CHAMP']."<br />
				<b>%LINKGAME|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKG']."<br />
				<b>%LINKHIGH|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKH'];

	$additional2 = "	<b>%LINK|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKT'];

	$additional3 = "	<b>%OTHER%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_OPPONENT']."<br />
				<b>%LINK|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKT'];

	$additional4 = "	<b>%OTHER%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_OPPONENT']."<br />
				<b>%LINK|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKT'];

	$additional5 = "	<b>%LINK|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKT'];

	$additional6 = "	<b>%LINK|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKT'];

	$additional7 = "	<b>%LINK|</b><i>text</i><b>%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_LINKT']."<br />
				<b>%LIMIT%</b> &nbsp; = &nbsp; ".$ibforums->lang['acp_notifi_DAYS'];

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'savemessages');

	print_table_header($ibforums->lang['acp_notifi_head']); 
	print_description_row($ibforums->lang['acp_notifi_global']."<br /><dfn>".$commoncodes."</dfn>");
	print_yes_no_row($ibforums->lang['acp_notifi_sendwhen'].$ibforums->lang['acp_notifi_msg1desc'], 'msgsys_hscore', $arcade['msgsys_hscore']);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg1name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional1."</dfn>", 'msgsys_hscore_text', $arcade['msgsys_hscore_text'], 6, 45);
	print_yes_no_row($ibforums->lang['acp_notifi_sendwhen'].$ibforums->lang['acp_notifi_msg2desc'], 'msgsys_tstart', $arcade['msgsys_tstart']);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg2name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional2."</dfn>", 'msgsys_tstart_text', $arcade['msgsys_tstart_text'], 6, 45);
	print_yes_no_row($ibforums->lang['acp_notifi_sendwhen'].$ibforums->lang['acp_notifi_msg3desc'], 'msgsys_tadvance', $arcade['msgsys_tadvance']);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg3name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional3."</dfn>", 'msgsys_tadvance_text', $arcade['msgsys_tadvance_text'], 6, 45);
	print_yes_no_row($ibforums->lang['acp_notifi_sendwhen'].$ibforums->lang['acp_notifi_msg4desc'], 'msgsys_telim', $arcade['msgsys_telim']);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg4name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional4."</dfn>", 'msgsys_telim_text', $arcade['msgsys_telim_text'], 6, 45);
	print_yes_no_row($ibforums->lang['acp_notifi_towinner'], 'msgsys_twin', $arcade['msgsys_twin']);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg5name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional5."</dfn>", 'msgsys_twin_text', $arcade['msgsys_twin_text'], 6, 45);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg6name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional6."</dfn>", 'msgsys_tremind_text', $arcade['msgsys_tremind_text'], 6, 45);
	print_textarea_row($ibforums->lang['acp_notifi_msgtxt']."<b>".$ibforums->lang['acp_notifi_msg7name']."</b><br /><dfn>".$ibforums->lang['acp_notifi_additional']."<br />".$additional7."</dfn>", 'msgsys_tdisqual_text', $arcade['msgsys_tdisqual_text'], 6, 45);

	print_submit_row($ibforums->lang['acp_save_settings'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// save messages
// ##############################
if ($action == "savemessages")
{
	global $IN;

       	$db_string = $DB->compile_db_update_string( array ( 	'msgsys_hscore'    	=> $IN['msgsys_hscore'],
                                                            	'msgsys_hscore_text'   	=> $IN['msgsys_hscore_text'],
                                                            	'msgsys_tstart'     	=> $IN['msgsys_tstart'],
                                                            	'msgsys_tstart_text'    => $IN['msgsys_tstart_text'],
                                                            	'msgsys_tadvance'     	=> $IN['msgsys_tadvance'],
                                                            	'msgsys_tadvance_text'  => $IN['msgsys_tadvance_text'],
                                                            	'msgsys_telim'     	=> $IN['msgsys_telim'],
                                                            	'msgsys_telim_text'  	=> $IN['msgsys_telim_text'],
                                                            	'msgsys_twin'     	=> $IN['msgsys_twin'],
                                                            	'msgsys_twin_text'     	=> $IN['msgsys_twin_text'],
                                                            	'msgsys_tremind_text'  	=> $IN['msgsys_tremind_text'],
                                                            	'msgsys_tdisqual_text'  => $IN['msgsys_tdisqual_text']
                                                    		) );

       	$DB->query("UPDATE ibf_games_settings SET ".$db_string);

/*
       	if( $arcade['log'] )
       	{
       		$ADMIN->save_log("");
       	}
*/
	define('CP_REDIRECT', 'arcade.php?code=messages');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// main settings
// ##############################
if ($action == "settings")
{
	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'updatesettings');

	// read the language-files
	// de => name
        $files = array();
        $dir = ROOT_PATH."arcade/lang";
	$langfiles = array();

	if ( is_dir($dir) )
	{
		$the_dir = opendir($dir);

		while( ($filename = readdir($the_dir)) !== false )
		{
			if( ($filename != ".") && ($filename != "..") )
			{
				if (substr($filename,0,12) == "lang_Arcade_")
				{
					$langid = substr($filename,12,2);
					switch ($langid)
					{
						case "en": $langname = "english"; break;
						case "de": $langname = "deutsch"; break;
						case "fr": $langname = "francais"; break;
						case "nl": $langname = "nederlands"; break;
						case "it": $langname = "italiano"; break;
						case "tr": $langname = "turkiye"; break;
						case "es" : $langname = "espaniol"; break;
						case "pt" : $langname = "portuguese"; break;
						case "ar" : $langname = "arabic"; break;
						default: $langname = "?"; break;
					}
					$langfiles[$langid] = $langname;
				}
			}
		}
		closedir($the_dir);
	}

	print_table_header($ibforums->lang['acp_main_header']);
	print_select_row($ibforums->lang['acp_main_arcade_status'], 'arcade_status', array('1' => $ibforums->lang['acp_on'], '0' => $ibforums->lang['acp_off']), $arcade['arcade_status']);
	print_select_row($ibforums->lang['acp_main_arcade_lang'], 'arcade_language', $langfiles, $arcade['arcade_language']);
	print_input_row($ibforums->lang['acp_main_arcade_timeout'], 'scoretimeout', $arcade['scoretimeout'], 0);
	print_select_row($ibforums->lang['acp_main_g_display_sort'], 'g_display_sort', array('gtitle' => $ibforums->lang['acp_alphabetic'], 'gcount' => $ibforums->lang['acp_oftenplayed'], 'added' => $ibforums->lang['dateadded'], 'position' => $ibforums->lang['acp_userdefined']), $arcade['g_display_sort']);
	print_select_row($ibforums->lang['acp_main_g_disp_order'], 'g_display_order', array('ASC' => $ibforums->lang['acp_ASC'], 'DESC' => $ibforums->lang['acp_DESC']), $arcade['g_display_order']);
	print_input_row($ibforums->lang['acp_main_games_pr'], 'games_pr', $arcade['games_pr'], 0);
	print_input_row($ibforums->lang['acp_main_games_pp'], 'games_pp', $arcade['games_pp'], 0);
	print_input_row($ibforums->lang['acp_main_scores_amount'], 'scores_amount', $arcade['scores_amount'], 0);
	print_input_row($ibforums->lang['acp_main_user_choices'], 'user_choices', $arcade['user_choices'], 0);
	print_select_row($ibforums->lang['acp_main_skin'], 'skin', array('0' => "ibProArcade", '1' => "v3arcade"), $arcade['skin']);
	print_yes_no_row($ibforums->lang['acp_main_user_skin'], 'allow_user_skin', $arcade['allow_user_skin']);

	$selectedmin = ""; $selectedhour = ""; $selectedday = "";
	if ($arcade['show_new_frame'] == "60") { $selectedmin = "selected='selected'"; } 
	if ($arcade['show_new_frame'] == "3600") { $selectedhour = "selected='selected'"; } 
	if ($arcade['show_new_frame'] == "86400") { $selectedday = "selected='selected'"; } 
	$output = array();
	$output[] = $ibforums->lang['acp_main_show_new'];
	$output[] = "	<div align='left'>
			<input type='text' name='show_new' size='3' class='textinput' value='".$arcade['show_new']."'>\n
			<select name='show_new_frame' class='dropdown'>\n
			<option value='60' ".$selectedmin.">".$ibforums->lang['acp_minutes']."</option>\n
			<option value='3600' ".$selectedhour.">".$ibforums->lang['acp_hours']."</option>\n
			<option value='86400' ".$selectedday.">".$ibforums->lang['acp_days']."</option>\n
			</select>\n
			</div>";
	print_cells_row($output);

	print_input_row($ibforums->lang['acp_main_games_new'], 'games_new', $arcade['games_new'], 0);
	print_input_row($ibforums->lang['acp_main_games_popular'], 'games_popular', $arcade['games_popular'], 0);

	print_select_row($ibforums->lang['acp_main_score_type'], 'score_type', array('all' => $ibforums->lang['acp_allscores'], 'top' => $ibforums->lang['acp_bestscores']), $arcade['score_type']);
	print_select_row($ibforums->lang['acp_main_scores_sep'], 'score_sep', array('0' => $ibforums->lang['acp_main_seperator1'], ',' => $ibforums->lang['acp_main_seperator2'], ' ' => $ibforums->lang['acp_main_seperator3'], '.' => $ibforums->lang['acp_main_seperator4']), $arcade['score_sep']);
	//print_input_row($ibforums->lang['acp_main_decimal_amount'], 'dec_amount', $arcade['dec_amount'], 0);
	print_yes_no_row($ibforums->lang['acp_main_show_crowns'], 'show_crowns', $arcade['show_crowns']);
	print_yes_no_row($ibforums->lang['acp_main_show_t_won'], 'show_t_won', $arcade['show_t_won']);
	print_select_row($ibforums->lang['acp_main_crown_type'], 'crown_type', array('0' => "Standard", '1' => $ibforums->lang['acp_marquee_hori'], '2' => $ibforums->lang['acp_marquee_verti'], '3' => $ibforums->lang['acp_nomarquee'], '4' => $ibforums->lang['acp_trophy']), $arcade['crown_type']);
	print_select_row($ibforums->lang['acp_main_notification'], 'notification', array('none' => $ibforums->lang['acp_main_notify_pm1'], 'pm' => $ibforums->lang['acp_main_notify_pm2'], 'mail' => $ibforums->lang['acp_main_notify_pm3'], 'pm+mail' => $ibforums->lang['acp_main_notify_pm4']), $arcade['notification']);
	print_yes_no_row($ibforums->lang['acp_main_use_cats'], 'use_cats', $arcade['use_cats']);
	print_input_row($ibforums->lang['acp_main_cats_per_tr'], 'cats_per_tr', $arcade['cats_per_tr'], 0);

	$DB->query("SELECT * FROM ibf_games_cats WHERE active=1 ORDER BY pos, cat_name");
	$cat_list = array();
	while( $CAT = $DB->fetch_row() )
	{
		$cat_list[$CAT['c_id']] = $CAT['cat_name'];
	}

	print_select_row($ibforums->lang['acp_main_def_cat'], 'def_cat', $cat_list, $arcade['def_cat']);
	print_yes_no_row($ibforums->lang['acp_main_use_announce'], 'use_announce', $arcade['use_announce']);
	print_textarea_row($ibforums->lang['acp_main_announce'], 'announcement', $arcade['announcement'], 6, 45);
	print_yes_no_row($ibforums->lang['acp_main_log'], 'log', $arcade['log']);
	if ($arcade['htmltitle']=="") { $arcade['htmltitle'] = "%FORUMNAME% - %IBPRO% - %ACTION%"; }
	print_input_row("HTML-Title format<dfn>%FORUMNAME% = your forums name<br />%IBPRO% = Arcade (in your language)<br />%ACTION% = current site within ibProArcade, should be included for SEO-optimization!</dfn>", 'htmltitle', $arcade['htmltitle'], 0);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_main_prune_header']);
	print_yes_no_row($ibforums->lang['acp_main_prune_auto'], 'auto_prune', $arcade['auto_prune']);

	$selectedhour = ""; $selectedday = ""; $selectedmonth = "";
	if ($arcade['auto_prune_time2'] == "3600") { $selectedhour = "selected='selected'"; } 
	if ($arcade['auto_prune_time2'] == "86400") { $selectedday = "selected='selected'"; } 
	if ($arcade['auto_prune_time2'] == "2592000") { $selectedmonth = "selected='selected'"; } 
	$output = array();
	$output[] = $ibforums->lang['acp_main_prune_time1'];
	$output[] = "	<div align='left'>
			<input type='text' name='auto_prune_time' size='3' class='textinput' value='".$arcade['auto_prune_time']."'>\n
			<select name='auto_prune_time2' class='dropdown'>\n
			<option value='3600' ".$selectedhour.">".$ibforums->lang['acp_hours']."</option>\n
			<option value='86400' ".$selectedday.">".$ibforums->lang['acp_days']."</option>\n
			<option value='2592000' ".$selectedmonth.">".$ibforums->lang['acp_months']."</option>\n
			</select>\n
			</div>";
	print_cells_row($output);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_main_online_header']);
	print_yes_no_row($ibforums->lang['acp_main_online_main'], 'show_active_glist', $show_active['glist']);
	print_yes_no_row($ibforums->lang['acp_main_online_game'], 'show_active_play', $show_active['play']);
	print_yes_no_row($ibforums->lang['acp_main_online_tourney'], 'show_active_playtourney', $show_active['playtourney']);
	print_yes_no_row($ibforums->lang['acp_main_online_comment'], 'show_active_newscore', $show_active['newscore']);
	print_yes_no_row($ibforums->lang['acp_main_online_scores'], 'show_active_stats', $show_active['stats']);
	print_yes_no_row($ibforums->lang['acp_main_online_tourna1'], 'show_active_viewtournaments', $show_active['viewtournaments']);
	print_yes_no_row($ibforums->lang['acp_main_online_tourna2'], 'show_active_viewtourney', $show_active['viewtourney']);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_main_league_header']);
	for ($count=0; $count<10; $count++)
	{
		print_input_row($ibforums->lang['acp_main_league_rank'].($count+1), "league_score".$count, $arcade['league_scores'][$count], 0);
		construct_hidden_code("oldleague".$count, $arcade['league_scores'][$count]);
	}
	print_input_row($ibforums->lang['acp_main_league_last'], 'league_score10', $arcade['league_scores'][10], 0);
	construct_hidden_code("oldleague10", $arcade['league_scores'][10]);
	construct_hidden_code("old_language", $arcade['arcade_language']);
	construct_hidden_code("old_scoretype", $arcade['score_type']);

	$vbversion = substr($vboptions[templateversion],0,3);
	if ($vbversion != "3.0")
	{
		($hook = vBulletinHook::fetch_hook('ibproarcade_acp_mainsettings')) ? eval($hook) : false;
	}

	print_submit_row($ibforums->lang['acp_save_settings'], 0);
	print_cp_footer();
	exit;
}

// ################################
// store main settings to DB
// ################################
if ($action == "updatesettings")
{
	global $IN;

/*	$IN[glist] = $show_active[glist];
	$IN[newscore] = $show_active[newscore];
	$IN[stats] = $show_active[stats];
	$IN[viewtournaments] = $show_active[viewtournaments];
	$IN[playtourney] = $show_active[playtourney];
	$IN[viewtourney] = $show_active[viewtourney];
*/
	// Überprüfung der Werte auf Plausibilität
        $IN['scores_amount'] = intval($IN['scores_amount']);

	if ( empty($IN['arcade_language']) )
	{
		// default language is english
		$IN['arcade_language'] = "en";
	}

        if( empty($IN['scores_amount']) || $IN['scores_amount'] < 1 || $IN['scores_amount'] > 100)
     	{
        	$IN['scores_amount']=10;
        }

	$changedleague = false;

	for ($x=0; $x<=10; $x++)
	{
		$arcade['league_scores'][$x] = $IN["league_score".$x];
		if ($IN["league_score".$x] != $IN["oldleague".$x])
		{
			$changedleague = true;
		}
	}
	$arcade['league_scores'] = implode("," ,$arcade['league_scores']);

        	if( empty($IN['time_frame_num']) || !is_numeric($IN['time_frame_num']) || $IN['time_frame_num'] < 0 )
        	{
        		$IN['time_frame_num'] = 0;
        	}

        	$IN['auto_prune_time'] = intval($IN['auto_prune_time']);
        	if( $IN['auto_prune_time'] < 0 )
        	{
        		$IN['auto_prune_time'] = 0;
        	}

        	$IN['games_pp'] = intval($IN['games_pp']);
        	if( ($IN['games_pp'] < 1) || ($IN['games_pp'] > 200))
        	{
        		$IN['games_pp'] = 200;
        	}

        	$IN['cats_per_tr'] = intval($IN['cats_per_tr']);
        	if( $IN['cats_per_tr'] < 0 )
        	{
        		$IN['cats_per_tr'] = 0;
        	}

        	$IN['dec_amount'] = intval($IN['dec_amount']);
        	if( $IN['dec_amount'] < 0 )
        	{
        		$IN['dec_amount'] = 0;
        	}

        	$IN['games_pr'] = intval($IN['games_pr']);
        	if( $IN['games_pr'] < 1 )
        	{
        		$IN['games_pr'] = 1;
        	}

		if ($IN['games_new'] < 1)
		{
			$IN['games_new'] = 1;
		}

		if ($IN['games_popular'] < 1)
		{
			$IN['games_popular']=1;
		}

		$IN['scoretimeout'] = intval($IN['scoretimeout']);
		if ($IN['scoretimeout'] < 1)
		{
			$IN['scoretimeout'] = 1;
		}

	$announcement	= $IN['announcement'];	

	if ($vbversion == "3.0")
	{
		require_once('./includes/functions_bbcodeparse.php');		
		$announcement_parsed = parse_bbcode($IN['announcement']);
	}
	else
	{
		require_once('./includes/class_bbcode.php');
		$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
		$announcement_parsed = $bbcode_parser->parse($IN['announcement'],0,1);
	}

        	$show_active            	= array();
        	$tmp                 		= array();
        	$tmp['glist']           		= $IN['show_active_glist'];
        	$tmp['play']            		= $IN['show_active_play'];
        	$tmp['newscore']        	= $IN['show_active_newscore'];
        	$tmp['stats']		= $IN['show_active_stats'];
        	$tmp['viewtournaments']	= $IN['show_active_viewtournaments'];
        	$tmp['playtourney']     	= $IN['show_active_playtourney'];
        	$tmp['viewtourney']     	= $IN['show_active_viewtourney'];
        	$show_active 		= serialize($tmp);

		$queryprepare = array (	'arcade_status'     	=> $IN['arcade_status'],
					'arcade_language'	=> $IN['arcade_language'],
                                        'g_display_sort'    	=> $IN['g_display_sort'],
					'g_display_order'   	=> $IN['g_display_order'],
					'scores_amount'     	=> $IN['scores_amount'],
					'score_type'        	=> $IN['score_type'],
					'log'              	=> $IN['log'],
					'skin'              	=> $IN['skin'],
					'use_cats'          	=> $IN['use_cats'],
					'crown_type'        	=> $IN['crown_type'],
					'notification'		=> $IN['notification'],
					'show_new'          	=> $IN['show_new'],
					'show_new_frame'	=> $IN['show_new_frame'],
					'show_active'      	=> $show_active,
					'auto_prune'        	=> $IN['auto_prune'],
					'auto_prune_time'   	=> $IN['auto_prune_time'],
					'auto_prune_time2'	=> $IN['auto_prune_time2'],
					'games_pr'		=> $IN['games_pr'],
					'games_pp'		=> $IN['games_pp'],
					'user_choices'		=> $IN['user_choices'],
					'allow_user_skin'	=> $IN['allow_user_skin'],
					'def_cat'		=> $IN['def_cat'],
					'cats_per_tr'		=> $IN['cats_per_tr'],
					'show_crowns'		=> $IN['show_crowns'],
					'show_t_won'		=> $IN['show_t_won'],
					'score_sep'		=> $IN['score_sep'],
					'dec_amount'		=> $IN['dec_amount'],
					'league_scores'		=> $arcade['league_scores'],
					'use_announce'		=> $IN['use_announce'],
					'announcement'		=> $announcement,
					'announcement_parsed' 	=> $announcement_parsed,
					'games_new' 		=> $IN['games_new'],
					'games_popular' 	=> $IN['games_popular'],
					'scoretimeout'  => $IN['scoretimeout'],
					'htmltitle'     => addslashes($IN['htmltitle'])
					);

		$vbversion = substr($vboptions[templateversion],0,3);
		if ($vbversion != "3.0")
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_acp_mainsettings_query')) ? eval($hook) : false;
		}

        	$db_string = $DB->compile_db_update_string($queryprepare);

        	$DB->query("UPDATE ibf_games_settings SET ".$db_string);

	/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("");
        	}
	*/

	if (($IN['old_scoretype'] != $IN['score_type']) && ($IN['score_type']=="top"))
	{
		// prune scores, just keep the top per game from each player
	}

	if ($IN['old_language'] != $IN['arcade_language'])
	// language changed, so update the vB-phrases for leftside menu :-)
	{
		// read new languagefile
		$languageid = $IN['arcade_language'];
		$langfile = "lang_Arcade_".$languageid;
		$ibforums->lang = $std->load_words($ibforums->lang, $langfile, $ibforums->lang_id );

		// check if file is valid and compatible
		if ($ibforums->lang['acp_on'] == "")
		{
			// incompatible language-file, load english (default)
			$ibforums->lang = $std->load_words($ibforums->lang, 'lang_Arcade_en', $ibforums->lang_id );
		}

		// update phrases to fit the new language
	      	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu0'].'" WHERE varname="ibparcade_acpmenu0"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu1'].'" WHERE varname="ibparcade_acpmenu1"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu2'].'" WHERE varname="ibparcade_acpmenu2"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu3'].'" WHERE varname="ibparcade_acpmenu3"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu4'].'" WHERE varname="ibparcade_acpmenu4"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu5'].'" WHERE varname="ibparcade_acpmenu5"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu6'].'" WHERE varname="ibparcade_acpmenu6"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu7'].'" WHERE varname="ibparcade_acpmenu7"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu8'].'" WHERE varname="ibparcade_acpmenu8"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu9'].'" WHERE varname="ibparcade_acpmenu9"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu10'].'" WHERE varname="ibparcade_acpmenu10"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['acp_menu11'].'" WHERE varname="ibparcade_acpmenu11"');

        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['wol_home'].'" WHERE varname="ibproarcade_home"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['wol_play'].'" WHERE varname="ibproarcade_playing_game"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['wol_viewscore'].'" WHERE varname="ibproarcade_viewing_highscores"');
        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['wol_viewhome'].'" WHERE varname="ibproarcade_viewing_home"');

        	$DB->query('UPDATE ibf_phrase SET text="'.$ibforums->lang['postbit_tourney'].'" WHERE varname="ibpa_tourney"');

		// make sure to rebuild the language after saving data
		$finalcommand = "language.php?do=rebuild&goto=arcade.php?code=settings";

		if ($changedleague)
		{
			// update league after rebuilding language
			$finalcommand = "language.php?do=rebuild&goto=arcade.php?code=tool_league";
		}

		define('CP_REDIRECT', $finalcommand);
		print_stop_message('saved_settings_successfully');
	}

	if ($changedleague)
	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'tool_league');
		print_table_header($ibforums->lang['acp_gamesort_err_header']);
		print_description_row($ibforums->lang['acp_main_updleague']);
		print_submit_row($ibforums->lang['acp_main_league_button'], 0);
		print_cp_footer();
		exit;
	}
	else
	{
		define('CP_REDIRECT', 'arcade.php?code=settings');
		print_stop_message('saved_settings_successfully');
	}
}



// ##############################
// show games
// ##############################
if ($action == "gamelist")
{
	global $IN, $std, $vbulletin, $vboptions;

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'games_setmulticat');
	print_table_header($ibforums->lang['acp_games_header_az']);

	$extrainfo = "";
        	if( (isset($IN['cat'])) && ($IN['cat'] == 0) )
	{
		$extrainfo = "<br /><b>".$ibforums->lang['acp_games_headerinfo2']."</b>";
	}

	print_description_row("<div align='center'>".$ibforums->lang['acp_games_headerinfo'].$extrainfo."</div>");

	$the_links = "";

		// additional Sort-Options
		$the_links .= "<dfn>";
		$prefix = "<span style='color: red;'><b>"; $suffix="</b></span>";
		$separator = " <b>|</b> ";
		$link1 = "<a href='arcade.php?$session[sessionurl]&amp;code=gamelist&amp;sort=";
		$link2 = "'>";
		$link3 = "</a>";

		$type = "dateasc";
		$text = $ibforums->lang['acp_games_filter1'];
		if ($IN['sort']==$type)
		{
			$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
		}
		else
		{
			$the_links.= $link1.$type.$link2.$text.$link3;
		}
		$the_links.=$separator;

		$type = "datedesc";
		$text = $ibforums->lang['acp_games_filter2'];
		if ($IN['sort']==$type)
		{
			$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
		}
		else
		{
			$the_links.= $link1.$type.$link2.$text.$link3;
		}
		$the_links.=$separator;

		$type = "timesplayed";
		$text = $ibforums->lang['acp_games_filter3'];
		if ($IN['sort']==$type)
		{
			$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
		}
		else
		{
			$the_links.= $link1.$type.$link2.$text.$link3;
		}
		$the_links.=$separator;

		$type = "inactive";
		$text = $ibforums->lang['acp_games_filter4'];
		if ($IN['sort']==$type)
		{
			$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
		}
		else
		{
			$the_links.= $link1.$type.$link2.$text.$link3;
		}

		// detect vBplaza
		if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
		{
			$the_links.="<br />";

			$type = "costs";
			$text = $ibforums->lang['acp_games_filter5'];
			if ($IN['sort']==$type)
			{
				$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
			}
			else
			{
				$the_links.= $link1.$type.$link2.$text.$link3;
			}
			$the_links.=$separator;

			$type = "jpraise";
			$text = $ibforums->lang['acp_games_filter6'];
			if ($IN['sort']==$type)
			{
				$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
			}
			else
			{
				$the_links.= $link1.$type.$link2.$text.$link3;
			}
			$the_links.=$separator;

			$type = "jpstatic";
			$text = $ibforums->lang['acp_games_filter7'];
			if ($IN['sort']==$type)
			{
				$the_links.= $link1.$type.$link2.$prefix.$text.$suffix.$link3;
			}
			else
			{
				$the_links.= $link1.$type.$link2.$text.$link3;
			}
		}

		$vbversion = substr($vboptions[templateversion],0,3);
		if ($vbversion != "3.0")
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_acp_gamelist')) ? eval($hook) : false;
		}

		$the_links .= "</dfn><hr>";

        	$alphabet = array( 	"0-9" , "A" , "B" , "C" , "D" , "E" , "F" , "G" , "H" ,
                           		"I" , "J" , "K" , "L" , "M" , "N" , "O" , "P" , "Q" ,
                           		"R" , "S" , "T" , "U" , "V" , "W" , "X" , "Y" , "Z" );
        	foreach( $alphabet as $letter )
        	{
        		$prefix = "";
            		$suffix = "";
            		if( $letter == $IN['filter'] )
            		{
            			$prefix = "<span style='color: red;'><b>";
            			$suffix = "</b></span>";
            		}
            		$the_links .= "&nbsp;&nbsp;<a href='arcade.php?$session[sessionurl]&amp;code=gamelist&amp;filter=".$letter."'>".$prefix.$letter.$suffix."</a>";
        	}

        	if( isset($IN['filter']) || isset($IN['cat']) || isset($IN['sort']) )
        	{
            		$the_links .= "&nbsp;&nbsp;<a href='arcade.php?$session[sessionurl]&amp;code=gamelist'>".$ibforums->lang['acp_all']."</a>";
        	}
        	else
        	{
        		$the_links .= "&nbsp;&nbsp;<a href='arcade.php?$session[sessionurl]&amp;code=gamelist'><span style='color: red;'><b>".$ibforums->lang['acp_all']."</b></span></a>";
        	}

        	if( $arcade['use_cats'] )
        	{
        		$the_links .= "<br /><hr>";
			$charcount=0;
        		$DB->query("SELECT c.c_id, c.cat_name, c.pos, c.password, count(g.gid) AS howmany FROM ibf_games_cats AS c LEFT JOIN ibf_games_list AS g ON (g.gcat=c.c_id) WHERE c.show_all<>1 GROUP BY g.gcat ORDER BY c.pos, c.cat_name");
            		while( $CAT = $DB->fetch_row() )
            		{
            			$prefix = "";
            			$suffix = "";

            			if( $CAT['c_id'] == $IN['cat'] )
            			{
            				$prefix = "<span style='color: red;'><b>";
            				$suffix = "</b></span>";
            			}

				if ( $CAT['password'] != "" )
				{
					$prefix .= "<i>";
					$suffix = "</i>".$suffix;
				}

				$suffix = " <span class='smallfont'>(".$CAT['howmany'].")</span>".$suffix;

            			$the_links .= "&nbsp;&nbsp;<a href='arcade.php?$session[sessionurl]&amp;code=gamelist&amp;cat=".$CAT['c_id']."'>".$prefix.$CAT['cat_name'].$suffix."</a>";

				$charcount = $charcount + strlen($CAT['cat_name']);
				if ($charcount > 60)	// max. 60 characters per row
				{
					$the_links .= "<br />";
					$charcount = 0;
				} 
            		}
        	}

	print_description_row("<div align='center'>".$the_links."</div>");

	print_table_break('', "90%");

	$header = array();
	$header[] = $ibforums->lang['acp_gamesort_icon'];
	$header[] = $ibforums->lang['acp_gamesort_game'];
	$header[] = $ibforums->lang['acp_gamesort_info'];
	$header[] = $ibforums->lang['acp_gamesort_cat'];
	$header[] = $ibforums->lang['acp_gamesort_active'];
	$header[] = $ibforums->lang['acp_gamesort_tourney'];
	$header[] = $ibforums->lang['acp_gamesort_edit'];
	$header[] = $ibforums->lang['acp_gamesort_delete'];
	$header[] = $ibforums->lang['acp_result_empty'];

	$colspan = sizeof($header);
	print_table_header($ibforums->lang['acp_games_header'], $colspan);
	print_cells_row($header, 1);

        	$status['color'] = array( 0 => "<span style='color: red;'><b>".$ibforums->lang['acp_games_active_n']."</b></span>", 1 => "<span style='color: #000000'>".$ibforums->lang['acp_games_active_y']."</span>" );

        	$query_extra = "";
        	if( isset($IN['filter']) )
        	{
        		if( $IN['filter'] != "0-9" )
            		{
            			$query_extra = "WHERE gtitle LIKE '".$IN['filter']."%'";
            		}
            		else
            		{
            			$query_extra = "WHERE gtitle REGEXP '^[0-9]'";
            		}
        	}
        
		if( isset($IN['cat']) )
        	{
        		$query_extra = "WHERE gcat=".$IN['cat'];
        	}

		if ((isset($IN['cat'])) || (isset($IN['filter'])))
		{
			if ($IN['sort']=="inactive")
			{
				// although multiple filters are not clickable, but who knows ;)
				$query_extra .= " AND active<>1";
			}
		}
		else
		{
			if ($IN['sort']=="inactive")
			{
				$query_extra = "WHERE active<>1";
			}
		}

        	$counter = 0;
        	//$DB->query("SELECT * FROM ibf_games_cats WHERE show_all<>1 ORDER BY pos, cat_name");
        	$DB->query("SELECT * FROM ibf_games_cats ORDER BY pos, cat_name");
        	$cat_list = array();
        	while( $CAT = $DB->fetch_row() )
        	{
        		$cat_list[$counter] = array( $CAT['c_id'] , $CAT['cat_name'] , $CAT['password'] );
            		$counter++;
        	}

		$sortextra = "";
		if (($IN['sort']=="dateasc") || ($IN['sort']=="datedesc") || ($IN['sort']=="timesplayed") || ($IN['sort']=="inactive") || ($IN['sort']=="costs") || ($IN['sort']=="jpraise") || ($IN['sort']=="jpstatic"))
		{
			if ($IN['sort']=="dateasc") { $sortextra = "added ASC,"; }
			if ($IN['sort']=="datedesc") { $sortextra = "added DESC,"; }
			if ($IN['sort']=="timesplayed") { $sortextra = "gcount DESC,"; }
			if ($IN['sort']=="costs") { $sortextra = "cost DESC,"; }

			if ($IN['sort']=="jpraise")
			{
				$sortextra = "jackpot DESC,";
				if ($query_extra=="") 	{ $query_extra ="WHERE jackpot_type='-1'";}
				else			{ $query_extra.=" AND jackpot_type='-1'";}
			}

			if ($IN['sort']=="jpstatic")
			{
				$sortextra = "jackpot_type DESC,";
				if ($query_extra=="") 	{ $query_extra ="WHERE jackpot_type<>'-1'";}
				else			{ $query_extra.=" AND jackpot_type<>'-1'";}
			}
		}

		$counter=1;
        	$this_query = $DB->query("SELECT * FROM ibf_games_list ".$query_extra." ORDER BY ".$sortextra."gtitle");
        	while( $GAME = $DB->fetch_row($this_query) )
        	{
			$counter++;
			$filesize = "";
            		if( $GAME['filesize'] )
            		{
            			$filesize = $std->size_format($GAME['filesize']);
            		}
            		else
            		{
            			$filesize = "---";
            		}

        		$top['score'] = "---";
            		$top['name'] = "---";

            		$score_query = $DB->query("SELECT * FROM ibf_games_champs WHERE champ_gid=".$GAME['gid']);
            		if( $DB->get_num_rows($score_query) )
            		{
            			$top = $DB->fetch_row();
// Punkte formatieren ?
                		$top['score'] = $top['champ_score'];
                		$top['name'] = "<a href='../index.php?showuser=".$top['champ_mid']."' target='_blank'>".$top['champ_name']."</a>";
            		}

			$playlink1 = "<a href='../index.php?act=Arcade&amp;do=play&amp;gameid=".$GAME['gid']."' target='_blank' title='".$GAME['gwords']."'>";
			$playlink2 = "</a>";
            		$status['play_link'] = ($GAME['active']) ? $playlink1.$GAME['gtitle'].$playlink2 : $playlink1."<i>".$GAME['gtitle']."</i>".$playlink2;

		$catname = "---";
		$catpassed = false;
		foreach ($cat_list as $k => $v)
		{
			if ($v[0] == $GAME['gcat'])
			{
				$catname = $v[1];
				if ($v[2] != "")
				{
					$catpassed = true;
				}
			}
		}
		$catpasspre = ""; $catpasssuf = "";
		if ($catpassed)
		{
			$catpasspre = "<i>";
			$catpasssuf = "</i>";
		}

		$costinfo = "";
		$jackpotinfo = "";

		// detect vBplaza
		if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
		{
			$costinfo = $ibforums->lang['costs_pgame'].$GAME['cost']."<br />";
			if (intval($GAME['jackpot_type'])>=0)
			{
				// static Jackpot
				$jackpotinfo = "<div align='center'><dfn>Jackpot ".$ibforums->lang['acp_game_jack_static']."<br />".$GAME['jackpot_type']."</dfn></div>";
			}
			else
			{
				$jackpotinfo = "<div align='center'><dfn>Jackpot ".$ibforums->lang['acp_game_jack_raise']."<br />".$GAME['jackpot']."</dfn></div>";
			}
		}

		$vbversion = substr($vboptions[templateversion],0,3);
		if ($vbversion != "3.0")
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_acp_gamebit')) ? eval($hook) : false;
		}

		$hscorename = "";
		if ($top['score']!="---")
		{
			$hscorename = " (".$top['name'].")";
		}

		$securegame = "";
//		if ($GAME['game_type']==1)
		if ( ( file_exists(ROOT_PATH.'arcade/gamedata/'.$GAME['gname'].'/v32game.txt' ) ) || ( file_exists(ROOT_PATH.'arcade/gamedata/'.$GAME['gname'].'/v3game.txt' ) ) )
		{
			$securegame = "<img src='../arcade/images/secure.gif' alt='secure' />";
		}

		$filesize="";
		if ($GAME['filesize'] > 0)
		{
			$filesize = "<hr /><dfn>".$std->size_format($GAME['filesize'])."</dfn>";
		}

		$cell = array();
		$cell[] = "<div align='center'>".$playlink1."<img src='../arcade/images/".$GAME['gname']."1.gif' alt='".$GAME['gtitle']."' title='".$GAME['gtitle']."' border=0 width=50 height=50 />".$playlink2."</div>";
		$cell[] = "<div align='center' style='font-weight: bold'>".$status['play_link']."</a></div>".$jackpotinfo;
		$cell[] = "<div align='left'><dfn>".$ibforums->lang['acp_games_info1'].": ".$top['score'].$hscorename."<br />".$ibforums->lang['acp_games_info3'].": ".$GAME['gcount']."<br />".$costinfo.$ibforums->lang['acp_games_info4'].": ".timeoutput($GAME['added'])."</dfn></div>";
		$cell[] = "<dfn>".$catpasspre.$catname.$catpasssuf."</dfn>".$securegame;
		$cell[] = $status['color'][$GAME['active']];
		$cell[] = $status['color'][$GAME['tourney_use']];
		$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=editgame&amp;gid=".$GAME['gid']."'>".$ibforums->lang['acp_games_editgame']."</a><hr style='color: red;' />
                                               <a href='arcade.php?$session[sessionurl]&amp;code=scores&amp;do=game&amp;gid=".$GAME['gid']."'>".$ibforums->lang['acp_games_editscores']."</a></div>";
		$cell[] = "<a href='arcade.php?$session[sessionurl]&amp;code=del&amp;gid=".$GAME['gid']."'>".$ibforums->lang['acp_games_delete']."</a>".$filesize;
		$cell[] = "<input type='checkbox' name='move_to[]' value='".$GAME['gid']."'>";	
		print_cells_row($cell);
	}

	// line for CHECK ALL
	$counter++;
	$selectall = "<div align='center'><a href='#' onclick=\"check_all('".$counter."'); return false;\">".$ibforums->lang['acp_selectall']."</a> | <a href='#' onclick=\"uncheck_all('".$counter."'); return false;\">".$ibforums->lang['acp_deselectall']."</a></div>";
	$counter--;
	$javascript = "	<script language='javascript'>
			<!--
			function check_all(amount)
			{
				var box_form = document.forms[0];
				var a = '';
				for ( a = 1 ; a < amount ; a++ )
				{
					box_form.elements[a].checked = true;
				}
			}

			function uncheck_all(amount)
			{
				var box_form = document.forms[0];
				var a = '';
				for ( a = 1 ; a < amount ; a++ )
				{
					box_form.elements[a].checked = false;
				}
			}
			-->
			</script>";

	if ($counter>1)
	{
		print_description_row($javascript.$selectall,0,$colspan);
	}

	print_table_break('', "90%");

	$colspan=0;
	if ($counter > 1)
	{
		$header = array();
		$header[] = "<div align='right'>".$ibforums->lang['acp_games_multibox1']."</div>";
		$header[] = "<div align='left'>".$ibforums->lang['acp_games_multibox2']."</div>";
		$header[] = "<div align='left'>".$ibforums->lang['acp_games_multibox3']."</div>";

		$colspan = sizeof($header);
		print_table_header($ibforums->lang['acp_games_multibox'], $colspan);
		print_cells_row($header, 1);

		$dropdown = "<select name='put_in_cat' class='dropdown'>\n";
		foreach ($cat_list as $k => $v)
		{
			$dropdown = $dropdown . "<option value='".$v[0]."' ";
			if ($v[0] == 1) { $dropdown = $dropdown . "selected='selected' "; }
			$dropdown = $dropdown . ">".$v[1]."</option>\n";
		}
		$dropdown = $dropdown . "</select>\n";

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_movecat' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_games_movetocat1']."</div>";
		$cell[] = "<div align='left'>".$dropdown.$ibforums->lang['acp_games_movetocat2']."</div>";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_active' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_active']."</div>";
		$cell[] = "<div align='left'><select name='select_active' class='dropdown'>\n
				<option value='1' selected='selected'>".$ibforums->lang['acp_on']."</option>\n
				<option value='0'>".$ibforums->lang['acp_off']."</option>\n
				</select></div>";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_tourney' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_tourney']."</div>";
		$cell[] = "<div align='left'><select name='select_tourney' class='dropdown'>\n
				<option value='1' selected='selected'>".$ibforums->lang['acp_on']."</option>\n
				<option value='0'>".$ibforums->lang['acp_off']."</option>\n
				</select></div>";
		print_cells_row($cell);

		// detect vBplaza
		if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
		{
			$cell = array();
			$cell[] = "<div align='right'><input type='checkbox' name='opt_costs' value='1'></div>";
			$cell[] = "<div align='left'>".$ibforums->lang['acp_game_costs']."</div>";
			$cell[] = "<div align='left'><input type='text' name='select_costs' size='10' class='textinput' value='0'></div>";
			print_cells_row($cell);

			$cell = array();
			$cell[] = "<div align='right'><input type='checkbox' name='opt_jackpottype' value='1'></div>";
			$cell[] = "<div align='left'>".$ibforums->lang['acp_game_jackpottype']."</div>";
			$cell[] = "<div align='left'><select name='select_jackpottype' class='dropdown'>\n
					<option value='1'>".$ibforums->lang['acp_game_jack_raise']."</option>\n
					<option value='0' selected='selected'>".$ibforums->lang['acp_game_jack_static']."</option>\n
					</select></div>";
			print_cells_row($cell);

			$cell = array();
			$cell[] = "<div align='right'><input type='checkbox' name='opt_jackpotstatic' value='1'></div>";
			$cell[] = "<div align='left'>".$ibforums->lang['acp_game_static']."</div>";
			$cell[] = "<div align='left'><input type='text' name='select_jackpotstatic' size='10' class='textinput' value='0'></div>";
			print_cells_row($cell);

			$cell = array();
			$cell[] = "<div align='right'><input type='checkbox' name='opt_jackpot' value='1'></div>";
			$cell[] = "<div align='left'>".$ibforums->lang['acp_game_jackpot']."</div>";
			$cell[] = "<div align='left'><input type='text' name='select_jackpot' size='10' class='textinput' value='0'></div>";
			print_cells_row($cell);
		}

		$vbversion = substr($vboptions[templateversion],0,3);
		if ($vbversion != "3.0")
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_acp_gameoptions')) ? eval($hook) : false;
		}

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_resettimes' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_resettimes']."</div>";
		$cell[] = "&nbsp;";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_resettime' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_resettime']."</div>";
		$cell[] = "&nbsp;";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_resetscores' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_resetscores']."</div>";
		$cell[] = "&nbsp;";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_resetbest' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_resetbest']."</div>";
		$cell[] = "&nbsp;";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_filesize' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_resetsize']."</div>";
		$cell[] = "&nbsp;";
		print_cells_row($cell);

		$cell = array();
		$cell[] = "<div align='right'><input type='checkbox' name='opt_delgame' value='1'></div>";
		$cell[] = "<div align='left'>".$ibforums->lang['acp_game_delgame']."</div>";
		$cell[] = "&nbsp;";
		print_cells_row($cell);
	}

	print_submit_row($ibforums->lang['acp_start'], 0, $colspan);
	print_cp_footer();
	exit;
}


// ##############################
// set options for multiple games 
// ##############################
if ($action == "games_setmulticat")
{
	global $IN;

	// get the GameIDs that where selected
       	$cat_string = "(0)";
       	if( !empty($IN['move_to']) )
       	{
       		$cat_string = "(".implode("," , $IN['move_to']).")";
       	}

	// move to category
	if ($IN['opt_movecat']==1)
	{
        	$db_string = $DB->compile_db_update_string( array (	'gcat'   => $IN['put_in_cat'] ) );

        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);

        	$new_amounts = array();

        	$this_query = $DB->query("SELECT c_id, show_all FROM ibf_games_cats ORDER BY c_id");
        	while( $CAT = $DB->fetch_row($this_query) )
        	{
        		$query_extra = "";
        		if( !$CAT['show_all'] )
            		{
				$query_extra = "AND gcat=".$CAT['c_id'];
            		}
    			$DB->query("SELECT COUNT(gid) AS amount FROM ibf_games_list WHERE active=1 ".$query_extra);
	            	$the = $DB->fetch_row();
	            	$new_amounts[ $CAT['c_id'] ] = $the['amount'];
        	}

        	foreach( $new_amounts as $cat=>$amount )
        	{
            		$db_string = $DB->compile_db_update_string( array ( 'num_of_games' => $amount ) );
            		$DB->query("UPDATE ibf_games_cats SET ".$db_string." WHERE c_id='".$cat."'");
        	}
	}

	// set active on/off
	if ($IN['opt_active']==1)
	{
		$db_string = $DB->compile_db_update_string( array (	'active'   => $IN['select_active'] ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
		updateleague();
	}

	// set tournament-game on/off
	if ($IN['opt_tourney']==1)
	{
		$db_string = $DB->compile_db_update_string( array (	'tourney_use'   => $IN['select_tourney'] ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}

	// filesize
	if ($IN['opt_filesize']==1)
	{
	        $games = array();
	        $getgames=$DB->query("SELECT gid, gname FROM ibf_games_list WHERE gid IN ".$cat_string);
	        while( $GAME = $DB->fetch_row($getgames) )
	        {
	        	$games[] = array('gid'	=> $GAME['gid'], 'gname' => $GAME['gname']);
        	}

	        $dir = getcwd()."/arcade";

		foreach ($games AS $this_game)
		{
			$filesize=0;
			$file = $dir."/".$this_game['gname'].".swf";
			if(file_exists($file))
			{
				@chmod( $file , 0777 );
				$filesize = filesize($file);
			}
			$DB->query("UPDATE ibf_games_list SET filesize='".$filesize."' WHERE gid='".$this_game['gid']."'");

		}
	}

	// set costs
	if (($IN['opt_costs']==1) && (intval($IN['select_costs'])>=0))
	{
		$db_string = $DB->compile_db_update_string( array (	'cost'   => $IN['select_costs'] ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}

	// set static jackpot (if possible)
	$staticjp = false;
	if ($IN['opt_jackpotstatic']==1)
	{
		if (intval($IN['select_jackpotstatic'])>=0)
		{
			$staticjackpot = $IN['select_jackpotstatic'];
			$staticjp = true;
		}	
	}

	// set jackpot-type
	if ($IN['opt_jackpottype']==1)
	{
		if ($IN['select_jackpottype']==0)
		{
			// static Jackpot
			if ($staticjp)
			{
				$db_string = $DB->compile_db_update_string( array (	'jackpot_type'   => $IN['select_jackpotstatic'] ) );
			}
			else
			{
				$db_string = $DB->compile_db_update_string( array (	'jackpot_type'   => '0' ));
			}
		}
		else
		{
			// raising Jackpot
			$db_string = $DB->compile_db_update_string( array (	'jackpot_type'   => '-1' ) );
		}
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}
	else
	{
		// no changes on Jackpot-Type, so finally store static value if that was changed
		if ($staticjp)
		{
			// check if Jackpot-Type of that Game isn't set to RAISING (done via Query)
			$db_string = $DB->compile_db_update_string( array (	'jackpot_type'   => $staticjackpot ) );
        		$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE jackpot_type<>'-1' AND gid IN ".$cat_string);
		}
	}

	// set Jackpot (raising)
	if ($IN['opt_jackpot']==1)
	{
		$db_string = $DB->compile_db_update_string( array (	'jackpot'   => $IN['select_jackpot'] ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}

	// reset times played
	if ($IN['opt_resettimes']==1)
	{
		$db_string = $DB->compile_db_update_string( array (	'gcount'   => '0' ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}

	// reset time played
	if ($IN['opt_resettime']==1)
	{
		$db_string = $DB->compile_db_update_string( array (	'gtime'   => '0' ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}

	// reset scores
	if ($IN['opt_resetscores']==1)
	{
        	$DB->query("DELETE FROM ibf_games_scores WHERE gid IN ".$cat_string);
	}

	// reset best result ever
	if ($IN['opt_resetbest']==1)
	{
		$db_string = $DB->compile_db_update_string( array (	'bestscore'   => '0', 'bestmid' => '', 'besttime' => '0' ) );
        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid IN ".$cat_string);
	}

	// delete game from database
	if ($IN['opt_delgame']==1)
	{
		// look for existing tournaments of that Game
		$looktourney = $DB->query("SELECT tid FROM ibf_tournaments WHERE gid IN ".$cat_string);
		while ($tourney=$DB->fetch_row($looktourney))
		{
			$tid=$tourney[tid];
			$DB->query("DELETE FROM ibf_tournament_players WHERE tid=".$tid);
			$DB->query("DELETE FROM ibf_tournament_players_statut WHERE tid=".$tid);
			$DB->query("DELETE FROM ibf_tournaments WHERE tid=".$tid);
		}

            	$DB->query("DELETE FROM ibf_games_scores WHERE gid IN ".$cat_string);
		$DB->query("DELETE FROM ibf_games_league WHERE gid IN ".$cat_string);
     		$DB->query("DELETE FROM ibf_games_list WHERE gid IN ".$cat_string);

		do_champ_update(1);
		update_cat_game_nums();
		do_league_update(1);
	}

	/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("...");
        	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=gamelist&cat=0');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// categories
// ##############################
if ($action == "cat")
{
	global $IN;

	print_cp_header($ibforums->lang['acp_header']);

	$header = array();
	$header[] = $ibforums->lang['acp_catlist_sort'];
	$header[] = $ibforums->lang['acp_catlist_name']	;
	$header[] = $ibforums->lang['acp_catlist_newname'];
	$header[] = $ibforums->lang['acp_catlist_pass'];
	$header[] = $ibforums->lang['acp_catlist_icon'];
	$header[] = $ibforums->lang['acp_catlist_active'];
	$header[] = $ibforums->lang['acp_catlist_all'];
	$header[] = $ibforums->lang['acp_catlist_remove'];

	$colspan = sizeof($header);
	print_form_header('arcade', 'edit_categories');
	print_table_header($ibforums->lang['acp_cat_header1'], $colspan);
	print_cells_row($header, 1);

        	$DB->query("SELECT COUNT(c_id) AS many FROM ibf_games_cats");
        	$how = $DB->fetch_row();

        	$the_list = array();
        	for( $a = 0 ; $a < $how['many'] ; $a++)
        	{
        		$the_list[$a] = array( $a+1 , $a+1 );
        	}

        	$DB->query("SELECT * FROM ibf_games_cats ORDER BY pos");

        	while( $CAT = $DB->fetch_row() )
        	{
   		$remove_link = ( $CAT['c_id'] !=1 ) ? "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=do_cat_stuff&amp;do=del&amp;c=".$CAT['c_id']."'>".$ibforums->lang['acp_remove']."</a></div>" : "";

		$cell = array();

		$celloutput = "<div align='center'><select name='".$CAT['c_id']."_order' class='dropdown'>\n";
		foreach ($the_list AS $k => $v)
		{
			$selected = "";
			if ( ($CAT['pos'] != "") AND ($v[0] == $CAT['pos']) )
			{
				$selected = " selected";
			}
			$celloutput = $celloutput . "<option value='".$v[0]."'".$selected.">".$v[1]."</option>\n";
		}
		$celloutput = $celloutput . "</select></div>";

		$cell[] = $celloutput;
		$cell[] = "<div style='font-weight: bold;' align='center'>".$CAT['cat_name']."</div>";
		$cell[] = "<div align='center'><input type='text' name='name_".$CAT['c_id']."' value='' size='30' class='textinput'></div>";
		$cell[] = "<div align='center'><input type='text' name='pass_".$CAT['c_id']."' value='".$CAT['password']."' size='12' class='textinput'></div>";
		$cell[] = "<div align='center' class='smallfont'>".$CAT['c_id'].".gif</div>";

		$selected1 = "";
		$selected0 = "";
		if ($CAT['active'] == 1)
		{
			$selected1 = " selected";
		}
		else
		{
			$selected0 = " selected";
		}

		$cell[] = "	<div align='center'><select name='active_".$CAT['c_id']."' class='dropdown'>\n
				<option value='1'".$selected1.">".$ibforums->lang['acp_games_active_y']."</option>\n
				<option value='0'".$selected0.">".$ibforums->lang['acp_games_active_n']."</option>\n
				</select></div>";

		$selected1 = "";
		$selected0 = "";
		if ($CAT['show_all'] == 1)
		{
			$selected1 = " selected";
		}
		else
		{
			$selected0 = " selected";
		}

		$cell[] = "	<div align='center'><select name='show_".$CAT['c_id']."' class='dropdown'>\n
				<option value='1'".$selected1.">".$ibforums->lang['acp_games_active_y']."</option>\n
				<option value='0'".$selected0.">".$ibforums->lang['acp_games_active_n']."</option>\n
				</select></div>";

		$cell[] = $remove_link;
		print_cells_row($cell);
	}
	print_description_row($ibforums->lang['acp_cat_describeall'], 0, $colspan);
	print_submit_row($ibforums->lang['acp_save'], 0, $colspan);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_cat_header2']);
	print_form_header('arcade', 'add_category');
	print_input_row($ibforums->lang['acp_cat_newcat'], 'cat_name', '', 0);
	print_submit_row($ibforums->lang['acp_cat_newcatbutton'], 0);
	print_cp_footer();
	exit;
}



// ##############################
// do category function
// ##############################
if ($action == "do_cat_stuff")
{
	global $ADMIN, $IN, $DB, $INFO;

	$error = array();
	switch( $IN['do'] )
	{
		case add:
	            	$name = $IN['cat_name'];
        	    	if( empty($name) || strlen($name) > 32 || strlen($name) < 4 )
            		{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'cat');
				print_table_header($ibforums->lang['acp_tool_prune_errhead']);
				print_description_row($ibforums->lang['acp_cat_error1']);
				print_submit_row($ibforums->lang['acp_back'], 0);
				print_cp_footer();
				exit;
            		}

        		$DB->query("SELECT * FROM ibf_games_cats WHERE cat_name='".$name."'");
            		if( $DB->get_num_rows() )
            		{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'cat');
				print_table_header($ibforums->lang['acp_tool_prune_errhead']);
				print_description_row($ibforums->lang['acp_cat_error2_1'].$name.$ibforums->lang['acp_cat_error2_2']);
				print_submit_row($ibforums->lang['acp_back'], 0);
				print_cp_footer();
				exit;
            		}

	            	$db_string = $DB->compile_db_insert_string( array (	'cat_name'            => $name,
        	                                                         	'active'              => 1,
                	                                                 	'show_all'            => 0,
                        	                                         	'pos'                 => 1,
                                	                                 	'password'            => "",
										'description'	      => "",
                                        	                     		) );
            		$DB->query("INSERT INTO ibf_games_cats (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
            		break;

      		case edit:
                	$this_query = $DB->query("SELECT * FROM ibf_games_cats");
                	while( $this_cat = $DB->fetch_row($this_query) )
                	{
                		$query = "";
                    		$name = "";
                    		if( $this_cat['active'] != $IN[ "active_".$this_cat['c_id'] ] )
                    		{
                    			$active = ( $this_cat['active'] == 1 ) ? 0 : 1;
                        		$query .= "active=".$active;
                    		}

     				if( $this_cat['show_all'] != $IN[ "show_".$this_cat['c_id'] ] )
                    		{
                    			$show = ( $this_cat['show_all'] == 1 ) ? 0 : 1;
                        		$query .= ($query != "") ? ", " : " ";
                        		$query .= "show_all=".$show;
                    		}

                    		if( $IN[ "name_".$this_cat['c_id'] ] != "" )
                    		{
                    			$name = $IN[ "name_".$this_cat['c_id'] ];
                        		if( strlen($name) > 32 || strlen($name) < 4 )
                        		{
                        			$error[$this_cat['c_id'] ] = $this_cat['cat_name'];
                        		}
                        		$query .= ($query != "") ? ", " : " ";
                        		$query .= "cat_name='".addslashes($name)."' ";
                    		}

                    		$pass = trim($IN[ "pass_".$this_cat['c_id'] ]);
                    		if( (strlen($pass) > 32 || strlen($pass) < 4) && (!empty($pass) || $pass != "") )
                    		{
                    			$error[ $this_cat['c_id'] ] = $this_cat['cat_name'];
                    		}
                    		$query .= ($query != "") ? ", " : " ";
                    		$query .= "password='".$pass."' ";

                    		if( $query != "" && $error[ $this_cat['c_id'] ] == "")
                    		{
                    			$DB->query("UPDATE ibf_games_cats SET ".$query." WHERE c_id=".$this_cat['c_id']);
                    		}
                	}

                	$order = array();
			$counter = 0;

                	foreach( $IN as $key=>$value )
                	{
				$counter++;

                		if( preg_match("#_order#", $key) )
                    		{
                    			$key = intval($key);
                        		$order[$key] = $value;
                    		}
                	}

                	unset($key);
                	unset($value);

                	foreach( $order as $key=>$value )
                	{
                		$db_string = $DB->compile_db_update_string( array ( 'pos' => $value ) );
                    		$DB->query("UPDATE ibf_games_cats SET ".$db_string." WHERE c_id='".$key."'");
                	}

			// check if the show_all settings are ok
			$cntquery = $DB->query("SELECT COUNT(*) AS howmany FROM ibf_games_cats");
			$cntresult = $DB->fetch_row($cntquery);
			$counter = $cntresult['howmany'];

			$cntquery = $DB->query("SELECT COUNT(*) AS howmany FROM ibf_games_cats WHERE show_all=1");
			$cntresult = $DB->fetch_row($cntquery);
			$cnt_showall = $cntresult['howmany'];

			if ($counter==$cnt_showall)
			{
				$query = "";
				if ($counter > 1)
				{
					$query = "WHERE c_id=1";
				}
				$DB->query("UPDATE ibf_games_cats SET show_all = 0 ".$query);
			}

            		break;

    		case del:
            		if( $IN['c'] == 1 )
                	{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'cat');
				print_table_header($ibforums->lang['acp_tool_prune_errhead']);
				print_description_row($ibforums->lang['acp_cat_error3']);
				print_submit_row($ibforums->lang['acp_back'], 0);
				print_cp_footer();
				exit;
                	}
                	$DB->query("DELETE FROM ibf_games_cats WHERE c_id=".$IN['c']);
                	$DB->query("UPDATE ibf_games_list SET gcat=1 WHERE gcat=".$IN['c']);
            		break;

            	default:
            		break;
 	}

        update_cat_game_nums();

/*
        if( $this->arcade['log'] )
        {
        	$ADMIN->save_log("Edited Arcade Categories".$gtitle);
        }
*/

        if( count($error) == 0 )
        {
		define('CP_REDIRECT', 'arcade.php?code=cat');
		print_stop_message('saved_settings_successfully');
        }
        else
        {
    		$errmsg = "<ol>";
            	foreach( $error as $k=>$v )
            	{
            		$errmsg .= "<li>".$v."</li>";
            	}
            	$errmsg = "</ol>";

		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'cat');
		print_table_header($ibforums->lang['acp_cat_donehead']);
		print_description_row($ibforums->lang['acp_cat_error4'].$errmsg);
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
        }
}


// ##############################
// add game
// ##############################
if ($action == "add_game")
{
	global $IN, $vboptions;

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'do_add');
	print_table_header($ibforums->lang['acp_game_header']);
	print_description_row($ibforums->lang['acp_game_info']);
	print_input_row($ibforums->lang['acp_game_name'], 'gtitle', $ibforums->lang['acp_game_namedefault'], 0);
	print_select_row($ibforums->lang['acp_game_active'], 'active', array('1' => $ibforums->lang['acp_on'], '0' => $ibforums->lang['acp_off']), 1);
	print_select_row($ibforums->lang['acp_game_scoretype'], 'highscore_type', array('high' => $ibforums->lang['acp_high'], 'low' => $ibforums->lang['acp_low']), 'high');
	// print_select_row($ibforums->lang['acp_game_gametype'], 'game_type', array('1' => $ibforums->lang['acp_on'], '0' => $ibforums->lang['acp_off']), '0');

       	//if( $arcade['use_cats'] )
       	//{
        	$DB->query("SELECT * FROM ibf_games_cats WHERE show_all<>1 ORDER BY pos, cat_name");
        	$cat_list = array();
        	while( $CAT = $DB->fetch_row() )
        	{
        		$cat_list[$CAT['c_id']] = $CAT['cat_name'];
        	}
		print_select_row($ibforums->lang['acp_game_cat'], 'in_cat', $cat_list, '1');
      	//}

	print_input_row($ibforums->lang['acp_game_gname'], 'gname', '', 0);
	print_input_row($ibforums->lang['acp_game_decimal'], 'decpoints', '0', 0);

	print_input_row($ibforums->lang['acp_game_bgcolor'], 'bgcolor', '000000', 0);
	print_input_row($ibforums->lang['acp_game_width'], 'gwidth', '400', 0);
	print_input_row($ibforums->lang['acp_game_height'], 'gheight', '400', 0);

	print_table_break('', "90%");

	// detect vBplaza
	if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
	{
		print_table_header($ibforums->lang['acp_game_vbplaza']);
		print_input_row($ibforums->lang['acp_game_costs'], 'cost', '25', 0);	
		print_select_row($ibforums->lang['acp_game_jackpottype'], 'jackpottype', array('1' => $ibforums->lang['acp_game_jack_raise'], '0' => $ibforums->lang['acp_game_jack_static']), 1);
		print_input_row($ibforums->lang['acp_game_static'], 'static', '100', 0);
		print_input_row($ibforums->lang['acp_game_jackpot'], 'jackpot', '0', 0);
		print_table_break('', "90%");
	}

	$vbversion = substr($vboptions[templateversion],0,3);
	if ($vbversion != "3.0")
	{
		($hook = vBulletinHook::fetch_hook('ibproarcade_acp_addgame')) ? eval($hook) : false;
	}

	print_table_header($ibforums->lang['acp_game_optheader']);
	print_textarea_row($ibforums->lang['acp_game_descr'], 'gwords', '', 6, 45);
	print_textarea_row($ibforums->lang['acp_game_object'], 'object', '', 6, 45);
	print_textarea_row($ibforums->lang['acp_game_keys'], 'keys', '', 6, 45);
	print_input_row($ibforums->lang['acp_game_pnfglic'], 'license', '', 0);
	print_submit_row($ibforums->lang['acp_game_addbutton'], 0);

	print_table_break('', "90%");

	$header = array();
	$header[] = "<div align='center'>".$ibforums->lang['acp_game_targame']."</div>";
	$header[] = $ibforums->lang['acp_game_tarfile'];
	$header[] = $ibforums->lang['acp_gamesort_game'];
	$header[] = "<div align='center'>".$ibforums->lang['acp_game_tararchive']."</div>";
	$colspan = sizeof($header);

	print_table_header($ibforums->lang['acp_game_tarheader'], $colspan);
	print_description_row($ibforums->lang['acp_game_tarinfo'], 0, $colspan);
	print_cells_row($header, 1);

        $files = array();
        $dir = ROOT_PATH."arcade/tar";

	if ( is_dir($dir) )
	{
		$the_dir = opendir($dir);

		while( ($filename = readdir($the_dir)) !== false )
		{
			if( ($filename != ".") && ($filename != "..") )
			{
//				$files[] = preg_match("/^(game).+?\.(tar)$/", $filename);
				if( preg_match("/^(game).+?\.(tar)$/", $filename) )
				{
					$files[] = $filename;
				}
			}
		}
		closedir($the_dir);
	}

	asort($files);

        	$install_link = "";
        	if( count($files) > 0 )
        	{
        		foreach( $files as $this_file )
            		{
            			$name = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2", $this_file );

                		$DB->query("SELECT gid, gname FROM ibf_games_list WHERE gname='".$name."'");
                		if( $DB->get_num_rows() )
                		{
                			$gid = $DB->fetch_row();
                			$install_link = "<a href='arcade.php?$session[sessionurl]&amp;code=del&amp;gid=".$gid['gid']."'><div align='right'>".$ibforums->lang['acp_game_tar_uninstall']."</div></a>";
                		}
                		else
                		{
                			$install_link = "<a href='arcade.php?$session[sessionurl]&amp;code=tar_install&amp;file=$this_file'><div align='left'><b>".$ibforums->lang['acp_game_tar_install']."</b></div></a>";
                		}

			$cell = array();
			$cell[] = "<div align='center'><b>".$name."</b></div>";
			$cell[] = "<div align='center'>".$this_file."</div>";
			$cell[] = "<div align='center'>".$install_link."</div>";
			$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=remove_tar&amp;file=$name'>".$ibforums->lang['acp_games_delete']."</a></div>";
			print_cells_row($cell);
            		}
			print_description_row("<div align='right'><a href='arcade.php?$session[sessionurl]&amp;code=install_all'>".$ibforums->lang['acp_game_tar_masslink']."</a></div>", 0, $colspan);
   		}
        	else
        	{
		print_description_row("<div align='center'>- <i>".$ibforums->lang['acp_game_tar_empty']."</i> -</div>", 0, $colspan);
        	}

	print_table_break('', "90%");

	$header = array();
	$header[] = "<div align='center'>".$ibforums->lang['acp_game_targame']."</div>";
	$header[] = $ibforums->lang['acp_game_tarfile']	;
	$header[] = $ibforums->lang['acp_gamesort_game'];
	$header[] = "<div align='center'>".$ibforums->lang['acp_game_ziparchive']."</div>";
	$colspan = sizeof($header);

	print_table_header($ibforums->lang['acp_game_zipheader'], $colspan);
	print_description_row($ibforums->lang['acp_game_zipinfo'], 0, $colspan);
	print_cells_row($header, 1);

        $files = array();
        $dir = ROOT_PATH."arcade/zip";

	if ( is_dir($dir) )
	{
		$zip_dir = opendir($dir);

		while( ($filename = readdir($zip_dir)) !== false )
		{
			if( ($filename != ".") && ($filename != "..") )
			{

				$ext = substr(strrchr($filename, "."), 1);
				if($ext  == "zip")
				{
					$files[] = str_replace(".zip","",$filename);
				}
			}
		}
		closedir($zip_dir);
	}

	asort($files);

        	$install_link = "";
        	if( count($files) > 0 )
        	{
        		foreach( $files as $this_file )
            		{
            			$name = $this_file;   

                		$DB->query("SELECT gid, gname FROM ibf_games_list WHERE gname='".$name."'");
                		if( $DB->get_num_rows() )
                		{
                			$gid = $DB->fetch_row();
                			$install_link = "<a href='arcade.php?$session[sessionurl]&amp;code=del&amp;gid=".$gid['gid']."'><div align='right'>".$ibforums->lang['acp_game_tar_uninstall']."</div></a>";
                		}
                		else
                		{
                			$install_link = "<a href='arcade.php?$session[sessionurl]&amp;code=zip_install&amp;file=$this_file'><div align='left'><b>".$ibforums->lang['acp_game_tar_install']."</b></div></a>";
                		}

			$cell = array();
			$cell[] = "<div align='center'><b>".$name."</b></div>";
			$cell[] = "<div align='center'>".$this_file.".zip</div>";
			$cell[] = "<div align='center'>".$install_link."</div>";
			$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=remove_zip&amp;file=$name'>".$ibforums->lang['acp_games_delete']."</a></div>";
			print_cells_row($cell);
            		}
			print_description_row("<div align='right'><a href='arcade.php?$session[sessionurl]&amp;code=install_all_zip'>".$ibforums->lang['acp_game_tar_masslink']."</a></div>", 0, $colspan);
   		}
        	else
        	{
		print_description_row("<div align='center'>- <i>".$ibforums->lang['acp_game_tar_empty']."</i> -</div>", 0, $colspan);
        	}

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_game_sqlheader']);
	print_form_header('arcade', 'do_add_sql');
	print_description_row($ibforums->lang['acp_game_sqlinfo']);
	print_input_row($ibforums->lang['acp_game_sql'], 'the_query', '', 0);
	print_submit_row($ibforums->lang['acp_game_sqlbutton'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// store gamedata to DB after add game
// ##############################
if ($action == "do_addgame")
{
    	global $IN;

        	$checks = array( 	'gtitle'       => $IN['gtitle'],
                         		'gname'        => $IN['gname'],
                         		'bgcolor'      => $IN['bgcolor'], );
        	foreach($checks as $check_key=>$check_value)
        	{
        		if( empty($check_value) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			construct_hidden_code('gid', $gid);
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_game_error1']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
            		$$check_key = $check_value;
        	}
        	unset($checks);

        	$checks = array( 	'gwidth'        => $IN['gwidth'],
                  		'gheight'        => $IN['gheight'] );
        	foreach($checks as $check_key=>$check_value)
        	{
        		$check_value = intval($check_value);
            		if($check_value == 0)
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			construct_hidden_code('gid', $gid);
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_game_error2']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
            		$$check_key = $check_value;
        	}

		$gname		= $IN['gname'];
        	$active 	= $IN['active'];
        	$gwords     = $IN['gwords'];
        	$keys       = $IN['keys'];
        	$object     = $IN['object'];
		$license = $IN['license'];
        	$cat = ($arcade['use_cats'] != 0) ? $IN['in_cat'] : 1;
        	$hscore = $IN['highscore_type'];
		$jackpottype = $IN['jackpottype'];
		$jackpot = $IN['jackpot'];
		$static = $IN['static'];
		$costs = $IN['cost'];

		if (strtolower(substr($gname,-4)) == ".swf")
		{
			$gname = substr($gname,0,(strlen($gname)-4));
		}

		$game_type = 0;
		if ( ( file_exists(ROOT_PATH.'arcade/gamedata/'.$gname.'/v32game.txt' ) ) || ( file_exists(ROOT_PATH.'arcade/gamedata/'.$gname.'/v3game.txt' ) ) )
		{
			$game_type = 1;
		}


        	if( (!is_numeric($costs)) || ($costs < 0) )
        	{
			$costs = 0;
        	}

		if (($jackpottype<0) || ($jackpottype>1))
		{
			$jackpottype = 1;
		}

        	if( (!is_numeric($jackpot)) || ($jackpot < 0) )
        	{
			$jackpot = 0;
        	}

        	if( (!is_numeric($static)) || ($static < 0) )
        	{
			$static = 0;
        	}

		if ($jackpottype == 1)
		{
			// if raising Jackpot set the Jackpot itself to Zero
			$jackpottype = "-1";
		}
		else
		{
			// static Jackpot
			$jackpottype = $static;
		}		

	        $dir = getcwd()."/arcade";

		$filesize=0;
		$file = $dir."/".$gname.".swf";
		if(file_exists($file))
		{
			@chmod( $file , 0777 );
			$filesize = filesize($file);
		}

		if (intval($cat) < 1) { $cat = 1; }

		if (game_exists($gname))
		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$gname."</b> - ".$ibforums->lang['acp_game_tar_error0']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
		}

        	$db_string = $DB->compile_db_insert_string( array (  
				'gname'     	=> $gname,
                                                             	'gwords'    	=> $gwords,
                                                             	'gtitle'    		=> $gtitle,
                                                             	'bgcolor'   	=> strtolower($bgcolor),
                                                             	'gwidth'    		=> $gwidth,
                                                             	'gheight'   	=> $gheight,
                                                             	'active'    		=> $active,
                                                             	'object'    		=> $object,
                                                             	'gkeys'     		=> $keys,
                                                             	'gcat'     		=> $cat,
                                                             	'added'     	=> time(),
			 					'decpoints'	=> $IN['decpoints'],
								'cost'		=> $costs,
								'jackpot_type'	=> $jackpottype,
								'jackpot'	=> $jackpot,
                                                             	'highscore_type' 	=> $hscore,
								'license'  		=> $license,
								'tourney_use'	=> 1,
								'gtime'		=> 0,
								'game_type'		=> $game_type,
								'filesize'	=> $filesize,
								'g_raters'	=> '',
                                                    	) );

        	$DB->query("INSERT INTO ibf_games_list (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

    	update_cat_game_nums();

/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("Spiel hinzugefügt: ".$gtitle);
        	}
*/
	define('CP_REDIRECT', 'arcade.php?code=gamelist&cat=0');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// perform SQL-query from 'add game'
// ##############################
if ($action == "do_add_sql")
{
  	global $DB, $IN;

        	$DB->return_die = 1;
        	$DB->query($IN['the_query'],1);

        	if( $DB->error != "" )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'do_add_sql');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row($ibforums->lang['acp_game_sqlerror'].$DB->error);
		print_input_row($ibforums->lang['acp_game_sql'], 'the_query', $IN['the_query'], 0);
		print_submit_row($ibforums->lang['acp_game_sqlbutton'], 0);
		print_cp_footer();
		exit;
        	}
        	else
        	{
        		update_cat_game_nums();
/*
	            	if( $arcade['log'] )
            		{
            			$ADMIN->save_log("Spiel mit SQL-Query hinzugefügt");
            		}
*/
		define('CP_REDIRECT', 'arcade.php?code=gamelist&cat=0');
		print_stop_message('saved_settings_successfully');
	}
}


// ##############################
// edit game
// ##############################
if ($action == "editgame")
{
	global $IN, $DB, $vboptions;

        	$gid = $IN['gid'];
        	$DB->query("SELECT * FROM ibf_games_list WHERE gid=".$gid);
        	$GAME = $DB->fetch_row();

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'do_edit');
	print_table_header($ibforums->lang['acp_game_header']." ".$GAME['gtitle']);
	construct_hidden_code('gid', $gid);

	print_input_row($ibforums->lang['acp_game_name'], 'gtitle', $GAME['gtitle'], 0);
	print_select_row($ibforums->lang['acp_game_active'], 'active', array('1' => $ibforums->lang['acp_on'], '0' => $ibforums->lang['acp_off']), $GAME['active']);
	print_select_row($ibforums->lang['acp_game_scoretype'], 'highscore_type', array('high' => $ibforums->lang['acp_high'], 'low' => $ibforums->lang['acp_low']), $GAME['highscore_type']);
	// print_select_row($ibforums->lang['acp_game_gametype'], 'game_type', array('1' => $ibforums->lang['acp_on'], '0' => $ibforums->lang['acp_off']), $GAME['game_type']);
	print_select_row($ibforums->lang['acp_game_tourney'], 'tourney_use', array('1' => $ibforums->lang['acp_yes'], '0' => $ibforums->lang['acp_no']), $GAME['tourney_use']);

       	//if( $arcade['use_cats'] )
       	//{
       		$DB->query("SELECT * FROM ibf_games_cats WHERE show_all<>1 ORDER BY pos, cat_name");
       		$cat_list = array();
       		while( $CAT = $DB->fetch_row() )
       		{
       			$cat_list[$CAT['c_id']] = $CAT['cat_name'];
       		}
		print_select_row($ibforums->lang['acp_game_cat'], 'in_cat', $cat_list, $GAME['gcat']);
        //}

	print_input_row($ibforums->lang['acp_game_timesplayed'], 'gcount', $GAME['gcount'], 0);
	print_input_row($ibforums->lang['acp_game_gname'], 'gname', $GAME['gname'], 0);
	print_input_row($ibforums->lang['acp_game_decimal'], 'decpoints', $GAME['decpoints'], 0);
	print_input_row($ibforums->lang['acp_game_bgcolor'], 'bgcolor', $GAME['bgcolor'], 0);
	print_input_row($ibforums->lang['acp_game_width'], 'gwidth', $GAME['gwidth'], 0);
	print_input_row($ibforums->lang['acp_game_height'], 'gheight', $GAME['gheight'], 0);

	print_table_break('', "90%");

	// detect vBplaza
	if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
	{
		print_table_header($ibforums->lang['acp_game_vbplaza']);
		print_input_row($ibforums->lang['acp_game_costs'], 'cost', $GAME['cost'], 0);	

		if ($GAME['jackpot_type']=="-1")
		{
			// raising Jackpot
			$GAME['jackpot_type']=1;
			$GAME['static']=0;
		}
		else
		{
			// static Jackpot
			$GAME['static']=$GAME['jackpot_type'];
			$GAME['jackpot_type']=0;
		}

		print_select_row($ibforums->lang['acp_game_jackpottype'], 'jackpottype', array('1' => $ibforums->lang['acp_game_jack_raise'], '0' => $ibforums->lang['acp_game_jack_static']), $GAME['jackpot_type']);
		print_input_row($ibforums->lang['acp_game_static'], 'static', $GAME['static'], 0);
		print_input_row($ibforums->lang['acp_game_jackpot'], 'jackpot', $GAME['jackpot'], 0);
		print_table_break('', "90%");
	}

	$vbversion = substr($vboptions[templateversion],0,3);
	if ($vbversion != "3.0")
	{
		($hook = vBulletinHook::fetch_hook('ibproarcade_acp_editgame')) ? eval($hook) : false;
	}

	print_table_header($ibforums->lang['acp_game_optheader']);
	print_textarea_row($ibforums->lang['acp_game_descr'], 'gwords', $GAME['gwords'], 6, 45);
	print_textarea_row($ibforums->lang['acp_game_object'], 'object', $GAME['object'], 6, 45);
	print_textarea_row($ibforums->lang['acp_game_keys'], 'keys', $GAME['gkeys'], 6, 45);
	print_input_row($ibforums->lang['acp_game_pnfglic'], 'license', $GAME['license'], 0);
	print_submit_row($ibforums->lang['acp_save'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// store gamedata to DB after edit
// ##############################
if ($action == "do_editgame")
{
   	global $IN;

        	$gid = $IN['gid'];
		$IN['bgcolor'] = strtolower($IN['bgcolor']);

		if ( dechex(hexdec(substr($IN['bgcolor'],0,2))) == (substr($IN['bgcolor'],0,2)) )
		{
			$bgcolor = substr($IN['bgcolor'],0,2);
		}
		else
		{
			$bgcolor = "00";
		}

		if ( dechex(hexdec(substr($IN['bgcolor'],2,2))) == (substr($IN['bgcolor'],2,2)) )
		{
			$bgcolor .= substr($IN['bgcolor'],2,2);
		}
		else
		{
			$bgcolor .= "00";
		}

		if ( dechex(hexdec(substr($IN['bgcolor'],4,2))) == (substr($IN['bgcolor'],4,2)) )
		{
			$bgcolor .= substr($IN['bgcolor'],4,2);
		}
		else
		{
			$bgcolor .= "00";
		}

        	$checks = array( 	'gtitle'       => $IN['gtitle'],
                         		'gname'        => $IN['gname'],
                         		'gname'        => $IN['gname'], );
        	foreach($checks as $check_key=>$check_value)
        	{
        		if( empty($check_value) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'editgame');
			construct_hidden_code('gid', $gid);
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_game_error1']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
            		$$check_key = $check_value;
        	}
        	unset($checks);

        	$checks = array( 	'gwidth'        => $IN['gwidth'],
                         		'gheight'        => $IN['gheight'] );
        	foreach($checks as $check_key=>$check_value)
        	{
        		$check_value = intval($check_value);
            		if($check_value == 0)
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'editgame');
			construct_hidden_code('gid', $gid);
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_game_error2']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
            		$$check_key = $check_value;
        	}

        	$active = $IN['active'];
        	$gcount = $IN['gcount'];
        	$gwords = $IN['gwords'];
        	$keys   = $IN['keys'];
        	$object = $IN['object'];
        	$cat    = $IN['in_cat'];
        	$hscore = $IN['highscore_type'];
		$license = $IN['license'];
		$dec = $IN['decpoints'];
		$costs	= $IN['cost'];
		$jackpot = $IN['jackpot'];
		$jackpottype = $IN['jackpottype'];
		$static = $IN['static'];
		$tourney = $IN['tourney_use'];

		$gametype = 0;

		if ( ( file_exists(ROOT_PATH.'arcade/gamedata/'.$gname.'/v32game.txt' ) ) || ( file_exists(ROOT_PATH.'arcade/gamedata/'.$gname.'/v3game.txt' ) ) )
		{
			$gametype = 1;
		}

        	if( !is_numeric($gcount) )
        	{
			$gcount = 0;
        	}

        	if( (!is_numeric($costs)) || ($costs < 0) )
        	{
			$costs = 0;
        	}

		if (($jackpottype<0) || ($jackpottype>1))
		{
			$jackpottype = 1;
		}

        	if( (!is_numeric($jackpot)) || ($jackpot < 0) )
        	{
			$jackpot = 0;
        	}

        	if( (!is_numeric($static)) || ($static < 0) )
        	{
			$static = 0;
        	}

		if ($jackpottype == 1)
		{
			// if raising Jackpot set the Jackpot itself to Zero
			$jackpottype = "-1";
		}
		else
		{
			// static Jackpot
			$jackpottype = $static;
		}		

		if (intval($cat) < 1) { $cat = 1; }

		if ((intval($gametype!=0)) && (intval($gametype!=1))) { $gametype = 0; }

        	$db_string = $DB->compile_db_update_string( array( 	'gwords'   		=>  $gwords,
                                                           		'gcount'   		=>  $gcount,
                                                           		'gtitle'   		=>  $gtitle,
                                                           		'bgcolor'  		=>  $bgcolor,
                                                          		'gwidth'   		=>  $gwidth,
                                                           		'gheight'  		=>  $gheight,
                                                           		'active'   		=>  $active,
                                                           		'object'   		=>  $object,
                                                           		'gkeys'    		=>  $keys,
                                                           		'gcat'     		=>  $cat,
                                                           		'gname'    		=>  $gname,
                                                           		'highscore_type'	=> $hscore,
									'cost'			=> $costs,
									'jackpot_type'		=> $jackpottype,
									'jackpot'		=> $jackpot,
									'license'		=> $license,
									'decpoints'		=> $dec,
									'tourney_use'		=> $tourney,
									'game_type'		=> $gametype,
                                                    				) );

        	$DB->query("SELECT * FROM ibf_games_list WHERE gid=".$gid);
        	$GAME = $DB->fetch_row();

        	$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid=".$gid);

        	update_cat_game_nums();

/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("Spiel ".$GAME['gtitle']." bearbeitet.");
        	}
*/
	define('CP_REDIRECT', 'arcade.php?code=gamelist&cat=0');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// TAR install
// ##############################
if ($action == "tar_install")
{
	global $IN, $INFO;

        	$name = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2", $IN['file'] );
        	$name = trim($name);

		$DB->query("SELECT * FROM ibf_games_list WHERE gname = '".$name."'");
		$row=$DB->fetch_row();
		if($row['gname'] == $name)
		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$file_dir."</b>".$ibforums->lang['acp_game_tar_error0']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			break;
		}

        	$tar_dir = getcwd().'/arcade/tar';
        	$tmp_dir = getcwd().'/arcade/tmp';
        	$arcade_dir = getcwd().'/arcade';
        	$images_dir = getcwd().'/arcade/images';
		$multi_dir = getcwd().'/arcade/gamedata';

        	$file_dir = $INFO['base_dir']."arcade/tar/".$IN['file'];

        	$file = $IN['file'];
        	$tar = '';

        	if( !file_exists($file_dir) )
        	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$file_dir."</b>".$ibforums->lang['acp_game_tar_error1']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
        	}

		if (!file_exists($tmp_dir))
		{
			@mkdir($tmp_dir);
			@chmod($tmp_dir , 0777);
		}

        	if( !is_writable($tar_dir) )
        	{
        		if( !@chmod($tar_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$tar_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

        	if( !is_writable($tmp_dir) )
        	{
        		if( !@chmod($tmp_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$tmp_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

        	if( !is_writable($arcade_dir) )
        	{
        		if( !@chmod($arcade_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$arcade_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

        	if( !is_writable($images_dir) )
        	{
        		if( !@chmod($images_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$images_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

      	if( !is_writable($multi_dir) )
      	{
              		if( !@chmod($multi_dir , 0777) )
          		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$multi_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
          		}
      	}

        	require FUNCTIONS_PATH."tar.php";
		$tar = new tar();

        	$tar->new_tar($tar_dir , $file);
        	$files = $tar->list_files();

        	if( count($files) > 0 )
		{
			foreach($files as $the_file)
			{
				if( !preg_match( "/^(?:[\(\)\:\;\~\.\w\d\+\-\_\/]+)$/", $the_file) )
				{
					print_cp_header($ibforums->lang['acp_header']);
					print_form_header('arcade', 'add_game');
					print_table_header($ibforums->lang['acp_tool_prune_errhead']);
					print_description_row($ibforums->lang['acp_game_tar_error3']);
					print_submit_row($ibforums->lang['acp_back'], 0);
					print_cp_footer();
					exit;
				}
			}
		}
		else
		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_game_tar_error3']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
		}

        	$tar->extract_files( $tmp_dir );

        	$config_file = $tmp_dir."/".$name.".php";
        	@chmod($config_file , 0777);

        	$swf_file = $tmp_dir."/".$name.".swf";
        	@chmod($swf_file , 0777);

		$filesize=0;
		if(file_exists($swf_file))
		{
			$filesize = filesize($swf_file);
		}

        	$gif1_file = $tmp_dir."/".$name."1.gif";
        	@chmod($gif1_file , 0777);

        	$gif2_file = $tmp_dir."/".$name."2.gif";
        	@chmod($gif2_file , 0777);

		// improved handling of multifiles by MrZeropage
	      	$multi = $tmp_dir."/gamedata/".$name;

       		if ( file_exists($multi) )
	       	{
			// make tempdir writeable
			@chmod($multi , 0777);
			// define target directory...
          		$dest_multi = $multi_dir."/".$name;
			// and make it writeable
			@mkdir($dest_multi , 0777);

			// now copy stuff from tempdir
			@copydirr($multi,$dest_multi,0777,false);
			// and remove temp-multidir
          		@rm($multi);

			// just securing...
			@unlink($dest_multi."/*.php");
			@unlink($dest_multi."/*.html");
			@copy($arcade_dir."/index.html" , $dest_multi."/index.html");
			@chmod($dest_multi."/index.html" , 0777);
       		}

        	$dest_swf = $arcade_dir."/".$name.".swf";
        	if( file_exists($dest_swf) )
        	{
        		@chmod($dest_swf , 0777);
        		@unlink($dest_swf);
        	}

        	$dest_gif1 = $images_dir."/".$name."1.gif";
        	if( file_exists($dest_gif1) )
        	{
        		@chmod($dest_gif1 , 0777);
        		@unlink($dest_gif1);
        	}

        	$dest_gif2 = $images_dir."/".$name."2.gif";
        	if( file_exists($dest_gif2) )
        	{
        		@chmod($dest_gif2 , 0777);
        		@unlink($dest_gif2);
        	}

        	require $config_file;

        	@copy($swf_file , $dest_swf);
        	@copy($gif1_file , $dest_gif1);
        	@copy($gif2_file , $dest_gif2);

        	@chmod($dest_swf , 0777);
        	@chmod($dest_gif1 , 0777);
        	@chmod($dest_gif2 , 0777);

        	@unlink($swf_file);
        	@unlink($gif1_file);
        	@unlink($gif2_file);
        	@unlink($config_file);

		// cleanup whole temp-directory
		@rm($tmp_dir."/*");

		// some default settings
		$config['gcat']=1;
		$config['active']=1;

		$config['bgcolor'] = strtolower($config['bgcolor']);

		if (hexdec($config['bgcolor'])==0)
		{
			$config['bgcolor']="000000";
		}

		if (game_exists($config['gname']))
		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$config['gname']."</b> - ".$ibforums->lang['acp_game_tar_error0']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
		}

        	$db_string = $DB->compile_db_insert_string( array (  'gname'     => stripslashes($config['gname']),
                                                             	'gwords'    	=> stripslashes($config['gwords']),
                                                             	'gtitle'    	=> stripslashes($config['gtitle']),
                                                             	'bgcolor'   	=> stripslashes($config['bgcolor']),
                                                             	'gwidth'    	=> stripslashes($config['gwidth']),
                                                             	'gheight'   	=> stripslashes($config['gheight']),
                                                             	'active'    	=> stripslashes($config['active']),
                                                             	'object'    	=> stripslashes($config['object']),
                                                             	'gkeys'     	=> stripslashes($config['gkeys']),
                                                             	'gcat'      	=> stripslashes($config['gcat']),
								'cost'		=> 0,
								'jackpot'	=> 0,
								'jackpot_type'	=> -1,
                                                             	'added'     => time(),
								'tourney_use'	=> 1,
								'gtime'		=> 0,
								'game_type'	=> 0,
								'g_raters'	=> '',
								'license'	=> '',
								'filesize'	=> $filesize,
                                     		) );
        	$DB->query("INSERT INTO ibf_games_list (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

        	update_cat_game_nums();

/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("Spiel aus TAR-Archiv hinzugefügt: ".$gtitle);
        	}
*/

	define('CP_REDIRECT', 'arcade.php?code=add_game');
	print_stop_message('saved_settings_successfully');
}

// ##############################
// ZIP install
// ##############################
if ($action == "zip_install")
{
	global $IN;

        	$name = $IN['file'];
        	$name = trim($name);

		$DB->query("SELECT * FROM ibf_games_list WHERE gname = '".$name."'");
		$row=$DB->fetch_row();
		if($row['gname'] == $name)
		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$file_dir."</b>".$ibforums->lang['acp_game_tar_error0']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			break;
		}

        	$zip_dir = getcwd().'/arcade/zip';
        	$arcade_dir = getcwd().'/arcade';
        	$images_dir = getcwd().'/arcade/images';
		$multi_dir = getcwd().'/arcade/gamedata';
        	$file_dir = ROOT_PATH.'/arcade/zip/'.$name.'.zip';

        	if( !file_exists($file_dir) )
	       	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$file_dir."</b>".$ibforums->lang['acp_game_tar_error1']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
        	}

        	if( !is_writable($zip_dir) )
        	{
        		if( !@chmod($zip_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$zip_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

        	if( !is_writable($arcade_dir) )
        	{
        		if( !@chmod($arcade_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$arcade_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

        	if( !is_writable($images_dir) )
        	{
        		if( !@chmod($images_dir , 0777) )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$images_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            		}
        	}

      	if( !is_writable($multi_dir) )
      	{
              		if( !@chmod($multi_dir , 0777) )
          		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$multi_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
          		}
      	}

  	require_once(FUNCTIONS_PATH."pclzip.lib.php");
  	$zip = new PclZip($file_dir);
  
  	if (($list = $zip->listContent()) == 0)
	{
    		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$tar_dir."</b>".$ibforums->lang['acp_game_zip_error8']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
  	}

  	for ($i=0; $i<sizeof($list); $i++)
	{
    		for(reset($list[$i]); $key = key($list[$i]); next($list[$i]))
		{
			if ($key == "filename")
			{
				$filename = substr(strrchr($list[$i][$key], "/"), 1);
				if (!$filename)
				{
					$filename = $list[$i][$key];
				}
				$ext = substr(strrchr($filename, "."), 1);
				if($ext  == "php")
				{
					$output = $zip->extract(PCLZIP_OPT_BY_NAME, $filename, PCLZIP_OPT_EXTRACT_AS_STRING);
					$buf = $output[0]['content'];

					// PHP Parsing messy :(
					if (!strpos($buf, "game_width"))
					{
						//It's the older type with the insert statement written directly, so we need to find it and take it apart
						$fileStart = strpos($buf, "highscore) VALUES (");
						$buf = substr($buf,$fileStart+19);
						$fileEnd = strpos($buf, ", NULL)");
						$buf = substr($buf,0,$fileEnd);
						//This is very buggy, if there is a comma in the description the array will be out of wack So far when this occurs it is only the description, width and height that get messed up.
						$arv3legacy = explode(",",str_replace("'","",$buf));
						$title = trim($arv3legacy[2]);
						$shortname = trim($arv3legacy[0]);
						$game_width = trim($arv3legacy[5]);
						$game_height = trim($arv3legacy[6]);
						$description = trim($arv3legacy[3]);
						$config = array(
								gname		=>  $shortname,
								gtitle		=>  $title,
								bgcolor		=>  '000000',
								gwidth		=>  $game_width,
								gheight		=>  $game_height,
								active		=>  '1',
								gcat		=>  '1',
								gwords		=>  $description,
								object		=>  $description,
								gkeys		=>  '',);
					}
					else
					{
						//It's the newer type with predefined vars
						$fileStart = strpos($buf, "global.php");
						$buf = substr($buf,$fileStart+13);
						$fileEnd = strpos($buf, "print_cp_header");
						$buf = substr($buf,0,$fileEnd);
						$tmpfilename = $zip_dir.'/'.rand().'.php';
						$tmpfile = fopen($tmpfilename,  "x");
						fwrite($tmpfile, '<?php'.$buf.'?>');
						fclose($tmpfile);
						require_once($tmpfilename);
						unlink($tmpfilename);
						$config = array(
								gname		=>  $shortname,
								gtitle		=>  $title,
								bgcolor		=>  '000000',
								gwidth		=>  $game_width,
								gheight		=>  $game_height,
								active		=>  '1',
								gcat		=>  '1',
								gwords		=>  $description,
								object		=>  $description,
								gkeys		=>  '',);
					}
					//End PHP Parsing
				}
				else if ($ext  == "swf")
				{
					$savefile = $arcade_dir.'/'.$filename;
					$filesize=0;

					if( file_exists($savefile) )
					{
						@chmod($savefile , 0777);
						$filesize = filesize($savefile);
						@unlink($savefile);
					}
					$output = $zip->extract(PCLZIP_OPT_BY_NAME, $filename, PCLZIP_OPT_EXTRACT_AS_STRING);
					$savefile = fopen($savefile,  "x");
		  			fwrite($savefile, $output[0]['content']);
		  			fclose($savefile);
				}
				else if ($ext  == "gif")
				{
					$savefile = $images_dir.'/'.$filename;
					if( file_exists($savefile) )
					{
						@chmod($savefile , 0777);
						@unlink($savefile);
					}
					$output = $zip->extract(PCLZIP_OPT_BY_NAME, $filename, PCLZIP_OPT_EXTRACT_AS_STRING);
					$savefile = fopen($savefile,  "x");
		  			fwrite($savefile, $output[0]['content']);
		  			fclose($savefile);				
				}	  
			}  
    		}
  	}

 	if (!$shortname)
 	{
		//incompatible game please add manually
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$tar_dir."</b>".$ibforums->lang['acp_game_zip_error8']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
  	}

    	rename($file_dir,$zip_dir.'/'.$shortname.".zip");

	// some default settings
	$config['gcat']=1;
	$config['active']=1;
	$config['bgcolor']="000000";

	if (game_exists($config['gname']))
	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$config['gname']."</b> - ".$ibforums->lang['acp_game_tar_error0']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
	}

	$db_string = $DB->compile_db_insert_string( array (  	'gname'     => stripslashes($config['gname']),
								'gwords'    => stripslashes($config['gwords']),
								'gtitle'    => stripslashes($config['gtitle']),
								'bgcolor'   => stripslashes($config['bgcolor']),
								'gwidth'    => stripslashes($config['gwidth']),
								'gheight'   => stripslashes($config['gheight']),
								'active'    => stripslashes($config['active']),
								'object'    => stripslashes($config['object']),
								'gkeys'     => stripslashes($config['gkeys']),
								'gcat'      => stripslashes($config['gcat']),
								'cost'		=> 0,
								'jackpot'	=> 0,
								'jackpot_type'	=> -1,
								'added'     => time(),
								'tourney_use'	=> 1,
								'gtime'		=> 0,
								'game_type'		=> 0,
								'g_raters'	=> '',
								'license'	=> '',
								'filesize'	=> $filesize,
								) );
	$DB->query("INSERT INTO ibf_games_list (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
	update_cat_game_nums();
	define('CP_REDIRECT', 'arcade.php?code=add_game');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// Game uninstall
// ##############################
if ($action == "del")
{
	global $IN, $INFO;

        	if( !isset($IN['confirm']) )
        	{
        		confirm();
        	}
        	else
        	{
        		if( $IN['confirm'] == 0 )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_game_tar_confhead']);
			print_description_row($ibforums->lang['acp_game_tar_error6']);
			print_submit_row($ibforums->lang['acp_continue'], 0);
			print_cp_footer();
			exit;
            		}
            		$gid = $IN['gid'];
            		$error = 0;
            		$DB->query("SELECT gtitle, gname FROM ibf_games_list WHERE gid=".$gid);
            		$GAME = $DB->fetch_row();
            		if( $IN['confirm'] == 2 )
            		{
            			$del = array( 'swf' => "" , 'gif1' => "" , 'gif2' => "" );
                		$swf_file = $INFO['base_dir']."arcade/".$GAME['gname'].".swf";
                		$gif1_file = $INFO['base_dir']."arcade/images/".$GAME['gname']."1.gif";
                		$gif2_file = $INFO['base_dir']."arcade/images/".$GAME['gname']."2.gif";

                		if( @unlink($swf_file) )
                		{
                			$del['swf'] = true;
                		}
                		if( @unlink($gif1_file) )
                		{
                			$del['gif1'] = true;
                		}
                		if( @unlink($gif2_file) )
                		{
                			$del['gif2'] = true;
                		}

				// delete data from /gamedata if existing...
			      	$multi = $INFO['base_dir']."arcade/gamedata/".$GAME['gname'];

		       		if ( file_exists($multi) )
			       	{
		          		@rm($multi);
       				}

                		$files = "<ol>";
                		foreach( $del as $key=>$value )
                		{
                			if( !$value )
                    			{
                    				$error = 1;
                        			$files .= "<li>";
                        			$files .= $GAME['gname'];
                        			$files .= ( preg_match("#gif#" , $key) ) ? substr( $key , 3 , 1).".".substr( $key , 0 , 3) : ".".substr( $key , 0 , 3);
                        			$files .= "</li>";
                    			}
                		}
                		$files .= "</ol>";
            		}

		// look for existing tournaments of that Game
		$looktourney = $DB->query("SELECT tid FROM ibf_tournaments WHERE gid=".$gid);
		while ($tourney=$DB->fetch_row($looktourney))
		{
			$tid=$tourney[tid];
			$DB->query("DELETE FROM ibf_tournament_players WHERE tid=".$tid);
			$DB->query("DELETE FROM ibf_tournament_players_statut WHERE tid=".$tid);
			$DB->query("DELETE FROM ibf_tournaments WHERE tid=".$tid);
		}

            	$DB->query("DELETE FROM ibf_games_scores WHERE gid=".$gid);
		$DB->query("DELETE FROM ibf_games_league WHERE gid=".$gid);
     		$DB->query("DELETE FROM ibf_games_list WHERE gid=".$gid);

		do_champ_update(1);
		update_cat_game_nums();
		do_league_update(1);

/*
            		if( $arcade['log'] )
            		{
            			$ADMIN->save_log("Spiel ".$GAME['gtitle']." gelöscht.");
            		}
*/

            		if( !$error )
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_game_tar_confhead']);
			print_description_row($ibforums->lang['acp_game_tar_error7']);
			print_submit_row($ibforums->lang['acp_continue'], 0);
			print_cp_footer();
			exit;
            		}
            		else
            		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($GAME['gname'].$ibforums->lang['acp_game_tar_error4'].$files);
			print_submit_row($ibforums->lang['acp_continue'], 0);
			print_cp_footer();
			exit;
            		}
	}
}


// ##############################
// remove TAR archive
// ##############################
if ($action == "remove_tar")
{
  	global $IN;

        	$dir = ROOT_PATH."arcade/tar/";
        	$file = $dir."game_".$IN['file'].".tar";

        	if( !file_exists($file) )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$file."</b>".$ibforums->lang['acp_game_tar_error1']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
        	}

        	@chmod($file , 0777);

        	if( !unlink($file) )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$file."</b>".$ibforums->lang['acp_game_tar_error5']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
        	}
        	else
        	{
/*
        		if( $arcade['log'] )
            		{
            			$ADMIN->save_log("Archiv gelöscht.");
            		}
*/
		define('CP_REDIRECT', 'arcade.php?code=add_game');
		print_stop_message('saved_settings_successfully');
        	}
}


// ##############################
// remove ZIP archive
// ##############################
if ($action == "remove_zip")
{
  	global $IN;

        	$dir = ROOT_PATH."arcade/zip/";
        	$file = $dir.$IN['file'].".zip";

        	if( !file_exists($file) )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$file."</b>".$ibforums->lang['acp_game_zip_error1']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
        	}

        	@chmod($file , 0777);

        	if( !unlink($file) )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'add_game');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row("<b>".$file."</b>".$ibforums->lang['acp_game_zip_error5']."<br />");
		print_submit_row($ibforums->lang['acp_back'], 0);
		print_cp_footer();
		exit;
        	}
        	else
        	{
/*
        		if( $arcade['log'] )
            		{
            			$ADMIN->save_log("Archiv gelöscht.");
            		}
*/
		define('CP_REDIRECT', 'arcade.php?code=add_game');
		print_stop_message('saved_settings_successfully');
        	}
}


// ##############################
// Install ALL TAR-Games at once
// ##############################
if ($action == "install_all")
{
	global $IN, $dl_dir;

	$dir = ROOT_PATH."arcade/tar";

	if ( is_dir($dir) )
	{
	  	$the_dir = opendir($dir);

	  	$tarcounter = 0;

	  	while( ($filename = readdir($the_dir)) !== false )
	  	{
	   		if( ($filename != ".") && ($filename != "..") )
	   		{
	    			if( preg_match("/^(game).+?\.(tar)$/", $filename) )
	    			{
					$name = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2", $filename );
					$name = trim($name);
					$result=$DB->query("SELECT * FROM ibf_games_list WHERE gname = '$name'");
					$row=mysql_fetch_row($result);
					if($row[1] != $name)
					{
	     					$filelists[$tarcounter] = $filename;
	     					$tarcounter++;
					}
	    			}
	   		}
	  	}

		closedir($the_dir);
	}

   	$tar_dir 	= getcwd().'/arcade/tar';
       	$arcade_dir 	= getcwd().'/arcade';
       	$images_dir 	= getcwd().'/arcade/images';
 	$multi_dir 	= getcwd().'/arcade/gamedata';
	$tmp_dir 	= getcwd().'/arcade/tmp';
 	$dl_dir   	= getcwd().'/arcade/installed';

       	if( !is_writable($tar_dir) )
       	{
        	if( !@chmod($tar_dir , 0777) )
           	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$tar_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
           	}	
       	}

       	if( !is_writable($arcade_dir) )
       	{
        	if( !@chmod($arcade_dir , 0777) )
           	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$arcade_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
           	}
       	}

       	if( !is_writable($images_dir) )
       	{
        	if( !@chmod($images_dir , 0777) )
           	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$images_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
           	}
       	}

      	if( !is_writable($multi_dir) )
      	{
              	if( !@chmod($multi_dir , 0777) )
          	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$multi_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
          	}
      	}

	if (!file_exists($tmp_dir))
	{
		@mkdir($tmp_dir);
		@chmod($tmp_dir , 0777);
	}

      	if( !is_writable($tmp_dir) )
      	{
              	if( !@chmod($tmp_dir , 0777) )
          	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$tmp_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
          	}
      	}

	$installcounter = 0;
	$skipcounter = 0;
	require FUNCTIONS_PATH."tar.php";

	while ($tarcounter > $installcounter)
	{
	 	$name 		= preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2", $filelists[$installcounter] );
	 	$name 		= trim($name);
	       	$file_dir 	= getcwd()."arcade/tar/".$filelists[$installcounter];
	       	$file 		= $filelists[$installcounter];
	   	$IN['file'] 	= $filelists[$installcounter];
	       	$tar 		= '';

		$tar = new tar();

	       	$tar->new_tar($tar_dir , $file);
	       	$files = $tar->list_files();

	       	$tar->extract_files( $tmp_dir );

	       	$config_file = $tmp_dir."/".$name.".php";
	       	@chmod($config_file , 0777);

	       	$swf_file = $tmp_dir."/".$name.".swf";
	       	@chmod($swf_file , 0777);

		$filesize=0;
		if(file_exists($swf_file))
		{
			$filesize = filesize($swf_file);
		}

	       	$gif1_file = $tmp_dir."/".$name."1.gif";
	       	@chmod($gif1_file , 0777);

	       	$gif2_file = $tmp_dir."/".$name."2.gif";
	       	@chmod($gif2_file , 0777);

	       	$ta_file = $tar_dir."/game_".$name.".tar";
	       	@chmod($ta_file , 0777);

		if ( file_exists($config_file) && file_exists($swf_file) && file_exists($gif1_file) && file_exists($gif2_file) )
		{
			// improved handling of multifiles by MrZeropage
		      	$multi = $tmp_dir."/gamedata/".$name;

	       		if ( file_exists($multi) )
		       	{
				// make tempdir writeable
				@chmod($multi , 0777);
				// define target directory...
	          		$dest_multi = $multi_dir."/".$name;
				// and make it writeable
				@mkdir($dest_multi , 0777);

				// now copy stuff from tempdir
				@copydirr($multi,$dest_multi,0777,false);
				// and remove temp-multidir
	          		@rm($multi);

				// just securing...
				@unlink($dest_multi."/*.php");
				@unlink($dest_multi."/*.html");
				@copy($arcade_dir."/index.html" , $dest_multi."/index.html");
				@chmod($dest_multi."/index.html" , 0777);
	       		}

		       	$dest_swf = $arcade_dir."/".$name.".swf";
			$filesize=0;

		       	if( file_exists($dest_swf) )
		       	{
		           	@chmod($dest_swf , 0777);
		           	@unlink($dest_swf);
		       	}

		       	$dest_gif1 = $images_dir."/".$name."1.gif";
		       	if( file_exists($dest_gif1) )
		       	{
		           	@chmod($dest_gif1 , 0777);
		           	@unlink($dest_gif1);
		       	}

		       	$dest_gif2 = $images_dir."/".$name."2.gif";
		       	if( file_exists($dest_gif2) )
		       	{
		           	@chmod($dest_gif2 , 0777);
		           	@unlink($dest_gif2);
		       	}

			/* no automatic move of archives
		       	$dest_ta = $dl_dir."/game_".$name.".tar";
		       	if( file_exists($dest_ta) )
		       	{
		           	@chmod($dest_ta , 0777);
		           	@unlink($dest_ta);
		       	}
			*/

		       	require $config_file;

		       	@copy($swf_file , $dest_swf);
		       	@copy($gif1_file , $dest_gif1);
		       	@copy($gif2_file , $dest_gif2);
		       	//@copy($ta_file , $dest_ta);

		       	@chmod($dest_swf , 0777);
		       	@chmod($dest_gif1 , 0777);
		       	@chmod($dest_gif2 , 0777);
		       	//@chmod($dest_ta , 0777);

		       	@unlink($swf_file);
		       	@unlink($gif1_file);
		       	@unlink($gif2_file);
		       	@unlink($config_file);
		       	//@unlink($ta_file);

			// cleanup whole temp-directory
			@rm($tmp_dir."/*");

			// some default settings
			$config['gcat']=1;
			$config['active']=1;

			$config['bgcolor'] = strtolower($config['bgcolor']);

			if (hexdec($config['bgcolor'])==0)
			{
				$config['bgcolor']="000000";
			}

			if (game_exists($config['gname']))
			{
				$skipcounter++;
			}
			else
			{
				// write to DB
			       	$db_string = $DB->compile_db_insert_string( array ( 'gname'     => stripslashes($config['gname']),
			                                                            'gwords'    => stripslashes($config['gwords']),
			                                                            'gtitle'    => stripslashes($config['gtitle']),
			                                                            'bgcolor'   => stripslashes($config['bgcolor']),
			                                                            'gwidth'    => stripslashes($config['gwidth']),
			                                                            'gheight'   => stripslashes($config['gheight']),
			                                                            'active'    => stripslashes($config['active']),
			                                                            'object'    => stripslashes($config['object']),
			                                                            'gkeys'     => stripslashes($config['gkeys']),
			                                                            'gcat'      => stripslashes($config['gcat']),
			                                                            'added'     => time(),
										    'cost'	=> 0,
										    'jackpot'	=> 0,
										    'jackpot_type'	=> -1,
										    'tourney_use'	=> 1,
										    'gtime'		=> 0,
										    'game_type'	=> 0,
										     'g_raters'	=> '',
										    'license'	=> '',
										    'filesize'	=> $filesize,
				                                                   ) );
		       		$DB->query("INSERT INTO ibf_games_list (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

				$installcounter++;
			}
		}
	}


	update_cat_game_nums();

/*
       	if( $arcade['log'] )
	{
		$ADMIN->save_log("Added ".$tarcounter." Game(s) via tar archives.");
	}
*/

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'add_game');
	print_table_header($ibforums->lang['acp_game_tar_masshead']);
	print_description_row("<div align='center'>".$ibforums->lang['acp_game_tar_massinfo1']."<b>".$installcounter."/".$tarcounter."</b>".$ibforums->lang['acp_game_tar_massinfo2']."</div>");
	print_submit_row($ibforums->lang['acp_game_tar_massbutton'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// Install ALL ZIP-Games at once
// ##############################
if ($action == "install_all_zip")
{
	global $IN;
	$dir = ROOT_PATH."arcade/zip";
	$zip_dir = getcwd().'/arcade/zip';
	$arcade_dir = getcwd().'/arcade';
	$images_dir = getcwd().'/arcade/images';
	$multi_dir = getcwd().'/arcade/gamedata';
	$tar = '';
	if( !is_writable($zip_dir) )
        {
        	if( !@chmod($zip_dir , 0777) )
           	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$zip_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
            	}
        }

       	if( !is_writable($arcade_dir) )
       	{
       		if( !@chmod($arcade_dir , 0777) )
		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$arcade_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
 		}
       	}

       	if( !is_writable($images_dir) )
       	{
       		if( !@chmod($images_dir , 0777) )
 		{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'add_game');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row("<b>".$images_dir."</b>".$ibforums->lang['acp_game_tar_error2']."<br />");
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
  		}
       	}

	$zipcounter = 0;
	$skipcounter = 0;
	require_once(FUNCTIONS_PATH."pclzip.lib.php");
	$the_dir = opendir($zip_dir);

  	while( ($filename = readdir($the_dir)) !== false )
  	{
		$ext = substr(strrchr($filename, "."), 1);
   		if($ext == "zip")
   		{
				$name = trim($filename);
				$result=$DB->query("SELECT * FROM ibf_games_list WHERE gname = '$name'");
					
				$row=mysql_fetch_row($result);
				if($row[1] != $name)
				{
     					//Game Doesnt exist lets install it
					$file_dir = $zip_dir.'/'.$name;
					$zip = new PclZip($file_dir);
  
					if (($list = $zip->listContent()) == 0) 
					{
						print_cp_header($ibforums->lang['acp_header']);
						print_form_header('arcade', 'add_game');
						print_table_header($ibforums->lang['acp_tool_prune_errhead']);
						print_description_row("<b>".$file_dir."</b>".$ibforums->lang['acp_game_zip_error8']."<br />");
						print_submit_row($ibforums->lang['acp_back'], 0);
						print_cp_footer();
						exit;
					}
						  
					for ($i=0; $i<sizeof($list); $i++) 
					{
						for(reset($list[$i]); $key = key($list[$i]); next($list[$i])) 
						{
							if ($key == "filename")
							{
								$filename = $list[$i][$key];
								//$filename = substr(strrchr($list[$i][$key], "/"), 1);
								//if (!$filename){$filename = $list[$i][$key];}
								$ext = substr(strrchr($filename, "."), 1);
								if($ext  == "php")
								{
									$output = $zip->extract(PCLZIP_OPT_BY_NAME, $filename, PCLZIP_OPT_EXTRACT_AS_STRING);
									$buf = $output[0]['content'];
									//PHP Parsing messy :(
									if (!strpos($buf, "game_width"))
									{
										//It's the older type with the insert statement written directly so we need to find it and take it apart
										$fileStart = strpos($buf, "highscore) VALUES (");
										$buf = substr($buf,$fileStart+19);
										$fileEnd = strpos($buf, ", NULL)");
										$buf = substr($buf,0,$fileEnd);
										//This is very buggy, if there is a comma in the descritpion the array will be out of wack So far when this occurs it is only the description, width and height that get messed up.
										$arv3legacy = explode(",",str_replace("'","",$buf));
										$title = trim($arv3legacy[2]);
										$shortname = trim($arv3legacy[0]);
										$game_width = trim($arv3legacy[5]);
										$game_height = trim($arv3legacy[6]);
										$description = trim($arv3legacy[3]);
										$config = array(
													gname		=>  $shortname,
													gtitle		=>  $title,
													bgcolor		=>  '000000',
													gwidth		=>  $game_width,
													gheight		=>  $game_height,
													active		=>  '1',
													gcat		=>  '1',
													gwords		=>  $description,
													object		=>  $description,
													gkeys		=>  '',);
									}
									else
									{
										//It's the newer type with predefined vars
										$fileStart = strpos($buf, "global.php");
										$buf = substr($buf,$fileStart+13);
										$fileEnd = strpos($buf, "print_cp_header");
										$buf = substr($buf,0,$fileEnd);
										$tmpfilename = $zip_dir.'/'.rand().'.php';
										$tmpfile = fopen($tmpfilename,  "x");
										fwrite($tmpfile, '<?php'.$buf.'?>');
										fclose($tmpfile);
										require_once($tmpfilename);
										unlink($tmpfilename);
										$config = array(
													gname		=>  $shortname,
													gtitle		=>  $title,
													bgcolor		=>  '000000',
													gwidth		=>  $game_width,
													gheight		=>  $game_height,
													active		=>  '1',
													gcat		=>  '1',
													gwords		=>  $description,
													object		=>  $description,
													gkeys		=>  '',);
							
									}
									//End PHP Parsing
								}
								else if ($ext  == "swf")
								{
									$filename2 = substr(strrchr($list[$i][$key], "/"), 1);
									if (!$filename2) {$filename2 = $list[$i][$key];}
									$savefile = $arcade_dir.'/'.$filename2;

									$filesize=0;
									if( file_exists($savefile) )
									{
										@chmod($savefile , 0777);
										$filesize = filesize($savefile);
										@unlink($savefile);
									}
									$output = $zip->extract(PCLZIP_OPT_BY_NAME, $filename, PCLZIP_OPT_EXTRACT_AS_STRING);
									$savefile = fopen($savefile,  "x");
									fwrite($savefile, $output[0]['content']);
									fclose($savefile);
								}
								else if ($ext  == "gif")
								{
									$filename2 = substr(strrchr($list[$i][$key], "/"), 1);
									if (!$filename2){$filename2 = $list[$i][$key];}
									$savefile = $images_dir.'/'.$filename2;
									if( file_exists($savefile) )
									{
										@chmod($savefile , 0777);
										@unlink($savefile);
									}
									$output = $zip->extract(PCLZIP_OPT_BY_NAME, $filename, PCLZIP_OPT_EXTRACT_AS_STRING);
									$savefile = fopen($savefile,  "x");
									fwrite($savefile, $output[0]['content']);
									fclose($savefile);				
								}	  
							}  
						}
					}

					if (!$shortname)
					{
						//incompatible game please add manually
						print_cp_header($ibforums->lang['acp_header']);
						print_form_header('arcade', 'add_game');
						print_table_header($ibforums->lang['acp_tool_prune_errhead']);
						print_description_row("<b>".$tar_dir."</b>".$ibforums->lang['acp_game_zip_error8']."<br />");
						print_submit_row($ibforums->lang['acp_back'], 0);
						print_cp_footer();
						exit;
					}

					rename($file_dir,$zip_dir.'/'.$shortname.".zip");

					// some default settings
					$config['gcat']=1;
					$config['active']=1;
					$config['bgcolor']="000000";

					if (game_exists($config['gname']))
					{
						$skipcounter++;
					}
					else
					{
						$db_string = $DB->compile_db_insert_string( array (  	'gname'     => stripslashes($config['gname']),
													'gwords'    => stripslashes($config['gwords']),
													'gtitle'    => stripslashes($config['gtitle']),
													'bgcolor'   => stripslashes($config['bgcolor']),
													'gwidth'    => stripslashes($config['gwidth']),
													'gheight'   => stripslashes($config['gheight']),
													'active'    => stripslashes($config['active']),
													'object'    => stripslashes($config['object']),
													'gkeys'     => stripslashes($config['gkeys']),
													'gcat'      => stripslashes($config['gcat']),
													'added'     => time(),
										  			'cost'	=> 0,
													'jackpot'	=> 0,
													'jackpot_type'	=> -1,
													'tourney_use'	=> 1,
													'gtime'		=> 0,
													'game_type'		=> 0,
													'g_raters'	=> '',
													'license'	=> '',
													'filesize'	=> $filesize
													) );
						$DB->query("INSERT INTO ibf_games_list (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

						$zipcounter++;
					}
			}
   		}
  	}

	closedir($the_dir);
	update_cat_game_nums();

/*
       	if( $arcade['log'] )
	{
		$ADMIN->save_log("Added ".$zipcounter." Game(s) via zip archives.");
	}
*/

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'add_game');
	print_table_header($ibforums->lang['acp_game_tar_masshead']);
	print_description_row("<div align='center'>".$ibforums->lang['acp_game_tar_massinfo1']."<b>".$zipcounter."</b>".$ibforums->lang['acp_game_tar_massinfo2']."</div>");
	print_submit_row($ibforums->lang['acp_game_tar_massbutton'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// Tournaments
// ##############################
if ($action == "tourney")
{
	global $IN;

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'tourneysettings');

	print_table_header($ibforums->lang['acp_tourney_settings']);
	print_input_row($ibforums->lang['acp_main_tourneylimit1'], 'tourney_limit1', $arcade['tourney_limit1'], 0);
	print_input_row($ibforums->lang['acp_main_tourneylimit2'], 'tourney_limit2', $arcade['tourney_limit2'], 0);
	print_table_header($ibforums->lang['acp_main_tourney_head']);
	print_input_row($ibforums->lang['acp_main_tourney_col1'], 'ladder_color', $arcade['ladder_color'], 0);
	print_input_row($ibforums->lang['acp_main_tourney_col2'], 'ladder_empty_color', $arcade['ladder_empty_color'], 0);
	print_input_row($ibforums->lang['acp_main_tourney_col3'], 'ladder_qual_color', $arcade['ladder_qual_color'], 0);
	print_input_row($ibforums->lang['acp_main_tourney_col4'], 'ladder_disqual_color', $arcade['ladder_disqual_color'], 0);
	print_input_row($ibforums->lang['acp_main_tourney_col5'], 'ladder_elim_color', $arcade['ladder_elim_color'], 0);
	print_submit_row($ibforums->lang['acp_save_settings'], 0);

	print_table_break('', "90%");

	$header = array();
	$header[] = "<div align='center'>".$ibforums->lang['acp_gamesort_icon']."</div>";
	$header[] = $ibforums->lang['acp_gamesort_game'];
	$header[] = $ibforums->lang['acp_gamesort_player'];
	$header[] = $ibforums->lang['acp_tourneylist_start'];
	$header[] = $ibforums->lang['acp_tourneylist_player'];
	$header[] = $ibforums->lang['acp_empty'];
	$colspan = sizeof($header);

	print_table_header($ibforums->lang['acp_tourneylist_head'], $colspan);
	print_form_header('arcade', 'tourney_stuff');
	print_cells_row($header, 1);

	print_description_row("<div align='center'><i><u>- ".$ibforums->lang['acp_tourneylist_active']." -</u></i></div>", 0, $colspan);

        	$this_query = $DB->query("SELECT * FROM ibf_tournaments WHERE champion=''");
        	if( $DB->get_num_rows($this_query) )
        	{
        		while( $TOURNEY = $DB->fetch_row($this_query) )
            		{
            			$DB->query("SELECT gname, gtitle FROM ibf_games_list WHERE gid=".$TOURNEY['gid']);
                		$GAME = $DB->fetch_row();

			$cell = array();

			$cell[] = "<div align='center'><img src='../arcade/images/".$GAME['gname']."2.gif' alt='".$GAME['gtitle']."' title='".$GAME['gtitle']."' /></div>";
			$cell[] = "<div align='center'><b>".$GAME['gtitle']."</b></div>";
			$cell[] = "<div align='center'>".$TOURNEY['numplayers']."</div>";
			$cell[] = "<div align='center'>".timeoutput($TOURNEY['datestarted'])."</div>";
			$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=tourney_stuff&amp;do=view&amp;tid=".$TOURNEY['tid']."'>".$ibforums->lang['acp_tourneylist_show']."</a></div>";
			$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=tourney_stuff&amp;do=remove&amp;tid=".$TOURNEY['tid']."'>".$ibforums->lang['acp_games_delete']."</a></div>";

			print_cells_row($cell);
            		}
        	}
        	else
        	{
		print_description_row("<div align='center'><b>".$ibforums->lang['acp_tourneylist_none']."</b></div>", 0, $colspan);
        	}

	print_description_row("<div align='center'><i><u>- ".$ibforums->lang['acp_tourneylist_ready']." -</u></i></div>", 0, $colspan);

        	$this_query = $DB->query("SELECT * FROM ibf_tournaments WHERE champion<>''");
        	if( $DB->get_num_rows($this_query) )
        	{
            		while( $TOURNEY = $DB->fetch_row($this_query) )
            		{
            			$DB->query("SELECT gname, gtitle FROM ibf_games_list WHERE gid=".$TOURNEY['gid']);
                		$GAME = $DB->fetch_row();

			$cell = array();

			$cell[] = "<div align='center'><img src='../arcade/images/".$GAME['gname']."2.gif' alt='".$GAME['gtitle']."' title='".$GAME['gtitle']."' /></div>";
			$cell[] = "<div align='center'><b>".$GAME['gtitle']."</b> ~ ".$TOURNEY['champion']."</div>";
			$cell[] = "<div align='center'>".$TOURNEY['numplayers']."</div>";
			$cell[] = "<div align='center'>".timeoutput($TOURNEY['datestarted'])."</div>";
			$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=tourney_stuff&amp;do=view&amp;tid=".$TOURNEY['tid']."'>".$ibforums->lang['acp_tourneylist_show']."</a></div>";
			$cell[] = "<div align='center'><a href='arcade.php?$session[sessionurl]&amp;code=tourney_stuff&amp;do=remove&amp;tid=".$TOURNEY['tid']."'>".$ibforums->lang['acp_games_delete']."</a></div>";

			print_cells_row($cell);
            		}
        	}
        	else
        	{
		print_description_row("<div align='center'><b>".$ibforums->lang['acp_tourneylist_none']."</b></div>", 0, $colspan);
        	}

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_tourneylist_newhead']);

	$output = "<select name='the_game' class='dropdown'>\n";

        $game_list = array();

	$cat=""; $catquery="";
	if ($arcade['use_cats'] == 1) { $catquery="cat.pos, cat.c_id, "; }

        $DB->query("	SELECT g.*, cat.password, cat.cat_name 
			FROM ibf_games_list AS g, ibf_games_cats AS cat 
			WHERE g.active = 1 AND g.gcat=cat.c_id AND trim(password)='' 
			ORDER BY ".$catquery."gtitle");

        while( $GAME = $DB->fetch_row() )
        {
		if( $GAME['cat_name'] != $cat && $arcade['use_cats'] == 1 )
		{
			if( preg_match("/optgroup/i", $form) )
			{
				$output .= "</optgroup>";
			}
			$output .= "<optgroup label='".$GAME['cat_name']."'>";
			$cat = $GAME['cat_name'];
		}

        	$output .= "<option value='{$GAME['gid']}'>".$GAME['gtitle']."</option>";
        }

	$output = $output . "</select>&nbsp;&nbsp;";

	$output = $output . "<select name='player_amount' class='dropdown'>\n
			<option value='2'>2 ".$ibforums->lang['acp_tourneylist_player']."</option>\n
			<option value='4'>4 ".$ibforums->lang['acp_tourneylist_player']."</option>\n
			<option value='8'>8 ".$ibforums->lang['acp_tourneylist_player']."</option>\n
			</select>&nbsp;&nbsp;";

	$output = $output . "<select name='tries' class='dropdown'>\n
			<option value='1'>1 ".$ibforums->lang['try']."</option>\n
			<option value='2'>2 ".$ibforums->lang['tries']."</option>\n
			<option value='3' selected='selected'>3 ".$ibforums->lang['tries']."</option>\n
			<option value='4'>4 ".$ibforums->lang['tries']."</option>\n
			<option value='5'>5 ".$ibforums->lang['tries']."</option>\n
			</select>";

	print_description_row($ibforums->lang['acp_tourney_create']."&nbsp;&nbsp;".$output, 0);
	construct_hidden_code('do', 'newt');
	print_submit_row($ibforums->lang['acp_tourneylist_button'], 0);

	print_form_header('arcade', 'tourney_stuff');
	print_table_header($ibforums->lang['acp_tourney_prune_head']);
	$output1 = "<select name='tourneyprune_status' class='dropdown'>\n
			<option value='2'>".$ibforums->lang['acp_tourney_prune_fin']."</option>\n
			<option value='1'>".$ibforums->lang['acp_tourney_prune_run']."</option>\n
			<option value='0'>".$ibforums->lang['acp_tourney_prune_open']."</option>\n
			</select>&nbsp;&nbsp;";
	$output2 = "<input type='text' name='tourneyprune_days' value='30' size='3' class='textinput' />";

	print_description_row($ibforums->lang['acp_tourney_prune_txt'].$output1." ".$ibforums->lang['acp_scoretool_age_old']." ".$output2." ".$ibforums->lang['acp_days'], 0);
	construct_hidden_code('do', 'prunet');
	print_submit_row($ibforums->lang['acp_start'], 0);

//	print_table_break('', "90%");

	print_cp_footer();
	exit;
}

// ##############################
// Tournament functions
// ##############################
if ($action == "tourneysettings")
{
        global $IN, $DB, $vboptions, $vbulletin;

	if (strlen($IN['ladder_color'])>6)
	{
		$IN['ladder_color']="000000";
	}

	if (strlen($IN['ladder_empty_color'])>6)
	{
		$IN['ladder_empty_color']="FFFFFF";
	}

	if (strlen($IN['ladder_qual_color'])>6)
	{
		$IN['ladder_qual_color']="A0FEA0";
	}

	if (strlen($IN['ladder_disqual_color'])>6)
	{
		$IN['ladder_disqual_color']="C22424";
	}

	if (strlen($IN['ladder_elim_color'])>6)
	{
		$IN['ladder_elim_color']="787878";
	}

	if ($IN['tourney_limit1'] < 0)
	{
		$IN['tourney_limit1']=0;
	}

	if ($IN['tourney_limit2'] < 0)
	{
		$IN['tourney_limit2']=0;
	}

	if (($IN['tourney_limit1']>0) && ($IN['tourney_limit2']>0) && ($IN['tourney_limit1']>=$IN['tourney_limit2']))
	{
		$IN['tourney_limit2']=$IN['tourney_limit1']+1;
	}

       	$db_string = $DB->compile_db_update_string( array ( 	'ladder_color'		=> $IN['ladder_color'],
								'ladder_empty_color'	=> $IN['ladder_empty_color'],
								'ladder_qual_color'	=> $IN['ladder_qual_color'],
								'ladder_disqual_color'	=> $IN['ladder_disqual_color'],
								'ladder_elim_color'	=> $IN['ladder_elim_color'],
								'tourney_limit1'	=> $IN['tourney_limit1'],
								'tourney_limit2'	=> $IN['tourney_limit2']
								) );

        $DB->query("UPDATE ibf_games_settings SET ".$db_string);

/*
       	if( $arcade['log'] )
       	{
       		$ADMIN->save_log("");
      	}
*/
	define('CP_REDIRECT', 'arcade.php?code=tourney');
	print_stop_message('saved_settings_successfully');
}

if ($action == "tourney_stuff")
{
        global $IN, $DB, $vboptions, $vbulletin;

        switch( $IN['do'] )
        {
	// ##### prune tournaments (by MrZeropage) #####
	case prunet:
		$timeout = time() - (intval($IN['tourneyprune_days']) * 86400);
		$status = "demare=";
		if (intval($IN['tourneyprune_status']) < 2)
		{
			$status .= $IN['tourneyprune_status'];
		}
		else
		{
			$status .= "1 AND champion<>''";
		}

		$counter=0;

		$gettourney = $DB->query("SELECT tid, datestarted FROM ibf_tournaments WHERE ".$status." AND datestarted < '".$timeout."'");
		while ($tinfo = $DB->fetch_row($gettourney))
		{
			$tid = $tinfo['tid'];
			$DB->query("DELETE FROM ibf_tournament_players WHERE tid=".$tid);
			$DB->query("DELETE FROM ibf_tournament_players_statut WHERE tid=".$tid);
			$DB->query("DELETE FROM ibf_tournaments WHERE tid=".$tid);
		}
/*
               	if( $arcade['log'] )
               	{
               		$ADMIN->save_log("Turnier-Teilnehmer ersetzt.");
               	}
*/

		define('CP_REDIRECT', 'arcade.php?code=tourney');
		print_stop_message('saved_settings_successfully');
		break;

	// ##### new Tournament #####
	case newt:

		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'tourney_stuff');

		construct_hidden_code('do', 'add_t_confirm');
		construct_hidden_code('gid', $IN['the_game']);
		construct_hidden_code('player_amount', $IN['player_amount']);
		construct_hidden_code('tries', $IN['tries']);
		construct_hidden_code('costs', $IN['costs']);

                	$DB->query("SELECT * FROM ibf_games_list WHERE gid=".$IN['the_game']);
                	$GAME = $DB->fetch_row();

		print_table_header($ibforums->lang['acp_newtourney_head1'].$GAME['gtitle'].$ibforums->lang['acp_newtourney_head2']);
		print_description_row($ibforums->lang['acp_newtourney_info'], 0);

		for( $counter = 1 ; $counter <= $IN['player_amount'] ; $counter++ )
                	{
			print_input_row($ibforums->lang['acp_tourneylist_player']." ".$counter, 'users[]', '', 0);
                	}

		print_submit_row($ibforums->lang['acp_newtourney_button'], 0);
		print_cp_footer();
		break;

	// ##### confirm selected players for this tourney #####
            	case add_t_confirm:

		/*
            		if( $IN['users'] == "" )
                	{
                		$IN['users'] = array();
                	}
		*/

                	$i = 0;
			$dbstring="";
                	foreach($IN['users'] as $value)
                	{
                		$user = intval($value);
                    		if($user == 0)
                    		{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'newt');
				print_table_header($ibforums->lang['acp_tool_prune_errhead']);
				print_description_row($ibforums->lang['acp_newtourney_error1']);
				construct_hidden_code('the_game', $IN['gid']);
				construct_hidden_code('player_amount', $IN['player_amount']);
				construct_hidden_code('tries', $IN['tries']);
				construct_hidden_code('costs', $IN['costs']);
				print_submit_row($ibforums->lang['acp_back'], 0);
				print_cp_footer();
				exit;
                    		}
                    		$dbstring .= ($i) ? ", ".$user : $user;
                    		$i++;
                	}

               		$DB->query("SELECT id, name FROM ibf_members WHERE id IN(".$dbstring.") ORDER BY id");

                	if($i != $DB->get_num_rows() )
                	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'newt');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_newtourney_error2']);
			construct_hidden_code('the_game', $IN['gid']);
			construct_hidden_code('player_amount', $IN['player_amount']);
			construct_hidden_code('tries', $IN['tries']);
			construct_hidden_code('costs', $IN['costs']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
                	}
                	else
                	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'do_add_t');
			print_table_header($ibforums->lang['acp_newtourney_head3']);
			construct_hidden_code('gid', $IN['gid']);
			construct_hidden_code('player_amount', $IN['player_amount']);
			construct_hidden_code('users[]', $IN['users']);
			construct_hidden_code('tries', $IN['tries']);
			print_description_row($ibforums->lang['acp_newtourney_confirm']."<br />".$ibforums->lang['acp_newtourney_descr']);

            			$drop_list = array();
                    		$counter = intval($IN['player_amount'])/2;
                    		$drop_list[0] = $ibforums->lang['acp_newtourney_select'];
                    		for( $i = 1 ; $i <= $counter ; $i++ )
                    		{
                			$drop_list[$i] = $ibforums->lang['acp_newtourney_match'].$i;
                    		}
                    		while( $USER = $DB->fetch_row() )
                    		{
				print_select_row("<a href='../index.php?showuser=".$USER['id']."' target='_blank'><b>".$USER['name']."</b></a>", $USER['id'].'_user', $drop_list);
                    		}
				print_yes_no_row($ibforums->lang['acp_newtourney_pm'], 'pmnotify', 1);

				// detect vBplaza
				if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
				{
					print_input_row($ibforums->lang['costs_tourneyj'], 'costs', $IN['costs'], 0);
				}

				$vbversion = substr($vboptions[templateversion],0,3);
				if ($vbversion != "3.0")
				{
					($hook = vBulletinHook::fetch_hook('ibproarcade_acp_newtourney')) ? eval($hook) : false;
				}

                	}
		print_submit_row($ibforums->lang['acp_newtourney_save'], 0);
		print_cp_footer();
		break;

	// ##### add new tournament to database #####
	case do_add_t:

            		$game = array();
                	if( $IN['player_amount'] == '2' )
                	{
                		$rung = 1;
                	}
                	elseif( $IN['player_amount'] == '4' )
                	{
                		$rung = 2;
                	}
                	else
                	{
                		$rung = 3;
                	}

			$tries = intval($IN['tries']);
			if (($tries<1) || ($tries>5))
			{
				// seems to be illegal value, so set it to default
				$tries = 3;
			}

			$costs = $IN['costs'];
			if ((intval($costs) < 0) || ($costs = ""))
			{
				$costs = 0;
			}

                	if( $IN['player_amount'] != 2 )
                	{
                		$counter = intval($IN['player_amount'])/2;
                    		for( $i = 1 ; $i <= $counter ; $i++ )
                    		{
                    			$game[$i] = 0;
                    		}
                	}
                	$match = array();
                	foreach( $IN as $key=>$value )
                	{
                		if( preg_match("#_user#", $key) )
                    		{
                    			if( $value == 0 )
                        			{
							print_cp_header($ibforums->lang['acp_header']);
							print_form_header('arcade', 'newt');
							print_table_header($ibforums->lang['acp_tool_prune_errhead']);
							print_description_row($ibforums->lang['all_user_match']);
							construct_hidden_code('the_game', $IN['gid']);
							construct_hidden_code('player_amount', $IN['player_amount']);
							construct_hidden_code('costs', $IN['costs']);
							print_submit_row($ibforums->lang['acp_back'], 0);
							print_cp_footer();
							exit;
                        			}
                        			$game[$value]++;
                        			if( $game[$value] > 2 )
                        			{
							print_cp_header($ibforums->lang['acp_header']);
							print_form_header('arcade', 'add_t_confirm');
							print_table_header($ibforums->lang['acp_tool_prune_errhead']);
							print_description_row($ibforums->lang['two_per_match']);
							construct_hidden_code('the_game', $IN['gid']);
							construct_hidden_code('player_amount', $IN['player_amount']);
							construct_hidden_code('costs', $IN['costs']);
							print_submit_row($ibforums->lang['acp_back'], 0);
							print_cp_footer();
							exit;
                        			}
                    		}
                    		$key = intval($key);
                    		$match[$key] = $value;
                	}

			$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
			if ($vbversion == "3.0")
			{
				$forumlink = $vboptions['bburl']."/";
			}
			else
			{
				$forumlink = $vbulletin->options['bburl']."/";
			}

                	$db_string = $DB->compile_db_insert_string( array ( 	'gid'           	=> $IN['gid'],
                                                                    		'numplayers'        	=> $IN['player_amount'],
                                                                    		'datestarted'       	=> time(),
										'demare'		=> 1,
										'creat'			=> $main->arcade->user['name'],
										'plibre'		=> 0,
										'nbtries'		=> $tries,
										'cost'			=> $costs,
										'champion'		=> '',
										'url_discut'		=> '',
                                                            					) );
                	$DB->query("INSERT INTO ibf_tournaments (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
                	$tid = $DB->get_insert_id();
                	unset($key);
                	unset($value);
                	foreach( $match as $key=>$value )
                	{
                		if( $key != 0 )
                    		{
                    			$db_string = $DB->compile_db_insert_string( array ( 	'mid'        		=>  $key,
                                             		            				'tid'               	=>  $tid,
                                                     						'rung'              	=>  $rung,
                                                  						'rungscore'         	=>  0,
                                        	      						'faceoff'           	=>  $value,
                                        	      						'timeplayed'        	=>  time(),
                                                						'timesplayed'   	=>  0,
												'notified'		=>  0
                                                            					) );
                        		$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

                			$db_string = $DB->compile_db_insert_string( array ( 	'tid'			=> $tid,
                                                                    				'mid'			=> $key,
                                                                    				'statut'		=> 0,	) ) ;

					$DB->query("INSERT INTO ibf_tournament_players_statut (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

					if ($IN['pmnotify'] == 1)
					{
						// PM-Notification to Tournament-Participants by MrZeropage
						$DB->query("SELECT id, name, email, arcade_pmactive FROM ibf_user WHERE userid=".$key);
						$recip=$DB->fetch_row();

						$senderid = $ibforums->member['id'];
						$DB->query("SELECT id, name FROM ibf_members WHERE id=".$senderid);
						$userinfo = $DB->fetch_row();

						$sendername = $userinfo['name'];
						$recipient = $key;
					
						$title   = $ibforums->lang['pmtourney_title'];
						$message =	$ibforums->lang['pmtourney_text1'].
								$recip['name'].
								$ibforums->lang['pmtourney_text2'].
								$forumlink."arcade.php?act=Arcade&do=viewtourney&tid=".$tid.
								$ibforums->lang['pmtourney_text3'];

						$mailtitle   = $ibforums->lang['mailtourney_title'];
						$mailmessage =	$ibforums->lang['mailtourney_text1'].
								$recip['name'].
								$ibforums->lang['mailtourney_text2'].
								$forumlink."arcade.php?act=Arcade&do=viewtourney&tid=".$tid.
								$ibforums->lang['mailtourney_text3'];

						// does the recipient want to receive any Notifications from the Arcade ?
						if ($recip['arcade_pmactive'] == 1)
						{
							// read Notification-Settings
							$DB->query("SELECT notification FROM ibf_games_settings");
							$setting = $DB->fetch_row();

							// Notification via PM
							if (($setting['notification']=="pm") || ($setting['notification']=="pm+mail"))
							{
								$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".addslashes($sendername)."', '".addslashes($title)."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($recipient))) . "', 0, " . TIMENOW . ", 0, 0)");
								$pmid = $DB->get_insert_id();
								$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$recipient");
								$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$recipient', '0', '0')");
							}

							// Notification via eMail
							if (($setting['notification']=="mail") || ($setting['notification']=="pm+mail"))
							{
								vbmail($recip['email'],$mailtitle,$mailmessage);
							}
						}
					}
		 		}
                	}
/*
                	if( $arcade['log'] )
                	{
                		$ADMIN->save_log("Turnier erstellt");
                	}
*/
		define('CP_REDIRECT', 'arcade.php?code=tourney');
		print_stop_message('saved_settings_successfully');
            		break;

	// ##### view tournament, replace users #####
	case view:

		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'confirm_r');

		$header = array();
		$header[] = "<div align='center'>".$ibforums->lang['acp_tourneylist_player']."</div>";
		$header[] = $ibforums->lang['acp_empty'];
		$colspan = sizeof($header);

		print_table_header($ibforums->lang['acp_tourney_edit'], $colspan);

		$javascript = "	<script language='javascript'>
                                 		<!--
                                    		function set_player(the_id,the_name)
                                    		{
                                    			var the_form = document.forms[0];
                                        			the_form.username.value = the_name;
                                        			the_form.userid.value = the_id;
                                        			the_form.replaceid.disabled = false;
                                        			the_form.button.disabled = false;

                                        			return false;
                                    		}
                                 		-->
                                 		</script>";

		print_description_row($ibforums->lang['acp_tourney_editinfo'].$javascript, 0, $colspan);
		construct_hidden_code('tid', $IN['tid']);
		construct_hidden_code('userid', '');

		print_cells_row($header, 1);

			// make it lookup the first round of the tournament
			$lookup_query = $DB->query("SELECT numplayers FROM ibf_tournaments WHERE tid=".$IN['tid']);
			$lookup = $DB->fetch_row($lookup_query);

			switch ($lookup['numplayers'])
			{
				case '2': $selectrung=" AND t.rung=1"; break;
				case '4': $selectrung=" AND t.rung=2"; break;
				case '8': $selectrung=" AND t.rung=3"; break;
				default : $selectrung="";
			}

                	$this_query = $DB->query("SELECT t.*, m.name AS username, m.id as userid
                                            			FROM ibf_tournament_players AS t
                                            			LEFT JOIN ibf_members AS m
                                            			ON (t.mid = m.id)
                                            			WHERE tid=".$IN['tid'].$selectrung);
                	while($TOURNEY = $DB->fetch_row($this_query))
                	{
			$cell = array();

			$cell[] = "<div align='center'><a href='../index.php?showuser=".$TOURNEY['userid']."' target='_blank'>".$TOURNEY['username']."</a></div>";
			$cell[] = "<div align='center'><a href='#' onclick=\"set_player('".$TOURNEY['userid']."','".$TOURNEY['username']."'); return false;\">".$ibforums->lang['acp_replace']."</a></div>";
			print_cells_row($cell);
                	}

		print_table_break('', "90%");

		print_table_header($ibforums->lang['acp_tourney_edituser']);
		print_description_row($ibforums->lang['acp_tourney_editusertxt']."<input type='text' name='username' value='' size='32' class='textinput' readonly='readonly'>");
		print_input_row($ibforums->lang['acp_tourney_replace'], 'replaceid', '', 0);
		print_submit_row($ibforums->lang['acp_replace'], 0);
		print_cp_footer();
            		break;

	// ##### confirm to replace user in tourney #####
     	case confirm_r:

            		if( !is_numeric($IN['replaceid']) || $IN['replaceid'] <= 0 )
                	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'viewtourney');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_tourney_error1']);
			construct_hidden_code('tid', $IN['tid']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
			break;
                	}
                	if( $IN['replaceid'] == $IN['userid'] )
                	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'viewtourney');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_tourney_error2']);
			construct_hidden_code('tid', $IN['tid']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
			break;
                	}
                	$this_query = $DB->query("SELECT id, name FROM ibf_members WHERE id=".$IN['replaceid']);
                	if( $DB->get_num_rows($this_query) )
                	{
            			$DB->query("SELECT * FROM ibf_tournament_players WHERE mid=".$IN['replaceid']." AND tid=".$IN['tid']);
                    		if( $DB->get_num_rows() )
                    		{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'viewtourney');
				print_table_header($ibforums->lang['acp_tool_prune_errhead']);
				print_description_row($ibforums->lang['acp_tourney_error4']);
				construct_hidden_code('tid', $IN['tid']);
				print_submit_row($ibforums->lang['acp_back'], 0);
				print_cp_footer();
				exit;
				break;
                    		}
                    		$USER = $DB->fetch_row($this_query);

			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'do_replace');
			print_table_header($ibforums->lang['acp_tourney_replaceconf']);
			print_description_row($ibforums->lang['acp_tourney_replacetxt1']."<b>".$IN['username']."</b>".$ibforums->lang['acp_tourney_replacetxt2']."<b>".$USER['name']."</b>".$ibforums->lang['acp_tourney_replacetxt3']);
			construct_hidden_code('tid', $IN['tid']);
			construct_hidden_code('oldmid', $IN['userid']);
			construct_hidden_code('newmid', $IN['replaceid']);
			print_submit_row($ibforums->lang['acp_confirm'], 0);
			print_cp_footer();
                	}
                	else
                	{
			print_cp_header($ibforums->lang['acp_header']);
			print_form_header('arcade', 'viewtourney');
			print_table_header($ibforums->lang['acp_tool_prune_errhead']);
			print_description_row($ibforums->lang['acp_tourney_error3']);
			construct_hidden_code('tid', $IN['tid']);
			print_submit_row($ibforums->lang['acp_back'], 0);
			print_cp_footer();
			exit;
			break;
                	}
            		break;

	// ##### do replace user in tourney #####
    	case do_replace:
            		$DB->query("UPDATE ibf_tournament_players SET mid=".$IN['newmid'].", notified=0 WHERE tid=".$IN['tid']." AND mid=".$IN['oldmid']);
            		$DB->query("UPDATE ibf_tournament_players_statut SET mid=".$IN['newmid']." WHERE tid=".$IN['tid']." AND mid=".$IN['oldmid']);
/*
                	if( $arcade['log'] )
                	{
                		$ADMIN->save_log("Turnier-Teilnehmer ersetzt.");
                	}
*/
		define('CP_REDIRECT', 'arcade.php?code=tourney');
		print_stop_message('saved_settings_successfully');
            		break;

	// ##### remove tournament #####
	case remove:
            		$DB->query("DELETE FROM ibf_tournaments WHERE tid=".$IN['tid']);
			$DB->query("DELETE FROM ibf_tournament_players_statut WHERE tid=".$IN['tid']);
                	$DB->query("DELETE FROM ibf_tournament_players WHERE tid=".$IN['tid']);
/*
		if( $arcade['log'] )
                	{
            			$ADMIN->save_log("Turnier gelöscht.");
                	}
*/
		define('CP_REDIRECT', 'arcade.php?code=tourney');
		print_stop_message('saved_settings_successfully');
     		break;

	default:
            		break;
	}
}




// ##############################
// usergroup permissions
// ##############################
if ($action == "groups")
{
	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'updategroups');

	// generate Category-List for Permission-Selection
	$cats=array();
	$cats[0] = "-- ".$ibforums->lang['acp_all']." --";
	$counter = 1;
       	$DB->query("SELECT * FROM ibf_games_cats ORDER BY pos, cat_name");
       	while( $CAT = $DB->fetch_row() )
       	{
		$protected = "";
		if ($CAT['password']!='') { $protected=" (*)"; }
		$cats[$CAT['c_id']] = $CAT['cat_name'].$protected;
		$counter++;
        }
	if ($counter > 10) { $counter = 10; }

	$DB->query("SELECT g_id, g_title, arcade_access, p_require, max_play, ppd_require, ibpa_cats, tourney FROM ibf_groups");
	while( $GROUP = $DB->fetch_row() )
	{
		print_table_header($GROUP['g_title']);
		if ($GROUP['g_id'] == 1) 	// UserGroupID 1 = unregistered / Guest
		{
			print_select_row($ibforums->lang['acp_group_guest'], $GROUP['g_id']."_access", array('0' => $ibforums->lang['acp_group_noperm'], '1' => $ibforums->lang['acp_group_viewperm'], '3' => $ibforums->lang['acp_group_guestplay'], '4' => $ibforums->lang['acp_group_guestplaysave']), $GROUP['arcade_access']);
			print_input_row($ibforums->lang['acp_group_guestid'], $GROUP['g_id']."_p_require", $GROUP['p_require'], 0);
			$selected = explode(',', $GROUP['ibpa_cats']);
			print_select_row($ibforums->lang['acp_group_catperm'], $GROUP['g_id'].'_catperm[]', $cats, $selected, 0, $counter, true);
		}
		else
		{
			print_select_row($ibforums->lang['acp_group'], $GROUP['g_id']."_access", array('0' => $ibforums->lang['acp_group_noperm'], '1' => $ibforums->lang['acp_group_viewperm'], '2' => $ibforums->lang['acp_group_playperm']), $GROUP['arcade_access']);
			print_input_row($ibforums->lang['acp_group_posts'], $GROUP['g_id']."_p_require", $GROUP['p_require'], 0);
			print_input_row($ibforums->lang['acp_group_ppd'], $GROUP['g_id']."_ppd_require", $GROUP['ppd_require'], 0);
			print_input_row($ibforums->lang['acp_group_gpd'], $GROUP['g_id']."_max_play", $GROUP['max_play'], 0);
			print_yes_no_row($ibforums->lang['acp_group_tourney'], $GROUP['g_id'].'_tourney', $GROUP['tourney']);
			// Cat-Permissions per UG
			$selected = explode(',', $GROUP['ibpa_cats']);
			print_select_row($ibforums->lang['acp_group_catperm'], $GROUP['g_id'].'_catperm[]', $cats, $selected, 0, $counter, true);
		}
		print_table_break('', "90%");
	}
	print_submit_row($ibforums->lang['acp_save_settings'], 0);
	print_cp_footer();
	exit;
}


// ################################
// store usergroup-permissions to DB
// ################################
if ($action == "updategroups")
{
	$the_groups = array();
	$access    	= "";
	$posts     	= "";
	$max        	= "";
	$ppd		= "";
	$tourney	= "";

	$DB->query("SELECT g_id FROM ibf_groups");
	while( $g = $DB->fetch_row() )
	{
		$the_groups[] = $g['g_id'];
	}

        	foreach( $the_groups as $id )
        	{
        		$access = $_POST[ $id."_access" ];
            		$posts = intval($_POST[ $id."_p_require" ]);
            		$max = intval($_POST[ $id."_max_play" ]);
            		$ppd = intval($_POST[ $id."_ppd_require" ]);
			$tourney = intval($_POST[$id."_tourney"]);

			$categories = $_POST[ $id."_catperm" ];
			$cats = "";
			if ($categories != "")
			{
				$cats = implode("," , $categories);
			}

            		if( $posts < 0 )
			{
				$posts = 0;
            		}

            		if( $max < 0 )
            		{
            			$max = 0;
            		}

            		if( $ppd < 0 )
            		{
            			$ppd = 0;
            		}

			if( ($tourney < 0) || ($tourney > 1) || (empty($tourney)) )
			{
				$tourney = 0;
			}

			if ( ($id == 1) && ($posts < 1) && ($access == 4) )
			{
				// make sure userid is set for guest-saving scores!
				$access = 3;
			}

			if ( ($id == 1) && ($access < 4) )
			{
				// reset guestplayer-id if scoresaving is off
				$posts = 0;
			}

			if ($id==1)
			{
				// fixed values for guests
				$tourney=0;
				$ppd=0;
				$max=0;
			}

		$db_string = $DB->compile_db_update_string( array (
								'arcade_access' => $access,
                                                                'p_require'     => $posts,
                                                                'max_play'      => $max,
                                                                'ppd_require'	=> $ppd,
								'ibpa_cats'	=> $cats,
								'tourney'	=> $tourney, 
				) );
		$DB->query("UPDATE ibf_groups SET ".$db_string." WHERE g_id=".$id);
        	}

	/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("");
        	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=groups');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// user-search
// ##############################
if ($action == "user_search")
{
	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'usermanager');
	print_table_header($ibforums->lang['acp_usersearch_header']);
	construct_hidden_code('limitstart', 0);
	construct_hidden_code('limitnumber', 10);	// 10 user per result-page
	print_input_row($ibforums->lang['acp_usersearch_name'], 'name', "", 0);
	print_membergroup_row($ibforums->lang['acp_usersearch_group'], 'group_list', 0, $user);
	print_select_row($ibforums->lang['acp_usersearch_ban'], 'is_banned', array('0' => $ibforums->lang['acp_usersearch_ban_null'], '1' => $ibforums->lang['acp_usersearch_ban_yes'], '2' => $ibforums->lang['acp_usersearch_ban_no']), 0);
	print_select_row($ibforums->lang['acp_usersearch_sort'], 'sort_by', array('name' => $ibforums->lang['username'], 'id' =>$ibforums->lang['acp_usersearch_id'], 'g_title' => $ibforums->lang['acp_usersearch_ugid'], 'posts' => $ibforums->lang['acp_usersearch_posts']), 'name');
	print_select_row($ibforums->lang['acp_usersearch_order'], 'sort_order', array('ASC' => $ibforums->lang['acp_ASC'], 'DESC' => $ibforums->lang['acp_DESC']), 'ASC');
	print_submit_row($ibforums->lang['acp_search'], 0);
	print_cp_footer();
	exit;
}


// ################################
// user-manager
// ################################
if ($action == "usermanager")
{
       	$name = $_POST['name'];
	$IN['is_banned'] = $_POST['is_banned'];
	$IN['sort_by'] = $_POST['sort_by'];
	$IN['sort_order'] = $_POST['sort_order'];
	$IN['in_group'] = $_POST['group_list'];
	$limitstart = $_POST['limitstart'];
	$limitnumber = $_POST['limitnumber'];
	$limitfinish = $limitstart + $limitnumber;

	if (empty($name))
        	{
		$name = "*";
		/*
		define('CP_REDIRECT', 'arcade.php?code=user_search');
        		print_stop_message('no_users_matched_your_query');
		*/
        	}

	if ($name == "*")
	{
		$name = "%";
	}

	$query = "";

        	$a = 0;

	if ($IN['in_group'])
	{
	foreach($IN['in_group'] AS $group)
	{
		if( !($a) )
            		{
		$query .= "AND ";
                	$query .= "g__id IN (";
            		}
            	$query .= ($a) ? "," : "";
            	$query .= $group;
            	$a++;
	}
	}
        	$query .= ") ";

        	if( $a == 0 )
        	{
        		$query = "";
        	}

        	if( $IN['is_banned'] != 0 )
        	{
        		$query .= " AND ";
            		if( $IN['is_banned'] == 1 )
            		{
            			$query .= "arcade_ban=1";
            		}
            		else
            		{
            			$query .= "arcade_ban=0";
            		}
        	}

        	$this_query = $DB->query("	SELECT m.id, m.name, m.mgroup, m.ip_address AS ip, m.posts, g.g_title
        			  	FROM ibf_members AS m
                                    		LEFT JOIN ibf_groups AS g
                                    		ON (m.mgroup = g.g_id)
                                    		WHERE name LIKE '%".$name."%' AND id<>0 ".$query."
                                    		ORDER BY ".$IN['sort_by']." ".$IN['sort_order']."
				");

	$countusers['users'] = $DB->get_num_rows($this_query);

        	$this_query = $DB->query("	SELECT m.id, m.name, m.mgroup, m.ip_address AS ip, m.posts, g.g_title
        			  	FROM ibf_members AS m
                                    		LEFT JOIN ibf_groups AS g
                                    		ON (m.mgroup = g.g_id)
                                    		WHERE name LIKE '%".$name."%' AND id<>0 ".$query."
                                    		ORDER BY ".$IN['sort_by']." ".$IN['sort_order']."
				LIMIT $limitstart, $limitnumber
				");

        	if( $DB->get_num_rows($this_query) )
        	{
		$header = array();
		$header[] = $ibforums->lang['acp_result_name'];
		$header[] = $ibforums->lang['acp_result_usergroup'];
		$header[] = $ibforums->lang['acp_result_postings'];
		$header[] = $ibforums->lang['acp_result_options'];

		$colspan = sizeof($header);
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'usermanager');
		print_table_header($ibforums->lang['acp_result_showing1'].($limitstart+1).$ibforums->lang['acp_result_showing2'].iif($limitfinish > $countusers['users'], $countusers['users'], $limitfinish).$ibforums->lang['acp_result_showing3'].$countusers['users'], $colspan);
		print_cells_row($header, 1);
	}
	else
	{
		define('CP_REDIRECT', 'arcade.php?code=user_search');
		print_stop_message('no_users_matched_your_query');
	}

	// Ausgabe der Ergebnisse
            	while( $member = $DB->fetch_row($this_query) )
	{
		$cell = array();
		$cell[] = "<a href='../index.php?$session[sessionurl]&amp;showuser=$member[id]'><b>$member[name]</b></a>";
		$cell[] = $member['g_title'];
		$cell[] = $member['posts'];
		$cell[] = "<a href='arcade.php?$session[sessionurl]&amp;code=edit_user&amp;user=".$member['id']."'><b>".$ibforums->lang['acp_result_editperm']."</b></a><br /><a href='arcade.php?$session[sessionurl]&amp;code=scores&amp;do=user&amp;user=".$member['id']."'><b>".$ibforums->lang['acp_result_editscore']."</b></a>";
		print_cells_row($cell);
	}

	construct_hidden_code('limitnumber', $limitnumber);
	construct_hidden_code('sort_by', $IN['sort_by']);
	construct_hidden_code('sort_order', $IN['sort_order']);
	construct_hidden_code('name', $name);
	construct_hidden_code('is_banned', $IN['is_banned']);
	construct_hidden_code('group_list', $IN['in_group']);

	if ($limitstart == 0 AND $countusers['users'] > $limitnumber)
	{
		construct_hidden_code('limitstart', $limitstart + $limitnumber);
		print_submit_row($vbphrase['next_page'], 0, $colspan);
	}
	else if ($limitfinish < $countusers['users'])
	{
		construct_hidden_code('limitstart', $limitstart + $limitnumber);
		print_submit_row($vbphrase['next_page'], 0, $colspan, $vbphrase['prev_page'], '', true);
	}
	else if ($limitfinish >= $countusers['users'])
	{
		construct_hidden_code('limitstart', 0);
		print_submit_row($vbphrase['first_page'], 0, $colspan, $vbphrase['prev_page'], '', true);
	}
	else
	{
		print_table_footer();
	}

	print_cp_footer();
	exit;
}


// ##############################
// edit user
// ##############################
if ($action == "edit_user")
{
	$IN['user'] = $_GET['user'];
        	$DB->query("SELECT id, name, is_arcade_mod, arcade_ban, arcade_mod_privs, create_tourney FROM ibf_members WHERE id=".$IN['user']);
	$user = $DB->fetch_row();

	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'save_user');
	print_table_header($ibforums->lang['acp_useredit_header'].$user['name']);
	construct_hidden_code('id', $IN['user']);
	print_yes_no_row($ibforums->lang['acp_group_tourney'], 'create_tourney', $user['create_tourney']);
	print_yes_no_row($ibforums->lang['acp_useredit_moderator'], 'is_mod', $user['is_arcade_mod']);
	print_yes_no_row($ibforums->lang['acp_useredit_banned'], 'is_banned', $user['arcade_ban']);

	print_table_break('', "90%");

	$privs = unserialize($user['arcade_mod_privs']);
	print_table_header($ibforums->lang['acp_useredit_modheader']);
	print_description_row($ibforums->lang['acp_useredit_modinfo']);
	print_yes_no_row($ibforums->lang['acp_useredit_modscore'], 'scores', $privs['scores']);
	print_yes_no_row($ibforums->lang['acp_useredit_modcomment'], 'comments', $privs['comments']);
	print_yes_no_row($ibforums->lang['acp_useredit_modtourney'], 'tourney', $privs['tourney']);
	print_yes_no_row($ibforums->lang['acp_useredit_modchamps'], 'champs', $privs['champs']);
	print_submit_row($ibforums->lang['acp_save'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// save user
// ##############################
if ($action == "save_user")
{
	$privs		= array();
        	$tmp		= array();
        	$tmp['scores']	= $_POST['scores'];
        	$tmp['comments']	= $_POST['comments'];
        	$tmp['tourney']     	= $_POST['tourney'];
        	$tmp['champs']      	= $_POST['champs'];
        	$privs 		= serialize($tmp);
		$IN['create_tourney']	= $_POST['create_tourney'];
		$IN['is_mod']	= $_POST['is_mod'];
		$IN['is_banned']	= $_POST['is_banned'];
		$IN['id']		= $_POST['id'];

        	$db_string = $DB->compile_db_update_string( array (	'is_arcade_mod' 	=> $IN['is_mod'],
									'create_tourney'	=> $IN['create_tourney'],
                                                            				'arcade_ban' 	=> $IN['is_banned'],
                                                            				'arcade_mod_privs'	=> $privs,
                                                    			) );

        	$DB->query("UPDATE ibf_members SET ".$db_string." WHERE id=".$IN['id']);

	/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("Benutzer ".$IN['name']." bearbeitet.");
        	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=user_search');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// edit scores of a user
// ##############################
if ($action == "scores")
{
	global $IN, $vboptions, $vbulletin;

        	$gid = $IN['gid'];
	$javascript = "";

        	if( !isset($IN['remove']) )
        	{
		$javascript = "	<script language='javascript'>
                            			<!--
                            			function check_all(amount)
                                		{
                                			var box_form = document.forms[0];
                                    			var a = '';
                                    			for ( a = 1 ; a < amount ; a++ )
                                    			{
                                    				box_form.elements[a].checked = true;
                                    			}
                                		}
                                		function uncheck_all(amount)
                                		{
                                			var box_form = document.forms[0];
                                    			var a = '';
                                    			for ( a = 1 ; a < amount ; a++ )
                                    			{
                                    				box_form.elements[a].checked = false;
                                    			}
                                		}
                                		function set_comment(the_comment,the_score)
                                		{
                                			var the_form = document.forms[1];
                                    			the_form.comment_input.value = the_comment;
                                    			the_form.score.value = the_score;
                                    			the_form.button.disabled = false;
                                    			the_form.comment_input.disabled = false;
                                    			return false;
                                		}
                            			-->
                            			</script>";

            		if( $IN['do'] == 'game' )
            		{
            			$DB->query("SELECT gtitle FROM ibf_games_list WHERE gid=".$gid);
                		$GAME = $DB->fetch_row();

               		 	$DB->query("SELECT * FROM ibf_games_scores WHERE gid=".$gid." ORDER BY score DESC");
                		if( $DB->get_num_rows() )
                		{
				$header = array();
				$header[] = $ibforums->lang['acp_result_name'];
				$header[] = $ibforums->lang['acp_result_score'];
				$header[] = $ibforums->lang['acp_gamesort_delete'];
				$header[] = $ibforums->lang['acp_result_empty'];	

				$colspan = sizeof($header);
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'tool_multi_del');
				print_table_header($ibforums->lang['acp_score_gamedel'].$GAME['gtitle'], $colspan);

				$description = "	<div align='center'>
                                				<a href='arcade.php?$session[sessionurl]&amp;code=scores&amp;remove=game&amp;game_id=".$gid."'>".$ibforums->lang['acp_score_removeall']."</a>
                               					</div>";

				print_description_row($description.$javascript);
				print_table_break('', "90%");
				print_cells_row($header, 1);

                			$counter = 1;

                			while( $SCORE = $DB->fetch_row() )
                			{
					$cell = array();
					$cell[] = "<a href='../index.php?$session[sessionurl]&amp;showuser=".$SCORE['mid']."' target='_blank'><b>".$SCORE['name']."</b></a>";
					$cell[] = $SCORE['score'];
					$cell[] = "<a href='arcade.php?$session[sessionurl]&amp;code=scores&amp;remove=single&amp;sid=".$SCORE['s_id']."'>".$ibforums->lang['acp_gamesort_delete']."</a>";
					$cell[] = "<input type='checkbox' name='del[]' value='".$SCORE['s_id']."'>";
					print_cells_row($cell);
		               			$counter++;
            				}

				print_table_break('', "90%");

				$counter++;
				$selectall = "<div align='center'><a href='#' onclick=\"check_all('".$counter."'); return false;\">".$ibforums->lang['acp_selectall']."</a> | <a href='#' onclick=\"uncheck_all('".$counter."'); return false;\">".$ibforums->lang['acp_deselectall']."</a></div>";
				$counter--;
				print_description_row($selectall);
				print_submit_row($ibforums->lang['acp_score_removechoice'], 0);
        			}
               	 		else
                		{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'gamelist');
				print_table_header($ibforums->lang['acp_score_gamedel'].$GAME['gtitle'], $colspan);
				print_description_row($ibforums->lang['acp_score_noscores']);
				print_submit_row($ibforums->lang['acp_back'], 0);
                		}
			print_cp_footer();
			exit;
		}

	            	else

            		{
            			$DB->query("SELECT id, name FROM ibf_members WHERE id=".$IN['user']);
                		$USER = $DB->fetch_row();

                		$DB->query("	SELECT s.*, g.gname, g.gtitle FROM ibf_games_scores AS s
                				LEFT JOIN ibf_games_list AS g ON (s.gid = g.gid)
                            				WHERE mid=".$USER['id']." ORDER BY s_id");

			if( $DB->get_num_rows() )
                		{
				$header = array();
				$header[] = $ibforums->lang['acp_gamesort_icon'];
				$header[] = $ibforums->lang['acp_gamesort_game'];
				$header[] = $ibforums->lang['acp_result_score'];
				$header[] = $ibforums->lang['acp_gamesort_comment'];
				$header[] = $ibforums->lang['acp_gamesort_delete'];
				$header[] = $ibforums->lang['acp_result_empty'];

				$colspan = sizeof($header);
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'multi_del');
				print_table_header($ibforums->lang['acp_score_scoredel'].$USER['name'], $colspan);

				$description = "	<div align='center'>
                	                	    		<a href='arcade.php?$session[sessionurl]&amp;code=scores&amp;remove=user&amp;user=".$USER['id']."'>".$ibforums->lang['acp_score_removeall']."</a>
                                				</div>";

				print_description_row($description.$javascript);
				print_table_break('', "90%");
				print_cells_row($header, 1);

                    			$counter = 1;
                    			while( $SCORE = $DB->fetch_row() )
                    			{
                    				$js_comment = $SCORE['comment'];
                        			$js_comment = str_replace("&#39;","\'",$js_comment);

						$linebreak = "";
						if ($SCORE['comment']!="")
						{
							$linebreak = "<br />";

							// parse the comment
							$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
							if ($vbversion == "3.0")
							{
								require_once('./includes/functions_bbcodeparse.php');		
								$parsed_comment = parse_bbcode($SCORE['comment']);
							}
							else
							{
								require_once('./includes/class_bbcode.php');
								$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
								$parsed_comment = $bbcode_parser->parse($SCORE['comment'],0,1);
							}
							$SCORE['comment'] = $parsed_comment;
						}


					$cell = array();
					$cell[] = "<img src='../arcade/images/".$SCORE['gname']."2.gif' alt='".$SCORE['gtitle']."' title='".$SCORE['gtitle']."' width=20 height=20 />";
					$cell[] = $SCORE['gtitle'];
					$cell[] = $SCORE['score'];
					$cell[] = $SCORE['comment'].$linebreak."[ <a href=\"#\" onClick=\"set_comment('".$js_comment."','".$SCORE['s_id']."'); return false;\">".$ibforums->lang['acp_gamesort_edit']."</a> ]";
					$cell[] = "<a href='arcade.php?$session[sessionurl]&amp;code=scores&amp;remove=single&amp;sid=".$SCORE['s_id']."'>".$ibforums->lang['acp_remove']."</a>";
					$cell[] = "<input type='checkbox' name='del[]' value='".$SCORE['s_id']."'>";
					print_cells_row($cell);

					$counter++;
				}

				print_table_break('', "90%");

				$counter++;
				print_description_row("<div align='center'><a href='#' onclick=\"check_all('".$counter."'); return false;\">".$ibforums->lang['acp_selectall']."</a> | <a href='#' onclick=\"uncheck_all('".$counter."'); return false;\">".$ibforums->lang['acp_deselectall']."</a></div>");
				$counter--;
				print_submit_row($ibforums->lang['acp_score_removechoice'], 0);

				print_form_header('arcade', 'edit_comment');
				print_table_header($ibforums->lang['acp_score_commentheader']);
				print_description_row($ibforums->lang['acp_score_commenttext']);
				print_description_row("<div align='center'><input type='text' name='comment_input' value='' size='80' class='textinput'></div>");
				construct_hidden_code('userid', $USER['id']);
				construct_hidden_code('score', "");
				print_submit_row($ibforums->lang['acp_score_comment'], 0);
			}
			else
			{
				print_cp_header($ibforums->lang['acp_header']);
				print_form_header('arcade', 'user_search');
				print_table_header($ibforums->lang['acp_score_scoredel'].$USER['name'], $colspan);
				print_description_row($ibforums->lang['acp_score_noscoreuser']);
				print_submit_row($ibforums->lang['acp_back'], 0);
			}
			print_cp_footer();
		}


        	}
        	else
        	{
        		if( $IN['remove'] == 'user' )
            		{
            			$id = $IN['user'];
                		$DB->query("SELECT name FROM ibf_members WHERE id=".$id);
                		$USER = $DB->fetch_row();
                		$DB->query("DELETE FROM ibf_games_scores WHERE mid=".$id);

                		do_champ_update(1);
				do_league_update();

/*
                		if( $arcade['log'] )
                		{
                			$ADMIN->save_log("Ergebnisse von ".$USER['name']." entfernt.");
                		}
*/
		define('CP_REDIRECT', 'arcade.php?code=gamelist');
		print_stop_message('saved_settings_successfully');
            		}

            		elseif( $IN['remove'] == 'game' )
            		{
            			$gid = $IN['game_id'];
                		$DB->query("DELETE FROM ibf_games_scores WHERE gid=".$gid);

                		do_champ_update(1);
				do_league_update();

/*
                		if( $arcade['log'] )
                		{
                			$ADMIN->save_log("Ergebnisse gelöscht");
                		}
*/

			define('CP_REDIRECT', 'arcade.php?code=gamelist');
			print_stop_message('saved_settings_successfully');
            		}
            	else
            	{
            		$score = $IN['sid'];

	                $DB->query("DELETE FROM ibf_games_scores WHERE s_id=".$score);

                	do_champ_update(1);
			do_league_update();

/*
   	             	if( $arcade['log'] )
                	{
                		$ADMIN->save_log("Ergebnisse gelöscht");
                	}
*/

			define('CP_REDIRECT', 'arcade.php?code=gamelist&cat=0');
			print_stop_message('saved_settings_successfully');
            	}
	}
}


// ##############################
// comment editing
// ##############################
if ($action == "edit_comment")
{
	global $IN;

	$DB->query("UPDATE ibf_games_scores SET comment='".cleansql($IN['comment_input'])."' WHERE s_id=".$IN['score']);
	define('CP_REDIRECT', 'arcade.php?code=scores&do=user&user='.$IN['userid']);
	print_stop_message('saved_settings_successfully');
}


// ##############################
// IP banning
// ##############################
if ($action == "ip_ban")
{
	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'save_ipban');
	print_table_header($ibforums->lang['acp_ipban_header']);
	print_description_row($ibforums->lang['acp_ipban_text']);
        	$arcade['banned_ips'] = str_replace("|", "\n", $arcade['banned_ips']);
	print_textarea_row($ibforums->lang['acp_ipban_field'], 'the_ips', $arcade['banned_ips'], 10, 16);
	print_submit_row($ibforums->lang['acp_save'], 0);
	print_cp_footer();
	exit;
}


// ##############################
// save IP banning settings to DB
// ##############################
if ($action == "save_ipban")
{
        	$ip = $_POST['the_ips'];

        	$ip = trim($std->txt_stripslashes($ip));
        	$ip = str_replace('|', "&#124;", $ip);
        	$ip = preg_replace( "/\n/", '|', str_replace( "\n\n", "\n", str_replace( "\r", "\n", $ip ) ) );
        	$ip = preg_replace( "/\|{1,}\s{1,}?/s", "|", $ip );
        	$ip = preg_replace( "/^\|/", "", $ip );
        	$ip = preg_replace( "/\|$/", "", $ip );
        	$ip = str_replace( "'", '&#39;', $ip );

        	$db_string = $DB->compile_db_update_string( array ( 'banned_ips' => $ip ) );
        	$DB->query("UPDATE ibf_games_settings SET ".$db_string);

	/*
        	if( $arcade['log'] )
        	{
        		$ADMIN->save_log("gesperrte IP-Adressen aktualisiert");
        	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=ip_ban');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// score tools page
// ##############################
if ($action == "score_tools")
{
	print_cp_header($ibforums->lang['acp_header']);
	print_form_header('arcade', 'tool_scores');
	print_table_header($ibforums->lang['acp_scoretool_header']);

	print_description_row($ibforums->lang['acp_scoretool_text']);
	print_input_row($ibforums->lang['acp_scoretool_name'], 'uname', "", 0);

        $game_list = array();
	$games = array();
	$games[0] = $ibforums->lang['acp_scoretool_allgames'];
        $counter = 1;
        $DB->query("SELECT * FROM ibf_games_list ORDER BY gtitle");
        while( $GAME = $DB->fetch_row() )
        {
		$games[$GAME['gid']] = $GAME['gtitle'];
        }

	print_select_row($ibforums->lang['acp_scoretool_games'], 'in_game[]', $games, '', 0, 10, true);

	$output = array();
	$output[] = $ibforums->lang['acp_scoretool_age'];
	$output[] = "	<div align='left'><select name='old_new' class='dropdown'>\n<option value='o'>".$ibforums->lang['acp_scoretool_age_old']."</option>\n<option value='n'>".$ibforums->lang['acp_scoretool_age_new']."</option>\n</select>\n\n
			<input type='text' name='age' size='3' class='textinput'>
			<select name='time_frame' class='dropdown'>\n<option value='h'>".$ibforums->lang['acp_hours']."</option>\n<option value='d'>".$ibforums->lang['acp_days']."</option>\n<option value='m'>".$ibforums->lang['acp_months']."</option>\n</select>\n\n</div>
			";

	print_cells_row($output);
	print_select_row($ibforums->lang['acp_scoretool_sortby'], 'sort', array('name' => $ibforums->lang['acp_scoretool_sname'], 'gid' => $ibforums->lang['acp_scoretool_sgid'], 'score' => $ibforums->lang['acp_scoretool_sscore'], 'datescored' => $ibforums->lang['acp_scoretool_sdate']), 'name');
	print_select_row($ibforums->lang['acp_scoretool_sortorder'], 'order', array('ASC' => $ibforums->lang['acp_ASC'], 'DESC' => $ibforums->lang['acp_DESC']), 'ASC');
	print_select_row($ibforums->lang['acp_scoretool_logic'], 'search_type', array('AND' => $ibforums->lang['acp_AND'], 'OR' => $ibforums->lang['acp_OR']), 'AND');
	print_input_row($ibforums->lang['acp_scoretool_amount'], 'limit', "", 0);

	$javascript = "<script language='javascript' type='text/javascript'>
                        <!--
                            function del_all()
                            {
                                var del_them = confirm('".$ibforums->lang['acp_scoretool_delall_yn']."');
                                if( del_them == true )
                                {
                                	this.location.href='arcade.php?$session[sessionurl]&code=del_all';
                                }
                                else
                                {
                                	return false;
                                }
                            }
                        -->
                        </script>";

        $description = "<div align='center'>
                       	<a href='#' onclick='del_all(); return false;'>".$ibforums->lang['acp_scoretool_delall']."</a>
			&nbsp;
                        <span style='color: #FF0000; font-weight: bold;'>".$ibforums->lang['acp_scoretool_delalltxt1']."</span>
                        <small>".$ibforums->lang['acp_scoretool_delalltxt2']."</small>
                        </div>";

	print_description_row($description.$javascript);

	print_submit_row($ibforums->lang['acp_search'], 0);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_tool_actnames']);
	print_form_header('arcade', 'tool_actnames');
	print_description_row($ibforums->lang['acp_tool_actnamestext']);
	print_input_row($ibforums->lang['acp_tool_actnamesamount'], 'amount_per_row', 500, 0);
        	$DB->query("SELECT COUNT(s_id) AS amount FROM ibf_games_scores");
        	$scores = $DB->fetch_row();
	construct_hidden_code('total', $scores['amount']);
	construct_hidden_code('start', 1);
	print_submit_row($ibforums->lang['acp_start'], 0);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_tool_champs']);
	print_form_header('arcade', 'tool_champs');
	print_description_row($ibforums->lang['acp_tool_champstext']);
	print_select_row($ibforums->lang['acp_tool_champsgames'], 'in_game[]', $games, '', 0, 10, true);
	print_submit_row($ibforums->lang['acp_start'], 0);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_tool_best']);
	print_form_header('arcade', 'tool_best');
	print_description_row($ibforums->lang['acp_tool_besttext']);
	print_select_row($ibforums->lang['acp_tool_champsgames'], 'in_game[]', $games, '', 0, 10, true);
	print_submit_row($ibforums->lang['acp_start'], 0);

	print_table_break('', "90%");

	print_table_header($ibforums->lang['acp_tool_league']);
	print_form_header('arcade', 'tool_league');
	print_description_row($ibforums->lang['acp_tool_leaguetext']);
	print_submit_row($ibforums->lang['acp_start'], 0);

	print_cp_footer();
	exit;
}


// ##############################
// score-tool: prune scores
// ##############################
if ($action == "tool_scores")
{
	global $IN;

        	$type = $IN['search_type'];
        	$query = "";

        	if( !empty($IN['uname']) )
        	{
        		$query .= " name LIKE '%".$IN['uname']."%' ";
        	}
        
	if( !in_array("0" , $IN['in_game']) )
        	{
            		if( !empty($query) )
            		{
            			$query .= $type;
            		}
        		$query .= " gid IN (".implode("," , $IN['in_game']).")";
        	}

        	if( !empty($IN['age']) )
        	{
            		if( !empty($query) )
            		{
            			$query .= $type;
            		}
            		if( $IN['time_frame'] == 'h' )
            		{
            			$time = $IN['age']*(60*60);
            		}
            		elseif( $IN['time_frame'] == 'd' )
            		{
            			$time = $IN['age']*(60*60*24);
            		}
            		else
            		{
            			$time = $IN['age']*(60*60*24*30);
            		}
            		$thetime = time()-$time;
            		$op = ($IN['old_new'] == 'o') ? "<" : ">";
            		$query .= " datescored".$op.$thetime." ";
        	}

        	if( !empty($query) )
        	{
        		$query .= " ORDER BY ".$IN['sort']." ".$IN['order'];
        	}

        	if( !empty($IN['limit']) )
        	{
            		if( !empty($query) && is_numeric($IN['limit']) && $IN['limit'] > 0 )
            		{
            			$query .= " LIMIT 0, ".$IN['limit'];
            		}
        	}

        	if( empty($query) )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'score_tools');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row($ibforums->lang['acp_tool_prune_errtext']);
		print_submit_row($ibforums->lang['acp_tool_prune_errbutt'], 0);
		print_cp_footer();
		exit;
        	}

        	$this_query = $DB->query("SELECT * FROM ibf_games_scores WHERE ".$query);
        	if( $DB->get_num_rows($this_query) )
        	{
		$header = array();
		$header[] = $ibforums->lang['acp_result_score'];
		$header[] = $ibforums->lang['acp_result_name']	;
		$header[] = $ibforums->lang['acp_result_empty'];
		$header[] = $ibforums->lang['acp_result_empty'];
		$colspan = sizeof($header);

		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'tool_multi_del');
		print_table_header($ibforums->lang['acp_tool_prune_header'], $colspan);

		$javascript = "	<script language='javascript'>
                            			<!--
                            			function check_all(amount)
                                		{
                                			var box_form = document.forms[0];
                                    			var a = '';
                                    			for ( a = 1 ; a < amount ; a++ )
                                    			{
                                    				box_form.elements[a].checked = true;
                                    			}
                                		}

                                		function uncheck_all(amount)
                                		{
                                			var box_form = document.forms[0];
                                    			var a = '';
                                			for ( a = 1 ; a < amount ; a++ )
                                			{
                                    				box_form.elements[a].checked = false;
                                			}
                                		}
                            			-->
                            			</script>";

		print_description_row($ibforums->lang['acp_tool_prune_headtxt'].$javascript, 0, $colspan);

		print_cells_row($header, 1);

            		$gnames = array();
            		$counter = 1;
            		while( $RESULT = $DB->fetch_row($this_query) )
            		{
            			if( !isset($gnames[ $RESULT['gid'] ]) )
                		{
                			$DB->query("SELECT gtitle FROM ibf_games_list WHERE gid=".$RESULT['gid']);
                    			$g = $DB->fetch_row();
                    			$gnames[ $RESULT['gid'] ] = $g['gtitle'];
                		}

// Datumformat Zeitzone ?!
                	$time = timeoutput($RESULT['datescored']);

			$cell = array();
			$cell[] = $ibforums->lang['score'].": ".$RESULT['score']."<br />".$ibforums->lang['game'].": ".$gnames[ $RESULT['gid'] ]."<br />".$ibforums->lang['acp_scoretool_sdate'].": ".$time;
			$cell[] = "<a href='../index.php?$session[sessionurl]&amp;showuser=".$RESULT['mid']."' target='_blank'><b>".$RESULT['name']."</b></a>";
			$cell[] = "<a href='./arcade.php?$session[sessionurl]&amp;code=scores&amp;remove=single&amp;sid=".$RESULT['s_id']."'>".$ibforums->lang['acp_remove']."</a>";
			$cell[] = "<input type='checkbox' name='del[]' value='".$RESULT['s_id']."'>";
			print_cells_row($cell);

                		$counter++;
            		}

	$counter++;
	$selectall = "<div align='center'><a href='#' onclick=\"check_all('".$counter."'); return false;\">".$ibforums->lang['acp_selectall']."</a> | <a href='#' onclick=\"uncheck_all('".$counter."'); return false;\">".$ibforums->lang['acp_deselectall']."</a></div>";
	$counter--;
	print_description_row($selectall,0,$colspan);

	print_submit_row($ibforums->lang['acp_remove_selected'], 0, $colspan);
	print_cp_footer();
	exit;
	}
	else
	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'score_tools');
		print_table_header($ibforums->lang['acp_tool_prune_errhead']);
		print_description_row($ibforums->lang['acp_tool_prune_errtxt2']);
		print_submit_row($ibforums->lang['acp_tool_prune_errbutt'], 0);
		print_cp_footer();
		exit;
	}
}

// ##############################
// score-tool: delete multiple scores
// ##############################
if ($action == "tool_multi_del")
{
  	global $IN;

        $s_ids	= $IN['del'];
        $i      = 0;
        if( $s_ids == "" )
        {
        	$s_ids = array();
        }

        foreach( $s_ids as $value )
        {
            if( !$i )
            {
            	$remove .= $value;
                $i++;
            }
            else
            {
            	$remove .= ", ".$value;
            }
        }

        if( $remove == "" )
        {
        	$remove = 0;
        }
        $DB->query("DELETE FROM ibf_games_scores WHERE s_id IN (".$remove.")");

        do_champ_update(1);
	do_league_update();

/*
        if( $this->arcade['log'] )
        {
        	$ADMIN->save_log("Deleted Scores");
        }
*/
	define('CP_REDIRECT', 'arcade.php?code=score_tools');
	print_stop_message('saved_settings_successfully');
}

// ##############################
// score-tools: delete all scores
// ##############################
if ($action == "del_all")
{
    	global $DB;

        $DB->query("DELETE FROM ibf_games_scores");

        do_champ_update(1);
	do_league_update(1);

/*
        if( $this->arcade['log'] )
        {
        	$ADMIN->save_log("Deleted All Scores");
        }
*/
	define('CP_REDIRECT', 'arcade.php?code=score_tools');
	print_stop_message('saved_settings_successfully');
}

// ##############################
// score-tools: update usernames
// ##############################
if ($action == "tool_actnames")
{
	global $IN;

	$total = $IN['total'];
        	$per_row = $IN['amount_per_row'];

        	if( empty($per_row) || !is_numeric($per_row) || $per_row < 1 )
        	{
        		$per_row = 500;
        	}

        	$start = (isset($IN['start'])) ? $IN['start'] : 0;
        	$end = (($start + $per_row) >= $total) ? 1 : 0;
        	$changed = "";
        	$cache = array();

        	$this_query = $DB->query("	SELECT s.mid, s.name AS score_name, m.id, m.name
                      			FROM ibf_games_scores AS s
                                    		LEFT JOIN ibf_members AS m
                                    		ON (s.mid = m.id)
                      			ORDER BY s_id LIMIT ".$start.", ".$per_row);

        	while( $row = $DB->fetch_row($this_query) )
        	{
        		if( ($row['name'] != $row['score_name']) && (!in_array($row['score_name'] , $cache)) && (!empty($row['name'])) )
            		{
            			$cache[] = $row['score_name'];
                		$changed .= "<li>".$row['score_name'].$ibforums->lang['acp_tool_userrename'].$row['name']."</li>";
                		$db_string = $DB->compile_db_update_string( array ( 'name' => $row['name'] ) );
                		$DB->query("UPDATE ibf_games_scores SET ".$db_string." WHERE mid=".$row['id']);
                		$DB->query("UPDATE ibf_games_champs SET champ_name='".$row['name']."' WHERE champ_mid=".$row['id']);

				// update finished tournaments...
				$tourcheck_query = $DB->query("SELECT tid FROM ibf_tournaments WHERE champion='".$row['score_name']."'");
				while ($tourcheck = $DB->fetch_row($tourcheck_query))
				{
					$DB->query("UPDATE ibf_tournaments SET champion='".$row['name']."' WHERE tid=".$tourcheck['tid']);
				}
            		}

            		if( (empty($row['name'])) && (!in_array($row['score_name'] , $cache)) )
            		{
            			$cache[] = $row['score_name'];
            			$changed .= "<li>".$row['score_name'].$ibforums->lang['acp_tool_userdelete']."</li>";
                		$DB->query("DELETE FROM ibf_games_scores WHERE mid=".$row['mid']);
            		}
        	}

        	if( !$end )
        	{
        		$new_start = ($start + $per_row);

		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'tool_actnames');
		print_table_header($ibforums->lang['acp_result_showing0'].$start.$ibforums->lang['acp_result_showing2'].($new_start-1).$ibforums->lang['acp_result_showing3'].$total);
		construct_hidden_code('total', $total);
		construct_hidden_code('amount_per_row', $per_row);
		construct_hidden_code('start', $new_start);
		print_description_row($ibforums->lang['acp_tool_userupdate']);

            		if( !empty($changed) )
            		{
            			$changed_names = "<ol>".$changed."</ol>";
            		}
            		else
            		{
            			$changed_names .= "<b>".$ibforums->lang['acp_tourneylist_none']."</b>";
            		}

		print_description_row($ibforums->lang['acp_tool_userupdatelist'].$changed_names);

		print_submit_row($ibforums->lang['acp_continue'], 0);
		print_cp_footer();
		define('CP_REDIRECT', 'arcade.php?code=score_tools');
		exit;
        	}
        	else
        	{
		/*
        		if( $arcade['log'] )
            		{
            			$ADMIN->save_log("Benutzernamen aktualisiert.");
            		}
		*/

		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'tool_champs');
		print_table_header($ibforums->lang['acp_done']);
		construct_hidden_code('in_game[]', 0);
		print_description_row($ibforums->lang['acp_tool_userdone']);
		define('CP_REDIRECT', 'arcade.php?code=tool_champs');
		print_submit_row($ibforums->lang['acp_continue'], 0);
		print_cp_footer();
		exit;
	}
}


// ##############################
// score-tools: update champs
// ##############################
if ($action == "tool_champs")
{
	global $IN;

        	$query_extra = "";

	if( !in_array("0" , $IN['in_game']) )
	{
		$query_extra .= "WHERE gid IN (".implode("," , $IN['in_game']).")";
	}

        	$the_champs = array();

        	if( $query_extra == "" )
        	{
        		$DB->query("DELETE FROM ibf_games_champs");
        	}

	$game_query = $DB->query("SELECT gid, highscore_type, gtitle FROM ibf_games_list ".$query_extra." ORDER by gid");
	while( $game = $DB->fetch_row($game_query) )
	{
        		$order = ($game['highscore_type'] == "high") ? "DESC" : "ASC";

    		$DB->query("	SELECT s.mid, s.gid, s.name, s.datescored, s.score, g.gtitle 
				FROM ibf_games_scores AS s, ibf_games_list AS g
                			WHERE s.gid=g.gid AND s.gid=".$game['gid']."
                			ORDER BY score ".$order.", timespent ASC");

            		if( $DB->get_num_rows() )
            		{
    			$champ = $DB->fetch_row();
    			$the_champs[] = array(	'gid'	=>	$champ['gid'],
    						'gtitle'	=>	$champ['gtitle'],
                            					'mid'	=>	$champ['mid'],
                            					'name'      =>	$champ['name'],
                            					'date'	=>	$champ['datescored'],
                            					'score'	=>	$champ['score'] );
            		}
	}

	foreach( $the_champs as $this_champ )
	{
            		if( $query_extra != "" )
            		{
            			$db_string = $DB->compile_db_update_string( array ( 	'champ_gid'     	=> $this_champ['gid'],
                                                                 					'champ_gtitle' 	=> $this_champ['gtitle'],
                                                                 					'champ_mid'     	=> $this_champ['mid'],
                                                                 					'champ_name'    	=> $this_champ['name'],
                                                                 					'champ_date'    	=> $this_champ['date'],
                                                                    					'champ_score'	=> $this_champ['score'] ) );
			$DB->query("UPDATE ibf_games_champs SET ".$db_string." WHERE champ_gid=".$this_champ['gid']);
            		}
            		else
            		{
        			$db_string = $DB->compile_db_insert_string( array (	'champ_gid' 	=> $this_champ['gid'],
                                                                					'champ_gtitle'  	=> $this_champ['gtitle'],
                                                             						'champ_mid'     	=> $this_champ['mid'],
                                                                					'champ_name'    	=> $this_champ['name'],
                                                                					'champ_date'    	=> $this_champ['date'],
                                                                					'champ_score'	=> $this_champ['score'] ) );
			$DB->query("INSERT INTO ibf_games_champs (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
            		}
	}

	/*
	if( $arcade['log'] )
	{
		$ADMIN->save_log("Spiele-Sortierung angepasst.");
	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=score_tools');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// score-tools: update best results
// ##############################
if ($action == "tool_best")
{
	global $IN;

       	$query_extra = "";

	if( !in_array("0" , $IN['in_game']) )
	{
		$query_extra .= "WHERE champ_gid IN (".implode("," , $IN['in_game']).")";
	}

	$this_query=$DB->query("SELECT champ_gid, champ_mid, champ_score, champ_time FROM ibf_games_champs ".$query_extra);
	WHILE ($champinfo=$DB->fetch_row($this_query))
	{
		$check_query=$DB->query("SELECT gid, bestscore, besttime, highscore_type FROM ibf_games_list WHERE gid=".$champinfo['champ_gid']);
		$gameinfo=$DB->fetch_row($check_query);

		// update the best result only if current champ-result is really better
		if (($gameinfo['bestscore'] < $champinfo['champ_score'] && $gameinfo['highscore_type']=="high") || ($gameinfo['bestscore'] > $champinfo['champ_score'] && $gameinfo['highscore_type']=="low") || (($gameinfo['bestscore'] == $champinfo['champ_score']) && ($champinfo['champ_time']) < $gameinfo['besttime'] || $gameinfo['besttime']==0))
		{
			$DB->query("UPDATE ibf_games_list SET bestmid=".$champinfo['champ_mid'].", bestscore='".$champinfo['champ_score']."', besttime='".$champinfo['champ_time']."' WHERE gid=".$champinfo['champ_gid']);
		}

		// force update if the is no best result and "the lower the better"
		if (((intval($gameinfo['bestscore'])==0) || ($gameinfo['bestscore']=="")) && ($gameinfo['highscore_type']=="low"))
		{
			$DB->query("UPDATE ibf_games_list SET bestmid=".$champinfo['champ_mid'].", bestscore='".$champinfo['champ_score']."', besttime='".$champinfo['champ_time']."' WHERE gid=".$champinfo['champ_gid']);
		}
	}

	/*
	if( $arcade['log'] )
	{
		$ADMIN->save_log("aktualisiert.");
	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=score_tools');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// score-tools: update league
// ##############################
if ($action == "tool_league")
{
	global $IN;

	$DB->query("DELETE FROM ibf_games_league");

	$game_query = $DB->query("SELECT * FROM ibf_games_list WHERE active=1");

	while ($ginfo = $DB->fetch_row($game_query))
	{
		$ordering = ($ginfo['highscore_type'] == "high") ? "DESC" : "ASC";
		$ctr = 1;
		$this_query = $DB->query("SELECT mid FROM ibf_games_scores WHERE gid='".$ginfo['gid']."' ORDER BY score ".$ordering.", timespent ASC LIMIT 0,10");
		if ($DB->get_num_rows($this_query))
		{
			while($lboard = $DB->fetch_row($this_query))
			{
				switch($ctr)
				{
					case 1: $points = $arcade['league_scores'][0];
					break;
					case 2: $points = $arcade['league_scores'][1];
					break;
					case 3: $points = $arcade['league_scores'][2];
					break;
					case 4: $points = $arcade['league_scores'][3];
					break;
					case 5: $points = $arcade['league_scores'][4];
					break;
					case 6: $points = $arcade['league_scores'][5];
					break;
					case 7: $points = $arcade['league_scores'][6];
					break;
					case 8: $points = $arcade['league_scores'][7];
					break;
					case 9: $points = $arcade['league_scores'][8];
					break;
					case 10: $points = $arcade['league_scores'][9];
					break;
					default: $points = $arcade['league_scores'][10];
				}

				if ($points > 0)
				{
					extract($ginfo);
					$lid = $lboard['mid'];
					$db_string = $DB->compile_db_insert_string( array (	'mid'   	=> $lid,
											'gid'  	=> $gid,
											'position' 	=> $ctr,
											'points'    	=> $points,
											'cat'	=> $gcat, ) );
					$DB->query("INSERT INTO ibf_games_league (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
				}
	
				$ctr++;
			}
		}
	}

	/*
        	if( $arcade['log'] )
        	{
    		$ADMIN->save_log("Spiele-Sortierung angepasst.");
        	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=score_tools');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// custom game sorting
// ##############################
if ($action == "custom")
{
  	if( !$DB->field_exists("position","ibf_games_list") )
        	{
        		$DB->query("ALTER TABLE ibf_games_list ADD position MEDIUMINT( 8 ) DEFAULT '1' NOT NULL");
        	}

        	$DB->query("SELECT g_display_sort AS sort FROM ibf_games_settings");
        	$CONFIG = $DB->fetch_row();

        	if( $CONFIG['sort'] != 'position' )
        	{
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'jumpsettings');
		print_table_header($ibforums->lang['acp_gamesort_err_header']);
		print_description_row($ibforums->lang['acp_gamesort_err_text']);
		print_submit_row($ibforums->lang['acp_gamesort_err_button'], 0);
		print_cp_footer();
		exit;
        	}
        	else
        	{
      		$DB->query("SELECT * FROM ibf_games_list ORDER BY position");

		$header = array();
		$header[] = "<div align='left'>".$ibforums->lang['acp_gamesort_pos']."</div>";
		$header[] = "<div align='center'>".$ibforums->lang['acp_gamesort_icon']."</div>";
		$header[] = "<div align='left'>".$ibforums->lang['acp_gamesort_game']."</div>";

		$colspan = sizeof($header);
		print_cp_header($ibforums->lang['acp_header']);
		print_form_header('arcade', 'sort_games');
		print_table_header($ibforums->lang['acp_gamesort_header'], $colspan);
		print_cells_row($header, 1);

            		while( $GAME = $DB->fetch_row() )
            		{
			$input_name = $GAME['gid']."_position";
			$cell = array();
			$cell[] = "<input type='text' name='$input_name' value='$GAME[position]' size='5' class='textinput'>";
			$cell[] = "<div align='center'><img src='../arcade/images/".$GAME['gname']."1.gif' alt='".$GAME['gtitle']."' title='".$GAME['gtitle']."' /></div>";
			$cell[] = "<div align='left'>".$GAME['gtitle']."</div>";
			print_cells_row($cell);
            		}

		print_submit_row($ibforums->lang['acp_gamesort_button'], 0, $colspan);
		print_cp_footer();
		exit;
	}
}


// ##############################
// store custom game-order to DB
// ##############################
if ($action == "sortgames")
{
	global $IN;

        	$order = array();

        	foreach( $IN as $key=>$value )
        	{
        		if( preg_match("#_position#", $key) )
            		{
            			if( !is_numeric($value) || $value < 0 )
                		{
                			$value = 1;
                		}
                	$key = intval($key);
                	$order[$key] = $value;
            		}
        	}

        	unset($key);
        	unset($value);

        	foreach( $order as $key=>$value )
        	{
            		$db_string = $DB->compile_db_update_string( array ( 'position' => $value ) );
            		$DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid='".$key."'");
        	}

	/*
        	if( $arcade['log'] )
        	{
    		$ADMIN->save_log("Spiele-Sortierung angepasst.");
        	}
	*/

	define('CP_REDIRECT', 'arcade.php?code=custom');
	print_stop_message('saved_settings_successfully');
}


// ##############################
// information
// ##############################
if ($action == "info")
{
	$query = $DB->query("SELECT arcade_language FROM ibf_games_settings");
	$setting = $DB->fetch_row($query);

	$indexcheck = "";
	$javascript = '	<script type="text/javascript">
			<!--
			var newwindow;
			function popup(url,features)
			{
				newwindow=window.open(url,"text",features);
			}
			//-->
			</script>';

	if ($vbulletin->options['forumhome'] != "index")
	{
		if ($vboptions['forumhome'] != "index")
		{
			$homefile = $vbulletin->options['forumhome'].$vboptions['forumhome'];

			if ($setting['arcade_language']=="de")
			{
				$indexcheck = $javascript;
				$indexcheck.= "<b>Hinweis:</b><br />Die Startseite des Forums ist <i>".$homefile.".php</i> - deshalb muss die Datei <i>/index.php</i> angepasst werden, ansonsten werden keine Punkte gespeichert!";
				$indexcheck.= "<dfn>Genaue Informationen zur notwendigen Anpassung sind im ibProArcade-Archiv enthalten: <i>INFO - vbadvanced or other Portal.txt</i> oder in diesem ";
				$indexcheck.= "<a href=\"javascript:popup('arcade.php?code=showtext_index&amp;index={$homefile}', 'height=600,width=550,left=200,top=100,resizable=yes,scrollbars=no,toolbar=no,status=no');\">Popup-Fenster</a>";
				$indexcheck.= "</dfn>";
				$indexcheck.= "<hr />";
			}
			else
			{
				$indexcheck = $javascript;
				$indexcheck.= "<b>Notice:</b><br />Your forum's mainpage is <i>".$homefile.".php</i> - please make sure to adapt your <i>/index.php</i> otherwise scores won't be recorded!";
				$indexcheck.= "<dfn>You will find detailed instructions in the ibProArcade-archive: <i>INFO - vbadvanced or other Portal.txt</i> or in this ";
				$indexcheck.= "<a href=\"javascript:popup('arcade.php?code=showtext_index&amp;index={$homefile}', 'height=600,width=550,left=200,top=100,resizable=yes,scrollbars=no,toolbar=no,status=no');\">popup-window</a>";
				$indexcheck.= "</dfn>";
				$indexcheck.= "<hr />";
			}
		}
	}

	$checkhold = getcwd().'/holdsession.php';
	$holdcheck = "";
	if (!file_exists($checkhold))
	{
		if ($setting['arcade_language']=="de")
		{
			$holdcheck = "<b>ACHTUNG</b><br /><i>holdsession.php</i> fehlt im Hauptverzeichnis des Forums, bitte umgehend dort hochladen!<hr />";
		}
		else
		{
			$holdcheck = "<b>ATTENTION</b><br /><i>holdsession.php</i> is missing in your forums root directory, please upload it.<hr />";
		}
	}

	$paypal_link_en = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';

	$paypal_link_de = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x50.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';

	$paypal_link_en_special = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';

	$paypal_link_de_special = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x50.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';

	$paypal_donate_en = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';

	$paypal_donate_de = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x50.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>';

	$timelimit = 1325289563;

	print_cp_header($ibforums->lang['acp_header']);

	print_description_row("<b><u>ibProArcade v2.7.2+</u></b><dfn>by MrZeropage</dfn><br />");

	if ($setting['arcade_language']=="de")
	{
		print_description_row("Die jeweils aktuelle Version ist <a href='http://www.vbulletin-germany.org/showthread.php?t=709' target='_blank'>hier</a> zu finden.<br />Für deutschen Support bitte <a href='http://www.vbulletin-germany.org/forumdisplay.php?f=28' target='_blank'>hier</a> klicken.<br /><br />");
		print_description_row("<hr />");
		print_description_row($indexcheck);
		print_description_row($holdcheck);
		print_description_row("<table><tr><td valign='center'>".$paypal_donate_de."</td><td valign='top'>Freiwillige Spenden als Danksagung, zur Unterstützung usw. sind jederzeit willkommen - DANKE :)<dfn>Der Spendenbetrag kann frei eingegeben werden.</dfn></td></tr></table>");
		print_description_row("<hr />");
		if (time() > $timelimit)
		{
			print_description_row("<table valign='top'><tr><td valign='center'>".$paypal_link_de."</td><td valign='top'>Um die Versions- und Copyright-Informationen in der Spielhalle zu entfernen, wird eine <i><b>Branding-Free</b></i>-Lizenz angeboten.<br />Der Preis für die Lizenz liegt bei <b>40,- Euro</b><dfn>Die Lizenz ist für eine Installation, eine URL, eine Website gültig.<br />Nach Abschluss der Zahlung bitte per Mail bei <i>vb3hack@gmail.com</i> melden für Anweisung, wie das Copyright entfernt wird.</dfn></td></tr></table><br />");
		}
		else
		{
			print_description_row("<table><tr><td valign='center'>".$paypal_link_de_special."</td><td valign='top'><span style='color: red;'><b>** SONDERAKTION bis 31.12.2011 **</b></span><br />Um die Versions- und Copyright-Informationen in der Spielhalle zu entfernen, wird eine <i><b>Branding-Free</b></i>-Lizenz angeboten.<br />Der <b>Sonderpreis (gültig bis 31.12.2011)</b> für die Lizenz liegt bei <b>25,- Euro</b> (statt 40,-)<dfn>Die Lizenz ist für eine Installation, eine URL, eine Website gültig.<br />Nach Abschluss der Zahlung bitte per Mail bei <i>vb3hack@gmail.com</i> melden für Anweisung, wie das Copyright entfernt wird.</dfn></td></tr></table><br />");
		}
		print_description_row("<hr />");
		print_description_row("<dfn>Zahlungen können auch manuell per PayPal an die Adresse <b><i>vb3hack@gmail.com</b></i> erfolgen.<br />Bitte beachten Sie bei Verwendung der oberen Links, dass diese Adresse als Transaktionsadresse stets oben aufgeführt ist.</dfn>");
	}
	else
	{
		print_description_row("You can get the actual version <a href='http://www.vbulletin.org/forum/showthread.php?t=101554' target='_blank'>here</a>.<br />For english support please <a href='http://www.vbulletin.org/forum/forumdisplay.php?f=170' target='_blank'>click here</a>.<br /><br />");
		print_description_row("<hr />");
		print_description_row($indexcheck);
		print_description_row($holdcheck);
		print_description_row("<table><tr><td valign='center'>".$paypal_donate_en."</td><td valign='top'>Donations to express your THANKS and support for further development are welcome :)<dfn>You can donate any amount, every dollar is welcome.</dfn></td></tr></table>");
		print_description_row("<hr />");
		if (time() > $timelimit)
		{
			print_description_row("<table valign='top'><tr><td valign='center'>".$paypal_link_en."</td><td valign='top'>To remove the Copyright-Information from the Arcade, we offer a <i><b>Branding-Free</b></i>-License.<br />The price per license is <b>50,- US$</b><dfn>One license is valid for one installation, one site, one URL.<br />After the payment is done, please send mail to <i>vb3hack@gmail.com</i> to get instructions about removement.</dfn></td></tr></table><br />");
		}
		else
		{
			print_description_row("<table><tr><td valign='center'>".$paypal_link_en_special."</td><td valign='top'><span style='color: red;'><b>** SPECIAL OFFER valid until 31st december 2011 **</b></span><br />To remove the Copyright-Information from the Arcade, we offer a <i><b>Branding-Free</b></i>-License.<br />The <b>special price (valid until 31st december 2011)</b> for one license is <b>35,- US$</b> (instead of 50,- US$)<dfn>One license is valid for one installation, one site, one URL.<br />After the payment is done, please send mail to <i>vb3hack@gmail.com</i> to get instructions about removement.</dfn></td></tr></table><br />");
		}
		print_description_row("<hr />");
		print_description_row("<dfn>You can pay manually via PayPal to <b><i>vb3hack@gmail.com</b></i> if you want to.<br />Please make sure that this mailaddress is always the recipient for payments, even in the PayPal-Links above!</dfn>");
	}

	print_cp_footer();
}

if ($action=="showtext_index")
{
	$query = $DB->query("SELECT arcade_language FROM ibf_games_settings");
	$setting = $DB->fetch_row($query);

	if ($setting['arcade_language']=="de")
	{
		$t1 = "Öffne";
		$t2 = "Suche nach: (ganz am Anfang der Datei)";
		$t3 = "<b>Darunter</b> füge hinzu:";
		$t4 = "Speichere die Veränderung auf dem Server.";
	}
	else
	{
		$t1 = "Open";
		$t2 = "Search for: (very top of file)";
		$t3 = "<b>Below</b> that add:";
		$t4 = "Save and upload your index.php back to your server.";
	}

	$instructions = '
<span style="font-size: 15pt; color: #0000FF;">'.$t1.' "/index.php"</span>
<br /><br />
'.$t2.'
<br /><br />
<textarea class="row2" rows="2" cols="60">
<?php
</textarea>
<br /><br />
'.$t3.'
<br /><br />
<textarea class="row2" rows="20" cols="60">
// ibProArcade
if($_POST[\'module\'] == "pnFlashGames")
{
	require_once(\'./global.php\');

	switch($_POST[\'func\'])
	{
		case "storeScore":
		$_GET[\'act\'] = "Arcade";
		$_GET[\'module\'] = "arcade";
		$_GET[\'do\'] = "pnFStoreScore";
		break;

		case "saveGame":
		$_GET[\'do\'] = "pnFSaveGame";
		break;

		case "loadGame":
		$_GET[\'do\'] = "pnFLoadGame";
		break;

		case "loadGameScores":
		$gid = $vbulletin->input->clean_gpc(\'p\', \'gid\', TYPE_INT);
		$uid= $vbulletin->userinfo[\'userid\'];
		$game = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "games_scores WHERE mid=$uid AND gid = $gid ORDER BY score DESC LIMIT 0,1");
		$scores = $game[score];

		if($scores != false)
		{
			//Return true
			print "&opSuccess=true&gameScores=$scores&endvar=1"; //send endvar to keep opSuccess separate from all other output from PostNuke
		}
		else
		{
			print "&opSuccess=false&error=Error&endvar=1";
		}
		break;
	}
}

$act = $_GET[act];
$autocom = $_GET[autocom];
$showuser= $_GET[showuser];
if($act == "Arcade" || $autocom=="arcade") {
include "arcade.php";
exit();
}
if(!empty($showuser) && $showuser >= 1) {
$u = $showuser;
$_GET[u] = $showuser;
include "member.php";
exit();
}

// end of ibProArcade
</textarea>
<br /><br />
<span style="color: #FF0000;">'.$t4.'</span>
';

echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ibProArcade</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css" media="all"> 
<!-- 
body { 
   margin-left: 0px; 
   margin-top: 0px; 
   margin-right: 0px; 
   margin-bottom: 0px; 
} 
--> 
</style>
</head>
<body>
{$instructions}
</body></html>
EOF;
}


// ##### function for the improved TAR-installer #####
function copyr($source, $dest)
{
   	// Simple copy for a file
   	if (is_file($source))
	{
       		return copy($source, $dest);
   	}

   	// Make destination directory
   	if (!is_dir($dest))
	{
       		mkdir($dest);
   	}

   	// Loop through the folder
   	$dir = dir($source);
   	while (false !== $entry = $dir->read())
	{
       		// Skip pointers
       		if ($entry == '.' || $entry == '..')
		{
           			continue;
       		}

       		// Deep copy directories
       		if ($dest !== "$source/$entry")
		{
           			copyr("$source/$entry", "$dest/$entry");
       		}
	}

   	// Clean up
   	$dir->close();
   	return true;
}


// #### function to update the league ####
function updateleague()
{
	global $DB, $arcade;

	$DB->query("DELETE FROM ibf_games_league");

	$game_query = $DB->query("SELECT * FROM ibf_games_list WHERE active=1");

	while ($ginfo = $DB->fetch_row($game_query))
	{
		$ordering = ($ginfo['highscore_type'] == "high") ? "DESC" : "ASC";
		$ctr = 1;
		$this_query = $DB->query("SELECT mid FROM ibf_games_scores WHERE gid='".$ginfo['gid']."' ORDER BY score ".$ordering.", timespent ASC LIMIT 0,10");
		if ($DB->get_num_rows($this_query))
		{
			while($lboard = $DB->fetch_row($this_query))
			{
				switch($ctr)
				{
					case 1: $points = $arcade['league_scores'][0];
					break;
					case 2: $points = $arcade['league_scores'][1];
					break;
					case 3: $points = $arcade['league_scores'][2];
					break;
					case 4: $points = $arcade['league_scores'][3];
					break;
					case 5: $points = $arcade['league_scores'][4];
					break;
					case 6: $points = $arcade['league_scores'][5];
					break;
					case 7: $points = $arcade['league_scores'][6];
					break;
					case 8: $points = $arcade['league_scores'][7];
					break;
					case 9: $points = $arcade['league_scores'][8];
					break;
					case 10: $points = $arcade['league_scores'][9];
					break;
					default: $points = $arcade['league_scores'][10];
				}

				if ($points > 0)
				{
					$db_string = $DB->compile_db_insert_string( array (	'mid'   	=> $lboard['mid'],
											'gid'  	=> $ginfo['gid'],
											'position' 	=> $ctr,
											'points'    	=> $points,
											'cat'	=> $ginfo['gcat'], ) );
					$DB->query("INSERT INTO ibf_games_league (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
				}
	
				$ctr++;
			}
		}
	}
}

function copydirr($fromDir,$toDir,$chmod=0757,$verbose=false)
{
$errors=array();
$messages=array();
if (!is_writable($toDir))
   $errors[]='target '.$toDir.' is not writable';
if (!is_dir($toDir))
   $errors[]='target '.$toDir.' is not a directory';
if (!is_dir($fromDir))
   $errors[]='source '.$fromDir.' is not a directory';
if (!empty($errors))
   {
   if ($verbose)
       foreach($errors as $err)
           echo '<strong>Error</strong>: '.$err.'<br />';
   return false;
   }
$exceptions=array('.','..');
$handle=opendir($fromDir);
while (false!==($item=readdir($handle)))
   if (!in_array($item,$exceptions))
       {
       //* cleanup for trailing slashes in directories destinations
       $from=str_replace('//','/',$fromDir.'/'.$item);
       $to=str_replace('//','/',$toDir.'/'.$item);
       if (is_file($from))
           {
           if (@copy($from,$to))
               {
               chmod($to,$chmod);
               touch($to,filemtime($from)); // to track last modified time
               $messages[]='File copied from '.$from.' to '.$to;
               }
           else
               $errors[]='cannot copy file from '.$from.' to '.$to;
           }
       if (is_dir($from))
           {
           if (@mkdir($to))
               {
               chmod($to,$chmod);
               $messages[]='Directory created: '.$to;
               }
           else
               $errors[]='cannot create directory '.$to;
           copydirr($from,$to,$chmod,$verbose);
           }
       }
closedir($handle);
if ($verbose)
   {
   foreach($errors as $err)
       echo '<strong>Error</strong>: '.$err.'<br />';
   foreach($messages as $msg)
       echo $msg.'<br />';
   }
return true;
}

function rm($fileglob)
{
   if (is_string($fileglob)) {
       if (is_file($fileglob)) {
           return unlink($fileglob);
       } else if (is_dir($fileglob)) {
           $ok = rm("$fileglob/*");
           if (! $ok) {
               return false;
           }
           return rmdir($fileglob);
       } else {
           $matching = glob($fileglob);
           if ($matching === false) {
               trigger_error(sprintf('No files match supplied glob %s', $fileglob), E_USER_WARNING);
               return false;
           }      
           $rcs = array_map('rm', $matching);
           if (in_array(false, $rcs)) {
               return false;
           }
       }      
   } else if (is_array($fileglob)) {
       $rcs = array_map('rm', $fileglob);
       if (in_array(false, $rcs)) {
           return false;
       }
   } else {
       trigger_error('Param #1 must be filename or glob pattern, or array of filenames or glob patterns', E_USER_ERROR);
       return false;
   }

   return true;
}

function game_exists($gname)
{
	global $DB, $DUPECHECK;

	if ($DUPECHECK == 0)
	{
		return false;
	}
	else
	{
		$result = $false;
		if ($gname!="")
		{
			$checkquery = $DB->query("SELECT gname FROM ibf_games_list WHERE gname='".$gname."'");
			if ($DB->get_num_rows($checkquery))
			{
				$result = true;
			}
		}
		return $result;
	}
}

function cleansql($value) 
{ 
	if( get_magic_quotes_gpc() ) 
	{ 
		$value = stripslashes( $value ); 
	} 
	//check if this function exists 
	if( function_exists( "mysql_real_escape_string" ) ) 
	{ 
		$value = mysql_real_escape_string( $value ); 
	} 
	//for PHP version < 4.3.0 use addslashes 
	else 
	{ 
		$value = addslashes( $value ); 
	} 
	return $value; 
}

    function clean_value($val)
    {
    	if ($val == "")
    	{
    		return "";
    	}

    	$val = str_replace( "&#032;", " ", $val );

    	if ( $ibforums->vars['strip_space_chr'] )
    	{
    		$val = str_replace( chr(0xCA), "", $val );  //Remove sneaky spaces
    	}

    	$val = str_replace( "&"            , "&amp;"         , $val );
    	$val = str_replace( "<!--"         , "&#60;&#33;--"  , $val );
    	$val = str_replace( "-->"          , "--&#62;"       , $val );
    	$val = preg_replace( "/<script/i"  , "&#60;script"   , $val );
    	$val = str_replace( ">"            , "&gt;"          , $val );
    	$val = str_replace( "<"            , "&lt;"          , $val );
//    	$val = str_replace( "\""           , "&quot;"        , $val );
    	$val = preg_replace( "/\n/"        , "<br />"        , $val ); // Convert literal newlines
    	$val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
    	$val = preg_replace( "/\r/"        , ""              , $val ); // Remove literal carriage returns
    	$val = str_replace( "!"            , "&#33;"         , $val );
    	$val = str_replace( "'"            , "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.

    	// Ensure unicode chars are OK
	$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );

	// Strip slashes if not already done so.
	$val = stripslashes($val);

    	// Swop user inputted backslashes
    	$val = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $val );

	$val = cleansql($val);

    	return $val;
    }

$vbversion = substr($vboptions[templateversion],0,3);
if ($vbversion != "3.0")
{
	($hook = vBulletinHook::fetch_hook('ibproarcade_acp_morecode')) ? eval($hook) : false;
}

?>
