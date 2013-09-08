<?php
//----------------------------------------------------------------------------------------
// ibProArcade v2.7.2+ by MrZeropage (for vBulletin 3.x and 4.x)
// the professional arcade modification for vBulletin
//
// vBulletin-version developed by MrZeropage (starting with v2.5.3+ in december 2005)
// vBulletin-AdminCP for ibProArcade Copyright by MrZeropage
//
// Original ibProArcade v2.5.1 © Chris Kelly 2004 www.ibproarcade.com                  
// Creator and Lead Developer: Chris Kelly                                        
// Co-Devoloper: Elijah Gallatin                                                    
//----------------------------------------------------------------------------------------
// Contributers                                                   
//  tubesock  -  Personal Scores Report Module, v3skin update  
//  stlmike   -  Tar install testing, AllMods         
//----------------------------------------------------------------------------------------
// Thanks to everybody who helped during development or the extensive test-phase
// German support  -> http://www.vbulletin-germany.org
// English support -> http://www.vbulletin.org  
//
// Make sure not to use any converted Game that has no permission from its author !  
//----------------------------------------------------------------------------------------

// #################################################################################
// ### some global switches - only change if you are told to for support-reasons ###
// #################################################################################

$DEBUGMODE	= 0;		// 0 = off / 1 = enable the debugmode / 2 = verbose debugging (support only!)
$NATIVEMODE	= 0;		// set this to 1 to disable all Hooks/PlugIns within ibProArcade
$LOGIPS		= 1;		// set this to 0 to disable logging of IP-addresses

$AVATARRESIZE	= 0;		// set this to 0 to disable resizing of avatars or problems with getimagesize()
$FIXSTYLE	= 0;		// set this to 1 if you get empty pages (tournament-creation ect.)
$FIXIE		= 1;		// current workaround to fix problems with IE7
$MYSQLI		= 0;		// set to 1 to use ibProArcade-mySQLi-driver (may cause problems!)

// ##########################################
// ### !! DO NOT CHANGE ANYTHING BELOW !! ###
// ##########################################

// #################### Add v3arcade Game Support ########################
if (isset($_POST['sessdo']))
{
	if($_POST['sessdo'] == "sessionstart")
	{
	        $gamerand = rand(1,10);
	        $gametime = mktime();
		$lastid = $_POST['gamename'];
	        echo "&connStatus=1&initbar=$gamerand&gametime=$gametime&lastid=$lastid&result=OK";
	        exit;
	}

	if($_POST['sessdo'] == "permrequest")
	{
	        $microone = microtime();
	        setcookie("v3score", $_POST['score']);
	        echo "&validate=1&microone=$microone&result=OK";
	        exit;
	}
}

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ################## Page-Refresher while playing #######################
if ($_GET['do'] == "holdsession")
{
	include ("./holdsession.php");
	exit;
}

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'arcade');
define('ROOT_PATH' , "./" );
define('CAT_IMGS' , "./arcade/cat_imgs/" );
define('MODULE_PATH' , "./arcade/modules/" );
define('FUNCTIONS_PATH', "./arcade/functions/");
define('PATH', (($getcwd = getcwd()) ? $getcwd : '.'));

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array('ARCADE');

// ######################### REQUIRE BACK-END ############################ 
include_once "./global.php";

setcookie('ibPAcookiecheck', "yesss");

$vbversion = substr($vboptions[templateversion],0,3);
if ($vbversion != "3.0")
{
	// make some legacy-variables compatible to vBulletin 3.5.x
	$vboptions[forumhome]	= $vbulletin->options[forumhome];
	$vboptions[bburl]	= $vbulletin->options[bburl];

	if ($NATIVEMODE==0)
	{
		($hook = vBulletinHook::fetch_hook('ibproarcade_global_start')) ? eval($hook) : false;
	}
}

class info {

        var $member     = array();
        var $input      = array();
        var $session_id = "";
        var $base_url   = "";
        var $vars       = "";
        var $skin_id    = "0";     
        var $skin_rid   = "";      
        var $lang_id    = "en";
        var $skin       = "";
        var $lang       = "";
        var $server_load = 0;
        var $version    = "v1.3 Final";
        var $lastclick  = "";
        var $location   = "";
        var $debug_html = "";
        var $perm_id    = "";
        var $forum_read = array();
        var $topic_cache = "";
        var $session_type = "";

        function info() {
                global $sess, $std, $DB, $INFO, $vboptions, $vbulletin, $session;

                $this->vars = &$INFO;

                $this->vars['board_name'] = $vboptions['bbtitle'];
        }
}

include ROOT_PATH . "includes/config.php";

require FUNCTIONS_PATH . "functions.php";

$std   = new FUNC;
$print = new display();
$sess  = new session();

$skin_universal = $std->load_template('skin_global');

$sql_driver = FUNCTIONS_PATH . "dbclass.php";

// support for mysqli by MrZeropage
if ((strtolower($config['Database']['dbtype']) == "mysqli") && ($MYSQLI == 1))
{
	$sql_driver = FUNCTIONS_PATH . "dbclass_mysqli.php";
}

require $sql_driver;
$DB = new db_driver;

// automatic vB-Version-Detection by MrZeroage
$vbversion = substr($vboptions[templateversion],0,3);
if ($vbversion == "3.0")
{
	// DB-Access for vBulletin 3.0.x
	$DB->obj['sql_database']     = $dbname;
	$DB->obj['sql_user']         = $dbusername;
	$DB->obj['sql_pass']         = $dbpassword;
	$DB->obj['sql_host']         = $servername;
	$DB->obj['sql_port']         = $port;
	$DB->obj['sql_tbl_prefix']   = $tableprefix;
	$DB->connect();
	// End DB Connectivity
	unset($dbname, $dbusername, $dbpassword, $servername);
}
else
{
	// DB-Access for vBulletin 3.5.x
	$DB->obj['sql_database']     = $config['Database']['dbname'];
	$DB->obj['sql_user']         = $config['MasterServer']['username'];
	$DB->obj['sql_pass']         = $config['MasterServer']['password'];
	$DB->obj['sql_host']         = $config['MasterServer']['servername'];
	$DB->obj['sql_port']         = $config['MasterServer']['port'];
	$DB->obj['sql_tbl_prefix']   = $config['Database']['tableprefix'];
	$DB->connect();
	// End DB Connectivity
	unset($config['Database']['dbname'], $config['MasterServer']['username'], $config['MasterServer']['password'], $config['Database']['tableprefix']);
}

//--------------------------------
// Wrap it all up in a nice easy to
// transport super class
//--------------------------------

$ibforums = new info();

//--------------------------------
//  Set up our vars
//--------------------------------

$ibforums->input = $std->parse_incoming();

if ($vbversion == "3.0")
{ $forumpath = $vboptions[bburl]; $sess = $session['sessionurl']; }
else
{ $forumpath = $vbulletin->options[bburl]; $sess = $vbulletin->session->vars['sessionurl']; }

// fix for lousy configurated vBulletin options as this eats some time in support...
if (strpos($forumpath,"index.php")===false)
{
	// seems to be correct
}
else
{
	$forumpath = str_replace("/index.php", "", $forumpath);
}

$ibforums->vars['base_url'] = $forumpath . "/" . "arcade.php?".$sess."&amp;";
$ibforums->base_url = $forumpath . "/" . "arcade.php?".$sess."&amp;";

foreach($_POST as $key => $value) {
        $ibforums->input[$key] = $value;
}
foreach($_GET as $key => $value) {
        $ibforums->input[$key] = $value;
}

$ibforums->input['keepsess'] = 0;	// for PNflashgames to keep their session

// handle calls from v3arcade links
if (($ibforums->input['categoryid']!="") && (intval($ibforums->input['categoryid'])>0))
{
	$ibforums->input['cat'] = $ibforums->input['categoryid'];
}

// define possible Guest-Player-ID
$guestplayerid=0;
$DB->query("SELECT arcade_access, p_require FROM ibf_groups WHERE g_id = 1");
$guestperm = $DB->fetch_row();
if ($guestperm['arcade_access']==4)
{
	if (intval($guestperm['p_require'] > 0))
	{ $guestplayerid = $guestperm['p_require']; }
}

// DEBUG-Mode v1.0 by MrZeropage
$DEBUGPAGE = "";
if ($ibforums->input['debug']=="yes")
{
	if ($DEBUGMODE!=0)
	{
		$DEBUGPAGE.= "<b><u>DEBUG-Information</u></b><br />";
		$DEBUGPAGE.= "vboptions[forumhome] &nbsp; = &nbsp; ".$vboptions[forumhome]."<br />";
		$DEBUGPAGE.= "vboptions[bburl] &nbsp; = &nbsp; ".$vboptions[bburl]."<br />";
		if ($vboptions[templateversion]!="")
		{
			$DEBUGPAGE.= "vboptions[templateversion] &nbsp; = &nbsp; ".$vboptions[templateversion]."<br />";
		}
		else
		{
			$DEBUGPAGE.= "vbulletin->options[templateversion] &nbsp; = &nbsp; ".$vbulletin->options[templateversion]."<br />";
		}
		$DEBUGPAGE.= "TABLE_PREFIX &nbsp; = &nbsp; ".TABLE_PREFIX."<br />";
	}
}

if ($vbversion == "3.0")
{
	// vBulletin 3.0.x
	if(isset($bbuserinfo['userid']) && !empty($bbuserinfo['userid']))
	{
        	$ibforums->member['id'] = $bbuserinfo['userid'];
	} 
	else
	{
        	$ibforums->member['id'] = $guestplayerid;
	}
}
else
{
	// vBulletin 3.5.x
	if(isset($vbulletin->userinfo['userid']) && !empty($vbulletin->userinfo['userid']))
	{
		$ibforums->member['id'] = $vbulletin->userinfo['userid'];
	} 
	else
	{
		$ibforums->member['id'] = $guestplayerid;
	}
}

if($_POST['sessdo'] == "burn")
{
	$gamename = ibp_cleansql($_POST['id']);
	$cheat=0;

	$getgameidquery = $DB->query("SELECT gid FROM ibf_games_list WHERE gname='".$gamename."'");
	$getgameid = $DB->fetch_row($getgameidquery);
	$gameid = $getgameid['gid'];

	if ($guestplayerid != $ibforums->member['id'])
	{
		// this is not a guest playing
		$userquery = $DB->query("SELECT * FROM ibf_user WHERE userid=".$ibforums->member['id']);
		$userinfo = $DB->fetch_row($userquery);

		if ($userinfo['arcade_session']=="")
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #020 - no session";
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		// check if there is a stored session for that gameid
		$DB->query("SELECT * FROM ibf_games_session WHERE sessid='".$userinfo['arcade_session']."' LIMIT 1");
		$vs = $DB->fetch_row();
		if ( ($vs['gameid'] != $_COOKIE['gidstarted']) || ($gameid != $vs['gameid']) )
		{
			// cheat! so eliminate the result *g*
			$cheat++;
			$VERBOSE = ($DEBUGMODE == 2) ? " -> vs_gid=".$vs['gameid']." | cookie_gid=".$_COOKIE['gidstarted']." | gameid=".$gameid : "";
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #001";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}
	}
	else
	{
		// this is a guest!
		$userinfo['arcade_session'] = intval($_COOKIE['guestsession']);
	}

	$_POST['gname'] = $gamename;
        $_POST['gscore'] = ibp_cleansql($_COOKIE['v3score']);

        $ibforums->input['do'] = "newscore";
}

class Arcade
{
    var $output           = "";
    var $html             = "";
    var $page_title = "";
    var $nav            = array();
    var $parser         = "";
    var $arcade         = array();
    var $version        = "2.7.2+";
    var $updatecheck	= "http://www.vbulletin-arcade.com/ibpaversion.inc"; // do not change this !!
    var $BFL 		= false;

        function Arcade()
        {
                global $ibforums, $DB, $std, $print, $DEBUGPAGE, $DEBUGMODE, $vbulletin, $vboptions;

        require MODULE_PATH."mod_arcade.php";
        $this->arcade = new mod_arcade;
        $this->arcade->authorize();

	// load the language set in AdminCP (by MrZeropage)
	$ibforums->langid = $this->arcade->settings['arcade_language'];
	$langfile="lang_Arcade_".$ibforums->langid;
	$ibforums->lang = $std->load_words($ibforums->lang, $langfile, $ibforums->lang_id );

                if( $this->arcade->settings['skin'] == 0 )
        {
                    $this->html = $std->load_template('skin_Arcade');
                }
        else
        {
                        $this->html = $std->load_template('skin_v3Arcade');
                }

	// hand some main setting to LANG to reach functions.php
	$ibforums->lang['htmltitle'] = ($this->arcade->settings['htmltitle']) ? $this->arcade->settings['htmltitle'] : "%FORUMNAME% - %IBPRO% - %ACTION%";

        $this->arcade->top_links($this->html);
        $this->output .= $this->arcade->extra_links;

	if ($ibforums->input['debug']=="yes")
	{
		if ($DEBUGMODE!=0)
		{
			$DEBUGPAGE .= "score_type = ".$this->arcade->settings['score_type']."<br />";
			$DEBUGPAGE .= "use_cats = ".$this->arcade->settings['use_cats']."<br />";
			$DEBUGPAGE .= "def_cat = ".$this->arcade->settings['def_cat']."<br />";
			$DEBUGPAGE .= "cats_per_tr = ".$this->arcade->settings['cats_per_tr']."<br />";
			$DEBUGPAGE .= "crown_type = ".$this->arcade->settings['crown_type']."<br />";
			$DEBUGPAGE .= "show_crowns = ".$this->arcade->settings['show_crowns']."<br />";
			$DEBUGPAGE .= "show_t_won = ".$this->arcade->settings['show_t_won']."<br />";
			$DEBUGPAGE .= "notification = ".$this->arcade->settings['notification']."<br />";
			$DEBUGPAGE .= "auto_prune = ".$this->arcade->settings['auto_prune']."<br />";
		}

		$DEBUGPAGE .= "ibProArcade v".$this->version."<br />";
		$this->output .= $DEBUGPAGE."<br />";
	}

        if( isset($ibforums->input['module']) && $ibforums->input['module'] != "arcade" )
        {
                return;
        }
        else
        {
                        switch( $ibforums->input['do'] )
                        {
                    case 'rate':
                        $this->rate();
                    break;

                                case 'play':
                                $this->arcade->play_game_authorize();
                                        $this->play_game();
                                        break;

                                case 'playfull':
                                        $this->arcade->play_game_authorize();
                                        $this->playfull();
                                        break;

                                case 'newscore':
                                        $this->post_score();
                                        break;

                                case 'stats':
                                        $this->show_stats();
                                        break;

                                case 'viewtournaments':
                                        $this->view_tournaments();
                                        break;

                                case 'playtourney':
                                        $this->play_tourney();
                                        break;

                                case 'viewtourney':
                                        $this->view_tourney($ibforums->input['tid']);
                                        break;

				case 'createtourney':
					$this->create_tourney();
					break;

				case 'registertourney':
					$this->register_tourney($ibforums->input['tid']);
					break;

				case 'docreatetourney':
					$this->do_create_tourney();
					break;

				case 'disqualtourney':
					$this->disqual_tournoi($ibforums->input['tid'], $ibforums->input['rung'], $ibforums->input['faceoff'], $ibforums->input['mid']);
					break;

				case 'corigetourney':
					$this->corige_tournoi($ibforums->input['tid'], $ibforums->input['rung'], $ibforums->input['faceoff']);
					break;

				case 'viewtourneyend':
					$this->view_tourney_end();
					break;

   				case 'pnFStoreScore':
    					$this->pnFlashGames_StoreScore();
    					break;
   				case 'pnFSaveGame':
    					$this->pnFlashGames_SaveGame();
    					break;
   				case 'pnFLoadGame':
    					$this->pnFlashGames_LoadGame();
    					break;
				case 'verifyscore':
					$this->verify_score();
					break;
				case 'savescore':
					$this->save_score();
					break;
                                default:
                                        $this->show_games();
                                        break;
                        }

            $action = (isset($ibforums->input['do'])) ? $ibforums->input['do'] : 'glist';
            $show_users = unserialize($this->arcade->settings['show_active']);
            if( $show_users[$action] != 0 )
            {
                    $this->arcade->get_active($this->html);
                    $this->output .= $this->arcade->active;
            }

            $this->output .= $this->html->copyright($this->version,$ibforums->lang['timeformat1'],$this->BFL);
            $print->add_output($this->output);
                $print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav ) );
        }

        }


// All we have to do here is set gscore to the score sent by the game
function pnFlashGames_StoreScore(){
		global $ibforums, $DB;
		// Set the variables as needed
		$gid = intval($ibforums->input['gid']);
	    $sql = "SELECT * FROM ibf_games_list WHERE gid='".$gid."'";
		$DB->query($sql);
		$gameinfo = $DB->fetch_row();
		$ibforums->input['gname'] = $gameinfo['gname'];
		$_GET['gname'] = $ibforums->input['gname'];
		$_POST['gname'] = $ibforums->input['gname'];
       // Convert a time based score to an integer if necessary
        $score = $ibforums->input['score'];
       if(strstr($score, ":") !== false){
           $timestamp = strtotime($score);
           $formatedTime = strftime("%H:%M:%S", $timestamp);
           $hours = substr($formatedTime, 0, 2);
           $minutes = substr($formatedTime, 3, 2);
           $seconds = substr($formatedTime, 6, 2);
           $numSeconds = (($hours * 60) * 60) + ($minutes * 60) + $seconds;

           $score = $numSeconds;
           $ibforums->input['gscore'] = $score;
 }

       $ibforums->input['gscore'] = $ibforums->input['score'];
	   $_POST['gscore'] = $ibforums->input['score'];
 // There might be a better way to do this
 if(isset($_POST['gscore'])){
  // We will only set this if 'score' was set (the pnflashgames score var)
  // Otherwise, the request is still not legit and we will let IbProArcade's security weed that out
  $_POST['gscore'] = $ibforums->input['gscore']; // IbProArcade checks the _POST data, so we have to fake this in order to not get labeled as a cheater
 }

 // pnFlashGames looks for this to make sure the score got saved correctly
 print "&opSuccess=true&endvar=1";

 // tell the scoresaver not to delete the session...
 $_POST['keepsess'] = 1;
 $ibforums->input['keepsess'] = 1;

 // Allow the stock function to the hard work here :)
 $this->post_score();
}

// Save game function
function pnFlashGames_SaveGame(){
 global $ibforums, $DB, $std;

    $gid = intval($ibforums->input['gid']);
    $gameData = ibp_cleansql($_POST['gameData']); //We can't use the $ibforums->input[gameData] because of the changes that are made to the data by the ibforums class.  We need the data in its raw format here
    $uname = ibp_cleansql($this->arcade->user['name']);

    if ((!isset($gid)) ||
        (!isset($uname)) ||
        (!isset($gameData))) {
        // Missing information, exit now
        print "&opSuccess=Missing info&endvar=1";
        return false;
    }

    $savedgames = 'ibf_games_savedGames';

    $sql = "SELECT gameData as oldGameData
      FROM $savedgames
            WHERE uname='$uname'
            AND   gid=$gid";
 $DB->query($sql);

    if($DB->get_num_rows() == 0){
        //No rows found, this user has not stored a high score for this game yet
        $sql = "INSERT INTO $savedgames
                SET gid=$gid,
                    uname='".$uname."',
                    gameData='$gameData',
                    saveDate=NOW()";
    }else{
        //old gameData found so replace it with the new one.
        $sql = "UPDATE $savedgames
                SET    gameData='$gameData',
                       saveDate=NOW()
                WHERE  uname='".$uname."'
                AND    gid=$gid";
    }

    // Do the selected action
 $DB->query($sql);

    print "&opSuccess=true&endvar=1";
}

// Load the users last saved game
function pnFlashGames_LoadGame(){
 global $ibforums, $DB, $std;

    $gid = intval($ibforums->input['gid']);
    $uname = addslashes($this->arcade->user['name']);

    if ((!isset($gid)) ||
        (!isset($uname))) {
        // Missing information, exit now
        return false;
    }

    $savedgames = 'ibf_games_savedGames';
    $sql = "SELECT gameData
      FROM $savedgames
            WHERE uname='$uname'
            AND   gid=$gid";
 $DB->query($sql);

    if($DB->get_num_rows() == 0){
        //No rows found, this user has not stored a high score for this game yet
        $gameData = "";
    }else{
        //Game data found
        $data = $DB->fetch_row();
        $gameData = urlencode(($data['gameData']));
    }

    print "&opSuccess=true&gameData=$gameData&endvar=1";
}
	function pnFlashGames_getDomain(){
		$url = "http://".$_SERVER['HTTP_HOST']."/";
		// get host name from URL
		preg_match("/^(http:\/\/)?([^\/]+)/i",$url, $matches);
		$host = $matches[2];
		$host = str_replace("www.", "", $host);
		return $host;	
	}


