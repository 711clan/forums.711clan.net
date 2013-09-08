<?php

class FUNC {

	var $time_formats  = array();
	var $time_options  = array();
	var $offset        = "";
	var $offset_set    = 0;
	var $num_format    = "";
	var $allow_unicode = 1;
	var $get_magic_quotes = 0;

	function FUNC() {
		global $INFO, $vboptions, $vbulletin;

		$this->time_options = array( 'JOINED' => $INFO['clock_joined'],
									 'SHORT'  => $INFO['clock_short'],
									 'LONG'   => $INFO['clock_long']
								   );

		$this->num_format = ($INFO['number_format'] == 'space') ? ' ' : $INFO['number_format'];

		$this->get_magic_quotes = get_magic_quotes_gpc();

	}

	function txt_stripslashes($t)
	{
		if ( $this->get_magic_quotes )
		{
    		$t = stripslashes($t);
    	}

    	return $t;
    }

	function txt_raw2form($t="")
	{
		$t = str_replace( '$', "&#036;", $t);

		if ( get_magic_quotes_gpc() )
		{
			$t = stripslashes($t);
		}

		$t = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $t );

		return $t;
	}

	function txt_safeslashes($t="")
	{
		return str_replace( '\\', "\\\\", $this->txt_stripslashes($t));
	}

	function txt_htmlspecialchars($t="")
	{
		// Use forward look up to only convert & not &#123;
		$t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t );
		$t = str_replace( "<", "&lt;"  , $t );
		$t = str_replace( ">", "&gt;"  , $t );
		$t = str_replace( '"', "&quot;", $t );

		return $t; // A nice cup of?
	}

	function txt_UNhtmlspecialchars($t="")
	{
		$t = str_replace( "&amp;" , "&", $t );
		$t = str_replace( "&lt;"  , "<", $t );
		$t = str_replace( "&gt;"  , ">", $t );
		$t = str_replace( "&quot;", '"', $t );

		return $t;
	}

	function return_md5_check()
	{
		global $ibforums;

		if ( $ibforums->member['id'] )
		{
			return md5($ibforums->member['email'].'&'.$ibforums->member['password'].'&'.$ibforums->member['joined']);
		}
		else
		{
			return md5("this is only here to prevent it breaking on guests");
		}
	}

	function trim_leading_comma($t)
	{
		return preg_replace( "/^,/", "", $t );
	}

	function trim_trailing_comma($t)
	{
		return preg_replace( "/,$/", "", $t );
	}


	function clean_comma($t)
	{
		return preg_replace( "/,{2,}/", ",", $t );
	}

	function clean_perm_string($t)
	{
		$t = $this->clean_comma($t);
		$t = $this->trim_leading_comma($t);
		$t = $this->trim_trailing_comma($t);

		return $t;
	}

	/*-------------------------------------------------------------------------*/
	// size_format
	// ------------------
	// Give it a byte to eat and it'll return nice stuff!
	/*-------------------------------------------------------------------------*/

	function size_format($bytes="")
	{
		global $ibforums;

		$retval = "";

		if ($bytes >= 1048576)
		{
			$retval = round($bytes / 1048576 * 100 ) / 100 . " MB";
		}
		else if ($bytes  >= 1024)
		{
			$retval = round($bytes / 1024 * 100 ) / 100 . " kB";
		}
		else
		{
			$retval = $bytes . " Bytes";
		}

		return $retval;
	}

	function hdl_ban_line($bline)
	{
		global $ibforums;

		if ( is_array( $bline ) )
		{
			// Set ( 'timespan' 'unit' )

			$factor = $bline['unit'] == 'd' ? 86400 : 3600;

			$date_end = time() + ( $bline['timespan'] * $factor );

			return time() . ':' . $date_end . ':' . $bline['timespan'] . ':' . $bline['unit'];
		}
		else
		{
			$arr = array();

			list( $arr['date_start'], $arr['date_end'], $arr['timespan'], $arr['unit'] ) = explode( ":", $bline );

			return $arr;
		}

	}

	function check_perms($forum_perm="")
	{
		global $ibforums;

		if ( $forum_perm == "" )
		{
			return FALSE;
		}
		else if ( $forum_perm == '*' )
		{
			return TRUE;
		}
		else
		{
			$forum_perm_array = explode( ",", $forum_perm );

			foreach( $ibforums->perm_id_array as $u_id )
			{
				if ( in_array( $u_id, $forum_perm_array ) )
				{
					return TRUE;
				}
			}

			// Still here? Not a match then.

			return FALSE;
		}
	}

	function do_number_format($number)
	{
		global $ibforums;

		if ($ibforums->vars['number_format'] != 'none')
		{
			return number_format($number , 0, '', $this->num_format);
		}
		else
		{
			return $number;
		}
	}


	function hdl_forum_read_cookie($set="")
	{
		global $ibforums;

		if ( $set == "" )
		{
			// Get cookie and return array...

			if ( $fread = $this->my_getcookie('forum_read') )
			{
				$farray = unserialize(stripslashes($fread));

				if ( is_array($farray) and count($farray) > 0 )
				{
					foreach( $farray as $id => $stamp )
					{
						$ibforums->forum_read[$id] = $stamp;
					}
				}
			}

			return TRUE;
		}
		else
		{
			// Set cookie...

			$fread = addslashes(serialize($ibforums->forum_read));

			$this->my_setcookie('forum_read', $fread);

			return TRUE;
		}
	}

	function scale_image($arg)
	{
		// max_width, max_height, cur_width, cur_height

		$ret = array(
					  'img_width'  => $arg['cur_width'],
					  'img_height' => $arg['cur_height']
					);

		if ( $arg['cur_width'] > $arg['max_width'] )
		{
			$ret['img_width']  = $arg['max_width'];
			$ret['img_height'] = ceil( ( $arg['cur_height'] * ( ( $arg['max_width'] * 100 ) / $arg['cur_width'] ) ) / 100 );
			$arg['cur_height'] = $ret['img_height'];
			$arg['cur_width']  = $ret['img_width'];
		}

		if ( $arg['cur_height'] > $arg['max_height'] )
		{
			$ret['img_height']  = $arg['max_height'];
			$ret['img_width']   = ceil( ( $arg['cur_width'] * ( ( $arg['max_height'] * 100 ) / $arg['cur_height'] ) ) / 100 );
		}


		return $ret;

	}


	function my_nl2br($t="")
	{
		return str_replace( "\n", "<br />", $t );
	}

	function my_br2nl($t="")
	{
		$t = preg_replace( "#(?:\n|\r)?<br />(?:\n|\r)?#", "\n", $t );
		$t = preg_replace( "#(?:\n|\r)?<br>(?:\n|\r)?#"  , "\n", $t );

		return $t;
	}


	function load_template( $name, $id='' )
	{
		global $ibforums, $DB;

		$tags      = 1;

		require ROOT_PATH."arcade/skins/$name.php";
		return new $name();
	}


	function make_profile_link($name, $id="")
	{
		global $ibforums;

		if ($id > 0)
		{
			return "<a href='./member.php?$session[sessionurl]&amp;u=$id'>$name</a>";
		}
		else
		{
			return $name;
		}
	}

	function boink_it($url)
	{
		global $ibforums;

		// Ensure &amp;s are taken care of

		$url = str_replace( "&amp;", "&", $url );

		if ($ibforums->vars['header_redirect'] == 'refresh')
		{
			@header("Refresh: 0;url=".$url);
		}
		else if ($ibforums->vars['header_redirect'] == 'html')
		{
			@flush();
			echo("<html><head><meta http-equiv='refresh' content='0; url=$url'></head><body></body></html>");
			exit();
		}
		else
		{
			@header("Location: ".$url);
		}
		exit();
	}


	/*-------------------------------------------------------------------------*/
    // text_tidy:
    // Takes raw text from the DB and makes it all nice and pretty - which also
    // parses un-HTML'd characters. Use this with caution!
    /*-------------------------------------------------------------------------*/

    function text_tidy($txt = "") {

    	$trans = get_html_translation_table(HTML_ENTITIES);
    	$trans = array_flip($trans);

    	$txt = strtr( $txt, $trans );

    	$txt = preg_replace( "/\s{2}/" , "&nbsp; "      , $txt );
    	$txt = preg_replace( "/\r/"    , "\n"           , $txt );
    	$txt = preg_replace( "/\t/"    , "&nbsp;&nbsp;" , $txt );
    	//$txt = preg_replace( "/\\n/"   , "&#92;n"       , $txt );

    	return $txt;

    }

	/*-------------------------------------------------------------------------*/
    // compile_db_string:
    // Takes an array of keys and values and formats them into a string the DB
    // can use.
    // $array = ( 'THIS' => 'this', 'THAT' => 'that' );
    // will be returned as THIS, THAT  'this', 'that'
    /*-------------------------------------------------------------------------*/

    function compile_db_string($data) {

    	$field_names  = "";
		$field_values = "";

		foreach ($data as $k => $v) {
			$v = preg_replace( "/'/", "\\'", $v );
			$field_names  .= "$k,";
			$field_values .= "'$v',";
		}

		$field_names  = preg_replace( "/,$/" , "" , $field_names  );
		$field_values = preg_replace( "/,$/" , "" , $field_values );

		return array( 'FIELD_NAMES'  => $field_names,
					  'FIELD_VALUES' => $field_values,
					);
	}

	function build_pagelinks($data)
	{
		global $ibforums, $skin_universal;

		$work = array();

		$section = ($data['leave_out'] == "") ? 9 : $data['leave_out'];  // Number of pages to show per section( either side of current), IE: 1 ... 4 5 [6] 7 8 ... 10

		$work['pages']  = 1;

		if ( ($data['TOTAL_POSS'] % $data['PER_PAGE']) == 0 )
		{
			$work['pages'] = $data['TOTAL_POSS'] / $data['PER_PAGE'];
		}
		else
		{
			$number = ($data['TOTAL_POSS'] / $data['PER_PAGE']);
			$work['pages'] = ceil( $number);
		}


		$work['total_page']   = $work['pages'];
		$work['current_page'] = $data['CUR_ST_VAL'] > 0 ? ($data['CUR_ST_VAL'] / $data['PER_PAGE']) + 1 : 1;


		if ($work['pages'] > 1)
		{
			$work['first_page'] = $skin_universal->make_page_jump($data['TOTAL_POSS'],$data['PER_PAGE'], $data['BASE_URL']).$work['pages'];

			for( $i = 0; $i <= $work['pages'] - 1; ++$i )
			{
				$RealNo = $i * $data['PER_PAGE'];
				$PageNo = $i+1;

				if ($RealNo == $data['CUR_ST_VAL'])
				{
					$work['page_span'] .= "<td class=\"alt2\"><span class=\"smallfont\"><strong>{$PageNo}</strong></span></td>";
					$actualpage = $PageNo;
				}
				else
				{

					if ($PageNo < ($work['current_page'] - $section))
					{
						$work['st_dots'] = "<td class=\"alt1\"><a class=\"smallfont\" href='{$data['BASE_URL']}&amp;st=0' title='{$ibforums->lang['ps_page']} 1'>&laquo; {$ibforums->lang['ps_first']}&nbsp;...</a></td>";
						continue;
					}

					// If the next page is out of our section range, add some dotty dots!

					if ($PageNo > ($work['current_page'] + $section))
					{
						$work['end_dots'] = "<td class=\"alt1\"><a class=\"smallfont\" href='{$data['BASE_URL']}&amp;st=".($work['pages']-1) * $data['PER_PAGE']."' title='{$ibforums->lang['ps_page']}{$work['pages']}'>{$ibforums->lang['ps_last']}...&nbsp;&raquo;</a></td>";
						break;
					}


					$work['page_span'] .= "<td class=\"alt1\"><a class=\"smallfont\" href='{$data['BASE_URL']}&amp;st={$RealNo}'>{$PageNo}</a></td>";
				}
			}

			$work['return']    = "<div class=\"pagenav\" align=\"right\"><table class=\"tborder\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\"><tr><td class=\"vbmenu_control\" style=\"font-weight:normal\">".$ibforums->lang['pagenav1'].$actualpage.$ibforums->lang['pagenav2'].$work['first_page'].$ibforums->lang['pagenav3']."</td>".$work['st_dots'].$work['page_span'].'&nbsp;'.$work['end_dots']."</tr></table></div>";
		}
		else
		{
			$work['return']    = $data['L_SINGLE'];
		}

		return $work['return'];
	}

	function clean_email($email = "") {

		$email = trim($email);

		$email = str_replace( " ", "", $email );

    	$email = preg_replace( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $email );

    	if ( preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email) )
    	{
    		return $email;
    	}
    	else
    	{
    		return FALSE;
    	}
	}

    function load_skin() {
    	global $ibforums, $INFO, $DB;

    	$id       = -1;
    	$skin_set = 0;

    	if ( ( $ibforums->is_bot == 1 ) and ($ibforums->vars['spider_suit'] != "") )
    	{
    		$skin_set = 1;
    		$id       = $ibforums->vars['spider_suit'];
    	}
    	else
    	{
			if ($ibforums->input['f'] and $ibforums->input['act'] != 'UserCP')
			{
				if ( $ibforums->vars[ 'forum_skin_'.$ibforums->input['f'] ] != "" )
				{
					$id = $ibforums->vars[ 'forum_skin_'.$ibforums->input['f'] ];

					$skin_set = 1;
				}
			}

			$extra = "";

			if ($skin_set != 1 and $ibforums->vars['allow_skins'] == 1)
			{
				if (isset($ibforums->input['skinid']))
				{
					$id    = intval($ibforums->input['skinid']);
					$extra = " AND s.hidden=0";
					$skin_set = 1;
				}
				else if ( $ibforums->member['skin'] != "" and intval($ibforums->member['skin']) >= 0 )
				{
					$id = $ibforums->member['skin'];

					if ($id == 'Default') $id = -1;

					$skin_set = 1;
				}
			}
    	}

    	if ( $id >= 0 and $skin_set == 1)
    	{
    		$DB->query("SELECT s.*, t.template, c.css_text
    					FROM ibf_skins s
    					  LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					  LEFT JOIN ibf_css c ON (c.cssid=s.css_id)
    	           	   WHERE s.sid=$id".$extra);

    	    if (! $DB->get_num_rows() )
    	    {
    	    	if ( $ibforums->member['id'] )
    	    	{
    	    		$DB->query("UPDATE ibf_members SET skin='-1' WHERE id='".$ibforums->member['id']."'");
    	    	}

    	    		$DB->query("SELECT s.*, t.template, c.css_text
    							FROM ibf_skins s
    					  		 LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					 		 LEFT JOIN ibf_css c ON (s.css_id=c.cssid)
    	           	   		    WHERE s.default_set=1");
    	    }

    	}
    	else
    	{
    		$DB->query("SELECT s.*, t.template, c.css_text
    					FROM ibf_skins s
    					  LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					  LEFT JOIN ibf_css c ON (s.css_id=c.cssid)
    	           	   WHERE s.default_set=1");
    	}

    	if ( ! $row = $DB->fetch_row() )
    	{
    		echo("Could not query the skin information!");
    		exit();
    	}

    	if ( ($ibforums->input['setskin']) and ($ibforums->member['id']) )
    	{
    		$DB->query( "UPDATE ibf_members SET skin=".intval($row['sid'])." WHERE id=".intval($ibforums->member['id']) );

    		$ibforums->member['skin'] = $row['sid'];
    	}

    	return $row;

    }

    function load_words($current_lang_array, $area, $lang_type) {

        require ROOT_PATH."arcade/lang/".$area.".php";

        foreach ($lang as $k => $v)
        {
        	$current_lang_array[$k] = stripslashes($v);
        }

        unset($lang);

        return $current_lang_array;

    }


    function get_date($date, $method) {
        global $ibforums;

        if (!$date)
        {
            return '--';
        }

        if (empty($method))
        {
        	$method = 'LONG';
        }

        if ($this->offset_set == 0)
        {
			$this->offset = $this->get_time_offset();

			$this->offset_set = 1;
        }

        return gmdate($this->time_options[$method], ($date + $this->offset) );
    }

function get_time_offset()
{
	// Zeitanpassung gem. Benutzereinstellungen, angepasst an vBulletin (by MrZeropage)
	
    	global $ibforums, $vboptions, $bbuserinfo, $vbulletin;

	if ($vboptions['templateversion']=="")
	{
	  $vbversion = substr($vbulletin->options[templateversion],0,3);
	}
	else
	{
	  $vbversion = substr($vboptions[templateversion],0,3);
	}
	if ($vbversion == "3.0")
	{
		// vBulletin 3.0.x
		$differenz = $bbuserinfo['timezoneoffset'];
		if ($bbuserinfo['dstonoff'])
		{
			// Sommerzeit aktiv
			$differenz++;
		}
	}
	else
	{
		// vBulletin 3.5.x
		$differenz = $vbulletin->userinfo['timezoneoffset'];
		if ($vbulletin->userinfo['dstonoff'])
		{
			// Sommerzeit aktiv
			$differenz++;
		}
	}

	$differenz = $differenz * 3600;
				
	return $differenz;
}

    function my_setcookie($name, $value = "", $sticky = 1) {
        global $INFO;

        //$expires = "";

        if ($sticky == 1)
        {
        	$expires = time() + 60*60*24*365;
        }

        $INFO['cookie_domain'] = $INFO['cookie_domain'] == "" ? ""  : $INFO['cookie_domain'];
        $INFO['cookie_path']   = $INFO['cookie_path']   == "" ? "/" : $INFO['cookie_path'];

        $name = $INFO['cookie_id'].$name;

        @setcookie($name, $value, $expires, $INFO['cookie_path'], $INFO['cookie_domain']);
    }

    function my_getcookie($name)
    {
    	global $INFO, $HTTP_COOKIE_VARS;

    	if (isset($HTTP_COOKIE_VARS[$INFO['cookie_id'].$name]))
    	{
    		return urldecode($HTTP_COOKIE_VARS[$INFO['cookie_id'].$name]);
    	}
    	else
    	{
    		return FALSE;
    	}

    }

    function parse_incoming()
    {
    	global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_CLIENT_IP, $REQUEST_METHOD, $REMOTE_ADDR, $HTTP_PROXY_USER, $HTTP_X_FORWARDED_FOR;
    	$return = array();

		if( is_array($HTTP_GET_VARS) )
		{
			while( list($k, $v) = each($HTTP_GET_VARS) )
			{
				if( is_array($HTTP_GET_VARS[$k]) )
				{
					while( list($k2, $v2) = each($HTTP_GET_VARS[$k]) )
					{
						$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);
					}
				}
				else
				{
					$return[$k] = $this->clean_value($v);
				}
			}
		}

		if( is_array($HTTP_POST_VARS) )
		{
			while( list($k, $v) = each($HTTP_POST_VARS) )
			{
				if ( is_array($HTTP_POST_VARS[$k]) )
				{
					while( list($k2, $v2) = each($HTTP_POST_VARS[$k]) )
					{
						$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);
					}
				}
				else
				{
					$return[$k] = $this->clean_value($v);
				}
			}
		}

		$addrs = array();

		foreach( array_reverse( explode( ',', $HTTP_X_FORWARDED_FOR ) ) as $x_f )
		{
			$x_f = trim($x_f);

			if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f ) )
			{
				$addrs[] = $x_f;
			}
		}

		$addrs[] = $_SERVER['REMOTE_ADDR'];
		$addrs[] = $HTTP_PROXY_USER;
		$addrs[] = $REMOTE_ADDR;

		$return['IP_ADDRESS'] = $this->select_var( $addrs );

		$return['IP_ADDRESS'] = preg_replace( "/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3.\\4", $return['IP_ADDRESS'] );

		$return['request_method'] = ( $_SERVER['REQUEST_METHOD'] != "" ) ? strtolower($_SERVER['REQUEST_METHOD']) : strtolower($REQUEST_METHOD);

		return $return;
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
    	global $ibforums;

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
    	$val = str_replace( "\""           , "&quot;"        , $val );
    	$val = preg_replace( "/\n/"        , "<br>"          , $val ); // Convert literal newlines
    	$val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
    	$val = preg_replace( "/\r/"        , ""              , $val ); // Remove literal carriage returns
    	$val = str_replace( "!"            , "&#33;"         , $val );
    	$val = str_replace( "'"            , "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.

    	// Ensure unicode chars are OK

    	if ( $this->allow_unicode )
		{
			$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );
		}

		// Strip slashes if not already done so.

    	if ( $this->get_magic_quotes )
    	{
    		$val = stripslashes($val);
    	}

    	// Swop user inputted backslashes

    	$val = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $val );

    	return $val;
    }


    function remove_tags($text="")
    {
    	// Removes < BOARD TAGS > from posted forms

    	$text = preg_replace( "/(<|&lt;)% (BOARD HEADER|CSS|JAVASCRIPT|TITLE|BOARD|STATS|GENERATOR|COPYRIGHT|NAVIGATION) %(>|&gt;)/i", "&#60;% \\2 %&#62;", $text );

    	//$text = str_replace( "<%", "&#60;%", $text );

    	return $text;
    }

    function is_number($number="")
    {

    	if ($number == "") return -1;

    	if ( preg_match( "/^([0-9]+)$/", $number ) )
    	{
    		return $number;
    	}
    	else
    	{
    		return "";
    	}
    }

    /*-------------------------------------------------------------------------*/
    // MEMBER FUNCTIONS
    /*-------------------------------------------------------------------------*/


    function set_up_guest($name='Guest') {
    	global $INFO;

    	return array( 'name'     => $name,
    				  'id'       => 0,
    				  'password' => "",
    				  'email'    => "",
    				  'title'    => "Unregistered",
    				  'mgroup'    => $INFO['guest_group'],
    				  'view_sigs' => $INFO['guests_sig'],
    				  'view_img'  => $INFO['guests_img'],
    				  'view_avs'  => $INFO['guests_ava'],
    				);
    }

    function get_avatar($member_avatar="", $member_view_avatars=0, $avatar_dims="x")
    {
    	global $ibforums, $DB, $vboptions, $vbulletin, $AVATARRESIZE;

	$imagesizer=0;
	if (function_exists('getimagesize') && $AVATARRESIZE==1)
	{
		$imagesizer=1;
	}

	$filename	= $member_avatar['filename'];
	$avatarid 	= $member_avatar['avatarid'];
	$avatarrevision	= $member_avatar['avatarrevision'];
	$userid   	= $member_avatar['userid'];
	$avatardateline	= $member_avatar['dateline'];

	if ($userid == 0)
	{
		// this is a guest
		return "";
	}

	// get size-settings for this user
	$getavatarsizequery = $DB->query("	SELECT g.avatarmaxwidth AS maxwidth, g.avatarmaxheight AS maxheight
						FROM ibf_usergroup AS g
						LEFT JOIN ibf_user AS u
						ON (g.usergroupid = u.usergroupid)
						WHERE u.userid = ".$userid);
	$getavatarsize = $DB->fetch_row();
	$maxavatarwidth  = $getavatarsize['maxwidth'];
	$maxavatarheight = $getavatarsize['maxheight'];

	if ($vboptions['templateversion']=="")
	{
	  $vbversion = substr($vbulletin->options[templateversion],0,3);
	}
	else
	{
	  $vbversion = substr($vboptions[templateversion],0,3);
	}

	if ($vbversion == "3.0")
	{
		$path = $vboptions['avatarurl']."/avatar".$userid."_".$avatarrevision.".gif";
		$forumpath = $vboptions['bburl'];
	}
	else
	{
		$forumpath = $vbulletin->options['bburl'];
		if ($vbulletin->options['usefileavatar'])
		{
			$path = $vbulletin->options['avatarurl']."/avatar".$userid."_".$avatarrevision.".gif";
		}
		else
		{
			$path = $vbulletin->options['bburl'] . "/image.php?u=".$userid."&amp;dateline=".$avatardateline;
		}
	}

	$member_avatar = $member_avatar['avatar'];

		if(preg_match ( "/\.php/", $member_avatar) || preg_match ( "/avatar/", $member_avatar))
		{
			if (preg_match ( "/\.php/", $member_avatar))
			{
				$member_avatar = $forumpath."/".$member_avatar;
			}

			$sizedata="";
			if ($imagesizer==1)
			{
				$check = getimagesize($member_avatar);
				$avatarwidth  = $check[0];
				$avatarheight = $check[1];
				if ($avatarwidth > $maxavatarwidth) { $avatarwidth = $maxavatarwidth; }
				if ($avatarheight > $maxavatarheight) { $avatarheight = $maxavatarheight; }
				$sizedata = "width='".$avatarwidth."' height='".$avatarheight."' ";
			}

			return "<img src='{$member_avatar}' border='0' alt='' ".$sizedata."/>";
		}

	if (($avatarid==0) && ($filename!=""))
	{
		$sizedata="";
		if ($imagesizer==1)
		{
			$check = getimagesize($path);
			$avatarwidth  = $check[0];
			$avatarheight = $check[1];
			if ($avatarwidth > $maxavatarwidth) { $avatarwidth = $maxavatarwidth; }
			if ($avatarheight > $maxavatarheight) { $avatarheight = $maxavatarheight; }
			$sizedata = "width='".$avatarwidth."' height='".$avatarheight."' ";
		}

		return "<img src='{$path}' border='0' alt='' ".$sizedata."/>";
	}

    	if (!$member_avatar or $member_view_avatars == 0 or !$ibforums->vars['avatars_on'])
    	{
    		return "";
    	}

    	if (preg_match ( "/^noavatar/", $member_avatar ))
    	{
    		return "";
    	}

    	if ( (preg_match ( "/\.swf/", $member_avatar)) and ($ibforums->vars['allow_flash'] != 1) )
    	{
    		return "";
    	}

    	$davatar_dims    = explode( "x", $ibforums->vars['avatar_dims'] );
		$default_a_dims  = explode( "x", $ibforums->vars['avatar_def'] );

    	//---------------------------------------
		// Have we enabled URL / Upload avatars?
		//---------------------------------------

		$this_dims = explode( "x", $avatar_dims );
		if (!$this_dims[0]) $this_dims[0] = $davatar_dims[0];
		if (!$this_dims[1]) $this_dims[1] = $davatar_dims[1];

		if ( preg_match( "/^http:\/\//", $member_avatar ) )
		{
			// Ok, it's a URL..

			if (preg_match ( "/\.swf/", $member_avatar))
			{
				return "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" width='{$this_dims[0]}' height='{$this_dims[1]}'>
						<param name='movie' value='{$member_avatar}'><param name='play' value='true'>
						<param name='loop' value='true'><param name='quality' value='high'>
						<embed src='{$member_avatar}' width='{$this_dims[0]}' height='{$this_dims[1]}' play='true' loop='true' quality='high'></embed>
						</object>";
			}
			else
			{
				return "<img src='{$member_avatar}' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}' alt='' />";
			}

			//---------------------------------------
			// Not a URL? Is it an uploaded avatar?
			//---------------------------------------
		}
		else if ( ($ibforums->vars['avup_size_max'] > 1) and ( preg_match( "/^upload:av-(?:\d+)\.(?:\S+)/", $member_avatar ) ) )
		{
			$member_avatar = preg_replace( "/^upload:/", "", $member_avatar );

			if ( preg_match ( "/\.swf/", $member_avatar) )
			{
				return "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" width='{$this_dims[0]}' height='{$this_dims[1]}'>
						<param name='movie' value='{$ibforums->vars['upload_url']}/$member_avatar'><param name='play' value='true'>
						<param name='loop' value='true'><param name='quality' value='high'>
					    <embed src='{$ibforums->vars['upload_url']}/$member_avatar\' width='{$this_dims[0]}' height='{$this_dims[1]}' play='true' loop='true' quality='high'></embed>
						</object>";
			}
			else
			{
				return "<img src='{$ibforums->vars['upload_url']}/$member_avatar' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}' alt='' />";
			}
		}

		//---------------------------------------
		// No, it's not a URL or an upload, must
		// be a normal avatar then
		//---------------------------------------

		else if ($member_avatar != "")
		{
			//---------------------------------------
			// Do we have an avatar still ?
		   	//---------------------------------------

			$url = $ibforums->vars['AVATARS_URL']."/".$member_avatar;

			$sizedata="";
			if ($imagesizer==1)
			{
				$check = getimagesize($url);
				$avatarwidth  = $check[0];
				$avatarheight = $check[1];
				if ($avatarwidth > $maxavatarwidth) { $avatarwidth = $maxavatarwidth; }
				if ($avatarheight > $maxavatarheight) { $avatarheight = $maxavatarheight; }
				$sizedata = "width='".$avatarwidth."' height='".$avatarheight."' ";
			}

			return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' alt='' ".$sizedata."/>";
		}
		else
		{
			//---------------------------------------
			// No, ok - return blank
			//---------------------------------------

			return "";
		}
    }




    /*-------------------------------------------------------------------------*/
    // ERROR FUNCTIONS
    /*-------------------------------------------------------------------------*/

    function Error($error) {
    	global $DB, $ibforums, $skin_universal;

    	//INIT is passed to the array if we've not yet loaded a skin and stuff

    	if ( $error['INIT'] == 1)
    	{

    		$DB->query("SELECT s.*, t.template, c.css_text
    					FROM ibf_skins s
    					  LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					  LEFT JOIN ibf_css c ON (s.css_id=c.cssid)
    	           	   WHERE s.default_set=1");

    	    $ibforums->skin = $DB->fetch_row();

    		$ibforums->session_id = $this->my_getcookie('session_id');

			$ibforums->base_url   = $ibforums->vars['board_url'].'/index.'.$ibforums->vars['php_ext'].'?s='.$ibforums->session_id;
			$ibforums->skin_rid   = $ibforums->skin['set_id'];
			$ibforums->skin_id    = 's'.$ibforums->skin['set_id'];

			if ($ibforums->vars['default_language'] == "")
			{
				$ibforums->vars['default_language'] = 'en';
			}

			$ibforums->lang_id = $ibforums->member['language'] ? $ibforums->member['language'] : $ibforums->vars['default_language'];

			if ( ($ibforums->lang_id != $ibforums->vars['default_language']) and (! is_dir( ROOT_PATH."lang/".$ibforums->lang_id ) ) )
			{
				$ibforums->lang_id = $ibforums->vars['default_language'];
			}

			$ibforums->lang = $this->load_words($ibforums->lang, "lang_global", $ibforums->lang_id);

			$ibforums->vars['img_url']   = 'style_images/' . $ibforums->skin['img_dir'];

			//$skin_universal = $this->load_template('skin_global');

		}

		//$skin_universal = $this->load_template('skin_global');

    	//$ibforums->lang = $this->load_words($ibforums->lang, "lang_error", $ibforums->lang_id);
	// Implementation of ACP-Language-Selector by MrZeropage
    		$DB->query("SELECT arcade_language FROM ibf_games_settings");

    	    	$languagesetting = $DB->fetch_row();

	$ibforums->langid = $languagesetting['arcade_language'];
	$langfile="lang_Arcade_".$ibforums->langid;
	$ibforums->lang = $this->load_words($ibforums->lang, $langfile, $ibforums->lang_id );

    	list($em_1, $em_2) = explode( '@', $ibforums->vars['email_in'] );

    	$msg = $ibforums->lang[ $error['MSG'] ];

    	if ($error['EXTRA']!="")
    	{
		if (preg_match("/<#EXTRA#>/", $msg))
		{
			$msg = preg_replace( "/<#EXTRA#>/", $error['EXTRA'], $msg );
		}
		else
		{
			$msg .= "<br /><br />".$error['EXTRA'];
		}
    	}

    	$html = $skin_universal->Error( $msg, $em_1, $em_2);

    	//-----------------------------------------
    	// If we're a guest, show the log in box..
    	//-----------------------------------------

    	if ($ibforums->member['id'] == "" and $error['MSG'] != 'server_too_busy' and $error['MSG'] != 'account_susp')
    	{
    		$html = str_replace( "<!--IBF.LOG_IN_TABLE-->", $skin_universal->error_log_in($_SERVER['QUERY_STRING']), $html);
    	}

    	//-----------------------------------------
    	// Do we have any post data to keepy?
    	//-----------------------------------------

    	if ( $ibforums->input['act'] == 'Post' OR $ibforums->input['act'] == 'Msg' OR $ibforums->input['act'] == 'calendar' )
    	{
    		if ( $_POST['Post'] )
    		{
    			$post_thing = $skin_universal->error_post_textarea($this->txt_htmlspecialchars($this->txt_stripslashes($_POST['Post'])) );

    			$html = str_replace( "<!--IBF.POST_TEXTAREA-->", $post_thing, $html );
    		}
    	}


    	$print = new display();

    	$print->add_output($html);

    	$print->do_output( array(
    								OVERRIDE   => 1,
    								TITLE      => $ibforums->lang['error_title'],
    							 )
    					  );
    }




    function board_offline()
    {
    	global $DB, $ibforums, $root_path, $skin_universal;

//    	$ibforums->lang = $this->load_words($ibforums->lang, "lang_error", $ibforums->lang_id);
// Implementation of ACP-Language-Selector by MrZeropage
    		$DB->query("SELECT arcade_language FROM ibf_games_settings");

    	    	$languagesetting = $DB->fetch_row();

	$ibforums->langid = $languagesetting['arcade_language'];
	$langfile="lang_Arcade_".$ibforums->langid;
	$ibforums->lang = $this->load_words($ibforums->lang, $langfile, $ibforums->lang_id );

    	$msg = preg_replace( "/\n/", "<br>", stripslashes($ibforums->vars['offline_msg']) );

    	$html = $skin_universal->board_offline( $msg );

    	$print = new display();

    	$print->add_output($html);

    	$print->do_output( array(
    								OVERRIDE   => 1,
    								TITLE      => $ibforums->lang['offline_title'],
    							 )
    					  );
    }

    function select_var($array) {

    	if ( !is_array($array) ) return -1;

    	ksort($array);


    	$chosen = -1;  // Ensure that we return zero if nothing else is available

    	foreach ($array as $k => $v)
    	{
    		if (isset($v))
    		{
    			$chosen = $v;
    			break;
    		}
    	}

    	return $chosen;
    }


} // end class


//######################################################
// Our "print" class
//######################################################


class display {

    var $to_print = "";

    //-------------------------------------------
    // Appends the parsed HTML to our class var
    //-------------------------------------------

    function add_output($to_add) {
        $this->to_print .= $to_add;
        //return 'true' on success
        return true;
    }

    //-------------------------------------------
    // Parses all the information and prints it.
    //-------------------------------------------

    function do_output($output_array)
    {
        global $DB, $ibforums, $arcade, $header, $vbphrase, $vboptions, $stylevar, $headinclude, $bbuserinfo, $session, $show, $pmbox;
	global $forumjump, $timezone, $logincode, $_USEROPTIONS, $scriptpath, $admincpdir, $modcpdir, $quickchooserbits;
	global $languagechooserbits, $spacer_open, $spacer_close, $vbulletin, $navbar, $footer;
	global $template_hook, $notifications_menubits, $notifications_total, $notices, $ad_location;
	global $vba_options;
	// add additional needed variables here

		if(!isset($output_array['NAV'])) {
			$output_array['NAV'][] = "Arcade Error";
		}
		foreach($output_array['NAV'] as $key => $value) {
			$output_array['NAV'][$key] = str_replace("{", "", $output_array['NAV'][$key]);
			$output_array['NAV'][$key] = str_replace("}", "", $output_array['NAV'][$key]);
		}

		require_once('./includes/functions.php');
		require_once('./includes/functions_user.php');

		// get vB-version
  	if ($vboptions['templateversion']=="")
  	{
  	  $vbversion = substr($vbulletin->options[templateversion],0,3);
  	}
  	else
  	{
  	  $vbversion = substr($vboptions[templateversion],0,3);
  	}

		// construct HTML-TITLE
		$forumname = ($vbulletin->options['bbtitle']) ? $vbulletin->options['bbtitle'] : $vboptions['bbtitle'];
		$link = $_SERVER['REQUEST_URI'];
		
		if (strpos($link,"do=play"))
		{
		  // playing a game
		  $gameid = intval(substr($link,strpos($link,"gameid=")+7));
			$query = $DB->query("SELECT gtitle FROM ibf_games_list WHERE gid=".$gameid);
			if ($row = $DB->fetch_row($query))
			{
		  	$action = $row['gtitle'];
			}
		}

		if (strpos($link,"do=stats"))
		{
		  // highscores
		  $gameid = intval(substr($link,strpos($link,"gameid=")+7));
			$query = $DB->query("SELECT gtitle FROM ibf_games_list WHERE gid=".$gameid);
			if ($row = $DB->fetch_row($query))
			{
		  	$action = $row['gtitle']." - ".$ibforums->lang['hscores_title'];
			}
		}

		if (strpos($link,"do=viewtourney"))
		{
		  // one tournament
		  $tid = intval(substr($link,strpos($link,"tid=")+4));
			$query = $DB->query("SELECT g.gtitle FROM ibf_games_list AS g LEFT JOIN ibf_tournaments AS t ON (t.gid = g.gid) WHERE tid=".$tid);
			if ($row = $DB->fetch_row($query))
			{
		  	$action = $ibforums->lang['tournament']." - ".$row['gtitle'];
			}
		}

		if (strpos($link,"do=viewtournaments") || strpos($link,"do=viewtourneyend"))
		{
		  // tournaments
		  $action = $ibforums->lang['tournament_view'];
		}

		if (strpos($link,"do=createtourney"))
		{
		  // create tournament
		  $action = $ibforums->lang['create_tourney'];
		}

		if (strpos($link,"module=settings"))
		{
		  // settings
		  $action = $ibforums->lang['your_settings'];
		}

		if (strpos($link,"module=favorites"))
		{
		  // favorites
		  $action = $ibforums->lang['my_favs'];
		}
		
		if (strpos($link,"module=report"))
		{
		  // ranking
		  $action = $ibforums->lang['your_report'];
		}

		if (strpos($link,"module=league"))
		{
		  // league
		  $action = $ibforums->lang['league'];
		}
		
		$ibprotitle = $ibforums->lang['htmltitle'];   // read from settings in /arcade.php
		$ibprotitle = preg_replace('/%FORUMNAME%/',$forumname,$ibprotitle);
		$ibprotitle = preg_replace('/%IBPRO%/',$ibforums->lang['page_title'],$ibprotitle);
		$ibprotitle = preg_replace('/%ACTION%/',$action,$ibprotitle);
		$ibprotitle = rtrim($ibprotitle);
		$ibprotitle = rtrim($ibprotitle," -");

		// HTML-TITLE DONE :)

if (intval(substr($vbversion,0,1)) < 4)
{
		$debug = false;
		$navbits = $output_array['NAV'];
		$navbits = construct_navbits($navbits);
		eval('$navbar="' . fetch_template('navbar') . '";');
		eval('$footer="' . fetch_template('footer') . '";');
		$maincontent = $this->to_print;
		eval('print_output("' . fetch_template('ARCADE') . '");');
}
else
{
		// we are on vB 4 or later WHOHOOO
		$debug = false;
		$navbits = $output_array['NAV'];
		$navbits = construct_navbits($navbits);
		$navbits = construct_navbits(array('' => $ibforums->lang['page_title']));
		$navbar = render_navbar_template(construct_navbits($navbits));
		$maincontent = $this->to_print;
		$templater = vB_Template::create('ARCADE');
		$templater->register_page_templates();
		$templater->register('navbar', $navbar);
		$templater->register('footer', $footer);
		$templater->register('ibprotitle', $ibprotitle);
		$templater->register('pagetitle', $ibforums->lang['page_title']);
		$templater->register('maincontent', $maincontent);
		$templater->register('arcadeheader', $arcadeheader);
		print_output($templater->render());
}

        exit;
    }

    //-------------------------------------------
    // print the headers
    //-------------------------------------------

    function do_headers() {
    	global $ibforums;

    	if ($ibforums->vars['print_headers'])
    	{
			@header("HTTP/1.0 200 OK");
			@header("HTTP/1.1 200 OK");
			@header("Content-type: text/html");

			if ($ibforums->vars['nocache'])
			{
				@header("Cache-Control: no-cache, must-revalidate, max-age=0");
				@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				@header("Pragma: no-cache");
			}
        }
    }

    function redirect_screen($text="", $url="", $override=0)
    {
		global $vbulletin;
		include_once "./includes/functions.php";
		$vbulletin->url = $url;
		standard_redirect($text, $url);
		exit;
    }

    function pop_up_window($title = 'Invision Power Board', $text = "" )
    {
    	global $ibforums, $skin_universal, $DB;

    	//---------------------------------------------------------
        // CSS
        //---------------------------------------------------------

        if ( $ibforums->skin['css_method'] == 'external' )
        {
        	$css = $skin_universal->css_external($ibforums->skin['css_id'], $ibforums->skin['img_dir']);
        }
        else
        {
        	$css = $skin_universal->css_inline( str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $ibforums->skin['css_text'] ) );
        }

    	$html = $skin_universal->pop_up_window($title, $css, $text);

    	$TAGS = $DB->query("SELECT macro_value, macro_replace FROM ibf_macro WHERE macro_set='{$ibforums->skin['macro_id']}'");

    	while ( $row = $DB->fetch_row($TAGS) )
      	{
			if ($row['macro_value'] != "")
			{
				$html = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $html );
			}
		}

		$html = str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $html );

    	$DB->close_db();

    	if ($ibforums->vars['disable_gzip'] != 1)
        {
        	$buffer = ob_get_contents();
        	ob_end_clean();
        	ob_start('ob_gzhandler');
        	print $buffer;
        }

        $this->do_headers();

    	echo ($html);
    	exit;
    }


} // END class




