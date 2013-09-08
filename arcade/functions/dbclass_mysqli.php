<?php

class db_driver {

    var $obj = array ( "sql_database"   => ""         ,
                       "sql_user"       => "root"     ,
                       "sql_pass"       => ""         ,
                       "sql_host"       => "localhost",
                       "sql_port"       => ""         ,
                       "persistent"     => "0"         ,
                       "sql_tbl_prefix"        => "ibf_"      ,
                       "cached_queries" => array(),
                       'debug'          => 0,
                     );
                     
     var $query_id      = "";
     var $connection_id = "";
     var $query_count   = 0;
     var $record_row    = array();
     var $return_die    = 0;
     var $error         = "";
     var $failed        = 0;
                  
    /*========================================================================*/
    // Connect to the database                 
    /*========================================================================*/  
                   
    function connect() {

	$this->obj['sql_host'] = ($this->obj['sql_port'] == "") ? $this->obj['sql_host'] : $this->obj['sql_host'].":".$this->obj['sql_port'];
    
    	if ($this->obj['persistent'])
    	{
    	    $this->connection_id = mysqli_real_connect( $this->obj['sql_host'] ,
												   $this->obj['sql_user'] ,
												   $this->obj['sql_pass'] 
												);
        }
        else
        {
			$this->connection_id = mysqli_real_connect( $this->obj['sql_host'] ,
												  $this->obj['sql_user'] ,
												  $this->obj['sql_pass'] 
												);
		}
		
        if ( !mysqli_select_db($this->obj['sql_database'], $this->connection_id) )
        {
            echo ("ERROR: Cannot find database ".$this->obj['sql_database']);
        }
    }
    
    
    
    /*========================================================================*/
    // Process a query
    /*========================================================================*/
    
