<?php

class module
{
	var $html = "";
	var $all = 0;

	function module()
	{
		global $main, $ibforums, $DB, $std;

		$main->arcade->authorize();  //Makes sure user can see the arcade

		$main->arcade->top_links($main->html);

        $this->html .= $main->arcade->extra_links;

        if( $main->arcade->settings['use_cats'] )
        {
		if(isset($ibforums->input['lcat'])) 		
			$lcat = intval($ibforums->input['lcat']);

		$this->html .= "<form action=\"{$ibforums->base_url}act=Arcade&amp;module=league\" method=\"post\">";
		$the_cats .= "<option value=\"\">{$ibforums->lang['acp_all']}</option>";
        	$DB->query("SELECT * FROM ibf_games_cats WHERE active=1 AND trim(password)=''");
        	while( $CAT = $DB->fetch_row() )
        	{
            	$select = "";
            	if( $lcat )
                {
                	if( $lcat == $CAT['c_id'] )
                    {
                    	$select = "selected=\"selected\"";
			$all = $CAT['show_all'];
                    }
                }
        		$the_cats .= "<option ".$select." value=\"".$CAT['c_id']."\">".$CAT['cat_name']."</option>";
        	}
            $this->html .= $main->html->cat_select($the_cats);

	    $this->html .= "&nbsp;<input type=\"submit\" value=\"{$ibforums->lang['sort_em']}\" class=\"forminput\" /><br /><br />";
	    
	}

	$this->html .= $main->html->leagueheader();

	$rank = 0;

	$extquery = "";

       if( ($main->arcade->settings['use_cats']) && (!$all) && ($lcat)) {
           $extquery = "WHERE l.cat=".$lcat." ";
       }
       $league = $DB->query("SELECT l.mid, l.gid, AVG(l.position) AS position, SUM(l.points) as points,
                                    m.name
                                    FROM ibf_games_league AS l
                                    LEFT JOIN ibf_members AS m ON (l.mid=m.id)
                                    ".$extquery."
                                    GROUP by mid
                                    ORDER BY points DESC");
               while ($row = $DB->fetch_row($league) ) {
                   extract($row);
                   $rank++;
                   $name = $row['name'];
                   if ($ibforums->member['id'] == $mid) {
                       $name = "<b>".$name."</b>";
                   }
                   $name = "<a href='".$ibforums->base_url."act=Arcade&amp;module=report&amp;user=".$mid."'><color='#000000'>".$name."</color></a>";
                   $avgrank = $main->arcade->do_arcade_format($row['position'],2);
		$this->html .= $main->html->leaguerow("row2",$rank,$name,$avgrank,$main->arcade->do_arcade_format($points,0));
		}
		$this->html .= $main->html->stop("&nbsp;","&nbsp;");
		$this->html .= $main->html->copyright($main->version,$ibforums->lang['timeformat1'],$main->BFL);
                $main->arcade->print_it($this->html, $ibforums->lang['league'], $ibforums->lang['league']);
	}

}	
$module = new module;

?>
