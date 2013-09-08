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

if (!class_exists('vB_DataManager', false))
{
	exit;
}

/**
* Class to do data save/delete operations for instances
*
* @package	vbshout
*/
class vBShout_DataManager_Instance extends vB_DataManager
{
	/**
	* Array of recognised and required fields for instances, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'instanceid' 		=> array(TYPE_UINT, 	REQ_INCR, 	VF_METHOD, 	'verify_nonzero'),
		'varname' 			=> array(TYPE_STR, 		REQ_YES, 	VF_METHOD),
		'name' 				=> array(TYPE_STR, 		REQ_YES, 	VF_METHOD),
		'description' 		=> array(TYPE_STR, 		REQ_NO),
		'active' 			=> array(TYPE_UINT, 	REQ_NO, 	VF_METHOD, 	'verify_onoff'),
		'displayorder' 		=> array(TYPE_UINT, 	REQ_NO),
		'autodisplay' 		=> array(TYPE_UINT, 	REQ_YES),
		'permissions' 		=> array(TYPE_NOCLEAN, 	REQ_NO, 	VF_METHOD, 	'verify_serialized'),
		'bbcodepermissions' => array(TYPE_NOCLEAN, 	REQ_NO, 	VF_METHOD, 	'verify_serialized'),
		'options' 			=> array(TYPE_NOCLEAN, 	REQ_YES, 	VF_METHOD, 	'verify_serialized'),
		'forumids' 			=> array(TYPE_NOCLEAN, 	REQ_NO, 	VF_METHOD, 	'verify_serialized'),
		'sticky' 			=> array(TYPE_STR, 		REQ_NO),
		'sticky_raw' 		=> array(TYPE_STR, 		REQ_NO),
		'notices' 			=> array(TYPE_NOCLEAN, 	REQ_NO, 	VF_METHOD, 	'verify_serialized'),
		'shoutsound' 		=> array(TYPE_STR, 		REQ_NO),
		'invitesound' 		=> array(TYPE_STR, 		REQ_NO),
		'pmsound' 			=> array(TYPE_STR, 		REQ_NO),
	);

	/**
	* Array of field names that are bitfields, together with the name of the variable in the registry with the definitions.
	*
	* @var	array
	*/
	//var $bitfields = array('adminpermissions' => 'bf_ugp_adminpermissions');

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_vbshout_instance';

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('instanceid = %1$d', 'instanceid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function vBShout_DataManager_Instance(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::vB_DataManager($registry, $errtype);

		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instancedata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the name is valid
	*
	* @param	string	Title of the instance
	*
	* @return	boolean
	*/
	function verify_name(&$name)
	{
		global $vbphrase;
		
		$name = strval($name);
		if ($name === '')
		{
			// Invalid
			return false;
		}
		
		/*
		// Check for existing instance of this name
		if ($existing = $this->registry->db->query_first("
			SELECT `name`
			FROM `" . TABLE_PREFIX . "dbtech_vbshout_instance`
			WHERE `name` = " . $this->registry->db->sql_prepare($name) . "
				" . ($this->existing['instanceid'] ? "AND `instanceid` != " . $this->registry->db->sql_prepare($this->existing['instanceid']) : '') . "			
			LIMIT 1
		"))
		{
			// Whoopsie, exists
			$this->error('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_instance'], $name);
			return false;
		}
		*/
		
		return true;
	}

	/**
	* Verifies that the varname is valid
	*
	* @param	string	varname of the instance
	*
	* @return	boolean
	*/
	function verify_varname(&$varname)
	{
		global $vbphrase;
		
		$varname = strval($varname);
		if ($varname === '')
		{
			// Invalid
			return false;
		}
		
		// Check for existing instance of this name
		if ($existing = $this->registry->db->query_first("
			SELECT `varname`
			FROM `" . TABLE_PREFIX . "dbtech_vbshout_instance`
			WHERE `varname` = " . $this->registry->db->sql_prepare($varname) . "
				" . ($this->existing['instanceid'] ? "AND `instanceid` != " . $this->registry->db->sql_prepare($this->existing['instanceid']) : '') . "			
			LIMIT 1
		"))
		{
			// Whoopsie, exists
			$this->error('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_instance'], $varname);
			return false;
		}
		
		return true;
	}

	/**
	* Verifies that the onoff flag is valid
	*
	* @param	string	On/Off flag
	*
	* @return	boolean
	*/
	function verify_onoff(&$onoff)
	{
		// Validate onoff
		$onoff = (!in_array($onoff, array(0, 1)) ? 1 : $onoff);
		
		return true;
	}

	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}

		$return_value = true;
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instancedata_presave')) ? eval($hook) : false;

		$this->presave_called = $return_value;
		return $return_value;
	}
	
	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		
		$return_value = true;
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instancedata_predelete')) ? eval($hook) : false;

		$this->presave_called = $return_value;
		return $return_value;
	}

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instancedata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		VBSHOUT_CACHE::build('instance');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		$this->registry->db->query_write("
			DELETE FROM `" . TABLE_PREFIX . "dbtech_vbshout_shout`
			WHERE `instanceid` = " . $this->registry->db->sql_prepare($this->existing['instanceid'])
		);
	
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instancedata_delete')) ? eval($hook) : false;
		
		// Rebuild the cache
		VBSHOUT_CACHE::build('instance');
		
		// Rebuild shout counters
		VBSHOUT::build_shouts_counter();		
		
		return true;
	}
}