    function query($the_query, $bypass=0) {
    	
   //--------------------------------------
   // Change the table prefix if needed
   //--------------------------------------
		// Avatare kompatibel über alle Versionen by MrZeropage
		global $vboptions;
		$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
		if ($vbversion == "3.0")
		{
			$avatarkompatibel = "avatardata";
		}
		else
		{
			//$avatarkompatibel = "avatarrevision";
			$avatarkompatibel = "filedata";
		}

		if(strpos($the_query, "avatar,avatar_size")) {
			$equalpos = strpos($the_query, "=");
			$idneeded = substr($the_query, $equalpos+1, strlen($the_query)-($equalpos+1));
			/*$the_query = "SELECT user.userid, avatar.avatarpath, NOT ISNULL(customavatar.avatardata) AS hascustomavatar, customavatar.filename 
							FROM ibf_user AS user 
							LEFT JOIN ibf_userfield AS userfield ON (user.userid = userfield.userid)  
							LEFT JOIN avatar AS avatar ON (avatar.avatarid = user.avatarid) 
							LEFT JOIN customavatar AS customavatar ON (customavatar.userid = user.userid) 
							INNER JOIN language AS language ON (language.languageid = IF(user.languageid = 0, 1, user.languageid)) 
							WHERE user.userid = " . $idneeded;*/
			$the_query = "SELECT user.userid, user.avatarid, user.avatarrevision, avatar.avatarpath, customavatar.filename, NOT ISNULL(".$avatarkompatibel.") AS hascustom, customavatar.dateline FROM ibf_user AS user LEFT JOIN ibf_avatar AS avatar ON avatar.avatarid = user.avatarid LEFT JOIN ibf_customavatar AS customavatar ON customavatar.userid = user.userid WHERE customavatar.userid=" . $idneeded . " OR user.userid=".$idneeded;
		}
		if(strpos($the_query, "s.login_type")) {
			$gtlocation = strpos($the_query, ">");
			$timestamp = substr($the_query, $gtlocation+1, 10);
			$the_query = "SELECT s.userid, s.location, s.in_game, u.username, g.opentag, g.closetag 
							FROM ibf_session as s 
							LEFT JOIN ibf_user as u ON (u.userid = s.userid) 
							LEFT JOIN ibf_usergroup as g ON (u.usergroupid = g.usergroupid)
							WHERE s.lastactivity > " . $timestamp . " AND s.location LIKE '%rcade%' AND s.location NOT LIKE '%admin%'
							GROUP BY s.userid 
							ORDER BY s.lastactivity DESC";
		}

        if ($bypass != 1)
        {
			if ($this->obj['sql_tbl_prefix'] != "ibf_")
			{
			   $the_query = preg_replace("/ibf_(\S+?)([\s\.,]|$)/", $this->obj['sql_tbl_prefix']."\\1\\2", $the_query);
			}
        }


	if ((!strpos($the_query, "pmtext")) && (!strpos($the_query, "pmtotal")) && (!strpos($the_query, "pmtextid")))
	// no PM is being sent, so change tables
	{
		//Change the tables from the IPB names to the vBulletin names - ibProArcade modification
		if(!strpos($the_query, "gamesessions")){
			$the_query = str_replace("sessions", "session", $the_query);
		}
		$the_query = str_replace("members", "user", $the_query);
		$the_query = str_replace("groups", "usergroup", $the_query);
		$the_query = str_replace("emoticons", "smilie", $the_query);

		/*     // smily-fix ?
		if(strpos($the_query, "smilie")) {
			$the_query = str_replace("id", "smilieid", $the_query);
		}
		*/

		// fix to make PM-sending possible by MrZeropage
		if(strpos($the_query, "fromusersmilieid")) {
			$the_query = str_replace("smilieid", "id", $the_query);
		}
		if(strpos($the_query, "iconsmilieid")) {
			$the_query = str_replace("smilieid", "id", $the_query);
		}


		if(strpos($the_query, "admin_logs")) {
			$the_query = str_replace("admin_logs", "adminlog", $the_query);
			$the_query = str_replace("act", "script", $the_query);
			$the_query = str_replace("code", "action", $the_query);
			$the_query = str_replace("member_id", "userid", $the_query);
			$the_query = str_replace("ctime", "dateline", $the_query);
			$the_query = str_replace("note", "extrainfo", $the_query);
			$the_query = str_replace("ip_address", "ipaddress", $the_query);
		}
		if(strpos($the_query, " posts ")) {
			$the_query = str_replace(" posts ", " post ", $the_query);
			$the_query = str_replace("pid", "postid", $the_query);
			$the_query = str_replace("post_date", "dateline", $the_query);
			$the_query = str_replace("author_id", "userid", $the_query);
		}

		//Field name changes
		$the_query = str_replace(" id ", " userid ", $the_query);
		$the_query = str_replace(" id=", " userid=", $the_query);
		$the_query = str_replace(".id", ".userid", $the_query);
		//Had to add this because saying "idea" or "identity" in the score comment would be replaced by useridea.
		if(!strpos($the_query, "idea") || !strpos($the_query, "identity")) { 
			$the_query = str_replace(" id", " userid", $the_query);
		}
		$the_query = str_replace(" id,", " userid,", $the_query);
		$the_query = str_replace(",id", ",userid", $the_query);
		if(!strpos($the_query, "tournament") && !strpos($the_query, "games_savedGames") && !strpos($the_query, "champ_name") && !strpos($the_query, "varname") && !strpos($the_query, "gname") && !strpos($the_query, "filename") && !strpos($the_query, "datescored")&& !strpos($the_query, "username") && !strpos($the_query, "cat_name") && !strpos($the_query, "games_scores")) {

			$the_query = str_replace("name", "username", $the_query);
		}
		if(strpos($the_query, "m.name")) {
			$the_query = str_replace("m.name", "m.username", $the_query);
		}
		$the_query = str_replace("mgroup", "usergroupid", $the_query);
		$the_query = str_replace("g_access_cp", "adminpermissions", $the_query);
		$the_query = str_replace("g_id", "usergroupid", $the_query);
		$the_query = str_replace("g__id", "g.usergroupid", $the_query);	// by MrZeropage
		$the_query = str_replace("typed", "smilietext", $the_query);
		//$the_query = str_replace("image", "smiliepath", $the_query);
		$the_query = str_replace("g_title", "title", $the_query);
		$the_query = str_replace("ip_address", "ipaddress", $the_query);
	}
        
        if ($this->obj['debug'])
        {
    		global $Debug, $ibforums;
    		
    		$Debug->startTimer();
    	}
    	
        $this->query_id = mysqli_query($the_query, $this->connection_id);
      
        if (! $this->query_id )
        {
            $this->fatal_error("mySQL query error: $the_query");
        }
        
        if ($this->obj['debug'])
        {
        	$endtime = $Debug->endTimer();
        	
        	if ( preg_match( "/^select/i", $the_query ) )
        	{
        		$eid = mysqli_query("EXPLAIN $the_query", $this->connection_id);
        		$ibforums->debug_html .= "<table width='95%' border='1' cellpadding='6' cellspacing='0' bgcolor='#FFE8F3' align='center'>
										   <tr>
										   	 <td colspan='8' style='font-size:14px' bgcolor='#FFC5Cb'><b>Select Query</b></td>
										   </tr>
										   <tr>
										    <td colspan='8' style='font-family:courier, monaco, arial;font-size:14px;color:black'>$the_query</td>
										   </tr>
										   <tr bgcolor='#FFC5Cb'>
											 <td><b>table</b></td><td><b>type</b></td><td><b>possible_keys</b></td>
											 <td><b>key</b></td><td><b>key_len</b></td><td><b>ref</b></td>
											 <td><b>rows</b></td><td><b>Extra</b></td>
										   </tr>\n";
				while( $array = mysqli_fetch_array($eid) )
				{
					$type_col = '#FFFFFF';
					
					if ($array['type'] == 'ref' or $array['type'] == 'eq_ref' or $array['type'] == 'const')
					{
						$type_col = '#D8FFD4';
					}
					else if ($array['type'] == 'ALL')
					{
						$type_col = '#FFEEBA';
					}
					
					$ibforums->debug_html .= "<tr bgcolor='#FFFFFF'>
											 <td>$array[table]&nbsp;</td>
											 <td bgcolor='$type_col'>$array[type]&nbsp;</td>
											 <td>$array[possible_keys]&nbsp;</td>
											 <td>$array[key]&nbsp;</td>
											 <td>$array[key_len]&nbsp;</td>
											 <td>$array[ref]&nbsp;</td>
											 <td>$array[rows]&nbsp;</td>
											 <td>$array[Extra]&nbsp;</td>
										   </tr>\n";
				}
				
				if ($endtime > 0.1)
				{
					$endtime = "<span style='color:red'><b>$endtime</b></span>";
				}
				
				$ibforums->debug_html .= "<tr>
										  <td colspan='8' bgcolor='#FFD6DC' style='font-size:14px'><b>mySQL time</b>: $endtime</b></td>
										  </tr>
										  </table>\n<br />\n";
			}
			else
			{
			  $ibforums->debug_html .= "<table width='95%' border='1' cellpadding='6' cellspacing='0' bgcolor='#FEFEFE'  align='center'>
										 <tr>
										  <td style='font-size:14px' bgcolor='#EFEFEF'><b>Non Select Query</b></td>
										 </tr>
										 <tr>
										  <td style='font-family:courier, monaco, arial;font-size:14px'>$the_query</td>
										 </tr>
										 <tr>
										  <td style='font-size:14px' bgcolor='#EFEFEF'><b>mySQL time</b>: $endtime</span></td>
										 </tr>
										</table><br />\n\n";
			}
		}
		
		$this->query_count++;
        
        $this->obj['cached_queries'][] = $the_query;
        
        return $this->query_id;
    }
    
    
    /*========================================================================*/
    // Fetch a row based on the last query
    /*========================================================================*/
    
