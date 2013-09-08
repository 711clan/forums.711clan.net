<?php

class module
{
	var $html = "";
    var $user = array();

    function module()
    {
    	global $main, $ibforums, $DB, $std;

        if( isset($ibforums->input['is_submitted']) )
        {
        	$this->update_settings();
        }

        $main->arcade->authorize();

        $main->arcade->top_links($main->html);
        $this->html .= $main->arcade->extra_links;

        $selected['sort'] = array( gtitle	=> "" , gcount	=> "" , gwords	=> "" , g_rating	=> "" );
        $selected['order'] = array( ASC	=> "" , DESC	=> "" );

        $choices = explode("," , $main->arcade->settings['user_choices']);
        sort($choices);

        foreach( $choices as $choice )
        {
        	$select = "";
        	if( $choice )
            {
                if( $main->arcade->user['user_s_pp'] )
                {
                	if( $main->arcade->user['user_s_pp'] == $choice )
                    {
                    	$select = "selected=\"selected\"";
                    }
                }
            	$this->user['choices_s'] .= "<option ".$select." value=\"".$choice."\">".$choice."</option>";
            }
        }

        foreach( $choices as $choice )
        {
        	$select = "";
        	if( $choice )
            {
            	if( $main->arcade->user['user_g_pp'] )
                {
                	if( $main->arcade->user['user_g_pp'] == $choice )
                    {
                    	$select = "selected=\"selected\"";
                    }
                }
            	$this->user['choices_g'] .= "<option ".$select." value=\"".$choice."\">".$choice."</option>";
            }
        }

	// PM/Mail notification
        if($main->arcade->settings['notification']!="none")
        {
		$pm = 0;
        	if( $main->arcade->user['arcade_pmactive'] == 1)
            	{
            		$pm = 1;
            	}
        	$this->user['pm'] = $main->html->user_pm_row($pm);
        }

        if( $main->arcade->settings['allow_user_skin'] )
        {
        	$skin = array ( 1 => "" , 2 => "" );
        	if( $main->arcade->user['game_skin'] )
            {
            	$skin[ $main->arcade->user['game_skin'] ] = "selected=\"selected\"";
            }
        	$this->user['skins'] = $main->html->user_skin_row($skin);
        }

        if( $main->arcade->settings['use_cats'] )
        {
        	$DB->query("SELECT * FROM ibf_games_cats WHERE active=1");
        	while( $CAT = $DB->fetch_row() )
        	{
            	$select = "";
            	if( $main->arcade->user['def_g_cat'] )
                {
                	if( $main->arcade->user['def_g_cat'] == $CAT['c_id'] )
                    {
                    	$select = "selected=\"selected\"";
                    }
                }
        		$the_cats .= "<option ".$select." value=\"".$CAT['c_id']."\">".$CAT['cat_name']."</option>";
        	}
            $this->user['cat_row'] = $main->html->user_cat_row($the_cats);
        }

        $selected['sort'][ $main->arcade->user['user_sort'] ] = "selected='selected'";
        $selected['order'][ $main->arcade->user['user_order'] ] = "selected='selected'";

        $this->html .= $main->html->user_settings($this->user , $selected);

        $main->arcade->get_active($main->html);
        $this->html .= $main->arcade->active;
        $this->html .= $main->html->copyright($main->version,$ibforums->lang['timeformat1'],$main->BFL);
        $main->arcade->print_it( $this->html , $ibforums->lang['user_settings'] , $ibforums->lang['user_settings'] );
    }

    function update_settings()
    {
	global $ibforums, $pre, $DB, $print, $main;

	// check for some default values...
	if (($ibforums->input['def_cat'] == "") || (intval($ibforums->input['def_cat']) < 0))
	{
		$ibforums->input['def_cat'] = "0";
	}

	if (($ibforums->input['skin_use'] == "") || (intval($ibforums->input['skin_use']) < 0))
	{
		$ibforums->input['skin_use'] = "0";
	}

        $db_string = $DB->compile_db_update_string( array( 	'user_sort'		=> $ibforums->input['user_sort'],
        													'user_order'	=> $ibforums->input['user_order'],
                                                            'user_g_pp'		=> $ibforums->input['g_per_page'],
                                                            'user_s_pp'		=> $ibforums->input['s_per_page'],
                                                            'def_g_cat'		=> $ibforums->input['def_cat'],
                                                            'game_skin'		=> $ibforums->input['skin_use'],
							    'arcade_pmactive'	=> $ibforums->input['pm']  ) );
        $DB->query("UPDATE ibf_members SET ".$db_string." WHERE id=".$main->arcade->user['id']);

        $print->redirect_screen( $ibforums->lang['set_updated'], $ibforums->vars['base_url']."act=Arcade&amp;module=settings" );
    }
}

$module = new module;

?>
