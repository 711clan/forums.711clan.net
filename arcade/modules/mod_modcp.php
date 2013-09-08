<?php

class module
{
	var $html = "";
    var $links = array();
    var $actions = array();
    var $privs = array();

	function module()
    {
    	global $main, $ibforums, $DB, $std;

        $main->arcade->authorize(1);

        $this->privs = unserialize($main->arcade->user['arcade_mod_privs']);

        if( $main->arcade->user['is_admin'] )
        {
        	$this->privs['scores'] = 1;
            $this->privs['comments'] = 1;
            $this->privs['champs'] = 1;
            $this->privs['tourney'] = 1;
        }

        $this->mod_auth();

        if( isset($ibforums->input['notes']) && !isset($_GET['notes']) )
        {
        	$modnotes = trim($ibforums->input['notes']);
            $modnotes = str_replace("<br>","\n",$modnotes);
		$modnotes = clean_html($modnotes);
            $db_string = $DB->compile_db_update_string( array( 'mod_notes'		=>	$modnotes,  ) );
        	$DB->query("UPDATE ibf_games_settings SET ".$db_string);
            $extra = (isset($ibforums->input['do'])) ? "&amp;do=".$ibforums->input['do'] : "";
    		$std->boink_it($ibforums->base_url."act=Arcade&module=modcp".$extra);
        }

        if( isset($ibforums->input['del_score']) )
        {
        	$sid = intval($ibforums->input['del_score']);
            $DB->query("DELETE FROM ibf_games_scores WHERE s_id=".$sid);
            $this->champs(1);
            $std->boink_it($ibforums->base_url."act=Arcade&module=modcp");
        }

        if( isset($ibforums->input['edit']) )
        {
        	$this->edit_comment();
        }

        $this->actions = array( "splash"		=> $ibforums->lang['splash'],
        						"scores"		=> $ibforums->lang['scores_ctrl'],
                                "comments"		=> $ibforums->lang['comments_ctrl'],
        						"tourney"		=> $ibforums->lang['tourney'],
                                "champs"		=> $ibforums->lang['champs_ctrl'] );
        $header = (isset($ibforums->input['do'])) ? $this->actions[$ibforums->input['do']] : $ibforums->lang['splash'];

        switch( $ibforums->input['do'] )
        {
        	case scores:
            	$content = $this->scores();
                break;
            case comments:
            	$content = $this->comments();
                break;
            case multi_del:
            	$this->multi_del();
                break;
            case champs:
            	$content = $this->champs();
                break;
            case tourney:
            	$content = $this->tourney();
                break;
        	default:
            	$content = $this->splash();
            	break;
        }


        $modnotes = $main->arcade->settings['mod_notes'];

        $this->get_links();
        $modnotes_extra = "";
        if( isset($ibforums->input['do']) )
        {
        	$modnotes_extra = "<input type='hidden' name='do' value='".$ibforums->input['do']."' />";
        }

        $main->arcade->top_links($main->html);
        $this->html .= $main->arcade->extra_links;

        $this->html .= $main->html->mod_main($header, $this->links, $content, $modnotes, $modnotes_extra);

        $main->arcade->get_active($main->html);
        $this->html .= $main->arcade->active;
        $this->html .= $main->html->copyright($main->version,$ibforums->lang['timeformat1'],$main->BFL);
        $main->arcade->print_it($this->html , $ibforums->lang['arcade_cp_link'] , $ibforums->lang['arcade_cp_link']);
    }

    function mod_auth()
    {
    	global $std, $ibforums, $main;

        if( isset($ibforums->input['del_score']) || $ibforums->input['do'] == "scores" || $ibforums->input['do'] == "multi_del" )
        {
        	if( !$this->privs['scores'] )
            {
            	$std->boink_it($ibforums->base_url."act=Arcade&module=modcp");
            }
        }

        if( isset($ibforums->input['edit']) || $ibforums->input['do'] == "comments" )
        {
            if( !$this->privs['comments'] )
            {
            	$std->boink_it($ibforums->base_url."act=Arcade&module=modcp");
            }
        }

        if( $ibforums->input['do'] == "champs" )
        {
            if( !$this->privs['champs'] )
            {
            	$std->boink_it($ibforums->base_url."act=Arcade&module=modcp");
            }
        }

        if( $ibforums->input['do'] == "tourney" )
        {
            if( !$this->privs['tourney'] )
            {
            	$std->boink_it($ibforums->base_url."act=Arcade&module=modcp");
            }
        }
    }