    function fetch_row($query_id = "") {
		global $vboptions, $vbulletin;

	$vbversion = substr($vboptions[templateversion],0,3);	// Version 3.0 oder 3.5
	if ($vbversion != "3.0")
	// adapt some variables to vBulletin 3.5.x
	{
		$vboptions['usefileavatar'] = $vbulletin->options['usefileavatar'];
		$vboptions['avatarurl'] = $vbulletin->options['avatarurl'];
	}

   
    	if ($query_id == "")
    	{
    		$query_id = $this->query_id;
    	}
    	
        $this->record_row = mysqli_fetch_array($query_id, MYSQL_ASSOC);

		if(isset($this->record_row['userid'])) {
			$this->record_row['id'] = $this->record_row['userid'];
		}
		//$this->record_row['id'] = $this->record_row['userid'];
		if(isset($this->record_row['username'])) {
			$this->record_row['name'] = $this->record_row['username'];
		}
		if(isset($this->record_row['usergroupid'])) {
			$this->record_row['mgroup'] = $this->record_row['usergroupid'];
		}
		if(isset($this->record_row['adminpermissions'])) {
			$this->record_row['g_access_cp'] = $this->record_row['adminpermissions'];
		}
		if(isset($this->record_row['usergroupid'])) {
			$this->record_row['g_id'] = $this->record_row['usergroupid'];
		}
		if(isset($this->record_row['smilietext'])) {
			$this->record_row['typed'] = $this->record_row['smilietext'];
		}
		if(isset($this->record_row['smiliepath'])) {
			$this->record_row['image'] = $this->record_row['smiliepath'];
		}
		if (isset($this->record_row['avatarid'])) {
			if (!empty($this->record_row['avatarpath']))
			{
				$this->record_row['avatar'] = $this->record_row['avatarpath'];
			}
			else if ($this->record_row['hascustom'])
			{
				if ($vboptions['usefileavatar'])
				{
					$this->record_row['avatar'] = $vboptions['avatarurl'] . "/avatar" . $this->record_row['userid'] . "_" . $this->record_row['avatarrevision'] . ".gif";
				}
				else
				{
					$this->record_row['avatar'] = "image.php?u=" . $this->record_row['userid'] . "&amp;dateline=" . $this->record_row['dateline'];
				}
			}
			else
			{
				$this->record_row['avatar'] = '';
			}
		}
		if(isset($this->record_row['hascustomavatar'])) {
			if($this->record_row['hascustomavatar'] == 1) {
				if($vboptions['usefileavatar']) {
					$this->record_row['avatar'] = $vboptions[avatarurl] . "/avatar" . $this->record_row['userid'] . "_" . $avatarinfo[avatarrevision] . ".gif";
				} else {
					$this->record_row['avatar'] = "./image.php?u=" . $this->record_row['userid'];
				}
			} else {
				if(isset($this->record_row['avatarpath']) && !empty($this->record_row['avatarpath'])) {
					$this->record_row['avatar'] = $this->record_row['avatarpath'];
				} else {
					$this->record_row['avatar'] = "";
				}
			}
		}
		if(isset($this->record_row['opentag'])) {
			$this->record_row['prefix'] = $this->record_row['opentag'];
			$this->record_row['suffix'] = $this->record_row['closetag'];
			if($this->record_row['userid'] > 0) {
				$this->record_row['login_type'] = "-1";
			} else {
				$this->record_row['login_type'] = "0";
			}
			$this->record_row['member_name'] = $this->record_row['username'];
			$this->record_row['member_id'] = $this->record_row['userid'];
			if(strpos($this->record_row['location'], "do")) {
				$thisdo = substr($this->record_row['location'], strpos($this->record_row['location'], "do") + 3);
				if(strpos($thisdo, "&")) {
					$thisdo = substr($thisdo, 0, strpos($thisdo, "&"));
				}
			}
			if(strpos($this->record_row['location'], "gameid")) {
				$thisgameid = substr($this->record_row['location'], strpos($this->record_row['location'], "gameid") + 7);
				if(strpos($thisgameid, "&")) {
					$thisgameid = substr($thisgameid, 0, strpos($thisgameid, "&"));
				}
			}
			$this->record_row['in_game'] = intval($thisgameid) . "|" . $thisdo;
		}
		if(isset($this->record_row['title'])) {
			$this->record_row['g_title'] = $this->record_row['title'];
		}
		if(isset($this->record_row['ipaddress'])) {
			$this->record_row['ip_address'] = $this->record_row['ipaddress'];
		}
        
        return $this->record_row;
        
    }

