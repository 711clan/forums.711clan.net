<?php

// ibProArcade v2.6.9+

class skin_v3Arcade{

function game($game,$top,$extra) {
global $ibforums;
return <<<EOF
	<!-- Add JS function for pnFlashGames Updated games component (Not Sure if this is needed yet) -->
	<script type="text/javascript">
	<!--
	function refreshScores()
	{		
	}
	//-->
	</script>

	<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
  		<tr>
  			<td width="75%" align="center">
            	<div class="tborder" align="center">
                <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
                    <tr>
                    	<td class="alt1" align="center">
  				<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" width="{$game['gwidth']}" height="{$game['gheight']}">
  				<param name="menu" value="false" />
  				<param name="movie" value="./arcade/{$game['gname']}.swf?tgame=0&amp;pn_gid={$game['gid']}&amp;pn_license={$game['license']}&amp;pn_checksum={$game['checksum']}&amp;pn_domain={$game['domain']}&amp;pn_uname={$game['username']}" />
                                <param name="type" value="application/x-shockwave-flash" />
                                <param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer/" />
                                <param name="bgcolor" value="#{$game['bgcolor']}" />
  				<param name="quality" value="high" />
  				<param name="menu" value="false" />
                                <param name="width" value="{$game['gwidth']}" />
                                <param name="height" value="{$game['gheight']}" />
                                <param name="flashvars" value="location=./&amp;gamename={$game['gname']}&amp;hash={$game['hash']}" />
				<embed src="./arcade/{$game['gname']}.swf?tgame=0&amp;pn_gid={$game['gid']}&amp;pn_license={$game['license']}&amp;pn_checksum={$game['checksum']}&amp;pn_domain={$game['domain']}&amp;pn_uname={$game['username']}" width="{$game['gwidth']}" height="{$game['gheight']}" bgcolor="#{$game['bgcolor']}" quality="high" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer/" flashvars="location=./&amp;gamename={$game['gname']}" menu="false"></embed>
                                <noembed>{$ibforums->lang['no_embed']}{$game['gtitle']}</noembed>
				</object>
				<iframe src="holdsession.php?act=arcade&do=play&gameid=$game[gid]" width='0' height='0' frameborder='0'></iframe>
                        </td>
                    </tr>
                </table>
                </div>
            </td>
  			<td valign="top" align="left" width="25%">
            	<div class="tborder">
  				<table width="100%" border="0" cellspacing="1" cellpadding="4">
  					<tr>
						<td width="100%" align="center" class="tcat">{$game['gtitle']}</td>
					</tr>
					<tr>
                    	<td align="center" class="alt2"><img src="./arcade/images/{$game['gname']}1.gif" alt="" width="50" height="50" /></td>
                    </tr>
					<tr>
                    	<td align="center" class="alt1">{$ibforums->lang['score_beat']}<br /><b>{$top['score']}</b></td>
                    </tr>
					<tr>
                    	<td align="center" class="alt2"><b>{$top['name']}</b></td>
                    </tr>
                    <tr>
                    	<td align="center" class="alt1">
                        	<a href="{$ibforums->base_url}act=Arcade">{$ibforums->lang['back_arcade']}</a>
                        	<br /><a href="{$ibforums->base_url}act=Arcade&amp;do=stats&amp;gameid={$game['gid']}">{$ibforums->lang['view_high']}</a>
                        </td>
                    </tr>
					{$extra}
				</table>
                </div>
            </td>
  		</tr>
	</table>
	<br /><br />
	<iframe src="holdsession.php?act=arcade&do=play&gameid=$game[gid]" width='0' height='0' frameborder='0'></iframe>
EOF;
}

function top_links_table($links,$width,$extra) {
global $ibforums;
return <<<EOF
    <div class="tborder">
    <table width="100%" cellpadding="2" cellspacing="0" border="0">
    	<tr>
        	<td class="alt1" align="center" width="{$width}">{$links}</td>
            {$extra}
        </tr>
    </table>
    </div><br />
EOF;
}

function top_links_table_extra($cat , $selected) {
global $ibforums;
return <<<EOF
	<td class="alt1" align="right" width="5%">
	&nbsp;
    	</td>
EOF;
}

function objective($game) {
global $ibforums;
return <<<EOF
    <tr>
    	<td width="100%" align="center" class="tcat">{$ibforums->lang['object']}{$game['gtitle']}</td>
    </tr>
	<tr>
    	<td class="alt2">{$game['object']}</td>
    </tr>
EOF;
}

function keys($game) {
global $ibforums;
return <<<EOF
	<tr>
    	<td align="center" class="tcat">{$ibforums->lang['keys']}</td>
    </tr>
	<tr>
    	<td align="left" class="alt2">{$game['gkeys']}</td>
    </tr>
EOF;
}

function row($entry,$gamesplit,$top,$pbest,$links,$rowcol,$rating,$actualtop,$newgame) {
global $ibforums;
return <<<EOF
	<tr>
    	<td class="{$rowcol}">
			<table width="100%">
            	<tr>
                	<td valign="top" align="center" width="55">
						{$links['imglink']}<img src="arcade/images/{$entry['gname']}1.gif" height="50" width="50" title="{$entry['gwords']}{$entry['filesize']}" hspace="5" vspace ="1" style="border:0px #0069bb dashed;float:right;">{$links['imgend']}
					</td>
					<td valign='top'>
					<table width="100%" border="0" class="{$rowcol}">
					<tr>
                    			<td><b>{$links['click']}</b></td>
					<td align="right"><div class="smallfont"><i>{$entry['gtitle']}</i></div></td>
					</tr>
					</table>
						<div class="smallfont">{$entry['gwords']}</div>
						<table width="100%" border="0" class="{$rowcol}">
						  <tr>
							<td><b>{$ibforums->lang['times_played']}</b>{$entry['gcount']}&nbsp;</td>
							<td align="right">&nbsp;{$rating}</td>
						  </tr>
						</table>
                    </td>
				</tr>
			</table>
		</td>
		<td class="{$rowcol}" align="center" valign="middle" nowrap>
            <b>{$top['name']}</b><br />
            <b>$top[score]</b><br />
            <div class="smallfont"><a href="{$ibforums->base_url}act=Arcade&amp;do=stats&amp;gameid={$entry['gid']}">[{$ibforums->lang['view_high']}]</a></div>
        </td>
		<td class="{$rowcol}" align="center" valign="middle" nowrap>
        	{$ibforums->lang['pbest']}: {$pbest}<br />{$entry['v3style_info']}
		<div class="smallfont">{$entry['cost_info']}{$entry['jackpot_info']}</div>
        </td>
    </tr>
EOF;
}

function start($newtext,$latestinfo,$new_games,$pop_games,$ran_games,$tot_games,$usecats,$tot_cats,$clicktoplay,$plays_left,$tourneyinfo,$cats,$attente,$termine,$actifs,$mtinfo,$Champion,$Highscorechamp,$stylecolumns,$selected){
global $ibforums;

$totalstext = $ibforums->lang['infobox_title1'].$tot_games.$ibforums->lang['infobox_title2'];
if ($tot_cats < 1) { $tot_cats = 1; }
if ($usecats)
{
	$totalstext = $totalstext . $ibforums->lang['infobox_title3'].$tot_cats.$ibforums->lang['infobox_title4'];
	if ($tot_cats > 1)
	{
		$totalstext = $totalstext . $ibforums->lang['infobox_title6'];
	}
	else
	{
		$totalstext = $totalstext . $ibforums->lang['infobox_title5'];
	}
}
$totalstext = $totalstext . $ibforums->lang['infobox_title7'];

return <<<EOF
{$tourneyinfo['announcement']}<br />

<div class="tborder">
	<table width='100%' border="0" cellspacing="0" cellpadding="4">
  		<tr>
			<td align="center" width="20%" nowrap="nowrap" class="tcat">&nbsp;</td>
			<td align="center" width="60%" nowrap="nowrap" class="tcat">$totalstext</td>
			<td align="center" width="20%" nowrap="nowrap" class="tcat">&nbsp;</td>
</tr>
    	<tr>
      		<td class="alt1" align="left" valign="top" style="width: 20%;padding-left:7px;padding-top:5px;padding-bottom:5px;">

				<div class="tborder">
						<table width='100%' border='0' cellspacing='1' cellpadding='4'>
						<tr>
						<td align="center" width="20%" nowrap="nowrap" class="tcat">{$ibforums->lang['infobox_newgames']}</td>
						</tr>

						<tr>
      						<td class="alt2" align="left" valign="top" style="width: 20%;padding-right:7px;padding-top:5px;padding-bottom:5px;">

						{$new_games}

						</td>
						</tr>

						<tr>
						<td align="center" width="20%" nowrap="nowrap" class="tcat">{$ibforums->lang['search']}</td>
						</tr>
						<tr>

		<td width='20%' align='center' nowrap='nowrap' class='alt2' valign='middle'>
		<br /><form action="{$ibforums->base_url}act=Arcade" method="post">
    		<select name="search_type" class="forminput">
    		<option value="0" selected="selected">{$ibforums->lang['name_contains']}</option>
    		<option value="1">{$ibforums->lang['g_starts']}</option>
    		<option value="2">{$ibforums->lang['g_ends']}</option>
    		</select>&nbsp;&nbsp; <br />
    		<input type="text" size="15" name="gsearch" class="textinput" />&nbsp;&nbsp; <br /><br />
     		<input type="submit" value="{$ibforums->lang['gsearch']}" class="forminput" />
		</form>
		</td>

						</tr>
						</table>
				</div>

	  	</td>

      		<td class="alt1" align="center" valign="top" style="width: 60%;padding-left:7px;padding-top:5px;padding-bottom:5px;">

					<div class='tborder'>
						<table width='100%' border='0' cellspacing='0' cellpadding='4'>
						<tr>
							<th width='100%' align='center' nowrap='nowrap' class='tcat' colspan='3'><img src="./arcade/images/trophy.gif" border="0" alt="" />{$ibforums->lang['infobox_top3title']}<img src="./arcade/images/trophy.gif" border="0" alt="" /></th>
						</tr>

						<tr>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'><b>{$Highscorechamp['ArcadeChampionSmily1']}</b></td>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'><b>{$Highscorechamp['ArcadeChampionSmily2']}</b></td>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'><b>{$Highscorechamp['ArcadeChampionSmily3']}</b></td>
						</tr>

						<tr>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'>{$Highscorechamp['ArcadeChampionAvatarCode1']}</td>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'>{$Highscorechamp['ArcadeChampionAvatarCode2']}</td>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'>{$Highscorechamp['ArcadeChampionAvatarCode3']}</td>
						</tr>
						<tr>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'>{$Highscorechamp['ArcadeChampion1']}</td>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'>{$Highscorechamp['ArcadeChampion2']}</td>
							<td width='33%' align='center' nowrap='nowrap' class='alt2' valign='middle'>{$Highscorechamp['ArcadeChampion3']}</td>
						</tr>
						</table>
					</div>

					<br />

					<div class='tborder'>
						<table width='100%' border='0' cellspacing='0' cellpadding='2'>
						<tr>
							<th width='50%' align='center' nowrap='nowrap' class='tcat' colspan='1'><img src="./arcade/images/crown.gif" border="0" alt="" /> {$ibforums->lang['grand_champions']} <img src="./arcade/images/crown.gif" border="0" alt="" /></th>
							<th width='50%' align='center' nowrap='nowrap' class='tcat' colspan='1'>{$ibforums->lang['leagueleader']}</th>
						</tr>

						<tr>
							<td width='50%' align='center' nowrap='nowrap' class='alt2' valign='middle'>
							{$Champion['ArcadeChampionAvatarCode1']}
							</td>

							<td width='50%' align='center' nowrap='nowrap' class='alt2' valign='middle'>
							{$tourneyinfo['champavatar']}
							</td>
						</tr>

						<tr>
							<td width='50%' align='center' nowrap='nowrap' class='alt2' valign='middle'>
							{$Champion['ArcadeChampion1']}
							</td>

							<td width='50%' align='center' nowrap='nowrap' class='alt2' valign='middle'>
							{$tourneyinfo['champ']}
							</td>
						</tr>

						</table>
					</div>

				<fieldset class="fieldset" style="margin: 0px 0px 0px 0px;">
					<legend>{$ibforums->lang['newest_champs']}</legend>
					<div style="padding: 0px;">
						<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
							<tr>
								<td width="100%">
									<table cellpadding="2" cellspacing="1" border="0" width="100%">
										{$newtext}
									</table>
								</td>
							</tr>
						</table>
					</div>
				</fieldset>

				<fieldset class="fieldset" style="margin: 0px 0px 0px 0px;">
					<legend>{$ibforums->lang['latest_score']}</legend>
					<div style="padding: 0px;">
						<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
							<tr>
								<td width="100%">
								<table cellpadding="2" cellspacing="1" border="0" width="100%">
										<tr>
                                        									<td align="left">
												{$latestinfo}<br />
                                            									</td>
											<td align="right">
												{$clicktoplay['click']}
											</td>
										</tr>
								</table>
								</td>
							</tr>
						</table>
					</div>
				</fieldset>
		</td>

		<td class="alt1" align="right" valign="top" style="width: 20%;padding-left:7px;padding-top:5px;padding-bottom:5px;">
				<div class="tborder">
						<table width='100%' border='0' cellspacing='1' cellpadding='4'>
						<tr>
							<td align="center" width="20%" nowrap="nowrap" class="tcat">{$ibforums->lang['infobox_mostplayed']}</td>
						</tr>

						<tr>
      							<td class="alt2" align="right" valign="top" style="width: 20%;padding-right:7px;padding-top:5px;padding-bottom:5px;">
							{$pop_games}
							</td>
						</tr>
						
						<tr>
							<td align="center" width="20%" nowrap="nowrap" class="tcat">{$ibforums->lang['infobox_randomgame']}</td>
						</tr>

						<tr>
							<td width='20%' align='center' nowrap='nowrap' class='alt2' valign='middle'><br />{$ran_games}</td>
						</tr>

						</table>
				</div>
		
		</td>

		</tr>
</table>
    </div>

<br />

	<div class="tborder">
	<table width='100%' border="0" cellspacing="1" cellpadding="4">
			<tr>
			<td align="center" width="100%" nowrap="nowrap" class="tcat">{$ibforums->lang['infobox_tourneytitle']}</td>
			</tr>
    	<tr>
    		<td class="alt1" colspan="2">
				<table width="100%" border="1" cellspacing="1" cellpadding="1">
		<tr>
		<td width='33%' align='center' nowrap='nowrap' class='alt1' valign='middle'>$attente<br />$actifs<br />$termine</td>
		<td width='33%' align='center' nowrap='nowrap' class='alt1' valign='middle'>{$mtinfo['participe']}<br />{$mtinfo['encourse']}<br />{$mtinfo['elimine']}<br />{$mtinfo['disqualifie']}
		<td width='33%' align='center' nowrap='nowrap' class='alt1' valign='middle'><a href='{$ibforums->base_url}act=Arcade&amp;do=createtourney'><b>{$ibforums->lang['create_new_t']}</b></a></td>
                        </td>
					</tr>
				</table>
        	</td>
    	</tr>
    </table>
    </div>

	<br />

	<table width='100%' border="0" cellspacing="1" cellpadding="2">
	<tr>
	<td align="left" valign="bottom" width="35%">
	    	<form action="{$ibforums->base_url}act=Arcade{$cat}" method="post">
       		<select class="codebuttons" name="overwrite_sort">
		<option value="gtitle" {$selected['sort']['gtitle']}>{$ibforums->lang['gname']}</option>
		<option value="gcount" {$selected['sort']['gcount']}>{$ibforums->lang['times_p']}</option>
	        	<option value="gwords" {$selected['sort']['gwords']}>{$ibforums->lang['description']}</option>
        		<option value="g_rating" {$selected['sort']['g_rating']}>{$ibforums->lang['rating_s']}</option>
		<option value="added" {$selected['sort']['added']}>{$ibforums->lang['dateadded']}</option>
		</select>
	        	<select class="codebuttons" name="overwrite_order">
        		<option value="ASC" {$selected['order']['ASC']}>{$ibforums->lang['asc']}</option>
	        	<option value="DESC" {$selected['order']['DESC']}>{$ibforums->lang['desc']}</option>
        		</select>
		<input type="submit" value="{$ibforums->lang['sort_em']}" class="forminput" />
		</form>
	</td>

	<td align="right" width="65%">
	<table class="tborder" border="0" cellspacing="1" cellpadding="1" align="right">
	<tr>
	{$clicktoplay['alphabar']}
	</tr>
	</table>
	</td>
	</table>

	<div class="tborder">
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr>
			<td width="100%" align="center" colspan="4" class="tcat">{$ibforums->lang['game_info']}{$plays_left}</td>
    	</tr>
    	{$cats}
	{$tourneyinfo['desc']}
EOF;
}

function newest_champs_row($row) {
global $ibforums;
return <<<EOF
	<tr>
    	<td class="alt1" align="left" valign="middle" width="80%">
        	<span class="smallfont">
            	{$row['text']}
            </span>
        </td>
        <td class="alt1" align="right" valign="middle" width="20%">
        	<font size="1"><i>{$row['champ_date']}</i></font>
        </td>
    </tr>
EOF;
}

function the_cat_table($cats) {
global $ibforums;
return <<<EOF
	<tr>
    	<td colspan="4" class="pformstrip">
        	<div class="tborder">
            <table width="100%" border="0" cellspacing="1" cellpadding="4">
                <tr class="alt2">
                	{$cats}
                </tr>
            </table>
            </div>
        </td>
    </tr>
EOF;
}

function cat_cell($cat) {
global $ibforums;
return <<<EOF
	<td width="15%" class="alt2" align="center" style="font-weight: normal;">
        {$cat}
    </td>
EOF;
}

function stop($pages){
global $ibforums;
return <<<EOF
	</table>
    </div>
	{$pages}
	<br />
EOF;
}

function post_stop($yscore, $commentcell) {
global $ibforums;
return <<<EOF
	<br />
	<div class="tborder">
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr>
			<td width='100%' align='center' nowrap='nowrap' class='thead' colspan='5'>{$ibforums->lang['your_score']}</td>
		</tr>
		<tr>
			<td width="300" align="center" class="tcat">{$ibforums->lang['username']}</td>
			<td width="50" align="center" class="tcat">{$ibforums->lang['score']}</td>
			<td width="*" align="center" class="tcat">{$ibforums->lang['comment']}</td>
			<td width="150" align="center" class="tcat">{$ibforums->lang['time']}</td>
		</tr>
		<tr>
			<td width="300" align="center" class="alt2">{$yscore[1]}</td>
			<td width="50" align="center" class="alt2">{$yscore[2]}</td>
			<td width="*" align="center" class="alt2">{$commentcell}</td>
			<td width="150" align="center" class="alt2">{$yscore[3]}</td>
		</tr>
	</table>
	</div><br />
EOF;
}

function leaderstart($ginfo, $links) {
global $ibforums;
return <<<EOF
	<div class="tborder">
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr>
			<td width="75%" align="left" class="thead">&nbsp;&nbsp;&nbsp;{$ibforums->lang['game']}</td>
			<td width="25%" align="center" class="thead">{$ibforums->lang['options']}&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td class="alt2" align="left" valign="top">
				<table>
                	<tr>
                    	<td valign="top" align="center">
							&nbsp;&nbsp;&nbsp;{$links['imglink']}<img src="./arcade/images/{$ginfo['gname']}1.gif" align="top" alt="" border="0" width="50" height="50" />{$links['imgend']}
                        </td>
						<td valign="middle">
                        	<big><strong>{$ginfo['gtitle']}</strong> {$ibforums->lang['arcade_scores']}</big><br />
							<strong>{$ibforums->lang['description']}</strong> {$ginfo['gwords']}
                        </td>
					</tr>
                </table>
			</td>
			<td class="alt2" align="center" valign="top">
				{$links['click']}<br />
				<a href="{$ibforums->base_url}act=Arcade&amp;cat={$ginfo['gcat']}">{$ginfo['backlink']}</a><br />
				{$ginfo['fave']}
			</td>
		</tr>
	</table>
	</div>
	<br /><br />
	<div class="tborder">
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr>
			<td width="100%" align="center" class="thead" colspan="5">{$ibforums->lang['top_score']}</td>
		</tr>
		<tr>
            <td class="pformstrip" align="center" colspan="5">
        		<div class="tborder" style="width: 50%;">
            	<table cellpadding="3" cellspacing="0" border="0" width="100%">
            		<tr>
                		<td width="45%" class="alt1" align="right" valign="middle">
                        	{$ginfo['avatarcode']}
                    	</td>
                    	<td class="alt2" align="left" valign="middle">
                        	{$ginfo['champ_name']}<br />
                        {$ibforums->lang['hscorebox_text1']}<b><i>{$ginfo['gtitle']}</i></b>{$ibforums->lang['hscorebox_text2']}<br />
                        {$ibforums->lang['hscorebox_text3']}<b>{$ginfo['champ_score']}</b>{$ibforums->lang['hscorebox_text4']}
                    	</td>
                	</tr>
            	</table>
            	</div>
        	</td>
    	</tr>
		<tr>
			<td width="20" align="center" class="tcat">{$ibforums->lang['pound']}</td>
			<td width="300" align="center" class="tcat">{$ibforums->lang['username']}</td>
			<td width="50" align="center" class="tcat">{$ibforums->lang['score']}</td>
			<td width="*" align="center" class="tcat">{$ibforums->lang['comment']}</td>
			<td width="150" align="center" class="tcat">{$ibforums->lang['time']}</td>
		</tr>
EOF;
}

function leaderrow($rowinfo,$rownum,$usercell,$datecell,$scorecell, $rowcol) {
global $ibforums;

switch ($rownum)
{
	case "1":
	case "<b>1</b>":
	$rownum = "<img src='./arcade/images/1st.gif' alt='1' />"; break;
	case "2":
	case "<b>2</b>":
	$rownum = "<img src='./arcade/images/2nd.gif' alt='2' />"; break;
	case "3":
	case "<b>3</b>":
	$rownum = "<img src='./arcade/images/3rd.gif' alt='3' />"; break;
	default:
	break;
}

return <<<EOF
	<tr>
		<td width="20" align="center" class="{$rowcol}">{$rownum}</td>
		<td width="300" align="center" class="{$rowcol}">{$usercell}</td>
		<td width="50" align="center" class="{$rowcol}">{$scorecell}</td>
		<td width="*" align="center" class="{$rowcol}">{$rowinfo['comment']}</td>
		<td width="150" align="center" class="{$rowcol}">{$datecell}</td>
	</tr>
EOF;
}

function tournament_listing() {
global $ibforums;
return <<<EOF
	<div class="tborder">
	<table border="0" cellspacing="1" cellpadding="4" width="100%">
		<tr>
			<td width="100%" align="center" class="thead" colspan="5">{$ibforums->lang['active_tournaments']}</td>
		</tr>
		<tr>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['legend_of_zelda']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['tourney_title']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['num_of_players']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['your_status']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['date_started']}</td>
		</tr>
EOF;
}
function tournament_actif($rowinfo) {
global $ibforums;
return <<<EOF
<tr>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['link']}</td>
<td align='center' nowrap='nowrap' class='alt1'><a href='{$ibforums->base_url}act=Arcade&amp;do=play&amp;gameid={$rowinfo['gid']}'>{$rowinfo['gtitle']}</a> {$ibforums->lang['tournament']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['numplayers']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['statut']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['datestarted']}</td>
</tr>
EOF;
}