//######################################################
// Our "session" class
//######################################################


class session {

    var $ip_address = 0;
    var $user_agent = "";
    var $time_now   = 0;
    var $session_id = 0;
    var $session_dead_id = 0;
    var $session_user_id = 0;
    var $session_user_pass = "";
    var $last_click        = 0;
    var $location          = "";
    var $member            = array();

    // No need for a constructor

    function authorise()
    {
        global $DB, $INFO, $ibforums, $std, $HTTP_SERVER_VARS;

        //-------------------------------------------------
        // Before we go any lets check the load settings..
        //-------------------------------------------------

        if ($ibforums->vars['load_limit'] > 0)
        {
        	if ( file_exists('/proc/loadavg') )
        	{
        		if ( $fh = @fopen( '/proc/loadavg', 'r' ) )
        		{
        			$data = @fread( $fh, 6 );
        			@fclose( $fh );

        			$load_avg = explode( " ", $data );

        			$ibforums->server_load = trim($load_avg[0]);

        			if ($ibforums->server_load > $ibforums->vars['load_limit'])
        			{
        				$std->Error( array( 'LEVEL' => 1, 'MSG' => 'server_too_busy', 'INIT' => 1 ) );
        			}
        		}
        	}
        	else
        	{
				if ( $serverstats = @exec("uptime") )
				{
					preg_match( "/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $serverstats, $load );

					$ibforums->server_load = $load[1];
				}
			}
        }

        //--------------------------------------------
		// Are they banned?
		//--------------------------------------------

		if ($ibforums->vars['ban_ip'])
		{
			$ips = explode( "|", $ibforums->vars['ban_ip'] );

			foreach ($ips as $ip)
			{
				$ip = preg_replace( "/\*/", '.*' , preg_quote($ip, "/") );

				if ( preg_match( "/^$ip/", $ibforums->input['IP_ADDRESS'] ) )
				{
					$std->Error( array( 'LEVEL' => 1, 'MSG' => 'you_are_banned', 'INIT' => 1 ) );
				}
			}
		}

        //--------------------------------------------

        $this->member = array( 'id' => 0, 'password' => "", 'name' => "", 'mgroup' => $INFO['guest_group'] );

        //--------------------------------------------
        // no new headers if we're simply viewing an attachment..
        //--------------------------------------------

        if ( $ibforums->input['act'] == 'Attach' )
        {
        	return $this->member;
        }

        $HTTP_SERVER_VARS['HTTP_USER_AGENT'] = $std->clean_value($HTTP_SERVER_VARS['HTTP_USER_AGENT']);

        $this->ip_address = $ibforums->input['IP_ADDRESS'];
        $this->user_agent = substr($HTTP_SERVER_VARS['HTTP_USER_AGENT'],0,50);
        $this->time_now   = time();

        //-------------------------------------------------
        // Manage bots? (tee-hee)
        //-------------------------------------------------

        if ( $ibforums->vars['spider_sense'] == 1 )
        {

        	$remap_agents = array(
        						   'googlebot'     => 'google',
        						   'slurp@inktomi' => 'inktomi',
        						   'ask jeeves'    => 'jeeves',
        						   'lycos'         => 'lycos',
        						   'whatuseek'     => 'wuseek',
        						   'ia_archiver'   => 'Archive_org',
        						 );

        	if ( preg_match( '/(googlebot|slurp@inktomi|ask jeeves|lycos|whatuseek|ia_archiver)/i', $HTTP_SERVER_VARS['HTTP_USER_AGENT'], $match ) )
        	{

        		$DB->query("SELECT * from ibf_groups WHERE g_id=".$ibforums->vars['spider_group']);

        		$group = $DB->fetch_row();

				foreach ($group as $k => $v)
				{
					$this->member[ $k ] = $v;
				}

				$this->member['restrict_post']    = 1;
				$this->member['g_use_search']     = 0;
				$this->member['g_email_friend']   = 0;
				$this->member['g_edit_profile']   = 0;
				$this->member['g_use_pm']         = 0;
				$this->member['g_is_supmod']      = 0;
				$this->member['g_access_cp']      = 0;
				$this->member['g_access_offline'] = 0;
				$this->member['g_avoid_flood']    = 0;
				$this->member['id']               = 0;

				$ibforums->perm_id       = $this->member['g_perm_id'];
       			$ibforums->perm_id_array = explode( ",", $ibforums->perm_id );
       			$ibforums->session_type  = 'cookie';
       			$ibforums->is_bot        = 1;
       			$this->session_id        = "";

       			if ( ! $agent = $remap_agents[ $match[1] ] )
				{
					$agent = 'google';
				}

       			if ( $ibforums->vars['spider_visit'] )
       			{
       				$dba = $DB->compile_db_insert_string( array (
       																'bot'          => $agent,
       																'query_string' => str_replace( "'", "", $HTTP_SERVER_VARS['QUERY_STRING']),
       																'ip_address'   => $_SERVER['REMOTE_ADDR'],
       																'entry_date'   => time(),
       													)        );

       				$DB->query("INSERT INTO ibf_spider_logs ({$dba['FIELD_NAMES']}) VALUES({$dba['FIELD_VALUES']})");
       			}

       			if ( $ibforums->vars['spider_active'] )
       			{
       				$DB->query("DELETE FROM ibf_sessions WHERE id='".$agent."_session'");

       				$this->create_bot_session($agent);
       			}

       			return $this->member;
        	}
        }

        //-------------------------------------------------
        // Continue!
        //-------------------------------------------------

        $cookie = array();
        $cookie['session_id']   = $std->my_getcookie('bbsessionhash');
        $cookie['member_id']    = $std->my_getcookie('bbuserid');
        $cookie['pass_hash']    = $std->my_getcookie('bbpassword');


        if ( $cookie['session_id'] )
        {
        	$this->get_session($cookie['session_id']);
        	$ibforums->session_type = 'cookie';
        }
        elseif ( $ibforums->input['s'] )
        {
        	$this->get_session($ibforums->input['s']);
        	$ibforums->session_type = 'url';
        }
        else
        {
        	$this->session_id = 0;
        }

        //-------------------------------------------------
        // Finalise the incoming data..
        //-------------------------------------------------

        $ibforums->input['Privacy'] = $std->select_var( array(
															   1 => $ibforums->input['Privacy'],
															   2 => $std->my_getcookie('anonlogin')
												      )      );

		//-------------------------------------------------
		// Do we have a valid session ID?
		//-------------------------------------------------

		if ( $this->session_id )
		{
			// We've checked the IP addy and browser, so we can assume that this is
			// a valid session.

			if ( ($this->session_user_id != 0) and ( ! empty($this->session_user_id) ) )
			{
				// It's a member session, so load the member.

				$this->load_member($this->session_user_id);

				// Did we get a member?

				if ( (! $this->member['id']) or ($this->member['id'] == 0) )
				{
					$this->unload_member();
					$this->update_guest_session();
				}
				else
				{
					$this->update_member_session();
				}
			}
			else
			{
				$this->update_guest_session();
			}

		}
		else
		{
			// We didn't have a session, or the session didn't validate

			// Do we have cookies stored?

			if ($cookie['member_id'] != "" and $cookie['pass_hash'] != "")
			{
				$this->load_member($cookie['member_id']);

				if ( (! $this->member['id']) or ($this->member['id'] == 0) )
				{
					$this->unload_member();
					$this->create_guest_session();
				}
				else
				{
					if ($this->member['password'] == $cookie['pass_hash'])
					{
						$this->create_member_session();
					}
					else
					{
						$this->unload_member();
						$this->create_guest_session();
					}
				}
			}
			else
			{
				$this->create_guest_session();
			}
		}

        //-------------------------------------------------
        // Set up a guest if we get here and we don't have a member ID
        //-------------------------------------------------

        if (! $this->member['id'])
        {
        	$this->member = $std->set_up_guest();
        	$DB->query("SELECT * from ibf_groups WHERE g_id='".$INFO['guest_group']."'");
        	$group = $DB->fetch_row();

			foreach ($group as $k => $v)
			{
				$this->member[ $k ] = $v;
			}

		}

        //------------------------------------------------
        // Synchronise the last visit and activity times if
        // we have some in the member profile
        //-------------------------------------------------

        if ($this->member['id'])
        {
        	if ( ! $ibforums->input['last_activity'] )
        	{
				if ($this->member['last_activity'])
				{
					$ibforums->input['last_activity'] = $this->member['last_activity'];
				}
				else
				{
					$ibforums->input['last_activity'] = $this->time_now;
				}
        	}
        	//------------

        	if ( ! $ibforums->input['last_visit'] )
        	{
				if ($this->member['last_visit'])
				{
					$ibforums->input['last_visit'] = $this->member['last_visit'];
				}
				else
				{
					$ibforums->input['last_visit'] = $this->time_now;
				}
        	}

			//-------------------------------------------------
			// If there hasn't been a cookie update in 2 hours,
			// we assume that they've gone and come back
			//-------------------------------------------------

			if (!$this->member['last_visit'])
			{
				// No last visit set, do so now!

				$DB->query("UPDATE ibf_members SET last_visit='".$this->time_now."', last_activity='".$this->time_now."' WHERE id=".$this->member['id']);

			}
			else if ( (time() - $ibforums->input['last_activity']) > 300 )
			{
				// If the last click was longer than 5 mins ago and this is a member
				// Update their profile.

				$DB->query("UPDATE ibf_members SET last_activity='".$this->time_now."' WHERE id=".$this->member['id']);

			}

			//-------------------------------------------------
			// Check ban status
			//-------------------------------------------------

			if ( $this->member['temp_ban'] )
			{
				$ban_arr = $std->hdl_ban_line(  $this->member['temp_ban'] );

				if ( time() >= $ban_arr['date_end'] )
				{
					// Update this member's profile

					$DB->query("UPDATE ibf_members SET temp_ban='' WHERE id=".intval($this->member['id']) );
				}
				else
				{
					$ibforums->member = $this->member; // Set time right
					$std->Error( array( 'LEVEL' => 1, 'MSG' => 'account_susp', 'INIT' => 1, 'EXTRA' => $std->get_date($ban_arr['date_end'],'LONG') ) );
				}
			}

		}

		//-------------------------------------------------
        // Set a session ID cookie
        //-------------------------------------------------

        $std->my_setcookie("session_id", $this->session_id, -1);

        $ibforums->perm_id = ( $this->member['org_perm_id'] ) ? $this->member['org_perm_id'] : $this->member['g_perm_id'];

        $ibforums->perm_id_array = explode( ",", $ibforums->perm_id );

        return $this->member;

    }

    //+-------------------------------------------------
	// Attempt to load a member
	//+-------------------------------------------------

    function load_member($member_id=0)
    {
    	global $DB, $std, $ibforums;

    	$member_id = intval($member_id);

     	if ($member_id != 0)
        {

            $DB->query("SELECT moderator.mid as is_mod, moderator.allow_warn, m.id, m.name, m.mgroup, m.password, m.email, m.restrict_post, m.view_sigs, m.view_avs, m.view_pop, m.view_img, m.auto_track, m.arcade_ban, m.times_played,
                              m.mod_posts, m.language, m.skin, m.new_msg, m.show_popup, m.msg_total, m.time_offset, m.posts, m.joined, m.last_post,
            				  m.last_visit, m.last_activity, m.dst_in_use, m.view_prefs, m.org_perm_id, m.temp_ban, m.sub_end, g.*
            				  FROM ibf_members m
            				    LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup)
            				    LEFT JOIN ibf_moderators moderator ON (moderator.member_id=m.id OR moderator.group_id=m.mgroup )
            				  WHERE m.id=$member_id");

            if ( $DB->get_num_rows() )
            {
            	$this->member = $DB->fetch_row();
            }

            //-------------------------------------------------
            // Unless they have a member id, log 'em in as a guest
            //-------------------------------------------------

            if ( ($this->member['id'] == 0) or (empty($this->member['id'])) )
            {
				$this->unload_member();
            }
		}

		unset($member_id);
	}

	//+-------------------------------------------------
	// Remove the users cookies
	//+-------------------------------------------------

	function unload_member()
	{
		global $DB, $std, $ibforums;

		// Boink the cookies

		$std->my_setcookie( "member_id" , "0", -1  );
		$std->my_setcookie( "pass_hash" , "0", -1  );

		$this->member['id']       = 0;
		$this->member['name']     = "";
		$this->member['password'] = "";

	}

    //-------------------------------------------
    // Updates a current session.
    //-------------------------------------------

    function update_member_session() {
        global $DB, $ibforums;

        // Make sure we have a session id.

        if ( ! $this->session_id )
        {
        	$this->create_member_session();
        	return;
        }

        if (empty($this->member['id']))
        {
        	$this->unload_member();
        	$this->create_guest_session();
        	return;
        }


        $db_str = $DB->compile_db_update_string(
        										 array(
        										 		'member_name'  => $this->member['name'],
														'member_id'    => intval($this->member['id']),
														'member_group' => $this->member['mgroup'],
														'in_forum'     => intval($ibforums->input['f']),
														'in_topic'     => intval($ibforums->input['t']),
														'in_game'      => intval($ibforums->input['gameid'])."|".$ibforums->input['do'],
														'login_type'   => $ibforums->input['Privacy'],
														'running_time' => $this->time_now,
														'location'     => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE']
													  )
											  );

        $DB->query("UPDATE ibf_sessions SET $db_str WHERE id='{$this->session_id}'");

    }

    //--------------------------------------------------------------------

    function update_guest_session()
    {
        global $DB, $ibforums, $INFO;

        // Make sure we have a session id.

        if ( ! $this->session_id )
        {
        	$this->create_guest_session();
        	return;
        }

        $query  = "UPDATE ibf_sessions SET member_name='',member_id='0',member_group='".$INFO['guest_group']."'";
        $query .= ",login_type='0', running_time='".$this->time_now."', in_forum='".$ibforums->input['f']."', in_topic='".$ibforums->input['t']."', location='".$ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE']."' ";
        $query .= "WHERE id='".$this->session_id."'";

        // Update the database

        $DB->query($query);
    }


    //-------------------------------------------
    // Get a session based on the current session ID
    //-------------------------------------------

    function get_session($session_id="")
    {
        global $DB, $INFO, $std;

        $result = array();

        $query = "";

        $session_id = preg_replace("/([^a-zA-Z0-9])/", "", $session_id);

        if ( $session_id )
        {

			if ($INFO['match_browser'] == 1)
			{
				$query = " AND browser='".$this->user_agent."'";
			}

			$DB->query("SELECT id, member_id, running_time, location FROM ibf_sessions WHERE id='".$session_id."' and ip_address='".$this->ip_address."'".$query);

			if ( $DB->get_num_rows() != 1 )
			{
				// Either there is no session, or we have more than one session..

				$this->session_dead_id   = $session_id;
				$this->session_id        = 0;
        		$this->session_user_id   = 0;
        		return;
			}
			else
			{
				$result = $DB->fetch_row();

				if ($result['id'] == "")
				{
					$this->session_dead_id   = $session_id;
					$this->session_id        = 0;
					$this->session_user_id   = 0;
					unset($result);
					return;
				}
				else
				{
					$this->session_id        = $result['id'];
					$this->session_user_id   = $result['member_id'];
					$this->last_click        = $result['running_time'];
        			$this->location          = $result['location'];
        			unset($result);
					return;
				}
			}
		}
    }

    //-------------------------------------------
    // Creates a member session.
    //-------------------------------------------

    function create_member_session()
    {
        global $DB, $INFO, $std, $ibforums;

        if ($this->member['id'])
        {
        	//---------------------------------
        	// Remove the defunct sessions
        	//---------------------------------

			$INFO['session_expiration'] = $INFO['session_expiration'] ? (time() - $INFO['session_expiration']) : (time() - 3600);

			$DB->query( "DELETE FROM ibf_sessions WHERE running_time < {$INFO['session_expiration']} or member_id='".$this->member['id']."'");

			$this->session_id  = md5( uniqid(microtime()) );

			//---------------------------------
        	// Insert the new session
        	//---------------------------------

			$DB->query("INSERT INTO ibf_sessions (id, member_name, member_id, ip_address, browser, running_time, location, login_type, member_group) ".
					   "VALUES ('".$this->session_id."', '".$this->member['name']."', '".$this->member['id']."', '".$this->ip_address."', '".$this->user_agent."', '".$this->time_now."', ".
					   "',,', '".$ibforums->input['Privacy']."', ".$this->member['mgroup'].")");

			// If this is a member, update their last visit times, etc.

			if (time() - $this->member['last_activity'] > 300)
			{
				//---------------------------------
				// Reset the topics read cookie..
				//---------------------------------

				$std->my_setcookie('topicsread', '');

				$DB->query("UPDATE ibf_members SET last_visit=last_activity, last_activity='".$this->time_now."' WHERE id='".$this->member['id']."'");

				//---------------------------------
				// Fix up the last visit/activity times.
				//---------------------------------

				$ibforums->input['last_visit']    = $this->member['last_activity'];
				$ibforums->input['last_activity'] = $this->time_now;
			}
		}
		else
		{
			$this->create_guest_session();
		}
    }

    //--------------------------------------------------------------------

    function create_guest_session() {
        global $DB, $INFO, $std, $ibforums;

		//---------------------------------
		// Remove the defunct sessions
		//---------------------------------

		if ( ($this->session_dead_id != 0) and ( ! empty($this->session_dead_id) ) )
		{
			$extra = " or id='".$this->session_dead_id."'";
		}
		else
		{
			$extra = "";
		}

		$INFO['session_expiration'] = $INFO['session_expiration'] ? (time() - $INFO['session_expiration']) : (time() - 3600);

		$DB->query( "DELETE FROM ibf_sessions WHERE running_time < {$INFO['session_expiration']} or ip_address='".$this->ip_address."'".$extra);

		$this->session_id  = md5( uniqid(microtime()) );

		//---------------------------------
		// Insert the new session
		//---------------------------------

		$DB->query("INSERT INTO ibf_sessions (id, member_name, member_id, ip_address, browser, running_time, location, login_type, member_group) ".
				   "VALUES ('".$this->session_id."', '', '0', '".$this->ip_address."', '".$this->user_agent."', '".$this->time_now."', ".
				   "',,', '0', ".$INFO['guest_group'].")");

    }

    //-------------------------------------------
    // Creates a BOT session
    //-------------------------------------------

    function create_bot_session($bot)
    {
        global $DB, $INFO, $std, $ibforums;

        $db_str = $DB->compile_db_insert_string(
        										 array(
        										 		'id'           => $bot.'_session',
        										 		'member_name'  => $ibforums->vars['sp_'.$bot],
														'member_id'    => 0,
														'member_group' => $ibforums->vars['spider_group'],
														'in_forum'     => intval($ibforums->input['f']),
														'in_topic'     => intval($ibforums->input['t']),
														'login_type'   => $ibforums->vars['spider_anon'],
														'running_time' => $this->time_now,
														'location'     => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'],
														'ip_address'   => $this->ip_address,
														'browser'      => $this->user_agent,
													  )
											  );

		$DB->query("INSERT INTO ibf_sessions ({$db_str['FIELD_NAMES']}) VALUES({$db_str['FIELD_VALUES']})");

    }

    //-------------------------------------------
    // Updates a BOT current session.
    //-------------------------------------------

    function update_bot_session($bot)
    {
        global $DB, $ibforums, $INFO;

        $db_str = $DB->compile_db_update_string(
        										 array(
        										 		'member_name'  => $ibforums->vars['sp_'.$bot],
														'member_id'    => 0,
														'member_group' => $ibforums->vars['spider_group'],
														'in_forum'     => intval($ibforums->input['f']),
														'in_topic'     => intval($ibforums->input['t']),
														'login_type'   => $ibforums->vars['spider_anon'],
														'running_time' => $this->time_now,
														'location'     => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE']
													  )
											  );

        $DB->query("UPDATE ibf_sessions SET $db_str WHERE id='".$bot."_session'");

    }


}




?>