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
define('THIS_SCRIPT', 'forumdisplay');
define('CSRF_PROTECTION', false);
// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();


$specialtemplates = array(
);

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array(
);




// ####################### PRE-BACK-END ACTIONS ##########################
if(file_exists('./global.php'.SUFFIX)){
	require_once('./global.php'.SUFFIX);
} else {
	require_once('./global.php');
}

function mark_all_as_read_func( $xmlrpc_params){
	global $vbulletin;
	if ($vbulletin->userinfo['userid'] == 0)
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}
	require_once(DIR . '/includes/functions_misc.php');
	$mark_read_result = mark_forums_read($foruminfo['forumid']);

	if (defined('NOSHUTDOWNFUNC'))
	{
		exec_shut_down();
	}
	return  	new xmlrpcval(
					array(
      	                  'result' => new xmlrpcval(false,"boolean"),
      	    			  'result_text' =>  new xmlrpcval('','base64')
					), "struct"
				);

}
?>