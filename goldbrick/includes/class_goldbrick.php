<?php
/**
 * Goldbrick Media System
 *
 * @package Vbulletin 3.6 - 3.7
 * @author Vincent Waller (Nix)
 **/

class Goldbrick_Media
{
	/**
	 * vBulletin registry object
	 * @var		vB_Registry
	 */
	private $registry;
	
	/**
	 * Media information array to cache
	 * @var		array
	 */
	private $info;
	
	/**
	 * Fields that are created using preg_match
	 * Sites that do not define a custom set of fields will use the 'global' subset.
	 *
	 * @var		array
	 */
	private $regex_fields = array(
		'global' => array(
			'idregex'	 => array('id',	   'url'),
			'titleregex' => array('title', 'content')
		),
		'custom' => array()
	);

	/**
	 * Fields that are formatted using sprintf
	 * Sites that do not define a custom set of fields will use the 'global' subset.
	 * 
	 * @var		array
	 */
	private $format_fields = array(
		'global' => array(
			'srcformat'		 => array('src',	  'id'),
			'thumbformat'	 => array('thumb',	  'id'),
			'flashvarformat' => array('flashvar', 'id')
		),
		'custom' => array()
	);
	
	/**
	 * Valid media extensions
	 * 
	 * @var		array
	 */
	private $extensions = array(
		
		'flash'			=> array('flv', 'swf', 'mp3'),
		'quick_time'	=> array('mov', 'mpeg', 'mpg', 'mp4'),
		'real_media'	=> array('rm', 'ra', 'rv', 'ram'),
		'windows_media'	=> array('wma', 'wav', 'ogg', 'ape', 'mid', 'midi', 'asf', 'asx', 'wm', 'wmv'),
		'divx'			=> array('divx', 'avi'),
		'adobe_pdf'		=> array('pdf'),
		'image'			=> array('gif', 'jpg', 'jpeg', 'bmp', 'png')
	);
	
	/**
	 * Valid media options
	 * 
	 * @var		array
	 */
	
	private $gb_options = array(
		'title',
		'width',
		'height',
		'autoplay',
		'loop'
	);

	/**
	 * A list of fields for the gb_cache table.
	 * 
	 * Items in $info that are not in this array will be removed before inserting.
	 * Items in $db_fields that are not in $info will throw a fatal error.
	 * 
	 * @var		array
	 */
	private $db_fields = array(
		'width',
		'height',
		'widthpad',
		'title',
		'url',
		'src',
		'mime',
		'flashvar',
		'flashvarextra',
		'thumb',
		'loop',
		'extension',
		'site',
		'profile'
	);
	
	/**
	 * A list of safe variables to import from the site configuration files.
	 * @var		array
	 */
	private $safe_fields = array(
		'info'			=> true, 
		'regex_fields'	=> false, 
		'format_fields' => false
	);
	
	/**
	 * Target URL is already cached
	 * @var		boolean
	 */
	private $existing = false;
	
	/**
	 * A list of "behaviour" setting arrays that use the global/custom structure
	 * @var		array
	 */
	private $behaviours = array('regex_fields', 'format_fields');
	
	/**
	 * Enables debug mode for this class
	 * @var		boolean
	 */
	private $debug;
	
	/**
	 * Sets up reference to registry object.
	 * 
	 * @param	vB_Registry
	 * @param	boolean		Enable debug mode
	 */
	public function __construct(vB_Registry $registry, $debug = false)
	{
		$this->registry = $registry;
		$this->debug	= $debug || defined('GOLDBRICK_DEBUG_CACHE');
	}

