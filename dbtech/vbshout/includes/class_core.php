<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################
// vBShout functionality class

/**
* Handles everything to do with vBShout.
*/
class VBSHOUT
{
	/**
	* Version info
	*
	* @public	mixed
	*/	
	public static $jQueryVersion 	= '1.7.2';	
	public static $version 			= '6.2.1';
	public static $versionnumber	= '621';
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $vbulletin 	= NULL;
	
	/**
	* The database object
	*
	* @private	vBShout_Database
	*/	
	public static $db 				= NULL;
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $prefix 		= 'dbtech_';
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $bitfieldgroup	= array(
		'vbshoutpermissions'
	);
	
	/**
	* Array of permissions to be returned
	*
	* @public	array
	*/	
	public static $permissions 		= NULL;
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public static $cache			= array();
	
	/**
	* Whether we've called the DM fetcher
	*
	* @public	boolean
	*/		
	protected static $called		= false;
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public static $unserialize		= array(
		'chatroom' => array(
			'members',
		),
		'instance' => array(
			'permissions',
			'bbcodepermissions',
			'notices',
			'options',
			'forumids',
		),
	);
	
	/**
	* Whether we have the pro version or not
	*
	* @public	boolean
	*/		
	public static $isPro			= false;
	
	/**
	* List of shout types
	*
	* @private	array
	*/	
	public static $shouttypes 		= array(
		'shout'		=> 1,
		'pm'		=> 2,
		'me'		=> 4,
		'notif'		=> 8,
		'custom'	=> 16,
		'system'	=> 32,
		'mention'	=> 64,
		'tag'		=> 128,
		'thanks'	=> 256,
	);
	
	/**
	* List of shout styles for the current user
	*
	* @private	array
	*/	
	public static $shoutstyle 	= array();
	
	/**
	* List of all info returned by the fetcher
	*
	* @public	array
	*/	
	public static $fetched 		= array();
	
	/**
	* The source of our information.
	*
	* @public	string
	*/	
	public static $fetchtype 		= 'r';
	
	/**
	* The Tab ID we're working with
	*
	* @public	string
	*/	
	public static $tabid 			= '';
	
	/**
	* What instance we are working with
	*
	* @public	array
	*/	
	public static $instance 		= array();
	
	/**
	* What chatroom we are working with
	*
	* @public	array
	*/	
	public static $chatroom 		= array();
	
	/**
	* The currently active users
	*
	* @public	array
	*/	
	public static $activeusers 		= NULL;
	
	/**
	* The list of BBCode tags enabled globally
	*
	* @public	array
	*/	
	public static $tag_list 		= array();
	
	/**
	* Counter
	*
	* @protected	integer
	*/	
	protected static $i 			= 0;	
	
	/**
	* Dropdown cacher
	*
	* @protected	array
	*/	
	protected static $dropdown 		= array();	
	
	/**
	* What chat tabs we want to create
	* [$instanceId] = array('$tabId' => array('text' => 'Tab Text', 'canclose' => '0', 'showfunc' => '', 'closefunc' => ''))
	*
	* @public	array
	*/		
	public static $tabs 			= array();
	
