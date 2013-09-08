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
// cache functionality class

/**
* Class that handles keeping the database cache up to date.
*/
class VBSHOUT_CACHE
{
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	private static $vbulletin 		= NULL;
	
	/**
	* The prefix for the mod we are working with
	*
	* @public	string
	*/	
	public static $prefix 			= 'dbtech_vbshout_';
	
	/**
	* Array of cache fields
	*
	* @public	array
	*/	
	public static $cachefields 		= array();

	/**
	* Array of items to fetch
	*
	* @protected	array
	*/	
	protected static $queryfields	= array();

	/**
	* Array of items to NOT fetch
	*
	* @protected	array
	*/	
	protected static $exclude		= array();

	/**
	* Array of items to NOT fetch
	*
	* @protected	array
	*/	
	protected static $_tables		= array(
		'button'			=> '',
		'chatroom' 			=> '',
		'command' 			=> 'ORDER BY command ASC',
		'instance'			=> 'ORDER BY `displayorder` ASC',
	);
	
	
	
	/**
	* Initialises the database caching by setting the cache
	* list and begins verification of the data.
	*
	* @param	vB_Registry	Registry object
	* @param	string		Prefix
	* @param	array		(Optional) List of all cached arrays
	* @param	array		(Optional) List of values to not fetch
	*
	* @return	none		Nothing
	*/
	public static function init($vbulletin, $cachefields = array(), $exclude = array())
	{
		// Check if the vBulletin Registry is an object
		if (is_object($vbulletin))
		{
			// Yep, all good
			self::$vbulletin =& $vbulletin;
		}
		else
		{
			// Something went wrong here I think
			trigger_error(__CLASS__ . "::Registry object is not an object", E_USER_ERROR);
		}
		
		// Set exclude
		self::$exclude = $exclude;
		
		if (count($cachefields) > 0)
		{
			foreach ($cachefields as $key => $title)
			{
				if (strpos($title, self::$prefix) === false)
				{
					// Get rid of the non-relevant fields
					unset($cachefields[$key]);
				}
			}
			
			// Set the cleaned cachefields variable
			self::$cachefields = $cachefields;
		}
		
		if (count(self::$cachefields) == 0)
		{
			// We don't need this stuff
			return;
			
			// Something went wrong here I think
			//trigger_error("DBTech_Framework_Cache::Cachefields has no elements.", E_USER_ERROR);
		}
		
		// Check for valid info
		self::_checkDatastore();
		
		if (count(self::$queryfields) > 0)
		{
			// We need to re-query - prepare the string
			$itemlist = "'" . implode("','", self::$queryfields) . "'";
			
			if ($itemlist != "''")
			{
				// Do fetch from the database
				self::$vbulletin->datastore->do_db_fetch($itemlist);
			}
		}
		
		// Set the cache fields
		self::_set();		
	}

	/**
	* Builds the cache in case the datastore has been cleaned out.
	*
	* @param	string	Database table we are working with
	* @param	string	(Optional) Any additional clauses to the query
	*/
	public static function build($type, $clauses = '')
	{
		// Premove the prefix
		$dbtype = self::$prefix . $type;

		// Initialise the some arrays so we can add to them quicker
		$data = array();

		// Prepare the variable for the identifier
		$firstrow = $type . 'id';
		
		if (!is_object(VBSHOUT::$db))
		{
			// Ensure this doesn't error on upgrades
			return false;
		}

		VBSHOUT::$db->hideErrors();
		$data = VBSHOUT::$db->fetchAllKeyed('
			SELECT :dbtype.*
			FROM $:dbtype AS :dbtype
			:clauses
		', $firstrow, array(
			':dbtype' => $dbtype,
			':clauses' => isset(self::$_tables[$type]) ? self::$_tables[$type] : ''
		));
		foreach ($data as &$cols)
		{
			foreach ($cols as $key => $value)
			{
				// Loop through the query result and build the array
				$cols[$key] = addslashes($value);
			}
		}
		VBSHOUT::$db->showErrors();

		if (!is_array($data))
		{
			// Ensure this is an array
			$data = array();
		}

		// Finally update the datastore with the new value
		build_datastore($dbtype, serialize($data), 1);
		
		// Premove the prefix
		$field_short = substr($dbtype, strlen(self::$prefix));
		
		// Strip the slashes
		self::$vbulletin->input->stripslashes_deep($data);		
		
		// Set the data
		VBSHOUT::$cache[$field_short] = $data;
		
