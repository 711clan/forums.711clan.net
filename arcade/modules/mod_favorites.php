<?php

class module
{
	var $html = "";

	function module()
    {
    	global $main, $DB, $ibforums, $std;

        $main->arcade->authorize();

        if( $main->arcade->user['arcade_access'] != 2 || !$main->arcade->user['id'] )
        {
        	$std->boink_it($ibforums->base_url."act=Arcade");
        }

    	$temp = unserialize($main->arcade->user['favs']);

        if( !is_array($temp) )
        {
        	$temp = array();
        }

	$stylecolumns = $main->arcade->settings['games_pr'];

        $favs = $temp;
	$game_counter = 1;

        if( isset($ibforums->input['gameid']) )
        {
            $DB->query("SELECT g.gcat, c.c_id, c.password
						FROM ibf_games_list AS g
						LEFT JOIN ibf_games_cats AS c
						ON (g.gcat = c.c_id)
						WHERE gid=".intval($ibforums->input['gameid']));
			$this_game = $DB->fetch_row();
			if( $this_game['password'] != "" )
			{
				$the_cookie = 'cat_pass_'.$this_game['c_id'];
				$pass = $_COOKIE[$the_cookie];
				if( $this_game['password'] != $pass )
				{
					$std->boink_it($ibforums->base_url."act=Arcade&amp;module=favorites");
				}
			}

        	$gid = intval($ibforums->input['gameid']);

            $DB->query("SELECT gid FROM ibf_games_list WHERE gid=".$gid);
            if( $DB->get_num_rows() )
            {
            	if( in_array($gid , $favs) )
            	{
            		$new_favs = array();
            		foreach( $favs as $gameid )
            		{
            			if( $gameid != $gid )
                		{
                			$new_favs[] = $gameid;
                		}
            		}

                	$new_favs = serialize($new_favs);
                	$update_string = $new_favs;
            	}
            	else
            	{
            		$favs[] = $gid;
                	$favs = serialize($favs);
                	$update_string = $favs;
            	}

            	$db_string = $DB->compile_db_update_string( array( 'fav_games'		=>	$update_string,  ) );

        		$DB->query("UPDATE ibf_members SET ".$db_string." WHERE id=".$main->arcade->user['id']);
            }

            $std->boink_it($ibforums->base_url."act=Arcade&module=favorites");
        }

        $main->arcade->top_links($main->html);
        $this->html .= $main->arcade->extra_links;

        $favs_string = "(0)";
        if( count($favs) > 0 )
        {
        	$favs_string = "(".implode("," , $favs).")";
        }

        $highscores = array();
        $scores_query = $DB->query("SELECT champ_gid, champ_name, champ_score FROM ibf_games_champs WHERE champ_gid IN ".$favs_string." ORDER BY champ_gid");
        while( $row = $DB->fetch_row($scores_query) )
        {
        	$row['champ_score'] = $row['champ_score'];
        	$highscores[ $row['champ_gid'] ] = array(  'name' => $row['champ_name'], 'score' => $row['champ_score']);
        }

        $personal_highs = array();
        $scores_query = $DB->query("SELECT mid, gid, score AS the_score, MAX(score) FROM ibf_games_scores WHERE gid IN ".$favs_string." AND mid=".$main->arcade->user['id']." GROUP BY score");
        while( $row = $DB->fetch_row($scores_query) )
        {
        	$personal_highs[ $row['gid'] ] = $row['the_score'];
        }

        $games_query = $DB->query("SELECT * FROM ibf_games_list WHERE gid IN ".$favs_string."
								AND active=1
        							ORDER BY ".$main->arcade->settings['g_display_sort']." ".$main->arcade->settings['g_display_order']);
        if( $DB->get_num_rows($games_query) )
        {
        	$rowcol = "row2";

            if( $main->arcade->settings['show_new'] )
        	{
        		$time = time()-($main->arcade->settings['show_new']*$main->arcade->settings['show_new_frame']);
			}

       	while( $row = $DB->fetch_row($games_query) )
            {
	$gamesplit = "";
                if( $main->arcade->settings['skin'] != 0 )
                {
    				$rowcol = ($rowcol == "row1") ? "row2" : "row1";
				}

            	$rating = "";
            	$rating = $ibforums->lang['rating'];
            	$raters = unserialize($row['g_raters']);
            	if( empty($row['g_raters']) )
            	{
            		$rating .= $ibforums->lang['no_votes'];
            	}
            	else
            	{
            		$amount = count($raters).$ibforums->lang['rates'];
            		for( $a = 1 ; $a <= $row['g_rating'] ; $a++ )
                	{
                		$rating .= "<img src='./arcade/images/star1.gif' title='".$amount."' alt='".$amount."' />";
                	}
                	$leftover = (5-$row['g_rating']);
                	for( $a = 1 ; $a <= $leftover ; $a++ )
                	{
                		$rating .= "<img src='./arcade/images/star2.gif' title='".$amount."' alt='".$amount."' />";
                	}
            	}

            	if( $main->arcade->user['id'] != 0 && !isset($raters[$main->arcade->user['id']]) )
            	{
            		$rating .= $main->html->rate_link($row['gid']);
            	}

            	$main->arcade->make_links($row['gid'] , $row['gtitle']);

                if( $row['added'] > $time && $main->arcade->settings['show_new'] )
            	{
            		$main->arcade->links['click'] .= "&nbsp;<img src='./arcade/images/new.gif' title='New' alt='New' />";
            	}

                $pbesttext = '';
				if( $personal_highs[ $row['gid'] ] == "" )
                {
					$pbesttext = $ibforums->lang['n_a'];
				}
                else
                {
					$pbesttext = $main->arcade->do_arcade_format($personal_highs[ $row['gid'] ], $row['decpoints']);
				}

                $top = $highscores[ $row['gid'] ];
		$top['score'] = $main->arcade->do_arcade_format($top['score'],$row['decpoints']);

                $row['gtitle2'] = $row['gtitle'];

                if( $main->arcade->user['arcade_access'] == 2 && $main->arcade->user['id'] )
                {
                	$row['gtitle'] = "<a href='".$ibforums->base_url."act=Arcade&amp;module=favorites&amp;gameid=".$row['gid']."'>".$ibforums->lang[remove_from].$ibforums->lang[favorites]."</a>";
                }

		$row['gtime'] = $main->arcade->thatdate($row['gtime']);
		$row['gtotalscore'] = $main->arcade->do_arcade_format($row['gtotalscore'],$row['decpoints']);

	$bestquery=$DB->query("SELECT g.bestscore, g.bestmid, u.name FROM ibf_games_list AS g, ibf_user AS u WHERE g.gid=".$row['gid']." AND g.bestmid=u.userid");
	$bestdata=$DB->fetch_row($bestquery);

	$top['score'] = $main->arcade->do_arcade_format($bestdata['bestscore'],$row['decpoints']);
	$top['name'] = $bestdata['name'];

	if ($top['score'] != "0")
	{
	$top['name'] = "<img src=\"./arcade/images/crown.gif\"> <b> " . $top['name'] . " </b> <img src=\"./arcade/images/crown.gif\">";
	$top['score'] = $ibforums->lang['tourneyinfo_txt1']."<b>" . $top['score']. "</b>".$ibforums->lang['tourneyinfo_txt2'];
	}
	else
	{
	$top['name'] = "<i>".$ibforums->lang['noscorestored']."</i>";
	$top['score'] = "&nbsp;";
	}

	//game actual highscore
        $ordering = ($row['highscore_type'] == "high") ? "DESC" : "ASC";

	$this_query = $DB->query("SELECT * FROM ibf_games_scores WHERE gid=".$row['gid']." ORDER BY score ".$ordering.", datescored ASC");
	$actualhighscore = $DB->fetch_row($this_query);
	$actualtop['score'] = $main->arcade->do_arcade_format($actualhighscore['score'],$row['decpoints']);	

	if ($actualtop['score'] != "0")
	{
	$actualtop['name'] = "<img src=\"./arcade/images/trophy.gif\"> <b> " .$actualhighscore['name']. " </b> <img src=\"./arcade/images/trophy.gif\">";
	$actualtop['score'] = $ibforums->lang['tourneyinfo_txt1']."<b>" . $actualtop['score']. "</b>".$ibforums->lang['tourneyinfo_txt2'];
	}
	else
	{
	$actualtop['name'] = "<i>".$ibforums->lang['noscorestored']."</i>";
	$actualtop['score'] = "&nbsp;";
	}
    
            //is game new?
//	$newgame = "";
//            if( $the_game['added'] > $time && $this->arcade->settings['show_new'] )
//            {
//            	$this->arcade->links['click'] .= "&nbsp;<img src='./arcade/images/new.gif' title='New' alt='Neues Spiel' />";
//	$newgame = "<img src='./arcade/images/new.gif' title='New' alt='Neues Spiel' />";
//            }
// ----MrZero


                if ($main->arcade->settings['use_shop'] || $main->arcade->settings['use_inpoints']) {
				$cimg = "";
				if ($row['shop_gpoints'] > 0)
					$cimg = "&nbsp;<img src='./arcade/images/atb_coin.gif' alt='Minimum Score: ".$main->arcade->do_arcade_format($row['shop_minscore'],0)."\nGame Points: ".$main->arcade->do_arcade_format($row['shop_gpoints'],0)."\nArcade Points: ".$main->arcade->do_arcade_format($row['shop_points'],0)."\nMax Payout: ".$main->arcade->do_arcade_format($row['shop_maxpoints'],0)."' title='Minimum Score: ".$main->arcade->do_arcade_format($row['shop_minscore'],0)."\nGame Points: ".$main->arcade->do_arcade_format($row['shop_gpoints'],0)."\nArcade Points: ".$main->arcade->do_arcade_format($row['shop_points'],0)."\nMax Payout: ".$main->arcade->do_arcade_format($row['shop_maxpoints'],0)."' />";
				if ($row['gcost'] == 0)
					$row['gcost'] = "<b>".$ibforums->lang['free']."</b>";
				if($main->arcade->settings['skin'] != 0)
					$row['gcost'] = $ibforums->lang['cost_to_play'].": ".$row['gcost'].$cimg."<br />";
				else
					$row['gcost'] = "<li>".$ibforums->lang['cost_to_play'].": ".$row['gcost'].$cimg."</li>";
			}
			else
				$row['gcost'] = '';


      if ($game_counter == $stylecolumns) 
      {
             $gamesplit = "</tr><tr>";
             $game_counter = 0;
      }

            	$the_games .= $main->html->row($row,$gamesplit,$top,$pbesttext,$main->arcade->links,$rowcol,$rating,$actualtop,$newgame,$stylecolumns);
	$game_counter++;
            }
        }
        else
        {
        	$the_games .= $main->html->no_favs();
        }

        $this->html .= $main->html->favorites($the_games,$stylecolumns);

        $main->arcade->get_active($main->html);
        $this->html .= $main->arcade->active;

        $this->html .= $main->html->copyright($main->version,$ibforums->lang['timeformat1'],$main->BFL);
        $main->arcade->print_it($this->html , $ibforums->lang['favorite_games'] , $ibforums->lang['favorites']);
    }
}

$module = new module;

?>
