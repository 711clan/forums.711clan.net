<?php

class module
{
	var $html;

	function module()
	{
		global $main, $ibforums, $DB, $std;

		$main->arcade->authorize();  //Makes sure user can see the arcade

		$main->arcade->top_links($main->html);
        $this->html .= $main->arcade->extra_links;

        $tmp_html = array();

	$mid = (isset($ibforums->input['user'])) ? intval($ibforums->input['user']) : $ibforums->member['id'];

	if( $mid <= 0 )
	{
	$mid = $ibforums->member['id'];
	}

        $DB->query("SELECT name FROM ibf_members WHERE id=".$mid);
        $user = $DB->fetch_row();

        //Numbers
		$topten 			= 0;
		$firstplace 		= 0;
		$secondplace 		= 0;
		$thirdplace 		= 0;
		$totalgames 		= 0;
		$gamesplayed 		= 0;
		$totalgamesplayed 	= 0;
        $game_string 		= "(0)";
        //Arrays
        $games 				= array();
        $score_totals 		= array();
        $top_scores 		= array();
        $users_place 		= array();
        $users_top 			= array();
        $gid_array 			= array();

        $DB->query("SELECT g.gid, g.gtitle, g.gcount, g.highscore_type, c.password from ibf_games_list AS g, ibf_games_cats AS c WHERE g.active=1 AND g.gcat=c.c_id AND trim(password)='' ORDER BY ".$main->arcade->settings['g_display_sort']." ".$main->arcade->settings['g_display_order']);
        while( $this_game = $DB->fetch_row() )
        {
        	$games[] = array(
            					'gid'		=> $this_game['gid'],
                                'gtitle'	=> $this_game['gtitle'],
                                'played'	=> $this_game['gcount'],
                                'type'		=> $this_game['highscore_type'],
                       );
            $gid_array[] = $this_game['gid'];
            $totalgames++;
        }

        if( count($gid_array) > 0 )
        {
        	$game_string = "(".implode("," , $gid_array).")";
        }

        $DB->query("SELECT s.*, g.highscore_type AS type FROM ibf_games_scores AS s, ibf_games_list AS g WHERE s.gid=g.gid AND s.gid IN ".$game_string." ORDER BY score DESC, timespent ASC");
        while( $this_score = $DB->fetch_row() )
        {
        	if( !isset( $score_totals[ $this_score['gid'] ] ) )
            {
            	$top_scores[ $this_score['gid'] ]['score'] = 0;
            	$score_totals[ $this_score['gid'] ] = 0;
            }

            if( !isset($users_place[ $this_score['gid'] ]) )
            {
            	$users_place[ $this_score['gid'] ] = 0;
                $users_top[ $this_score['gid'] ] = 0;
            }

            if( ($this_score['score'] > $top_scores[ $this_score['gid'] ]['score']) && $this_score['type'] == "high" )
            {
            	$top_scores[ $this_score['gid'] ]['score'] = $this_score['score'];
                $top_scores[ $this_score['gid'] ]['time'] = $this_score['datescored'];
            }

            if( (($this_score['score'] < $top_scores[ $this_score['gid'] ]['score']) && $this_score['type'] == "low") || $top_scores[ $this_score['gid'] ]['score'] == 0 )
            {
                $top_scores[ $this_score['gid'] ]['score'] = $this_score['score'];
                $top_scores[ $this_score['gid'] ]['time'] = $this_score['datescored'];
            }

            $score_totals[ $this_score['gid'] ]++;

            if( $this_score['mid'] == $mid )
            {
            	$gamesplayed++;
                if( ($this_score['score'] > $users_top[ $this_score['gid'] ]) && $this_score['type'] == "high" )
            	{
            		$users_top[ $this_score['gid'] ] = $this_score['score'];
                    $users_place[ $this_score['gid'] ] = $score_totals[ $this_score['gid'] ];
            	}

            	if( (($this_score['score'] < $users_top[ $this_score['gid'] ]) && $this_score['type'] == "low") || $users_top[ $this_score['gid'] ] == 0 )
            	{
               		$users_top[ $this_score['gid'] ] = $this_score['score'];
                    $users_place[ $this_score['gid'] ] = $score_totals[ $this_score['gid'] ];
            	}
            }
        }

        $col = "row2";
        foreach( $games as $the_game )
        {
        	if( $main->arcade->settings['skin'] != 0 )
            {
            	$col = ($col == "row2") ? "row1" : "row2";
            }
        	$score = $ibforums->lang['n_a'];
            $the_age = $ibforums->lang['n_a'];
        	if( isset($top_scores[ $the_game['gid'] ]['time']) )
            {
        		$the_age = $this->thatdate( time() - $top_scores[ $the_game['gid'] ]['time'] );
            }
            if( $users_place[ $the_game['gid'] ] )
            {
            	if( $the_game['type'] == "high" )
                {
                	$rank = $users_place[ $the_game['gid'] ];
                }
                else
                {
                	$rank = ($score_totals[ $the_game['gid'] ] - $users_place[ $the_game['gid'] ]) + 1;
                }

                if( $rank == 1 )
                {
                	$rank = "<big><b>".$rank."</b></big>";
                    $firstplace++;
                }
                elseif( $rank <= 10 )
                {
                	if( $rank == 2 )
                    {
                    	$secondplace++;
                    }
					elseif( $rank == 3 )
                    {
                    	$thirdplace++;
                    }
                    else
                    {
                    	$topten++;
                    }
                	$rank = "<b>".$rank."</b>";
                }
                $rank = $rank.$ibforums->lang['out_of'].$score_totals[ $the_game['gid'] ];

                if( $users_top[ $the_game['gid'] ] )
                {
                	$score = $main->arcade->t3h_format($users_top[ $the_game['gid'] ]);
                }
            }
            else
            {
            	$rank = $ibforums->lang['n_a'];
            }

            $totalgamesplayed += $the_game['played'];

            $the_game['gtitle'] = $main->arcade->make_links( $the_game['gid'] , $the_game['gtitle'] , 1 );

        	$tmp_html['rank_row'] .= $main->html->rankrow( $col , $main->arcade->links['click'] , $score ,$rank , $the_game['played'] , $the_age );
        }

        $col = "row2";
        $detail_rows = array(
                          		$ibforums->lang['first_place']						=> $firstplace,
                                $ibforums->lang['second_place']						=> $secondplace,
                                $ibforums->lang['third_place']						=> $thirdplace,
                                $ibforums->lang['top_ten']							=> $topten,
                                $ibforums->lang['total_games']						=> $totalgames,
                                $ibforums->lang['total_i_play']	=> $gamesplayed,
                                $ibforums->lang['total_play']						=> $totalgamesplayed,
                       );

        foreach( $detail_rows as $header=>$content )
        {
            if( $main->arcade->settings['skin'] != 0 )
            {
            	$col = ($col == "row2") ? "row1" : "row2";
            }
            $tmp_html['rank_detail_row'] .= $main->html->rankdetailrow($col , $header , $content);
        }

        $this->html .= $main->html->report_main($tmp_html , $user['name']);

		$this->html .= $main->html->copyright($main->version,$ibforums->lang['timeformat1'],$main->BFL);
		$main->arcade->print_it($this->html, "Rankings", "Rankings");

	}

    function thatdate($time)
	{
		global $ibforums;

		$diff = $time;
		$daysDiff = floor($diff/60/60/24);
		$diff -= $daysDiff*60*60*24;
		$hrsDiff = floor($diff/60/60);
		$diff -= $hrsDiff*60*60;
		$minsDiff = floor($diff/60);
		$diff -= $minsDiff*60;
		$secsDiff = $diff;

		$eltime = "";
		if($daysDiff)
        {
        	 $eltime = $eltime.$daysDiff." ".$ibforums->lang['acp_day1'];
	if ($daysDiff == 1) { $eltime = $eltime." "; }
	else { $eltime = $eltime.$ibforums->lang['acp_day2']." "; }
        }

        if($hrsDiff)
        {
	if ($hrsDiff < 10) { $hrsDiff="0".$hrsDiff; }
        	$eltime = $eltime.$hrsDiff.":";
        }

        if($minsDiff)
        {
	if ($minsDiff < 10) { $minsDiff="0".$minsDiff; }
        	$eltime = $eltime.$minsDiff.":";
        }

	if ($secsDiff < 10) { $secsDiff="0".$secsDiff; }
        	$eltime = $eltime.$secsDiff."";

		return $eltime;
	}

}

$module = new module;

?>