function finished_tournament_listing() {
global $ibforums;
return <<<EOF
        <div class="tborder">
	  <table border="1" cellspacing="3" cellpadding="4" width="100%">
        <div class="tcat" align="center">{$ibforums->lang['unactive_tournaments']}</div>
        <table border="1" cellspacing="3" cellpadding="4" width="100%">
                <tr>
                        <td width="20%" align="center" class="alt1"><b>{$ibforums->lang['legend_of_zelda']}</b></td>
                        <td width="20%" align="center" class="alt1"><b>{$ibforums->lang['tourney_title']}</b></td>
                        <td width="20%" align="center" class="alt1"><b>{$ibforums->lang['num_of_players']}</b></td>
                        <td width="20%" align="center" class="alt1"><b>{$ibforums->lang['champion']}</b></td>
                        <td width="20%" align="center" class="alt1"><b>{$ibforums->lang['date_started']}</b></td>
                </tr>
EOF;
}
function tournament_attente() {
global $ibforums;
return <<<EOF
</table>
</div>
<br /><br />
<a name="attente"></a>
<div class='tborder'>
<table border='1' cellspacing='3' cellpadding='4' width='100%'>
<tr>
<th width='100%' align='center' nowrap='nowrap' class='tcat' colspan='8'>{$ibforums->lang['tourneys_waiting']}</th>
</tr>
<tr>
<td width='14%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['legend_of_zelda']}</td>
<td width='20%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['tourney_title']}</td>
<td width='10%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['players_req']}</td>
<td width='10%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['open_slots']}</td>
<td width='10%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['tries_round']}</td>
<td width='25%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['registration']}</td>
<td width='16%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['created_on']}</td>
<td width='16%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['creator']}</td>
</tr>
EOF;
}
function tournament_attente_row($rowinfo) {
global $ibforums;
return <<<EOF
<tr>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['link']}</td>
<td align='center' nowrap='nowrap' class='alt1'><a href='{$ibforums->base_url}act=Arcade&amp;do=play&amp;gameid={$rowinfo[gid]}'>{$rowinfo['gtitle']}</a> {$ibforums->lang['tournament']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['numplayers']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['plibre']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['nbtries']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['inscrire']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['datestarted']}</td>
<td align='center' nowrap='nowrap' class='alt1'>{$rowinfo['creat']}</td>
</tr>
EOF;
}
function create_tourney($formlistgame,$extra) {
global $ibforums;
return <<<EOF

<div class ='tcat'>
<table border='0' table cellspacing="3" cellpadding="4" width='100%'>
<tr>
  <td width="100%" align="center" class="tcat">{$ibforums->lang['create_new_t']}</td>
  <table width='100%'>
  	<form action='arcade.php?' method='post' name='creattourney' >
		 <input type='hidden' name='act' value='Arcade'>
		 <input type='hidden' name='do' value='docreatetourney'>
  		<tr>
  			<td class='alt2'>
			{$ibforums->lang['which_game']}</td>
			<td class='alt2'>
				<select name='the_game' class='forminput' size=1>
		  			$formlistgame
				</select>
			</td>
		</tr>
  		<tr>
  			<td class='alt2'>{$ibforums->lang['many_players']}</td>
			<td class='alt2'>
				<select name='nbjoueurs' class='forminput' size=3>
		  			<option value='2'>{$ibforums->lang['two_players']}</option>
		  			<option value='4'>{$ibforums->lang['four_players']}</option>
		  			<option value='8' selected='selected'>{$ibforums->lang['eight_players']}</option>
				</select>
			</td>
		</tr>
  		<tr>
  			<td class='alt2'>{$ibforums->lang['tries_each_round']}</td>
			<td class='alt2'>
				<select name='nbtries' class='forminput' size=5>
		  			<option value='1'>1 {$ibforums->lang['try']}</option>
		  			<option value='2'>2 {$ibforums->lang['tries']}</option>
		  			<option value='3' selected='selected'>3 {$ibforums->lang['tries']}</option>
		  			<option value='4'>4 {$ibforums->lang['tries']}</option>
		  			<option value='5'>5 {$ibforums->lang['tries']}</option>
				</select>
			</td>
		</tr>
		{$extra}
		<tr>
            <td align='center' class='pformstrip' colspan='2' ><input type='submit' value='{$ibforums->lang['create_tourney']}' id='button' accesskey='s'></td>
		</tr>
	</form>
  </table>
 </td>
</tr>
</table>
</div>
<br />
EOF;
}
function tournament_row($rowinfo) {
global $ibforums;
return <<<EOF
	<tr>
		<td align="center" class="alt1">{$rowinfo['link']}</td>
		<td align="center" class="alt1">{$rowinfo['gtitle']} {$ibforums->lang['tournament']}</td>
		<td align="center" class="alt1">{$rowinfo['numplayers']}</td>
		<td align="center" class="alt1">{$rowinfo['champion']}</td>
		<td align="center" class="alt1">{$rowinfo['datestarted']}</td>
	</tr>
EOF;
}

