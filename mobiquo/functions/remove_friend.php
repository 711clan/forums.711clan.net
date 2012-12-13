<?php
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/
defined('CWD1') or exit;
chdir(CWD1);
chdir('../');
// ####################### SET PHP ENVIRONMENT ###########################


// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('GET_EDIT_TEMPLATES', 'editsignature,updatesignature');
define('THIS_SCRIPT', 'profile');
define('CSRF_PROTECTION', false);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('user', 'timezone', 'posting', 'cprofilefield', 'cppermission');

// get special data templates from the datastore
$specialtemplates = array(
	'smiliecache',
	'bbcodecache',
	'banemail',
	'ranks',
	'noavatarperms'
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'USERCP_SHELL',
	'usercp_nav_folderbit'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'editprofile' => array(
		'modifyprofile',
		'modifyprofile_birthday',
		'userfield_checkbox_option',
		'userfield_optional_input',
		'userfield_radio',
		'userfield_radio_option',
		'userfield_select',
		'userfield_select_option',
		'userfield_select_multiple',
		'userfield_textarea',
		'userfield_textbox',
		'userfield_wrapper',
),
	'editoptions' => array(
		'modifyoptions',
		'modifyoptions_timezone',
		'userfield_checkbox_option',
		'userfield_optional_input',
		'userfield_radio',
		'userfield_radio_option',
		'userfield_select',
		'userfield_select_option',
		'userfield_select_multiple',
		'userfield_textarea',
		'userfield_textbox',
		'userfield_wrapper',
),
	'editavatar' => array(
		'modifyavatar',
		'help_avatars_row',
		'modifyavatar_category',
		'modifyavatarbit',
		'modifyavatarbit_custom',
		'modifyavatarbit_noavatar',
),
	'editusergroups' => array(
		'modifyusergroups',
		'modifyusergroups_joinrequestbit',
		'modifyusergroups_memberbit',
		'modifyusergroups_nonmemberbit',
		'modifyusergroups_displaybit',
		'modifyusergroups_groupleader',
),
	'editsignature' => array(
		'modifysignature',
		'forumrules'
		),
	'updatesignature' => array(
		'modifysignature',
		'forumrules'
		),
	'editpassword' => array(
		'modifypassword'
		),
	'editprofilepic' => array(
		'modifyprofilepic'
		),
	'joingroup' => array(
		'modifyusergroups_requesttojoin',
		'modifyusergroups_groupleader'
		),
	'editattachments' => array(
		'GENERIC_SHELL',
		'modifyattachmentsbit',
		'modifyattachments'
		),
	'addlist' => array(
		'modifyuserlist_confirm',
		),
	'removelist' => array(
		'modifyuserlist_confirm',
		),
	'buddylist' => array(
		'modifybuddylist',
		'modifybuddylist_user',
		'modifyuserlist_headinclude',
		),
	'ignorelist' => array(
		'modifyignorelist',
		'modifyignorelist_user',
		'modifyuserlist_headinclude',
		),
	'customize' => array(
		'memberinfo_usercss',
		'modifyusercss',
		'modifyusercss_backgroundbit',
		'modifyusercss_backgroundrow',
		'modifyusercss_bit',
		'modifyusercss_error',
		'modifyusercss_error_link',
		'modifyusercss_headinclude',
		'modifyprivacy_bit',
		),
	'privacy' => array(
		'modifyprofileprivacy',
		'modifyprivacy_bit'
		),
	'doprivacy' => array(
		'modifyprofileprivacy',
		'modifyprivacy_bit'
		)
		);
		$actiontemplates['docustomize'] = $actiontemplates['customize'];

		$actiontemplates['none'] =& $actiontemplates['editprofile'];

		// ######################### REQUIRE BACK-END ############################
		if(file_exists('./global.php'.SUFFIX)){
			require_once('./global.php'.SUFFIX);
		} else {
			require_once('./global.php');
		}
		if(file_exists(DIR . '/includes/functions_user.php'.SUFFIX)){
			require_once(DIR . '/includes/functions_user.php'.SUFFIX);
		} else {
			require_once(DIR . '/includes/functions_user.php');
		}

		// #######################################################################
		// ######################## START MAIN SCRIPT ############################
		// #######################################################################


		if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
		{
			$return = array(20,'security error (user may not have permission to access this feature)');
			return return_fault($return);
		}

		if (empty($vbulletin->userinfo['userid']))
		{
			$return = array(20,'security error (user may not have permission to access this feature)');
			return return_fault($return);
		}


		function remove_friend_func($xmlrpc_params){
			global $permissions,$vbulletin,$db;
			chdir(CWD1);
			chdir('../');
			$params = php_xmlrpc_decode($xmlrpc_params);
			if(!$params[0])
			{
				$return = array(2,'no  user id param.');
				return return_fault($return);
			}


			$user_name =    mobiquo_encode($params[0],'to_local');
			$user_id   = get_userid_by_name($user_name);

			if(!$user_id){

				$return = array(7,'invalid user id');
				return return_fault($return);
			}

			$vbulletin->GPC['userid'] = $user_id;
			$vbulletin->GPC['userlist'] = 'friend';

			$userinfo = mobiquo_verify_id('user', $vbulletin->GPC['userid'], true, true);
			cache_permissions($userinfo);


			// no referring URL, send them back to the profile page
			if ($vbulletin->url == $vbulletin->options['forumhome'] . '.php')
			{
				$vbulletin->url = 'member.php?' . $vbulletin->session->vars['sessionurl'] . "u=$userinfo[userid]";
			}

			// No was clicked
			if ($vbulletin->GPC['deny'])
			{
				$return = array(20,'security error (user may not have permission to access this feature)');
				return return_fault($return);
			}

			$users = array();
			switch ($vbulletin->GPC['userlist'])
			{
				case 'friend':
					$db->query_write("
				UPDATE " . TABLE_PREFIX . "userlist
				SET friend = 'no'
				WHERE userid = " . $vbulletin->userinfo['userid'] . "
					AND relationid = $userinfo[userid]
					AND type = 'buddy'
					AND friend = 'yes'
			");
					if ($db->affected_rows())
					{
						$users[] = $vbulletin->userinfo['userid'];
						$db->query_write("
					UPDATE " . TABLE_PREFIX . "userlist
					SET friend = 'no'
					WHERE relationid = " . $vbulletin->userinfo['userid'] . "
						AND userid = $userinfo[userid]
						AND type = 'buddy'
						AND friend = 'yes'
				");
						if ($db->affected_rows())
						{
							$users[] = $userinfo['userid'];
						}
						$db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET friendcount = IF(friendcount >= 1, friendcount - 1, 0)
					WHERE userid IN(" . implode(", ", $users) . ")
						AND friendcount <> 0
				");
					}
					// this option actually means remove buddy in this case, do don't break so we fall through.
					if (!$vbulletin->GPC['friend'])
					{
						break;
					}
				case 'buddy':
					$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "userlist
				WHERE userid = " . $vbulletin->userinfo['userid'] . "
					AND relationid = $userinfo[userid]
					AND type = 'buddy'
			");
					if ($db->affected_rows())
					{
						$users[] = $vbulletin->userinfo['userid'];

						// The user could have been a friend too
						list($pendingcount) = $db->query_first("
					SELECT COUNT(*)
					FROM " . TABLE_PREFIX . "userlist AS userlist
					LEFT JOIN " . TABLE_PREFIX . "userlist AS userlist_ignore ON(userlist_ignore.userid = " . $userinfo['userid'] . " AND userlist_ignore.relationid = userlist.userid AND userlist_ignore.type = 'ignore')
					WHERE userlist.relationid = " . $userinfo['userid'] . "
						AND userlist.type = 'buddy'
						AND userlist.friend = 'pending'
						AND userlist_ignore.type IS NULL", DBARRAY_NUM
						);

						$db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET friendreqcount = $pendingcount
					WHERE userid = " . $userinfo['userid']
						);
					}
					break;
				case 'ignore':
					$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "userlist
				WHERE userid = " . $vbulletin->userinfo['userid'] . "
					AND relationid = $userinfo[userid]
					AND type = 'ignore'
			");
					if ($db->affected_rows())
					{
						$users[] = $vbulletin->userinfo['userid'];
					}
					break;
				default:
					standard_error(fetch_error('invalidid', 'list', $vbulletin->options['contactuslink']));
			}

			require_once(DIR . '/includes/functions_databuild.php');
			foreach($users AS $userid)
			{
				build_userlist($userid);
			}



			if (defined('NOSHUTDOWNFUNC'))
			{
				exec_shut_down();
			}

			return 	 new xmlrpcresp(
			new xmlrpcval(
			array(
			      	                  'result' => new xmlrpcval(true,"boolean"),
			                          'display_text' =>  new xmlrpcval("",'base64')),
			                              "struct"
			                              )
			                              );
		}

		?>