	/**
	* Rendered instances
	*
	* @public	array
	*/		
	public static $rendered 		= array();
	

	
	/**
	* Does important checking before anything else should be going on
	*
	* @param	vB_Registry	Registry object
	*/
	public static function init($vbulletin)
	{
		// Check if the vBulletin Registry is an object
		if (!is_object($vbulletin))
		{
			// Something went wrong here I think
			trigger_error("Registry object is not an object", E_USER_ERROR);
		}
		
		// Set registry
		self::$vbulletin =& $vbulletin;
		
		// Set database object
		self::$db = new vBShout_Database($vbulletin->db);
		
		// Set permissions shorthand
		self::_getPermissions();
		
		// What permissions to override
		$override = array(
			'canview',
		);
		
		foreach ($override as $permname)
		{
			// Override various permissions
			self::$permissions[$permname] = (self::$permissions['ismanager'] ? 1 : self::$permissions[$permname]);
		}
		
		foreach (self::$unserialize as $cachetype => $keys)
		{
			foreach ((array)self::$cache[$cachetype] as $id => $arr)
			{
				foreach ($keys as $key)
				{
					// Do unserialize
					self::$cache[$cachetype][$id][$key] = @unserialize($arr[$key]);
					self::$cache[$cachetype][$id][$key] = (is_array(self::$cache[$cachetype][$id][$key]) ? self::$cache[$cachetype][$id][$key] : array());					
				}
			}
		}
		
		// Set pro version
		
		
		foreach ((array)self::$cache['instance'] as $instanceid => $instance)
		{
			// Load default options
			self::loadDefaultInstanceOptions(self::$cache['instance'][$instanceid]);
			
			// Load instance permissions
			self::loadInstancePermissions(self::$cache['instance'][$instanceid]);
			
			// Load instance permissions
			self::loadInstanceBbcodePermissions(self::$cache['instance'][$instanceid]);
		}
		
		foreach ((array)self::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Gtfo
				continue;
			}
			
			if (!$chatroom['members'])
			{
				// It was just pure empty
				self::$cache['chatroom'][$chatroomid]['members'] = array();
			}
			
			if (empty($chatroom['members']) AND !$chatroom['membergroupids'])
			{
				// This should never happen, rebuild members list
				$members = self::$db->fetchAllSingleKeyed('
					SELECT userid, status
					FROM $dbtech_vbshout_chatroommember
					WHERE chatroomid = ?
				', 'userid', 'status', array(
					$chatroomid
				));
				
				// init data manager
				$dm =& self::initDataManager('Chatroom', $vbulletin, ERRTYPE_SILENT);
					$dm->set_existing($chatroom);
				if (empty($members))
				{
					// No members
					$dm->set('active', 0);
				}
				else
				{
					// Save members
					$dm->set('members', $members);
				}
				$dm->save();
			}
		}		
	}
	
	/**
	* Check if we have permissions to perform an action
	*
	* @param	array		User info
	* @param	array		Permissions info
	*/		
	public static function checkPermissions(&$user, $permissions, $bitIndex)
	{
		if (!$user['usergroupid'] OR (!isset($user['membergroupids']) AND $user['userid']))
		{
			// Ensure we have this
			$user = fetch_userinfo($user['userid']);
		}
		
		if (!is_array($user['permissions']))
		{
			// Ensure we have the perms
			cache_permissions($user);
		}
		
		$ugs = fetch_membergroupids_array($user);		
		if (!$ugs[0])
		{
			// Hardcode guests
			$ugs[0] = 1;
		}
		
		$bits = array(
			'default' 	=> 4
		);
		$bit = $bits[$bitIndex];
		
		//self::$vbulletin->usergroupcache
		foreach ($ugs as $usergroupid)
		{
			$value = $permissions[$usergroupid][$bitIndex];
			$value = (isset($value) ? $value : 0);
			
			switch ($value)
			{
				case 1:
					// Allow
					return true;
					break;
				
				case -1:
					// Usergroup Default		
					if (!($user[self::$prefix . self::$bitfieldgroup[0]] & $bit))
					{
						// Allow by default
						return true;
					}
					break;
			}
		}
		
		// We didn't make it
		return false;
	}
	
	/**
	* Class factory. This is used for instantiating the extended classes.
	*
	* @param	string			The type of the class to be called (user, forum etc.)
	* @param	vB_Registry		An instance of the vB_Registry object.
	* @param	integer			One of the ERRTYPE_x constants
	*
	* @return	vB_DataManager	An instance of the desired class
	*/
	public static function &initDataManager($classtype, &$registry, $errtype = ERRTYPE_STANDARD)
	{
		if (empty(self::$called))
		{
			// include the abstract base class
			require_once(DIR . '/includes/class_dm.php');
			self::$called = true;
		}
	
		if (preg_match('#^\w+$#', $classtype))
		{
			require_once(DIR . '/dbtech/vbshout/includes/class_dm_' . strtolower($classtype) . '.php');
	
			$classname = 'vBShout_DataManager_' . $classtype;
			$object = new $classname($registry, $errtype);
	
			return $object;
		}
	}
	public static function &datamanager_init($classtype, &$registry, $errtype = ERRTYPE_STANDARD)
	{
		return self::initDataManager($classtype, $registry, $errtype);
	}
	
	/**
	* JS class fetcher for AdminCP
	*
	* @param	string	The JS file name or the code
	* @param	boolean	Whether it's a file or actual JS code
	*/
	public static function js($js = '', $file = true, $echo = true)
	{
		$output = '';
		if ($file)
		{
			$output = '<script type="text/javascript" src="' . self::$vbulletin->options['bburl'] . '/dbtech/vbshout/clientscript/vbshout' . $js . '.js?v=' . self::$versionnumber . '"></script>';
		}
		else
		{
			$output = "
				<script type=\"text/javascript\">
					<!--
					$js
					// -->
				</script>
			";
		}
		
		if ($echo)
		{
			echo $output;
		}
		else
		{
			return $output;
		}
	}
	
	/**
	* Determines the path to jQuery based on browser settings
	*/
	public static function jQueryPath()
	{
		// create the path to jQuery depending on the version
		if (self::$vbulletin->options['customjquery_path'])
		{
			$path = str_replace('{version}', self::$jQueryVersion, self::$vbulletin->options['customjquery_path']);
			if (!preg_match('#^https?://#si', self::$vbulletin->options['customjquery_path']))
			{
				$path = REQ_PROTOCOL . '://' . $path;
			}
			return $path;
		}
		else
		{
			switch (self::$vbulletin->options['remotejquery'])
			{
				case 1:
				default:
					// Google CDN
					return REQ_PROTOCOL . '://ajax.googleapis.com/ajax/libs/jquery/' . self::$jQueryVersion . '/jquery.min.js';
					break;

				case 2:
					// jQuery CDN
					return REQ_PROTOCOL . '://code.jquery.com/jquery-' . self::$jQueryVersion . '.min.js';
					break;

				case 3:
					// Microsoft CDN
					return REQ_PROTOCOL . '://ajax.aspnetcdn.com/ajax/jquery/jquery-' . self::$jQueryVersion . '.min.js';
					break;
			}
		}
	}

	/**
	* @param	integer	Depth of item (0 = no depth, 3 = third level depth)
	* @param	string	Character or string to repeat $depth times to build the depth mark
	* @param	string	Existing depth mark to append to
	*
	* @return	string
	*/
	function getDepthMark($depth, $depthchar, $depthmark = '')
	{
		for ($i = 0; $i < $depth; $i++)
		{
			$depthmark .= $depthchar;
		}
		return $depthmark;
	}

	/**
	* Breaks down a difference (in seconds) into its days / hours / minutes / seconds components.
	*
	* @param	integer	Difference (in seconds)
	*
	* @return	array
	*/
	function getTimeBreakdown($difference)
	{
		
		$breakdown = array();
		
		// Set days
		$breakdown['days'] = intval($difference / 86400);
		$difference -= ($breakdown['days'] * 86400);
		
		// Set hours
		$breakdown['hours'] = intval($difference / 3600);
		$difference -= ($breakdown['hours'] * 3600);
		
		// Set minutes
		$breakdown['minutes'] = intval($difference / 60);
		$difference -= ($breakdown['minutes'] * 60);
		
		// Set seconds
		$breakdown['seconds'] = intval($difference);
		
		return $breakdown;
	}
	
	/**
	* Quick Method of building the CPNav Template
	*
	* @param	string	The selected item in the CPNav
	*/	
	public static function setNavClass($selectedcell = 'main')
	{
		global $navclass;
	
		$cells = array(
			'main',
			
			'hottest',
			'statistics',
			'list',
		);
	
		//($hook = vBulletinHook::fetch_hook('usercp_nav_start')) ? eval($hook) : false;
		
		// set the class for each cell/group
		$navclass = array();
		foreach ($cells AS $cellname)
		{
			$navclass[$cellname] = (intval(self::$vbulletin->versionnumber) == 3 ? 'alt2' : 'inactive');
		}
		$navclass[$selectedcell] = (intval(self::$vbulletin->versionnumber) == 3 ? 'alt1' : 'active');
		
		//($hook = vBulletinHook::fetch_hook('usercp_nav_complete')) ? eval($hook) : false;
	}
	
	/**
	* Escapes a string and makes it JavaScript-safe
	*
	* @param	mixed	The string or array to make JS-safe
	*/	
	public static function jsEscapeString(&$arr)
	{
		$find = array(
			"\r\n",
			"\n",
			"\t",
			'"'
		);
		
		$replace = array(
			'\r\n',
			'\n',
			'\t',
			'\"',
		);
		
		$arr = str_replace($find, $replace, $arr);
	}
	
	/**
	* Encodes a string as a JSON object (consistent behaviour instead of relying on PHP built-in functions)
	*
	* @param	mixed	The string or array to encode
	* @param	boolean	(Optional) Whether this is an associative array
	* @param	boolean	(Optional) Whether we should escape the string or if they have already been escaped
	*/	
	public static function encodeJSON($arr, $assoc = true, $doescape = true)
	{
		if ($doescape)
		{
			self::jsEscapeString($arr);
		}
		if (!$assoc)
		{
			// Not associative, simple return
			return '{"' . implode('","', $arr) . '"}';
		}
		
		$content = array();
		foreach ((array)$arr as $key => $val)
		{
			if (is_array($val))
			{
				// Recursion, definition: see recursion
				$val = self::encodeJSON($val);
				$content[] = '"' . $key . '":' . $val;
			}
			else
			{
				$content[] = '"' . $key . '":"' . $val . '"';
			}
		}
		
		return '{' . implode(',', $content) . '}';
	}

	/**
	* Outputs an XML string to the browser 
	*
	* @param	mixed	array to output
	*/
	public static function outputXML($arr)
	{
		require_once(DIR . '/includes/class_xml.php');

		$xml = new vB_AJAX_XML_Builder(self::$vbulletin, 'text/xml');
			$xml->add_group('vbshout');
				
				foreach (array(
					'aoptimes',
					'chatrooms',
					'shouts',
				) as $key)
				{
					if (!is_array(self::$fetched[$key]))
					{
						// Skip this
						continue;
					}

					// Array values
					$xml->add_group($key);
					foreach ((array)self::$fetched[$key] as $key2 => $arr)
					{
						$xml->add_group(substr($key, 0, -1));
						foreach ($arr as $key3 => $val)
						{
							$xml->add_tag($key3, $val);
						}
						$xml->close_group();
					}
					$xml->close_group();
				}

				foreach (array(
					'ajax',
					'activereports',
					'activeusers2',
					'content',
					'editor',
					'error',
					'menucode',
					'pmtime',
					'pmuserid',
					'sticky',
				) as $key)
				{
					if (!isset(self::$fetched[$key]))
					{
						continue;
					}

					// Singular values
					$xml->add_tag($key, 		self::$fetched[$key]);
				}

				if (isset(self::$fetched['activeusers']['count']))
				{
					// Singular values
					$xml->add_tag('activeusers', 	self::$fetched['activeusers']['usernames'], array('count' 		=> self::$fetched['activeusers']['count']));
				}

				if (array_key_exists('chatroom', self::$fetched))
				{
					$xml->add_tag('chatroom', 	self::$fetched['chatroom']['title'], 		array('chatroomid' 	=> self::$fetched['chatroom']['chatroomid']));
				}

			$xml->close_group();
		$xml->print_xml();
	}
	
	/**
	* Outputs a JSON string to the browser 
	*
	* @param	mixed	array to output
	*/	
	public static function outputJSON($json, $full_shutdown = false)
	{
		if (!headers_sent())
		{
			// Set the header
			header('Content-type: application/json');
		}
		
		// Create JSON
		$json = self::encodeJSON($json);
		
		// Turn off debug output
		self::$vbulletin->debug = false;
		
		if (defined('VB_API') AND VB_API === true)
		{
			print_output($json);
		}

		//run any registered shutdown functions
		if (intval(self::$vbulletin->versionnumber) > 3)
		{
			$GLOBALS['vbulletin']->shutdown->shutdown();
		}
		exec_shut_down();
		self::$vbulletin->db->close();
		
		$sendHeader = false;
		switch(self::$vbulletin->options['ajaxheader'])
		{
			case 0 :
				$sendHeader = true;
				
			case 1 :
				$sendHeader = false;
				
			case 2 :
			default:
				$sendHeader = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false);
		}

		if ($sendHeader)
		{
			// this line is causing problems with mod_gzip/deflate, but is needed for some IIS setups
			@header('Content-Length: ' . strlen($json));
		}
		
		// Finally spit out JSON
		echo $json;
		die();
	}
	
	/**
	* Constructs some <option>s for use in the templates
	*
	* @param	array	The key:value data array
	* @param	mixed	(Optional) The selected id(s)
	* @param	boolean	(Optional) Whether we should HTMLise the values
	*/	
	public static function createSelectOptions($array, $selectedid = '', $htmlise = false)
	{
		if (!is_array($array))
		{
			return '';
		}
		
		$options = '';
		foreach ($array as $key => $val)
		{
			if (is_array($val))
			{
				// Create the template
				$templater = vB_Template::create('optgroup');
					$templater->register('optgroup_label', 	($htmlise ? htmlspecialchars_uni($key) : $key));
					$templater->register('optgroup_options', self::createSelectOptions($val, $selectedid, $tabindex, $htmlise));
				$options .= $templater->render();
			}
			else
			{
				if (is_array($selectedid))
				{
					$selected = iif(in_array($key, $selectedid), ' selected="selected"', '');
				}
				else
				{
					$selected = iif($key == $selectedid, ' selected="selected"', '');
				}
				
				$templater = vB_Template::create('option');
					$templater->register('optionvalue', 	($key !== 'no_value' ? $key : ''));
					$templater->register('optionselected', 	$selected);
					$templater->register('optiontitle', 	($htmlise ? htmlspecialchars_uni($val) : $val));
				$options .= $templater->render();
			}
		}
		
		return $options;
	}
	
	/**
	* Constructs a time selector
	*
	* @param	string	The title of the time select
	* @param	name	(Optional) The HTML form name
	* @param	array	(Optional) The time we should start with
	* @param	name	(Optional) The vertical align state
	* 
	* @return	string	The constructed time row
	*/	
	public static function timeRow($title, $name = 'date', $unixtime = '', $valign = 'middle')
	{
		global $vbphrase, $vbulletin;
		
		$output = '';
	
		$monthnames = array(
			0  => '- - - -',
			1  => $vbphrase['january'],
			2  => $vbphrase['february'],
			3  => $vbphrase['march'],
			4  => $vbphrase['april'],
			5  => $vbphrase['may'],
			6  => $vbphrase['june'],
			7  => $vbphrase['july'],
			8  => $vbphrase['august'],
			9  => $vbphrase['september'],
			10 => $vbphrase['october'],
			11 => $vbphrase['november'],
			12 => $vbphrase['december'],
		);
	
		if (is_array($unixtime))
		{
			require_once(DIR . '/includes/functions_misc.php');
			$unixtime = vbmktime(0, 0, 0, $unixtime['month'], $unixtime['day'], $unixtime['year']);
		}
	
		if ($unixtime)
		{
			$month = vbdate('n', $unixtime, false, false);
			$day = vbdate('j', $unixtime, false, false);
			$year = vbdate('Y', $unixtime, false, false);
			$hour = vbdate('G', $unixtime, false, false);
			$minute = vbdate('i', $unixtime, false, false);
		}
	
		$cell = array();
		$cell[] = "<label for=\"{$name}_month\">$vbphrase[month]</label><br /><select name=\"{$name}[month]\" id=\"{$name}_month\" tabindex=\"1\" class=\"primary select\"" . iif($vbulletin->debug, " title=\"name=&quot;$name" . "[month]&quot;\"") . ">\n" . self::createSelectOptions($monthnames, $month) . "\t\t</select>";
		$cell[] = "<label for=\"{$name}_date\">$vbphrase[day]</label><br /><input type=\"text\" class=\"primary textbox\" name=\"{$name}[day]\" id=\"{$name}_date\" value=\"$day\" size=\"4\" maxlength=\"2\" tabindex=\"1\"" . iif($vbulletin->debug, " title=\"name=&quot;$name" . "[day]&quot;\"") . ' />';
		$cell[] = "<label for=\"{$name}_year\">$vbphrase[year]</label><br /><input type=\"text\" class=\"primary textbox\" name=\"{$name}[year]\" id=\"{$name}_year\" value=\"$year\" size=\"4\" maxlength=\"4\" tabindex=\"1\"" . iif($vbulletin->debug, " title=\"name=&quot;$name" . "[year]&quot;\"") . ' />';
		$inputs = '';
		foreach($cell AS $html)
		{
			$inputs .= "\t\t<td style=\"padding-left:6px;\"><span class=\"smallfont\">$html</span></td>\n";
		}
		
		$output .= "<div id=\"ctrl_$name\" class=\"" . (intval(self::$vbulletin->versionnumber) == 3 ? 'alt1' : 'blockrow') . "\">$title: <table cellpadding=\"0\" cellspacing=\"2\" border=\"0\"><tr>\n$inputs\t\n</tr></table></div><br />";	
		
		return $output;
	}

	/**
	* Fetches all valid forum ids
	*
	* @return	array	List of forum ids we can access
	*/	
	public static function getForumIds()
	{
		$forumcache = self::$vbulletin->forumcache;
		/*
		$excludelist = explode(',', self::$vbulletin->options['dbtech_infopanels_forum_exclude']);
		foreach ($excludelist AS $key => $excludeid)
		{
			$excludeid = intval($excludeid);
			unset($forumcache[$excludeid]);
		}
		*/
	
		$forumids = array_keys($forumcache);
		
		// get forum ids for all forums user is allowed to view
		foreach ($forumids AS $key => $forumid)
		{
			if (is_array($includearray) AND empty($includearray[$forumid]))
			{
				unset($forumids[$key]);
				continue;
			}
	
			$fperms =& self::$vbulletin->userinfo['forumpermissions'][$forumid];
			$forum =& self::$vbulletin->forumcache[$forumid];
	
			if (!((int)$fperms & (int)self::$vbulletin->bf_ugp_forumpermissions['canview']) OR !((int)$fperms & (int)self::$vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR !verify_forum_password($forumid, $forum['password'], false))
			{
				unset($forumids[$key]);
			}
		}
		
		// Those shouts with 0 as their forumid
		$forumids[] = 0;
		
		return $forumids;
	}

	/**
	* Grabs what permissions we have got
	*/
	protected static function _getPermissions()
	{
		if (!self::$vbulletin->userinfo['permissions'])
		{
			// For some reason, this is missing
			cache_permissions(self::$vbulletin->userinfo);
		}
		
		foreach (self::$bitfieldgroup as $bitfieldgroup)
		{
			// Override bitfieldgroup variable
			$bitfieldgroup = self::$prefix . $bitfieldgroup;
			
			if (!is_array(self::$vbulletin->bf_ugp[$bitfieldgroup]))
			{
				// Something went wrong here I think
				require_once(DIR . '/includes/class_bitfield_builder.php');
				if (vB_Bitfield_Builder::build(false) !== false)
				{
					$myobj =& vB_Bitfield_Builder::init();
					if (sizeof($myobj->data['ugp'][$bitfieldgroup]) != sizeof(self::$vbulletin->bf_ugp[$bitfieldgroup]))
					{
						require_once(DIR . '/includes/adminfunctions.php');
						$myobj->save(self::$vbulletin->db);
						build_forum_permissions();
						
						if (IN_CONTROL_PANEL === true)
						{
							define('CP_REDIRECT', self::$vbulletin->scriptpath);
							print_stop_message('rebuilt_bitfields_successfully');
						}
						else
						{
							self::$vbulletin->url = self::$vbulletin->scriptpath;
							eval(print_standard_redirect(array('redirect_updatethanks', self::$vbulletin->userinfo['username']), true, true));
						}
					}
				}
				else
				{
					echo "<strong>error</strong>\n";
					print_r(vB_Bitfield_Builder::fetch_errors());
					die();
				}
			}
			
			foreach ((array)self::$vbulletin->bf_ugp[$bitfieldgroup] as $permname => $bit)
			{
				// Set the permission
				self::$permissions[$permname] = (!$bit ? self::$vbulletin->userinfo['permissions'][$bitfieldgroup][$permname] : (self::$vbulletin->userinfo['permissions'][$bitfieldgroup] & $bit ? 1 : 0));
			}
		}
	}	
	
	
	/**
	* Loads default instance options
	*
	* @param	array	The instance in question
	*/
	public static function loadDefaultInstanceOptions(&$instance)
	{
		$instance['options']['logging'] 				= (isset($instance['options']['logging']) 					? $instance['options']['logging'] 					: (self::$isPro ? 31 	: 15));
		$instance['options']['editors'] 				= (isset($instance['options']['editors']) 					? $instance['options']['editors'] 					: (self::$isPro ? 511 	: 127));
		$instance['options']['notices'] 				= (isset($instance['options']['notices']) 					? $instance['options']['notices'] 					: (self::$isPro ? 255 	: 31));
		$instance['options']['optimisation'] 			= (isset($instance['options']['optimisation']) 				? $instance['options']['optimisation'] 				: 1);
		$instance['options']['allowsmilies'] 			= (isset($instance['options']['allowsmilies']) 				? $instance['options']['allowsmilies'] 				: 1);
		$instance['options']['activeusers'] 			= (isset($instance['options']['activeusers']) 				? $instance['options']['activeusers'] 				: 0);
		$instance['options']['sounds'] 					= (isset($instance['options']['sounds']) 					? $instance['options']['sounds'] 					: 1);
		$instance['options']['enableshoutsound'] 		= (isset($instance['options']['enableshoutsound']) 			? $instance['options']['enableshoutsound'] 			: 1);
		$instance['options']['enableinvitesound'] 		= (isset($instance['options']['enableinvitesound']) 		? $instance['options']['enableinvitesound'] 		: 1);
		$instance['options']['enablepmsound'] 			= (isset($instance['options']['enablepmsound']) 			? $instance['options']['enablepmsound'] 			: 1);
		//$instance['options']['enablemenu'] 				= (isset($instance['options']['enablemenu']) 				? $instance['options']['enablemenu'] 				: 1);
		$instance['options']['altshouts'] 				= (isset($instance['options']['altshouts']) 				? $instance['options']['altshouts'] 				: 0);
		$instance['options']['enableaccess'] 			= (isset($instance['options']['enableaccess']) 				? $instance['options']['enableaccess'] 				: 1);
		$instance['options']['anonymise'] 				= (isset($instance['options']['anonymise']) 				? $instance['options']['anonymise'] 				: 0);
		$instance['options']['allcaps'] 				= (isset($instance['options']['allcaps']) 					? $instance['options']['allcaps'] 					: 0);
		$instance['options']['maxshouts'] 				= (isset($instance['options']['maxshouts']) 				? $instance['options']['maxshouts'] 				: 20);
		$instance['options']['maxarchiveshouts'] 		= (isset($instance['options']['maxarchiveshouts']) 			? $instance['options']['maxarchiveshouts'] 			: 20);
		$instance['options']['height'] 					= (isset($instance['options']['height']) 					? $instance['options']['height'] 					: 150);
		$instance['options']['floodchecktime'] 			= (isset($instance['options']['floodchecktime']) 			? $instance['options']['floodchecktime'] 			: 3);
		$instance['options']['maxchars'] 				= (isset($instance['options']['maxchars']) 					? $instance['options']['maxchars'] 					: 256);
		$instance['options']['maximages'] 				= (isset($instance['options']['maximages']) 				? $instance['options']['maximages'] 				: 2);
		$instance['options']['idletimeout'] 			= (isset($instance['options']['idletimeout']) 				? $instance['options']['idletimeout'] 				: 180);
		$instance['options']['refresh'] 				= (isset($instance['options']['refresh']) 					? $instance['options']['refresh'] 					: 5);
		$instance['options']['maxchats'] 				= (isset($instance['options']['maxchats']) 					? $instance['options']['maxchats'] 					: 5);
		$instance['options']['shoutorder'] 				= (isset($instance['options']['shoutorder']) 				? $instance['options']['shoutorder'] 				: 'DESC');
		$instance['options']['maxsize'] 				= (isset($instance['options']['maxsize']) 					? $instance['options']['maxsize'] 					: 3);
		$instance['options']['postping_interval'] 		= (isset($instance['options']['postping_interval']) 		? $instance['options']['postping_interval'] 		: 50);
		$instance['options']['threadping_interval'] 	= (isset($instance['options']['threadping_interval']) 		? $instance['options']['threadping_interval'] 		: 50);
		$instance['options']['memberping_interval'] 	= (isset($instance['options']['memberping_interval']) 		? $instance['options']['memberping_interval'] 		: 50);
		$instance['options']['shoutboxtabs'] 			= (isset($instance['options']['shoutboxtabs']) 				? $instance['options']['shoutboxtabs'] 				: 7);
		$instance['options']['logging_deep'] 			= (isset($instance['options']['logging_deep']) 				? $instance['options']['logging_deep'] 				: 0);
		$instance['options']['logging_deep_system'] 	= (isset($instance['options']['logging_deep_system']) 		? $instance['options']['logging_deep_system'] 		: 0);
		$instance['options']['enablepms'] 				= (isset($instance['options']['enablepms']) 				? $instance['options']['enablepms'] 				: 1);
		$instance['options']['enablepmnotifs'] 			= (isset($instance['options']['enablepmnotifs']) 			? $instance['options']['enablepmnotifs'] 			: 1);
		$instance['options']['enable_sysmsg'] 			= (isset($instance['options']['enable_sysmsg']) 			? $instance['options']['enable_sysmsg'] 			: 1);
		$instance['options']['sounds_idle'] 			= (isset($instance['options']['sounds_idle']) 				? $instance['options']['sounds_idle'] 				: 0);
		$instance['options']['avatars_normal'] 			= (isset($instance['options']['avatars_normal']) 			? $instance['options']['avatars_normal'] 			: 0);
		$instance['options']['avatar_width_normal'] 	= (isset($instance['options']['avatar_width_normal']) 		? $instance['options']['avatar_width_normal'] 		: 11);
		$instance['options']['avatar_height_normal'] 	= (isset($instance['options']['avatar_height_normal']) 		? $instance['options']['avatar_height_normal'] 		: 11);
		$instance['options']['avatars_full'] 			= (isset($instance['options']['avatars_full']) 				? $instance['options']['avatars_full'] 				: 0);
		$instance['options']['avatar_width_full'] 		= (isset($instance['options']['avatar_width_full']) 		? $instance['options']['avatar_width_full'] 		: 22);
		$instance['options']['avatar_height_full'] 		= (isset($instance['options']['avatar_height_full']) 		? $instance['options']['avatar_height_full'] 		: 22);
		$instance['options']['maxshouts_detached'] 		= (isset($instance['options']['maxshouts_detached']) 		? $instance['options']['maxshouts_detached'] 		: 40);
		$instance['options']['height_detached'] 		= (isset($instance['options']['height_detached']) 			? $instance['options']['height_detached'] 			: 300);
		$instance['options']['refresh_idle'] 			= (isset($instance['options']['refresh_idle']) 				? $instance['options']['refresh_idle'] 				: 5);
		$instance['options']['archive_numtopshouters'] 	= (isset($instance['options']['archive_numtopshouters']) 	? $instance['options']['archive_numtopshouters'] 	: 10);
		$instance['options']['autodelete'] 				= (isset($instance['options']['autodelete']) 				? $instance['options']['autodelete'] 				: 0);
		$instance['options']['shoutarea'] 				= (isset($instance['options']['shoutarea']) 				? $instance['options']['shoutarea'] 				: 'left');
		$instance['options']['archive_link'] 			= (isset($instance['options']['archive_link']) 				? $instance['options']['archive_link'] 				: 0);
		$instance['options']['minposts'] 				= (isset($instance['options']['minposts']) 					? $instance['options']['minposts'] 					: 0);
		$instance['options']['timeformat'] 				= (isset($instance['options']['timeformat']) 				? $instance['options']['timeformat'] 				: self::$vbulletin->options['timeformat']);
		$instance['options']['blogping_interval'] 		= (isset($instance['options']['blogping_interval']) 		? $instance['options']['blogping_interval'] 		: 50);
		$instance['options']['shoutping_interval'] 		= (isset($instance['options']['shoutping_interval']) 		? $instance['options']['shoutping_interval'] 		: 50);
		$instance['options']['aptlping_interval'] 		= (isset($instance['options']['aptlping_interval']) 		? $instance['options']['aptlping_interval'] 		: 50);
		$instance['options']['tagping_interval'] 		= (isset($instance['options']['tagping_interval']) 			? $instance['options']['tagping_interval'] 			: 50);
		$instance['options']['mentionping_interval'] 	= (isset($instance['options']['mentionping_interval']) 		? $instance['options']['mentionping_interval'] 		: 50);
		$instance['options']['quoteping_interval'] 		= (isset($instance['options']['quoteping_interval']) 		? $instance['options']['quoteping_interval'] 		: 50);
		$instance['options']['quizmadeping_interval'] 	= (isset($instance['options']['quizmadeping_interval']) 	? $instance['options']['quizmadeping_interval'] 	: 50);
		$instance['options']['quiztakenping_interval'] 	= (isset($instance['options']['quiztakenping_interval']) 	? $instance['options']['quiztakenping_interval'] 	: 50);
		
	}
	
	/**
	* Sets up the permissions based on instance
	*
	* @param	array		The instance
	* @param	array|null	User Info to check (null = vBulletin Userinfo)
	*/
	public static function loadInstancePermissions(&$instance, $userinfo = NULL)
	{
		// Set permissions shorthand
		$permarray = array();
		
		// Ensure we can fetch bitfields
		require_once(DIR . '/includes/adminfunctions_options.php');
		$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
		
		if ($userinfo === NULL)
		{
			// We're using our own user info
			$userinfo = self::$vbulletin->userinfo;
		}
		else if ($userinfo['userid'] == self::$vbulletin->userinfo['userid'] AND is_array($instance['permissions_parsed']))
		{
			// Just return parsed
			return $instance['permissions_parsed'];
		}
		
		foreach (array_merge(array($userinfo['usergroupid']), explode(',', $userinfo['membergroupids'])) as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				if (!isset($permarray[$permname]))
				{
					// Default to false
					$permarray[$permname] = false;
				}
				
				if (!$permarray[$permname] AND ((int)$instance['permissions'][$usergroupid] & (int)$bit))
				{
					// Override to true
					$permarray[$permname] = true;
				}
			}			
		}
		
		// Some hardcoded ones
		//$permarray['isprotected'] 	= ((int)$userinfo['permissions']['dbtech_vbshoutpermissions'] & (int)self::$vbulletin->bf_ugp_dbtech_vbshoutpermissions['isprotected']);
		$permarray['ismanager'] 	= ((int)$userinfo['permissions']['dbtech_vbshoutpermissions'] & (int)self::$vbulletin->bf_ugp_dbtech_vbshoutpermissions['ismanager']);
		$permarray['canpm']			= (isset($permarray['canpm']) ? $permarray['canpm'] : 1);
		
		if ($userinfo == self::$vbulletin->userinfo)
		{
			// Set the completed permissions array
			$instance['permissions_parsed'] = $permarray;
		}
		
		return $permarray;
	}
	public static function load_instance_permissions(&$instance, $userinfo = NULL)
	{
		self::loadInstancePermissions($instance, $userinfo);
	}
	
	/**
	* Sets up the BBCode permissions based on instance
	*
	* @param	array		The instance
	* @param	array|null	User Info to check (null = vBulletin Userinfo)
	*/
	public static function loadInstanceBbcodePermissions(&$instance, $userinfo = NULL)
	{
		// Set permissions shorthand
		$bitvalue 	= 0;
		$permarray = array();
		
		if ($userinfo === NULL)
		{
			// We're using our own user info
			$userinfo = self::$vbulletin->userinfo;
		}
		else if ($userinfo['userid'] == self::$vbulletin->userinfo['userid'] AND is_array($instance['bbcodepermissions_parsed']))
		{
			// Just return parsed
			return $instance['bbcodepermissions_parsed'];
		}		
		
		// Fetch all our usergroup ids
		$usergroupids = array_merge(array($userinfo['usergroupid']), explode(',', $userinfo['membergroupids']));
		
		// Ensure we can fetch bitfields
		require_once(DIR . '/includes/adminfunctions_options.php');
		$permissions = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
		
		foreach ($usergroupids as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				if (!isset($permarray[$permname]))
				{
					// Default to false
					$permarray[$permname] = false;
				}
				
				if (!$permarray[$permname] AND ((int)$instance['bbcodepermissions'][$usergroupid] & (int)$bit))
				{
					// Override to true
					$permarray[$permname] = true;
					$bitvalue += $bit;
				}
			}
		}
		
		if ($userinfo == self::$vbulletin->userinfo)
		{
			// Set the completed permissions array
			$instance['bbcodepermissions_parsed'] = array('bit' => $bitvalue, 'array' => $permarray);
		}
		
		return array('bit' => $bitvalue, 'array' => $permarray);
	}
	public static function load_instance_bbcodepermissions(&$instance, $userinfo = NULL)
	{
		self::loadInstanceBbcodePermissions($instance, $userinfo);
	}
	
	/**
	* Renders the main shoutbox template.
	* A method because this needs to happen on
	* multiple locations under different conditions.
	*/
	public static function render($instance)
	{
		global $vbphrase, $show, $template_hook, $vbulletin;		
		
		if (intval(self::$vbulletin->versionnumber) == 3)
		{
			global $instance, $stylevar, $session;
			global $bbuserinfo, $vboptions, $vbulletin, $css, $show, $cells;			
		}
		
		// Empty out this
		$template_hook['dbtech_vbshout_shoutcontrols_below'] 	= 
			$template_hook['dbtech_vbshout_editortools_pro'] 	= 
			$template_hook['dbtech_vbshout_below_shout'] 		= 
			$template_hook['dbtech_vbshout_popupbody'] 			= 
			$template_hook['dbtech_vbshout_editortools_end'] 	= 
			$template_hook['dbtech_vbshout_activeusers_right'] 	= 
			$template_hook['dbtech_vbshout_activeusers_left'] 	=
			$template_hook['dbtech_vbshout_shoutarea_left'] 	= 
			$template_hook['dbtech_vbshout_shoutarea_right'] 	= 
			$template_hook['dbtech_vbshout_shoutarea_above'] 	= 
			$template_hook['dbtech_vbshout_shoutarea_below'] 	= 
			'';
				
		foreach (array(
			'dbtech_vbshout_activeusers',
			'dbtech_vbshout_editortools_pro',
			'dbtech_vbshout_shoutbox',
			'dbtech_vbshout_editortools',
			'dbtech_vbshout_shoutarea_horizontal',
			'dbtech_vbshout_shoutarea_vertical',			
			'dbtech_vbshout_shoutcontrols',			
		) AS $templatename)
		{
			if (intval(self::$vbulletin->versionnumber) != 3)
			{
				// Register the instance variable on all these
				vB_Template::preRegister($templatename, array('instance' => $instance));
			}
			else
			{
				// vB3 code
				$GLOBALS['instance'] = &$instance;
			}
		}
		
		if (!is_array($show))
		{
			// Init
			$show = array();
		}
		
		// Create the template rendering engine
		$shoutbox = vB_Template::create('dbtech_vbshout_shoutbox');
			$shoutbox->register('permissions', $instance['permissions_parsed']);
				
		// Whether we need to do a CSS Hack
		$csshack = ' dbtech_fullshouts';

		// The main components of the shoutbox link
		$title 	= $instance['name'];
		if ($instance['permissions_parsed']['canviewarchive'])
		{
			$start 	= '<a href="vbshout.php?' . self::$vbulletin->session->vars['sessionurl'] . 'do=archive&amp;instanceid=' . $instance['instanceid'] . '">';
			$end 	= '</a>';
		}
		
		// Create the actual shoutbox variable
		//$headerlink = $start . $title . $end;
		$headerlink = '';
		
		// Re-add this, lol
		self::$shoutstyle = (self::$shoutstyle ? self::$shoutstyle : @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_shoutstyle']));
		
		if (self::$vbulletin->userinfo['userid'] AND $instance['permissions_parsed']['canshout'] AND $instance['options']['editors'])
		{		
			// Create the template containing the Editor Tools
			$tools = vB_Template::create('dbtech_vbshout_editortools');
				$tools->register('editorid', 	'dbtech_shoutbox_editor_wrapper');
				$tools->register('permissions', $instance['permissions_parsed']);
							
			if ($instance['options']['editors'] & 16)
			{
				// Check if we need to go with the default font
				$foundfont 	= false;
				
				$templater = vB_Template::create('editor_jsoptions_font');
				$string = $templater->render(true);
				$fonts = preg_split('#\r?\n#s', $string, -1, PREG_SPLIT_NO_EMPTY);
				foreach ($fonts AS $font)
				{
					if (strpos($font, 'editor_jsoptions_font'))
					{
						// We don't need template comments
						continue;
					}
					
					if (trim($font) == self::$shoutstyle[$instance['instanceid']]['font'])
					{
						// Yay we found the font
						$foundfont = true;
					}
					
					$templater = vB_Template::create('dbtech_vbshout_editor_toolbar_fontname');
						$templater->register('fontname', trim($font));
					$fontnames .= $templater->render(true);
				}
				
				if (!$foundfont)
				{
					if (intval(self::$vbulletin->versionnumber) != 3)
					{
						// Find the default font
						$chosenfont = explode(',', trim(vB_Template_Runtime::fetchStyleVar('font.fontFamily')));
						$chosenfont = trim(str_replace("'", '', $chosenfont[0]));
					}
					else
					{
						// vB3 code
						$chosenfont = 'Tahoma';
					}
					
					// Ensure this is set
					self::$shoutstyle[$instance['instanceid']]['font'] = $chosenfont;
				}
				
				// Register font stuff
				$tools->register('fontnames', 	$fontnames);
			}
		
			if ($instance['options']['editors'] & 8)
			{
				// Begin checking colours
				$colors = vB_Template::create('dbtech_vbshout_editor_toolbar_colors')->render();
				
				// Register colour stuff
				$tools->register('colors', 		$colors);
			}

			
			
			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shoutbox_editortools')) ? eval($hook) : false;
			
			// Finally render the editor tools
			$tools->register('template_hook', $template_hook);
			$editortools = $tools->render();
		}
		
		// Some important pre-register variables
		$domenu = false;
		$direction = 'left';
		$addedpx = 0;
		
		// Add to unsorted tabs
		$unsortedTabs = array();

		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shoutbox_start')) ? eval($hook) : false;
		
		if ($instance['permissions_parsed']['canmodchat'])
		{
			$unsortedTabs['shoutreports'] = array(
				'text' 			=> $vbphrase['dbtech_vbshout_unhandled_reports'] . ': <span name="dbtech_vbshout_shoutreports" data-instanceid="' . $instance['instanceid'] . '">0</span>',
				'canclose' 		=> '0',
				'extraparams' 	=> array(
					'loadurl' => 'vbshout.php?' . self::$vbulletin->session->vars['sessionurl_js'] . 'do=reportlist&instanceid=' . $instance['instanceid']
				)				
			);
		}
		
		foreach ((array)self::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chat room
				continue;
			}
			
			if ($chatroom['instanceid'] != $instance['instanceid'] AND $chatroom['instanceid'] != 0)
			{
				// Wrong instance id
				continue;
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of(self::$vbulletin->userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$unsortedTabs['chatroom_' . $chatroomid . '_' . $chatroom['instanceid']] = array(
						'text' 			=> str_replace('"', '\"', $chatroom['title']),
						'canclose' 		=> '0',
						'extraparams' 	=> array(
							'chatroomid' => $chatroomid
						)
					);
				}
			}
			else
			{
				if ($chatroom['members'][self::$vbulletin->userinfo['userid']] == '1')
				{
					// Do join it
					$unsortedTabs['chatroom_' . $chatroomid . '_' . $chatroom['instanceid']] = array(
						'text' 			=> str_replace('"', '\"', $chatroom['title']),
						'canclose' 		=> '1',
						'extraparams' 	=> array(
							'chatroomid' => $chatroomid
						)
					);
				}
			}
		}
		
		if (!is_array(self::$vbulletin->userinfo['dbtech_vbshout_displayorder']))
		{
			// Only unserialize if it's not an array
			self::$vbulletin->userinfo['dbtech_vbshout_displayorder'] = @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_displayorder']);
		}
		
		$tabdisplayorder = (array)self::$vbulletin->userinfo['dbtech_vbshout_displayorder'];
		if (is_array($tabdisplayorder[$instance['instanceid']]))
		{
			asort($tabdisplayorder[$instance['instanceid']]);
			foreach ($tabdisplayorder[$instance['instanceid']] as $tabid => $tab)
			{
				if (!$unsortedTabs[$tabid])
				{
					// Skip this tab
					continue;
				}
				
				// Add the tab
				self::$tabs[$instance['instanceid']][$tabid] = $unsortedTabs[$tabid];
				unset($unsortedTabs[$tabid]);
			}
		}
		
		foreach ($unsortedTabs as $tabid => $tab)
		{
			// Add remaining unsorted tabs
			self::$tabs[$instance['instanceid']][$tabid] = $tab;
		}
		
		if ($instance['options']['activeusers'])
		{
			// Begin creating the template
			$templater = vB_Template::create('dbtech_vbshout_activeusers');
				$templater->register('activeusers', (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']));
				$templater->register('height', 	($instance['options']['height'] + $addedpx));
			
			// We're using the separate Active Users block
			switch ($direction)
			{
				case 'left':
				case 'above':
				case 'below':
					// Register the active users frame
						$templater->register('direction', 	'left');
					$template_hook['dbtech_vbshout_activeusers_right'] = $templater->render();
					break;
					
				case 'right':
					// Register the active users frame
						$templater->register('direction', 	'right');
					$template_hook['dbtech_vbshout_activeusers_left'] = $templater->render();
					break;
			}
		}		
		
		// Register the header link variable
		$shoutbox->register('title', 		$title);
		$shoutbox->register('headerlink', 	$headerlink);
		
		// Register template variables
		if (!self::$vbulletin->userinfo['userid'] OR !$instance['permissions_parsed']['canshout'])
		{
			// Set the CSS hack
			$shoutbox->register('csshack', $csshack);
			
			// We can't shout
			$show['canshout'] = false;
		}
		else
		{
			// We can shout
			$show['canshout'] = true;
			
			if (!$shoutbox->is_registered('shoutarea'))
			{
				// We haven't registered a shout area yet
				$templater = vB_Template::create('dbtech_vbshout_shoutarea_vertical');
					$templater->register('direction', 'left');
					$shoutbox->register('direction', 'left');
				$shoutarea = $templater->render();
				
				// Register the shout controls also
				$templater = vB_Template::create('dbtech_vbshout_shoutcontrols');
					$templater->register('permissions', $instance['permissions_parsed']);
					$templater->register('editortools', $editortools);
				$template_hook['dbtech_vbshout_shoutcontrols_below'] = $templater->render();			
				
				// Register the shout area as being on the left
				$shoutbox->register('shoutarea', $shoutarea);
			}
		}
		
		// Finally render the template
		return $shoutbox->render();
	}
	
	/**
	* Handles an AJAX request from the Shoutbox.
	*
	* @param	string	What we're upto
	*/
	public static function ajax_handler($do)
	{
		global $vbphrase;
		
		// Grab instance id
		$instanceid = self::$vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
		
		if (!self::$instance = self::$cache['instance']["$instanceid"])
		{
			// Wrong instance
			self::$fetched['error'] = 'Invalid Instance: ' . $instanceid;
			
			// Prints the XML for reading by the AJAX script
			self::outputXML(self::$fetched);
		}
		
		// Any additional arguments we may be having to the fetching of shouts
		$args = array(
			'instanceid' => $instanceid
		);
		
		$chatroomid = self::$vbulletin->input->clean_gpc('r', 'chatroomid', TYPE_UINT);
		if ($chatroomid)
		{
			// Check if the chatroom is active
			self::$chatroom = self::$cache['chatroom']["$chatroomid"];
			
			if ($do != 'joinchat')
			{
				if (!self::$chatroom OR !self::$chatroom['active'])
				{
					// Wrong chatroom
					self::$fetched['error'] = 'disband_' . $chatroomid;
				}
				
				if (!self::$chatroom['membergroupids'])
				{
					// This is not a members-only group
					$userid = self::$vbulletin->userinfo['userid'];
					if (!isset(self::$chatroom['members']["$userid"]))
					{
						// We're not a member
						self::$fetched['error'] = 'disband_' . $chatroomid;
					}
				}
				else
				{
					if (!is_member_of(self::$vbulletin->userinfo, explode(',', self::$chatroom['membergroupids'])))
					{
						// Usergroup no longer a member
						self::$fetched['error'] = 'disband_' . $chatroomid;
					}			
				}
				
				// Override tabid for AOP purposes
				self::$tabid = 'chatroom_' . $chatroomid . '_' . self::$chatroom['instanceid'];
			}
		}
		
		

		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler_start')) ? eval($hook) : false;
		
		if (self::$fetched['error'])
		{
			// We had errors, don't bother
			
			// Prints the XML for reading by the AJAX script
			self::outputXML(self::$fetched);
		}
		
		// Strip non-valid characters
		$do = preg_replace('/[^\w-]/i', '', $do);
				
		if (file_exists(DIR . '/dbtech/vbshout/actions/ajax/' . $do . '.php'))
		{
			// Set where we're coming from
			self::$fetched['ajax'] = $do;
			
			// Fetch the file in question
			require(DIR . '/dbtech/vbshout/actions/ajax/' . $do . '.php');
		}

		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler')) ? eval($hook) : false;
		
		if (!self::$fetched['error'])
		{
			// Bugfix
			unset(self::$fetched['error']);
		}		

		// Prints the XML for reading by the AJAX script
		self::outputXML(self::$fetched);
	}
	
	/**
	* Processes an AJAX fetching request using AOP.
	*
	* @param	string	When we last fetched shouts
	*/
	public static function fetch_aop($tabid, $instanceid)
	{
		if ($tabid == 'activeusers')
		{
			// Shouldn't happen
			return false;
		}
		
		if (!is_writable(DIR . '/dbtech/vbshout/aop/'))
		{
			// Fall back to database
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_aop_error'];
			
			// Time now
			$mtime = TIMENOW;
		}
		
		// File system
		$mtime = intval(@file_get_contents(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt'));
		
		if (!$mtime)
		{
			$mtime = 0;
		}
		
		if ((TIMENOW - $mtime) >= 60)
		{
			// Reset AOP
			self::set_aop($tabid, $instanceid, false, true);
			return false;
		}
		
		foreach ((array)self::$fetched['aoptimes'] as $key => $info)
		{
			if ($info['tabid'] == $tabid)
			{
				// Already fetched
				self::$fetched['aoptimes'][$key]['aoptime'] = $mtime;
				self::$fetched['aoptimes'][$key]['tabid'] = $tabid;
				return true;
			}
		}
		
		//if ($mtime > $aoptime)
		//{
			// Include the new AOP time
			self::$fetched['aoptimes'][] = array(
				'aoptime' 	=> $mtime,
				'tabid' 	=> $tabid,
				'nosound' 	=> 0
			);
		//}
	}
	
	/**
	* Sets the new AOP time.
	*/
	public static function set_aop($tabid, $instanceid = 0, $markread = true, $nosound = false)
	{
		if ($tabid == 'activeusers')
		{
			// Shouldn't happen
			return false;
		}
		
		// Ensure this is taken into account
		clearstatcache();
		
		if (!is_writable(DIR . '/dbtech/vbshout/aop'))
		{
			// Fall back to database
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_aop_error'];
			return false;			
		}

		// Touch the files
		@file_put_contents(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt', TIMENOW);
		
		if ($markread)
		{
			// Duplicate this
			@file_put_contents(DIR . '/dbtech/vbshout/aop/markread-' . $tabid . $instanceid . '.txt', TIMENOW);
		}
		
		foreach ((array)self::$fetched['aoptimes'] as $key => $info)
		{
			if ($info['tabid'] == $tabid)
			{
				// Already fetched
				self::$fetched['aoptimes'][$key]['aoptime'] 	= $mtime;
				self::$fetched['aoptimes'][$key]['tabid'] 		= $tabid;
				self::$fetched['aoptimes'][$key]['nosound'] 	= (int)$nosound;
				return true;
			}
		}
		
		// Include the new AOP time
		self::$fetched['aoptimes'][] = array(
			'aoptime' 	=> TIMENOW,
			'tabid' 	=> $tabid,
			'nosound' 	=> (int)$nosound
		);
	}

	/**
	* Un-idles us
	*/
	public static function unIdle()
	{
		if (!self::$vbulletin->userinfo['userid'])
		{
			// Guests shouldn't be flagged as active
			return false;
		}

		if (self::$vbulletin->userinfo['dbtech_vbshout_invisiblesettings'][self::$instance['instanceid']])
		{
			// We're stealthing
			return false;
		}

		// Set us as active
		self::$db->query('
			REPLACE INTO $dbtech_vbshout_session
				(userid, lastactivity, instanceid, chatroomid)
			VALUES (?, ?, ?, ?)
		', array(
			self::$vbulletin->userinfo['userid'],
			TIMENOW,
			self::$instance['instanceid'],
			(self::$chatroom ? self::$chatroom['chatroomid'] : 0),
		));

		// Set timeout to twice that of the actual idle timeout, so that we don't needlessly purge sessions
		$timeout = TIMENOW - ((self::$instance['options']['idletimeout'] ? self::$instance['options']['idletimeout'] : 600) * 2);

		// Get rid of old sessions
		self::$db->delete('dbtech_vbshout_session', array($timeout), 'WHERE lastactivity < ?');
	}
	
	/**
	* Fetches shouts based on parameters.
	*
	* @param	array		(Optional) Additional arguments
	*/
	public static function fetch_shouts($args = array())
	{
		global $vbphrase;
		
		foreach (array(
			'dbtech_vbshout_activeusers',
			'dbtech_vbshout_editortools_pro',
			'dbtech_vbshout_menu',
			'dbtech_vbshout_shoutbox',
			'dbtech_vbshout_css',
			'dbtech_vbshout_editortools',
			'dbtech_vbshout_shoutarea_horizontal',
			'dbtech_vbshout_shoutarea_vertical',
			'dbtech_vbshout_shoutcontrols',						
		) AS $templatename)
		{
			// Register the instance variable on all these
			if (intval(self::$vbulletin->versionnumber) != 3)
			{
				// Register the instance variable on all these
				vB_Template::preRegister($templatename, array('instance' => self::$instance));
			}
			else
			{
				// vB3 code
				$GLOBALS['instance'] = self::$instance;
			}
		}
		
		// Cache array for fetch_musername()
		$shoutusers = array();
		
		// Various SQL hooks
		$hook_query_select = $hook_query_join = $hook_query_and = '';
		
		if ($args['type'] == -1 OR !$args['types'])
		{
			// Everything
			$hook_query_and .= 'AND (
				vbshout.userid IN(-1, ' . self::$vbulletin->userinfo['userid'] . ') OR
				vbshout.id IN(0, ' . self::$vbulletin->userinfo['userid'] . ')
			)';				// That either system or us posted, or was a message to us/anybody
			
			if (is_array($args['excludetypes']))
			{
				// Exclude types
				$hook_query_and .= 'AND vbshout.type NOT IN(' . implode(',', $args['excludetypes']) . ')';
			}
		}
		else
		{
			$types = array();
			foreach (self::$shouttypes as $key => $val)
			{
				// Go through all shout types
				if ($args['types'] & self::$shouttypes[$key])
				{
					switch ($key)
					{
						case 'shout':
							if ($args['onlyuser'])
							{
								// Every PM posted by us to the user
								// or to us
								$hook_query_and .= "AND vbshout.userid = '" . intval($args['onlyuser']) . "'";
							}
							break;
						
						case 'pm':
							if ($args['onlyuser'])
							{
								// Every PM posted by us to the user
								// or to us
								$hook_query_and .= 'AND (
									vbshout.userid = ' . self::$vbulletin->userinfo['userid'] . ' AND
										vbshout.id = ' . intval($args['onlyuser']) . '
								) OR (
									vbshout.id = ' . self::$vbulletin->userinfo['userid'] . ' AND
										vbshout.userid = ' . intval($args['onlyuser']) . '
								)';
							}
							break;
					}
					
					// Set the type
					$types[] = self::$shouttypes[$key];
				}
			}
			
			// Include all our types
			$hook_query_and .= 'AND vbshout.type IN(' . implode(',', $types) . ')';
		}
		
		// Fetch the shout order
		$shoutorder = self::$vbulletin->input->clean_gpc('r', 'shoutorder', TYPE_STR);
		$shoutorder = (in_array($shoutorder, array('ASC', 'DESC')) ? $shoutorder : self::$instance['options']['shoutorder']);
		
		$hook_query_and .= " AND vbshout.chatroomid = " . self::$vbulletin->db->sql_prepare(intval($args['chatroomid']));
		
		if (self::$instance['options']['activeusers'])
		{
			self::fetch_active_users(true, true);
			if ($args['chatroomid'])
			{
				// Array of all active users
				self::$fetched['activeusers']['usernames'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_chat_users']);
				if (self::$instance['options']['enableaccess'])
				{
					self::$fetched['activeusers']['usernames'] .= '<br /><br /><a href="vbshout.php?' . self::$vbulletin->session->vars['sessionurl'] . 'do=chataccess&amp;instanceid=' . self::$instance['instanceid'] . '&amp;chatroomid=' . $args['chatroomid'] . '" target="_blank"><b>' . $vbphrase['dbtech_vbshout_chat_access'] . '</b></a>';
				}
		
			}
			else
			{
				// Array of all active users
				self::$fetched['activeusers']['usernames'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
			}
		}

		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_fetch_shouts_query')) ? eval($hook) : false;

		// Query the shouts
		$shouts_q = self::$vbulletin->db->query_read_slave("
			SELECT
				user.avatarid,
				user.avatarrevision,
				user.username,
				user.usergroupid,
				user.membergroupids,
				user.infractiongroupid,
				user.displaygroupid,
				user.dbtech_vbshout_settings AS shoutsettings,
				user.dbtech_vbshout_shoutstyle AS shoutstyle" . (self::$vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . ",
				vbshout.*
				" . (self::$vbulletin->options['avatarenabled'] ? ', avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, customavatar.width AS avwidth, customavatar.height AS avheight, customavatar.height_thumb AS avheight_thumb, customavatar.width_thumb AS avwidth_thumb, customavatar.filedata_thumb' : '') . ",
				pmuser.username AS pmusername
				$hook_query_select
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
			" . (self::$vbulletin->options['avatarenabled'] ? "
			LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid)
			LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid)
			" : '') . "
			LEFT JOIN " . TABLE_PREFIX . "user AS pmuser ON(pmuser.userid = vbshout.id)			
			$hook_query_join
			WHERE vbshout.instanceid IN(-1, 0, " . intval(self::$instance['instanceid']) . ")
				AND vbshout.userid NOT IN(
					SELECT ignoreuserid
					FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
					WHERE userid = " . self::$vbulletin->userinfo['userid'] . "
				)
				AND vbshout.forumid IN(" . implode(',', self::getForumIds()) . ")
				AND (
					user.dbtech_vbshout_banned = '0' OR
					vbshout.userid = -1
				)
				$hook_query_and
			ORDER BY dateline DESC
			LIMIT " . (self::$instance['options']['maxshouts'] ? self::$instance['options']['maxshouts'] : 20)
		);
			
		// Set sticky		
		self::$fetched['sticky'] = self::$instance['sticky'];
		
		if (!self::$vbulletin->db->num_rows($shouts_q))
		{
			// We have no shouts
			self::$fetched['content'] = $vbphrase['dbtech_vbshout_nothing_to_display'];
			return false;
		}
		
		// Fetch active users without menu or force
		self::fetch_active_users();
		
		self::$fetched['activeusers']['count'] = count(self::$activeusers);
		
		// Re-add this, lol
		self::$shoutstyle = (self::$shoutstyle ? self::$shoutstyle : @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_shoutstyle']));
		
		$i = 1;
		while ($shouts_r = self::$vbulletin->db->fetch_array($shouts_q))
		{
			if ($shouts_r['userid'] == 0)
			{
				// Save some resources
				continue;
			}
			
			// Parses action codes like /me
			self::parse_action_codes($shouts_r['message'], $shouts_r['type']);

			// By default, we can't pm or edit
			$canpm = $canedit = false;

			if ($shouts_r['userid'] > -1)
			{
				if (!$shoutusers["$shouts_r[userid]"])
				{
					// Uncached user
					$shoutusers["$shouts_r[userid]"] = array(
						'userid' 					=> $shouts_r['userid'],
						'username' 					=> $shouts_r['username'],
						'usergroupid' 				=> $shouts_r['usergroupid'],
						'infractiongroupid' 		=> $shouts_r['infractiongroupid'],
						'displaygroupid' 			=> $shouts_r['displaygroupid'],
						'dbtech_vbshop_purchase' 	=> $shouts_r['dbtech_vbshop_purchase']
					);
				}
				
				// fetch the markup-enabled username
				fetch_musername($shoutusers["$shouts_r[userid]"]);
				
				if ($shouts_r['userid'] != self::$vbulletin->userinfo['userid'])
				{
					// We can PM this user
					$canpm = true;
				}
			}
			else
			{
				// This was the SYSTEM
				$shoutusers["$shouts_r[userid]"] = array(
					'userid' 	=> 0,
					'username' 	=> $vbphrase['dbtech_vbshout_system'],
					'musername' => $vbphrase['dbtech_vbshout_system'],
				);
				
				// We can't PM the system
				$canpm = false;
			}
			
			// Only registered users can have shoutbox styles
			if (!$shouts_r['shoutstyle'] = @unserialize($shouts_r['shoutstyle']))
			{
				// This shouldn't be false
				$shouts_r['shoutstyle'] = array();
			}
			
			// Ensure it's an array for the sake of bugfix
			$instanceid = self::$instance['instanceid'];
			$shouts_r['shoutstyle'] = (!$shouts_r['shoutstyle']["$instanceid"] ? array() : $shouts_r['shoutstyle'][$instanceid]);
			
			// Init the styleprops
			$styleprops = array();
			
			if (self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 8192)
			{
				// Override!
				$shouts_r['shoutstyle'] = self::$shoutstyle[$instanceid];
			}
			
			if (self::$instance['options']['editors'] & 1 AND $shouts_r['shoutstyle']['bold'])
			{
				// Bold
				$styleprops[] = 'font-weight:bold;';
			}
			
			if (self::$instance['options']['editors'] & 2 AND $shouts_r['shoutstyle']['italic'])
			{
				// Italic
				$styleprops[] = 'font-style:italic;';
			}
			
			if (self::$instance['options']['editors'] & 4 AND $shouts_r['shoutstyle']['underline'])
			{
				// Underline
				$styleprops[] = 'text-decoration:underline;';
			}
			
			if (self::$instance['options']['editors'] & 16 AND $shouts_r['shoutstyle']['font'])
			{
				// Font
				$styleprops[] = 'font-family:' . $shouts_r['shoutstyle']['font'] . ';';
			}
			
			if (self::$instance['options']['editors'] & 8 AND $shouts_r['shoutstyle']['color'])
			{
				// Color
				$styleprops[] = 'color:' . $shouts_r['shoutstyle']['color'] . ';';
			}			
			
			if (($shouts_r['userid'] == self::$vbulletin->userinfo['userid'] AND self::$instance['permissions_parsed']['caneditown']) OR
				($shouts_r['userid'] != self::$vbulletin->userinfo['userid'] AND self::$instance['permissions_parsed']['caneditothers']))
			{
				// We got the perms, give it to us
				$canedit = true;
			}
			
			switch ($shouts_r['type'])
			{
				case self::$shouttypes['me']:
				case self::$shouttypes['notif']:
					// slash me or notification
					$time = vbdate(self::$vbulletin->options['timeformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']);
					break;
					
				default:
					// Everything else
					$time = '[' . vbdate(self::$vbulletin->options['dateformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']) . ' ' .
							vbdate(self::$vbulletin->options['timeformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']) . ']';
					break;
			}
			
			// Get our usergroup permissions
			cache_permissions($shouts_r, false);
			
			// By default, we can't add infractions
			self::$instance['permissions_parsed']['giveinfraction'] = (
				// Must have 'cangiveinfraction' permission. Branch dies right here majority of the time
				self::$vbulletin->userinfo['permissions']['genericpermissions'] & self::$vbulletin->bf_ugp_genericpermissions['cangiveinfraction']
				// Can not give yourself an infraction
				AND $shouts_r['userid'] != self::$vbulletin->userinfo['userid']
				// Can not give an infraction to a post that already has one
				// Can not give an admin an infraction
				AND !($shouts_r['permissions']['adminpermissions'] & self::$vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
				// Only Admins can give a supermod an infraction
				AND (
					!($shouts_r['permissions']['adminpermissions'] & self::$vbulletin->bf_ugp_adminpermissions['ismoderator'])
					OR self::$vbulletin->userinfo['permissions']['adminpermissions'] & self::$vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
				)
			);

			

			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_fetch_shouts_loop')) ? eval($hook) : false;
			
			$shouts_r['message'] = str_replace(array("\r", "\n", "\r\n"), '', $shouts_r['message']);
			
			switch ($shouts_r['type'])
			{
				case self::$shouttypes['shout']:
					// Normal shout
					$template = 'shout';
					break;
					
				case self::$shouttypes['pm']:
					// PM
					$template = 'pm';
					break;
					
				case self::$shouttypes['me']:
				case self::$shouttypes['notif']:
					// slash me or a notification
					$template = 'me';
					break;
					
				default:
					// Error handler
					$template = 'shout';
					break;
			}
			
			if ($shouts_r['userid'] == -1)
			{
				// System message
				$template = 'system';
			}
			
			$altclass = 'alt1';
			if (self::$instance['options']['altshouts'] AND !((int)self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 131072))
			{
				$altclass = ($i % 2 == 0 ? ' alt2' : ' alt1');
			}
			
			if ($shouts_r['message_raw'] == '/silencelist' OR $shouts_r['message_raw'] == '/banlist')
			{
				// Special cases, allow HTML
				$shouts_r['message'] = unhtmlspecialchars($shouts_r['message']);
			}

			// Set PM stuff
			$pmUserParsed = $vbphrase['dbtech_vbshout_pm'];
			

			self::$fetched['shouts'][] = array(
				'template'				=> $template,
				'shoutid' 				=> $shouts_r['shoutid'],
				'userid' 				=> $shouts_r['userid'],
				'instanceid' 			=> self::$instance['instanceid'],
				'message_raw'			=> str_replace('\\\\', '\\', str_replace(array("%", "$", "\\"), array("&#37;", "&#36;", "\\\\"), htmlspecialchars_uni($shouts_r['message_raw']))),
				'canedit'				=> $canedit,				
				'time'					=> $time,
				'musername'				=> str_replace(array("%", "$"), array("&#37;", "&#36;"), $shoutusers["$shouts_r[userid]"]['musername']),
				'jsusername'			=> str_replace('"', '\"', $shouts_r['username']),
				'styleprops' 			=> implode(' ', $styleprops),
				'message'				=> str_replace('\\\\', '\\', str_replace(array("%", "$", "\\"), array("&#37;", "&#36;", "\\\\"), $shouts_r['message'])),
				'pmuserParsed'			=> $pmUserParsed,
				'altclass'				=> $altclass,
				'avatarpath'			=> $shouts_r['avatarurl'],
				'permissions'			=> json_encode(array(
					'canpm' 		=> $canpm,
					'isprotected' 	=> (!self::check_protected_usergroup($shouts_r, true) AND $shouts_r['userid'] != self::$vbulletin->userinfo['userid'] AND $shouts_r['shoutid']),
					'caninfract' 	=> self::$instance['permissions_parsed']['giveinfraction'],
					'canban' 		=> self::$instance['permissions_parsed']['canban'],
					'cankick' 		=> (array_key_exists('creator', self::$chatroom) AND self::$chatroom['creator'] == self::$vbulletin->userinfo['userid'] AND $shouts_r['userid'] != self::$vbulletin->userinfo['userid']),
					
				)),				
			);
			
			$i++;
		}

		if ($shoutorder == 'ASC')
		{
			// Reverse sort order
			self::$fetched['shouts'] = array_reverse(self::$fetched['shouts']);
		}
		
		if (!self::$fetched['shouts'])
		{
			// Show no content
			self::$fetched['content'] = $vbphrase['dbtech_vbshout_nothing_to_display'];
		}
		
		// No longer needed
		unset($shoutusers, $shout);
	}
	
	/**
	* Checks for action codes, and executes their meaning.
	* 
	* @param	string	The shout.
	* @param	string	The default shout type.
	* @param	integer	(Optional) The default id.
	* @param	integer	(Optional) The default userid.
	*
	* @return	mixed	Any new information we may have.
	*/
	public static function parse_action_codes(&$message, &$type)
	{
		global $vbphrase;
		
		if (preg_match("#^(\/[a-z]*?)\s(.+?)$#i", $message, $matches))
		{
			// 2-stage command
			switch ($matches[1])
			{
				case '/me':
					// A slash me
					$message 	= trim($matches[2]);
					$type 		= self::$shouttypes['me'];
					break;
					
				default:
					($hook = vBulletinHook::fetch_hook('dbtech_vbshout_parsecommand_2')) ? eval($hook) : false;
					break;
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_command_complete')) ? eval($hook) : false;
		
		return array($retval['type'], $retval['id'], $retval['userid']);
	}
	
	/**
	* Checks for a protected usergroup
	*
	* @param	array	Usergroup information
	* @param	boolean	(Optional) Whether we should just return boolean
	*/
	public static function check_protected_usergroup($exists, $boolreturn = false)
	{
		global $vbphrase;
		
		// Loads instance permissions
		$permarray = self::loadInstancePermissions(self::$instance, $exists);
		
		if ($permarray['isprotected'])
		{
			if (!$boolreturn)
			{
				// Err0r
				self::$fetched['error'] = construct_phrase($vbphrase['dbtech_vbshout_x_is_protected'], $exists['username']);
			}
			return true;
		}
		
		return false;
	}
	
	/**
	* Logs a specified command.
	*
	* @param	string	The executed command.
	* @param	mixed	(Optional) Additional comments.
	*/
	public static function log_command($command, $comment = NULL)
	{
		$bit = 0;
		switch ($command)
		{
			case 'shoutedit':
			case 'shoutdelete':
				$bit = 8;
				break;
			
			case 'prune':
				$bit = 1;
				break;
			
			case 'setsticky':
			case 'removesticky':
				$bit = 2;
				break;
				
			case 'ban':
			case 'unban':
				$bit = 4;
				break;
				
			case 'resetshouts':
				$bit = 32;
				break;
		}

		
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_log_process')) ? eval($hook) : false;
		
		if (!$bit OR !(self::$instance['options']['logging'] & $bit))
		{
			// We didn't have this option on
			return;
		}
		
		self::$vbulletin->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_log
				(userid, dateline, ipaddress, command, comment)
			VALUES (
				" . self::$vbulletin->db->sql_prepare(self::$vbulletin->userinfo['userid']) . ",
				" . self::$vbulletin->db->sql_prepare(TIMENOW) . ",
				" . self::$vbulletin->db->sql_prepare(IPADDRESS) . ",
				" . self::$vbulletin->db->sql_prepare($command) . ",
				" . self::$vbulletin->db->sql_prepare($comment) . "
			)
		");
	}
	
	/**
	* Determines the replacement for the BBCode SIZE limiter.
	*
	* @param	integer	The attempted SIZE value.
	*
	* @return	string	The new SIZE BBCode.
	*/
	public static function process_bbcode_size($size)
	{
		// Returns the prepared string
		return '[size=' . (intval($size) > self::$instance['options']['maxsize'] ? self::$instance['options']['maxsize'] : $size) . ']';
	}
	
	/**
	* Fetch all currently active users.
	*/	
	private function fetch_active_users($domenu = false, $force = false, $chatroomid = false)
	{
		global $vbphrase;
		
		if (self::$activeusers === NULL OR $force)
		{
			// Array of all active users
			self::$activeusers = array();
			$userids = array();
			
			// Query active users
			$activeusers_q = self::$vbulletin->db->query_read_slave("
				SELECT
					user.userid,
					username,
					usergroupid,
					membergroupids,
					infractiongroupid,
					displaygroupid,
					user.dbtech_vbshout_settings AS shoutsettings
					" . (self::$vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . "
				FROM " . TABLE_PREFIX . "dbtech_vbshout_session AS session
				LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = session.userid)
				WHERE session.lastactivity >= " . (TIMENOW - (self::$instance['options']['idletimeout'] ? self::$instance['options']['idletimeout'] : 600)) . "
					" . (!self::$chatroom ? "AND session.instanceid = " . intval(self::$instance['instanceid']) : '')  . "
					" . (self::$chatroom ? "AND session.chatroomid = " . intval(self::$chatroom['chatroomid']) : 'AND session.chatroomid = 0') . "
			");
			while ($activeusers_r = self::$vbulletin->db->fetch_array($activeusers_q))
			{
				if (in_array($activeusers_r['userid'], $userids))
				{
					// Skip this user
					continue;
				}
				
				// Add this
				$userids[] = $activeusers_r['userid'];
				
				// fetch the markup-enabled username
				fetch_musername($activeusers_r);
				
				// Fetch the SEO'd URL to a member's profile
				if (intval(self::$vbulletin->versionnumber) == 3)
				{
					self::$activeusers[] = '<a href="member.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $activeusers_r['userid'] . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
				}
				else
				{
					self::$activeusers[] = '<a href="' . fetch_seo_url('member', $activeusers_r) . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
				}
			}
		}
	}
		
	/**
	* Rebuilds the shout counter for every user.
	*/
	public static function build_shouts_counter()
	{
		// Begin shout counter
		$counters = array();
		
		// Grab all shouts
		$shouts_q = self::$vbulletin->db->query_read_slave("
			SELECT userid, shoutid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
		");
		while ($shouts_r = self::$vbulletin->db->fetch_array($shouts_q))
		{
			// Build shout counters
			$counters["$shouts_r[userid]"]++;
			
		}
		self::$vbulletin->db->free_result($shouts_q);
		unset($shouts_r);	
		
		$cases = array();
		foreach ($counters as $userid => $shouts)
		{
			// Set the case
			$cases[] = "WHEN $userid THEN $shouts";
		}
		
		if (count($cases))
		{
			// Finally update the user table
			self::$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "user
				SET dbtech_vbshout_shouts = CASE userid
				" . implode(' ', $cases) . "
				ELSE 0 END
			");
		}
	}
	
	/**
	* Leaves the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public static function leave_chatroom(&$chatroom, $userid)
	{
		$SQL = '';
		if ($chatroom['creator'] == $userid)
		{
			$null = array();
			
			// init data manager
			$dm =& self::initDataManager('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
				$dm->set_existing($chatroom);
				$dm->set('active', 	'0');
				$dm->set('members', $null);
			$dm->save();
		}
		else
		{
			// We weren't the creator, only we should abandon ship
			$SQL = "AND userid = " . self::$vbulletin->db->sql_prepare($userid);
		}
		
		
		// Leave the chat room
		self::$vbulletin->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
			WHERE chatroomid = " . self::$vbulletin->db->sql_prepare($chatroom['chatroomid']) . 
				$SQL
				. ($status ? " AND status = 0" : '')
		);
		
		if ($SQL)
		{
			// init data manager
			$dm =& self::initDataManager('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
				$dm->set_existing($chatroom);
				
			unset($chatroom['members']["$userid"]);
			
				$dm->set('members', $chatroom['members']);
			$dm->save();
		}
	}
	
	/**
	* Joins the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public static function join_chatroom(&$chatroom, $userid)
	{
		// Join the chat room
		self::$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
			SET status = 1
			WHERE chatroomid = " . self::$vbulletin->db->sql_prepare($chatroom['chatroomid']) . "
				AND userid = " . self::$vbulletin->db->sql_prepare($userid) . "
		");	
		
		// init data manager
		$dm =& self::initDataManager('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
			$dm->set_existing($chatroom);
			
		// We're now fully joined
		$chatroom['members']["$userid"] = '1';
			
			$dm->set('members', 	$chatroom['members']);
		$dm->save();
	}
	
	/**
	* Creates the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public static function invite_chatroom(&$chatroom, $userid, $invitedby)
	{
		// Invite to join the chat room
		self::$vbulletin->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
				(chatroomid, userid, status, invitedby)
			VALUES (
				" . self::$vbulletin->db->sql_prepare($chatroom['chatroomid']) . ",
				" . self::$vbulletin->db->sql_prepare($userid) . ",
				0,
				" . self::$vbulletin->db->sql_prepare(self::$vbulletin->userinfo['userid']) . "				
			)
		");
		
		if (self::$vbulletin->db->affected_rows())
		{
			// init data manager
			$dm =& self::initDataManager('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
				$dm->set_existing($chatroom);
				
			// We're now fully joined
			$chatroom['members']["$userid"] = '0';
						
				$dm->set('members', 	$chatroom['members']);
			$dm->save();
		}
	}
	
	/**
	* Fetches what chatrooms we're a member of
	*
	* @param	array	The user info we're checking membership of
	* @param	mixed	Whether we're checking a status or not
	* @param	mixed	Whether we're checking an instanceid or not
	*/
	public static function fetch_chatroom_memberships($userinfo, $status = NULL, $instanceid = NULL)
	{
		$memberof = array();
		foreach ((array)self::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chatroom
				continue;
			}
			
			if ($instanceid !== NULL)
			{
				if ($chatroom['instanceid'] != $instanceid AND $chatroom['instanceid'] != 0)
				{
					// Skip this instance id
					continue;
				}
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of($userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$memberof[] = $chatroomid;
				}
			}
			else
			{
				if (!isset($chatroom['members']["{$userinfo[userid]}"]))
				{
					// We're not a part this
					continue;
				}
				
				if ($status !== NULL AND $chatroom['members']["{$userinfo[userid]}"] !== $status)
				{
					// Wrong status
					continue;
				}
				
				// We're a member
				$memberof[] = $chatroomid;
			}
		}
		
		return $memberof;
	}
	
	/**
	* Rebuilds the shout counter for every user.
	*
	* @param	string	The new sticky note.
	*/
	public static function set_sticky($sticky)
	{
		// Store raw sticky
		$sticky_raw = $sticky;
		
		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');
		if (!function_exists('convert_url_to_bbcode'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		
		// Initialise the parser (use proper BBCode)
		$parser = new vB_BbCodeParser(self::$vbulletin, fetch_tag_list());
		
		if (self::$vbulletin->options['allowedbbcodes'] & 64)
		{
			// We can use the URL BBCode, so convert links
			$sticky = convert_url_to_bbcode($sticky);
		}	
		
		// BBCode parsing
		$sticky = $parser->parse($sticky, 'nonforum');		
		
		// init data manager
		$dm =& self::initDataManager('Instance', self::$vbulletin, ERRTYPE_ARRAY);
			$dm->set_existing(self::$instance);
			$dm->set('sticky', 		$sticky);
			$dm->set('sticky_raw', 	$sticky_raw);
		$dm->save();
		
		// Set new sticky
		self::$instance['sticky'] = $sticky;		
	}
}


// #############################################################################
// database functionality class

/**
* Class that handles database wrapper
*/
class vBShout_Database
{
	/**
	* The vBulletin database object
	*
	* @private	vB_Database
	*/		
	private $db;
	
	/**
	* The query result we executed
	*
	* @private	MySQL_Result
	*/	
	private $result;
	
	/**
	* Whether we're debugging output
	*
	* @public	boolean
	*/	
	public $debug = false;


	/**
	* Does important checking before anything else should be going on
	*
	* @param	vB_Registry		Registry object
	*/
	function __construct($dbobj)
	{
		$this->db = $dbobj;
	}

	/**
	 * Hides DB errrors
	 * 
	 * @return void
	 */
	public function hideErrors()
	{
		$this->db->hide_errors();
	}

	/**
	 * Shows DB errrors
	 * 
	 * @return void
	 */
	public function showErrors()
	{
		$this->db->show_errors();
	}

	/**
	 * Inserts a table row with specified data.
	 *
	 * @param mixed $table The table to insert data into.
	 * @param array $bind Column-value pairs.
	 * @param array $exclusions Array of field names that should be ignored from the $queryvalues array
	 * @param boolean $displayErrors Whether SQL errors should be displayed
	 * 
	 * @return int The number of affected rows.
	 */
	public function insert($table, array $bind, array $exclusions = array(), $displayErrors = true)
	{
		// Store the query
		$sql = fetch_query_sql($bind, $table, '', $exclusions);
		
		if ($this->debug)
		{
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			die();
		}
		
		if (!$displayErrors)
		{
			$this->db->hide_errors();
		}
		$this->db->query_write($sql);
		if (!$displayErrors)
		{
			$this->db->show_errors();
		}

		// Return insert ID if only one row was inserted, otherwise return number of affected rows
		$affected = $this->db->affected_rows();
		return($affected === 1 ? $this->db->insert_id() : $affected);
	}
	
	/**
	 * Updates table rows with specified data based on a WHERE clause.
	 *
	 * @param  mixed		$table The table to update.
	 * @param  array		$bind  Column-value pairs.
	 * @param  mixed		$where UPDATE WHERE clause(s).
	 * @param  mixed		$exclusions Array of field names that should be ignored from the $queryvalues array
	 * 
	 * @return int		  The number of affected rows.
	 */
	public function update($table, array $bind, $where, array $exclusions = array())
	{
		$sql = fetch_query_sql($bind, $table, $where, $exclusions);
		
		if ($this->debug)
		{
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			die();
		}
		
		$this->db->query_write($sql);
		return $this->db->affected_rows();
	}
	
	/**
	 * Deletes table rows based on a WHERE clause.
	 *
	 * @param  mixed		$table The table to update.
	 * @param  mixed  		$bind Data to bind into DELETE placeholders.
	 * @param  mixed		$where DELETE WHERE clause(s).
	 * 
	 * @return int		  The number of affected rows.
	 */
	public function delete($table, array $bind, $where = '')
	{
		/**
		 * Build the DELETE statement
		 */
		$sql = "DELETE FROM "
			 . TABLE_PREFIX . $table
			 . ' ' . $where;

		/**
		 * Execute the statement and return the number of affected rows
		 */
		$result = $this->query($sql, $bind, 'query_write');
		return $this->db->affected_rows();
	}
	
	/**
	 * Fetches all SQL result rows as a sequential array.
	 *
	 * @param string $sql  An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * 
	 * @return array
	 */
	public function fetchAll($sql, $bind = array())
	{
		$results = array();
		
		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$results[] = $row;
		}
		return $results;
	}
	
	/**
	 * Fetches results from the database with a specified column from each row keyed according to preference.
	 * The 'key' parameter provides the column name with which to key the result.
	 * The 'column' parameter provides the column name with which to use as the result.
	 * For example, calling fetchAllSingleKeyed('SELECT item_id, title, date FROM table', 'item_id', 'title')
	 * would result in an array keyed by item_id:
	 * [$itemId] => $title
	 *
	 * Note that the specified key must exist in the query result, or it will be ignored.
	 *
	 * @param string SQL to execute
	 * @param string Column with which to key the results array
	 * @param string Column to use as the result for that key
	 * @param mixed Parameters for the SQL
	 *
	 * @return array
	 */
	public function fetchAllSingleKeyed($sql, $key, $column, $bind = array())
	{
		$results = array();
		$i = 0;

		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$results[(isset($row[$key]) ? $row[$key] : $i)] = $row[$column];
			$i++;
		}

		return $results;
	}
	
	/**
	 * Fetches results from the database with each row keyed according to preference.
	 * The 'key' parameter provides the column name with which to key the result.
	 * For example, calling fetchAllKeyed('SELECT item_id, title, date FROM table', 'item_id')
	 * would result in an array keyed by item_id:
	 * [$itemId] => array('item_id' => $itemId, 'title' => $title, 'date' => $date)
	 *
	 * Note that the specified key must exist in the query result, or it will be ignored.
	 *
	 * @param string SQL to execute
	 * @param string Column with which to key the results array
	 * @param mixed Parameters for the SQL
	 *
	 * @return array
	 */
	public function fetchAllKeyed($sql, $key, $bind = array())
	{
		$results = array();
		$i = 0;

		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$results[(isset($row[$key]) ? $row[$key] : $i)] = $row;
			$i++;
		}

		return $results;
	}

	/**
	 * Fetches all SQL result rows as an associative array.
	 *
	 * The first column is the key, the entire row array is the
	 * value.  You should construct the query to be sure that
	 * the first column contains unique values, or else
	 * rows with duplicate values in the first column will
	 * overwrite previous data.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed $bind Data to bind into SELECT placeholders.
	 * 
	 * @return array
	 */
	public function fetchAssoc($sql, $bind = array())
	{
		$data = array();
		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$key = key($row);
			$data[$row[$key]] = $row;
		}
		return $data;
	}	
	
	/**
	 * Fetches the first row of the SQL result.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * @param mixed  $fetchMode Override current fetch mode.
	 * 
	 * @return array
	 */
	public function fetchRow($sql, $bind = array())
	{
		// Check the limit and fix $sql
		$limit = explode('limit', strtolower($sql));
		if (sizeof($limit) != 2 OR !is_numeric(trim($limit[1])))
		{
			// Append limit
			$sql .= ' LIMIT 1';
		}
		
		$result = $this->query($sql, $bind, 'query_first');
		return $result;
	}
	
	/**
	 * Fetches the first column of all SQL result rows as an array.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * @param mixed  $column OPTIONAL - Key to use for the column index
	 * @return array
	 */
	public function fetchCol($sql, $bind = array(), $column = '')
	{
		$data = array();
		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			// Validate the key
			$key = ((isset($row[$column]) AND $column) ? $column : key($row));
			$data[] = $row[$key];
		}
		return $data;
	}
	
	/**
	 * Fetches the first column of the first row of the SQL result.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * @param mixed  $column OPTIONAL - Key to use for the column index
	 * @return string
	 */
	public function fetchOne($sql, $bind = array(), $column = '')
	{
		$result = $this->fetchRow($sql, $bind);
		return ($column ? $result[$column] : (is_array($result) ? reset($result) : ''));
	}
	
	/**
	 * Prepares and executes an SQL statement with bound data.
	 *
	 * @param  mixed  $sql  The SQL statement with placeholders.
	 * @param  mixed  $bind An array of data to bind to the placeholders.
	 * @param  string Which query method to use
	 * 
	 * @return mixed  Result
	 */
	public function query($sql, $bind = array(), $which = 'query_read')
	{
		// make sure $bind is an array
		if (!is_array($bind))
		{
			$bind = (array)$bind;
		}
		
		if (!in_array($which, array('query_read', 'query_write', 'query_first')))
		{
			// Default to query read
			$which = 'query_read';
		}
		
		foreach ($bind as $key => $val)
		{
			if (is_numeric($key))
			{
				// Sort string mapping
				$val = (is_numeric($val) ? "'$val'" : "'" . $this->db->escape_string($val) . "'");
				
				// Replace first instance of ?
				$sql = implode($val, explode('?', $sql, 2));
			}
		}
		
		foreach ($bind as $key => $val)
		{
			if (!is_numeric($key))
			{
				// Array of token replacements
				$sql = str_replace($key, $val, $sql);
			}
		}
		
		// Set the table prefix
		$sql = preg_replace('/\s+`?\$/U', ' ' . TABLE_PREFIX, $sql);
		
		if ($this->debug)
		{
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			die();
		}		
		
		// Execute the query
		$this->result = $this->db->$which($sql);
		return $this->result;
	}
	
	/**
	 * Helper function for IN statements for SQL queries.
	 * For example, with an array $userids = array(1, 2, 3, 4, 5);
	 * the query would be WHERE userid IN' . $this->queryList($userids) . '
	 *
	 * @param  array The array to work with
	 * 
	 * @return mixed  Properly escaped and parenthesised IN() list
	 */
	public function queryList($arr)
	{
		$values = array();
		foreach ($arr as $val)
		{
			// Ensure the value is escaped properly
			$values[] = "'" . (is_numeric($val) ? $val : $this->db->escape_string($val)) . "'";
		}
		
		if (!count($values))
		{
			// Ensure there's no SQL errors
			$values[] = "'0'";
		}
		
		return 'IN(' . implode(', ', $values) . ')';
	}
}


// #############################################################################
// filter functionality class

/**
* Class that handles filtering arrays
*/
class VBSHOUT_FILTER
{
	/**
	* Id Field we are using
	*
	* @private	string
	*/	
	private static $idfield 	= NULL;
	
	/**
	* Id value we are looking for
	*
	* @private	mixed
	*/	
	private static $idval 		= NULL;
	
	
	
	/**
	* Sets up and begins the filtering process 
	*
	* @param	array	Array to filter
	* @param	string	What the ID Field is
	* @param	mixed	What we are looking for
	*
	* @return	array	Filtered array
	*/
	public static function filter($array, $idfield, $idval)
	{
		// Set the two things we can't pass on to the callback
		self::$idfield 	= $idfield;
		self::$idval	= $idval;
		
		// Filter this shiet
		return array_filter($array, array(__CLASS__, 'do_filter'));
	}
	
	/**
	* Checks if this element should be included
	*
	* @param	array	Array to filter
	*
	* @return	boolean	Whether we should include this or not
	*/	
	protected static function do_filter($array)
	{
		$idfield 	= self::$idfield;
		$idval		= self::$idval;
		return ($array["$idfield"] == $idval);
	}
}