function tourney_stop(){
return <<<EOF
	<br /><br />
			</td>
		</tr>
	</table>
	</div><br /><br />
EOF;
}

function tourneygame($game,$top,$tid) {
global $ibforums;
return <<<EOF
	<!-- Add JS function for pnFlashGames Updated games component (Not Sure if this is needed yet) -->
	<script type="text/javascript">
	<!--
	function refreshScores()
	{		
	}
	//-->
	</script>

	<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
  		<tr>
  			<td width="75%" align="center">
            	<div class="tborder" align="center">
                <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
		{$game['ginfotxt']}
                    <tr>
                    	<td class="alt1" align="center">
  				<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" width="{$game['gwidth']}" height="{$game['gheight']}">
  				<param name="menu" value="false" />
				<param name="movie" value="./arcade/{$game['gname']}.swf?tgame=1&amp;pn_gid={$game['gid']}&amp;pn_license={$game['license']}&amp;pn_checksum={$game['checksum']}&amp;pn_domain={$game['domain']}&amp;pn_uname={$game['username']}" />
                                <param name="type" value="application/x-shockwave-flash" />
                                <param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer/" />
                                <param name="bgcolor" value="#{$game['bgcolor']}" />
  				<param name="quality" value="high" />
  				<param name="menu" value="false" />
                                <param name="width" value="{$game['gwidth']}" />
                                <param name="height" value="{$game['gheight']}" />
                                <param name="flashvars" value="location=./&amp;gamename={$game['gname']}&amp;hash={$game['hash']}" />
                                <embed src="./arcade/{$game['gname']}.swf?tgame=1&amp;pn_gid={$game['gid']}&amp;pn_license={$game['license']}&amp;pn_checksum={$game['checksum']}&amp;pn_domain={$game['domain']}&amp;pn_uname={$game['username']}" width="{$game['gwidth']}" height="{$game['gheight']}" bgcolor="#{$game['bgcolor']}" quality="high" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer/" flashvars="location=./&amp;gamename={$game['gname']}" menu="false"></embed>
                                <noembed>{$ibforums->lang['no_embed']}{$game['gtitle']}</noembed>
				</object>
				<iframe src="holdsession.php?act=arcade&do=play&gameid=$game[gid]" width='0' height='0' frameborder='0'></iframe>
                        </td>
                    </tr>
                </table>
                </div>
            </td>
  			<td valign="top" align="left" width="25%">
            	<div class="tborder">
  				<table width="100%" border="0" cellspacing="1" cellpadding="4">
  					<tr>
						<td width="100%" align="center" class="tcat">{$game['gtitle']}</td>
					</tr>
					<tr>
                    	<td align="center" class="alt2"><img src="./arcade/images/{$game['gname']}1.gif" alt="" width="50" height="50" /></td>
                    </tr>
					<tr>
                    	<td align="center" class="alt2">{$top['playertext']}<br /><b>{$top['name']}</b></td>
                    </tr>
					<tr>
                    	<td align="center" class="alt2">{$top['scoretext']}<br /><b>{$top['rungscore']}</b></td>
                    </tr>
					{$game['extra']}
				</table>
                </div>
            </td>
  		</tr>
	</table>
	<br /><br />
	<iframe src="holdsession.php?act=arcade&do=play&gameid=$game[gid]" width='0' height='0' frameborder='0'></iframe>
EOF;
}