function pnFlashGames_getChecksum($file){
 $file = "arcade/".$file.".swf";
 if($fp = fopen($file, 'r')){
  $filecontent = fread($fp, filesize($file));
  fclose($fp);
  return md5($filecontent);
 }else{
  return false;
 }
}
        //------------------------------------------
        // View_Tourney
        //
        // This will show a specific tournament
        //
        //------------------------------------------
        function view_tourney($tid) {
                global $ibforums, $DB, $std;

                $std->time_options['ARCADE'] = "{$ibforums->lang['timeformat4']}";
		$a = explode( ',', gmdate( 'Y,n,j,G,i,s', time() + $std->get_time_offset() ) );

		$day1   = $a[2];
		$month1 = $a[1];
		$year1  = $a[0];

		$tid = intval($tid);

                $DB->query("SELECT t.nbtries, t.demare, t.datestarted, t.cost, t.numplayers, g.gtitle, g.gname, g.gid, g.decpoints, g.highscore_type FROM ibf_tournaments as t, ibf_games_list as g WHERE t.gid = g.gid AND t.tid=".$tid." LIMIT 0, 1");
                $tinfo = $DB->fetch_row();
                $tinfo['datestarted'] = $std->get_date($tinfo['datestarted'],'ARCADE');

		if ($tinfo['highscore_type']=="high")
		{
			$tinfo['hilotext']=$ibforums->lang['tourney_highwin'];
		}
		else
		{
			$tinfo['hilotext']=$ibforums->lang['tourney_lowwin'];
		}

		$tinfo['limit1'] = "";
		$tinfo['limit2'] = "";

		if ($this->arcade->settings['tourney_limit1'] > 0)
		{
			$tinfo['limit1'] = $ibforums->lang['tourney_remind1'].$this->arcade->settings['tourney_limit1'].$ibforums->lang['tourney_remind2']."<br />";
		}

		if ($this->arcade->settings['tourney_limit2'] > 0)
		{
			$tinfo['limit2'] = $ibforums->lang['tourney_disqual1'].$this->arcade->settings['tourney_limit2'].$ibforums->lang['tourney_disqual2']."<br />";
		}

		$tinfo['jackpottxt'] = "";
		if ( (floatval($tinfo['cost']) > 0) && (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1)) )
		{
			$tinfo['jackpottxt'] = $ibforums->lang['tourney_jackpot1']."<b>".$tinfo['cost']."</b><br />";
			$tinfo['jackpottxt'].= $ibforums->lang['tourney_jackpot2']."<b>".(floatval($tinfo['cost'])*intval($tinfo['numplayers']))."</b><br />";
		}

                $DB->query("SELECT champion FROM ibf_tournaments WHERE tid=".$tid);
                $champ = $DB->fetch_row();
                $this->output .= $this->html->tourney_start($ibforums->lang['tournament_ladder'] , $tinfo,$champ['champion']);

                $DB->query("SELECT DISTINCT rung FROM ibf_tournament_players WHERE tid=".$tid." ORDER BY rung DESC LIMIT 0, 1");
                $qrungs = $DB->fetch_row();
                $numrungs = $qrungs['rung'];
		$listenodisqual = "";
		$cptlnodq = 0;

                for($i=1; $i<=$numrungs; $i++)
		{
                        $ctr=0;
			$playerinfo = array();
			$tdclass = array();
                        $refctr = 0;
                        while($refctr <= 7)
			{
                                $playerinfo[$refctr]['name'] = "";
                                $playerinfo[$refctr]['timesplayed'] = 0;
                                $playerinfo[$refctr]['rungscore'] = 0;
                                $playerinfo[$refctr]['playlink'] = "";
				$playerinfo[$refctr]['repairlink'] = "";
				$playerinfo[$refctr]['disquallink'] = "";
				$playerinfo[$refctr]['bgcolor'] = "";
				$playerinfo[$refctr]['dateplayed'] = "";
				$tdclass[$refctr]="";
                                $refctr++;
                        }

			$already=0;
			$user_intourney = false;
			$tourney_started = false;
			if ($tinfo['demare'] > 0)
			{
				$tourney_started = true;
			}

                        $DB->query("SELECT m.name, t.timesplayed, t.timeplayed, t.rungscore, t.mid, t.rung, t.faceoff, ps.statut FROM ibf_tournament_players as t, ibf_members as m, ibf_tournament_players_statut as ps WHERE t.tid=".$tid." AND t.rung=".$i." AND m.id=t.mid AND ps.tid=t.tid AND ps.mid=t.mid ORDER BY t.faceoff ASC");
                        while($row = $DB->fetch_row()) {
				// set correct counter corresponding to faceoff
                                if(($ctr < 2) && ($row['faceoff'] == 2))
				{
                                        $ctr = 2;
                                }

                                if(($ctr < 4) && ($row['faceoff'] == 3))
				{
                                        $ctr = 4;
                                }

                                if(($ctr < 6) && ($row['faceoff'] == 4))
				{
                                        $ctr = 6;
                                }

				// is current user already participating in that tournament ?
				if ($ibforums->member['id'] == $row['mid'])
				{
					$user_intourney = true;
				}

				$playerinfo[$ctr]['disquallink'] = "";
				if($this->arcade->user['is_admin'] && $champ['champion'] == "")
				{
					$cpttest = 0;
					$bool = "false";
					while($cpttest < $cptlnodq)
					{
						if($listenodisqual[$cpttest][mid] == $row['mid'])
						{
							$bool = "true";
							$cpttest = $cptlnodq;
							$playerinfo[$ctr]['disquallink'] = "";
							$playerinfo[(floor($ctr/2))]['repairlink'] = "";
						}
						$cpttest++;
					}
					if($bool == "false")
					{
						$listenodisqual[$cptlnodq][mid] = $row['mid'];
						$cptlnodq++;
						if($row['statut'] == 0 && $row['timesplayed'] < $tinfo['nbtries'])
						{
							$playerinfo[$ctr]['disquallink'] = "<a href='".$ibforums->vars['base_url']."do=disqualtourney&amp;tid=".$tid."&amp;rung=".$row['rung']."&amp;faceoff=".$row['faceoff']."&mid=".$row['mid']."'>".$ibforums->lang['disqualify']."</a>";
						}
						if(floor($ctr/2) != ($crt/2) && $playerinfo[(floor($ctr/2))]['repairlink'] != "")
						{
							$playerinfo[(floor($ctr/2))]['repairlink'] = "<a href='".$ibforums->vars['base_url']."do=corigetourney&amp;tid=$tid&amp;rung=".$row['rung']."&amp;faceoff=".$row['faceoff']."'>".$ibforums->lang['winner_advance']."</a>";
						} else {
							if(floor($ctr/2) == ($ctr/2))
							{
								$playerinfo[(floor($ctr/2))]['repairlink'] = "<a href='".$ibforums->vars['base_url']."do=corigetourney&amp;tid=$tid&amp;rung=".$row['rung']."&amp;faceoff=".$row['faceoff']."'>".$ibforums->lang['winner_advance']."</a>";
							}
						}
					}
				} else {
					$playerinfo[(floor($ctr/2))]['repairlink'] = "";
					$playerinfo[$ctr]['disquallink'] = "";
				}

				if($row['mid'] > 0)
				{
					$playerinfo[$ctr]['name'] = $row['name'];
				} else {
					$playerinfo[$ctr]['name'] = "";
				}
				$diff = "";
				if($tinfo['demare'] == 1)
				{
					$small1="<span class='smallfont'>"; $small2="</span>";
					if(($champ['champion'] == "") && ($row['timesplayed'] < $tinfo['nbtries'])) {
						if($row['timesplayed'] == "0")
						{
							// not played any time yet
							$diff = $small1.$ibforums->lang['no_date']."<br />";
						} 
						else
						{
							$diff = $small1.$ibforums->lang['last_play'].":<br />";
						}
						 
						if (intval($this->diff_dates($row['timeplayed']))>0)
						{
							$red1=""; $red2="";
							if (intval($this->diff_dates($row['timeplayed'])) > $this->arcade->settings['tourney_limit1']-1)
							{ $red1="<font color='red'>"; $red2="</font>"; }

							if (intval($this->diff_dates($row['timeplayed']))<2)
							{
								if (intval($this->diff_dates($row['timeplayed']))==1)
								{ $daytext = $this->diff_dates($row['timeplayed'])." ".$ibforums->lang['acp_day1']; }
								else
								{ $daytext = $ibforums->lang['today']; }
							}
							else
							{
								$daytext = $this->diff_dates($row['timeplayed'])." ".$ibforums->lang['acp_day1'].$ibforums->lang['acp_day2'];
							}
							$diff .= $red1.$daytext.$red2;
						}
						else
						{
							$diff .= $ibforums->lang['today'];
						}

						$diff .= $small2;

					} else {
						$diff = $ibforums->lang['completed']."<br />";
					}

					$playerinfo[$ctr]['timesplayed'] = $row['timesplayed']." / ".$tinfo['nbtries'];
					$playerinfo[$ctr]['dateplayed']	 = $diff;

					if ($row['timesplayed'] < $tinfo['nbtries'])
					{
						$playerinfo[$ctr]['rungscore'] = $ibforums->lang['best_score'].":<br /><b>".$this->arcade->do_arcade_format($row['rungscore'], $tinfo['decpoints'])."</b><br /><br />";
					}
					else
					{
						$playerinfo[$ctr]['rungscore'] = $ibforums->lang['final_score'].":<br /><b>".$this->arcade->do_arcade_format($row['rungscore'], $tinfo['decpoints'])."</b><br /><br />";
					}

					if ($row['timesplayed']==0)
					{
						$playerinfo[$ctr]['rungscore'] = "<br /><br /><br />";
					}

					if($row['statut'] == 1)
					{
						$playerinfo[$ctr]['name'] = "<STRIKE>".$playerinfo[$ctr]['name']."</STRIKE>";
						$playerinfo[$ctr]['bgcolor'] = $this->arcade->settings['ladder_elim_color'];
						$playerinfo[$ctr]['playlink'] = "<b>".$ibforums->lang['eliminated']."</b>";
						$playerinfo[$ctr]['disquallink'] = "";
						$tdclass[$ctr] = "elim";
					}
					if($row['statut'] == 2)
					{
						$playerinfo[$ctr]['name'] = "<STRIKE>".$playerinfo[$ctr]['name']."</STRIKE>";
						$playerinfo[$ctr]['bgcolor'] = $this->arcade->settings['ladder_disqual_color'];
						$playerinfo[$ctr]['playlink'] = "<b>".$ibforums->lang['disqualified']."</b>";
						$playerinfo[$ctr]['disquallink'] = "";
						$tdclass[$ctr] = "disqual";
					}
					if($row['statut'] == 0)
					{
						$playerinfo[$ctr]['bgcolor'] = $this->arcade->settings['ladder_qual_color'];
						if($row['timesplayed'] < $tinfo['nbtries'] && $ibforums->member['id'] == $row['mid']) {
							$playerinfo[$ctr]['playlink'] = "<a href='".$ibforums->vars['base_url']."do=playtourney&amp;gameid=".$tinfo['gid']."&amp;tid=$tid&amp;rung=".$row['rung']."&amp;faceoff=".$row['faceoff']."'>".$ibforums->lang['play']."</a>";
						} else {
							$playerinfo[$ctr]['playlink'] = "";
						}
						$tdclass[$ctr] = "qual";
					}
				} else {
					$playerinfo[$ctr]['timesplayed'] = "";
					$playerinfo[$ctr]['rungscore'] = "";
					$playerinfo[$ctr]['playlink'] = "";
					$playerinfo[(floor($ctr/2))]['repairlink'] = "";
					$playerinfo[$ctr]['disquallink'] = "";
					$playerinfo[$ctr]['bgcolor'] = $this->arcade->settings['ladder_empty_color'];
					$playerinfo[$ctr]['dateplayed'] = "";
						$tdclass[$ctr] = "empty";
                                }

                                $ctr++;
                        }

			// Sort the Tournament to match correct faceoffs - by MrZeropage
			if ($i < $numrungs)
			{
				// not the first round in tournament so query the round before and build variables
				$old=0;
				$DB->query("SELECT m.name, t.timesplayed, t.rungscore, t.mid, t.rung, t.faceoff FROM ibf_tournament_players as t, ibf_members as m WHERE t.tid=".$tid." AND t.rung=".($i+1)." AND m.id=t.mid ORDER BY t.faceoff ASC");
				while ($qoldrow=$DB->fetch_row())
				{
					$oldrow[$old]['name']=$qoldrow['name'];
					$oldrow[$old]['timesplayed']=$qoldrow['timesplayed'];
					$oldrow[$old]['rungscore']=$qoldrow['rungscore'];
					$old++;
				}

				for ($j=0;$j<=$ctr;$j++)
				{
					if ($j==0) { $m=0; $n=1; }
					if ($j==1) { $m=2; $n=3; }
					if ($j==2) { $m=4; $n=5; }
					if ($j==3) { $m=6; $n=7; }

					if (($playerinfo[$j]['name'] != $oldrow[$m]['name']) && ($playerinfo[$j]['name'] != $oldrow[$n]['name']))
					{
						// swap this faceoff
						$tempdata['name']	= $playerinfo[$j]['name'];
						$tempdata['timesplayed']= $playerinfo[$j]['timesplayed'];
						$tempdata['rungscore']	= $playerinfo[$j]['rungscore'];
						$tempdata['playlink']	= $playerinfo[$j]['playlink'];
						$tempdata['disquallink']= $playerinfo[$j]['disquallink'];
						$tempdata['bgcolor']	= $playerinfo[$j]['bgcolor'];
						$tempdata['dateplayed']	= $playerinfo[$j]['dateplayed'];
						$tempdata['tdclass']	= $tdclass[$j];

						// search the old faceoff...
						$oldfaceoff=333;
						for ($k=0;$k<8;$k++)
						{
							if ($oldrow[$k]['name'] == $playerinfo[$j]['name'])
							{
								$oldfaceoff=$k;
							}
						}

						if ($oldfaceoff != 333)
						{
							if (($oldfaceoff==0) || ($oldfaceoff==1)) { $oldmatch = 0; }
							if (($oldfaceoff==2) || ($oldfaceoff==3)) { $oldmatch = 1; }
							if (($oldfaceoff==4) || ($oldfaceoff==5)) { $oldmatch = 2; }
							if (($oldfaceoff==6) || ($oldfaceoff==7)) { $oldmatch = 3; }

							$playerinfo[$j]['name'] 		= $playerinfo[$oldmatch]['name'];
							$playerinfo[$j]['timesplayed'] 		= $playerinfo[$oldmatch]['timesplayed'];
							$playerinfo[$j]['rungscore'] 		= $playerinfo[$oldmatch]['rungscore'];
							$playerinfo[$j]['playlink']		= $playerinfo[$oldmatch]['playlink'];
							$playerinfo[$j]['disquallink']		= $playerinfo[$oldmatch]['disquallink'];
							$playerinfo[$j]['bgcolor']		= $playerinfo[$oldmatch]['bgcolor'];
							$playerinfo[$j]['dateplayed']		= $playerinfo[$oldmatch]['dateplayed'];
							$tdclass[$j]				= $tdclass[$oldmatch];

							$playerinfo[$oldmatch]['name']		= $tempdata['name'];
							$playerinfo[$oldmatch]['timesplayed'] 	= $tempdata['timesplayed'];
							$playerinfo[$oldmatch]['rungscore'] 	= $tempdata['rungscore'];
							$playerinfo[$oldmatch]['playlink']	= $tempdata['playlink'];
							$playerinfo[$oldmatch]['disquallink']	= $tempdata['disquallink'];
							$playerinfo[$oldmatch]['bgcolor']	= $tempdata['bgcolor'];
							$playerinfo[$oldmatch]['dateplayed']	= $tempdata['dateplayed'];
							$tdclass[$oldmatch]				= $tempdata['tdclass'];
						}
					}
				}
			}

			// set WAITING flag to those without any opponent
			for ($j=0;$j<=$ctr;$j++)
			{
				// bad code, I know, but too lazy right now to do a fine one *gg*
				if ($j==0) { $opponent = 1; }
				if ($j==1) { $opponent = 0; }
				if ($j==2) { $opponent = 3; }
				if ($j==3) { $opponent = 2; }
				if ($j==4) { $opponent = 5; }
				if ($j==5) { $opponent = 4; }
				if ($j==6) { $opponent = 7; }
				if ($j==7) { $opponent = 6; }

				if (($playerinfo[$j]['name']=="") || ($playerinfo[$opponent]['name']==""))
				{
					// at least one slot in this faceoff is free!
					if ($playerinfo[$j]['name']=="")
					{
						$playerinfo[$j]['dateplayed']="";
					}
					if ($playerinfo[$opponent]['name']=="")
					{
						$playerinfo[$j]['dateplayed']="";
					}
				}
			}

                        $this->output .= $this->define_ladder($i, $playerinfo, $tdclass);
                }

		if ( !$user_intourney && !$tourney_started && intval($ibforums->member['id'])>0 )
		{
			$this->output .= "<br /><br /><div align='center'><font face='arial' size='3'><strong><a href='".$ibforums->base_url."do=registertourney&amp;tid=".$tid."'>".$ibforums->lang['tourney_joinin']."</a></strong></font></div>";
		}

                 $this->output .= $this->html->tourney_stop();
                 $this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$ibforums->lang['tournament_view'];
                 $this->nav        = array( "<a href='".$ibforums->base_url."act=Arcade'>{$ibforums->lang['page_title']}</a>", "<a href='".$ibforums->base_url."act=Arcade&amp;do=viewtournaments'>".$ibforums->lang['tournament_view']."</a>", $tinfo['gtitle']." ".$ibforums->lang['tournament'] );
        }

        //-----------------------------------
        // View_Tournaments
        //
        // This will show a listing of all
        //  currently active Tournaments
        //
        //-----------------------------------

        function view_tournaments() {
                global $ibforums, $DB, $std, $vbulletin, $vboptions, $NATIVEMODE;

                $std->time_options['ARCADE'] = "{$ibforums->lang['timeformat4']}";

		// Look for the tourneys where the member is registered
		$cpt = 0;
		$DB->query("SELECT * FROM ibf_tournament_players_statut WHERE mid='".$ibforums->member['id']."' AND statut<'3'");
		while($row = $DB->fetch_row()) {
			$tournoi[$cpt]['tid'] = $row['tid'];
			$tournoi[$cpt]['statut'] = $row['statut'];
			$cpt = $cpt + 1;
		}		$this->output .= $this->html->tournament_listing();

                $DB->query("SELECT t.numplayers,t.datestarted,t.tid,g.gtitle,g.gid,t.champion FROM ibf_tournaments as t, ibf_games_list as g WHERE champion = '' AND t.gid = g.gid AND demare>0 ORDER BY datestarted DESC");
                while($row = $DB->fetch_row()) {
                        $row['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;do=viewtourney&amp;tid=".$row['tid']."'>".$ibforums->lang['view_tourney']."</a>";
                        $row['datestarted'] = $std->get_date($row['datestarted'],'ARCADE');
			$row['statut'] = $ibforums->lang['not_in_tourney'];
			$i=0;
			if ($ibforums->member['id'] != 0) {
			while($i<$cpt) {
				if($tournoi[$i]['tid'] == $row['tid'])            // Inscrit
				{
					switch($tournoi[$i]['statut'])
					{
						case '0':
							$row['statut'] = $ibforums->lang['still_in'];
							$row['link'] = "<b>".$row['link']."</b>";
							break;
						case '1':
							$row['statut'] = $ibforums->lang['eliminated_2'];
							break;
						case '2':
							$row['statut'] = $ibforums->lang['disqualified_2'];
							break;
						default:
							echo $ibforums->lang['problem'];
							break;
					}
					$i=$cpt;
				}
				$i = $i + 1;
			}
			}
			$this->output .= $this->html->tournament_actif($row);
		}

		$this->output .= $this->html->tournament_attente();

		$costhtmlstart=""; $costhtmlend="";
		// detect vBplaza
		if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
		{
			$costhtmlstart	= "<br /><span class=\"smallfont\">".$ibforums->lang['costs_tourneyj'];
			$costhtmlend	= "</span>";
		}

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_view_tourney')) ? eval($hook) : false;
		}


		$DB->query("SELECT t.nbtries, t.plibre, t.creat, t.gid, t.numplayers,t.datestarted,t.tid,t.champion,t.cost,g.gtitle FROM ibf_tournaments as t, ibf_games_list as g WHERE t.gid = g.gid AND demare = 0 ORDER BY datestarted DESC");
		while($row = $DB->fetch_row()) {
			$row['link'] = "<a href='".$ibforums->vars['base_url']."do=viewtourney&amp;tid=".$row['tid']."'>".$ibforums->lang['see_this_tourney']."</a>";

			if ($costhtmlstart!="")
			{
				// add the costs to HTML and format it
				$value = $vbulletin->options['vbbux_decimalplaces'];
				if (($vbversion != "3.0") && ($NATIVEMODE==0))
				{
					($hook = vBulletinHook::fetch_hook('ibproarcade_view_tourney_getcosts')) ? eval($hook) : false;
				}
				$costhtmlstart .= vb_number_format($row['cost'], $value);
			}

			$row['inscrire'] = "<a href='".$ibforums->vars['base_url']."do=registertourney&amp;tid=".$row['tid']."'>".$ibforums->lang['register']."</a>".$costhtmlstart.$costhtmlend;
			$i=0;
			while($i<$cpt)
			{
				if($tournoi[$i]['tid'] == $row['tid'])            // Inscrit
				{
					$row['inscrire'] = $ibforums->lang['registered'];
					$i=$cpt;
				}
				$i++;
			}

			$row['datestarted'] = $std->get_date($row['datestarted'],'ARCADE');
			$row['creat'] = $row['creat'];

			if (intval($row['plibre'])==1)
			{
				$row['plibre'] = "<font color='red'><b>".$row['plibre']."</b></font>";
				if ($row['inscrire'] != $ibforums->lang['registered'])
				{
					$row['inscrire'] = "<b>".$row['inscrire']."</b>";
				}
			}

			if (intval($ibforums->member['id']) == 0)
			{
				$row['inscrire'] = "";
			}

			$this->output .= $this->html->tournament_attente_row($row);
		}

		$this->output .= $this->html->stop("&nbsp;", "&nbsp;");

		$this->output .= "<div align='center'><a href='{$ibforums->base_url}act=Arcade&amp;do=createtourney'><b>".$ibforums->lang['create_new_t']."</b></a></div><br /><br />";
		$this->output .= "<div align='center'><a href='{$ibforums->base_url}act=Arcade&amp;do=viewtourneyend'><b>".$ibforums->lang['acp_tourneylist_ready']."</b></a></div><br />";

 		$this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$ibforums->lang['tournament_view'];
 		$this->nav        = array( "<a href='{$ibforums->vars['base_url']}?act=Arcade'>{$ibforums->lang['page_title']}</a>", $ibforums->lang['tournament_view'] );
	}

        function define_ladder($rung, $playerinfo, $tdclass) {
		global $ibforums;

		for ($nr=0;$nr<9;$nr++)
		{
			if ($playerinfo[$nr]['name']=="")
			{
				$playerhtml[$nr]="<br /><br /><br /><br /><br />";
			}
			else
			{
				$playerhtml[$nr]="<b>".$playerinfo[$nr]['name']."</b><br />";
				if ($playerinfo[$nr]['timesplayed']!="")
				{
					$playerhtml[$nr] .= $ibforums->lang['tourneytxt1']."<br />".$playerinfo[$nr]['timesplayed']."<br />".$ibforums->lang['tourneytxt2']."<br />".
							"<b>".$playerinfo[$nr]['rungscore']."</b>".$playerinfo[$nr]['dateplayed']."<br />";
				}

				if ($playerinfo[$nr]['playlink']!="")
				{ $playerhtml[$nr] = $playerhtml[$nr] . $playerinfo[$nr]['playlink'] . "<br />"; }
				else
				{ $playerhtml[$nr] = $playerhtml[$nr] . "<br />"; }

				if ($playerinfo[$nr]['disquallink']!="")
				{ $playerhtml[$nr] = $playerhtml[$nr] . $playerinfo[$nr]['disquallink'] . "<br />"; }
				else
				{ $playerhtml[$nr] = $playerhtml[$nr] . "<br />";}

				$playerhtmlend_big[$nr]="";
//				if (strlen($playerinfo[$nr]['name'])>12)
//				{ $playerhtmlend_big[$nr]="<br /><br />"; }

				$playerhtmlend_small[$nr]="";
//				if (strlen($playerinfo[$nr]['name'])>14)
//				{ $playerhtmlend_small[$nr]="<br /><br />"; }
			}
		}

                $tablecolor = $this->arcade->settings['ladder_color'];
                switch ($rung) {
                        case 1:
                                $ladderhtml = "
					<!-- CSS Stylesheet -->
					<style type='text/css' id='tournament_css'>
					<!--
					.empty
					{
						background: #{$this->arcade->settings['ladder_empty_color']};
					}
					.qual
					{
						background: #{$this->arcade->settings['ladder_qual_color']};
					}
					.elim
					{
						background: #{$this->arcade->settings['ladder_elim_color']};
					}
					.disqual
					{
						background: #{$this->arcade->settings['ladder_disqual_color']};
					}
					-->
					</style>
					<!-- / CSS Stylesheet -->

					  <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                <table border=\"1\" width=\"200\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>

                                <table border=\"0\" width=\"200\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td></tr>
                                </table>
                                <table border=\"0\" width=\"300\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='".$tdclass[0]."'>
					{$playerhtml[0]}
					{$playerhtmlend_big[0]}
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[0]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='".$tdclass[1]."'>
					{$playerhtml[1]}
					{$playerhtmlend_big[1]}
                                </td></tr>
                                </table>
                                </td></tr>
                                </table>";
                                break;

                        case 2:
                                $ladderhtml = "<table border=\"0\" width=\"200\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td></tr>
                                </table>

                                <table border=\"0\" width=\"500\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"200\">
                                <table border=\"1\" width=\"200\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"200\">
                                <table border=\"1\" width=\"200\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td></tr>
                                </table>

                                <table border=\"0\" width=\"500\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                </tr>
                                </table>

                                <table border=\"0\" width=\"700\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[0]'>
					{$playerhtml[0]}
					{$playerhtmlend_big[0]}
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[0]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[1]'>
					{$playerhtml[1]}
					{$playerhtmlend_big[1]}
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[2]'>
					{$playerhtml[2]}
					{$playerhtmlend_big[2]}
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[1]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[3]'>
					{$playerhtml[3]}
					{$playerhtmlend_big[3]}
                                </td></tr>
                                </table>
                                </td>
                                </tr>
                                </table>";
                                break;

                        case 3:
                                $infoboxsize = 90;
				$infoboxhsize = 150;
                                $spacesize = 10;
                                $fontsize = 1;
                                $ladderhtml = "<table border=\"0\" width=\"500\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"198\">
                                <table border=\"0\" width=\"192\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                </tr>
                                </table>

                                <table border=\"0\" width=\"700\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"0\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"100\">
                                <table border=\"1\" width=\"100\" height=\"1\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                </tr>
                                </table>

                                <table border=\"0\" width=\"700\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"88\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"96\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"88\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"96\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"88\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"96\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"88\">
                                <table border=\"0\" width=\"100%\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"1\">
                                <table border=\"1\" width=\"1\" height=\"50\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"{$tablecolor}\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                </tr>
                                </table>

                                <table border=\"0\" width=\"800\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[0]'>
                                        <font size='{$fontsize}'>
					$playerhtml[0]
					$playerhtmlend_small[0]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[0]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[1]'>
                                        <font size='{$fontsize}'>
					$playerhtml[1]
					$playerhtmlend_small[1]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[2]'>
                                        <font size='{$fontsize}'>
					$playerhtml[2]
					$playerhtmlend_small[2]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[1]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[3]'>
                                        <font size='{$fontsize}'>
					$playerhtml[3]
					$playerhtmlend_small[3]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[4]'>
                                        <font size='{$fontsize}'>
					$playerhtml[4]
					$playerhtmlend_small[4]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[2]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[5]'>
                                        <font size='{$fontsize}'>
					$playerhtml[5]
					$playerhtmlend_small[5]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td></td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[6]'>
                                        <font size='{$fontsize}'>
					$playerhtml[6]
					$playerhtmlend_small[6]
					</font></td>
                                </td></tr>
                                </table>
                                </td>
                                <td width=\"{$spacesize}\">
                                <table border=\"0\" width=\"{$spacesize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
				<tr><td align=center>{$playerinfo[3]['repairlink']}</td></tr>
                                </table>
                                </td>
                                <td width=\"{$infoboxsize}\" valign=\"top\">
                                <table border=\"1\" width=\"{$infoboxsize}\" height=\"100\" align=\"center\" bordercolor=\"{$tablecolor}\" cellspacing=\"0\" cellpadding=\"0\">
                                <tr><td align='center' class='$tdclass[7]'>
                                        <font size='{$fontsize}'>
					$playerhtml[7]
					$playerhtmlend_small[7]
					</font></td>
                                </tr>
                                </table>
                                </td>
                                </table>";
                                break;
                }
                return $ladderhtml;
        }


        //------------------------------------------
        // Show_Games
        //
        // This shows the list of games available
        //
        //------------------------------------------

        function show_games()
        {
                global $ibforums, $DB, $std, $vbulletin, $vboptions, $NATIVEMODE;

        $games = array();
        $game_gids = array();
        $game_string = "(0)";
        $rowcol = "alt1";
        $total_num = 0;

	// cleanup some incoming stuff
	$ibforums->input['gsearch'] = ibp_cleansql($ibforums->input['gsearch']);

	$stylecolumns = $this->arcade->settings['games_pr'];	// this affects the ibPro-Style only!

        // Newest Games
                $DB->query("SELECT g.gid, g.gtitle, g.gname, g.gcat, cat.password, cat.active FROM ibf_games_list AS g, ibf_games_cats AS cat WHERE g.active=1 AND cat.active=1 AND g.gcat=cat.c_id AND trim(password)='' ORDER BY g.added DESC LIMIT ".$this->arcade->settings['games_new']);
                $firstnew = true;
                while($newgline = $DB->fetch_row()) {
                        if($firstnew) {
                                $firstnew = false;
                        } else {
                                $new_games .= "<br />\n";
                        }

                        $new_games .= "<img src='arcade/images/{$newgline[gname]}2.gif' alt='' width='20' height='20' /> <a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid={$newgline['gid']}'>{$newgline['gtitle']}</a> ";
                        }
                // End Newest Games

// Begin Most Popular Games
        $DB->query("SELECT g.gid, g.gtitle, g.gname, g.gcat, cat.password, g.gcount, cat.active FROM ibf_games_list AS g, ibf_games_cats AS cat WHERE g.active=1 AND cat.active=1 AND g.gcat=cat.c_id AND trim(password)='' ORDER BY g.gcount DESC LIMIT ".$this->arcade->settings['games_popular']);
                 $firstpop = true;
                 while($popgline = $DB->fetch_row()) {
                 if($firstpop) {
                    $firstpop = false;
                 } else {
                    $pop_games .= "<br />\n";
                 }
                    $pop_games .= " <a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid={$popgline['gid']}'>{$popgline['gtitle']}</a> <a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid={$popgline['gid']}'><img src='arcade/images/{$popgline[gname]}2.gif' border='0' alt='".$ibforums->lang[times_played].$popgline['gcount']."x' width='20' height='20' /></a>";
}
//Most End Popular Games

                // Random Game
                $DB->query("SELECT g.gid, g.gtitle, g.gname, g.gcat, cat.password, cat.active FROM ibf_games_list AS g, ibf_games_cats AS cat WHERE g.active=1 AND cat.active=1 AND g.gcat=cat.c_id AND trim(password)='' ORDER BY RAND() LIMIT 1");
                $firstran = true;
                while($rangline = $DB->fetch_row())
		{
                        if($firstran) {
                                $firstran = false;
                        } else {
                                $ran_games .= "<br />\n";
                        }

			// Create Code based on setting for static/AJAX
			$this->arcade->settings['random_AJAX']=0;
			if ($this->arcade->settings['random_AJAX'] < 1)
			{
				// static display
		                $ran_games .= "<a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid={$rangline['gid']}'> <img src='arcade/images/{$rangline[gname]}1.gif' border='0' alt='' width='50' height='50' /><br /><br />{$rangline['gtitle']}</a>";
			}
			else
			{
				$ran_games .= '	<span id="random_game"> </span>
						<script type="text/javascript">
						var interval = '.$this->arcade->settings['random_AJAX'].'
						function build_XMLHTTP_random(Handler)
						{
						    url = "./randomgame.php"     
						    IE = (window.ActiveXObject)? true : false
						    if(IE)
						    {
						        XMLHTTPobj_random = new ActiveXObject("Microsoft.XMLHTTP")
						    } 
						    else 
						    {
						        XMLHTTPobj_random = new XMLHttpRequest()
						    }

						    if (XMLHTTPobj_random) 
						    {
						        XMLHTTPobj_random.onreadystatechange = function(){getRESPONSE_random(Handler)}
						        XMLHTTPobj_random.open("GET", url, true)
						        XMLHTTPobj_random.send("")
						    }
						}

						function getRESPONSE_random(Handler)
						{
						    if (XMLHTTPobj_random.readyState == 4)
						    {
						        if (XMLHTTPobj_random.status == 200) 
						        {
						            eval(Handler + "(XMLHTTPobj_random)")
						        }
						    }
						}

						function parse_Data(XMLHTTPobj_random)
						{
						    response = XMLHTTPobj_random.responseText
						    dump = document.getElementById("random_game")
						    dump.innerHTML = response
						}

						function looper()
						{
						    build_XMLHTTP_random("parse_Data")
						    setTimeout("looper()", interval)
						}

						looper()
						</script>';
			}
                }
                // End Random Game



        // Begin Total Stuff
                $tot_games                 = 0;
                        //Arrays
        $games2                                 = array();
                $gid_array2                         = array();
        $DB->query("SELECT g.gid, c.password from ibf_games_list AS g, ibf_games_cats AS c WHERE g.active=1 AND g.gcat=c.c_id AND trim(password)='' ORDER BY ".$this->arcade->settings['g_display_sort']." ".$this->arcade->settings['g_display_order']);
        while( $this_game = $DB->fetch_row() )
        {
                $games2[] = array(
                                                    'gid'                => $this_game2['gid'],
                                                                 );
            $gid_array2[] = $this_game2['gid'];
            $tot_games++;
        }

        if( count($gid_array2) > 0 )
        {
                $game_string2 = "(".implode("," , $gid_array2).")";
        }

	$tot_cats = 0;

    	$DB->query("SELECT * FROM ibf_games_cats AS c WHERE c.active=1 AND c.show_all=0");
	while ( $this_cat = $DB->fetch_row() )
	{
		$tot_cats++;
	}

        // End Total Stuff

        // die Top3 der Highscores

$query = "SELECT champ_gid AS GID, champ_mid AS MID, champ_name AS name, COUNT(champ_mid) AS total FROM ibf_games_champs GROUP BY MID ORDER BY total DESC LIMIT 3";

$result = $DB->query($query);

$Cpt = 1;

while ($data = $DB->fetch_row($result))
{

    $Highscorechamp['ArcadeChampion'.$Cpt] = '<span style="font-weight: bold;"><a href="'.$ibforums->base_url.'act=Arcade&amp;module=report&amp;user='.$data['MID'].'">'.$data['name'].'</a></span><br />'.$ibforums->lang[top3box_txt1].'<b>'.$data['total'].'</b>'.$ibforums->lang[top3box_txt2];
    $Highscorechamp['ArcadeChampionMid'.$Cpt] = $data['MID'];
    $Highscorechamp['ArcadeChampionScore'.$Cpt] = $data['total'];

    $DB->query("SELECT avatar,avatar_size AS size FROM ibf_members WHERE id=".$Highscorechamp['ArcadeChampionMid'.$Cpt]);
    if ($avatar = $DB->fetch_row())
    {
      $Highscorechamp['ArcadeChampionAvatarCode'.$Cpt] = $std->get_avatar($avatar , 1 , $avatar['size']);
	if ($Highscorechamp['ArcadeChampionAvatarCode'.$Cpt] == "")
	{
      		$Highscorechamp['ArcadeChampionAvatarCode'.$Cpt] = "<img src='./arcade/images/noavatar.gif' alt='' />";
	}
    }
    else
    {
      $Highscorechamp['ArcadeChampionAvatarCode'.$Cpt] = "<img src='./arcade/images/noavatar.gif' alt='' />";
    }

    $Cpt++;

}

  if (!$Highscorechamp['ArcadeChampion1'])  $Highscorechamp['ArcadeChampion1'] = "{$ibforums->lang['top3box_norank']}";
  if (!$Highscorechamp['ArcadeChampion2'])  $Highscorechamp['ArcadeChampion2'] = "{$ibforums->lang['top3box_norank']}";
  if (!$Highscorechamp['ArcadeChampion3'])  $Highscorechamp['ArcadeChampion3'] = "{$ibforums->lang['top3box_norank']}";

        // Smilies für Top3 definieren

        $Highscorechamp['ArcadeChampionSmily1'] = "<img src=\"./arcade/images/1st.gif\" border=\"0\" alt=\"{$ibforums->lang['top3info1']}\" />";
        $Highscorechamp['ArcadeChampionSmily2'] = "<img src=\"./arcade/images/2nd.gif\" border=\"0\" alt=\"{$ibforums->lang['top3info2']}\" />";
        $Highscorechamp['ArcadeChampionSmily3'] = "<img src=\"./arcade/images/3rd.gif\" border=\"0\" alt=\"{$ibforums->lang['top3info3']}\" />";

        if ($Highscorechamp['ArcadeChampionScore2'] == $Highscorechamp['ArcadeChampionScore1'])
                {
                if ($Highscorechamp['ArcadeChampionScore2'] > 0)
                        {
                        $Highscorechamp['ArcadeChampionSmily2'] = $Highscorechamp['ArcadeChampionSmily1'];
                        }
                }
        if ($Highscorechamp['ArcadeChampionScore3'] == $Highscorechamp['ArcadeChampionScore2'])
                {
                if ($Highscorechamp['ArcadeChampionScore3'] > 0)
                        {
                        $Highscorechamp['ArcadeChampionSmily3'] = $Highscorechamp['ArcadeChampionSmily2'];
                        }
                }

  // Champion mit den meisten besten Ergebnissen aller Zeiten
  $Champion['ArcadeChampion1'] = "{$ibforums->lang['top3box_norank']}";
  $Champion['ArcadeChampion2'] = "{$ibforums->lang['top3box_norank']}";
  $Champion['ArcadeChampion3'] = "{$ibforums->lang['top3box_norank']}";
  $Cpt = 0;

	$DB->query("SELECT g.gid, g.active, count(*) nb, g.bestscore, g.bestmid AS champ_mid, u.username AS champ_name FROM ibf_games_list AS g INNER JOIN ibf_user AS u ON u.userid=g.bestmid WHERE active=1 GROUP BY bestmid ORDER BY 3 DESC LIMIT 1");

	while ($res = $DB->fetch_row())
	{
  		$Cpt++;
  		$Champion['ArcadeChampion'.$Cpt] = '<span style="font-weight: bold;"><a href="'.$ibforums->base_url.'act=Arcade&amp;module=report&amp;user='.$res['champ_mid'].'">'.$res['champ_name'].'</a></span><br />'.$ibforums->lang[arcadeking_txt1].'<b>'.$res['nb'].'</b>'.$ibforums->lang[arcadeking_txt2];
   		$Champion['ArcadeChampionMid'.$Cpt] = $res['champ_mid'];
  	}

  for ($i = 1; $i <= $Cpt; $i++) {
          $DB->query("SELECT avatar,avatar_size AS size FROM ibf_members WHERE id=".$Champion['ArcadeChampionMid'.$i]);
          if ($avatar = $DB->fetch_row())
		{
            		$Champion['ArcadeChampionAvatarCode'.$i] = $std->get_avatar($avatar , 1 , $avatar['size']);
			if ($Champion['ArcadeChampionAvatarCode'.$i] == "")
			{
   				$Champion['ArcadeChampionAvatarCode'.$i] = "<img src='./arcade/images/noavatar.gif' alt='' />";
			}
		}
          else
		{
            		$Champion['ArcadeChampionAvatarCode'.$i] = "<img src='./arcade/images/noavatar.gif' alt='' />";
		}
  }

        $std->time_options['ARCADE'] = "{$ibforums->lang['timeformat4']}";

        $cattable = "";
        $show_all = "";
        if( isset($ibforums->input['cat']) )
        {
                        $cat = $ibforums->input['cat'];
                }
        else
        {
                        $cat = $this->arcade->settings['def_cat'];
                }

                if(!is_numeric($cat)) {
                  $cat = 1;
                 }

		$termine = 0;
		$actifs = 0;
		$attente = 0;

		$DB->query("SELECT demare, champion FROM ibf_tournaments");
		while($row = $DB->fetch_row()) {
			if($row['demare'] == 0)
			{
				$attente++;
			} else {
				if($row['champion'] == '')
				{
					$actifs++;
				} else {
					$termine++;
				}
			}
		}

		$termine = "<a href='".$ibforums->vars['base_url']."do=viewtourneyend'><b>$termine</b> ".$ibforums->lang['finished_tourneys']."</a>";
		$actifs = "<a href='".$ibforums->vars['base_url']."do=viewtournaments'><b>$actifs</b> ".$ibforums->lang['running_tourneys']."</a>";
		if($attente == 0) {
			$attente = $ibforums->lang['no_tourney_waiting'];
		} else {
			$attente = "<a href='".$ibforums->vars['base_url']."do=viewtournaments#attente'><b>$attente</b> ".$ibforums->lang['tourneys_waiting']."</a>";
		}

		$DB->query("SELECT tps.*, ta.tid FROM ibf_tournament_players_statut tps LEFT JOIN ibf_tournaments ta ON (tps.tid=ta.tid) WHERE tps.mid='".$ibforums->member['id']."' AND tps.statut<'3' AND ta.demare = '1'");
		$mtinfo['encourse'] = 0;
		$mtinfo['elimine'] = 0;
		$mtinfo['disqualifie'] = 0;
	if ($ibforums->member['id'] != 0) {
		while($res = $DB->fetch_row()) {
			if($res['statut'] == 0)
				$mtinfo['encourse']++;
			if($res['statut'] == 1)
				$mtinfo['elimine']++;
			if($res['statut'] == 2)
				$mtinfo['disqualifie']++;
			}
	}
		$mtinfo['participe'] = $mtinfo['encourse'] + $mtinfo['elimine'] + $mtinfo['disqualifie'];
		$mtinfo['participe'] = $ibforums->lang['active_in'].$mtinfo['participe'];
		$mtinfo['encourse'] = $ibforums->lang['still_qualified']." ".$mtinfo['encourse'];
		$mtinfo['elimine'] = $ibforums->lang['eliminated_in']." ".$mtinfo['elimine'];
		$mtinfo['disqualifie'] = $ibforums->lang['disqualified_in']." ".$mtinfo['disqualifie'];

        $cat_counter = 0;
        //Category System
        if( $this->arcade->settings['use_cats'] )
        {
                $show_all = " AND gcat=".$cat;
            $categories = "";

	if ($this->arcade->user['userid']==0)
	{
		// this is a guest!
		$DB->query("SELECT ibpa_cats FROM ibf_groups WHERE g_id = 1");	
		$guestperm = $DB->fetch_row();
		$this->arcade->user['allowed_categories'] = $guestperm['ibpa_cats'];
	}

	$restrictedcats=false;
	$allowedcats = explode(',', $this->arcade->user['allowed_categories']);
	if (!in_array(0,$allowedcats))
	{
		$restrictedcats=true;
	}


            $DB->query("SELECT cat_name, c_id, show_all, password, num_of_games, description FROM ibf_games_cats WHERE active=1 ORDER BY pos ASC");


	while( $the_cat = $DB->fetch_row() )
	{

		$displaycat=true;
		if ($restrictedcats)
		{
			if (!in_array($the_cat['c_id'],$allowedcats))
			{
				$displaycat=false;
			}
		}

		if ($displaycat)
		{
			if( $cat == $the_cat['c_id'] )
			{
				$total_num = $the_cat['num_of_games'];
	
				if( $the_cat['show_all'] == 1 )
				{
					$show_all = " AND trim(password)=''";
					$the_cat['num_of_games'] = $ibforums->lang['acp_all'];
				}
	
				if( $the_cat["password"] != "" )
				{
					$the_cookie = "cat_pass_".$the_cat['c_id'];
					$pass = ibp_cleansql($_COOKIE[$the_cookie]);
					if( $pass != $the_cat['password'] )
					{
						$this->output .= $this->html->cat_pass($the_cat['c_id']);
						return;
					}
				}
	
				$the_cat['cat_name'] = "<b>".$the_cat['cat_name']."</b>";

				if (strlen($the_cat['description']) > 2)
				$tourneyinfo['desc'] = $this->html->cat_desc($the_cat['description']);
				else
				$tourneyinfo['desc'] = "";
			}
				else
			{
				if( $the_cat['show_all'] == 1 )
				{
					$the_cat['num_of_games'] = $ibforums->lang['acp_all'];
				}
			}
	
			if (intval($this->arcade->settings['cats_per_tr']) > 0)
			{
		                if( (($cat_counter % $this->arcade->settings['cats_per_tr']) == 0) && ($this->arcade->settings['cats_per_tr'] != 0) && ($cat_counter != 0) )
		                {
					$categories .= "</tr><tr>";
					$cat_counter = 0;
		                }
			}

			$file = CAT_IMGS.$the_cat['c_id'].".gif";
	                if( file_exists($file) )
	                {
	                        $the_cat['cat_name'] = "<img src='".$file."' alt='' border='0' width='20' height='20' />&nbsp;".$the_cat['cat_name'];
	                }

	                $the_cat = "<a href=\"".$ibforums->base_url."act=Arcade&amp;cat=".$the_cat['c_id']."\">".$the_cat['cat_name']."</a> (".$the_cat['num_of_games'].")";
	                $categories .= $this->html->cat_cell($the_cat);
        	        $cat_counter++;
		}
	}

            if( ($this->arcade->settings['cats_per_tr'] != 0) && ($this->arcade->settings['cats_per_tr'] - $cat_counter > 0) )
            {
                    $left_over = $this->arcade->settings['cats_per_tr'] - $cat_counter;
                    for( $a = 1 ; $a <= $left_over ; $a++ )
                {
                        $categories .= $this->html->cat_cell("&nbsp;");
                }
            }
            $cattable = $this->html->the_cat_table($categories,$stylecolumns);

		$extquery = "";

                       $DB->query("SELECT mid, gid, sum(position) AS position, sum(points) AS points FROM ibf_games_league ".$extquery." GROUP BY mid ORDER BY points DESC LIMIT 1");
                $row = $DB->fetch_row();
                if ($row['mid'] > 0) {
                        $DB->query("SELECT name FROM ibf_members WHERE id=".$row['mid']);
                                $row = $DB->fetch_row();
                        $tourneyinfo['catchamp'] = $row['name'];
                } else {
                $tourneyinfo['catchamp'] = $ibforums->lang['nobody'];
                }
                if( $this->arcade->settings['skin'] != 0 )
                        $tourneyinfo['catchamp'] = "<a href=\"".$ibforums->base_url."act=Arcade&amp;module=league&amp;lcat=".$cat."\">".$tourneyinfo['catchamp']." ".$ibforums->lang['category_champion']."</a>";
                else
                        $tourneyinfo['catchamp'] = "<a href=\"".$ibforums->base_url."act=Arcade&amp;module=league&amp;lcat=".$cat."\">".$tourneyinfo['catchamp']."</a>";
        }
                //End category table building.

        $query_limit = "";
        if( $this->arcade->settings['games_pp'] && ($ibforums->input['gsearch']=="") )
        {
            $this->arcade->get_pages(0 , $this->arcade->settings['use_cats'] , $total_num);
            $start = 0;
            if( isset($ibforums->input['st']) )
            {
                    $start = intval($ibforums->input['st']);
            }
		if ($start < 0) { $start = 0; }
            $query_limit = "LIMIT ".$start.", ".$this->arcade->settings['games_pp'];
        }

        //tourney stuff
        $tourneyinfo['active'] = 0;
                $tourneyinfo['unactive'] = 0;
        $tourneyinfo['numenrolled'] = 0;
        $tourneyinfo['championships'] = 0;
        $tourneyinfo['playable'] = $ibforums->lang['no_active'];

        $DB->query("SELECT champion FROM ibf_tournaments");
                while( $this_tourney = $DB->fetch_row() )
        {
                if( $this_tourney['champion'] == $this->arcade->user['name'] )
            {
                    $tourneyinfo['championships']++;
            }
                        if( $this_tourney['champion'] == "")
            {
                                $tourneyinfo['active']++;
                        }
            else
            {
                                $tourneyinfo['unactive']++;
                        }
                }

        $DB->query("SELECT DISTINCT(p.tid) FROM ibf_tournaments as t, ibf_tournament_players as p WHERE t.tid=p.tid AND t.champion='' AND p.mid = '".$this->arcade->user['id']."' ORDER BY t.datestarted ASC");
                while( $this_tourney = $DB->fetch_row() )
        {
                        $tourneyinfo['numenrolled']++;
                        $tourneyinfo['playable'] = "<a href='".$ibforums->base_url."act=Arcade&amp;do=viewtourney&amp;tid=".$this_tourney['tid']."'>".$ibforums->lang['view_latest_active']."</a>";
                }

        //plays left today
        $plays_left = "";
        if( $this->arcade->user['max_play'] != 0 && $this->arcade->user['arcade_access'] == 2)
        {
                $plays_left = $ibforums->lang['plays'].($this->arcade->user['max_play']-$this->arcade->user['times_played']);
        }





        //latest score and champions
       //Added g.decpoints after g.title
                $DB->query("SELECT s.*, g.gtitle, g.decpoints, c.password FROM ibf_games_scores AS s, ibf_games_list AS g, ibf_games_cats AS c WHERE s.gid=g.gid AND g.gcat=c.c_id AND g.active=1 AND trim(password)='' ORDER BY datescored DESC LIMIT 0,5");
                $newest_score = $DB->fetch_row();

        //$newest_score['score'] = $this->arcade->t3h_format($newest_score['score']);
                // Replaced
                $newest_score['score'] = $this->arcade->do_arcade_format($newest_score['score'],$newest_score['decpoints']);

                if ($this->arcade->settings['use_announce'])
		{
			$announce = $this->arcade->settings['announcement_parsed'];
                	$tourneyinfo['announcement'] = $this->html->generalbox($ibforums->lang['arcade_announcements'], $announce);
		}
                else
                $tourneyinfo['announcement'] = "";


        $latestinfo = $ibforums->lang['newest_score'];
        $latestinfo = preg_replace("/<% NAME %>/i" , $newest_score['name'] , $latestinfo);
        $latestinfo = preg_replace("/<% SCORE %>/i" , $newest_score['score'] , $latestinfo);
        $latestinfo = preg_replace("/<% GAME %>/i" , $newest_score['gtitle'] , $latestinfo);


        $newtext = "";
        $DB->query("SELECT c.*, g.gcat, cat.password FROM ibf_games_champs AS c, ibf_games_list AS g, ibf_games_cats AS cat WHERE c.champ_gid=g.gid AND g.gcat=cat.c_id AND g.active=1 AND trim(password)='' ORDER BY champ_date DESC LIMIT 0,5");
        while( $row = $DB->fetch_row() )
        {
                        $row['champ_date'] = $std->get_date($row['champ_date'],'ARCADE');
                if( $row['champ_date'] == date("{$ibforums->lang['timeformat4']}") )
                {
                                $row['champ_date'] = $ibforums->lang['today'];
                        }
                else
                {
				if ($ibforums->lang[timeformat1] == "de")
				{
                                $yesterday = date("{$ibforums->lang['timeformat4']}", mktime(0, 0, 0, date("d")-1  , date("m"), date("Y")));
				}
				else
				{
                                $yesterday = date("{$ibforums->lang['timeformat4']}", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
				}

                                if( $row['champ_date'] == $yesterday )
                    {
                                        $row['champ_date'] = $ibforums->lang['yesterday'];
                                }
                        }

            $row['text'] = $ibforums->lang['new_champ'];
            $row['text'] = preg_replace("/<% USERNAME %>/i", $row['champ_name'] , $row['text'] );
            $row['text'] = preg_replace("/<% GAMENAME %>/i", $row['champ_gtitle'] , $row['text'] );

            $newtext .= $this->html->newest_champs_row($row);
        }

        $DB->query("SELECT mid, gid, sum(position) AS position, sum(points) AS points FROM ibf_games_league GROUP BY mid ORDER BY points DESC LIMIT 1");
        $row = $DB->fetch_row();
        $points=$row['points'];
        $name = "<span style=\"font-weight: bold;\"><a href=\"";
        $name = $name . $ibforums->base_url."act=Arcade&amp;module=report&amp;user=".$row['mid']."\">";

        if ($row['mid'] > 0)
        {
        $Touruserid = $row['mid'];
        $DB->query("SELECT name FROM ibf_members WHERE id=".$Touruserid);
        $row = $DB->fetch_row();
        $name = $name . $row['name'].'</a></span>';
        $tourneyinfo['champ'] = "<b>".$name."</b><br />{$ibforums->lang['tourneyinfo_txt1']}<b>".$points."</b>{$ibforums->lang['tourneyinfo_txt2']}";

        $tourneyinfo['champavatar'] = "";

        $DB->query("SELECT avatar,avatar_size AS size FROM ibf_members WHERE id=".$Touruserid);

        if ($avatar = $DB->fetch_row())
                {
	                $tourneyinfo['champavatar'] = $std->get_avatar($avatar , 1 , $avatar['size']);
			if ($tourneyinfo['champavatar'] == "")
			{
		                $tourneyinfo['champavatar'] = "<img src='./arcade/images/noavatar.gif' alt='' />";
			}
                }
        else
                {
	                $tourneyinfo['champavatar'] = "<img src='./arcade/images/noavatar.gif' alt='' />";
                }

        }
        else
        {
                $tourneyinfo['champ'] = "{$ibforums->lang['top3box_norank']}";
                $tourneyinfo['champavatar'] = "";
        }

	$usecats = $this->arcade->settings['use_cats'];
	$defcat = $this->arcade->settings['def_cat'];

	// alpha-navbar by MrZeropage
	$alphabet = array(	"ALL", "0-9" , "A" , "B" , "C" , "D" , "E" , "F" , "G" , "H" ,
                           	"I" , "J" , "K" , "L" , "M" , "N" , "O" , "P" , "Q" ,
                           	"R" , "S" , "T" , "U" , "V" , "W" , "X" , "Y" , "Z" );
	$alphabar = ""; $currentfilter="ALL";
	if ($ibforums->input['gsearch'] != "")
	{
		$currentfilter = $ibforums->input['gsearch'];
	}

	foreach ($alphabet as $letter)
	{
		$style="alt1"; $boldon=""; $boldoff=""; $width="18";

		$urlstring="act=Arcade&amp;gsearch=".$letter."&amp;search_type=";
		if ($letter == "0-9")
		{
			$urlstring.="3";
			$width="24";
		}
		else
		{
			$urlstring.="1";
		}

		if ($letter == $currentfilter)
		{
			$style="alt2";
			$boldon="<b>";
			$boldoff="</b>";
		}

		if ($letter == "ALL")
		{
			$urlstring="";
			$letter=$ibforums->lang['acp_all'];
			$width="26";
		}

		if (isset($ibforums->input['cat']))
		{
			$urlstring .= "&amp;cat=".intval($ibforums->input['cat']);
		}

		$alphabar .= '<td class="'.$style.'" width="'.$width.'" height="20"><div align="center"><a class="smallfont" href="'.$ibforums->base_url.$urlstring.'">'.$boldon.$letter.$boldoff.'</a></div></td>';
	}

	$this->arcade->links['alphabar'] = $alphabar;
	// end alpha-navbar

	$selected['sort']['gtitle']="";
	$selected['sort']['gcount']="";
	$selected['sort']['gwords']="";
	$selected['sort']['g_rating']="";
	$selected['sort']['added']="";
	$selected['order']['ASC']="";
	$selected['order']['DESC']="";

	// make sure the setting is clean and has a valid value
	if (!in_array($this->arcade->settings['g_display_sort'],array('gtitle','gcount','gwords','g_rating','added')))
	{
		$this->arcade->settings['g_display_sort'] = "gtitle";
	}

	if (!in_array($this->arcade->settings['g_display_order'],array('ASC','DESC')))
	{
		$this->arcade->settings['g_display_order'] = "ASC";
	}

	$selected['sort'][$this->arcade->settings['g_display_sort']] = 'selected="selected" ';
	$selected['order'][$this->arcade->settings['g_display_order']] = 'selected="selected" ';

        //header
        $this->arcade->make_links($newest_score['gid'] , $newest_score['gtitle']);
        $viewerav = "";

	if ($this->arcade->settings['arcade_status']==0)
	{
		$this->output .= "<span style='color: red;'><div align='center'><b>".$ibforums->lang['arcade_offline']."</b></div></span>";
	}

        $this->output .= $this->html->start($newtext,$latestinfo,$new_games,$pop_games,$ran_games,$tot_games,$usecats,$tot_cats,$this->arcade->links,$plays_left,$tourneyinfo,$cattable,$attente,$termine,$actifs,$mtinfo,$Champion,$Highscorechamp,$stylecolumns,$selected);

		$this->output .= "<tr>";	// start of gametable

        //show new?
        if( $this->arcade->settings['show_new'] )
        {
                $time = time()-($this->arcade->settings['show_new']*$this->arcade->settings['show_new_frame']);
                }

                // Search Mod
       $search = ''; $show_all="";
        if( $ibforums->input['gsearch'] != "" )
        {
                $show_all = " and trim(password)=''";

            switch( intval($ibforums->input['search_type']) )
            {
                    case 0:
                        $search = " and gtitle like '%".$ibforums->input['gsearch']."%'";
                break;
                case 1:
                     $search = " and gtitle like '".$ibforums->input['gsearch']."%'";
                break;
                case 2:
                     $search = " and gtitle like '%".$ibforums->input['gsearch']."'";
                break;
			case 3:
				$search = " and gtitle REGEXP '^[0-9]'";
				break;
            }
        }
                // End Search Mod

	$catselect="";

	if ($this->arcade->settings['use_cats'])
	{
		if (isset($ibforums->input['cat']))
		{
			$querycat = intval($ibforums->input['cat']);
		}
		else
		{
			$querycat = $this->arcade->settings['def_cat'];
		}

		// category selected/setup, but make sure it is active and not showing all games anyway
		$catquery = $DB->query("SELECT show_all, active FROM ibf_games_cats WHERE c_id=".$querycat);
		$catdata = $DB->fetch_row($catquery);
		if (($catdata[show_all]==0) && ($catdata[active]==1))
		{
			if ($search=="")
			{
				// no searching here
				$catselect = " and gcat=".$querycat;
			}
			else
			{
				// ok, search found, now check if we can access ALL categories
				if (substr($this->arcade->user['allowed_categories'],0,1)!="0")
				{
					$mycats = $this->arcade->user['allowed_categories'];
					if (substr($mycats,-1)==",")
					{
						$mycats = substr($mycats,0,(strlen($mycats)-1));
					}
					$catselect = " and gcat IN (".$mycats.")";
				}
			}
		}
	}

                $game_counter = 1;

        //get games
               $DB->query("select g.*, c.password, c.cat_name from ibf_games_list as g
            left join ibf_games_cats as c on (g.gcat = c.c_id)
                  where g.active = 1 ".$show_all.$search.$catselect."
                    order by ".$this->arcade->settings['g_display_sort']." ".$this->arcade->settings['g_display_order']." ".$query_limit);
                while( $this_game = $DB->fetch_row() )
        {
                $games[] = array(	'gid'                =>  $this_game['gid'],
					'gwidth'    =>  $this_game['gwidth'],
					'gheight'   =>  $this_game['gheight'],
					'gkeys'   =>  $this_game['gkeys'],
					'gname'                =>        $this_game['gname'],
					'gtitle'        =>        $this_game['gtitle'],
					'gcount'        =>        $this_game['gcount'],
					'gwords'        =>        $this_game['gwords'],
					'added'                =>        $this_game['added'],
					'g_rating'        =>        $this_game['g_rating'],
					'gtime'     =>  $this_game['gtime'],
					'g_raters'        =>        $this_game['g_raters'],
					'decpoints' =>  $this_game['decpoints'],
					'filesize' => $this_game['filesize'],
					'cat_name' => $this_game['cat_name'],
					'cost' => $this_game['cost'],
					'jackpot' => $this_game['jackpot'],
					'jackpot_type' => $this_game['jackpot_type'],
					'highscore_type' => $this_game['highscore_type'],
					'bestscore' => $this_game['bestscore'],
					'bestmid' => $this_game['bestmid']
                        		);

            $game_gids[] = $this_game['gid'];
        }

	$game_string="(0)";
        if( count($game_gids) > 0 )
        {
                $game_string = "(".implode("," , $game_gids).")";
        }

	// get the best result of all time for each game
        $highscores = array();

	$scores_query = $DB->query("SELECT g.bestmid, g.bestscore AS champ_score, g.gid AS champ_gid, g.decpoints, u.username AS champ_name FROM ibf_games_list AS g INNER JOIN ibf_user AS u ON u.userid=g.bestmid WHERE g.gid IN ".$game_string." ORDER BY g.gid");

        while( $row = $DB->fetch_row($scores_query) )
	{
		$row['champ_score'] = $this->arcade->do_arcade_format($row['champ_score'],$row['decpoints']);
		$highscores[ $row['champ_gid'] ] = array(  'name'        => $row['champ_name'], 'score' => $row['champ_score'] );
	}

        $personal_highs = array();
        $scores_query = $DB->query("SELECT mid, gid, score AS the_score, MAX(score) FROM ibf_games_scores WHERE gid IN ".$game_string." AND mid=".$this->arcade->user['id']." GROUP BY score");
        while( $row = $DB->fetch_row($scores_query) )
        {
                $personal_highs[ $row['gid'] ] = $row['the_score'];
        }

        foreach( $games as $the_game )
        {
                $gamesplit = "";

                        //row color
            if( $this->arcade->settings['skin'] != 0 )
            {
                $rowcol = ($rowcol == "alt1") ? "alt2" : "alt1";
            }

        //game top score
        $top = $highscores[ $the_game['gid'] ];

        // crowns only if name not empty (by MrZeropage)
        if ($top['score'] <> 0)
        {
        $top['name'] = "<img src=\"./arcade/images/crown.gif\" alt=\"\" /> <b> " . $top['name'] . " </b> <img src=\"./arcade/images/crown.gif\" alt=\"\" />";
        $top['score'] = "{$ibforums->lang['tourneyinfo_txt1']}<b>" . $top['score'] . "</b>{$ibforums->lang['tourneyinfo_txt2']}";
        }
        else
        {
        $top['name'] = "<i>{$ibforums->lang['noscorestored']}</i>";
        $top['score'] = "&nbsp;";
        }

        //game actual highscore
        $ordering = ($the_game['highscore_type'] == "high") ? "DESC" : "ASC";

        $this_query = $DB->query("SELECT * FROM ibf_games_scores WHERE gid=".$the_game['gid']." ORDER BY score ".$ordering.", timespent ASC");
        $actualhighscore = $DB->fetch_row($this_query);
        $actualtop['score'] = $this->arcade->do_arcade_format($actualhighscore['score'],$the_game['decpoints']);

        if ($actualtop['score'] != "0")
        {
        $actualtop['name'] = "<img src=\"./arcade/images/trophy.gif\" alt=\"\" /> <b> " .$actualhighscore['name']. " </b> <img src=\"./arcade/images/trophy.gif\" alt=\"\" />";
        $actualtop['score'] = "{$ibforums->lang['tourneyinfo_txt1']}<b>" . $actualtop['score'] . "</b>{$ibforums->lang['tourneyinfo_txt2']}";
        }
        else
        {
        $actualtop['name'] = "<i>{$ibforums->lang['noscorestored']}</i>";
        $actualtop['score'] = "&nbsp;";
        }

            //create play links
            $this->arcade->make_links($the_game['gid'] , $the_game['gtitle']);

            //is game new?
        $newgame = "";
            if( $the_game['added'] > $time && $this->arcade->settings['show_new'] )
            {
                    $this->arcade->links['click'] .= "&nbsp;<img src='./arcade/images/new.gif' title='New' alt='{$ibforums->lang['newgame_star']}' />";
        $newgame = "<img src='./arcade/images/new.gif' title='New' alt='{$ibforums->lang['newgame_star']}' />";
            }

            //fav games
            $temp = unserialize($this->arcade->user['favs']);

                if( !is_array($temp) )
                {
                        $temp = array();
                }

                $favs = $temp;
                        $the_game['gtitle2'] = $the_game['gtitle'];
            $favtitle = $ibforums->lang['add_to_faves'];
            $favtitle = preg_replace("/<% GAMENAME %>/i" , $the_game['gtitle'] , $favtitle);
            $star = "";

            if( in_array($the_game['gid'] , $favs) )
            {
                    $star = "<img src='./arcade/images/favs.gif' title='".$ibforums->lang['favorite']."' alt='".$ibforums->lang['favorite']."' />&nbsp;";
                $favtitle = $ibforums->lang['remove_from_faves'];
                $favtitle = preg_replace("/<% GAMENAME %>/i" , $the_game['gtitle'] , $favtitle);
            }

            if( $this->arcade->user['arcade_access'] == 2 && $this->arcade->user['id'] )
            {
                    $the_game['gtitle'] = $star."<a href='".$ibforums->base_url."act=Arcade&amp;module=favorites&amp;gameid=".$the_game['gid']."' title='".$favtitle."'>$favtitle</a>";
            }

            //personal best
            $pbesttext = "";
                        if( $personal_highs[ $the_game['gid'] ] == "" )
            {
                                $pbesttext = $ibforums->lang['n_a'];
                        }
            else
            {
                                //$pbesttext = $this->arcade->t3h_format($personal_highs[ $the_game['gid'] ]);
                                //Replaced
                                $pbesttext = $this->arcade->do_arcade_format($personal_highs[ $the_game['gid'] ],$the_game['decpoints']);
                        }

            //rating
            $rating = "";
            $rating = $ibforums->lang['rating'];
            $raters = unserialize($the_game['g_raters']);
            if( empty($the_game['g_raters']) )
            {
                    $rating .= $ibforums->lang['no_votes'];
            }
            else
            {
                    $amount = count($raters).$ibforums->lang['rates'];
                    for( $a = 1 ; $a <= $the_game['g_rating'] ; $a++ )
                {
                        $rating .= "<img src='./arcade/images/star1.gif' title='".$amount."' alt='".$amount."' />";
                }
                $leftover = (5-$the_game['g_rating']);
                for( $a = 1 ; $a <= $leftover ; $a++ )
                {
                        $rating .= "<img src='./arcade/images/star2.gif' title='".$amount."' alt='".$amount."' />";
                }
            }

            if( $this->arcade->user['id'] != 0 && !isset($raters[$this->arcade->user['id']]) )
            {
		//no rating here, play game to rate!
                    //$rating .= $this->html->rate_link($the_game['gid']);
            }

                        // File Size Mod

            if( $the_game['filesize'] > 0)
            {
                    $the_game['filesize'] = "\n\n".$ibforums->lang['file_size'].": ".$std->size_format($the_game['filesize']);
            }
            else
            {
                    $the_game['filesize'] = "";
            }
                        // End File Size Mod

                        if ($game_counter == $stylecolumns)
                        {

             $gamesplit = "</tr><tr>";
             $game_counter = 0;
                        }

            //getting the row
                        $the_game['gtime'] = $this->arcade->thatdate($the_game['gtime']);

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_gamebit')) ? eval($hook) : false;
		}

		$the_game['jackpot_info'] = "";
		$the_game['cost_info'] = "";
		$the_game['jackpot_info'] = "";
		$the_game['ibprostyle_info1'] = "";
		$the_game['ibprostyle_info2'] = "";
		$the_game['v3style_info'] = "<div class='smallfont'>".$this->arcade->links['click']."</div>";

		// detect vBplaza
		if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
		{
			$the_game['ibprostyle_info1']="	<fieldset>
			<legend><b>{$ibforums->lang['gb_jackpot_title']}</b></legend>
			<span class=\"smallfont\"><center>";
			$the_game['ibprostyle_info2']="	</center></span></fieldset>";
			$the_game['v3style_info']="<br />";

			$the_game['cost_info'] = $ibforums->lang['costs_pgame']."<b>".$the_game['cost']."</b><br />";

			if ($the_game['jackpot_type']=='-1')
			{
				// raising Jackpot, so display it in GameBit!
				$the_game['jackpot_info'] = $ibforums->lang['gb_win_jackpot_raising']."<b>".$the_game['jackpot']."</b>";
			}
			else
			{
				// static Jackpot
				$the_game['jackpot_info'] = $ibforums->lang['gb_win_jackpot_static']."<b>".$the_game['jackpot']."</b>";
			}
		}

                $this->output .= $this->html->row($the_game,$gamesplit,$top,$pbesttext,$this->arcade->links,$rowcol,$rating,$actualtop,$newgame,$stylecolumns);
                        $game_counter++;
        }

                 $this->output .= $this->html->stop($this->arcade->links['pages']);
                 $this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title'];
                 $this->nav        = array( $ibforums->lang['page_title'] );
        }

        //------------------------------------------
        // Play_Game
        //
        // This brings up the game to play
        //
        //------------------------------------------

         function play_game() {
                 global $ibforums, $DB, $std, $vboptions, $vbulletin, $DEBUGMODE, $NATIVEMODE;

        	if( ($GROUP['max_play']-$ibforums->member['times_played']) <= 0 && $GROUP['max_play'] != 0 )
        	{
                	$std->Error( array( LEVEL => 1, MSG => 'max') );
        	}

                $id = intval($ibforums->input['gameid']);
		if ($id < 1) { $id=1; }

                setcookie('gidstarted', '', time() - 3600);
                setcookie('gidstarted',$id);
                setcookie('tidstarted', '', time() - 3600);
                setcookie('gpstarted', time());

                 if (! preg_match( "/^(\d+)$/" , $id ) ) {
                         $std->Error( array( LEVEL => 1, MSG => 'no_help_file') );
                 }

                 $DB->query("SELECT * FROM ibf_games_list WHERE gid=".$id);
                 $game = $DB->fetch_row();
		 $game['hash'] = $hash;

		$randomchar = "1";
		$randomchar2 = "1";
		// only run this if the game is using the new code
		if ( ( file_exists(ROOT_PATH.'arcade/gamedata/'.$game['gname'].'/v32game.txt' ) ) || ( file_exists(ROOT_PATH.'arcade/gamedata/'.$game['gname'].'/v3game.txt' ) ) )
		{
			$randomchar = rand(1, 10);
			$randomchar2 = rand(1, 5);
			$game['gidencoded'] = $game['gid'] * $randomchar ^ $randomchar2;
		}

		if ($this->arcade->user['arcade_access'] > 2)
		{
			// this is a guest

			// create a hash for that guest playing
			srand ((double)microtime()*1000000);
			$guesthash = rand(100000,999999);
	                setcookie('guesthash', $guesthash);

			if ($_COOKIE['ibPAcookiecheck'] != "yesss")
			{
				$server = strtolower($_SERVER['HTTP_REFERER']);
				if (strpos($server,"arcade.php") > 0)
				{
					$VERBOSE = ($DEBUGMODE == 2) ? " cookie #001 -> ghash=".$guesthash." | ibPAcheck=".$_COOKIE['ibPAcookiecheck'] : "";
					$std->Error( array( LEVEL => 1, MSG => 'cat_pass_notice', EXTRA => $VERBOSE) );
				}
				else
				{
					// game called directly (without arcade.php), so set cookie NOW for later verification
					setcookie('ibPAcookiecheck', "yesss");
				}
			}

			// now create session-entry...
			$DB->query("INSERT INTO ibf_games_session (gname, gameid, gtitle, mname, mid, randgid, randgid2) VALUES ('".addslashes($game['gname'])."', '".$game['gid']."', '".addslashes($game['gtitle'])."', '".$guesthash."', '0', '".$randomchar."', '".$randomchar2."')");
			$sessionid = $DB->get_insert_id();
	                setcookie('guestsession', $sessionid);

		}
		else
		{
			// prune any old session from that user
			$DB->query("DELETE FROM ibf_games_session WHERE mid=".$this->arcade->user['id']);

			// now create session-entry...
			$DB->query("INSERT INTO ibf_games_session (gname, gameid, gtitle, mname, mid, randgid, randgid2) VALUES ('".addslashes($game['gname'])."', '".$game['gid']."', '".addslashes($game['gtitle'])."', '".addslashes($this->arcade->user['name'])."', '".$this->arcade->user['id']."', '".$randomchar."', '".$randomchar2."')");
			$sessionid = $DB->get_insert_id();

			// ...and store session-info in userprofile
	       		$DB->query("UPDATE ibf_members SET times_played=times_played+1, arcade_sess_gid='".$game['gid']."', arcade_sess_start='".time()."', arcade_gtype=0, arcade_session='".$sessionid."' WHERE id=".$this->arcade->user['id']);
		}

                $DB->query("SELECT champ_name AS name,champ_score AS score FROM ibf_games_champs WHERE champ_gid=".$id);
                $top = $DB->fetch_row();
                $DB->query("UPDATE ibf_games_list SET gcount=gcount+1 WHERE gid=".$id);

                $top['score'] = $this->arcade->do_arcade_format($top['score'],$game['decpoints']);

		if ($top['name'] == "") { $top['name'] = $ibforums->lang['top3box_norank']; }
	
                  /***********************************************************************
 pnFlashGames Modification:
 Add in extra information to the $game array so that it can be displayed properly
 and so that the pnFlashGames component can get the information it needs
 */
 $game['username'] = $player_name = $this->arcade->user['name'];
 $game['checksum'] = $this->pnFlashGames_getChecksum($game['gname']);
 $game['domain'] = $this->pnFlashGames_getDomain(); //couldnt get this to work for some reason
 /**********************************************************************/
        $extra_html = "";
        if( trim($game['object']) != "" )
        {
                $extra_html .= $this->html->objective($game);
        }
        if( trim($game['gkeys']) != "" )
        {
                $extra_html .= $this->html->keys($game);
        }
           //fav games
            $temp = unserialize($this->arcade->user['favs']);

                if( !is_array($temp) )
                {
                        $temp = array();
                }

                $favs = $temp;

            $favtitle = $ibforums->lang['add_to_faves'];
            $favtitle = preg_replace("/<% GAMENAME %>/i" , $game['gtitle'] , $favtitle);
            $star = "";

            if( in_array($game['gid'] , $favs) )
            {
                    $star = "<img src='./arcade/images/favs.gif' title='".$ibforums->lang['favorite']."' alt='".$ibforums->lang['favorite']."' />&nbsp;";
                $favtitle = $ibforums->lang['remove_from_faves'];
                $favtitle = preg_replace("/<% GAMENAME %>/i" , $game['gtitle'] , $favtitle);
            }

            if( $this->arcade->user['arcade_access'] == 2 && $this->arcade->user['id'] )
            {
                    $game['fave'] = $star."<a href='".$ibforums->base_url."act=Arcade&amp;module=favorites&amp;gameid=".$game['gid']."' title='".$favtitle."'>$favtitle</a>";
            }

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_play_game')) ? eval($hook) : false;
		}


                 $this->output .= $this->html->game($game,$top,$extra_html);

                 $this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$game['gtitle'];
                 $this->nav        = array( "<a href=\"".$ibforums->base_url."act=Arcade&amp;cat=".$game['gcat']."\">{$ibforums->lang['page_title']}</a>", $game['gtitle'] );

         }

        //------------------------------------------
        // Play_Game In Popup Window v1.0
        //
        // This will allow users to play game in new window
        //
        //------------------------------------------

         function playfull() {
                 global $ibforums, $DB, $std, $vboptions, $vbulletin, $DEBUGMODE, $NATIVEMODE;

        if( ($GROUP['max_play']-$ibforums->member['times_played']) <= 0 && $GROUP['max_play'] != 0 )
        {
                $std->Error( array( LEVEL => 1, MSG => 'max') );
        }

                 $id = intval($ibforums->input['gameid']);
		if ($id < 1) { $id=1; }

                setcookie('gidstarted', '', time() - 3600);
                setcookie('gidstarted',$id);
                setcookie('tidstarted', '', time() - 3600);
                setcookie('gpstarted', time());

                 $DB->query("SELECT * FROM ibf_games_list WHERE gid=".$id);
                 $game = $DB->fetch_row();
		 $game['hash'] = $hash;

		$randomchar = "1";
		$randomchar2 = "1";
		// only run this if the game is using the new code
		if ( ( file_exists(ROOT_PATH.'arcade/gamedata/'.$game['gname'].'/v32game.txt' ) ) || ( file_exists(ROOT_PATH.'arcade/gamedata/'.$game['gname'].'/v3game.txt' ) ) )
		{
			$randomchar = rand(1, 10);
			$randomchar2 = rand(1, 5);
			$game['gidencoded'] = $game['gid'] * $randomchar ^ $randomchar2;
		}

		if ($this->arcade->user['arcade_access'] > 2)
		{
			// this is a guest

			// create a hash for that guest playing
			srand ((double)microtime()*1000000);
			$guesthash = rand(10,99);
	                setcookie('guesthash', $guesthash);

			// check if cookies are working
			if ($_COOKIE['ibPAcookiecheck'] != "yesss")
			{
				$VERBOSE = ($DEBUGMODE == 2) ? " cookie #002 -> ghash=".$guesthash." | ibPAcheck=".$_COOKIE['ibPAcookiecheck'] : "";
				$std->Error( array( LEVEL => 1, MSG => 'cat_pass_notice', EXTRA => $VERBOSE) );
			}

			// now create session-entry...
			$DB->query("INSERT INTO ibf_games_session (gname, gameid, gtitle, mname, mid, randgid, randgid2) VALUES ('".addslashes($game['gname'])."', '".$game['gid']."', '".addslashes($game['gtitle'])."', '".$guesthash."', '0', '".$randomchar."', '".$randomchar2."')");
			$sessionid = $DB->get_insert_id();
	                setcookie('guestsession', $sessionid);

		}
		else
		{
			// prune any old session from that user
			$DB->query("DELETE FROM ibf_games_session WHERE mid=".$this->arcade->user['id']);

			// now create session-entry...
			$DB->query("INSERT INTO ibf_games_session (gname, gameid, gtitle, mname, mid, randgid, randgid2) VALUES ('".addslashes($game['gname'])."', '".$game['gid']."', '".addslashes($game['gtitle'])."', '".addslashes($this->arcade->user['name'])."', '".$this->arcade->user['id']."', '".$randomchar."', '".$randomchar2."')");
			$sessionid = $DB->get_insert_id();

			// ...and store session-info in userprofile
	       		$DB->query("UPDATE ibf_members SET times_played=times_played+1, arcade_sess_gid='".$game['gid']."', arcade_sess_start='".time()."', arcade_gtype=0, arcade_session='".$sessionid."' WHERE id=".$this->arcade->user['id']);
		}

                 if (! preg_match( "/^(\d+)$/" , $id ) ) {
                         $std->Error( array( LEVEL => 1, MSG => 'no_help_file') );
                 }

                $DB->query("SELECT champ_name AS name,champ_score AS score FROM ibf_games_champs WHERE champ_gid=".$id);
                $top = $DB->fetch_row();

                $DB->query("UPDATE ibf_games_list SET gcount=gcount+1 WHERE gid=".$id);

                  /***********************************************************************
 pnFlashGames Modification:
 Add in extra information to the $game array so that it can be displayed properly
 and so that the pnFlashGames component can get the information it needs
 */
 $game['username'] = $player_name = $this->arcade->user['name'];
 $game['checksum'] = $this->pnFlashGames_getChecksum($game['gname']);
 $game['domain'] = $this->pnFlashGames_getDomain(); //couldnt get this to work for some reason
 /**********************************************************************/

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_play_game_popup')) ? eval($hook) : false;
		}

echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{$ibforums->lang['popuptitle']}{$game['gname']}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css" media="all"> 
<!-- 
body { 
   margin-left: 0px; 
   margin-top: 0px; 
   margin-right: 0px; 
   margin-bottom: 0px; 
} 
object, embed {
	width: 100%;
	height: 100%;
}
--> 
</style>
</head>
<body>
<iframe src="holdsession.php?act=arcade&do=play&gameid=$game[gid]" width='0' height='0' frameborder='0'></iframe>
<BODY BGCOLOR="#000000" oncontextmenu="javascript:return false" margin: 0; padding: 0;>
  				<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" id="playnewin" name="ibproarcade" width="100%" height="100%">
				<param name="menu" value="false" />
  				<param name="movie" value="./arcade/{$game['gname']}.swf?tgame=0&pn_gid={$game['gid']}&pn_license={$game['license']}&pn_checksum={$game['checksum']}&pn_domain={$game['domain']}&pn_uname={$game['username']}" />
				<param name="type" value="application/x-shockwave-flash" />
				<param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer/" />
				<param name="bgcolor" value="#{$game['bgcolor']}" />
				<param name="quality" value="high" />
				<param name="menu" value="false" />
				<param name="width" value="{$game['gwidth']}" />
				<param name="height" value="{$game['gheight']}" />
				<param name="flashvars" value="location=./&amp;gamename={$game['gname']}&hash={$game['hash']}" />

				<embed src="./arcade/{$game['gname']}.swf?tgame=0&pn_gid={$game['gid']}&pn_license={$game['license']}&pn_checksum={$game['checksum']}&pn_domain={$game['domain']}&pn_uname={$game['username']}" width="{$game['gwidth']}" height="{$game['gheight']}" bgcolor="#{$game['bgcolor']}" quality="high" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer/" flashvars="location=./&amp;gamename={$game['gname']}" menu="false"></embed>
				<noembed>{$ibforums->lang['no_embed']}{$game['gtitle']}</noembed>
				</object>