		foreach ((array)VBSHOUT::$cache[$field_short] as $id => $arr)
		{
			foreach ((array)VBSHOUT::$unserialize[$field_short] as $key)
			{
				// Do unserialize
				VBSHOUT::$cache[$field_short][$id][$key] = @unserialize(stripslashes($arr[$key]));
			}
		}
	}

	/**
	* Reads from the vB Optimise cache
	*
	* @param	string	Referencing the vB Optimise option varname
	* @param	string	The cache key to read from
	*/
	public static function read($cacheType, $key)
	{
		if (!self::_canCache($cacheType))
		{
			// We can't cache this
			return -1;
		}

		// Fetch the vBO data
		$_data = vb_optimise::$cache->get('dbtech.vbshout.' . $key);

		if (is_array($_data) AND TIMENOW < $_data['time'])
		{
			$i = 2;
			

			// We saved some queries
			vb_optimise::stat($i);
			vb_optimise::report('Fetched dbtech.vbshout.' . $key . ' from cache successfully.');

			return $_data['cache'];
		}

		return false;
	}

	/**
	* Writes to the vB Optimise cache
	*
	* @param	string	Database table we are working with
	* @param	string	(Optional) Any additional clauses to the query
	*/
	public static function write($data, $cacheType, $key)
	{
		if (!self::_canCache($cacheType))
		{
			// We can't cache this
			return false;
		}

		// By default, we want to "null out" the cache
		$_data = false;

		if ($data !== false)
		{
			// Write the vBO data
			$_data = array(
				'time'	=> TIMENOW + (self::$vbulletin->options['vbo_cache_vbshout' . $cacheType] * 3600),
				'cache'	=> $data,
			);
		}

		// Write the cache
		vb_optimise::$cache->set('dbtech.vbshout.' . $key, $_data);
		vb_optimise::report('Cached dbtech.vbshout.' . $key . ' successfully.');	

		return true;
	}

	/**
	* Writes to the vB Optimise cache
	*/
	public static function flush()
	{
		if (!self::_canCache())
		{
			// We can't cache this
			return false;
		}

		// Flush the cache
		vb_optimise::$cache->flush();

		return true;
	}

	/**
	* Checks whether or not datastore items are present,
	* and schedules for re-query if needed.
	*/
	private static function _checkDatastore()
	{
		foreach (self::$cachefields as $title)
		{
			if (strpos($title, self::$prefix) === false)
			{
				// We don't care.
				continue;
			}
			
			// Check if the value is set
			if (!isset(self::$vbulletin->$title))
			{
				if (in_array($title, self::$exclude))
				{
					// Skip this
					self::$vbulletin->$title = self::$exclude[$title];
				}
				else
				{
					// It wasn't :(
					self::$queryfields[] = $title;
				
					// Build datastore
					self::build(substr($title, strlen(self::$prefix)));
				}
			}
		}
	}
	
	/**
	* Sets the specified cache field after making sure all slashes
	* are stripped again
	*/
	private static function _set()
	{
		foreach (self::$cachefields as $field)
		{
			// Premove the prefix
			$field_short = substr($field, strlen(self::$prefix));
			
			// Fetch the data from the vB array
			$data = self::$vbulletin->$field;
			
			if (is_array($data))
			{
				// Strip the slashes
				self::$vbulletin->input->stripslashes_deep($data);

				// Unset from the vbulletin array to save memory
				unset(self::$vbulletin->$field);
			}
			else if (!in_array($field, self::$exclude))
			{
				// Ensure this is an array
				$data = array();
			}
			
			// Set the data
			VBSHOUT::$cache[$field_short] = $data;
		}
	}

	/**
	* Tests whether we can cache something
	*
	* @param	string	Original message
	* @param	string	Overriding
	*/	
	protected static function _canCache($cacheType = '')
	{
		if (!class_exists('vb_optimise'))
		{
			// We don't have vBO installed
			return false;
		}

		if (!isset(self::$vbulletin->options['vbo_online']))
		{
			// vBO is turned off
			return false;
		}

		if (!$cacheType)
		{
			// This will be used for the flush
			return true;
		}

		if (!isset(self::$vbulletin->options['vbo_cache_vbshout' . $cacheType]))
		{
			// vBO's version is too old
			return false;
		}

		if (!self::$vbulletin->options['vbo_cache_vbshout' . $cacheType])
		{
			// The cache time has been turned off
			return false;
		}

		return true;
	}	
}