function tourney_start($headertext, $tinfo, $champion) {
global $ibforums;
return <<<EOF
	<div class="tborder">
	<table border="0" cellspacing="1" cellpadding="4" width="100%">
	<tr>
		<td width="100%" align="center" class="thead">{$headertext}</td>
	</tr>
	<tr>
		<td width="100%" align="center" class="tcat" colspan="4">
		{$tinfo['gtitle']}{$ibforums->lang['tourney_started']}{$tinfo['datestarted']}<br />
		<div class="smallfont">
		{$tinfo['hilotext']}<br />
		{$tinfo['jackpottxt']}
		{$tinfo['limit1']}
		{$tinfo['limit2']}
		</div>
		</td>
	</tr>
	<tr>
    	<td>
        	<br /><br />
			<table border="1" width="100" height="100" align="center" bordercolor="#000" cellspacing="0" cellpadding="0">
				<tr>
                	<td align="center">
						<img src="./arcade/images/crown2.gif" alt="" /><br />
						<big><b>{$champion}</b></big>
					</td>
                </tr>
			</table>
EOF;
}

function tournament_middle() {
global $ibforums;
return <<<EOF
	</table>
	</div>
	<br /><br />
	<div class="tborder">
	<table border="0" cellspacing="1" cellpadding="4" width="100%">
		<tr>
			<td width="100%" align="center" class="thead" colspan="5">{$ibforums->lang['unactive_tournaments']}</td>
		</tr>
		<tr>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['legend_of_zelda']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['tourney_title']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['num_of_players']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['champion']}</td>
			<td width="20%" align="center" class="tcat">{$ibforums->lang['date_started']}</td>
		</tr>
EOF;
}

function active_users($users) {
global $ibforums;
return <<<EOF
	<div class="tborder" style="padding: 0px;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    	<tr>
        	<td class="tcat">{$ibforums->lang['header']}</td>
        </tr>
        <tr>
        	<td class="alt2" style="padding: 0px 5px 5px 5px; width: 100%">
            	<fieldset class="legend" style="padding: 6px; margin: 0px 0px 0px 0px">
                	<legend>{$ibforums->lang['active']}</legend>
                    <br /><div class="smallfont">{$users}</div>
                </fieldset>
            </td>
        </tr>
    </table>
    </div>
    <br /><br />
EOF;
}