	/*========================================================================*/
    // Fetch the number of rows affected by the last query
    /*========================================================================*/
    
    function get_affected_rows() {
        return mysqli_affected_rows($this->connection_id);
    }
    
    /*========================================================================*/
    // Fetch the number of rows in a result set
    /*========================================================================*/
    
    function get_num_rows() {
        return mysqli_num_rows($this->query_id);
    }
    
    /*========================================================================*/
    // Fetch the last insert id from an sql autoincrement
    /*========================================================================*/
    
    function get_insert_id() {
        return mysqli_insert_id($this->connection_id);
    }  
    
    /*========================================================================*/
    // Return the amount of queries used
    /*========================================================================*/
    
    function get_query_cnt() {
        return $this->query_count;
    }
    
    /*========================================================================*/
    // Free the result set from mySQLs memory
    /*========================================================================*/
    
    function free_result($query_id="") {
    
   		if ($query_id == "") {
    		$query_id = $this->query_id;
    	}
    	
    	@mysqli_free_result($query_id);
    }
    
    /*========================================================================*/
    // Shut down the database
    /*========================================================================*/
    
    function close_db() { 
        return mysqli_close($this->connection_id);
    }
    
    /*========================================================================*/
    // Return an array of tables
    /*========================================================================*/
    
    function get_table_names() {

/*
		$result     = mysql_list_tables($this->obj['sql_database']);
		$num_tables = @mysql_numrows($result);
		for ($i = 0; $i < $num_tables; $i++)
		{
			$tables[] = mysql_tablename($result, $i);
		}
*/
		// rewritten for mysqli by MrZeropage
		$result = mysqli_query("SHOW TABLES FROM ".$this->obj['sql_database']);
		while ($row = mysqli_fetch_array($result))
		{
			$tables[] = $row[0];
		}
		
		mysqli_free_result($result);
		
		return $tables;
   	}
   	