    function splash()
    {
    	global $main, $ibforums, $DB, $std, $vboptions, $vbulletin;

        $scores 	= "";
        $comments 	= "";
        $games 		= array();
        $colspan 	= array();

        $colspans = $this->privs['scores'] ? 5 : 4;
        $colspanc = $this->privs['comments']  ? 5 : 4;

        $DB->query("SELECT gid, gtitle FROM ibf_games_list ORDER BY gid");
        while( $this_game = $DB->fetch_row() )
        {
        	$games[$this_game['gid']] = $this_game['gtitle'];
        }

        $DB->query("SELECT * FROM ibf_games_scores ORDER BY datescored DESC LIMIT 0,5");
        while( $this_score = $DB->fetch_row() )
        {
        	$this_score['score'] = $main->arcade->t3h_format($this_score['score']);
        	$this_score['datescored'] = $this->convert_date($this_score['datescored']);
        	$this_score['game'] = $games[$this_score['gid']];
            	$this_score['remove'] = ($this->privs['scores']) ? "<td align='center' class='alt1'><a href='".$ibforums->vars['base_url']."act=Arcade&amp;module=modcp&amp;del_score=".$this_score['s_id']."'>".$ibforums->lang['remove']."</a></td>" : "";
        	$scores .= $main->html->score_row($this_score);
        }

        $champs = "";
        $DB->query("SELECT * FROM ibf_games_champs ORDER BY champ_date DESC LIMIT 0, 5");
        while( $this_champ = $DB->fetch_row() )
        {
        	$this_champ['champ_score'] = $main->arcade->t3h_format($this_champ['champ_score']);
        	$this_champ['champ_date'] = $this->convert_date($this_champ['champ_date']);
        	$champs .= $main->html->champ_row($this_champ);
        }

        $DB->query("SELECT * FROM ibf_games_scores WHERE trim(comment)<>'' ORDER BY datescored DESC LIMIT 0,5");
        while( $this_comment = $DB->fetch_row() )
        {
        	$this_comment['datescored'] = $this->convert_date($this_comment['datescored']);
            $this_comment['comment'] = array( 'TEXT' => $this_comment['comment'], 'SMILIES' => 1, 'CODE' => 1, 'SIGNATURE' => 0, 'HTML' => 0);
			//$this_comment['comment'] = $main->parser->convert($this_comment['comment']);
		$this_comment['comment'] = $this_comment['comment']['TEXT'];

	// parse the comment
	$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
	if ($vbversion == "3.0")
	{
		require_once('./includes/functions_bbcodeparse.php');		
		$parsed_comment = parse_bbcode($this_comment['comment']);
	}
	else
	{
		require_once('./includes/class_bbcode.php');
		$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
		$parsed_comment = $bbcode_parser->parse($this_comment['comment'],0,1);
	}
	$this_comment['comment'] = $parsed_comment;

        	$this_comment['game'] = $games[$this_comment['gid']];
            $this_comment['edit'] = ($this->privs['comments']) ? $main->html->edit_link($this_comment['s_id']) : "";
        	$comments .= $main->html->comment_row($this_comment);
        }

        $remove = ($this->privs['scores']) ? "<td align='center' class='alt1'> &nbsp; </td>" : "";
        $edit = ($this->privs['comments']) ? "<td align='center' class='alt1'> &nbsp; </td>" : "";

	$versioninfo = $main->version;
	/*
	// Version-detection by MrZeropage ** do not remove !! **
	$thisversion = $main->version;
	$updatefile = $main->updatecheck;

	if (file_exists($updatefile))
	{
		include($updatefile);
	}
	else
	{
		$actversion="null";
	}
		
	if (($thisversion != $actversion) && ($actversion != "null"))
	{
		// there is an update available
		$link1=""; $link2="";
		if ($main->arcade->user['is_admin'])
		{
			if ($main->arcade->settings['arcade_language'] == "de")
			{
				$actlink=$actlink_de;
			}
			else
			{
				$actlink=$actlink_en;
			}
		}
		$versioninfo = $thisversion."<div class='smallfont'>".$link1.$ibforums->lang['verlink1']."<b><span style='color: red;'>".$actversion."</span></b>".$ibforums->lang['verlink2'].$link2."</div>";
	}
	else
	{
		// this version is up to date
		$addinfo="";
		if ($actversion == "null")
		{
			$addinfo="<div class='smallfont'><i><b>".$ibforums->lang['verlink0']."</b></i></div>";
		}
		$versioninfo = $thisversion.$addinfo;
	}
	*/

        $return = $main->html->splash($scores , $comments , $champs , $colspans , $colspanc , $remove, $edit, $versioninfo);

        return $return;
    }