function mod_main($header, $links , $content, $notes, $modnotes_extra) {
global $ibforums;
return <<<EOF
	<noscript>
    	<div class="tborder" style="border-bottom: none;">
    	<table border="0" cellpadding="3" cellspacing="0" width="100%">
    		<tr>
            	<td class="alt1" align="center">
                	<span class="red">{$ibforums->lang['warning']}</span>{$ibforums->lang['no_js']}
                </td>
            </tr>
        </table>
        </div>
    </noscript>
    <div class="tborder">
    <table border="0" cellpadding="0" cellspacing="6" width="100%">
    	<tr>
        	<td width="25%" valign="top">
        		<div class="tborder">
        		<table border="0" cellpadding="4" cellspacing="0" width="100%">
                	<tr>
                		<td class="thead">{$ibforums->lang['options']}</td>
            		</tr>
            		<tr>
                		<td class="alt1">
                        	{$links['home']}
                    		{$links['scores']}
                    		{$links['comments']}
                            {$links['tourney']}
                    		{$links['champs']}
                		</td>
            		</tr>
        		</table>
        		</div>
        		<br />
        		<div class="tborder">
        		<table border="0" cellpadding="0" cellspacing="0" width="100%">
                	<tr>
                		<td class="thead">{$ibforums->lang['mod_notes']}</td>
            		</tr>
            		<tr>
                		<td class="alt1" align="center">
                        	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp" method="post">
                        		{$modnotes_extra}
                        		<textarea rows="8" cols="25" class="textinput" name="notes">{$notes}</textarea><br /><br />
                        		<input type="submit" value="Update" class="forminput" />
                    		</form>
               			</td>
            		</tr>
        		</table>
        		</div>
            </td>
            <td width="*" valign="top">
        		<div class="tborder">
        		<table border="0" cellpadding="4" cellspacing="0" width="100%">
                	<tr>
                		<td class="thead">{$header}</td>
            		</tr>
            		<tr>
                		<td class="alt2">{$content}</td>
            		</tr>
        		</table>
        		</div>
            </td>
        </tr>
    </table>
    </div>
    <br />
EOF;
}

function edit_link($sid) {
global $ibforums;
return <<<EOF
   <td align='center' class='alt1'>
   		<a href="#" onclick="window.open('{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;edit={$sid}','comment_edit','height=150,width=400'); return false;">{$ibforums->lang['edit_comment']}</a>
   </td>
EOF;
}

function rate_link($gid) {
global $ibforums;
return <<<EOF
	<a href="#" onclick="window.open('{$ibforums->base_url}act=Arcade&amp;do=rate&amp;gid={$gid}','comment_edit','height=150,width=400'); return false;">{$ibforums->lang['rate_game']}</a>
EOF;
}

function splash($scores , $comments , $champs , $colspans, $colspanc, $remove , $edit, $version) {
global $ibforums;
return <<<EOF
    <div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="alt1" align="center">{$ibforums->lang['current_version']}{$ibforums->lang['dev_version']}{$ibforums->lang['your_version']}<b>{$version}</b></td>
        </tr>
    </table>
    </div><br />
    <div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat" colspan="{$colspans}">{$ibforums->lang['last_5_s']}</td>
        </tr>
        <tr>
        	<td align="center" class="alt2">{$ibforums->lang['game']}</td>
            <td align="center" class="alt2">{$ibforums->lang['username']}</td>
            <td align="center" class="alt2">{$ibforums->lang['score']}</td>
            <td align="center" class="alt2">{$ibforums->lang['time']}</td>
            {$remove}
        </tr>
        {$scores}
    </table>
    </div><br />
    <div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat" colspan="4">{$ibforums->lang['last_5_champ']}</td>
        </tr>
        <tr>
        	<td align="center" class="alt2">{$ibforums->lang['game']}</td>
            <td align="center" class="alt2">{$ibforums->lang['username']}</td>
            <td align="center" class="alt2">{$ibforums->lang['score']}</td>
            <td align="center" class="alt2">{$ibforums->lang['time']}</td>
        </tr>
        {$champs}
    </table>
    </div><br />
    <div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat" colspan="{$colspanc}">{$ibforums->lang['last_5_c']}</td>
        </tr>
        <tr>
        	<td align="center" class="alt2">{$ibforums->lang['game']}</td>
            <td align="center" class="alt2">{$ibforums->lang['username']}</td>
            <td align="center" class="alt2">{$ibforums->lang['comment']}</td>
            <td align="center" class="alt2">{$ibforums->lang['time']}</td>
            {$edit}
        </tr>
        {$comments}
    </table>
    </div>
EOF;
}

function search_form($do,$game_select,$extra) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do={$do}" method="post">
    <input type="hidden" name="is_submitted" value="Arcade modCP, another idea Outlaw will steal." />
    <div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat" colspan="2">{$ibforums->lang['search_for']}</td>
        </tr>
        <tr>
        	<td class="alt1" style="padding: 3px;" colspan="2">{$ibforums->lang['omit']}</td>
        </tr>
        <tr>
        	<td align="left" class="alt1" style="width: 40%;">{$ibforums->lang['by_user']}</td>
            <td align="center" class="alt1" style="width: 60%;"><input type="text" class="textinput" name="username" size="50" /></td>
        </tr>
        <tr>
        	<td class="alt1" align="left">{$ibforums->lang['in_game']}</td>
            <td class="alt1" align="center">
                <select size="5" name="in_game[]" multiple="multiple" style="width: 60%;" class="codebuttons">
                        {$game_select}
                </select>
            </td>
        </tr>
        {$extra}
        <tr>
        	<td class="alt1" align="left">{$ibforums->lang['time_frame']}</td>
            <td class="alt1" align="center">
            	<select name="age_old_new" class="codebuttons">
                	<option value="<">{$ibforums->lang['older']}</option>
                    <option value=">">{$ibforums->lang['newer']}</option>
                </select>&nbsp;
                <input type="text" name="age_num" size="5" class="textinput" />&nbsp;
                <select name="age_hdm" class="codebuttons">
                	<option value="3600">{$ibforums->lang['hour']}</option>
                    <option value="86400">{$ibforums->lang['day']}</option>
                    <option value="2592000">{$ibforums->lang['month']}</option>
                </select>
            </td>
        </tr>
        <tr>
        	<td class="alt1" align="left">{$ibforums->lang['limit']}</td>
            <td class="alt1" align="center"><input type="text" name="limit" size="4" class="textinput" /></td>
        </tr>
        <tr>
        	<td class="alt1" style="padding: 3px;" colspan="2" align="center"><input type="submit" value={$ibforums->lang['acp_search']} class="forminput" /></td>
        </tr>
    </table>
    </div>
    <form>
EOF;
}

function score_extra() {
global $ibforums;
return <<<EOF
    <tr>
    	<td class="alt1" align="left">{$ibforums->lang['score_is']}</td>
        <td class="alt1" align="center">
            <select name="greater_less" class="codebuttons">
            	<option value=">">{$ibforums->lang['greater']}</option>
            	<option value="<">{$ibforums->lang['less']}</option>
            </select>&nbsp;
            <input type="text" name="score_minmax" size="8" class="textinput" />
        </td>
    </tr>
EOF;
}

function comment_extra() {
global $ibforums;
return <<<EOF
    <tr>
        <td class="alt1" align="left">{$ibforums->lang['comment_is']}</td>
        <td class="alt1" align="center">
        	<input type="text" name="comment_length" size="8" class="textinput" />
        </td>
    </tr>
EOF;
}

function score_results($scores) {
global $ibforums;
return <<<EOF
    <script language="JavaScript" src="./arcade/modules/checkbox.js"></script>
    <form name="score_result_form" action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=multi_del" method="post">
    <div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat" colspan="5">{$ibforums->lang['results']}</td>
        </tr>
        <tr>
        	<td align="center" class="alt2">{$ibforums->lang['game']}</td>
            <td align="center" class="alt2">{$ibforums->lang['username']}</td>
            <td align="center" class="alt2">{$ibforums->lang['score']}</td>
            <td align="center" class="alt2">{$ibforums->lang['time']}</td>
            <td class="alt2">&nbsp;</td>
        </tr>
        {$scores}
        <tr>
        	<td class="alt2" colspan="6" align="center">
            	[ <a href="#" onclick="CheckAll(); return false;">{$ibforums->lang['check_all']}</a> | <a href="#" onclick="unselect_all(); return false;">{$ibforums->lang['uncheck_all']}</a> ]
        	</td>
        </tr>
        <tr>
        	<td class="pformstrip" colspan="6" align="center">
            	<input type="submit" value="{$ibforums->lang['remove_selected']}" name="how_many" class="forminput" />
            </td>
        </tr>
    </table>
    </div>
    </form>
EOF;
}