</body></html>
EOF;
exit();

}
        //------------------------------------------
        // Play_Tourney
        //
        // This brings up the game to play
        //
        //------------------------------------------

         function play_tourney() {
                 global $ibforums, $DB, $std, $vboptions, $vbulletin, $guestplayerid, $NATIVEMODE;

                $id = intval($ibforums->input['gameid']);
                $tid = intval($ibforums->input['tid']);
                $rung = intval($ibforums->input['rung']);
		$faceoff = intval($ibforums->input['faceoff']);
                setcookie('gidstarted', '', time() - 3600);
                setcookie('gidstarted',$id);
                setcookie('tidstarted', '', time() - 3600);
                setcookie('tidstarted', $tid);

		if ($guestplayerid == $this->arcade->user['id'])
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_tourney_user') );
		}

		$DB->query("SELECT nbtries FROM ibf_tournaments WHERE tid=".$tid);
		$readrow = $DB->fetch_row();
		$nbtries = $readrow['nbtries'];

                $DB->query("SELECT * FROM ibf_tournament_players WHERE mid=".$this->arcade->user['id']." AND tid=".$tid." AND timesplayed<".$nbtries." AND rung=".$rung);
                $ctr=0;
                while($row = $DB->fetch_row()) {
                        $ctr++;
                }

                if($ctr==0) {
                        $std->Error( array( LEVEL => 1, MSG => 'passed_times_played') );
                }

                 if (! preg_match( "/^(\d+)$/" , $id ) ) {
                         $std->Error( array( LEVEL => 1, MSG => 'no_help_file') );
                 }
                 $DB->query("SELECT * FROM ibf_games_list WHERE gid=".$id);
                 $game = $DB->fetch_row();

		$randomchar = "1";
		$randomchar2 = "1";

		// prune any old session from that user
		$DB->query("DELETE FROM ibf_games_session WHERE mid=".$this->arcade->user['id']);

		// now create session-entry...
		$DB->query("INSERT INTO ibf_games_session (gname, gameid, gtitle, mname, mid) VALUES ('".addslashes($game['gname'])."', '".$game['gid']."', '".addslashes($game['gtitle'])."', '".addslashes($this->arcade->user['name'])."', '".$this->arcade->user['id']."')");
		$sessionid = $DB->get_insert_id();

		// ...and store session-info in userprofile
       		$DB->query("UPDATE ibf_members SET times_played=times_played+1, arcade_sess_gid='".$game['gid']."', arcade_sess_start='".time()."', arcade_gtype=1, arcade_session='".$sessionid."' WHERE id=".$this->arcade->user['id']);

                $DB->query("SELECT m.name, t.rungscore, t.timesplayed FROM ibf_members as m, ibf_tournament_players as t WHERE m.id = t.mid AND t.tid=".$tid." AND t.rung=".$rung." AND t.faceoff=".$faceoff." AND t.mid<>".$this->arcade->user['id']." LIMIT  0, 1");
                $top = $DB->fetch_row();
                $DB->query("UPDATE ibf_games_list SET gcount=gcount+1 WHERE gid=".$id);
                $DB->query("UPDATE ibf_tournament_players SET timesplayed=timesplayed+1, notified=0 WHERE tid=".$tid." AND mid=".$this->arcade->user['id']." AND rung=".$rung);

		/***********************************************************************
		pnFlashGames Modification:
		Add in extra information to the $game array so that it can be displayed properly
		and so that the pnFlashGames component can get the information it needs
		*/
		$game['username'] = $player_name = $this->arcade->user['name'];
		$game['checksum'] = $this->pnFlashGames_getChecksum($game['gname']);
		$game['domain'] = $this->pnFlashGames_getDomain();
		/**********************************************************************/

		$top['nbtries'] = $nbtries;
                $top['rungscore'] = $this->arcade->do_arcade_format($top['rungscore'],$game['decpoints']);

		$top['playertext'] = $ibforums->lang['tourney_opponent'];
		$top['name'] .= "<br /><span class='smallfont'><i>(".$ibforums->lang['tourney_playinfo1']." ".$top['timesplayed']."/".$top['nbtries']." ".$ibforums->lang['tourney_playinfo2'].")</i></span>";

		$top['scoretext'] = $ibforums->lang['tourney_scoretext1'];
		if ($top['timesplayed']==0)
		{
			$top['scoretext'] = $ibforums->lang['tourney_scoretext2'];
			$top['rungscore'] = "";
		}
		if ($top['timesplayed'] == $top['nbtries'])
		{
			$top['scoretext'] = $ibforums->lang['tourney_scoretext3'];
		}

		$game['ginfotxt'] = "	<tr>
                    			<td class='alt1' align='center'>
					<div><font face='arial' size='3' color='red'><strong>*** ".$ibforums->lang['gtourneyinfotxt1']." ***</strong></font></div>
					<div><font color='black' size='2'>".$ibforums->lang['gtourneyinfotxt2']."</font></div>
                    			</tr>";

		$game['extra'] = "";
		if( trim($game['object']) != "" )
		{
                	$game['extra'] .= $this->html->objective($game);
        	}
		if( trim($game['gkeys']) != "" )
		{
			$game['extra'] .= $this->html->keys($game);
		}

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_play_game_tourney')) ? eval($hook) : false;
		}

                 $this->output .= $this->html->tourneygame($game,$top,$tid);

                 $this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$game['gtitle'];
                 $this->nav        = array( "<a href='".$ibforums->base_url."act=Arcade'>{$ibforums->lang['page_title']}</a>", $game['gtitle'] );

         }

        //----------------------------------------
        // Show_Stats
        //
        // This shows the leaderboard
        //
        //----------------------------------------

        function show_stats() {
                global $ibforums, $DB, $std, $vbulletin, $vboptions;

		// clean incoming stuff...
		$ibforums->input['comment'] = ibp_cleanhtml(ibp_cleansql($ibforums->input['comment']));
		$ibforums->input['s_id'] = intval(ibp_cleansql($ibforums->input['s_id']));
		$ibforums->input['gameid'] = intval($ibforums->input['gameid']);

                if($ibforums->input['comment'] != "") {

                        if(isset($ibforums->input['s_id']) && $ibforums->input['s_id'] != "") {
                                $DB->query("UPDATE ibf_games_scores SET comment='".$ibforums->input['comment']."' WHERE s_id=".$ibforums->input['s_id']);
                        } else {
                                $DB->query("UPDATE ibf_games_scores SET comment='".$ibforums->input['comment']."' WHERE mid=".$this->arcade->user['id']." AND gid=".$ibforums->input['gameid']);
                        }
                }

        $query_limit = "";
        if( $this->arcade->settings['scores_amount'] )
        {
                $this->arcade->get_pages(1);
            $start = 0;
            if( isset($ibforums->input['st']) )
            {
                    $start = intval($ibforums->input['st']);
            }
            $query_limit = "LIMIT ".$start.", ".$this->arcade->settings['scores_amount'];
        }

                $id = intval($ibforums->input['gameid']);

                $DB->query("SELECT g.gid,g.gname,g.gtitle,g.gwords,g.gcat,g.highscore_type,g.decpoints, c.*  FROM ibf_games_list AS g, ibf_games_champs AS c WHERE (g.gid = c.champ_gid) AND gid=".$id);
                if( $DB->get_num_rows() )
        {
                $ginfo = $DB->fetch_row();
            //$ginfo['champ_score'] = $this->arcade->t3h_format($ginfo['champ_score']);
                        //replaced
                        $ginfo['champ_score'] = $this->arcade->do_arcade_format($ginfo['champ_score'],$ginfo['decpoints']);
                        if( !empty($ginfo['champ_mid']) )
                {
                        $DB->query("SELECT avatar,avatar_size AS size FROM ibf_members WHERE id=".$ginfo['champ_mid']);
                        $avatar = $DB->fetch_row();
                                $ginfo['avatarcode'] = $std->get_avatar($avatar , 1 , $avatar['size']);
                }
        }
        else
        {
                $DB->query("SELECT * FROM ibf_games_list WHERE gid=".$id);
            $ginfo = $DB->fetch_row();
        }
        $ginfo['avatarcode'] = (empty($ginfo['avatarcode'])) ? "<img src='./arcade/images/noavatar.gif' alt='' />" : $ginfo['avatarcode'];

           // favorites-link
            $temp = unserialize($this->arcade->user['favs']);

                if( !is_array($temp) )
                {
                        $temp = array();
                }

                $favs = $temp;

            $favtitle = $ibforums->lang['add_to_faves'];
            $favtitle = preg_replace("/<% GAMENAME %>/i" , $game['gtitle'] , $favtitle);
            $star = "";

            if( in_array($ginfo['gid'] , $favs) )
            {
                //$star = "<img src='./arcade/images/favs.gif' title='".$ibforums->lang['favorite']."' alt='".$ibforums->lang['favorite']."' />&nbsp;";
                $favtitle = $ibforums->lang['remove_from_faves'];
                $favtitle = preg_replace("/<% GAMENAME %>/i" , $ginfo['gtitle'] , $favtitle);
            }

            if( $this->arcade->user['arcade_access'] == 2 && $this->arcade->user['id'] )
            {
                    $ginfo['fave'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=favorites&amp;gameid=".$ginfo['gid']."' title='".$favtitle."'>$favtitle</a>";
            }

	// make the link fit everything ;)
	$ginfo['backlink'] = $ibforums->lang['arcade_home'];
	if ($this->arcade->settings['use_cats'])
	{
		$ginfo['backlink'] = $ibforums->lang['showothersincat'];
	}

        $this->arcade->make_links($ginfo['gid'] , $ginfo['gtitle']);
                $this->output .= $this->html->leaderstart($ginfo, $this->arcade->links);

                $DB->query("SELECT mid FROM ibf_games_scores WHERE gid=".$id);
                $num_scores=0;
                while($nsarray = $DB->fetch_row()){
                        $num_scores++;
                }

        $ordering = ($ginfo['highscore_type'] == "high") ? "DESC" : "ASC";

                //$this_query = $DB->query("SELECT * FROM ibf_games_scores WHERE gid=".$id." ORDER BY score ".$ordering.", datescored ASC ".$query_limit);
                $this_query = $DB->query("SELECT * FROM ibf_games_scores WHERE gid=".$id." ORDER BY score ".$ordering.",timespent ASC ".$query_limit);
                $ctr=$start+1;
                $intctr=1;
                $rowcol = "alt2";
                while($lboard = $DB->fetch_row($this_query)) {

	            	//$lboard['comment'] = array( 'TEXT' => $lboard['comment'], 'SMILIES' => 1, 'CODE' => 1, 'SIGNATURE' => 0, 'HTML' => 0);
                	//$lboard['comment'] = $this->parser->convert($lboard['comment']);

	// parse the comment
	$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
	if ($vbversion == "3.0")
	{
		require_once('./includes/functions_bbcodeparse.php');		
		$parsed_comment = parse_bbcode($lboard['comment']);
	}
	else
	{
		require_once('./includes/class_bbcode.php');
		$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
		$parsed_comment = $bbcode_parser->parse($lboard['comment'],0,1);
	}
	$lboard['comment'] = $parsed_comment;


			if ($ibforums->lang[timeformat1]=="de")
			{
                        $std->time_options['LROW'] = "{$ibforums->lang['timeformat2']} \u\m {$ibforums->lang['timeformat3']}";
			}
			else
			{
                        $std->time_options['LROW'] = "{$ibforums->lang['timeformat2']} {$ibforums->lang['timeformat3']}";
			}

                        $formatteddate = $std->get_date($lboard['datescored'],'LROW');

                        $usercell = "<a href='{$ibforums->base_url}act=Arcade&amp;module=report&amp;user={$lboard[mid]}'>";
                        $usercell .= "{$lboard[name]}</a>";
                        $datecell = $formatteddate;
                        $scorecell = $this->arcade->do_arcade_format($lboard['score'],$ginfo['decpoints']);

                        if ($lboard['timespent'] == 0)
                                $lboard['timespent'] = $ibforums->lang['n_a'];
                        else
                                $lboard['timespent'] = $this->arcade->thatdate($lboard['timespent']);

                        if($this->arcade->settings['skin'] != 0) {
                            if($rowcol == "alt1") {
                                        $rowcol = "alt2";
                                } else {
                                        $rowcol = "alt1";
                                }
                        }

                        $this->output .= $this->html->leaderrow($lboard,$ctr,$usercell,$datecell,$scorecell,$rowcol);
                        $ctr++;
                        $intctr++;
                        $start_row++;
                }

                $this->output .= $this->html->stop($this->arcade->links['pages']);
                 $this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$ibforums->lang['arcade_scores'];
                 $this->nav        = array( "<a href='".$ibforums->base_url."act=Arcade&amp;cat=".$ginfo['gcat']."'>{$ibforums->lang['page_title']}</a>", "{$ibforums->lang['hscores_title']}" );
        }


	// #######################################
	// new function for secure games (v32/v33)
	// #######################################

	function verify_score()
	{
		global $DB, $DEBUGMODE;

		list($usec, $sec) = explode(" ", microtime());
		$gametime = ((float)$usec + (float)$sec);
        
		$randomchar = rand(1, 10);
		$randomchar2 = rand(1, 5);

		if ($this->arcade->user['arcade_access'] > 2)
		{
			// this is a guest
			$userinfo['arcade_session'] = intval($_COOKIE['guestsession']);
		}
		else
		{
			$userquery = $DB->query("SELECT * FROM ibf_user WHERE userid=".$this->arcade->user['id']);
			$userinfo = $DB->fetch_row($userquery);
		}

		if ($userinfo['arcade_session']=="")
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #021 - no session";
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		$DB->query("SELECT sessd FROM ibf_games_session WHERE sessid='".$userinfo['arcade_session']."'");
 		$s = $DB->fetch_row();

		if( !$DB->get_num_rows() )
		{
			die();
		}

		if( $s['sessd'] != 1 )
        	{
			$DB->query("UPDATE ibf_games_session SET randchar1 = '".$randomchar."' , randchar2 = '".$randomchar2."' , start = '".$gametime."' , sessd = 1 WHERE sessid = '".$userinfo['arcade_session']."'");
			//sleep(1); 	// Do not unedit this unless your instructed to !
			// Do not edit this line!
			echo "&randchar=$randomchar&randchar2=$randomchar2&savescore=1&blah=OK";
			exit();
		}
	}

	function save_score()
	{
		// ### for new, secured ibPro-Games

		global $DB, $ibforums, $std, $DEBUGMODE, $FIXIE, $vboptions, $vbulletin, $NATIVEMODE;

		$tgame = 0;
		$player_ip = $ibforums->input['IP_ADDRESS'];
		$player_score = isset($ibforums->input['gscore']) ? $ibforums->input['gscore'] : 0;
		$gidencoded = isset($ibforums->input['arcadegid']) ? $ibforums->input['arcadegid'] : 0;
		$genscore = $ibforums->input['enscore'];
		$swfgname = $ibforums->input['gname'];
		$player_score_encode = $player_score;

		// ### protection for external scripts faking incoming gamedata
		// ### by MrZeropage

		// look who sends the POST-data...
		$referer = "";

		if ($_SERVER['REFERER']!="") { $referer = strtolower($_SERVER['REFERER']); }
		if ($_SERVER['HTTP_REFERER']!="") { $referer = strtolower($_SERVER['HTTP_REFERER']); }
		if ($HTTP_SERVER_VARS['REFERER']!="") { $referer = strtolower($HTTP_SERVER_VARS['REFERER']); }

		// avoid external scripts calling this function
		if ((strpos($referer,"arcade.php") > 0) && (strpos($referer,$vboptions[bburl]) > 0))
		{
			// maybe there is some cheater trying to inject a score ?! *boooooh*
			$VERBOSE = ($DEBUGMODE == 2) ? " -> referrercheck | referer=".$referer : "";
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #002";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		// ###

		if ($this->arcade->user['arcade_access'] == 3)
		{
			// This is a Guest that won't record any score
			$player_score = 0;
			$good_score = 1;
		}

		if ($this->arcade->user['arcade_access'] > 2)
		{
			// this is a guest
			$userinfo['arcade_session']	= intval($_COOKIE['guestsession']);
			$userinfo['arcade_sess_start'] 	= $_COOKIE['gpstarted'];
			$userinfo['arcade_gtype']	= 0;	// guests never play tourneys!
			$userinfo['arcade_sess_gid'] = intval($_COOKIE['gidstarted']);
			$player_name = $this->arcade->user['name'];
		}
		else
		{
			$userquery = $DB->query("SELECT * FROM ibf_user WHERE userid=".$this->arcade->user['id']);
			$userinfo = $DB->fetch_row($userquery);
			$player_name = $userinfo['username'];
		}

		$DB->query("SELECT * FROM ibf_games_list WHERE gid=".$userinfo['arcade_sess_gid']);
		$g = $DB->fetch_row();

		// Kiss Cookies good bye!
		if( !isset($userinfo['arcade_gtype']) || $userinfo['arcade_gtype'] == '0' )
		{
			$tgame = 0;
			$tid = 0;
		}
		else
		{
			$tgame = 1;
			$tid = $userinfo['arcade_gtype'];
		}
      
		if ($userinfo['arcade_session']=="")
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #022 - no session";
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		$DB->query("SELECT * FROM ibf_games_session WHERE sessid='".$userinfo['arcade_session']."' LIMIT 1");
 		$vs = $DB->fetch_row();
		$gid = $vs['gameid'];

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_savescore_readsessiondata')) ? eval($hook) : false;
		}

		if ((($vs['sessd']!=1) && ($vs['sessid']>0)) && ($FIXIE==1))
		{
			// this score has NOT run through the verify() function!
			// actual issue using IE7 and doing more than one play per game

			// make it valid anyway ;)
			list($usec, $sec) = explode(" ", microtime());
			$gametime = ((float)$usec + (float)$sec);
			$randomchar = rand(1, 10);
			$randomchar2 = rand(1, 5);

			$vs['sessd'] = 1;
			$vs['start'] = $gametime;
			$vs['randchar1'] = $randomchar;
			$vs['randchar2'] = $randomchar2;
			$genscore = $player_score * $vs['randchar1'] ^ $vs['randchar2'];
		}

		// for the new games...
		if ( ( file_exists(ROOT_PATH.'arcade/gamedata/'.$g['gname'].'/v32game.txt' ) ) || ( file_exists(ROOT_PATH.'arcade/gamedata/'.$g['gname'].'/v3game.txt' ) ) )
		{
			$encoded_gid = $vs['gameid'] * $vs['randgid'] ^ $vs['randgid2'];

			if ($gidencoded == 0)
			{
				$gidencoded = $g['gid'] * $vs['randgid'] ^ $vs['randgid2'];
			}
		}

		$decodescore = $player_score * $vs['randchar1'] ^ $vs['randchar2'];

		list($usec, $sec) = explode(" ", microtime());
		$time_end = ((float)$usec + (float)$sec);
		$timecheck = round($time_end - $vs['start'], 4);

		$DB->query("DELETE FROM ibf_games_session WHERE sessid=".$userinfo['arcade_session']);

		if ( ($vs['sessd'] != 1) || (!$vs['start']) || (!$vs['sessid']) )
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #003";
			$BROWSERDATA = ($_SERVER['HTTP_USER_AGENT'] != "") ? $_SERVER['HTTP_USER_AGENT'] : $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			$VERBOSE = ($DEBUGMODE == 2) ? " -> sessd=".$vs['sessd']." | start=".$vs['start']." | sessid=".$vs['sessid']."<br />userid=".$this->arcade->user['id']." | ui[arcade_session]=".$userinfo['arcade_session']." | ui[arcade_sess_gid]=".$userinfo['arcade_sess_gid']."<br />FIXIE = ".$FIXIE."<br /><br />".$BROWSERDATA : "";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		if ($gid != $userinfo['arcade_sess_gid'])
		{
			// avoid cross-scoring... finally! *gg*
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #004";
			$VERBOSE  = ($DEBUGMODE == 2) ? " -> gid=".$gid." | ui[arcade_sess_gid]=".$userinfo['arcade_sess_gid'] : "";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		$readtimeoutquery = $DB->query("SELECT scoretimeout FROM ibf_games_settings");
		$readtimeout = $DB->fetch_row($readtimeoutquery);
		$SCORETIMEOUT = $readtimeout['scoretimeout'];

		if( !$timecheck || $timecheck > $SCORETIMEOUT )
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "Transmission Timeout (".$timecheck." sec)" : "Error #005";
			$VERBOSE  = ($DEBUGMODE == 2) ? " -> timecheck=".$timecheck : "";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		if( $genscore != $decodescore )
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #006";
			$VERBOSE  = ($DEBUGMODE == 2) ? " -> genscore=".$genscore." | decodescore=".$decodescore." | score=".$player_score : "";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		if ( file_exists(ROOT_PATH.'arcade/gamedata/'.$g['gname'].'/v32game.txt' )  )
		{
			if( ($gidencoded != $encoded_gid) || ($swfgname != $g['gname']) )
			{
				$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #007";
				$VERBOSE  = ($DEBUGMODE == 2) ? " -> gidenc=".$gidencoded." | enc_gid=".$encoded_gid." | swfgn=".$swfgname." | g[gname]=".$g['gname'] : "";
				$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
				$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
			}
		}

		//Get's the timespent
		$timespent = 0;
		if( $userinfo['arcade_sess_start'] )
		{
			$timespent = time() - $userinfo['arcade_sess_start'];	
		}
        
		if( $timespent )
		{
			$DB->query("UPDATE ibf_games_list SET gtime=gtime+'".$timespent."', gtotalscore=gtotalscore+'".$player_score."' WHERE gid='".$gid."' LIMIT 1");
			$DB->query("UPDATE ibf_members SET games_played=games_played+1, time_played=time_played+'".$timespent."' WHERE id=".$ibforums->member['id']);
		}

		// users sessiondata is no longer needed, so kick it to avoid any re-usage
       		$DB->query("UPDATE ibf_members SET arcade_sess_gid='0', arcade_sess_start='0', arcade_gtype=0, arcade_session='0' WHERE id=".$this->arcade->user['id']);

		$this->storescore($this->arcade->user['id'],$player_score,$timespent,$gid,$tgame,$tid,0);
	}


	function post_score()
	{
		// ### for old, unsecured ibPro-Games
		// ### for v3arcade-Games
		// ### for pnFlashGames-Games

		global $ibforums, $DB, $std, $vboptions, $vbulletin, $DEBUGMODE, $NATIVEMODE;
		$tgame = 0;

		$player_ip = $ibforums->input['IP_ADDRESS'];
		$member_id = $this->arcade->user['id'];
		$player_name = $this->arcade->user['name'];
		$player_score = floatval($_POST['gscore']);
		$game_name = ibp_cleansql($_POST['gname']);
		$score = $ibforums->input['gscore'];
		$keepsess = $ibforums->input['keepsess'];

		// ### protection for external scripts faking incoming gamedata
		// ### by MrZeropage

		// look who sends the POST-data...
		$referer = "";
		if ($_SERVER['REFERER']!="") { $referer = strtolower($_SERVER['REFERER']); }
		if ($_SERVER['HTTP_REFERER']!="") { $referer = strtolower($_SERVER['HTTP_REFERER']); }
		if ($HTTP_SERVER_VARS['REFERER']!="") { $referer = strtolower($HTTP_SERVER_VARS['REFERER']); }

		// avoid external scripts calling this function
		if ((strpos($referer,"arcade.php") > 0) && (strpos($referer,$vboptions[bburl]) > 0))
		{
			// maybe there is some cheater trying to inject a score ?! *boooooh*
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #008";
			$VERBOSE  = ($DEBUGMODE == 2) ? " -> REF = ".$referer : "";
			$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		// ###

		if ($this->arcade->user['arcade_access'] == 3)
		{
			// This is a Guest that won't record any score
			$player_score = 0;
			$good_score = 1;
		}

		if ($this->arcade->user['arcade_access'] > 2)
		{
			// GUEST
			$userinfo['arcade_session']	= $_COOKIE['guestsession'];
			$userinfo['arcade_sess_start'] 	= $_COOKIE['gpstarted'];
			$userinfo['arcade_gtype']	= 0;	// guests never play tourneys!
		}
		else
		{
			$userquery = $DB->query("SELECT * FROM ibf_user WHERE userid=".$this->arcade->user['id']);
			$userinfo = $DB->fetch_row($userquery);
		}

		$getgameidquery = $DB->query("SELECT gid FROM ibf_games_list WHERE gname='".$game_name."'");
		$getgameid = $DB->fetch_row($getgameidquery);

		$timespent = 0;
		if( $userinfo['arcade_sess_start'] )
		{
			$timespent = time() - $userinfo['arcade_sess_start'];	
		}

		if(strstr($score, ":") !== false)
		{
			$timestamp = strtotime($score);
			$formatedTime = strftime("%H:%M:%S", $timestamp);
			$hours = substr($formatedTime, 0, 2);
			$minutes = substr($formatedTime, 3, 2);
			$seconds = substr($formatedTime, 6, 2);
			$numSeconds = (($hours * 60) * 60) + ($minutes * 60) + $seconds;

			$score = $numSeconds;
			$ibforums->input['gscore'] = $score;
		}

		// Kiss Cookies good bye!
		if( !isset($userinfo['arcade_gtype']) || $userinfo['arcade_gtype'] == '0' )
		{
			$tgame = 0;
			$tid = 0;
		}
		else
		{
			$tgame = 1;
			$tid = $userinfo['arcade_gtype'];
		}

		$DB->query("SELECT gid, highscore_type, game_type FROM ibf_games_list WHERE gname='".$game_name."' LIMIT 1");
		$g = $DB->fetch_row();

		if ($g['game_type']==1)
		{
			// this is a secure game which should not use POST_SCORE !!
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #009";
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		if ($userinfo['arcade_session']=="")
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #023 - no session";
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		$DB->query("SELECT * FROM ibf_games_session WHERE sessid='".$userinfo['arcade_session']."' LIMIT 1");
 		$vs = $DB->fetch_row();
		$gid = $vs['gameid'];

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_postscore_readsessiondata')) ? eval($hook) : false;
		}

		if ($keepsess != 1)
		{
			// remove session as all data is read from it... and this is NO PNfg
			$DB->query("DELETE FROM ibf_games_session WHERE sessid=".$userinfo['arcade_session']);
		}

		if ( (!$vs['sessid']) )
		{
			$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #010";
			$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
		}

		// #########################################
		// final fix for cross-scoring by MrZeropage
		// #########################################

		// first we check if GameID stored in userinfo is the same of submitting Game
		if ($this->arcade->user['arcade_access'] < 3)	// make sure this is NOT a guest
		{
			// compare userinfo and sessiondata
			if ( ($gid != $userinfo['arcade_sess_gid']) || ($gid != $g['gid']) || ($userinfo['userid'] != $vs['mid']) || ($vs['gname'] != $game_name) )
			{
				$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #011";
				$VERBOSE  = ($DEBUGMODE == 2) ? " -> gid=".$gid."|ui[a_sess_gid]=".$userinfo['arcade_sess_gid']."|g[gid]=".$g['gid']."|vs[mid]=".$vs['mid']."|vs[gname]=".$vs['gname']."|gamename=".$game_name : "";
				$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
				$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
			}

			// compare incoming gameid with the one stored in sessiondata
			if ($getgameid['gid'] != $gid)
			{
				$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #012";
				$VERBOSE  = ($DEBUGMODE == 2) ? " -> gid=".$gid."|getgameid=".$getgameid['gid'] : "";
				$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
				$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
			}

		}
		else
		{
			// this is a guest...
			if ($vs['mname'] != $_COOKIE['guesthash'])
			{
				$ERRORLEVEL = ($DEBUGMODE == 0) ? "" : "Error #013";
				$VERBOSE  = ($DEBUGMODE == 2) ? " -> vs[mname]=".$vs['mname']."|guesthash=".$_COOKIE['guesthash'] : "";
				$ERRORLEVEL = $ERRORLEVEL . $VERBOSE;
				$std->Error( array( LEVEL => 1, MSG => 'cheater', EXTRA => $ERRORLEVEL) );
			}

			// cleanup cookiestuff from guest
			$_COOKIE['guesthash'] = "";
			$_COOKIE['guestsession'] = "";
		}

		if ($keepsess != 1)
		{
			// users sessiondata is no longer needed, so kick it to avoid any re-usage
	       		$DB->query("UPDATE ibf_members SET arcade_sess_gid='0', arcade_sess_start='0', arcade_gtype=0, arcade_session='0' WHERE id=".$this->arcade->user['id']);
		}

		// #########################################

		if ($timespent < 86400)
		{
			$DB->query("UPDATE ibf_games_list SET gtime=gtime+'".$timespent."', gtotalscore=gtotalscore+'".$player_score."' WHERE gid='".$gid."' LIMIT 1");
			if ( ($ibforums->member['id'] > 0) && ($this->arcade->user['arcade_access']==2 || $this->arcade->user['arcade_access']==4) )
			{
				$DB->query("UPDATE ibf_members SET games_played=games_played+1, time_played=time_played+'".$timespent."' WHERE id=".$ibforums->member['id']);
			}
		}

		$this->storescore($this->arcade->user['id'],$player_score,$timespent,$gid,$tgame,$tid,$keepsess);
	}


	function storescore($userid,$player_score,$timespent,$gid,$tgame,$tid,$keepsess)
	{
		// ##############################################################
		// common handling of scores coming from any game (by MrZeropage)
		// ##############################################################

		global $DB, $std, $vbulletin, $vboptions, $ibforums, $LOGIPS, $NATIVEMODE;

		$gid = intval($gid);
		$tid = intval($tid);
		$userid = intval($userid);
		$keepsess = intval($keepsess);
		$player_score = floatval($player_score);
		$timespent = ibp_cleansql($timespent);
		$server = strtolower($_SERVER['HTTP_REFERER']);
		$referer = strpos($server,"arcade.php");

		// for guests playing and calling games directly (without arcade.php) we have to re-check cookies
		if (($_COOKIE['ibPAcookiecheck'] != "yesss") && ($_COOKIE['guesthash']) > 0)
		{
			$VERBOSE = ($DEBUGMODE == 2) ? " cookie #003 -> ghash=".$_COOKIE['guesthash']." | ibPAcheck=".$_COOKIE['ibPAcookiecheck']." | referer=".$_SERVER['HTTP_REFERER'] : "";
			$std->Error( array( LEVEL => 1, MSG => 'cat_pass_notice', EXTRA => $VERBOSE) );
		}

		$gamequery = $DB->query("SELECT highscore_type FROM ibf_games_list WHERE gid='".$gid."' LIMIT 1");
		$g = $DB->fetch_row($gamequery);
        	$ordering = ($g['highscore_type'] == "high") ? "DESC" : "ASC";

		$player_name = $this->arcade->user['name'];
		$member_id = $userid;
		$player_ip = ($LOGIPS == 0) ? "" : $ibforums->input['IP_ADDRESS'];

		// get this user's best result in that game
        	$scorequery = $DB->query("SELECT score, timespent FROM ibf_games_scores WHERE gid=".$gid." AND mid=".$this->arcade->user['id']." ORDER BY score ".$ordering." LIMIT 0, 1");
        	if( $DB->get_num_rows($scorequery) )
        	{
                	$userscore = $DB->fetch_row($scorequery);
            		$score = $userscore['score'];
			$usertime = $userscore['timespent'];
			$name_found = 1;
        	}
        	else
        	{
                	$score = 0;
			$usertime = 0;
			$name_found = 0;
        	}

		if($tgame == 0)
        	{
			$std->time_options['LROW'] = "G:i";

			$max_scores_shown = $this->arcade->settings['scores_amount'];

			$DB->query("SELECT g.gid,g.gname,g.gtitle,g.gwords,g.gcat,g.highscore_type,g.decpoints,g.cost,g.jackpot,g.jackpot_type,g.g_rating,g.g_raters,c.*  FROM ibf_games_list AS g, ibf_games_champs AS c WHERE (g.gid = c.champ_gid) AND gid=".$gid);
			if( $DB->get_num_rows() )
                	{
                       		$ginfo = $DB->fetch_row();
				$ginfo['champ_score'] = $this->arcade->do_arcade_format($ginfo['champ_score'],$ginfo['decpoints']);
				if( !empty($ginfo['champ_mid']) )
                        	{
					$DB->query("SELECT avatar,avatar_size AS size FROM ibf_members WHERE id=".$ginfo['champ_mid']);
					$avatar = $DB->fetch_row();
                                        $ginfo['avatarcode'] = $std->get_avatar($avatar , 1 , $avatar['size']);
                        	}
                	}
                	else
                	{
                        	$DB->query("SELECT * FROM ibf_games_list WHERE gid=".$gid);
                    		$ginfo = $DB->fetch_row();
                	}

			$ginfo['avatarcode'] = (empty($ginfo['avatarcode'])) ? "<img src='./arcade/images/noavatar.gif' alt='' />" : $ginfo['avatarcode'];

			$DB->query("SELECT champ_score AS score, champ_time AS time, champ_name AS name, champ_mid AS mid, champ_gtitle FROM ibf_games_champs WHERE champ_gid=".$gid);
			if( $DB->get_num_rows() )
			{
                    		$champ = $DB->fetch_row();

//xxxxx
				if (($player_score<>0) && ((($player_score > $champ['score'] && $ginfo['highscore_type'] == "high") || ($player_score < $champ['score'] && $ginfo['highscore_type'] == "low")) || (($player_score == $champ['score']) && (($timespent < $champ['time']) || ($champ['time']==0) ))))
				{
					$vbversion = substr($vboptions[templateversion],0,3);
					if (($vbversion != "3.0") && ($NATIVEMODE==0))
					{
						($hook = vBulletinHook::fetch_hook('ibproarcade_new_champ')) ? eval($hook) : false;
					}

                        		$db_string = $DB->compile_db_update_string( array ( 'champ_gid'     => $gid,
                                       	                                         'champ_gtitle'  => $ginfo['gtitle'],
                                               	                                 'champ_mid'     => $this->arcade->user['id'],
                                                       	                         'champ_name'    => $this->arcade->user['name'],
                                                               	                 'champ_date'    => time(),
										'champ_score'        => $player_score,
										'champ_time'	=> $timespent ) );

					// update Avatarinfo for HTML-Output
					$DB->query("SELECT avatar,avatar_size AS size FROM ibf_members WHERE id=".$this->arcade->user['id']);
					$avatar = $DB->fetch_row();
                                        $ginfo['avatarcode'] = $std->get_avatar($avatar , 1 , $avatar['size']);
					$ginfo['avatarcode'] = (empty($ginfo['avatarcode'])) ? "<img src='./arcade/images/noavatar.gif' alt='' />" : $ginfo['avatarcode'];

					if ($player_score != 0)
					{
						// PM-Notification on new highscore by MrZeropage :-)
						$senderid = $this->arcade->user['id'];
						$sendername = $this->arcade->user['name'];
						$recipient = $champ['mid'];

						$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
						if ($vbversion == "3.0")
						{
							$forumlink = $vboptions['bburl']."/";
						}
						else
						{
							$forumlink = $vbulletin->options['bburl']."/";
						}
					
						$title = $ibforums->lang['pmnote_title'];
						$mailtitle = $ibforums->lang['mailnote_title'];

						$message = $this->arcade->settings['msgsys_hscore_text'];
						$mailmessage = $this->arcade->settings['msgsys_hscore_text'];

						$message = preg_replace('/%NAME%/',$champ['name'],$message);
						$message = preg_replace('/%GAME%/',$ginfo['gtitle'],$message);
						$message = preg_replace('/%CHAMP%/',$this->arcade->user['name'],$message);
						$message = preg_replace("#%LINKGAME\|(.*?)%#","[url='".$forumlink."arcade.php?do=play&gameid=".$gid."']$1[/url]",$message);
						$message = preg_replace("#%LINKHIGH\|(.*?)%#","[url='".$forumlink."arcade.php?do=stats&gameid=".$gid."']$1[/url]",$message);

						$mailmessage = preg_replace('/%NAME%/',$champ['name'],$mailmessage);
						$mailmessage = preg_replace('/%GAME%/',$ginfo['gtitle'],$mailmessage);
						$mailmessage = preg_replace('/%CHAMP%/',$this->arcade->user['name'],$mailmessage);
						$mailmessage = preg_replace("#%LINKGAME\|(.*?)%#","<a href='".$forumlink."arcade.php?do=play&gameid=".$gid."'>$1</a>",$mailmessage);
						$mailmessage = preg_replace("#%LINKHIGH\|(.*?)%#","<a href='".$forumlink."arcade.php?do=stats&gameid=".$gid."'>$1</a>",$mailmessage);

						$mailmessage = strip_bbcode($mailmessage, true);
						$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);

						if ( ($senderid != $recipient) && ($this->arcade->settings['msgsys_hscore']==1) )
						{
							// does the recipient want to receive any Notifications from the Arcade ?
							$DB->query("SELECT arcade_pmactive, email FROM ibf_user WHERE userid=$recipient");
							$recip = $DB->fetch_row();
	
							// check for possible Guest-Player
							if ($guestplayerid == $recipient)
							{ $recip['arcade_pmactive']=0; }	

							if (($recip['arcade_pmactive'] == 1) && ($this->arcade->settings['msgsys_hscore']==1))
							{
								// Notification via PM
								if (($this->arcade->settings['notification']=="pm") || ($this->arcade->settings['notification']=="pm+mail"))
								{
									$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".addslashes($sendername)."', '".addslashes($title)."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($recipient))) . "', 0, " . TIMENOW . ", 0, 0)");
									$pmid = $DB->get_insert_id();
									$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$recipient");
									$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$recipient', '0', '0')");
								}
	
								// Notification via eMail
								if (($this->arcade->settings['notification']=="mail") || ($this->arcade->settings['notification']=="pm+mail"))
								{
									vbmail($recip['email'],$mailtitle,$mailmessage);
								}
							}
						}

						// finally update Highscore-Table
						if ( ($this->arcade->user['id'] != 0) && ($player_score <> 0) )
						{
        	                                	$DB->query("UPDATE ibf_games_champs SET ".$db_string." WHERE champ_gid=".$gid);
							$ginfo['champ_mid'] = $this->arcade->user['id'];
							$ginfo['champ_name'] = $this->arcade->user['name'];
							$ginfo['champ_score'] = $player_score;
						}
					}
				}
			}
			else
			{
				$db_string = $DB->compile_db_insert_string( array ( 'champ_gid'     => $gid,
                                	                                         'champ_gtitle'  => $ginfo['gtitle'],
                                        	                                 'champ_mid'     => $this->arcade->user['id'],
                                                	                         'champ_name'    => $this->arcade->user['name'],
                                                        	                 'champ_date'    => time(),
                                                                	    	 'champ_score'        => $player_score,
										'champ_time'	=> $timespent        ) );

        			if ($player_score <> 0)     // no champ with no result ...
        			{
                    			$DB->query("INSERT INTO ibf_games_champs (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
						$ginfo['champ_mid'] = $this->arcade->user['id'];
						$ginfo['champ_name'] = $this->arcade->user['name'];
						$ginfo['champ_score'] = $player_score;
        			}
            	}

			// best result of all time?   by MrZeropage
			if ($player_score <> 0)
			{
				$DB->query("SELECT gid, bestmid, bestscore, besttime, highscore_type FROM ibf_games_list WHERE gid=".$gid);
				if ($DB->get_num_rows())
				{
					// check if existing best result ever is lower
					$best = $DB->fetch_row();
					if (($best['bestscore'] < $player_score && $best['highscore_type'] == "high") || ($best['bestscore'] > $player_score && $best['highscore_type'] == "low") || (intval($best['bestscore'])==0) || ($best['bestscore']=="") || (($best['bestscore'] == $player_score) && (($best['besttime']==0) || ($best['besttime'] > $timespent))))
					{
						$userid=$this->arcade->user['id'];
						$DB->query("UPDATE ibf_games_list SET bestmid=".$userid.", bestscore=".$player_score.", besttime='".$timespent."' WHERE gid=$gid");
					}
				}
			}
			// end of b.r.o.a.t.

			if ( isset($player_score) && is_numeric($player_score) && isset($player_name) )
			{
				//Has this name played already?
				$gtime = time();

				$highsid = array();
				$highsid['s_id'] = 0;

				if( $this->arcade->settings['score_type'] == 'top' || empty($this->arcade->settings['score_type']) )
				{
					if ( $name_found == 1 )
					{
						// if name already exists, and score is good enough, update it
						if ( (($player_score > $score) && $ginfo['highscore_type'] == "high") || (($player_score < $score && $ginfo['highscore_type'] == "low") && $score != 0) || (($player_score == $score) && ($timespent < $usertime)) )
						{
							$db_string = $DB->compile_db_update_string( array ( 'score'             => $player_score,
                                                                                                 'datescored'          => $gtime,
												'ip' => $player_ip,
												'timespent' => $timespent  ) );
							$DB->query("UPDATE ibf_games_scores SET ".$db_string." WHERE mid=".$member_id." AND gid=".$gid);

							$good_score=1;
							$DB->query("SELECT s_id FROM ibf_games_scores WHERE score=".$player_score." AND datescored=".$gtime." AND gid=".$gid." AND mid=".$member_id." ORDER BY s_id DESC LIMIT 0, 1");
							$highsid = $DB->fetch_row();
						}
					}
					else
					{
						$good_score = 1;

						//Insert new name, score and ip
						if ($good_score==1)
						{
							$db_string = $DB->compile_db_insert_string( array ( 'mid'                          => $member_id,
        	                                                                                         'gid'                     => $gid,
                	                                                                                 'name'                    => $player_name,
                        			                                                         'score'                        => $player_score,
                                                 	                                                 'ip'                    => $player_ip,
                                                                                                        'timespent'                => $timespent,
                                                         		                                   'datescored'        => $gtime,
													'comment'	=> ''        ) );
							$DB->query("INSERT INTO ibf_games_scores
                                                          (" .$db_string['FIELD_NAMES']. ") VALUES
                                                   (". $db_string['FIELD_VALUES'] .")");
						//$getsid = $DB->get_insert_id();
						//$highsid['s_id']=$getsid;
						$DB->query("SELECT s_id FROM ibf_games_scores WHERE score=".$player_score." AND datescored=".$gtime." AND gid=".$gid." AND mid=".$member_id." ORDER BY s_id DESC LIMIT 0, 1");
						$highsid = $DB->fetch_row();
						}
					}
				}
				else
				{
                       		$good_score = 1;
                    		$db_string = $DB->compile_db_insert_string( array ( 'mid'                          => $member_id,
                                                                                 'gid'                     => $gid,
                                                                                 'name'                    => $player_name,
                                                                        'score'                        => $player_score,
                                                                                 'ip'                    => $player_ip,
                                                                                                                                                'timespent'                => $timespent,
                                                                            'datescored'        => $gtime,
										'comment'	=> ''        ) );
                    		$DB->query("INSERT INTO ibf_games_scores
                                                          (" .$db_string['FIELD_NAMES']. ") VALUES
                                                   (". $db_string['FIELD_VALUES'] .")");
					//$highsid = $DB->get_insert_id();
					//$highsid['s_id']=$getsid;
					$DB->query("SELECT s_id FROM ibf_games_scores ORDER BY s_id DESC LIMIT 0, 1");
					$highsid = $DB->fetch_row();
				}
			}

		if ($keepsess != 1)
		{
			// favorites-link
			$temp = unserialize($this->arcade->user['favs']);

                	if( !is_array($temp) )
                	{
                	 	$temp = array();
		        }

		        $favs = $temp;

		        $favtitle = $ibforums->lang['add_to_faves'];
		        $favtitle = preg_replace("/<% GAMENAME %>/i" , $game['gtitle'] , $favtitle);
		        $star = "";

		        if( in_array($ginfo['gid'] , $favs) )
		        {
		            //$star = "<img src='./arcade/images/favs.gif' title='".$ibforums->lang['favorite']."' alt='".$ibforums->lang['favorite']."' />&nbsp;";
		            $favtitle = $ibforums->lang['remove_from_faves'];
		            $favtitle = preg_replace("/<% GAMENAME %>/i" , $ginfo['gtitle'] , $favtitle);
		        }

		        if( $this->arcade->user['arcade_access'] == 2 && $this->arcade->user['id'] )
		        {
		                $ginfo['fave'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=favorites&amp;gameid=".$ginfo['gid']."' title='".$favtitle."'>$favtitle</a>";
		        }

			// make the link fit everything ;)
			$ginfo['backlink'] = $ibforums->lang['arcade_home'];
			if ($this->arcade->settings['use_cats'])
			{
				$ginfo['backlink'] = $ibforums->lang['showothersincat'];
			}

			$this->arcade->make_links($ginfo['gid'] , $ginfo['gtitle']);
                        $this->output .= $this->html->leaderstart($ginfo, $this->arcade->links);

           		$yscore[1] = $player_name;
			$yscore[2] = $this->arcade->do_arcade_format($player_score,$ginfo['decpoints']);
			$yscore[3] = $std->get_date($gtime,'LROW');
			$yscore[4] = $gid;
			$yscore[5] = $good_score;
			$yscore[6] = $this->arcade->thatdate($timespent);

			$ordering = ($ginfo['highscore_type'] == "high") ? "DESC" : "ASC";

			// set all League-Scores for this Game to default, as they get recalculated next
			$leaguearray = explode("," , $this->arcade->settings['league_scores'] );
			$DB->query("UPDATE ibf_games_league SET points='".$leaguearray[10]."', position='0' WHERE gid=".$gid);

			// calculate which scores should show up on page
			$s_query = $DB->query("SELECT COUNT(*) AS counter FROM ibf_games_scores WHERE gid=".$gid);
			$s_all = $DB->fetch_row($s_query);
			$s_all = $s_all['counter'];
			$s_limit = $this->arcade->settings['scores_amount'];

			$lookctr = 1; $s_pos = 0;
			$lookupquery = $DB->query("SELECT s_id, mid, name, score FROM ibf_games_scores WHERE gid=".$gid." ORDER BY score ".$ordering.",timespent ASC");
			while ($lookup = $DB->fetch_row($lookupquery))
			{
				if ((($highsid['s_id']!=0) && ($lookup['s_id']==$highsid['s_id'])) || (($highsid['s_id']=="0") && ($lookup['name']==$player_name)))
				{
					$s_pos = $lookctr;
				}
				$lookctr++;
			}

			if (($s_pos/$s_limit)==(floor($s_pos/$s_limit)))
			{
				$startpage = (floor($s_pos / $s_limit) * $s_limit)-$s_limit;
			}
			else
			{
				$startpage = floor($s_pos / $s_limit) * $s_limit;
			}

			$endpage = $startpage + $s_limit + 1;

			$startpage = $startpage;

			if ($endpage > $s_all)
			{
				$endpage = $s_all;
			}
			if ($startpage < 0)
			{
				$startpage = 0;
			}
			if ($endpage <= $startpage)
			{
				$endpage = $startpage + 1;
			}

			// setup LIMIT which should be at least 11 to make sure all Top10 get leaguepoints
			if ($endpage < 11)
			{
				$limitquery = "LIMIT 11";
			}
			else
			{
				$limitquery = "LIMIT ".$endpage; 
			}

			$this_query = $DB->query("SELECT * FROM ibf_games_scores WHERE gid=".$gid." ORDER BY score ".$ordering.",timespent ASC ".$limitquery);

			$ctr=1;
			$rowcol = "alt2";
			while($lboard = $DB->fetch_row($this_query))
			{
				// parse the comment
				$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
				if ($vbversion == "3.0")
				{
					require_once('./includes/functions_bbcodeparse.php');		
					$parsed_comment = parse_bbcode($lboard['comment']);
				}
				else
				{
					require_once('./includes/class_bbcode.php');
					$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
					$parsed_comment = $bbcode_parser->parse($lboard['comment'],0,1);
				}
				$lboard['comment'] = $parsed_comment;

				if ($ibforums->lang[timeformat1]=="de")
				{
	                	        $std->time_options['LROW'] = "{$ibforums->lang['timeformat2']} \u\m {$ibforums->lang['timeformat3']}";
				}
				else
				{
		                        $std->time_options['LROW'] = "{$ibforums->lang['timeformat2']} {$ibforums->lang['timeformat3']}";
				}

				$formatteddate = $std->get_date($lboard['datescored'],'LROW');

				$usercell = "<a href='{$ibforums->base_url}act=Arcade&amp;module=report&amp;user={$lboard[mid]}'>";
				$usercell .= "{$lboard[name]}</a>";
				$datecell = $formatteddate;
				$scorecell = $this->arcade->do_arcade_format($lboard['score'],$ginfo['decpoints']);
				if ($lboard['timespent'] == 0)
                        	{
					$lboard['timespent'] = $ibforums->lang['n_a'];
				}
				else
				{
					$lboard['timespent'] = $this->arcade->thatdate($lboard['timespent']);
				}

				if($this->arcade->settings['skin'] != 0)
				{
					if($rowcol == "alt1")
					{
						$rowcol = "alt2";
					}
					else
					{
						$rowcol = "alt1";
					}
				}

				// only display scorelines that match the page needed
				if (($ctr > $startpage) && ($ctr < ($endpage+1)))
				{
					if ($ctr != $s_pos)
					{
						$this->output .= $this->html->leaderrow($lboard,$ctr,$usercell,$datecell,$scorecell,$rowcol);
					}
					else
					{
						if ($this->arcade->user['arcade_access']==3) { $yscore[5]=0; }
						if (($ctr==1 && $s_pos==1) && ($yscore[5]==1)) { $usercell = "<img src='./arcade/images/trophy.gif' alt=''>&nbsp;".$usercell."&nbsp;<img src='./arcade/images/trophy.gif' alt=''>"; }
						$commentcell=$lboard['comment'];
						if($yscore[5]==1)
						{
							$commentcell = "<form action=\"".$ibforums->base_url."act=Arcade&amp;do=stats&amp;gameid=".$yscore[4]."&amp;st=".$startpage."\" method=\"POST\">\n";
							$commentcell .= "<input type=\"text\" name=\"comment\" size=\"50\">&nbsp;&nbsp;\n";
							$commentcell .= "<input type=\"submit\" name=\"submitbtn\" value=\"".$ibforums->lang['save_comment']."\">\n";
							if( $this->arcade->settings['score_type'] == 'all' )
							{
								$commentcell .= "<input type=\"hidden\" name=\"s_id\" value=\"{$highsid[s_id]}\">";
							}
							$commentcell .= "</form>";
						}
						$data['comment'] = $commentcell;
						$data['timespent'] = $yscore[6];

						$vbversion = substr($vboptions[templateversion],0,3);
						if (($vbversion != "3.0") && ($NATIVEMODE==0))
						{
							($hook = vBulletinHook::fetch_hook('ibproarcade_play_game_finished')) ? eval($hook) : false;
						}

						$this->output .= $this->html->leaderrow($data,"<b>".$ctr."</b>","<b>".$usercell."</b>",$datecell,$scorecell,"alt1");
					}
				}

				switch($ctr)
				{
                                case 1: $points = $leaguearray[0];
                                break;
                                case 2: $points = $leaguearray[1];
                                break;
                                case 3: $points = $leaguearray[2];
                                break;
                                case 4: $points = $leaguearray[3];
                                break;
                                case 5: $points = $leaguearray[4];
                                break;
                                case 6: $points = $leaguearray[5];
                                break;
                                case 7: $points = $leaguearray[6];
                                break;
                                case 8: $points = $leaguearray[7];
                                break;
                                case 9: $points = $leaguearray[8];
                                break;
                                case 10: $points = $leaguearray[9];
                                break;
                                default: $points = $leaguearray[10];
				}

				// ***** LEAGUE finally fixed by MrZeropage :) *****
				if (($ctr < 11) && ($points != $leaguearray[10]))
				{
					// check if that player already has an entry for that game
					$DB->query("SELECT gid, mid, lid FROM ibf_games_league WHERE gid=".$gid." AND mid=".$lboard['mid']." AND position=0");					
					if ($DB->get_num_rows())
					{
						// already in league, so just update the entry
						$onerow = $DB->fetch_row();
		
		                               	$db_string = $DB->compile_db_update_string( array (
									'mid'   => $lboard['mid'],
									'gid'   => $gid,
									'position'      => $ctr,
									'points'        => $points,
									'cat'                => $ginfo['gcat']
									) );
						$DB->query("UPDATE ibf_games_league SET ".$db_string." WHERE gid=".$gid." AND mid=".$lboard['mid']." AND lid=".$onerow['lid']);
					}
					else
					{	
							// new entry in league
		                               	$db_string = $DB->compile_db_insert_string( array (
									'mid'   => $lboard['mid'],
									'gid'   => $gid,
									'position'      => $ctr,
									'points'        => $points,
									'cat'                => $ginfo['gcat']
									) );
						$DB->query("INSERT INTO ibf_games_league (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
					}
				}

				// ***** end of League *****

				$ctr++;
			}

			$this->output .= $this->html->stop($this->arcade->links['pages']);

			if ($yscore[5]!=1)     // show information if score is not stored because of main settings 
			{
				if ($this->arcade->user['arcade_access']==3)
				{
					$this->output .= "<div align='center'><font color='red'>".$ibforums->lang['not_recorded_guest']."</font></div><br />";
				}
				else
				{
					$this->output .= "<div align='center'><font color='red'>".$ibforums->lang['not_recorded']."</font></div><br />";
				}
			}

			$this->output .= "<div align='center'><a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid=".$gid."'>".$ibforums->lang['play_again']."</a></div><br />";

			$rating = "";
			$rating = $ibforums->lang['rating'];
			$raters = unserialize($ginfo['g_raters']);

			if (isset($raters[$this->arcade->user['id']]) || $this->arcade->user['id']==0)
			{
				// no output for rating (guestplayer or already rated this game)
			}
			else
			{
				if( empty($ginfo['g_raters']) )
				{
					$rating .= $ibforums->lang['no_votes'];
				}
				else
				{
					$amount = count($raters).$ibforums->lang['rates'];
					for( $a = 1 ; $a <= $ginfo['g_rating'] ; $a++ )
					{
						$rating .= "<img src='./arcade/images/star1.gif' title='".$amount."' alt='".$amount."' />";
					}
					$leftover = (5-$ginfo['g_rating']);
					for( $a = 1 ; $a <= $leftover ; $a++ )
					{
						$rating .= "<img src='./arcade/images/star2.gif' title='".$amount."' alt='".$amount."' />";
					}
				}

				$this->output .= "<div align='center'>".$rating."<br />";
				$this->output .= "<a href=\"#\" onclick=\"window.open('{$ibforums->base_url}act=Arcade&amp;do=rate&amp;gid={$gid}&amp;scored=1','comment_edit','height=150,width=400'); return false;\">{$ibforums->lang['rate_game']}</a></div><br /><br />";
			}

			$this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$ibforums->lang['hscores_title'];
			$this->nav        = array( "<a href='".$ibforums->base_url."act=Arcade&amp;cat=".$ginfo['gcat']."'>{$ibforums->lang['page_title']}</a>", "{$ibforums->lang['hscores_title']}" );

	}
	else
	{
		// PNfg! This means we have to update the league now...
		$leaguearray = explode("," , $this->arcade->settings['league_scores'] );
		$DB->query("UPDATE ibf_games_league SET points='".$leaguearray[10]."', position='0' WHERE gid=".$gid);

		$game_query = $DB->query("SELECT * FROM ibf_games_list WHERE gid=".$gid);
		$ginfo = $DB->fetch_row($game_query);
		$ordering = ($ginfo['highscore_type'] == "high") ? "DESC" : "ASC";

		$ctr = 1;
		$this_query = $DB->query("SELECT mid FROM ibf_games_scores WHERE gid='".$gid."' ORDER BY score ".$ordering.", timespent ASC LIMIT 0,10");
		if ($DB->get_num_rows($this_query))
		{
			while($lboard = $DB->fetch_row($this_query))
			{
				switch($ctr)
				{
					case 1: $points = $leaguearray[0];
					break;
					case 2: $points = $leaguearray[1];
					break;
					case 3: $points = $leaguearray[2];
					break;
					case 4: $points = $leaguearray[3];
					break;
					case 5: $points = $leaguearray[4];
					break;
					case 6: $points = $leaguearray[5];
					break;
					case 7: $points = $leaguearray[6];
					break;
					case 8: $points = $leaguearray[7];
					break;
					case 9: $points = $leaguearray[8];
					break;
					case 10: $points = $leaguearray[9];
					break;
					default: $points = $leaguearray[10];
				}

				if ($points > 0)
				{
					extract($ginfo);
					$mid = $this->arcade->user['id'];

					// check if that player already has an entry for that game
					$DB->query("SELECT gid, mid, lid FROM ibf_games_league WHERE gid=".$gid." AND mid=".$mid." AND position=0");					
					if ($DB->get_num_rows())
					{
						// already in league, so just update the entry
						$onerow = $DB->fetch_row();
		
		                               	$db_string = $DB->compile_db_update_string( array (
									'mid'   => $mid,
									'gid'   => $gid,
									'position'      => $ctr,
									'points'        => $points,
									'cat'                => $ginfo['gcat']
									) );
						$DB->query("UPDATE ibf_games_league SET ".$db_string." WHERE gid=".$gid." AND mid=".$mid." AND lid=".$onerow['lid']);
					}
					else
					{	
						// new entry in league
		                               	$db_string = $DB->compile_db_insert_string( array (
									'mid'   => $mid,
									'gid'   => $gid,
									'position'      => $ctr,
									'points'        => $points,
									'cat'                => $ginfo['gcat']
									) );
						$DB->query("INSERT INTO ibf_games_league (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
					}
				}
				$ctr++;
			}
		}		
	}

		}
		elseif($tgame==1)
		{
			$tid = intval($_COOKIE['tidstarted']);

			$tquery = $DB->query("SELECT nbtries,cost,numplayers,gid FROM ibf_tournaments WHERE tid = '".$tid."' LIMIT 1");
			$row = $DB->fetch_row($tquery);
			$nbtries = $row['nbtries'];
			$jackpot = $row['cost'] * $row['numplayers'];

			// get Game-Settings
			$gamequery = $DB->query("SELECT gid, bestmid, bestscore, besttime, highscore_type, gtitle FROM ibf_games_list WHERE gid=".$row['gid']);
			$gameinfo=$DB->fetch_row($gamequery);
			$scoretype=$gameinfo['highscore_type'];

			// best result of all time?   by MrZeropage
			if ($player_score <> 0)
			{
				// check if existing best result ever is lower
				if ( (($gameinfo['bestscore'] < $player_score) && $scoretype == "high") || (($gameinfo['bestscore'] > $player_score) && $scoretype == "low") || (empty($gameinfo['bestscore'])) || ( ($gameinfo['bestscore'] == $player_score) && (($gameinfo['besttime']==0) || ($gameinfo['besttime'] > $timespent))))
				{
					$userid=$this->arcade->user['id'];
					$DB->query("UPDATE ibf_games_list SET bestmid=".$userid.", bestscore='".$player_score."', besttime='".$timespent."' WHERE gid=".$gid);
				}
			}

                        // Check to see if this faceoff in this rung has been played.
                        $tourneyquery = $DB->query("SELECT rung,rungscore,timeplayed,faceoff,timesplayed FROM ibf_tournament_players WHERE mid=".$this->arcade->user['id']." AND tid=".$tid." ORDER BY rung DESC");

                        $ctr = 0;
                        while($row = $DB->fetch_row($tourneyquery))
			{
                      		if($row['timesplayed'] <= $nbtries)
				{
                           		$ctr++;
                                }
                                $savearray[0] = $row['rung'];
                                $savearray[1] = $row['rungscore'];
                                $savearray[2] = $row['timeplayed'];
                                $savearray[3] = $row['faceoff'];
                                $savearray[4] = $this->arcade->user['id'];
                                $savearray[5] = $tid;
                                $savearray[6] = $row['timesplayed'];
                        }

                        if($ctr > 0)
			{
				$thisscore = $player_score;
				if ( (($thisscore > $savearray[1]) && $scoretype == "high") || (($thisscore > 0 && $savearray[1]==0) && $scoretype == "low") || (($thisscore < $savearray[1]) && $scoretype == "low") && ($thisscore != 0) )
				{
	                                // Save new game score in that spot
	                                $DB->query("UPDATE ibf_tournament_players SET rungscore=".$player_score.", timeplayed='".time()."', notified=0 WHERE mid=".$this->arcade->user['id']." AND tid=".$tid." AND rung=".$savearray[0]);
	                        	$savearray[1] = $thisscore;
	                	}
				else
				{
	                                $DB->query("UPDATE ibf_tournament_players SET timeplayed='".time()."', notified=0 WHERE mid=".$this->arcade->user['id']." AND tid=".$tid." AND rung=".$savearray[0]);
				}
	      		}
			else
			{
	  			$this->view_tourney($tid);
	       			return;
	   		}

		// see if the opponent has played yet
		$DB->query("SELECT * FROM ibf_tournament_players WHERE tid=".$tid." AND rung=".$savearray[0]." AND faceoff='".$savearray[3]."' AND mid <> '".$savearray[4]."' LIMIT 0, 1");
		$opponentinfo = $DB->fetch_row();

		$advancetourney=0;
		if($opponentinfo['timesplayed'] < $nbtries || $savearray[6] < $nbtries)
		{
			// check if the result could be clear before all Games are played

			// opponent is finished ?
			if ($opponentinfo['timesplayed']==$nbtries)
			{
				if (($savearray[1] > $opponentinfo['rungscore'] && $scoretype == "high") || ($savearray[1] < $opponentinfo['rungscore'] && $savearray[1]>0 && $scoretype == "low"))
				{
					// just beaten your opponent, so advance to next round :)
					$advancetourney=1;
					$savearray[6]=$nbtries;
				}
			}

			// player himself is finished ?
			if ($savearray[6]==$nbtries)
			{
				if (($savearray[1] < $opponentinfo['rungscore'] && $scoretype == "high") || ($savearray[1] > $opponentinfo['rungscore'] && $opponentinfo['rungscore']>0 && $scoretype == "low"))
				{
					// player did not reach opponent, so advance to next round :)
					$advancetourney=1;
					$opponentinfo['timesplayed']=$nbtries;
				}
			}

		} 
		else
		{
			$advancetourney=1;
		}

		if ($advancetourney==1)
		{
			if($savearray[3] == 1 || $savearray[3] == 2)
			{
				$nextfaceoff=1;
			} 
			else
			{
				$nextfaceoff=2;
			}

			if (($savearray[0] == 1) && ($opponentinfo['rungscore'] != $savearray[1]))
			{
				if (($opponentinfo['rungscore'] >= $savearray[1] && $scoretype == "high") || ($opponentinfo['rungscore'] < $savearray[1] && $opponentinfo['rungscore']>0 && $scoretype == "low"))
				{
					$winner = $opponentinfo['mid'];
					$loser	= $savearray[4];

				} 
				else 	
				{
					$winner = $savearray[4];
					$loser	= $opponentinfo['mid'];
				}

				$DB->query("SELECT name,id FROM ibf_members WHERE id='".$winner."' LIMIT 1");
				$name = $DB->fetch_row();
				$DB->query("UPDATE ibf_tournaments SET champion='".ibp_cleansql($name['name'])."' WHERE tid=".$tid);

				$DB->query("UPDATE ibf_tournament_players_statut SET statut='1' WHERE tid='".$tid."' AND mid='".$loser."'");
				$DB->query("UPDATE ibf_tournament_players_statut SET statut='3' WHERE tid='".$tid."' AND mid='".$winner."'");

				// Notification to winner by MrZeropage :-)
				if ($this->arcade->settings['msgsys_twin']==1)
				{
					$senderid = $this->arcade->user['id'];
					$sendername = $this->arcade->user['name'];
					$recipient = $winner;
	
					$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
					if ($vbversion == "3.0")
					{
						$forumlink = $vboptions['bburl']."/";
					}
					else
					{
						$forumlink = $vbulletin->options['bburl']."/";
					}
					
					$title = $ibforums->lang['pmnote_winner'];
					$mailtitle = $ibforums->lang['pmnote_winner'];
	
					$message = $this->arcade->settings['msgsys_twin_text'];
					$mailmessage = $this->arcade->settings['msgsys_twin_text'];
	
					$message = preg_replace('/%NAME%/',$name['name'],$message);
					$message = preg_replace('/%GAME%/',$gameinfo['gtitle'],$message);
					$message = preg_replace("#%LINK\|(.*?)%#","[url='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."']$1[/url]",$message);
	
					$mailmessage = preg_replace('/%NAME%/',$name['name'],$mailmessage);
					$mailmessage = preg_replace('/%GAME%/',$gameinfo['gtitle'],$mailmessage);
					$mailmessage = preg_replace("#%LINK\|(.*?)%#","<a href='".$forumlink."arcade.php?do=viewtourney&tid=".$gid."'>$1</a>",$mailmessage);
	
					$mailmessage = strip_bbcode($mailmessage, true);
					$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);

					// does the recipient want to receive any Notifications from the Arcade ?
					$DB->query("SELECT arcade_pmactive, email FROM ibf_user WHERE userid=$recipient");
					$recip = $DB->fetch_row();
	
					// check for possible Guest-Player
					if ($guestplayerid == $recipient)
					{ $recip['arcade_pmactive']=0; }	

					if ($recip['arcade_pmactive'] == 1)
					{
							// Notification via PM
						if (($this->arcade->settings['notification']=="pm") || ($this->arcade->settings['notification']=="pm+mail"))
						{
							$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".addslashes($sendername)."', '".addslashes($title)."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($recipient))) . "', 0, " . TIMENOW . ", 0, 0)");
							$pmid = $DB->get_insert_id();
							$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$recipient");
							$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$recipient', '0', '0')");
						}

						// Notification via eMail
						if (($this->arcade->settings['notification']=="mail") || ($this->arcade->settings['notification']=="pm+mail"))
						{
							vbmail($recip['email'],$mailtitle,$mailmessage);
						}
					}
				}
	
				$vbversion = substr($vboptions[templateversion],0,3);
				if (($vbversion != "3.0") && ($NATIVEMODE==0))
				{
					($hook = vBulletinHook::fetch_hook('ibproarcade_tourney_won')) ? eval($hook) : false;
				}

				$this->view_tourney($tid);
				return;
			}

			$timenow = time();

			if ($opponentinfo['rungscore'] == $savearray[1])
			{
				// both opponents finally have same result, so give both one more try
				$DB->query("UPDATE ibf_tournament_players SET timesplayed=timesplayed-1, timeplayed='".$timenow."', notified=0 WHERE tid='".$tid."' AND mid='".$opponentinfo['mid']."' AND rung='".$opponentinfo['rung']."'");
				$DB->query("UPDATE ibf_tournament_players SET timesplayed=timesplayed-1, timeplayed='".$timenow."', notified=0 WHERE tid='".$tid."' AND mid='".$savearray['4']."' AND rung='".$savearray[0]."'");
			}
			else
			{
				if (($opponentinfo['rungscore'] > $savearray[1] && $scoretype == "high") || ($opponentinfo['rungscore'] < $savearray[1] && $scoretype == "low"))
				{
					// opponent has won
					$db_string = $DB->compile_db_insert_string( array ( 	'mid'		=> $opponentinfo['mid'],
	                                                                                 	'tid'		=> $tid,
	                                                                                 	'rung'		=> ($opponentinfo['rung']-1),
	                                                                        		'rungscore'	=> 0,
        	                                                                         	'faceoff'	=> $nextfaceoff,
	                                                                            		'timeplayed'	=> $timenow,
	                                                                        		'timesplayed'	=> 0,
												'notified'	=> 0,        ) );
	
	                    		$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
					$DB->query("UPDATE ibf_tournament_players_statut SET statut='1' WHERE tid='".$tid."' AND mid='".$savearray[4]."'");

					// check if there is an opponent (to set back his timer)
					$check=$DB->query("SELECT mid FROM ibf_tournament_players WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($opponentinfo['rung']-1)." AND mid<>".$opponentinfo['mid']);
					if ($row = $DB->fetch_row($check))
					{
						$DB->query("UPDATE ibf_tournament_players SET timeplayed='".$timenow."', notified=0 WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($opponentinfo['rung']-1)." AND mid=".$row['mid']);
					}

					// fill up tries in old rung
					$DB->query("UPDATE ibf_tournament_players SET timesplayed=".$nbtries.", notified=1 WHERE tid=".$tid." AND rung=".$opponentinfo['rung']." AND mid=".$opponentinfo['mid']);

					// set data for notifications
					$winnerid = $opponentinfo['mid'];
					$loserid = $this->arcade->user['id'];
	              		} 
				else 
				{
					// player has won
					$db_string = $DB->compile_db_insert_string( array ( 	'mid'		=> $savearray[4],
	                                                                                 	'tid'		=> $savearray[5],
	                                                                                 	'rung'		=> ($savearray[0]-1),
	                                                                        		'rungscore'	=> 0,
	                                                                                 	'faceoff'	=> $nextfaceoff,
	                                                                            		'timeplayed'	=> $timenow,
	                                                                        		'timesplayed'	=> 0,
												'notified'	=> 0,        ) );
	
					$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
					$DB->query("UPDATE ibf_tournament_players_statut SET statut='1' WHERE tid='".$tid."' AND mid='".$opponentinfo['mid']."'");

					// check if there is an opponent (to set back his timer)
					$check=$DB->query("SELECT mid FROM ibf_tournament_players WHERE tid=".$savearray[5]." AND faceoff=".$nextfaceoff." AND rung=".($savearray[0]-1)." AND mid<>".$savearray[4]);
					if ($row = $DB->fetch_row($check))
					{
						$DB->query("UPDATE ibf_tournament_players SET timeplayed='".$timenow."', notified=0 WHERE tid=".$savearray[5]." AND faceoff=".$nextfaceoff." AND rung=".($savearray[0]-1)." AND mid=".$row['mid']);
					}

					// fill up tries in old rung
					$DB->query("UPDATE ibf_tournament_players SET timesplayed=".$nbtries.", notified=1 WHERE tid=".$savearray[5]." AND rung=".$savearray[0]." AND mid=".$savearray[4]);

					// set data for notifications
					$loserid = $opponentinfo['mid'];
					$winnerid = $this->arcade->user['id'];
					$tid = $savearray[5];
				}

				// Notifications by MrZeropage
				$senderid = $this->arcade->user['id'];
				$sendername = $this->arcade->user['name'];

				if ($senderid == $winnerid)
				{
					$winnername = $sendername;
					$getnamequery=$DB->query("SELECT username FROM ibf_user WHERE userid=".$loserid);
					$getname = $DB->fetch_row($getnamequery);
					$losername = $getname['username'];
				}
				else
				{
					$losername = $sendername;
					$getnamequery=$DB->query("SELECT username FROM ibf_user WHERE userid=".$winnerid);
					$getname = $DB->fetch_row($getnamequery);
					$winnername = $getname['username'];
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
					
				$title = $ibforums->lang['pmnote_elim'];
				$mailtitle = $ibforums->lang['pmnote_elim'];

				$message = $this->arcade->settings['msgsys_telim_text'];
				$mailmessage = $this->arcade->settings['msgsys_telim_text'];

				$message = preg_replace('/%NAME%/',$losername,$message);
				$message = preg_replace('/%OTHER%/',$winnername,$message);
				$message = preg_replace('/%GAME%/',$gameinfo['gtitle'],$message);
				$message = preg_replace("#%LINK\|(.*?)%#","[url='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."']$1[/url]",$message);

				$mailmessage = preg_replace('/%NAME%/',$losername,$mailmessage);
				$mailmessage = preg_replace('/%OTHER%/',$winnername,$mailmessage);
				$mailmessage = preg_replace('/%GAME%/',$gameinfo['gtitle'],$mailmessage);
				$mailmessage = preg_replace("#%LINK\|(.*?)%#","<a href='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."'>$1</a>",$mailmessage);

				$mailmessage = strip_bbcode($mailmessage, true);
				$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);

				$recipient = $loserid;

				// does the recipient want to receive any Notifications from the Arcade ?
				$DB->query("SELECT arcade_pmactive, email FROM ibf_user WHERE userid=$recipient");
				$recip = $DB->fetch_row();
	
				// check for possible Guest-Player
				if ($guestplayerid == $recipient)
				{ $recip['arcade_pmactive']=0; }	

				if (($recip['arcade_pmactive'] == 1) && ($this->arcade->settings['msgsys_telim']==1))
				{
					// Notification via PM
					if (($this->arcade->settings['notification']=="pm") || ($this->arcade->settings['notification']=="pm+mail"))
					{
						$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".addslashes($sendername)."', '".addslashes($title)."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($recipient))) . "', 0, " . TIMENOW . ", 0, 0)");
						$pmid = $DB->get_insert_id();
						$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$recipient");
						$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$recipient', '0', '0')");
					}

					// Notification via eMail
					if (($this->arcade->settings['notification']=="mail") || ($this->arcade->settings['notification']=="pm+mail"))
					{
						vbmail($recip['email'],$mailtitle,$mailmessage);
					}
				}

				// and now to the winner of the match...
				$title = $ibforums->lang['pmnote_adv'];
				$mailtitle = $ibforums->lang['pmnote_adv'];

				$message = $this->arcade->settings['msgsys_tadvance_text'];
				$mailmessage = $this->arcade->settings['msgsys_tadvance_text'];

				$message = preg_replace('/%NAME%/',$winnername,$message);
				$message = preg_replace('/%OTHER%/',$losername,$message);
				$message = preg_replace('/%GAME%/',$gameinfo['gtitle'],$message);
				$message = preg_replace("#%LINK\|(.*?)%#","[url='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."']$1[/url]",$message);

				$mailmessage = preg_replace('/%NAME%/',$winnername,$mailmessage);
				$mailmessage = preg_replace('/%OTHER%/',$losername,$mailmessage);
				$mailmessage = preg_replace('/%GAME%/',$gameinfo['gtitle'],$mailmessage);
				$mailmessage = preg_replace("#%LINK\|(.*?)%#","<a href='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."'>$1</a>",$mailmessage);

				$mailmessage = strip_bbcode($mailmessage, true);
				$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);

				$recipient = $winnerid;

				// does the recipient want to receive any Notifications from the Arcade ?
				$DB->query("SELECT arcade_pmactive, email FROM ibf_user WHERE userid=$recipient");
				$recip = $DB->fetch_row();
	
				// check for possible Guest-Player
				if ($guestplayerid == $recipient)
				{ $recip['arcade_pmactive']=0; }	

				if (($recip['arcade_pmactive'] == 1) && ($this->arcade->settings['msgsys_tadvance']==1))
				{
					// Notification via PM
					if (($this->arcade->settings['notification']=="pm") || ($this->arcade->settings['notification']=="pm+mail"))
					{
						$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".addslashes($sendername)."', '".addslashes($title)."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($recipient))) . "', 0, " . TIMENOW . ", 0, 0)");
						$pmid = $DB->get_insert_id();
						$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$recipient");
						$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$recipient', '0', '0')");
					}

					// Notification via eMail
					if (($this->arcade->settings['notification']=="mail") || ($this->arcade->settings['notification']=="pm+mail"))
					{
						vbmail($recip['email'],$mailtitle,$mailmessage);
					}
				}
			}

			if ($keepsess == 1)
			{
				// tournament-game, so change gametype to normal game after first result
				$DB->query("UPDATE ibf_members SET arcade_gtype='0' WHERE id=".$this->arcade->user['id']);
			}
			else
			{
				$this->view_tourney($tid);
			}
		}
		else
		{
			if ($keepsess == 1)
			{
				// tournament-game, so change gametype to normal game after first result
				$DB->query("UPDATE ibf_members SET arcade_gtype='0' WHERE id=".$this->arcade->user['id']);
			}
			else
			{
				$this->view_tourney($tid);
			}
		}
	}

	}


    function rate()
    {
            global $ibforums, $DB, $print;

        $gid = intval($ibforums->input['gid']);
	$pagereload = $ibforums->input['scored'];

        $raters = array();

        $DB->query("SELECT g_raters FROM ibf_games_list WHERE gid=".$gid);
        $temp = $DB->fetch_row();
        $temp = unserialize($temp['g_raters']);

        if( !is_array($temp) )
        {
                $temp = array();
        }

        $raters = $temp;

        $id = $this->arcade->user['id'];
        if( isset($raters[$id]) || $id == 0 )
        {
                $msg = ( $id ) ? $ibforums->lang['rated_allready'] : $ibforums->lang['guest_rate'];
            $html = $this->html->rating_general($msg,$pagereload);
            $html .= $this->html->copyright($this->version,$ibforums->lang['timeformat1'],$this->BFL);
            $print->pop_up_window("{$ibforums->lang['rate_title']}",$html);
            exit();
        }

        if( !isset($ibforums->input['rating']) )
        {
                $DB->query("SELECT gid, gtitle FROM ibf_games_list WHERE gid=".$gid);
            $game = $DB->fetch_row();

                    $html .= $this->html->rating($game,$pagereload);
                $html .= $this->html->copyright($this->version,$ibforums->lang['timeformat1'],$this->BFL);

                $print->pop_up_window("{$ibforums->lang['rate_title']}",$html);
                exit();
        }
        else
        {
            $new_amount = count($raters)+1;
            $total_rate = 0;
            foreach( $raters as $user=>$rate )
            {
                    $total_rate += $rate;
            }
            $total_rate += $ibforums->input['rating'];
            $new_rate = floor($total_rate/$new_amount);

            if( $new_rate > 5 )
            {
                    $new_rate = 5;
            }
            if( $new_rate < 1 )
            {
                    $new_rate = 1;
            }

            $raters[$id] = $ibforums->input['rating'];
            $new_raters = serialize($raters);
            $db_string = $DB->compile_db_update_string( array( 'g_rating'                =>        $new_rate,
                                                                                                               'g_raters'                =>        $new_raters,  ) );
                $DB->query("UPDATE ibf_games_list SET ".$db_string." WHERE gid=".$gid);

            $html = $this->html->rating_general($ibforums->lang['thanks_rating'],$pagereload);
            $html .= $this->html->copyright($this->version,$ibforums->lang['timeformat1'],$this->BFL);
            $print->pop_up_window("{$ibforums->lang['rate_title']}",$html);
                exit();
        }
    }

	function facteur($jour , $mois , $annee)
	{
		global $ibforums, $DB, $std, $GROUP;

	    $b=365*$annee;
	    $c=31*($mois-1);
	    if (($mois==1) || ($mois==2)){
	        $d= 0;
	        $e = intval(($annee -1)/4);
	        $h = intval(0.75*(intval(($annee-1)/100)+1));
	    }
	    else {
	        $d= intval(0.4*$mois+2.3);
	        $e = intval($annee/4);
	        $h = intval(0.75*(intval($annee/100)+1));
	    }
	    $result = $jour + $b+ $c - $d +$e -$h;
	    return $result;
	}

        function diff_dates($time)
        {
                $diff = time() - $time;
                $daysDiff = floor($diff/60/60/24);

                return $daysDiff;

        }

	// Function to view the terminated tourneys

	function view_tourney_end() {
		global $ibforums, $DB, $std, $GROUP;

		$std->time_options['ARCADE'] = $ibforums->lang['timeformat4'];

		$this->output = $this->html->finished_tournament_listing($tourneyinfo);

		$DB->query("SELECT t.numplayers,t.datestarted,t.tid,g.gtitle,t.champion, t.url_discut FROM ibf_tournaments as t, ibf_games_list as g WHERE t.champion <> '' AND t.gid = g.gid ORDER BY datestarted DESC");
		while($row = $DB->fetch_row()) {
			$row['link'] = "<a href='".$ibforums->vars['base_url']."do=viewtourney&amp;tid=".$row['tid']."'>".$ibforums->lang['see_this_tourney']."</a>";
			$row['datestarted'] = $std->get_date($row['datestarted'],'ARCADE');
			$this->output .= $this->html->tournament_row($row);
		}

		$this->output .= $this->html->stop("&nbsp;", "&nbsp;");
 		$this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$ibforums->lang['tournament_view'];
 		$this->nav        = array( "<a href='".$ibforums->vars['base_url']."'>{$ibforums->lang['page_title']}</a>", $ibforums->lang['tournament_view'] );
 	}

	function create_tourney() {

		global $ibforums, $DB, $std, $GROUP, $vbulletin, $vboptions, $guestplayerid, $NATIVEMODE;

		// is this a guest?
		if ($ibforums->member['id']==0)
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_tourney_group') );
		}

		// check users private permissions first
		$DB->query("SELECT create_tourney AS tourney FROM ibf_user WHERE userid=".$ibforums->member['id']);
		$userperm = $DB->fetch_row();

		// check if player is allowed to create a tournament through all primary and secondary usergroups
		$cancreate = 0;
		$DB->query("SELECT m.usergroupid, m.membergroupids FROM ibf_members AS m LEFT JOIN ibf_groups AS g ON (m.mgroup=g.g_id) WHERE m.id=".$ibforums->member['id']);
	        $userdata = $DB->fetch_row();
		$groups = array();
		$groups[] = $userdata['usergroupid'];
		if ($userdata['membergroupids'] != "")
		{
			$groups = array_merge($groups,explode(',',$userdata['membergroupids']));
		}
		$groupstring = implode(',',$groups);

		$DB->query("SELECT tourney FROM ibf_groups WHERE g_id IN (".$groupstring.")");
		while ($check = $DB->fetch_row())
		{
			if ($check['tourney'] == 1)
			{
				$cancreate = 1;
			}
		}
		unset($groups);
		unset($check);
		unset($userdata);

		$DB->query("SELECT g.tourney AS tourney, u.is_arcade_mod AS is_mod, g.g_access_cp AS is_admin FROM ibf_members AS u LEFT JOIN ibf_groups AS g ON (u.mgroup=g.g_id) WHERE userid=".$ibforums->member['id']." LIMIT 0,1");
		$groupperm = $DB->fetch_row();

		if (!$groupperm['is_mod'] && !$groupperm['is_admin'])	// Admins and Arcade-Mod do always have permission to create Tourneys
		{
			if (!$cancreate)
			{
				$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_tourney_group') );
			}

			if (!$userperm['tourney'])
			{
				$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_tourney_user') );
			}
		}

		if ($guestplayerid == $ibforums->member['id'])
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_tourney_group') );
		}


		// Looking for the game list

	$form = "";

	$cat=""; $catquery="";
	if ($this->arcade->settings['use_cats'] == 1) { $catquery="cat.pos, cat.c_id, "; }
        $DB->query("	SELECT g.*, cat.password, cat.cat_name 
			FROM ibf_games_list AS g, ibf_games_cats AS cat 
			WHERE g.active = 1 AND g.tourney_use = 1 AND g.gcat=cat.c_id AND trim(password)='' 
			ORDER BY ".$catquery."gtitle");

        while( $GAME = $DB->fetch_row() )
        {
		if( $GAME['cat_name'] != $cat && $this->arcade->settings['use_cats'] == 1 )
		{
			if( preg_match("/optgroup/i", $form) )
			{
				$form .= "</optgroup>";
			}
			$form .= "<optgroup label='".$GAME['cat_name']."'>";
			$cat = $GAME['cat_name'];
		}

        	$form .= "<option value='{$GAME['gid']}'>".$GAME['gtitle']."</option>";
        }




	$extra="";
	// detect vBplaza
	if (($vbulletin->options['vbbux_enabled'] == 1) && ($vbulletin->options['vbbux_arcadeintegration'] == 1))
	{
		$extra = "	<tr>
  				<td class='alt2'>{$ibforums->lang['costs_tourney']}</td>
				<td class='alt2'>
				<input type=\"text\" name=\"tourney_costs\" id=\"costs\" value=\"0\" size=\"5\" />
				</td>
				</tr>";
	}

	$vbversion = substr($vboptions[templateversion],0,3);
	if (($vbversion != "3.0") && ($NATIVEMODE==0))
	{
		($hook = vBulletinHook::fetch_hook('ibproarcade_create_tourney')) ? eval($hook) : false;
	}


	   $this->output .= $this->html->create_tourney($form,$extra);
	   $this->page_title = $ibforums->vars['board_name']." -> ".$ibforums->lang['page_title']." -> ".$ibforums->lang['tournament_view'];
	   $this->nav        = array( "<a href='".$ibforums->vars['base_url']."'>{$ibforums->lang['page_title']}</a>", $ibforums->lang['tournament_view'] );

	}

	function do_create_tourney() {

 		global $ibforums, $DB, $std, $GROUP, $bbuserinfo, $print, $vboptions, $vbulletin, $NATIVEMODE;

		 // We aren't a guest... are we?
		 if( $ibforums->member['id'] == "" || $ibforums->member['id'] == "0") {
		     $std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_guests') );
	 	}

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_do_create_tourney_start')) ? eval($hook) : false;
		}

		$nbjoueurs = $ibforums->input['nbjoueurs'];
		$nbtries = $ibforums->input['nbtries'];
		$game = intval($ibforums->input['the_game']);
		$costs = intval($ibforums->input['tourney_costs']);

		$DB->query("SELECT gtitle from ibf_games_list WHERE gid='".$game."' LIMIT 1");
		$GAME = $DB->fetch_row();
		$gtitle = $GAME['gtitle'];

       	$db_string = $DB->compile_db_insert_string( array ( 'gid'	    	=> $game,
           													'numplayers'	=> $nbjoueurs,
                                                            'datestarted' 	=> time(),
	                                                        'demare'   		=>  0,
   								'creat'		   => $this->arcade->user['name'],
   	                                                        'plibre'		=> $nbjoueurs - 1,
   	                                                        'nbtries'		=> $nbtries,
								'cost'		=> $costs,
								'champion'	=> '',
								'url_discut'	=> '',
                                                            ) );

	   	$DB->query("INSERT INTO ibf_tournaments
	        									 (" .$db_string['FIELD_NAMES']. ") VALUES
	                    						 (". $db_string['FIELD_VALUES'] .")");

	   	$tid = $DB->get_insert_id();
	   	$nbjoueurs = $nbjoueurs + 1;
	   	$rung = ceil($nbjoueurs / 3);
	   	$cpt = 1;

	   	while($cpt < $nbjoueurs)
	   	{
	   		$faceoff = ceil($cpt/2);
       		$db_string = $DB->compile_db_insert_string( array ( 'mid'			=> 	0,
	        													'tid'			=> 	$tid,
	                                                            'rung'			=>  $rung,
	                                                           	'rungscore' 	=>  0,
	                                                           	'faceoff'		=> 	$faceoff,
	                                                            'timeplayed'	=> 	time(),
	                                                            'timesplayed'   =>  0,
									'notified'	=> 0,
	                                                            ) );
         	$DB->query("INSERT INTO ibf_tournament_players
	        							   (" .$db_string['FIELD_NAMES']. ") VALUES
	                    				   (". $db_string['FIELD_VALUES'] .")");

	        $cpt = $cpt + 1;
	   	}

	   	// Insertion du membre créateur du tournoi dans ce tournoi (au hasard)

	   	$nbjoueurs = $nbjoueurs - 1;
		$hasard = rand(1,$nbjoueurs);
		$faceoff = ceil($hasard/2);
		$DB->query("UPDATE ibf_tournament_players SET mid='".$ibforums->member['id']."' WHERE rung='".$rung."' AND tid='".$tid."' AND faceoff='".$faceoff."' LIMIT 1");

		// Création statut pour ce tournoi (0 = Ok, actif)

                $db_string = $DB->compile_db_insert_string( array ( 'tid'	=> $tid,
                                                                    'mid'	=> $ibforums->member['id'],
                                                                    'statut'	=> 0,	) ) ;

		$DB->query("INSERT INTO ibf_tournament_players_statut (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

		// Création statut bidon pour mid = 0

                $db_string = $DB->compile_db_insert_string( array ( 'tid'	=> $tid,
                                                               	    'mid'	=> 0,
                                                                     'statut'	=> 0, ) );

		$DB->query("INSERT INTO ibf_tournament_players_statut (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

		if ($FIXSTYLE == 1)
		{
			echo " "; // some forums need this...
		}

		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_do_create_tourney_end')) ? eval($hook) : false;
		}

 		$print->redirect_screen($ibforums->lang['tournament_created'], $ibforums->vars['base_url']."do=viewtourney&amp;tid=".$tid);
	}

	function register_tourney($tid) {

		global $ibforums, $DB, $std, $GROUP, $print, $vboptions, $vbulletin, $guestplayerid, $NATIVEMODE;

		 // We aren't a guest... are we?
		 if( $ibforums->member['id'] == "" || $ibforums->member['id'] == "0" || $guestplayerid==$ibforums->member['id']) {
		     $std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_guests') );
	 	}

		// check if player is allowed to play the Arcade through all primary and secondary usergroups
		$playok = 0;
		$DB->query("SELECT g.arcade_access, m.usergroupid, m.membergroupids FROM ibf_members AS m LEFT JOIN ibf_groups AS g ON (m.mgroup=g.g_id) WHERE m.id=".$ibforums->member['id']);
	        $userdata = $DB->fetch_row();
		$groups = array();
		$groups[] = $userdata['usergroupid'];
		if ($userdata['membergroupids'] != "")
		{
			$groups = array_merge($groups,explode(',',$userdata['membergroupids']));
		}
		$groupstring = implode(',',$groups);

		$DB->query("SELECT arcade_access FROM ibf_groups WHERE g_id IN (".$groupstring.")");
		while ($check = $DB->fetch_row())
		{
			if ($check['arcade_access'] > 1)
			{
				$playok = 1;
			}
		}
		unset($groups);
		unset($check);

		if ($playok == 0)
		{
		     $std->Error( array( 'LEVEL' => 1, 'MSG' => 'err_noplay') );
		}

		$tid = intval($tid);
	 	// Ce membre n'est-il pas déjà inscrit à ce tournoi ?
	 	$DB->query("SELECT mid FROM ibf_tournament_players WHERE tid='".$tid."' AND mid='".$ibforums->member['id']."'");
	 	if($DB->fetch_row())
	 	{
	 		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'player_already') );
	 	}
		/*
        	if($ibforums->member['posts'] < $GROUP['p_require'])
        	{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 't_p_requires') );
		}
		*/

		$DB->query("SELECT creat, gid, demare, numplayers, cost FROM ibf_tournaments WHERE tid = '".$tid."' LIMIT 1");
		$row = $DB->fetch_row();

		if($row['demare'] == 1)
        	{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 't_deja_dem') );
		}

		$vbversion = substr($vboptions[templateversion],0,3);
		if (($vbversion != "3.0") && ($NATIVEMODE==0))
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_register_tourney_start')) ? eval($hook) : false;
		}

		$DB->query("SELECT gtitle from ibf_games_list WHERE gid='".$row['gid']."' LIMIT 1");
		$GAME = $DB->fetch_row();
		$gtitle = $GAME['gtitle'];

		$nbjoueurs = $row['numplayers'];
		$rung = ceil($nbjoueurs / 3);
		$gid = $row['gid'];
		$creat = $row['creat'];

		$ctr = 0;
		$liste = "";

		$DB->query("SELECT * FROM ibf_tournament_players WHERE tid='".$tid."' ORDER BY faceoff ASC");
		while($row = $DB->fetch_row())
		{
			if($ctr==0)
			{
				$ctr = 1;
			} else {
				$ctr = 0;
			}

			$num = ($row['faceoff']*2) - $ctr;

			if($row['mid'] == 0)            // Place libre
			{
				$liste .= $num;
			}
		}

		$hasard = rand(1,strlen($liste)) - 1;
		$num = $liste{$hasard};                  // On a une place ok :)

		$faceoff = ceil($num/2);
		$datestarted = time();
		$DB->query("UPDATE ibf_tournament_players SET mid='".$ibforums->member['id']."', notified=0 WHERE rung='".$rung."' AND tid='".$tid."' AND mid='0' AND faceoff='".$faceoff."' LIMIT 1");

		// Création statut pour ce tournoi (0 = Ok, actif)
		$DB->query("INSERT INTO ibf_tournament_players_statut VALUES ('".$tid."', '".$ibforums->member['id']."', '0')");

		if(strlen($liste) == 1)   // Le tournoi est donc plein après cette inscription ;)
		{
			$DB->query("UPDATE ibf_tournament_players SET timeplayed='".$datestarted."' WHERE tid='".$tid."'");
			$DB->query("UPDATE ibf_tournaments SET datestarted='".$datestarted."',demare = '1', plibre='0' WHERE tid='".$tid."'");

			if ($this->arcade->settings['msgsys_tstart']==1)
			{
				// send notification-message to all participants that the tourney starts right now
				$DB->query("SELECT gtitle from ibf_games_list WHERE gid='".$gid."' LIMIT 1");
				$title = $DB->fetch_row();
				$gamename = $title['gtitle'];

				$sendername 	= "Arcade System Message";

				global $vbulletin, $vboptions;

				$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
				if ($vbversion == "3.0")
				{
					$forumlink = $vboptions['bburl']."/";
				}
				else
				{
					$forumlink = $vbulletin->options['bburl']."/";
				}
	
				$callusers = $DB->query("SELECT mid FROM ibf_tournament_players WHERE tid='".$tid."'");
				while($row = $DB->fetch_row($callusers))
				{
					$recipient = $row['mid'];

					// does the recipient want to receive any Notifications from the Arcade ?
					$DB->query("SELECT arcade_pmactive, email, username FROM ibf_user WHERE userid=".$recipient);
					$recip = $DB->fetch_row();
					$recipientname = $recip['username'];
	
					$title = $gamename . " " . $ibforums->lang['pm_tourney_full'];
					$mailtitle = $gamename . " " . $ibforums->lang['pm_tourney_full'];

					$message = $this->arcade->settings['msgsys_tstart_text'];
					$mailmessage = $this->arcade->settings['msgsys_tstart_text'];

					$message = preg_replace('/%NAME%/',$recipientname,$message);
					$message = preg_replace('/%GAME%/',$gamename,$message);
					$message = preg_replace("#%LINK\|(.*?)%#","[url='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."']$1[/url]",$message);

					$mailmessage = preg_replace('/%NAME%/',$recipientname,$mailmessage);
					$mailmessage = preg_replace('/%GAME%/',$gamename,$mailmessage);
					$mailmessage = preg_replace("#%LINK\|(.*?)%#","<a href='".$forumlink."arcade.php?do=viewtourney&tid=".$tid."'>$1</a>",$mailmessage);

					$mailmessage = strip_bbcode($mailmessage, true);
					$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);

					if ($guestplayerid == $recipient)
					{ $recip['arcade_pmactive']=0; }
	
					if ($recip['arcade_pmactive'] == 1)
					{
						// Notification via PM
						if (($this->arcade->settings['notification']=="pm") || ($this->arcade->settings['notification']=="pm+mail"))
						{
							$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$recipient."', '".$sendername."', '".$title."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($recipient))) . "', 0, " . TIMENOW . ", 0, 0)");
							$pmid = $DB->get_insert_id();
							$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$recipient");
							$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$recipient', '0', '0')");
						}
	
						// Notification via eMail
						if (($this->arcade->settings['notification']=="mail") || ($this->arcade->settings['notification']=="pm+mail"))
						{
							vbmail($recip['email'],$mailtitle,$mailmessage);
						}
					}
				}
			}
		}
	
		$DB->query("UPDATE ibf_tournaments SET plibre=plibre-1 WHERE tid='".$tid."'");

		if ($FIXSTYLE == 1)
		{
			echo " "; // some forums need this...
		}

 		$print->redirect_screen($ibforums->lang['tourney_reg_ok'], $ibforums->vars['base_url']."do=viewtourney&amp;tid=".$tid);

	 }

	function corige_tournoi($tid, $rung, $faceoff)
	{
		global $ibforums, $DB, $std, $GROUP, $print;

		$tid = intval($tid);
		$rung = intval($rung);
		$faceoff = intval($faceoff);

		$DB->query("SELECT * FROM ibf_tournaments WHERE tid='$tid'");
		$infot = $DB->fetch_row();

		$DB->query("SELECT * from ibf_games_list WHERE gid='".$infot['gid']."' LIMIT 1");
		$infog = $DB->fetch_row();

		$cpt=0;
		// Recherche des infos sur les 2 joueurs
		$DB->query("SELECT * FROM ibf_tournament_players WHERE tid='$tid' AND rung='$rung' AND faceoff='$faceoff'");
		while($row = $DB->fetch_row())
		{
			$infoj[$cpt] = $row;
			$cpt++;
		}

		$nbtries = $infot['nbtries'];

		if($infoj[0]['timesplayed'] < $nbtries || $infoj[1]['timesplayed'] < $nbtries) {
			echo $ibforums->lang['advance_players'];
			$this->view_tourney($tid);
			return;
		}

		if($faceoff == 1 || $faceoff == 2) {
			$nextfaceoff=1;
		} else {
			$nextfaceoff=2;
		}

		// Cas où un champion doit être couronné

		if($rung == 1) {

			if ( (($infoj[0]['rungscore'] >= $infoj[1]['rungscore']) && $infog['highscore_type'] == "high") || (($infoj[0]['rungscore'] <= $infoj[1]['rungscore']) && $infog['highscore_type'] == "low" && $infoj[0]['rungscore'] > 0) )
			{
				$winner = $infoj[0]['mid'];
			} else {
				$winner = $infoj[1]['mid'];
			}

			$DB->query("SELECT name,id FROM ibf_members WHERE id='".$winner."' LIMIT 1");
			$name = $DB->fetch_row();
			$DB->query("UPDATE ibf_tournaments SET champion='".ibp_cleansql($name['name'])."' WHERE tid='$tid' LIMIT 1");

			// Mise à jour du statut de tous les participants (Statut = 3 => Tournoi terminé)
			$DB->query("UPDATE ibf_tournament_players_statut  SET statut='3' WHERE tid='".$tid."'");

			$print->redirect_screen($ibforums->lang['operation_ok'], $ibforums->vars['base_url']."do=viewtourney&amp;tid=".$tid);
			return;

		}

		// Le tournoi n'est pas terminé, on fait juste avancer

		$loser = "";
		if ((($infoj[0]['rungscore'] >= $infoj[1]['rungscore']) && $infog['highscore_type'] == "high") || (($infoj[0]['rungscore'] <= $infoj[1]['rungscore']) && $infog['highscore_type'] == "low" && $infoj[0]['rungscore'] > 0))
		{
                    	$db_string = $DB->compile_db_insert_string( array ( 'mid'  			=> $infoj[0]['mid'],
                                                               		'tid'     		=> $tid,
                                                               		'rung'    		=> ($rung-1),
	                                                                'rungscore'		=> 0,
                                                               		'faceoff'    	=> $nextfaceoff,
                                                                    	'timeplayed'	=> time(),
                                                                        'timesplayed'	=> 0,	
									'notified'	=> 0,) );
                    	$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
			$loser = $infoj[1]['mid'];
		} else {
                    	$db_string = $DB->compile_db_insert_string( array ( 'mid'  			=> $infoj[1]['mid'],
                                                               		'tid'     		=> $tid,
                                                               		'rung'    		=> ($rung-1),
	                                                                'rungscore'		=> 0,
                                                               		'faceoff'    	=> $nextfaceoff,
                                                                    	'timeplayed'	=> time(),
                                                                        'timesplayed'	=> 0,
									'notified'	=> 0,	) );
                    	$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
			$loser = $infoj[0]['mid'];
		}

		// Mise à jour du statut du perdant
		$DB->query("UPDATE ibf_tournament_players_statut SET statut='1' WHERE tid='".$tid."' AND mid='".$loser."'");

		// update last playtime for both (new) opponents
		$DB->query("UPDATE ibf_tournament_players SET timeplayed=".time().", notified=0 WHERE tid=".$tid." AND rung=".($rung-1)." AND faceoff=".$nextfaceoff);

		$print->redirect_screen($ibforums->lang['operation_ok'], $ibforums->vars['base_url']."do=viewtourney&amp;tid=".$tid);
	}

	function disqual_tournoi($tid, $rung, $faceoff, $mid)
	{
		global $ibforums, $DB, $std, $GROUP, $print;

		$tid = intval($tid);
		$rung = intval($rung);
		$faceoff = intval($faceoff);
		$mid = intval($mid);

		// check permission to run this
        	$DB->query("SELECT m.is_arcade_mod AS is_mod, g.g_access_cp AS is_admin FROM ibf_members AS m LEFT JOIN ibf_groups AS g ON (m.mgroup = g.g_id) WHERE id=".$ibforums->member['id']." LIMIT 0, 1");
        	$userinfo = $DB->fetch_row();

		if ($userinfo['is_mod'] || $userinfo['is_admin'])
		{

		$DB->query("SELECT * FROM ibf_tournaments WHERE tid='$tid'");
		$infot = $DB->fetch_row();

		$cpt=0;
		// Recherche des infos sur les 2 joueurs
		$DB->query("SELECT * FROM ibf_tournament_players WHERE tid='$tid' AND rung='$rung' AND faceoff='$faceoff'");
		while($row = $DB->fetch_row())
		{
			$infoj[$cpt] = $row;
			$cpt++;
		}

		$nbtries = $infot['nbtries'];

		if($faceoff == 1 || $faceoff == 2) {
			$nextfaceoff=1;
		} else {
			$nextfaceoff=2;
		}

		// Cas où un champion doit être couronné

		if($rung == 1) {
			if($mid == $infoj[0]['mid']) {
				$winner = $infoj[1]['mid'];
			} else {
				$winner = $infoj[0]['mid'];
			}

			$DB->query("SELECT gname, gtitle from ibf_games_list WHERE gid='".$infot['gid']."' LIMIT 1");
			$row = $DB->fetch_row();
			$gname = $row['gname'];
			$gtitle = $row['gtitle'];

			$DB->query("SELECT name,id FROM ibf_members WHERE id='".$winner."' LIMIT 1");
			$name = $DB->fetch_row();
			$DB->query("UPDATE ibf_tournaments SET champion='".ibp_cleansql($name['name'])."' WHERE tid='$tid' LIMIT 1");

			// Mise à jour du statut de tous les participants (Statut = 3 => Tournoi terminé)
			$DB->query("UPDATE ibf_tournament_players_statut SET statut='3' WHERE tid='".$tid."' AND mid=".$winner);
			$DB->query("UPDATE ibf_tournament_players_statut SET statut='2' WHERE tid='".$tid."' AND mid=".$mid);
			$DB->query("UPDATE ibf_tournament_players SET timesplayed='$nbtries' WHERE tid='$tid' AND faceoff='$faceoff' AND rung='$rung'");

			$print->redirect_screen($ibforums->lang['operation_ok'], $ibforums->vars['base_url']."do=viewtourney&amp;tid=".$tid);
			return;

		}

		// Le tournoi n'est pas terminé, on fait juste avancer

		$loser = "";
		if($mid == $infoj[0]['mid']) {
                    	$db_string = $DB->compile_db_insert_string( array ( 'mid'  			=> $infoj[1]['mid'],
                                                               		'tid'     		=> $tid,
                                                               		'rung'    		=> ($rung-1),
	                                                                'rungscore'		=> 0,
                                                               		'faceoff'    	=> $nextfaceoff,
                                                                    	'timeplayed'	=> time(),
                                                                        'timesplayed'	=> 0,	
									'notified'	=> 0,) );
                    	$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
			$loser = $infoj[0]['mid'];

			// check if there is an opponent (to set back his timer)
			$check=$DB->query("SELECT mid FROM ibf_tournament_players WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($rung-1)." AND mid<>".$infoj[1]['mid']);
			if ($row = $DB->fetch_row($check))
			{
				$DB->query("UPDATE ibf_tournament_players SET timeplayed='".time()."', notified=0 WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($rung-1)." AND mid=".$row['mid']);
			}

		} else {
                    	$db_string = $DB->compile_db_insert_string( array ( 'mid'  			=> $infoj[0]['mid'],
                                                               		'tid'     		=> $tid,
                                                               		'rung'    		=> ($rung-1),
	                                                                'rungscore'		=> 0,
                                                               		'faceoff'    	=> $nextfaceoff,
                                                                    	'timeplayed'	=> time(),
                                                                        'timesplayed'	=> 0,	
									'notified'	=> 0,) );
                    	$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
			$loser = $infoj[1]['mid'];

			// check if there is an opponent (to set back his timer)
			$check=$DB->query("SELECT mid FROM ibf_tournament_players WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($rung-1)." AND mid<>".$infoj[0]['mid']);
			if ($row = $DB->fetch_row($check))
			{
				$DB->query("UPDATE ibf_tournament_players SET timeplayed='".time()."', notified=0 WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($rung-1)." AND mid=".$row['mid']);
			}
		}

		// Mise à jour du statut du perdant
		$DB->query("UPDATE ibf_tournament_players_statut SET statut='2' WHERE tid='".$tid."' AND mid='".$loser."'");
		$DB->query("UPDATE ibf_tournament_players SET timesplayed='$nbtries' WHERE tid='$tid' AND faceoff='$faceoff' AND rung='$rung'");

		}

		$print->redirect_screen($ibforums->lang['operation_ok'], $ibforums->vars['base_url']."do=viewtourney&amp;tid=".$tid);
	}

}

$main = new Arcade;

if( isset($ibforums->input['module']) )
{
        $file = MODULE_PATH."mod_".$ibforums->input['module'].".php";
        if( file_exists($file) )
    {
                require $file;
    }
    else
    {
            $main->show_games();
        $print->add_output($main->output);
        $print->do_output( array( 'TITLE' => $main->page_title, 'JS' => 0, NAV => $main->nav ) );
    }
}

function recursive_str_ireplace($replacethis,$withthis,$inthis)
{
	while (1==1)
	{
		$inthis = str_ireplace($replacethis,$withthis,$inthis);
		if(stristr($inthis, $replacethis) === FALSE)
		{
			RETURN $inthis;
		}
	}
	RETURN $inthis;
}

function ibp_cleansql($value) 
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

	// remove any SQL-commands
	$sqlcomm[] = 'create';
	$sqlcomm[] = 'database';
	$sqlcomm[] = 'table';
	$sqlcomm[] = 'insert';
	$sqlcomm[] = 'update';
	$sqlcomm[] = 'rename';
	$sqlcomm[] = 'replace';
	$sqlcomm[] = 'select';
	$sqlcomm[] = 'handler';
	$sqlcomm[] = 'delete';
	$sqlcomm[] = 'truncate';
	$sqlcomm[] = 'drop';
	$sqlcomm[] = 'where';
	$sqlcomm[] = 'or';
	$sqlcomm[] = 'and';
	$sqlcomm[] = 'values';
	$sqlcomm[] = 'set';
	$sqlcomm[] = 'password';
	$sqlcomm[] = 'salt';
	$sqlcomm[] = 'concat';
	$sqlcomm[] = 'schema';
	$value = recursive_str_ireplace($sqlcomm, '', $value);
 
	return $value; 
}

function ibp_cleanhtml($value)
{
	if ($value != strip_tags($value))
	{
		// seems to be HTML in the text...
		$search = array('@<script[^>]*?>.*?</script>@si',	// Strip out javascript
				'@<style[^>]*?>.*?</style>@siU',	// Strip style tags properly
				'@<[\/\!]*?[^<>]*?>@si',		// Strip out HTML tags
				'@<![\s\S]*?--[ \t\n\r]*>@'		// Strip multi-line comments including CDATA
				);
		$value = preg_replace($search, '', $value);
		$value = strip_tags($value);
	}
	return $value;
}

    function clean_key($key) {

    	if ($key == "")
    	{
    		return "";
    	}
    	$key = preg_replace( "/\.\./"           , ""  , $key );
    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );
    	return $key;
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

	$val = ibp_cleansql($val);

    	return $val;
    }

?>