    function convert_date($timestamp)
    {
    	global $ibforums, $std;

        $std->time_options['ARCADE'] = "{$ibforums->lang['timeformat4']}";

        $the_date = $std->get_date($timestamp , "ARCADE");
        if( $the_date == date("{$ibforums->lang['timeformat4']}") )
        {
			$the_date = $ibforums->lang['today'];
		}
        else
        {
			if ($ibforums->lang[timeformat1] == "de")
			{
			$yesterday = date("{$ibforums->lang['timeformat4']}", mktime(0, 0, 0, date("d")  , date("m")-1, date("Y")));
			}
			else
			{
			$yesterday = date("{$ibforums->lang['timeformat4']}", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
			}
			if( $the_date == $yesterday )
            {
				$the_date = $ibforums->lang['yesterday'];
			}
		}

        return $the_date;
    }

    function scores()
    {
    	global $ibforums, $main, $DB;

        if( !isset($ibforums->input['is_submitted']) )
        {
            $game_select .= "<option value='0' selected='selected'>".$ibforums->lang['all_games']."</option>";
            $cat = "";
        	$DB->query("SELECT g.gid, g.gtitle, c.cat_name
            			FROM ibf_games_list AS g
                        LEFT JOIN ibf_games_cats AS c
                        ON (g.gcat = c.c_id)
                        ORDER BY g.gtitle");
            while( $game = $DB->fetch_row() )
            {
            	if( $game['cat_name'] != $cat && $main->arcade->settings['use_cats'] == 1 )
                {
                    if( preg_match("/optgroup/i", $game_select) )
                    {
                		$game_select .= "</optgroup>";
                    }
                	$game_select .= "<optgroup label='".$game['cat_name']."'>";
                    $cat = $game['cat_name'];
                }
                $game_select .= "<option value='".$game['gid']."'>".$game['gtitle']."</option>";
            }

            $extra = $main->html->score_extra();

            $ibforums->lang['time_frame'] = str_replace("!R!" , $ibforums->lang['score_link'] , $ibforums->lang['time_frame']);
            $ibforums->lang['search_for'] = str_replace("!R!" , $ibforums->lang['score_link'] , $ibforums->lang['search_for']);

        	$return = $main->html->search_form("scores",$game_select,$extra);
        }
        else
        {
            $DB->query("SELECT gid, gtitle FROM ibf_games_list ORDER BY gid");
        	while( $this_game = $DB->fetch_row() )
        	{
        		$games[$this_game['gid']] = $this_game['gtitle'];
        	}

        	$the_query = $this->make_search_query("s");
            $DB->query($the_query);
            if( $DB->get_num_rows() )
            {
            	while( $this_score = $DB->fetch_row() )
        		{
        			$this_score['datescored'] = $this->convert_date($this_score['datescored']);
        			$this_score['game'] = $games[$this_score['gid']];
                    $this_score['score'] = $main->arcade->t3h_format($this_score['score']);
        			$scores .= $main->html->score_row_result($this_score);
        		}
            }
            $return .= $main->html->score_results($scores);
        }

        return $return;
    }

    function multi_del()
    {
    	global $ibforums, $DB, $print;

        $score_string = "(0)";
        if( !empty($ibforums->input['scores']) )
        {
        	$score_string = "(".implode("," , $ibforums->input['scores']).")";
        }

        $DB->query("DELETE FROM ibf_games_scores WHERE s_id IN ".$score_string);
        $this->champs(1);
        $print->redirect_screen( $ibforums->lang['scores_deleted'], $ibforums->vars['base_url']."act=Arcade&amp;module=modcp" );
    }

    function comments()
    {
    	global $ibforums, $main, $DB;

        if( !isset($ibforums->input['is_submitted']) )
        {
            $game_select .= "<option value='0' selected='selected'>".$ibforums->lang['all_games']."</option>";
            $cat = "";
        	$DB->query("SELECT g.gid, g.gtitle, c.cat_name
            			FROM ibf_games_list AS g
                        LEFT JOIN ibf_games_cats AS c
                        ON (g.gcat = c.c_id)
                        ORDER BY g.gtitle");
            while( $game = $DB->fetch_row() )
            {
            	if( $game['cat_name'] != $cat && $main->arcade->settings['use_cats'] == 1 )
                {
                    if( preg_match("/optgroup/i", $game_select) )
                    {
                		$game_select .= "</optgroup>";
                    }
                	$game_select .= "<optgroup label='".$game['cat_name']."'>";
                    $cat = $game['cat_name'];
                }
                $game_select .= "<option value='".$game['gid']."'>".$game['gtitle']."</option>";
            }

            $ibforums->lang['time_frame'] = str_replace("!R!" , $ibforums->lang['comment_link'] , $ibforums->lang['time_frame']);
            $ibforums->lang['search_for'] = str_replace("!R!" , $ibforums->lang['comment_link'] , $ibforums->lang['search_for']);

            $extra = $main->html->comment_extra();

        	$return = $main->html->search_form("comments",$game_select,$extra);
        }
		else
        {
            $DB->query("SELECT gid, gtitle FROM ibf_games_list ORDER BY gid");
        	while( $this_game = $DB->fetch_row() )
        	{
        		$games[$this_game['gid']] = $this_game['gtitle'];
        	}

            $the_query = $this->make_search_query("c");
            $DB->query($the_query);
            if( $DB->get_num_rows() )
            {
            	while( $this_comment = $DB->fetch_row() )
        		{
        			$this_comment['datescored'] = $this->convert_date($this_comment['datescored']);
                    $this_comment['comment'] = array( 'TEXT' => $this_comment['comment'], 'SMILIES' => 1, 'CODE' => 1, 'SIGNATURE' => 0, 'HTML' => 0);
					//$this_comment['comment'] = $main->parser->convert($this_comment['comment']);
				$this_comment['comment'] = $this_comment['comment']['TEXT'];
        			$this_comment['game'] = $games[$this_comment['gid']];
                    $this_comment['edit'] = $main->html->edit_link($this_comment['s_id']);
        			$comments .= $main->html->comment_row_result($this_comment);
        		}
            }
            $return .= $main->html->comment_results($comments);
        }

        return $return;
    }

    function make_search_query($type)
    {
    	global $ibforums;

        $query = "";

        $name = trim($ibforums->input['username']);
        if( !empty($name) )
        {
        	$query .= "WHERE name LIKE '%".$name."%'";
        }

        $a = 0;
        $all_games = 0;
        foreach($ibforums->input['in_game'] as $k=>$v)
        {
        	if( $v == 0 )
            {
            	$all_games = 1;
                break;
            }
            if( !($a) )
            {
            	$query .= (!empty($query)) ? " AND " : "WHERE ";
                $query .= "gid IN (";
            }
            $query .= ($a) ? "," : "";
            $query .= $v;
            $a++;
        }
        $query .= (!$all_games) ? ")" : "";

        if( $type == "s" )
        {
        	$ibforums->input['score_minmax'] = trim($ibforums->input['score_minmax']);
            if(	!empty($ibforums->input['score_minmax']) )
            {
            	$ibforums->input['score_minmax'] = intval($ibforums->input['score_minmax']);
            	$query .= (!empty($query)) ? " AND " : "WHERE ";
                $op = $ibforums->input['greater_less'];
            	$op = str_replace("&lt;","<",$op);
            	$op = str_replace("&gt;",">",$op);
                $query .= "score".$op.$ibforums->input['score_minmax'];
            }
        }
        else
        {
        	$ibforums->input['comment_length'] = trim($ibforums->input['comment_length']);
            if( !empty($ibforums->input['comment_length']) )
            {
            	$ibforums->input['comment_length'] = intval($ibforums->input['comment_length']);
            	$query .= (!empty($query)) ? " AND " : "WHERE ";
                $query .= "CHAR_LENGTH(comment)>".$ibforums->input['comment_length'];
            }
            $query .= (!empty($query)) ? " AND trim(comment)<>''" : " WHERE trim(comment)<>''";
        }

        $ibforums->input['age_num'] = trim($ibforums->input['age_num']);
        if( !empty($ibforums->input['age_num']) )
        {
        	$age = intval($ibforums->input['age_num']);
            $op = $ibforums->input['age_old_new'];
            $op = str_replace("&lt;","<",$op);
            $op = str_replace("&gt;",">",$op);
            $hdm = $ibforums->input['age_hdm'];
            $time = time()-($age*$hdm);

            $query .= (!empty($query)) ? " AND " : "WHERE ";
            $query .= " datescored".$op.$time;
        }

        $limit = 100;
        $ibforums->input['limit'] = trim($ibforums->input['limit']);
        if( !empty($ibforums->input['limit']) )
        {
        	$limit = $ibforums->input['limit'];
            if( !is_numeric($limit) || ($limit > 100) )
            {
            	$limit = 100;
            }
        }
        $query_limit = " LIMIT 0, ".$limit;

        if( !empty($query) )
        {
			$query .= " ORDER BY s_id".$query_limit;
        }
        else
        {
        	$query .= " LIMIT 0, ".$limit;
        }

        $return = "SELECT * FROM ibf_games_scores ".$query;
        return $return;
    }

    function get_links()
    {
    	global $main, $ibforums;

        $this->links['home'] = "&middot;<a href='{$ibforums->base_url}act=Arcade&amp;module=modcp'>{$ibforums->lang['home']}</a>";

        if( $this->privs['scores'] )
        {
        	$this->links['scores'] = "<br />&middot;<a href='{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=scores'>{$ibforums->lang['score_link']}</a>";
        }

        if( $this->privs['comments'] )
        {
        	$this->links['comments'] = "<br />&middot;<a href='{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=comments'>{$ibforums->lang['comment_link']}</a>";
        }

        if( $this->privs['tourney'] )
        {
        	$this->links['tourney'] = "<br />&middot;<a href='{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=tourney'>{$ibforums->lang['tournament']}</a>";
        }

        if( $this->privs['champs'] )
        {
        	$this->links['champs'] = "<br />&middot;<a href='{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=champs'>{$ibforums->lang['champs_link']}</a>";
    	}
    }

    function edit_comment()
    {
    	global $ibforums, $DB, $main;

    	$sid = intval($ibforums->input['edit']);
        if( !isset($ibforums->input['comment']) )
        {
        	$DB->query("SELECT comment FROM ibf_games_scores WHERE s_id=".$sid);
            if( $DB->get_num_rows() )
            {
            	$c = $DB->fetch_row();
                $comment = "value=\"".$c['comment']."\"";
                $button = "";
            }
            else
            {
            	$comment = "disabled='disabled' value='Invalid Id'";
            	$button = "disabled='disabled'";
            }
            $this->html .= $main->html->edit_comment($sid , $comment , $button);
            $this->html .= $main->html->copyright($main->version,$ibforums->lang['timeformat1']);
            $main->arcade->print_it($this->html , "Arcade ModCP" , "" , 1);
        }
        else
        {
        	$comment = trim($ibforums->input['comment']);
		$comment = clean_html($comment);
        	$db_string = $DB->compile_db_update_string( array( 'comment'		=>	$comment,  ) );
        	$DB->query("UPDATE ibf_games_scores SET ".$db_string." WHERE s_id=".$sid);
    		print $main->html->close_win();
        }
        exit();
    }

    function champs($auto_run = 0)
    {
    	global $main, $DB, $ibforums, $print;

        if( !isset($ibforums->input['is_submitted']) && !$auto_run )
        {
        	$game_select .= "<option value='0' selected='selected'>".$ibforums->lang['all_games']."</option>";
        	$cat = "";
        	$DB->query("SELECT g.gid, g.gtitle, c.cat_name
        				FROM ibf_games_list AS g
                    	LEFT JOIN ibf_games_cats AS c
                    	ON (g.gcat = c.c_id)
                    	ORDER BY g.gtitle");
        	while( $game = $DB->fetch_row() )
        	{
        		if( $game['cat_name'] != $cat && $main->arcade->settings['use_cats'] == 1 )
            	{
            		if( preg_match("/optgroup/i", $game_select) )
                	{
                		$game_select .= "</optgroup>";
                	}
                	$game_select .= "<optgroup label='".$game['cat_name']."'>";
                	$cat = $game['cat_name'];
            	}
            	$game_select .= "<option value='".$game['gid']."'>".$game['gtitle']."</option>";
        	}

        	$return = $main->html->champs_update($game_select);

        	return $return;
        }
        else
        {
        	$game_string = "";
        	if( !$auto_run )
            {
            	if( !in_array( 0 , $ibforums->input['in_game'] ) )
            	{
            		$game_string = "WHERE gid IN (".implode("," , $ibforums->input['in_game']).")";
            	}
            }

            $the_champs = array();

            if( $game_string == "" )
            {
            	$DB->query("DELETE FROM ibf_games_champs");
            }

			$game_query = $DB->query("SELECT gid, highscore_type, gtitle FROM ibf_games_list ".$game_string." ORDER by gtitle");
			while( $game = $DB->fetch_row($game_query) )
			{
        		$order = ($game['highscore_type'] == "high") ? "DESC" : "ASC";

    			$DB->query("SELECT s.mid, s.gid, s.name, s.datescored, s.score, g.gtitle
    						FROM ibf_games_scores AS s, ibf_games_list AS g
                			WHERE s.gid=g.gid AND s.gid=".$game['gid']."
                			ORDER BY score ".$order.", datescored ASC");
            	if( $DB->get_num_rows() )
            	{
    				$champ = $DB->fetch_row();
    				$the_champs[] = array(	'gid'		=>	$champ['gid'],
    										'gtitle'	=>	$champ['gtitle'],
                            				'mid'		=>	$champ['mid'],
                            				'name'      =>	$champ['name'],
                            				'date'		=>	$champ['datescored'],
                            				'score'		=>	$champ['score'] );
            	}
			}

			foreach( $the_champs as $this_champ )
			{
            	if( $game_string != "" )
                {
                    $db_string = $DB->compile_db_update_string( array ( 'champ_gid'     => $this_champ['gid'],
                                                                 		'champ_gtitle'  => $this_champ['gtitle'],
                                                                 		'champ_mid'     => $this_champ['mid'],
                                                                 		'champ_name'    => $this_champ['name'],
                                                                 		'champ_date'    => $this_champ['date'],
                                                                    	'champ_score'	=> $this_champ['score'] ) );
					$DB->query("UPDATE ibf_games_champs SET ".$db_string." WHERE champ_gid=".$this_champ['gid']);
                }
                else
                {
        			$db_string = $DB->compile_db_insert_string( array ( 'champ_gid'     => $this_champ['gid'],
                                                                		'champ_gtitle'  => $this_champ['gtitle'],
                                                                		'champ_mid'     => $this_champ['mid'],
                                                                		'champ_name'    => $this_champ['name'],
                                                                		'champ_date'    => $this_champ['date'],
                                                                		'champ_score'	=> $this_champ['score'] ) );
					$DB->query("INSERT INTO ibf_games_champs (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
            	}
            }

            if( !$auto_run )
            {
            	$print->redirect_screen( $ibforums->lang['champs_updated'], $ibforums->vars['base_url']."act=Arcade&amp;module=modcp" );
            }
        }
    }

    function tourney()
    {
    	global $ibforums, $main, $DB, $print, $vboptions, $vbulletin;

        switch( $ibforums->input['code'] )
        {
        	case 'newt':

                $DB->query("SELECT * FROM ibf_games_list WHERE gid=".$ibforums->input['the_game']);
                $GAME = $DB->fetch_row();

                $counter = 1;

                $t_header = $ibforums->lang['new_g_t'];
                $t_header = preg_replace( "/<% GAME %>/i" , $GAME['gtitle'] , $t_header);

                $hiddens = array( 'player_amount'	=>	$ibforums->input['player_amount'],
                                  'gid'				=>	$ibforums->input['the_game'],
				  'tries'		=> $ibforums->input['tries']  );

                for( $counter = 1 ; $counter <= $ibforums->input['player_amount'] ; $counter++ )
                {
                	$rows .= $main->html->new_tourney_id_row($counter);
                }

                $return .= $main->html->new_tourney_id($t_header , $rows , $hiddens);
            break;
            case 'add_t_confirm':
                if( $ibforums->input['users'] == "" )
                {
                	$ibforums->input['users'] = array();
                }

		$dbstring="";
                $i = 0;
                foreach($ibforums->input['users'] as $value)
                {
                	$user = intval($value);
                    if($user == 0)
                    {
                        $return .= $main->html->error($ibforums->lang['id_greater_zero']);
                        return $return;
                    }
                    $dbstring .= ($i) ? ", ".$user : $user;
                    $i++;
                }

		if ($i > 0)
		{
	                $DB->query("SELECT id, name FROM ibf_members WHERE id IN(".$dbstring.") ORDER BY id");
		}
		else
		{
        		$return .= $main->html->tournament_listing();

        		$DB->query("SELECT t.*, g.gtitle FROM ibf_tournaments AS t, ibf_games_list AS g WHERE (g.gid = t.gid) AND champion=''");
        		while( $tourney = $DB->fetch_row() )
        		{
        			$tourney['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$tourney['tid']."'>".$ibforums->lang['view_users']."</a><br /><br />
                    					<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=del&amp;tid=".$tourney['tid']."'>".$ibforums->lang['remove']."</a>";
            		$tourney['datestarted'] = $this->convert_date($tourney['datestarted']);

            		$return .= $main->html->tournament_row($tourney);
        		}

        		$return .= $main->html->tournament_middle();

        		$DB->query("SELECT t.*, g.gtitle FROM ibf_tournaments AS t, ibf_games_list AS g WHERE (g.gid = t.gid) AND champion<>''");
        		while( $tourney = $DB->fetch_row() )
        		{
        			$tourney['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$tourney['tid']."'>".$ibforums->lang['view_users']."</a><br /><br />
                    					<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=del&amp;tid=".$tourney['tid']."'>".$ibforums->lang['remove']."</a>";
            		$tourney['datestarted'] = $this->convert_date($tourney['datestarted']);

            		$return .= $main->html->tournament_row($tourney);
        		}

        		$return .= $main->html->stop("&nbsp;");

	            	$cat = "";
        		$DB->query("SELECT g.gid, g.gtitle, c.cat_name, c.pos
            				FROM ibf_games_list AS g
                        	LEFT JOIN ibf_games_cats AS c
                        	ON (g.gcat = c.c_id)
                        	ORDER BY g.gcat, g.gtitle");
	            	while( $game = $DB->fetch_row() )
	            	{
	            		if( $game['cat_name'] != $cat && $main->arcade->settings['use_cats'] == 1 )
	                	{
	                    	if( preg_match("/optgroup/i", $game_select) )
	                    	{
	                			$game_select .= "</optgroup>";
	                    	}
	                		$game_select .= "<optgroup label='".$game['cat_name']."'>";
	                    	$cat = $game['cat_name'];
                		}
        	        	$game_select .= "<option value='".$game['gid']."'>".$game['gtitle']."</option>";
	            	}

	                $return .= $main->html->new_tourney($game_select);
			break;
                }

                if($i != $DB->get_num_rows() )
                {
                     $return .= $main->html->error($ibforums->lang['none_found']);
                     return $return;
                }
                else
                {
                    $hiddens = array( 'player_amount'	=>	$ibforums->input['player_amount'],
                                      'gid'				=>	$ibforums->input['gid'],
					'tries'		=> $ibforums->input['tries'],
					'pmnotify'	=> '1');

                    $drop_list = array();
                    $counter = intval($ibforums->input['player_amount'])/2;
                    $drop_list = "<option value='0'>".$ibforums->lang['acp_newtourney_select']."</option>";
                    for( $i = 1 ; $i <= $counter ; $i++ )
                    {
                		$drop_list .= "<option value='".$i."'>".$ibforums->lang['t_match'].$i."</option>";
                    }
                    while( $USER = $DB->fetch_row() )
                    {
                        $users .= $main->html->new_tourney_users_name( $USER , $drop_list );
                    }

		// here you may add a SELECT-line for sending PM-Notify

                    $return .= $main->html->new_tourney_users($users , $hiddens);
                }
            break;
            case 'do_add_t':
                $game = array();
		$pmnotify = intval($ibforums->input['pmnotify']);

                if( $ibforums->input['player_amount'] == '2' )
                {
                	$rung = 1;
                }
                elseif( $ibforums->input['player_amount'] == '4' )
                {
                	$rung = 2;
                }
                else
                {
                	$rung = 3;
                }

			$tries = intval($ibforums->input['tries']);
			if (($tries<1) || ($tries>5))
			{
				// seems to be illegal value, so set it to default
				$tries = 3;
			}

                if( $ibforums->input['player_amount'] != 2 )
                {
                	$counter = intval($ibforums->input['player_amount'])/2;
                    for( $i = 1 ; $i <= $counter ; $i++ )
                    {
                    	$game[$i] = 0;
                    }
                }
                $match = array();
                foreach( $ibforums->input as $key=>$value )
                {
                	if( preg_match("#_user#", $key) )
                    {
                    	if( $value == 0 )
                        {
                            $return .= $main->html->error($ibforums->lang['all_user_match']);
                     		return $return;
                        }
                        $game[$value]++;
                        if( $game[$value] > 2 )
                        {
                            $return .= $main->html->error($ibforums->lang['two_per_match']);
                     		return $return;
                        }
                    }
                    $key = intval($key);
                    $match[$key] = $value;
                }

		$DB->query("SELECT id, name FROM ibf_members WHERE id=".$ibforums->member['id']);
		$userinfo = $DB->fetch_row();
		$username = $userinfo['name'];

                $db_string = $DB->compile_db_insert_string( array ( 'gid'               => $ibforums->input['gid'],
                                                                    'numplayers'        => $ibforums->input['player_amount'],
                                                                    'datestarted'       => time(),
								    'demare'		=> 1,
								    'creat'		=> $username,
								    'plibre'		=> 0,
								    'nbtries'		=> $tries,
								    'cost'		=> 0,
								    'champion'		=> ''
                                                            ) );
                $DB->query("INSERT INTO ibf_tournaments
                                   (" .$db_string['FIELD_NAMES']. ") VALUES
                                   (". $db_string['FIELD_VALUES'] .")");
                $tid = $DB->get_insert_id();
                unset($key);
                unset($value);

                foreach( $match as $key=>$value )
                {
                	if( $key != 0 )
                    {
                    	$db_string = $DB->compile_db_insert_string( array ( 'mid'               =>  $key,
                                                                            'tid'               =>  $tid,
                                                                        	'rung'              =>  $rung,
                                                                            'rungscore'         =>  0,
                                                                            'faceoff'           =>  $value,
                                                                        	'timeplayed'        =>  time(),
                                                                        	'timesplayed'   =>  0,
										'notified'	=> 0,
                                                            ) );

                        $DB->query("INSERT INTO ibf_tournament_players
                                           (" .$db_string['FIELD_NAMES']. ") VALUES
                                           (". $db_string['FIELD_VALUES'] .")");

                			$db_string = $DB->compile_db_insert_string( array ( 	'tid'			=> $tid,
                                                                    				'mid'			=> $key,
                                                                    				'statut'		=> 0,	) ) ;

					$DB->query("INSERT INTO ibf_tournament_players_statut (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

					if ($pmnotify == 1)
					{
						// PM-Notification to Tournament-Participants by MrZeropage
						$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
						if ($vbversion == "3.0")
						{
							$forumlink = $vboptions['bburl']."/";
						}
						else
						{
							$forumlink = $vbulletin->options['bburl']."/";
						}

						$DB->query("SELECT id, name, email, arcade_pmactive FROM ibf_members WHERE id=".$key);
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
								$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".$sendername."', '".$title."', '" . addslashes($message) . "',  '" . $recipient . "', 0, " . TIMENOW . ", 0, 0)");
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
                $print->redirect_screen( $ibforums->lang['tourney_done'], $ibforums->vars['base_url']."act=Arcade&amp;module=modcp&amp;do=tourney" );
            break;
        	case 'view':
            	$tid = $ibforums->input['tid'];

                $DB->query("SELECT p.*, t.champion, m.name FROM ibf_tournament_players AS p, ibf_tournaments AS t, ibf_members AS m WHERE (p.tid = t.tid) AND (p.mid = m.id) AND p.tid=".$tid);
                while( $user = $DB->fetch_row() )
                {
                	$players .= $main->html->t_player_row($user);
                }

                $return .= $main->html->touney_players($players , $tid);
            break;
            case replace:
            	if( !isset($ibforums->input['confirmed']) )
                {
			if ($ibforums->input['replaceid']=="")
			{
				// view tournament page
	        		$return .= $main->html->tournament_listing();

	        		$DB->query("SELECT t.*, g.gtitle FROM ibf_tournaments AS t, ibf_games_list AS g WHERE (g.gid = t.gid) AND champion=''");
	        		while( $tourney = $DB->fetch_row() )
	        		{
        				$tourney['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$tourney['tid']."'>".$ibforums->lang['view_users']."</a><br /><br />
                	    					<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=del&amp;tid=".$tourney['tid']."'>".$ibforums->lang['remove']."</a>";
		            		$tourney['datestarted'] = $this->convert_date($tourney['datestarted']);

		            		$return .= $main->html->tournament_row($tourney);
        			}

	        		$return .= $main->html->tournament_middle();

	        		$DB->query("SELECT t.*, g.gtitle FROM ibf_tournaments AS t, ibf_games_list AS g WHERE (g.gid = t.gid) AND champion<>''");
        			while( $tourney = $DB->fetch_row() )
        			{
        				$tourney['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$tourney['tid']."'>".$ibforums->lang['view_users']."</a><br /><br />
                    						<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=del&amp;tid=".$tourney['tid']."'>".$ibforums->lang['remove']."</a>";
		            		$tourney['datestarted'] = $this->convert_date($tourney['datestarted']);

		            		$return .= $main->html->tournament_row($tourney);
        			}

	        		$return .= $main->html->stop("&nbsp;");

		            	$cat = "";
        			$DB->query("SELECT g.gid, g.gtitle, c.cat_name
            					FROM ibf_games_list AS g
                        			LEFT JOIN ibf_games_cats AS c
		                        	ON (g.gcat = c.c_id)
                		        	ORDER BY g.gtitle");
		            	while( $game = $DB->fetch_row() )
		            	{
		            		if( $game['cat_name'] != $cat && $main->arcade->settings['use_cats'] == 1 )
		                	{
			                    	if( preg_match("/optgroup/i", $game_select) )
			                    	{
		                			$game_select .= "</optgroup>";
			                    	}
		                		$game_select .= "<optgroup label='".$game['cat_name']."'>";
			                    	$cat = $game['cat_name'];
		                	}
		                	$game_select .= "<option value='".$game['gid']."'>".$game['gtitle']."</option>";
		            	}

		                $return .= $main->html->new_tourney($game_select);
			}
			else
			{
	                	if( !is_numeric($ibforums->input['replaceid']) || $ibforums->input['replaceid'] <= 0 )
        	        	{
                			$return .= $main->html->error($ibforums->lang['no_mem_id']);
                		}
	                	if( $ibforums->input['replaceid'] == $ibforums->input['userid'] )
        	        	{
                			$return .= $main->html->error($ibforums->lang['same_user']);
                		}
	                	$this_query = $DB->query("SELECT id, name FROM ibf_members WHERE id=".$ibforums->input['replaceid']);
        	        	if( $DB->get_num_rows($this_query) )
                		{
            				$DB->query("SELECT * FROM ibf_tournament_players WHERE mid=".$ibforums->input['replaceid']." AND tid=".$ibforums->input['tid']);
	        	            	if( $DB->get_num_rows() )
        	        	    	{
	        	            		$return .= $main->html->error($ibforums->lang['allready_in_t']);
        	        	        	return $return;
		                    	}
		                    	$USER = $DB->fetch_row($this_query);

		                    	$hiddens = array(  	'tid'		=>	$ibforums->input['tid'],
		                                       		'oldmid' 	=>  $ibforums->input['userid'],
		                                       		'newmid' 	=> 	$ibforums->input['replaceid']  );

		                    	$text = $ibforums->lang['replace_text'];
		                    	$text = preg_replace("/<% NAME1 %>/i" , $ibforums->input['username'] , $text);
		                    	$text = preg_replace("/<% NAME2 %>/i" , $USER['name'] , $text);

		                    	$return .= $main->html->confirm_replace($text , $hiddens);
	                	}
        	        	else
                		{
                			$return .= $main->html->error($ibforums->lang['no_found_id']);
	                	}
			}
                }
                else
                {
                	$DB->query("UPDATE ibf_tournament_players SET mid=".$ibforums->input['newmid']." WHERE tid=".$ibforums->input['tid']." AND mid=".$ibforums->input['oldmid']);
                	$DB->query("UPDATE ibf_tournament_players_statut SET mid=".$ibforums->input['newmid']." WHERE tid=".$ibforums->input['tid']." AND mid=".$ibforums->input['oldmid']);
                	$print->redirect_screen( $ibforums->lang['user_is_r'], $ibforums->vars['base_url']."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$ibforums->input['tid'] );
                }
            break;
            case 'del':
                $DB->query("DELETE FROM ibf_tournaments WHERE tid=".$ibforums->input['tid']);
                $DB->query("DELETE FROM ibf_tournament_players WHERE tid=".$ibforums->input['tid']);
                $print->redirect_screen( $ibforums->lang['tourney_del'], $ibforums->vars['base_url']."act=Arcade&amp;module=modcp&amp;do=tourney" );
            break;
        	default:
        		$return .= $main->html->tournament_listing();

        		$DB->query("SELECT t.*, g.gtitle FROM ibf_tournaments AS t, ibf_games_list AS g WHERE (g.gid = t.gid) AND champion=''");
        		while( $tourney = $DB->fetch_row() )
        		{
        			$tourney['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$tourney['tid']."'>".$ibforums->lang['view_users']."</a><br /><br />
                    					<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=del&amp;tid=".$tourney['tid']."'>".$ibforums->lang['remove']."</a>";
            		$tourney['datestarted'] = $this->convert_date($tourney['datestarted']);

            		$return .= $main->html->tournament_row($tourney);
        		}

        		$return .= $main->html->tournament_middle();

        		$DB->query("SELECT t.*, g.gtitle FROM ibf_tournaments AS t, ibf_games_list AS g WHERE (g.gid = t.gid) AND champion<>''");
        		while( $tourney = $DB->fetch_row() )
        		{
        			$tourney['link'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=view&amp;tid=".$tourney['tid']."'>".$ibforums->lang['view_users']."</a><br /><br />
                    					<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=del&amp;tid=".$tourney['tid']."'>".$ibforums->lang['remove']."</a>";
            		$tourney['datestarted'] = $this->convert_date($tourney['datestarted']);

            		$return .= $main->html->tournament_row($tourney);
        		}

        		$return .= $main->html->stop("&nbsp;");

            	$cat = "";
        		$DB->query("SELECT g.gid, g.gtitle, c.cat_name
            				FROM ibf_games_list AS g
                        	LEFT JOIN ibf_games_cats AS c
                        	ON (g.gcat = c.c_id)
                        	ORDER BY c.pos, c.c_id, g.gtitle");
            	while( $game = $DB->fetch_row() )
            	{
            		if( $game['cat_name'] != $cat && $main->arcade->settings['use_cats'] == 1 )
                	{
                    	if( preg_match("/optgroup/i", $game_select) )
                    	{
                			$game_select .= "</optgroup>";
                    	}
                		$game_select .= "<optgroup label='".$game['cat_name']."'>";
                    	$cat = $game['cat_name'];
                	}
                	$game_select .= "<option value='".$game['gid']."'>".$game['gtitle']."</option>";
            	}

                $return .= $main->html->new_tourney($game_select);
        }

        return $return;
    }
}

$module = new module;

function clean_html($value)
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

?>