function score_row($score) {
global $ibforums;
return <<<EOF
	<tr>
    	<td align="center" class="alt1">{$score['game']}</td>
        <td align="center" class="alt1"><a href="./member.php?$session[sessionurl]&amp;u={$score['mid']}">{$score['name']}</a> ({$score['ip']})</td>
        <td align="center" class="alt1">{$score['score']}</td>
        <td align="center" class="alt1">{$score['datescored']}</td>
        {$score['remove']}
    </tr>
EOF;
}

function comment_results($comments) {
global $ibforums;
return <<<EOF
	<div class="tborder">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat" colspan="5">{$ibforums->lang['results']}</td>
        </tr>
        <tr>
        	<td align="center" class="alt2">{$ibforums->lang['game']}</td>
            <td align="center" class="alt2">{$ibforums->lang['username']}</td>
            <td align="center" class="alt2">{$ibforums->lang['comment']}</td>
            <td align="center" class="alt2">{$ibforums->lang['time']}</td>
            <td align="center" class="alt2">{$ibforums->lang['edit_comment']}</td>
        </tr>
        {$comments}
    </table>
    </div>
EOF;
}

function score_row_result($score) {
global $ibforums;
return <<<EOF
	<tr class="alt1">
    	<td align="center">{$score['game']}</td>
        <td align="center"><a href="./member.php?$session[sessionurl]&amp;u={$score['mid']}">{$score['name']}</a> ({$score['ip']})</td>
        <td align="center">{$score['score']}</td>
        <td align="center">{$score['datescored']}</td>
        <td align="center"><input type="checkbox" name="scores[]" value="{$score['s_id']}" class="forminput" onclick="cca(this);" /></td>
    </tr>
EOF;
}

function comment_row_result($comment) {
global $ibforums;
return <<<EOF
	<tr class="alt1">
    	<td align="center">{$comment['game']}</td>
        <td align="center"><a href="./member.php?$session[sessionurl]&amp;u={$comment['mid']}">{$comment['name']}</a> ({$comment['ip']})</td>
        <td align="center">{$comment['comment']}</td>
        <td align="center">{$comment['datescored']}</td>
        {$comment['edit']}
    </tr>
EOF;
}

function comment_row($comment) {
global $ibforums;
return <<<EOF
	<tr>
    	<td align="center" class="alt1">{$comment['game']}</td>
        <td align="center" class="alt1"><a href="./member.php?$session[sessionurl]&amp;u={$comment['mid']}">{$comment['name']}</a> ({$comment['ip']})</td>
        <td align="center" class="alt1">{$comment['comment']}</td>
        <td align="center" class="alt1">{$comment['datescored']}</td>
        {$comment['edit']}
    </tr>
EOF;
}

function champ_row($champ) {
global $ibforums;
return <<<EOF
	<tr>
        <td align="center" class="alt1">{$champ['champ_gtitle']}</td>
        <td align="center" class="alt1"><a href="./member.php?$session[sessionurl]&amp;u={$champ['champ_mid']}">{$champ['champ_name']}</a></td>
        <td align="center" class="alt1">{$champ['champ_score']}</td>
        <td align="center" class="alt1">{$champ['champ_date']}</td>
    </tr>
EOF;
}

function edit_comment($sid , $comment , $button) {
global $ibforums;
return <<<EOF
    <div class="tborder" align="center">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat">{$ibforums->lang['editcomment']}</td>
        </tr>
        <tr>
        	<td class="pformright" align="center">
                <form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;edit={$sid}" method="post">
                	<input type="text" size="50" name="comment" class="textinput" {$comment} /><br /><br />
                    <input type="submit" value="{$ibforums->lang['edit_comment']}" class="forminput" {$button} />
                </form>
            </td>
        </tr>
    </table>
    </div><br />
EOF;
}

function rating($game,$pagereload) {
global $ibforums;
return <<<EOF
    <div class="tborder" align="center">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    	<tr>
        	<td class="tcat">{$ibforums->lang['now_rating']}{$game['gtitle']}</td>
        </tr>
        <tr>
        	<td class="pformright" align="center">
                {$ibforums->lang['what_rating']}
                <form action="{$ibforums->base_url}act=Arcade&amp;do=rate" method="post">
                	<input type="hidden" name="gid" value="{$game['gid']}" />
                	<input type="hidden" name="pagereload" value="{$pagereload}" />
                	<input type="hidden" name="scored" value="{$pagereload}" />
                    <select name="rating" class="codebuttons">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3" selected="selected">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select><br /><br />
                    <input type="submit" class="forminput" value="{$ibforums->lang['rateit']}" />
                </form>
            </td>
        </tr>
    </table>
    </div><br />
EOF;
}

function rating_general($text,$pagereload) {
global $ibforums;

$output="";

if ($pagereload!=true)
{
	$output .= "    <script language=\"javascript\" type=\"text/javascript\">
    <!--
        opener.location.replace(window.opener.location.href);
	-->
	</script>";
}

$output .= "	<div class=\"tborder\" align=\"center\">
	<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">
        <tr>
        	<td class=\"tcat\">{$ibforums->lang['rate_message']}</td>
        </tr>
        <tr>
        	<td class=\"alt1\">{$text}</td>
        </tr>
    </table>
    </div><br />";

return $output;

}

function cat_pass($cid) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade" method="post">
    <input type="hidden" name="the_cat" value="{$cid}" />
    <div class="tborder" align="center">
    <table border="0" cellpadding="3" cellspacing="0" width="100%">
        <tr>
        	<td class="tcat">{$ibforums->lang['cat_pass']}</td>
        </tr>
        <tr>
        	<td class="alt1">
                {$ibforums->lang['cat_pass_msg']}<br /><br />
                <input type="password" name="cat_pass" class="textinput" /><br />
                <input type="submit" value="Submit" class="forminput" /><br /><br />
                {$ibforums->lang['cat_pass_notice']}
            </td>
        </tr>
    </table>
    </div>
    </form><br />
EOF;
}

function close_win() {
return <<<EOF
	<script language="javascript" type="text/javascript">
    <!--
    	opener.location.replace(window.opener.location.href);
        window.close();
    -->
    </script>
EOF;
}

function favorites($game_list) {
global $ibforums;
return <<<EOF
	<div class="tborder">
    <table cellpadding="3" cellspacing="1" border="0" width="100%">
        <tr>
        	<td class="thead" colspan="4" align="center">{$ibforums->lang['my_favs']}</td>
        </tr>
        {$game_list}
    </table>
    </div><br />
EOF;
}

function user_settings($user, $selected) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=settings" method="post">
    <input type="hidden" name="is_submitted" value="Yeah I'm submitted; what are you going to do about it" />
    <div align="center">
    <div class="tborder" align="left" style="width: 75%;">
    <table width="100%" border="0" cellpading="3" cellspacing="1">
        <tr>
        	<td class="thead" colspan="2">{$ibforums->lang['your_settings']}</td>
        </tr>
        <tr>
        	<td class="pformleft" style="width: 60%;">{$ibforums->lang['sorting_def']}</td>
            <td class="pformright" style="width: 40%;">
                <select class="codebuttons" name="user_sort">
                	<option value="0">{$ibforums->lang['the_default']}</option>
                    <option value="gtitle" {$selected['sort']['gtitle']}>{$ibforums->lang['gname']}</option>
                    <option value="gcount" {$selected['sort']['gcount']}>{$ibforums->lang['times_p']}</option>
                    <option value="gwords" {$selected['sort']['gwords']}>{$ibforums->lang['description']}</option>
                    <option value="g_rating" {$selected['sort']['g_rating']}>{$ibforums->lang['rating_s']}</option>
				<option value="added" {$selected['sort']['added']}>{$ibforums->lang['dateadded']}</option>
                </select>
                <select class="codebuttons" name="user_order">
                	<option value="0">{$ibforums->lang['the_default']}</option>
                    <option value="ASC" {$selected['order']['ASC']}>{$ibforums->lang['asc']}</option>
                    <option value="DESC" {$selected['order']['DESC']}>{$ibforums->lang['desc']}</option>
                </select>
            </td>
        </tr>
        <tr>
        	<td class="pformleft">{$ibforums->lang['games_per_page']}</td>
            <td class="pformright">
                <select name="g_per_page" class="forminput">
                	<option value="0">{$ibforums->lang['the_default']}</option>
                    {$user['choices_g']}
                </select>
            </td>
        </tr>
        <tr>
        	<td class="pformleft">{$ibforums->lang['scores_per_page']}</td>
            <td class="pformright">
                <select name="s_per_page" class="forminput">
                	<option value="0">{$ibforums->lang['the_default']}</option>
                    {$user['choices_s']}
                </select>
            </td>
        </tr>
        {$user['pm']}
        {$user['skins']}
        {$user['cat_row']}
        <tr>
        	<td class="pformstrip" colspan="2" align="center">
                <input type="submit" value="{$ibforums->lang['update_me']}" class="forminput" />
            </td>
        </tr>
    </table>
    </div>
    </div>
    </form><br /><br />