	/**
	 * Takes a URL and returns the information about the media file/host
	 * 
	 * @param	string	URL to parse
	 * @return	array	URL information
	 */
	public function parse_url($url, $gb_options = null)
	{
		if ($this->debug)
		{
			goldbrick_debug('Source URL', $url);
		}
		
		if ($existing = $this->check_existing($url))
		{
			$this->info		= $existing;
			$this->existing = true;
			
			if ($this->debug)
			{
				echo '<h2>URL Already Cached!</h2>';
			}
			return $existing;
		}

		if (!$identifier_site = $this->find_host_identifier($url) AND !$identifier_ext = $this->find_valid_extension($url))
		{
			return false;
		}

		if ($identifier_site)
		{
			$identifier = $identifier_site;
			
			$this->load_config_values($identifier, $gb_options);
			
			$this->info['site'] = $identifier;
		}
		
		else if ($identifier_ext)
		{
			$identifier = $identifier_ext;
			
			$this->load_config_ext_values($identifier, $gb_options, $url);
		}

		else
		{
			return false;
		}

		$this->info['url']	= $url;
		
		if (!is_array($identifier) AND !$this->is_valid_url($url))
		{
			if ($this->debug) echo '<h2>Invalid URL</h2>';
			return false;
		}

		if (empty($this->info['norequest']) AND $identifier_site)
		{
			$this->info['content'] = $this->open_site($url);
		}

		if (function_exists($function = "goldbrick_hook_{$identifier}_opened"))
		{
			$function($this->info);
		}
		
		if(!is_array($identifier))
		{
			$this->exec_regex_fields(
				$this->get_settings_source($identifier, 'regex')
			);

			$this->exec_format_fields(
				$this->get_settings_source($identifier, 'format')
			);
		}

		if (function_exists($function = "goldbrick_hook_{$identifier}_complete"))
		{
			$function($this->info);
		}

		if ($this->info['increase_size'])
		{
			$this->info['widthpad']		= $this->info['width'] + $this->info['increase_size'];
			$this->info['height']		= $this->info['height'] + $this->info['increase_size'];
		}

		if ($this->debug)
		{
			goldbrick_debug('Info after processing', $this->info);
			
			foreach ($this->info as $key => $value)
			{
				if (!in_array($key, $this->db_fields))
				{
					unset($this->info[$key]);
				}
			}
			
			goldbrick_debug('Final array to be saved', $this->info);
			
			foreach ($this->db_fields as $field)
			{
				if (!isset($this->info[$field]))
				{
					trigger_error("required field $field is missing", E_USER_ERROR);
				}
			}
			exit;
		}

		return $this->info;
	}