   	/*========================================================================*/
    // Return an array of fields
    /*========================================================================*/
    
    function get_result_fields($query_id="") {
    
   		if ($query_id == "")
   		{
    		$query_id = $this->query_id;
    	}
    
		while ($field = mysqli_fetch_field($query_id))
		{
            $Fields[] = $field;
		}
		
		//mysqli_free_result($query_id);
		
		return $Fields;
   	}
    
    /*========================================================================*/
    // Basic error handler
    /*========================================================================*/
    
    function fatal_error($the_error) {
    	global $INFO;
    	
    	
    	// Are we simply returning the error?
    	
    	if ($this->return_die == 1)
    	{
    		$this->error    = mysqli_error();
    		$this->error_no = mysqli_errno();
    		$this->failed   = 1;
    		return;
    	}
    	
    	$the_error .= "\n\nmySQL error: ".mysqli_error()."\n";
    	$the_error .= "mySQL error code: ".$this->error_no."\n";
    	$the_error .= "Date: ".date("l dS of F Y h:i:s A");
    	
    	$out = "<html><head><title>ibProArcade Database Error</title>
    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
    		   &nbsp;<br><br><blockquote><b>There is a SQL error.</b><br>
    		   You can contact the board administrator by clicking <a href='mailto:{$INFO['email_in']}?subject=SQL+Error'>here</a>
    		   <br><br><b>Error Returned</b><br>
    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">".htmlspecialchars($the_error)."</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";
    		   
    
        echo($out);
        die("");
    }
    
    /*========================================================================*/
    // Create an array from a multidimensional array returning formatted
    // strings ready to use in an INSERT query, saves having to manually format
    // the (INSERT INTO table) ('field', 'field', 'field') VALUES ('val', 'val')
    /*========================================================================*/
    
    function compile_db_insert_string($data) {
    
    	$field_names  = "";
		$field_values = "";
		
		foreach ($data as $k => $v)
		{
			$v = addslashes($v);				// allmighty fix by MrZeropage!
			//$v = preg_replace( "/'/", "\\'", $v );
			//$v = preg_replace( "/#/", "\\#", $v );
			$field_names  .= "$k,";
			$field_values .= "'$v',";
		}
		
		$field_names  = preg_replace( "/,$/" , "" , $field_names  );
		$field_values = preg_replace( "/,$/" , "" , $field_values );
		
		return array( 'FIELD_NAMES'  => $field_names,
					  'FIELD_VALUES' => $field_values,
					);
	}
	
	/*========================================================================*/
    // Create an array from a multidimensional array returning a formatted
    // string ready to use in an UPDATE query, saves having to manually format
    // the FIELD='val', FIELD='val', FIELD='val'
    /*========================================================================*/
    
    function compile_db_update_string($data) {
		
		$return_string = "";
		
		foreach ($data as $k => $v)
		{
			$v = addslashes($v);
			//$v = preg_replace( "/'/", "\\'", $v );
			$return_string .= $k . "='".$v."',";
		}
		
		$return_string = preg_replace( "/,$/" , "" , $return_string );
		
		return $return_string;
	}
	
	/*========================================================================*/
    // Test to see if a field exists by forcing and trapping an error.
    // It ain't pretty, but it do the job don't it, eh?
    // Posh my ass.
    // Return 1 for exists, 0 for not exists and jello for the naked guy
    // Fun fact: The number of times I spelt 'field' as 'feild'in this part: 104
    /*========================================================================*/
    
    function field_exists($field, $table) {
		
		$this->return_die = 1;
		$this->error = "";
		
		$this->query("SELECT COUNT($field) as count FROM $table");
		
		$return = 1;
		
		if ( $this->failed )
		{
			$return = 0;
		}
		
		$this->error = "";
		$this->return_die = 0;
		$this->error_no   = 0;
		$this->failed     = 0;
		
		return $return;
	}
    
} // end class


?>