EOF;
}

function user_cat_row($cats) {
global $ibforums;
return <<<EOF
	<tr>
    	<td class="pformleft">{$ibforums->lang['default_cat']}</td>
        <td class="pformright">
            <select name="def_cat" class="forminput">
                <option value="0">{$ibforums->lang['the_default']}</option>
                {$cats}
            </select>
        </td>
    </tr>
EOF;
}

function user_skin_row($skin) {
global $ibforums;
return <<<EOF
	<tr>
        <td class="pformleft">{$ibforums->lang['skin_to_use']}</td>
        <td class="pformright">
            <select name="skin_use" class="forminput">
                <option value="0">{$ibforums->lang['the_default']}</option>
                <option {$skin['1']} value="1">ibProArcade</option>
                <option {$skin['2']} value="2">v3Arcade</option>
            </select>
        </td>
    </tr>
EOF;
}

function user_pm_row($pm) {
global $ibforums;
if ($pm == 1)
{
	$selected_yes = "selected=\"selected\"";
	$selected_no = "";
}
else
{
	$selected_no = "selected=\"selected\"";
	$selected_yes = "";
}
return <<<EOF
	<tr>
    	<td class="pformleft">{$ibforums->lang['pm_notification']}</td>
        <td class="pformright">
            <select name="pm" class="forminput">
            	<option value="0" {$selected_no} >{$ibforums->lang['acp_no']}</option>
            	<option value="1" {$selected_yes} >{$ibforums->lang['acp_yes']}</option>
            </select>
        </td>
    </tr>
EOF;
}

function champs_update($game_select) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=champs" method="post">
    <input type="hidden" name="is_submitted" value="AFK, tornado" />
    <div class="tborder">
    <table width="100%" border="0" cellpading="3" cellspacing="1">
    	<tr>
        	<td class="thead" colspan="2">{$ibforums->lang['update_champs']}</td>
        </tr>
        <tr>
        	<td class="alt1" style="width: 60%;">{$ibforums->lang['over_100']}</td>
            <td class="alt1" style="width: 40%;">
            	<select size="5" name="in_game[]" multiple="multiple" style="width: 60%;" class="codebuttons">
                	{$game_select}
                </select>
            </td>
        </tr>
        <tr>
        	<td class="alt1" colspan="2" align="center">
            	<input type="submit" value="{$ibforums->lang['update_champs']}" class="forminput" />
            </td>
        </tr>
    </table>
    </div>
    </form><br /><br />
EOF;
}

function report_main($html , $name) {
global $ibforums;
return <<<EOF
	<table border="0" cellpadding="0" cellspacing="6" width="100%">
    	<tr>
        	<td width="75%" valign="top">
                <div class="tborder">
                <table border="0" cellpadding="4" cellspacing="0" width="100%">
                	<tr>
                    	<td width="100%" align="center" nowrap="nowrap" class="thead" colspan="5">{$ibforums->lang['report_for']}{$name}</td>
                    </tr>
                    <tr>
                    	<td width="25%" align="center" nowrap="nowrap" class="tcat">{$ibforums->lang['game']}</td>
                            <td width="10%" align="center" nowrap="nowrap" class="tcat">{$ibforums->lang['score']}</td>
                            <td width="20%" align="center" nowrap="nowrap" class="tcat">{$ibforums->lang['rank']}</td>
                            <td width="10%" align="center" nowrap="nowrap" class="tcat">{$ibforums->lang['times_p']}</td>
                            <td width="20%" align="center" nowrap="nowrap" class="tcat">{$ibforums->lang['high_score_age']}</td>
                    </tr>
                    {$html['rank_row']}
                </table>
                </div>
            </td>
            <td width="*" valign="top">
            	<div class="tborder">
                <table border="0" cellpadding="4" cellspacing="0" width="100%" class="alt1">
                	<tr>
                    	<td width="100%" align="center" class="thead" colspan="2">{$ibforums->lang['details']}{$name}</td>
                    </tr>
                    {$html['rank_detail_row']}
            	</table>
                </div>
            </td>
        </tr>
    </table>
    <br />
EOF;
}

function rankrow($rowcol,$game,$score,$rank,$tp,$age) {
return <<<EOF
	<tr class="alt1">
        <td width="25%" align="center" class="{$rowcol}">{$game}</td>
        <td width="10%" align="center" class="{$rowcol}">{$score}</td>
        <td width="20%" align="center" class="{$rowcol}">{$rank}</td>
        <td width="10%" align="center" class="{$rowcol}">{$tp}</td>
        <td width="20%" align="center" class="{$rowcol}">{$age}</td>
    </tr>
EOF;
}

function rankdetailrow($rowcol,$title,$detail) {
global $ibforums;
return <<<EOF
	<tr>
        <td width="80%" align="right" class="{$rowcol}" nowrap>{$title}</td>
        <td width="20%" align="left" class="{$rowcol}">{$detail}</td>
    </tr>
EOF;
}

function no_favs() {
global $ibforums;
return <<<EOF
	<tr>
        <td class="alt2" align="center"><span style="font-weight: bold; font-size: 10pt;">{$ibforums->lang['no_favs']}</td>
    </tr>
EOF;
}

function touney_players($players , $tid) {
global $ibforums;
return <<<EOF
	<script language="javascript" type="text/javascript">
    <!--
    	function set_player(the_id,the_name)
        {
        	var the_form = document.t3hform;
            the_form.username.value = the_name;
        	the_form.userid.value = the_id;
            the_form.replaceid.disabled = false;
            the_form.button.disabled = false;

            return false;
        }
    -->
    </script>
	<div class="tborder">
    <table cellspacing="1" cellpadding="3" width="100%" border="0">
    	<tr>
        	<td class="thead" colspan="2">{$ibforums->lang['tourney_player']}</td>
        </tr>
        {$players}
    </table>
    </div>
    <br />
    <div class="tborder">
    <table cellspacing="1" cellpadding="3" width="100%" border="0">
    	<tr>
        	<td class="alt1" colspan="2">
            	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=replace" method="post" name="t3hform">
                <input type="hidden" name="userid" value="" />
                <input type="hidden" name="tid" value="{$tid}" />
            	<div align="center">{$ibforums->lang['user_to_r']}<input type="text" name="username" readonly="readonly" value="" class="textinput" /></div>
                <div align="center">{$ibforums->lang['replace_with']}<input type="text" name="replaceid" value="" disabled="disabled" size="4" class="textinput" /></div>
            	<div align="center"><input type="submit" name="button" class="forminput" value="{$ibforums->lang['replace_user']}" disabled="disabled" /></div>
            	</form>
            </td>
        </tr>
    </table>
    </div>
EOF;
}

function new_tourney($games) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=newt" method="post">
	<div class="tborder">
    <table cellpadding="3" cellspacing="1" border="0" width="100%">
    	<tr>
        	<td class="thead" colspan="2">{$ibforums->lang['create_new_t']}</td>
        </tr>
        <tr>
        	<td class="pformleft" style="width: 40%;">{$ibforums->lang['amount_of_p_g']}</td>
        	<td class="pformright" style="width: 60%;">
                <select name="the_game" class="forminput">
                	{$games}
                </select>
                &nbsp;&nbsp;
            	<select name="player_amount" class="forminput">
                	<option value="2">{$ibforums->lang['two_players']}</option>
                    <option value="4">{$ibforums->lang['four_players']}</option>
                    <option value="8">{$ibforums->lang['eight_players']}</option>
                </select>
		&nbsp;&nbsp;
		<select name="tries" class="forminput">
			<option value="1">1 {$ibforums->lang['try']}</option>
			<option value="2">2 {$ibforums->lang['tries']}</option>
			<option value="3" selected="selected">3 {$ibforums->lang['tries']}</option>
			<option value="4">4 {$ibforums->lang['tries']}</option>
			<option value="5">5 {$ibforums->lang['tries']}</option>
		</select>

            </td>
        </tr>
        <tr>
        	<td class="pformstrip" colspan="2" align="center">
            	<input type="submit" class="forminput" value="{$ibforums->lang['create_t']}" />
            </td>
        </tr>
    </table>
    </div>
    </form>