	/**
	 * Checks to see if a URL exists in the database
	 * @param	string	URL to check
	 * @return	mixed	URL information array or FALSE on failure
	 */
	private function check_existing($link)
	{
		$hash = md5($link);

		return $this->registry->db->query_first("
			SELECT *
			FROM " . TABLE_PREFIX . "gb_cache
			WHERE hash = '$hash'
		");
	}

	/**
	 * Extracts the host 'identifier' from a URL.
	 * ex: 'youtube', 'metacafe', etc.
	 * 
	 * @param	string	Source URL
	 * @return	string	host identifier
	 */
	private function find_host_identifier($url)
	{
		$matches = array();
		preg_match('/^(http:\/\/)?([^\/]+)/i', $url, $matches);
		
		$host  = preg_replace('/^([\.a-z0-9]+)/', '$1', str_replace('www.', '', $matches[2]));
		$parts = explode('.', $host);
		
		if (file_exists(DIR . '/goldbrick/includes/sites/' . $parts[1] . '.php'))
		{
			return $parts[1];
		}
		
		if (file_exists(DIR . '/goldbrick/includes/sites/' . $parts[0] . '.php'))
		{
			return $parts[0];
		}
		
		return false;
	}

	/**
	 * Check URL for valid extensions.
	 * ex: '.mp3', '.png', etc.
	 * 
	 * @param	string	Source URL
	 * @return	string	host identifier
	 */
	private function find_valid_extension($url)
	{
		
		$gb_ext = strtolower(file_extension($url));
		
		if ($gb_array = multiarrayearch($gb_ext, $this->extensions))
		{
			
			$ext = 'gb_' . $gb_array[1];
			
			if ($this->registry->options['gb_allowed_ext'] & $this->registry->bf_misc_gb_allowed_ext[$ext])
			{
				if (file_exists(DIR . '/goldbrick/includes/extensions/' . $gb_array[1] . '.php'))
				{
					return $gb_array;
				}
			}
		}
	}
	
	/**
	 * Loads configuration values into object context.
	 * 
	 * @param	string		Site identifier
	 */
	protected function load_config_values($identifier, $gb_options)
	{
		$changes = array();
		
		$data	 = goldbrick_cache_load_config($identifier, array_keys($this->safe_fields), $gb_options);
		
		foreach ($this->safe_fields as $field => $auto)
		{
			if (!isset($data[$field]))
			{
				return;
			}
			
			if ($auto)
			{
				$this->$field = $data[$field];		
			}
			
			else if (in_array($field, $this->behaviours))
			{
				$this->{$field}['custom'][$identifier] = $data[$field];
			}
			
			if ($this->debug)
			{
				goldbrick_debug("\$$field loaded from config", $data[$field]);
			}
		}
	}
	
	/**
	 * Loads configuration values into object context.
	 * 
	 * @param	string		Site identifier
	 */
	protected function load_config_ext_values($identifier, $gb_options, $url)
	{
		$changes = array();

		$data	 = goldbrick_cache_load_ext_config($identifier, array_keys($this->safe_fields), $gb_options, $url);

		foreach ($this->safe_fields as $field => $auto)
		{
			if (!isset($data[$field]))
			{
				return;
			}
			
			if ($auto)
			{
				$this->$field = $data[$field];		
			}
			
			else if (in_array($field, $this->behaviours))
			{
				$this->{$field}['custom'][$identifier] = $data[$field];
			}
			
			if ($this->debug)
			{
				goldbrick_debug("\$$field loaded from config", $data[$field]);
			}
		}
	}
	
	/**
	 * Determines whether or not a posted URL is valid based on the guidelines
	 * in the site config.
	 * 
	 * @param	string		URL
	 * @return	boolean		true = valid ; false = invalid
	 */
	private function is_valid_url($url)
	{
		$settings = $this->get_settings_source($this->info['site'], 'regex');

		if (isset($this->info['validregex']))
		{
			$func  = 'array_values';
			$regex = $this->info['validregex'];
			unset($this->info['validregex']);
		}	

		else if (isset($this->info['idregex']) and $settings['idregex'][1] ==  'url')
		{
			$func  = 'array_keys';
			$regex = $this->info['idregex'];
		}
		
		else
		{
			var_dump($this->info);
			trigger_error("$url has not been validated!", E_USER_ERROR);
		}

		if (is_array($regex))
		{
			foreach ($func($regex) as $expression)
			{
				if (preg_match($expression, $url))
				{
					return true;
				}
			}	
			return false;
		}
		
		return preg_match($regex, $url);
	}
	
	/**
	 * Determines whether or not a posted URL is valid based on the guidelines
	 * in the extension config.
	 * 
	 * @param	string		URL
	 * @return	boolean		true = valid ; false = invalid
	 */
	public function	 is_valid_attachment($attach_ext)
	{
		global $vbulletin;

		if ($gb_array = multiarrayearch($attach_ext, $this->extensions))
		{
			if (file_exists(DIR . '/goldbrick/includes/extensions/' . $gb_array[1] . '.php'))
			{
				return $gb_array;
			}
		}
	}
	
	/**
	 * Fetches the $type settings for a given site.
	 * If the site defines its own ['custom'] subarray of a setting type, then
	 * it will be used -- if not, then it will fall back on the ['global'] group.
	 * 
	 * @param	string		Site identifier
	 * @param	string		Sub array ('regex' or 'format')
	 */
	private function get_settings_source($identifier, $type = 'regex')
	{
		$key = $type . '_fields';
		
		return	!empty($this->{$key}['custom'][$identifier])
			? $this->{$key}['custom'][$identifier]
			: $this->{$key}['global'];
	}
	
	/**
	 * Opens a remote connection and fetches HTML from the page.
	 * 
	 * @param	string	Remote URL
	 * @return	string	HTML Response
	 */
	private function open_site($url)
	{
		if (empty($url))
		{
			$url = unhtmlspecialchars($this->info['link']);
		}
		
		if (function_exists('curl_init'))
		{
			$user_agent		= 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11';
			$header[0]		= "Accept: text/xml,application/xml,application/xhtml+xml,";
			$header[0]		.= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
			$header[]		= "Cache-Control: max-age=0";
			$header[]		= "Connection: keep-alive";
			$header[]		= "Keep-Alive: 300";
			$header[]		= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
			$header[]		= "Accept-Language: en-us,en;q=0.5";
		
			if ($this->info['header'])
			{
				$header[] = $this->info['header'];
			}
			
			$header[] = "Pragma: "; // browsers keep this blank.
		
			$handle = curl_init();

			curl_setopt($handle, CURLOPT_URL, $url);
			curl_setopt($handle, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
			curl_setopt($handle, CURLOPT_REFERER, 'http://www.google.com');
			curl_setopt($handle, CURLOPT_ENCODING, 'gzip,deflate');
			curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($handle, CURLOPT_AUTOREFERER, true);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
			
			if ($this->info['postfield'])
			{
				curl_setopt($handle, CURLOPT_POSTFIELDS, $this->info['postfield']);
			}
			
			curl_setopt($handle, CURLOPT_TIMEOUT, 30);
			
			$content = curl_exec($handle);

			curl_close($handle);
		} 
		
		else if (@fclose(@fopen($url, 'r')) and ini_get('allow_url_fopen')) 
		{
			$content = file($url);
			$content = implode('', $content);
		} 
		
		else 
		{
			return false;
		}
		
		return $content;
	}
	
	/**
	 * Executes all regular expression field replacements.
	 * 
	 * $settings contains the settings data used by the site (custom or global) 
	 * which decides:
	 * 
	 * 'key' - $this->info key containing regular expression array
	 * [0]	 - $this->info key to set
	 * [1]	 - $this->info key to use as RE subject
	 * 
	 * $this->info[$regexfield] contains an array: 'regex' => match index, ...
	 * 
	 * @param	array		Regex settings
	 */
	private function exec_regex_fields($settings)
	{
		foreach ($settings as $regexfield => $regexinfo)
		{
			list($realfield, $var) = $regexinfo;

			if ($this->info[$regexfield])
			{
				if (!is_array($this->info[$regexfield]))
				{
					$this->info[$regexfield] = array($this->info[$regexfield]);
				}

				foreach ($this->info[$regexfield] as $regex => $match)
				{
					$matches = null;
					if (preg_match($regex, $this->info[$var], $matches))
					{
						//goldbrick_debug('Passed Regex', $regex, $var, $this->info[$var]);
						
						$this->info[$realfield] = $matches[$match];

						break;
					}
					
					if ($this->debug)
					{
						goldbrick_debug('Failed Regex', $regex, $var, $this->info[$var]);
					}
				}
			}

			unset($this->info[$regexfield]);
		}
	}
	
	/**
	 * Executes all field formatting using sprintf.
	 * 
	 * $settings contains the settings data used by the site (custom or global) 
	 * which decides:
	 * 
	 * 'key' - $this->info key containing format string
	 * [0]	 - $this->info key to set
	 * [1]	 - $this->info key to use as Nth value in sprintf...
	 * [2]	 - ...
	 * 
	 * @param	array		Format settings
	 */
	private function exec_format_fields($settings)
	{
		foreach ($settings as $format_input => $formatinfo)
		{
			$format_output = array_shift($formatinfo);
			
			$params = array();
			foreach ($formatinfo as $param)
			{
				$params[] = $this->info[$param];
			}
			
			unset($formatinfo);

			if ($this->info[$format_input])
			{
				array_unshift($params, $this->info[$format_input]);
				$this->info[$format_output] = call_user_func_array('sprintf', $params);
			}

			unset($this->info[$format_input]);
		}
	}

	/**
	 * Saves the current $cache_media [ $this->info ] array to the database.
	 * 
	 * @param	array		Media information to store
	 * @param	integer		Userid of poster
	 * @param	integer		Postid - 0 when creating a new post
	 * @param	string		Posthash - '' when editing posts
	 * @param	integer		Type - 0 = null, 1 = post, 2 = profile, 3 = blog
	 */
	public function save($cache, $userid, $postid = 0, $posthash = '', $type = 0)
	{
		if (!$this->existing)
		{
			foreach ($cache as $key => $value)
			{
				if (!in_array($key, $this->db_fields))
				{
					unset($cache[$key]);
				}
			}
			
			/*foreach ($this->db_fields as $field)
			{
				if (!isset($this->info[$field]))
				{
					echo $field;
					trigger_error("required field $field is missing", E_USER_ERROR);
				}
			}*/
			
			$cache['hash']		= md5(unhtmlspecialchars($cache['url']));
			$cache['dateline']	= TIMENOW;
			$cache['unique']	= substr($cache['hash'], 0, 8);
		}
		
		$media = array(
			'unique'   => $cache['unique'],
			'hash'	   => $cache['hash'],
			'postid'   => $postid,
			'posthash' => $posthash,
			'userid'   => $userid,
			'type'     => $type
		);


		if (!$this->existing)
		{
			$this->registry->db->query_write(fetch_query_sql($cache, 'gb_cache'));
			$this->registry->db->query_write(fetch_query_sql($media, 'gb_media'));
			
			//require_once(DIR . '/goldbrick/includes/functions_goldbrick.php');
			//insert_tags(null, $cache['site'], '1');
		}
	}
	
	/**
	 * Finalizes the cache data by replacing the posthash with the postid after it
	 * has been posted.
	 * 
	 * @param	integer		Postid
	 * @param	string		Posthash
	 */
	public function set_postid($postid, $posthash)
	{
		$this->registry->db->query_write(fetch_query_sql(
			array(
				'postid'   => $postid,
				'posthash' => ''
			),
			'gb_media',
			"WHERE posthash = '$posthash'"
		));
	}
	
	/**
	 * Delivers the HTML for a given media tag.
	 * This is the BBCode callback function (wrapped in a public callback, rather).
	 * 
	 * @param	string		URL to deliver
	 * @param	string		Options to customize delivery
	 * 
	 * @return	string		HTML output
	 */
	public function deliver($url, $options)
	{
		global $vbulletin, $vbphrase, $stylevar;

		$url = unhtmlspecialchars($url);
		

		/*if (!$info = $this->media[$url])
		{
			if ($this->debug)
			{
				goldbrick_debug('Media Cache', $this->media);
				goldbrick_debug('Requested URL', $url);
				
				trigger_error('URL not pre-cached!', E_USER_WARNING);
			}
			$url = htmlspecialchars_uni($url);
			return "<a href=\"$url\" target=\"_blank\">$url</a>";
		}*/
		//$info['unique']  = substr($info['hash'], 0, 8);

		if ($info['site'] !== 0)
		{
			//$info['profile'] = $this->get_config_profile($info['site']);
		}

		else
		{
			$info['profile'] = $this->get_config_ext_profile($info['profile']);
		}

		if (is_integer($url))
		{
			$info = array_merge($info, $this->parse_media_options($options));
		}

		if ($this->debug)
		{
			goldbrick_debug('Delivering Media', $url);
			echo $content . '<hr />';
		}
		
		$cutoff = 1;#$this->registry->options['gb_expiration_period'] * 86400;
		
		// cleanup
		/*if ($info['dateline'] + $cutoff < TIMENOW)
		{
			if (empty($this->expired))
			{
				goldbrick_inject_plugin(
					'global_complete',
					"require_once(DIR . '/goldbrick/plugins/global_complete.php');"
				);
			}
			$this->expired[] = md5($url);
		}*/

		return $content;
	}

	/**
	 * Sets the post ids to load media for
	 * 
	 * @param	array		List of postids
	 */
	public function set_postids($postids)
	{
		if ($this->debug)
		{
			goldbrick_debug('PostIDs', $postids);
		}

		if (is_array($posids))
		{
			$this->media = $this->fetch_media_from_cache(implode(',', $postids));
		}
		
		else
		{
			$this->media = $this->fetch_media_from_cache($postids);
		}
		
		return $this->media;
		
		if ($this->debug)
		{
			goldbrick_debug('Fetched Media', $this->media);
		}
	}
	
	
	/**
	 * Sets the post ids to load media for
	 * 
	 * @param	array		List of postids
	 */
	public function set_uniques($uniques)
	{
		if ($this->debug)
		{
			goldbrick_debug('PostIDs', $postids);
		}
		
		/*if (is_array($uniques))
		{
			$this->media = $this->fetch_media_from_cache(implode(',', $postids));
		}
		
		else
		{
			$this->media = $this->fetch_media($fetch_hash);
		}*/

		if (!$this->media = $this->fetch_media($uniques))
		{
			return null;
		}

		return $this->media;
		
		if ($this->debug)
		{
			goldbrick_debug('Fetched Media', $this->media);
		}
	}
	
	/**
	 * Fetches all media associated with $postids from the cache.
	 * 
	 * @param	string		Comma-separated list of post ids
	 * @return	array		Media records
	 */
	private function fetch_media_from_cache($postids)
	{
		$result = $this->registry->db->query_read("
			SELECT cache.*
			FROM " . TABLE_PREFIX . "gb_media
			LEFT JOIN " . TABLE_PREFIX . "gb_cache as cache using (hash)
			WHERE postid IN ($postids)
		");

		$records = array();
		while ($record = $this->registry->db->fetch_row($result))
		{
			$records = $record;
		}

		$this->registry->db->free_result($record);

		return $records;
	}
	
	private function fetch_media($uniques)
	{
		/*$result = $this->registry->db->query_read("
			SELECT *
			FROM " . TABLE_PREFIX . "gb_cache
			WHERE $uniques = hash
		");

		$records = array();
		while ($record = $this->registry->db->fetch_array($result))
		{
			$records = $record;
		}*/

		$records = $this->registry->db->query_first("
		SELECT * FROM " . TABLE_PREFIX . "gb_cache
		WHERE hash = '" . $uniques . "'
		");
		//$this->registry->db->free_result($record);

		return $records;
	}
}
?>