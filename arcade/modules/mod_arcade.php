<?php

class mod_arcade
{
	var $settings 		= array();
    	var $user			= array();
    	var $links			= array();
    	var $active			= "";
    	var $extra_links	= "";

	function mod_arcade()
    {
    	global $ibforums, $DB, $std;

        $DB->query("SELECT * FROM ibf_games_settings");
        $this->settings = $DB->fetch_row();

	// ############################################################
	// ### new permission-handling (C) by MrZeropage            ###
	// ### this now works with primary and secondary usergroups ###
	// ############################################################

	if ( intval($ibforums->member['id']) < 1 )
	{
		// this is a guest
		$DB->query("	SELECT	arcade_access, p_require,
					ibpa_cats AS allowed_categories
				FROM	ibf_groups
				WHERE	g_id = 1
				");
		$this->user = $DB->fetch_row();
		$this->user['id'] = 0;
		$this->user['is_admin']	= 0;
		$this->user['max_play'] = 0;
		$this->user['ppd_require'] = 0;

		// Guests can play?
		if ($this->user['arcade_access'] > 2)
		{
			if ($this->user['arcade_access']==3)
			{
				// Guest can play, but no Score-Recording
			}
			else
			{
				// Guest can play, record Scores with Guest-UserID
				$this->user['id']		= $this->user['p_require'];
				$this->user['p_require']	= 0;
				$this->user['arcade_access']	= 2;
			}
		}

	}
	else
	{
		// get userdata
		$DB->query("	SELECT	userid, username, posts, arcade_ban, times_played, is_arcade_mod AS is_mod,
					fav_games AS favs, user_sort, user_order, user_g_pp, user_s_pp, def_g_cat,
					game_skin, arcade_mod_privs, arcade_pmactive,
					usergroupid, membergroupids
				FROM	ibf_members
				WHERE	userid={$ibforums->member['id']}
				");
	        $this->user = $DB->fetch_row();
		$this->user['id'] = $this->user['userid'];

		// setup array and string of primary and all secondary usergroups of this user
		$groups = array();
		$groups[] = $this->user['usergroupid'];

		if ($this->user['membergroupids'] != "")
		{
			$groups = array_merge($groups,explode(',',$this->user['membergroupids']));
		}

		$groupstring = implode(',',$groups);

		// generate array of settings in his groups
		$DB->query("	SELECT	arcade_access, p_require, max_play, ppd_require,
					g_access_cp AS is_admin, ibpa_cats AS allowed_categories
				FROM	ibf_groups
				WHERE	g_id IN (".$groupstring.")
				");

		// reset settings
		$access 	= 0;
		$p_req		= 0;
		$m_play		= 0;
		$ppd_req	= 0;
		$access_cp	= 0;
		$cats		= array();
		$zero_p		= 0;
		$zero_ppd	= 0;
		$zero_max	= 0;

		// now read and compare the settings from those groups
		while ($check = $DB->fetch_row())
		{
			// get highest permission
			if ($access < $check['arcade_access'])
			{
				$access		= $check['arcade_access'];
			}

			// get lowest p_require
			if ($check['p_require'] == 0) { $zero_p = 1; $p_req = 0; }
			if ((($p_req == 0) && ($check['p_require'] > 0)) || ($p_req > $check['p_require']))
			{
				if ($zero_p == 0)
				{
					$p_req		= $check['p_require'];
				}
			}

			// get highest max_play
			if ($check['max_play'] == 0) { $zero_max = 1; $m_play = 0; }
			if (($m_play < $check['max_play']) && ($zero_max == 0))
			{
				$m_play		= $check['max_play'];
			}

			// get lowest ppd_require
			if ($check['ppd_require'] == 0)	{ $zero_ppd = 1; $ppd_req = 0; }
			if ((($ppd_req == 0) && ($check['ppd_require'] > 0)) || ($ppd_req > $check['ppd_require']))
			{
				if ($zero_ppd == 0)
				{
					$ppd_req	= $check['ppd_require'];
				}
			}

			// get highest access_cp
			if ($access_cp < $check['is_admin'])
			{
				$access_cp	= $check['is_admin'];
			}

			// compile/compare categories
			$catstring = $check['allowed_categories'];
			$catarray = array();
			$catarray = explode(',',$catstring);
			foreach ($catarray AS $thiscat)
			{
				if (!in_array($thiscat, $cats))
				{
					$cats[] = $thiscat;
				}
			}
		
		}

		$this->user['arcade_access'] 	= $access;
		$this->user['p_require'] 	= $p_req;
		$this->user['max_play'] 	= $m_play;
		$this->user['ppd_require'] 	= $ppd_req;
		$this->user['is_admin']		= $access_cp;
		$this->user['allowed_categories'] = implode(',',$cats);

		unset($cats);
		unset($catarray);
		unset($groups);
	}

	// ############################################################

        if( $this->settings['auto_prune'] && $this->settings['auto_prune_time'] != 0 )
        {
        	$time = time() - ($this->settings['auto_prune_time']*$this->settings['auto_prune_time2']);
            	$DB->query("DELETE FROM ibf_games_scores WHERE datescored<".$time);

		// scores pruned, now update the champs
		$game_string = "";
		$the_champs = array();

		$DB->query("DELETE FROM ibf_games_champs");

		$game_query = $DB->query("SELECT gid, highscore_type, gtitle FROM ibf_games_list ".$game_string." ORDER by gid");
		while( $game = $DB->fetch_row($game_query) )
		{
        		$order = ($game['highscore_type'] == "high") ? "DESC" : "ASC";

			$DB->query("SELECT s.mid, s.gid, s.name, s.datescored, s.score, s.timespent, g.gtitle
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
                            					'score'		=>	$champ['score'],
								'time'		=>	$champ['timespent'] );
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
                                                                    		'champ_score'	=> $this_champ['score'],
										'champ_time'	=> $this_champ['time'] ) );
				$DB->query("UPDATE ibf_games_champs SET ".$db_string." WHERE champ_gid=".$this_champ['gid']);
                	}
                	else
                	{
        			$db_string = $DB->compile_db_insert_string( array ( 'champ_gid'     => $this_champ['gid'],
                        	                                       		'champ_gtitle'  => $this_champ['gtitle'],
                                                                		'champ_mid'     => $this_champ['mid'],
                                                                		'champ_name'    => $this_champ['name'],
                                                                		'champ_date'    => $this_champ['date'],
                                                                		'champ_score'	=> $this_champ['score'],
										'champ_time'	=> $this_champ['time'] ) );
				$DB->query("INSERT INTO ibf_games_champs (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
            		}
		}
        }

		if( isset($ibforums->input['cat_pass']) )
		{
			$cat = $ibforums->input['the_cat'];
			@setcookie("cat_pass_".$cat, $ibforums->input['cat_pass']);
			$std->boink_it($ibforums->base_url."act=Arcade&amp;cat=".$cat);
		}

	// tournament-automation-system (C) by MrZeropage
	if (($this->settings['tourney_limit1'] > 0) || ($this->settings['tourney_limit2'] > 0))
	{
		// load the language set in AdminCP (by MrZeropage)
		$ibforums->langid = $this->settings['arcade_language'];
		$langfile="lang_Arcade_".$ibforums->langid;
		$ibforums->lang = $std->load_words($ibforums->lang, $langfile, $ibforums->lang_id );
	}

	if ($this->settings['tourney_limit1'] > 0)
	{
		// send Notifications out to the lazy players
		$deadline = time() - $this->settings['tourney_limit1']*86400;

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

		$notify_query = $DB->query("SELECT p.mid, p.tid, t.nbtries, p.timesplayed, p.faceoff, p.rung, t.creat FROM ibf_tournament_players AS p LEFT JOIN ibf_tournaments AS t ON (p.tid=t.tid) LEFT JOIN ibf_tournament_players_statut AS s ON (p.tid=s.tid AND p.mid=s.mid) WHERE p.timesplayed<t.nbtries AND p.notified=0 AND t.demare=1 AND s.statut=0 AND p.timeplayed<".$deadline);
		while ($notify = $DB->fetch_row($notify_query))
		{
			// check if there already is an opponent
			$checkopquery = $DB->query("SELECT mid FROM ibf_tournament_players WHERE tid=".$notify['tid']." AND rung=".$notify['rung']." AND faceoff=".$notify['faceoff']." AND mid<>".$notify['mid']);
			$checkop = $DB->fetch_row($checkopquery);
			if ($checkop['mid']<>0)
			{
				$n_userid	= $notify['mid'];
				$n_tid		= $notify['tid'];
				$n_triesleft	= $notify['nbtries']-$notify['timesplayed'];
				$sendername	= $notify['creat'];
	
				// get the names from user and tourney
				$getnamequery = $DB->query("SELECT name FROM ibf_user WHERE userid=".$n_userid);
				$getname = $DB->fetch_row($getnamequery);
				$n_name		= $getname['name'];

				// get the name of the creator of the tourney to be sender of notification
				$getidquery = $DB->query("SELECT userid FROM ibf_user WHERE username='".addslashes($notify['creat'])."'");
				if ($DB->get_num_rows($getidquery) == 0)
				{
					// creator unknown (maybe username changed?), set to default
					if ($this->user['id'] > 0)
					{
						$senderid 	= $this->user['id'];
						$sendername 	= $this->user['name'];
					}
					else
					{
						$senderid	= 0;
						$sendername	= "Arcade System";
					}
				}
				else
				{
					$getid = $DB->fetch_row($getidquery);
					$senderid = $getid['userid'];
				}
	
				$getnamequery = $DB->query("SELECT g.gtitle FROM ibf_games_list AS g LEFT JOIN ibf_tournaments AS t ON (t.gid = g.gid) WHERE t.tid=".$n_tid);
				$getname = $DB->fetch_row($getnamequery);
				$g_name		= $getname['gtitle'];
	
				$message = $this->settings['msgsys_tremind_text'];
				$mailmessage = $this->settings['msgsys_tremind_text'];
	
				$message = preg_replace('/%NAME%/',$n_name,$message);
				$message = preg_replace('/%GAME%/',$g_name,$message);
				$message = preg_replace("#%LINK\|(.*?)%#","[url='".$forumlink."arcade.php?do=viewtourney&tid=".$n_tid."']$1[/url]",$message);
	
				$mailmessage = preg_replace('/%NAME%/',$n_name,$mailmessage);
				$mailmessage = preg_replace('/%GAME%/',$g_name,$mailmessage);
				$mailmessage = preg_replace("#%LINK\|(.*?)%#","<a href='".$forumlink."arcade.php?do=viewtourney&tid=".$n_tid."'>$1</a>",$mailmessage);
	
				$mailmessage = strip_bbcode($mailmessage, true);
				$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);
	
				$title		= $ibforums->lang['pmnote_remind'];
				$mailtitle	= $ibforums->lang['pmnote_remind'];

				// does the recipient want to receive any Notifications from the Arcade ?
				$DB->query("SELECT arcade_pmactive, email FROM ibf_user WHERE userid=".$n_userid);
				$recip = $DB->fetch_row();
	
				if ($recip['arcade_pmactive'] == 1)
				{
					// Notification via PM
					if (($this->settings['notification']=="pm") || ($this->settings['notification']=="pm+mail"))
					{
						$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".$sendername."', '".$title."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($n_userid))) . "', 0, " . time() . ", 0, 0)");
						$pmid = $DB->get_insert_id();
						$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$n_userid");
						$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$n_userid', '0', '0')");
					}
		
					// Notification via eMail
					if (($this->settings['notification']=="mail") || ($this->settings['notification']=="pm+mail"))
					{
						vbmail($recip['email'],$mailtitle,$mailmessage);
					}
				}
	
				// update the notified-flag to avoid multiple notifications
				$DB->query("UPDATE ibf_tournament_players SET notified=1 WHERE tid=".$n_tid." AND mid=".$n_userid);
			}
		}
	}

	if ($this->settings['tourney_limit2'] > 0)
	{
		// disqualify the spoilers of the tournaments
		$deadline = time() - $this->settings['tourney_limit2']*86400;

		$notifyquery = "";
		if ($this->settings['tourney_limit1'] > 0)
		{
			$notifyquery = "AND p.notified=1 ";
		}

		$matrix[1][1]=0;
		$matrix[2][1]=0;
		$matrix[2][2]=0;
		$matrix[3][1]=0;
		$matrix[3][2]=0;
		$matrix[3][3]=0;
		$matrix[3][4]=0;

		$disqual_query = $DB->query("SELECT p.mid, p.tid, t.nbtries, p.timesplayed, p.rungscore, p.rung, p.faceoff, p.notified, t.creat FROM ibf_tournament_players AS p LEFT JOIN ibf_tournaments AS t ON (p.tid=t.tid) LEFT JOIN ibf_tournament_players_statut AS s ON (p.tid=s.tid AND p.mid=s.mid) WHERE p.timesplayed<t.nbtries ".$notifyquery."AND t.demare=1 AND s.statut=0 AND p.timeplayed<".$deadline);
		while ($disqualify = $DB->fetch_row($disqual_query))
		{
			// get the opponent from this user who is going to be kicked out
			$opponentquery = $DB->query("SELECT mid, timesplayed, rungscore, notified FROM ibf_tournament_players WHERE tid='".$disqualify['tid']."' AND rung='".$disqualify['rung']."' AND faceoff='".$disqualify['faceoff']."' AND mid<>'".$disqualify['mid']."'");
			$opponent = $DB->fetch_row($opponentquery);

			$nbtries 	= $disqualify['nbtries'];
			$faceoff 	= $disqualify['faceoff'];
			$rung		= $disqualify['rung'];
			$tid		= $disqualify['tid'];
			$sendername	= $disqualify['creat'];

			$mid_d		= $disqualify['mid'];
			$mid_o		= $opponent['mid'];

			$tries_d	= $disqualify['timesplayed'];
			$tries_o	= $opponent['timesplayed'];

			$score_d	= $disqualify['rungscore'];
			$score_o	= $opponent['rungscore'];

			$notify_d	= $disqualify['notified'];
			$notify_o	= $opponent['notified'];

			if ($matrix[$rung][$faceoff]==0)
			{
				// this faceoff has not been disqualified yet

				$matrix[$rung][$faceoff]=1;

				$gameinfoquery = $DB->query("SELECT g.highscore_type FROM ibf_games_list AS g LEFT JOIN ibf_tournaments AS t ON (t.tid = ".$tid.") WHERE g.gid=t.gid");
				$gameinfo = $DB->fetch_row($gameinfoquery);
				$scoretype = $gameinfo['highscore_type'];

				$swap=0;

/* no checks needed as the user who gets disqualified GETS disqualified anyway

				if ( (($score_d > $score_o) && $scoretype=="high") || (($score_d < $score_o) && $scoretype=="low") )
				{
					// user to be disqualified has better result than opponent
					$swap=1;
				}
				else
				{
					// opponent has better or equal result

					// both have same result
					if ($tries_d > $tries_o)
					{
						$swap=1;
					}
					else
					{
						// both have same amount of played tries
						// so kick random player
						srand(microtime()*1000000);
						if (rand(1,2) == 1)
						{
							$swap=1;
						}
					}
				}
*/

				if (($tries_d == $nbtries) && ($tries_o < $nbtries))
				{
					$swap = 1;
				}

				if ($swap==1)
				{
					$temp		= $mid_d;
					$mid_d		= $mid_o;
					$mid_o		= $temp;

					$temp		= $tries_d;
					$tries_d	= $tries_o;
					$tries_o	= $temp;

					$temp 		= $score_d;
					$score_d	= $score_o;
					$score_o	= $score_d;
				}

				if($faceoff == 1 || $faceoff == 2)
				{
					$nextfaceoff=1;
				}
				else
				{
					$nextfaceoff=2;
				}

				if ($rung == 1)
				{
					// last rung, so we have a winner - the opponent!
					$DB->query("SELECT name FROM ibf_members WHERE id='".$mid_o."' LIMIT 1");
					$name = $DB->fetch_row();
					$DB->query("UPDATE ibf_tournaments SET champion='".addslashes($name['name'])."' WHERE tid='".$tid."' LIMIT 1");
	
					$DB->query("UPDATE ibf_tournament_players_statut SET statut='3' WHERE tid='".$tid."' AND mid=".$mid_o);
					$DB->query("UPDATE ibf_tournament_players_statut SET statut='2' WHERE tid='".$tid."' AND mid=".$mid_d);
					$DB->query("UPDATE ibf_tournament_players SET timesplayed='".$nbtries."' WHERE tid='".$tid."' AND faceoff='".$faceoff."' AND rung='".$rung."'");
				}
				else
				{
					$db_string = $DB->compile_db_insert_string( array (	'mid'  		=> $mid_o,
	                                                               			'tid'     		=> $tid,
	                                                               			'rung'    		=> ($rung-1),
		                                                                		'rungscore'		=> 0,
	                                                               			'faceoff'    	=> $nextfaceoff,
	                                                                    		'timeplayed'	=> time(),
	                                                                        	'timesplayed'	=> 0,	
														'notified'		=> 0,
													) );
	                    	$DB->query("INSERT INTO ibf_tournament_players (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
	
					// check if there is a new opponent (to set back his timer)
					$check=$DB->query("SELECT mid FROM ibf_tournament_players WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($rung-1)." AND mid<>".$mid_o);
					if ($row = $DB->fetch_row($check))
					{
						$DB->query("UPDATE ibf_tournament_players SET timeplayed='".time()."', notified=0 WHERE tid=".$tid." AND faceoff=".$nextfaceoff." AND rung=".($rung-1)." AND mid=".$row['mid']);
					}
	
					$DB->query("UPDATE ibf_tournament_players_statut SET statut='2' WHERE tid='".$tid."' AND mid='".$mid_d."'");
					$DB->query("UPDATE ibf_tournament_players SET timesplayed='$nbtries' WHERE tid='$tid' AND faceoff='$faceoff' AND rung='$rung'");
				}

				// send notification
				$n_userid	= $mid_d;
				$n_tid		= $tid;
		
				// get the names from user and tourney
				$getnamequery = $DB->query("SELECT name FROM ibf_user WHERE userid=".$n_userid);
				$getname = $DB->fetch_row($getnamequery);
				$n_name		= $getname['name'];

				// get the name of the creator of the tourney to be sender of notification
				$getidquery = $DB->query("SELECT userid FROM ibf_user WHERE username='".$disqualify['creat']."'");
				if ($DB->get_num_rows($getidquery) == 0)
				{
					// creator unknown (maybe username changed?), set to default
					if ($this->user['id'] > 0)
					{
						$senderid 	= $this->user['id'];
						$sendername 	= $this->user['name'];
					}
					else
					{
						$senderid	= 0;
						$sendername	= "Arcade System";
					}
				}
				else
				{
					$getid = $DB->fetch_row($getidquery);
					$senderid = $getid['userid'];
				}

		
				$getnamequery = $DB->query("SELECT g.gtitle FROM ibf_games_list AS g LEFT JOIN ibf_tournaments AS t ON (t.gid = g.gid) WHERE t.tid=".$n_tid);
				$getname = $DB->fetch_row($getnamequery);
				$g_name		= $getname['gtitle'];
	
				$message = $this->settings['msgsys_tdisqual_text'];
				$mailmessage = $this->settings['msgsys_tdisqual_text'];
		
				$message = preg_replace('/%NAME%/',$n_name,$message);
				$message = preg_replace('/%GAME%/',$g_name,$message);
				$message = preg_replace('/%LIMIT%/',$this->settings['tourney_limit2'],$message);
				$message = preg_replace("#%LINK\|(.*?)%#","[url='".$forumlink."arcade.php?do=viewtourney&tid=".$n_tid."']$1[/url]",$message);
		
				$mailmessage = preg_replace('/%NAME%/',$n_name,$mailmessage);
				$mailmessage = preg_replace('/%GAME%/',$g_name,$mailmessage);
				$mailmessage = preg_replace('/%LIMIT%/',$this->settings['tourney_limit2'],$message);
				$mailmessage = preg_replace("#%LINK\|(.*?)%#","<a href='".$forumlink."arcade.php?do=viewtourney&tid=".$n_tid."'>$1</a>",$mailmessage);
		
				$mailmessage = strip_bbcode($mailmessage, true);
				$mailmessage = preg_replace('/<\/?[a-z][a-z0-9]*[^<>]*>/i', '', $mailmessage);
		
				$title		= $ibforums->lang['pmnote_disqual'];
				$mailtitle	= $ibforums->lang['pmnote_disqual'];
		
				// does the recipient want to receive any Notifications from the Arcade ?
				$DB->query("SELECT arcade_pmactive, email FROM ibf_user WHERE userid=".$n_userid);
				$recip = $DB->fetch_row();
	
				if ($recip['arcade_pmactive'] == 1)
				{
					// Notification via PM
					if (($this->settings['notification']=="pm") || ($this->settings['notification']=="pm+mail"))
					{
						$DB->query("INSERT INTO ibf_pmtext (fromuserid, fromusername, title, message, touserarray, iconid, dateline, showsignature, allowsmilie) VALUES ('".$senderid."', '".$sendername."', '".$title."', '" . addslashes($message) . "',  '" . addslashes(serialize(array($n_userid))) . "', 0, " . time() . ", 0, 0)");
						$pmid = $DB->get_insert_id();
						$DB->query("UPDATE ibf_user SET pmtotal=pmtotal+1, pmunread=pmunread+1 WHERE userid=$n_userid");
						$DB->query("INSERT INTO ibf_pm (pmtextid, userid, folderid, messageread) VALUES ('$pmid', '$n_userid', '0', '0')");
					}
	
					// Notification via eMail
					if (($this->settings['notification']=="mail") || ($this->settings['notification']=="pm+mail"))
					{
						vbmail($recip['email'],$mailtitle,$mailmessage);
					}
				}
			}
		}
	}

        $this->set_defaults();
    }

    //Makes sure the user is able to see the arcade use authorize() for standard users and authorize(1) to check to see if the user is an arcade mod or admin
    function authorize($mod_check = 0)
    {
    	global $ibforums, $DB, $std, $vboptions, $vbulletin;

	$vbversion = substr($vboptions[templateversion],0,3);
	if ($vbversion != "3.0")
	{
		($hook = vBulletinHook::fetch_hook('ibproarcade_authorize_start')) ? eval($hook) : false;
	}	

        if( time() > $this->settings['next_day'] )
        {
		$newtime = mktime(0,0,0,date("m"),date("d")+1, date("Y"));
            	$DB->query("UPDATE ibf_members SET times_played=0");
            	$DB->query("UPDATE ibf_games_settings SET next_day=".$newtime);
        }

        if( $this->user['ppd_require'] )
        {
       		$posts_today_time = mktime(0,0,0,date("m"),date("d"),date("Y"));
            	$DB->query("SELECT COUNT(postid) AS amount FROM ibf_post WHERE dateline>".$posts_today_time." AND userid=".$this->user['id']);
            	$posts_today = $DB->fetch_row();
            	$this->user['posts_today'] = $posts_today['amount'];
        }

        if( !$this->settings['arcade_status'] && !$this->user['is_admin'] )
        {
        	$std->Error( array( LEVEL => 1, MSG => 'arcade_off') );
        }

        // maybe the product is disabled?
        $vbversion = substr($vboptions[templateversion],0,3);
	if ($vbversion != "3.0")
        {
                if (!$vbulletin->products['ibproarcade'])
                {
                    $std->Error( array( LEVEL => 1, MSG => 'arcade_off') );
                }
        }

	if( $this->user['arcade_access'] == 0 )
        {
        	$std->Error( array( LEVEL => 1, MSG => 'group_no_view') );
        }

        if( $this->user['arcade_ban'] )
        {
        	$std->Error( array( LEVEL => 1, MSG => 'ban_name' ) );
        }

        if( $this->settings['banned_ips'] )
	{
		$ips = explode( "|", $this->settings['banned_ips'] );

			foreach ($ips as $ip)
			{
				$ip = preg_replace( "/\*/", '.*' , preg_quote($ip, "/") );

				if ( preg_match( "/^$ip/", $ibforums->input['IP_ADDRESS'] ) )
				{
					$std->Error( array( 'LEVEL' => 1, 'MSG' => 'ban_ip' ) );
				}
			}
		}

        if( $mod_check != 0 )
        {
        	if( !$this->user['is_mod'] && !$this->user['is_admin'] )
            {
            	$std->boink_it($ibforums->base_url."act=Arcade");
            }
        }

    }

    function set_defaults()
    {
    	global $ibforums;

	$validsort = array('gtitle','gcount','gwords','g_rating','added');
	$validorder = array('ASC','DESC');

        if( $this->user['user_sort'] )
        {
		// make sure the setting is clean and has a valid value
		if (in_array($this->user['user_sort'],$validsort))
		{
			$this->settings['g_display_sort'] = $this->user['user_sort'];
		}
        }

        if( $this->user['user_order'] )
        {
		// make sure the setting is clean and has a valid value
		if (in_array($this->user['user_order'],$validorder))
		{
			$this->settings['g_display_order'] = $this->user['user_order'];
		}
        }

        if( isset($_COOKIE['g_display_sort']) && isset($_COOKIE['g_display_order']) )
        {
		// make sure the setting is clean and has a valid value
		if (in_array($_COOKIE['g_display_sort'],$validsort))
		{
			$this->settings['g_display_sort'] = $_COOKIE['g_display_sort'];
		}
		if (in_array($_COOKIE['g_display_order'],$validorder))
		{
			$this->settings['g_display_order'] = $_COOKIE['g_display_order'];
		}
        }

        if( isset($ibforums->input['overwrite_sort']) && isset($ibforums->input['overwrite_order']) )
        {
		// make sure the setting is clean and has a valid value
		if (in_array($ibforums->input['overwrite_sort'],$validsort))
		{
			$this->settings['g_display_sort'] = $ibforums->input['overwrite_sort'];
			@setcookie("g_display_sort", $ibforums->input['overwrite_sort']);
		}
		if (in_array($ibforums->input['overwrite_order'],$validorder))
		{
			$this->settings['g_display_order'] = $ibforums->input['overwrite_order'];
			@setcookie("g_display_order", $ibforums->input['overwrite_order']);
		}
        }

        if ( $this->user['game_skin'] && $this->settings['allow_user_skin'] )
        {
        	$this->settings['skin'] = ($this->user['game_skin']-1);
        }

        if( $this->user['def_g_cat'] )
        {
        	$this->settings['def_cat'] = $this->user['def_g_cat'];
        }

        if( $this->user['user_g_pp'] )
        {
        	$this->settings['games_pp'] = $this->user['user_g_pp'];
        }

        if( $this->user['user_s_pp'] )
        {
        	$this->settings['scores_amount'] = $this->user['user_s_pp'];
        }
    }

    //Make sure user is allowed to play if not send them to arcade index
    function play_game_authorize()
    {
    	global $ibforums, $std, $DB;

        $play = 1;
	$onlycountingforums = 0;	// global setting (by now)

	if( $this->user['arcade_access'] > 2)
	{
		// guest-player that is allowed to play!
		// so skip some permisson-checks...
	}
	else
	{
	        if( $this->user['arcade_access'] < 2 )
	        {
	        	$play = 0;
	        	$std->Error( array( LEVEL => 1, MSG => 'err_noplay') );
	        }

		$postcounter=0;
		if ($onylcountingforums==1)
		{
			// get total posts that are in forums where postcounting is active
			$countquery = $DB->query("SELECT p.userid, f.options
					FROM ibf_post AS p, ibf_thread AS t, ibf_forum AS f
					WHERE p.userid=".$this->user['id']."
					AND p.threadid=t.threadid
					AND t.forumid=f.forumid");
			while ($counter=$DB->fetch_row($countquery))
			{
				if (substr($counter['options'],4,1)=="1") { $postcounter++; }
			}
		}
		else
		{
			// count ALL posts regardless of forum-settings and -counting
			$postcounter = $this->user['posts'];
		}

	    	if( $postcounter < $this->user['p_require'] )
	        {
	        	$play = 0;
	        	$std->Error( array( LEVEL => 1, MSG => 'err_postrequire', EXTRA => $this->user['p_require']) );
	        }
	        if( (($this->user['max_play']-$this->user['times_played']) <= 0) && $this->user['max_play'] != 0 )
	        {
	        	$play = 0;
			$std->Error( array( LEVEL => 1, MSG => 'err_maxplay', EXTRA => $this->user['max_play']) );
	        }

	        if( (($this->user['posts_today'] - $this->user['ppd_require']) < 0) && $this->user['ppd_require'] != 0 )
	        {
	        	$play = 0;
	        	$std->Error( array( LEVEL => 1, MSG => 'err_ppdrequire', EXTRA => $this->user['ppd_require'] ) );
	        }
	}

		$DB->query("SELECT g.gcat, c.c_id, c.password, g.active
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
				$play = 0;
		        	$std->Error( array( LEVEL => 1, MSG => 'err_password') );
			}
		}

	$allowed = explode(',', $this->user['allowed_categories']);
	if (!in_array(0,$allowed))
	{
		if (!in_array($this_game['c_id'],$allowed))
		{
			$play=0;
			$std->Error(array(LEVEL=>1,MSG=>'err_category'));
		}
	}

	if ($this_game['active']!=1)
	{
		$play=0;
	}

	if ($this->user['is_admin'])
	{
		// admin can play anything :)
		$play=1;
	}

        if( !$play )
        {
        	$std->boink_it($ibforums->base_url."act=Arcade");
        }
    }

    //Simply returns all the game gtitles in an array      array[gid] = gtitle;
    function get_game_gtitles()
    {
    	global $ibforums, $DB;

        $DB->query("SELECT gid, gtitle FROM ibf_games_list ORDER BY gid");
        while( $g = $DB->fetch_row() )
        {
        	$gamelist[ $g['gid'] ] = $g['gtitle'];
        }

        return $gamelist;
    }

    //Make a game link based on users permissions
    function make_links($gid , $gname = "" , $name_return = 0)
    {
    	global $ibforums, $DB;

        $this->links['click'] = "";
        $this->links['imglink'] = "";
        $this->links['imgend'] = "";


	if( !$this->user['id'] )
	{
		$DB->query("SELECT arcade_access, p_require FROM ibf_groups WHERE g_id = 1");
		$guestperm = $DB->fetch_row();
		if ($guestperm['arcade_access'] > 2)
		{
			// guest is allowed to play!
			$this->links['click'] .= "<a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid=".$gid."'>".$ibforums->lang['play_game'].$gname."</a>";
		}
		else
		{
			$this->links['click'] .= (!$name_return) ? $ibforums->lang['log_in'] : $gname;
		}

		return;
	}

		if( $this->user['arcade_access'] != 2 )
		{
			$this->links['click'] .= (!$name_return) ? $ibforums->lang['g_disable'] : $gname;
			return;
		}

        if( $this->user['posts'] < $this->user['p_require'] )
        {
        	$this->links['click'] .= (!$name_return) ? $ibforums->lang['p_require'].$this->user['p_require'] : $gname;
            return;
        }

        if( (($this->user['max_play'] - $this->user['times_played']) <= 0) && $this->user['max_play'] != 0)
        {
        	$this->links['click'] .= (!$name_return) ? $ibforums->lang['max'] : $gname;
            return;
        }

        if( (($this->user['posts_today'] - $this->user['ppd_require']) < 0) && $this->user['ppd_require'] != 0 )
        {
        	$this->links['click'] .= (!$name_return) ? ($this->user['ppd_require'] - $this->user['posts_today']).$ibforums->lang['ppd_left'] : $gname;
            return;
        }

        if( !$name_return )
        {
        	$this->links['click'] .= "<a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid=".$gid."'>".$ibforums->lang['play_game'].$gname."</a>";
        }
        else
        {
        	$this->links['click'] .= "<a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid=".$gid."'>".$gname."</a>";
        }
        $this->links['imglink'] .= "<a href='".$ibforums->base_url."act=Arcade&amp;do=play&amp;gameid=".$gid."'>";
		$this->links['imgend'] .= "</a>";
    }

    //Gets the users active in the arcade and returns the html for it
    function get_active($html="")
    {
    	global $ibforums, $main, $DB;

        $active_list = array();
        $the_games = array();
        $game_list = array();
        $user_count = 0;
        $guest_count = 0;
        $anon_count = 0;
        $names = "";

        $cut_off = (!empty($ibforums->vars['au_cutoff'])) ? $ibforums->vars['au_cutoff'] * 60 : 15 * 60;
		$time    = time() - $cut_off;

        $DB->query("SELECT s.member_id, s.member_name, s.login_type, s.in_game, g.suffix, g.prefix, u.options
							FROM ibf_sessions AS s
							LEFT JOIN ibf_groups AS g ON (g.g_id=s.member_group)
							LEFT JOIN ibf_user AS u ON (u.userid=s.member_id)
			        WHERE running_time>".$time." AND location='Arcade,,'
			        ORDER BY s.member_name ASC");

        while( $users = $DB->fetch_row() )
        {

					$invisible_bit_from_right = 10;
					$userdata = base_convert($users['options'],10,2);
					$invisible = substr($userdata,(strlen($userdata)-$invisible_bit_from_right),1);

        	$active_list[] = array( name		=> $users['member_name'],
                                    id			=> $users['member_id'],
                                    in_game		=> $users['in_game'],
                                    suffix		=> $users['suffix'],
                                    prefix		=> $users['prefix'],
                                    login		=> $users['login_type'],
				    												invisible		=> $invisible
                             );

            if( trim($users['in_game']) != "" )
            {
            	$temp = explode("|" , $users['in_game']);
            	$the_games[] = $temp[0];
            }
        }

        if(count($the_games) > 0)
 		{
  			$games_list = implode(",", $the_games);

  			$DB->query("SELECT gid, gtitle, gname FROM ibf_games_list WHERE gid IN ($games_list)");
  			while($g = $DB->fetch_row())
  			{
      			$games_names[$g['gid']] = $g['gtitle'];
      			$gname_names[$g['gid']] = $g['gname'];
  			}
 		}

        $a = 0;
        foreach( $active_list as $this_user )
        {
        	$span_pre = "";
            $span_suf = "";

            $this_user['in_game'] = explode("|",$this_user['in_game']);
   			$gid = $this_user['in_game'][0];
   			$do = $this_user['in_game'][1];

            if( $do == "play" )
            {
            	$span_pre = "<span title='".$ibforums->lang["playing"].$games_names[$gid]."' style='text-decoration: none; border-bottom: 1px dashed; padding-bottom: 1px;'>";
//xxx
            	$span_pre .= "<img src='arcade/images/".$gname_names[$gid]."1.gif' widht='20' height='20' border='0' alt='".$ibforums->lang["playing"].$games_names[$gid]."' />&nbsp;";
            }
            elseif( $do == "stats" )
            {
                $span_pre = "<span title='".$ibforums->lang["viewing"].$games_names[$gid]."'>";
            }
            else
            {
            	$span_pre = "<span title='".$ibforums->lang["arcade_home"]."'>";
            }
            $span_suf = "</span>";

            if( $this_user['login'] != 1 )
            {
							if ($this_user['invisible'] != 1)
							{
	            	if( $this_user['id'] > 0 )
        	        {
                	$user_count++;
	            		$names .= ($a) ? ",&nbsp;" : "";
        	    		$names .= $span_pre."<a href='./member.php?$session[sessionurl]&amp;u=".$this_user['id']."'>";
            			$names .= $this_user['prefix'].$this_user['name'].$this_user['suffix'];
            			$names .= "</a>".$span_suf;
	            		$a++;
        	        }
                	else
	                {
        	        	$guest_count++;
                	}
							}
							else
							{
							    // check for Admin who can see invisible users
							    if ($this->user['mgroup'] == 6)
							    {
                  		$names .= ($a) ? ",&nbsp;" : "";
            					$names .= $span_pre."<a href='./member.php?$session[sessionurl]&amp;u=".$this_user['id']."'>";
            					$names .= $this_user['prefix'].$this_user['name'].$this_user['suffix'];
            					$names .= "</a>*".$span_suf;
                    	$a++;
									}
							}
            }
            else
            {
                if( ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) && ($ibforums->vars['disable_admin_anon'] != 1) )
            	{
                    $names .= ($a) ? ",&nbsp;" : "";
            		$names .= $span_pre."<a href='./member.php?$session[sessionurl]&amp;u=".$this_user['id']."'>";
            		$names .= $this_user['prefix'].$this_user['name'].$this_user['suffix'];
            		$names .= "</a>*".$span_suf;
                    $a++;
            	}
                $anon_count++;
            }
        }

        $total_now = $user_count + $guest_count + $anon_count;

        if( $total_now > $this->settings['most_users_on'] )
        {
            $db_string = $DB->compile_db_update_string( array( 'most_users_on'		=>	$total_now,  ) );
        	$DB->query("UPDATE ibf_games_settings SET ".$db_string);
            $this->settings['most_users_on'] = $total_now;
        }

        $ibforums->lang['active'] 	= str_replace("!M!" , $user_count , $ibforums->lang['active'] );
        $ibforums->lang['active'] 	= str_replace("!G!" , $guest_count , $ibforums->lang['active'] );
        $ibforums->lang['active'] 	= str_replace("!A!" , $anon_count , $ibforums->lang['active'] );
        $ibforums->lang['header'] 	= str_replace("!R!" , $this->settings['most_users_on'] , $ibforums->lang['header'] );
        $ibforums->lang['header'] 	= str_replace("!T!" , $total_now , $ibforums->lang['header'] );

        $this->active = $html->active_users($names);
    }

    //Makes the box on top with settings, report, modcp, etc... links
    function top_links($html)
    {
    	global $ibforums, $vbulletin, $vboptions, $DB;

        $selected['sort'] = array( gtitle	=> "" , gcount	=> "" , gwords	=> "" , g_rating	=> "" );
        $selected['order'] = array( ASC	=> "" , DESC	=> "" );
        $the_links = "";
        $extra = "";
        $width	= "100%";

	$guestplayerid=0;
	$DB->query("SELECT arcade_access, p_require FROM ibf_groups WHERE g_id = 1");
	$guestperm = $DB->fetch_row();
	if ($guestperm['arcade_access']==4)
	{
		if (intval($guestperm['p_require'] > 0))
		{ $guestplayerid = $guestperm['p_require']; }
	}

        if( $this->user['id'] )
        {
		// Arcade NavBar
		$the_links .= "<a href='".$ibforums->base_url."act=Arcade'>".$ibforums->lang['page_title']."</a>";

		$the_links .= "&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='".$ibforums->base_url."do=viewtournaments'>".$ibforums->lang['tourney_navbar']."</a>";

		if ($guestplayerid != $this->user['id'])
		{
			$the_links .= "&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='".$ibforums->base_url."act=Arcade&amp;module=settings'>".$ibforums->lang['your_settings']."</a>";
		}

            if( $this->user['arcade_access'] == 2 )
            {
            	$the_links .= "&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='".$ibforums->base_url."act=Arcade&amp;module=favorites'>".$ibforums->lang['your_favs']."</a>";
            }

		if ($guestplayerid != $this->user['id'])
		{
			$the_links .= "&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='".$ibforums->base_url."act=Arcade&amp;module=report'>".$ibforums->lang['your_report']."</a>";
		}

			$the_links .= "&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='".$ibforums->base_url."act=Arcade&amp;module=league'>".$ibforums->lang['leaderboard']."</a>";


            if(( $this->user['is_mod'] || $this->user['is_admin'] ) && ($this->user['id']!=$guestplayerid))
        	{
        		$the_links .= "&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='".$ibforums->base_url."act=Arcade&amp;module=modcp'>".$ibforums->lang['arcade_cp_link']."</a>";
        	}

            if( (!isset($ibforums->input['do']) || $ibforums->input['do'] == "") && !isset($ibforums->input['module']) )
            {
            	$cat = "";
                if( isset($ibforums->input['cat']) && is_numeric($ibforums->input['cat']) )
                {
                	$cat .= "&amp;cat=".$ibforums->input['cat'];
                }

                if( isset($ibforums->input['st']) && is_numeric($ibforums->input['st']) )
                {
                	$cat .= "&amp;st=".$ibforums->input['st'];
                }

                $selected['sort'][ $this->settings['g_display_sort'] ] = "selected='selected'";
                $selected['order'][ $this->settings['g_display_order'] ] = "selected='selected'";

            	// $extra = $html->top_links_table_extra($cat , $selected);
                $width = "70%";
            }

		$arcadepass_expire="";

		$vbversion = substr($vboptions[templateversion],0,3);
		if ($vbversion != "3.0")
		{
			($hook = vBulletinHook::fetch_hook('ibproarcade_navbar_complete')) ? eval($hook) : false;
		}

		if ($arcadepass_expire!="")
		{
			$the_links = "<span class='smallfont' style='color: red;'><b>".$ibforums->lang['arcade_pass'].$arcadepass_expire."</b></span><br />".$the_links;
		}

            $this->extra_links = $html->top_links_table($the_links,$width,$extra);
        }
    }

    function get_pages($type = 0 , $use_cats = 0 , $incoming_total = 0)
    {
    	global $std, $ibforums, $DB;

        $start = 0;
        if( isset($ibforums->input['st']) )
        {
        	$start = $ibforums->input['st'];
        }

        if( $type == 0)
        {
        	$url_extra = "";
        	if( $use_cats )
        	{
        		$cat = $ibforums->input['cat'];

            	if( empty($cat) )
            	{
            		$cat = $this->settings['def_cat'];
            	}

                $total['amount'] = $incoming_total;

            	$url_extra = "&amp;cat=".$cat;
        	}
            else
            {
        		$DB->query("SELECT COUNT(gid) AS amount FROM ibf_games_list WHERE active=1");
        		$total = $DB->fetch_row();
            }

            $per_page = $this->settings['games_pp'];
        }

        if( $type == 1 )
        {
        	$gid = $ibforums->input['gameid'];

            $DB->query("SELECT COUNT(s_id) AS amount FROM ibf_games_scores WHERE gid=".$gid);
			$total = $DB->fetch_row();

            $per_page = $this->settings['scores_amount'];

            $url_extra = "&amp;do=stats&amp;gameid=".$gid;
        }
        $this->links['pages'] = $std->build_pagelinks(  array( 	'TOTAL_POSS'  	=> $total['amount'],
																'PER_PAGE'    	=> $per_page,
																'CUR_ST_VAL'  	=> $start,
																'L_SINGLE'     	=> "",
																'L_MULTI'      	=> $ibforums->lang['pages'],
																'BASE_URL'     	=> $ibforums->base_url."act=Arcade".$url_extra
											  				)
									   				);

        if( !empty($this->links['pages']) )
        {
        	$this->links['pages'] .= "";
        }
    }


	        //------------------------------------------
        // thatdate
        //
        // This will take unix seconds and convert them to 0d 0h 0m 0s
        //
        //------------------------------------------


        function thatdate($time)
        {
                $diff = $time;
                $daysDiff = floor($diff/60/60/24);
                $diff -= $daysDiff*60*60*24;
                $hrsDiff = floor($diff/60/60);
                $diff -= $hrsDiff*60*60;
                $minsDiff = floor($diff/60);
                $diff -= $minsDiff*60;
                $secsDiff = $diff;

                $eltime = "";
                if ($daysDiff)	{ $eltime = $eltime.$daysDiff." ".$ibforums->lang['acp_day1']; 
			if ($daysDiff < 10) { $eltime=$eltime." "; } else { $eltime=$eltime.$ibforums->lang['acp_day2']." "; }
			}
	if ($hrsDiff) {
	if ($hrsDiff<10) { $eltime=$eltime."0"; }	
	$eltime=$eltime.$hrsDiff.":";
	}
	if ($minsDiff<10) { $eltime=$eltime."0"; }	
	$eltime=$eltime.$minsDiff.":";
	if ($secsDiff<10) { $eltime=$eltime."0"; }	
	$eltime=$eltime.$secsDiff;
                
                return $eltime;

        }

        //------------------------------------------
        // do_arcade_format
        //
        // This will format the numbers in the arcade with what is specified in the Arcade main settings
        //
        //------------------------------------------

        function do_arcade_format($number,$decpoints = 0)
        {
                global $ibforums;

                if (!isset($decpoints)) { $decpoints = 0; }

                if ($this->settings['score_sep'] != '0')
                {
                        return number_format($number , $decpoints, $ibforums->lang['commasign'], $this->settings['score_sep']);
                }
                else
                {
                        return number_format($number , $decpoints, $ibforums->lang['commasign'], '');
                }
        }

    //formats the score
    function t3h_format($num)
    {

    	$this->settings['score_sep'] = preg_replace( "/0/i" , "" , $this->settings['score_sep']);
        $this->settings['score_sep'] = preg_replace( "/s/i" , " " , $this->settings['score_sep']);
        $num = number_format($num , $this->settings['dec_amount'] , '.' , $this->settings['score_sep']);
		if( preg_match( "/\.0+$/i" , $num )  )
    	{
    		$num = preg_replace( "/\.0+/i" , "" , $num );
    	}

        $num = preg_replace( "/\.(.+)0+$/i" , ".\\1" , $num );

        return $num;
    }

    //Prints the output
    function print_it($incoming = "" , $title="ibProArcade" , $nav = array("ibProArcade") , $popup = 0 , $top = 0)
    {
    	global $print;

        if( !is_array($nav) )
        {
        	$temp = $nav;
            $nav = array( $temp );
        }

        $output = "";
        $output = $incoming;
        if( !$popup )
        {
        	$print->add_output($output);
        	$print->do_output( array( 'TITLE' => $title, 'JS' => 0, NAV => $nav ) );
        }
        else
        {
        	$print->pop_up_window($title,$output);
        }
    }
}

?>