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
* Class to do data save/delete operations for buttons
*
* @package	vbshout
*/
class vBShout_DataManager_Button extends vB_DataManager
{
	/**
	* Array of recognised and required fields for buttons, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'buttonid' 			=> array(TYPE_UINT, 	REQ_INCR, 	VF_METHOD, 	'verify_nonzero'),
		'title' 			=> array(TYPE_STR, 		REQ_YES, 	VF_METHOD),
		'active' 			=> array(TYPE_STR, 		REQ_NO, 	VF_METHOD, 	'verify_onoff'),
		'instanceid' 		=> array(TYPE_UINT, 	REQ_NO, 	VF_METHOD),
		'link' 				=> array(TYPE_STR, 		REQ_YES),
		'image' 			=> array(TYPE_STR, 		REQ_YES),
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
	var $table = 'dbtech_vbshout_button';

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('buttonid = %1$d', 'buttonid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Button of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function vBShout_DataManager_Button(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::vB_DataManager($registry, $errtype);

		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_buttondata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the title is valid
	*
	* @param	string	Title of the button
	*
	* @return	boolean
	*/
	function verify_title(&$title)
	{
		global $vbphrase;
		
		$title = strval($title);
		if ($title === '')
		{
			// Invalid
			return false;
		}
		
		/*
		// Check for existing button of this name
		if ($existing = $this->registry->db->query_first("
			SELECT `title`
			FROM `" . TABLE_PREFIX . "dbtech_vbshout_button`
			WHERE `title` = " . $this->registry->db->sql_prepare($title) . "
				" . ($this->existing['buttonid'] ? "AND `buttonid` != " . $this->registry->db->sql_prepare($this->existing['buttonid']) : '') . "			
			LIMIT 1
		"))
		{
			// Whoopsie, exists
			$this->error('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_button'], $title);
			return false;
		}
		*/
		
		return true;
	}

	/**
	* Verifies that the instanceid is valid
	*
	* @param	integer	instanceid of the button
	*
	* @return	boolean
	*/
	function verify_instanceid(&$instanceid)
	{
		global $vbphrase;
		
		return (is_array(VBSHOUT::$cache['instance']["$instanceid"]) OR $instanceid == 0);
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
		$onoff = (!in_array($onoff, array('0', '1')) ? '1' : $onoff);
		
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
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_buttondata_presave')) ? eval($hook) : false;

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
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_buttondata_predelete')) ? eval($hook) : false;

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
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_buttondata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		VBSHOUT_CACHE::build('button');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_buttondata_delete')) ? eval($hook) : false;
		
		// Rebuild the cache
		VBSHOUT_CACHE::build('button');
		
		return true;
	}
}