EOF;
}

function new_tourney_id($header, $rows, $hiddens) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=add_t_confirm" method="post">
    <input type="hidden" name="player_amount" value="{$hiddens['player_amount']}" />
    <input type="hidden" name="gid" value="{$hiddens['gid']}" />
    <input type="hidden" name="tries" value="{$hiddens['tries']}" />
    <input type="hidden" name="pmnotify" value="{$hiddens['pmnotify']}" />
	<div class="tborder">
    <table border="0" width="100%" cellpadding="3" cellspacing="1">
    	<tr>
        	<td class="thead" align="center" colspan="2">{$header}</td>
        </tr>
        <tr>
        	<td class="alt2" colspan="2">{$ibforums->lang['enter_user_id']}</td>
        </tr>
        {$rows}
        <tr>
        	<td class="pformstrip" colspan="2" align="center">
            	<input type="submit" value="{$ibforums->lang['get_users']}" class="forminput" />
            </td>
        </tr>
    </table>
    </div>
    </form>
EOF;
}

function new_tourney_id_row($counter) {
global $ibforums;
return <<<EOF
	<tr>
    	<td class="pformleft" style="width: 60%;">{$ibforums->lang['player_t']}{$counter}</td>
        <td class="pformright" style="width: 40%;">
        	<input type="text" class="textinput" name="users[]" value="" size="4" />
        </td>
    </tr>
EOF;
}
function cat_desc($desc) {
global $ibforums;
return <<<EOF
<tr>
<td colspan="4" class="alt1">
<fieldset class='fieldset' style='margin:0px 0px 0px 0px'>
<legend>Category Description&nbsp;</legend>
<div style='padding:0px'>
<table cellpadding='0' cellspacing='0' border='0' align='center' width='100%'>
<tr>
<td width='100%'>
<table cellpadding='2' cellspacing='1' border='0' width='100%'>
<tr>
<td align='center' class="alt1">{$desc}</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</fieldset>
</td>
</tr>
EOF;
}
function new_tourney_users($users , $hiddens) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=do_add_t" method="post">
    <input type="hidden" name="player_amount" value="{$hiddens['player_amount']}" />
    <input type="hidden" name="gid" value="{$hiddens['gid']}" />
    <input type="hidden" name="tries" value="{$hiddens['tries']}" />
    <input type="hidden" name="pmnotify" value="{$hiddens['pmnotify']}" />
	<div class="tborder">
    <table border="0" width="100%" cellpadding="3" cellspacing="1">
    	<tr>
        	<td class="thead">{$ibforums->lang['create_new_t']}</td>
        </tr>
        <tr>
        	<td class="alt2">{$ibforums->lang['confirm_users']}</td>
        </tr>
        {$users}
        <tr>
        	<td class="pformstrip" align="center"><input type="submit" class="forminput" value="{$ibforums->lang['confirm_r']}" /></td>
        </tr>
    </table>
    </div>
    </form>
EOF;
}

function new_tourney_users_name($user , $dropdown) {
global $ibforums;
return <<<EOF
	<tr>
		<td class="alt1" align="center">
    		<a href="./member.php?$session[sessionurl]&amp;u={$user['id']}" target="_blank">{$user['name']}</a>&nbsp;
        	<select name="{$user['id']}_user" class="forminput">
        		{$dropdown}
        	</select>
    	</td>
    </tr>
EOF;
}

function t_player_row($player) {
global $ibforums;
return <<<EOF
    <tr>
    	<td class="alt1">
        	<a href="./member.php?$session[sessionurl]&amp;u={$player['mid']}">{$player['name']}</a>
        </td>
        <td class="alt2">
        	<a href="#" onclick="set_player('{$player['mid']}','{$player['name']}'); return false;">{$ibforums->lang['replace_user']}</a>
        </td>
    </tr>
EOF;
}

function confirm_replace($text , $hiddens) {
global $ibforums;
return <<<EOF
	<form action="{$ibforums->base_url}act=Arcade&amp;module=modcp&amp;do=tourney&amp;code=replace" method="post">
    <input type="hidden" name="confirmed" value="1" />
    <input type="hidden" name="tid" value="{$hiddens['tid']}" />
    <input type="hidden" name="oldmid" value="{$hiddens['oldmid']}" />
    <input type="hidden" name="newmid" value="{$hiddens['newmid']}" />
	<div class="tborder">
    <table cellspacing="1" cellpadding="3" border="0" width="100%">
    	<tr>
        	<td class="thead" align="center">{$ibforums->lang['replace_user']}</td>
        </tr>
        <tr>
        	<td class="alt2">{$ibforums->lang['make_sure']}</td>
        </tr>
        <tr>
        	<td class="alt1" align="center">{$text}</td>
        </tr>
        <tr>
        	<td class="pformstrip" align="center"><input type="submit" value="{$ibforums->lang['confirm_r']}" class="forminput" /></td>
        </tr>
    </table>
    </div>
EOF;
}

function leagueheader() {
global $ibforums;
return <<<EOF
<div class="tborder">
<div class="tcat" align="center">{$ibforums->lang['leaderboard']}</div>
<table width='100%' border='0' cellspacing='1' cellpadding='4'>
  <tr>
    <td width='14%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['rank']}</td>
    <td width='36%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['username']}</td>
    <td width='26%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['average_rank']}</td>
    <td width='24%' align='center' nowrap='nowrap' class='tcat'>{$ibforums->lang['score']}</td>
  </tr>
EOF;
}

function leaguerow($rowcol,$rank,$name,$avgrank,$points) {
global $ibforums;
return <<<EOF
<tr>
<td align='center' nowrap='nowrap' class='alt1'>$rank</td>
<td align='center' nowrap='nowrap' class='alt1'>$name</td>
<td align='center' nowrap='nowrap' class='alt1'>$avgrank</td>
<td align='center' nowrap='nowrap' class='alt1'>$points</td>
</tr>
EOF;
}

function cat_select($cats) {

global $ibforums;
return <<<EOF
	<center>{$ibforums->lang['choose_cat']}:
		<select name="lcat" class="forminput">
                {$cats}
            </select>
EOF;
}

function error($e) {
global $ibforums;
return <<<EOF
    <div class="tborder">
    <table cellspacing="1" cellpadding="3" width="100%" border="0">
    	<tr>
        	<td class="thead">{$ibforums->lang['error']}</td>
        </tr>
        <tr>
        	<td class="alt1" align="center">{$e}</td>
        </tr>
    </table>
    </div>
EOF;
}

function copyright($version,$country,$BFL) {
//
// if you had BRANDING FREE before ibProArcade v2.7.1+
// please contact me via mail -> ibproarcade@gmail.com
// to get the new instructions for BRANDING FREE - more easy now!
// !! DO NOT CHANGE THE CODE IN HERE ANYMORE !!
//
$text = "
	<div align='center' style='font-size: 8pt;'>
    	ibProArcade v{$version}<br />";
if ($country == "de") { $text = $text . "Erweiterte vBulletin-Version &copy; MrZeropage (<a href='http://www.vbulletin-germany.org/forumdisplay.php?f=28' target='_blank' title='www.vbulletin-germany.org'>www.vbulletin-germany.org</a>)<br />"; }
else { $text = $text . "Extended vBulletin-Version &copy; MrZeropage (<a href='http://www.vbulletin.org/forum/forumdisplay.php?f=170' target='_blank' title='www.vbulletin.org'>www.vbulletin.org</a>)<br />"; }
$text = $text . "</div><br />";
return ($BFL ? "" : $text);
}

function generalbox($title,$text) {
global $ibforums;
return <<<EOF
	<div class="tborder">
<table width='100%' border='0' cellspacing='0' cellpadding='4'>
	<tr>
	<th width='33%' align='center' class='tcat'>{$title}</th></td>
	</tr>
	<tr>
	<td width='33%' align='center' class='alt2' valign='middle'>{$text}</td>
	</tr>
	</table>
</div>
EOF;
}

}
?>
