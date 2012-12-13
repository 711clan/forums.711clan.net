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
require_once(CWD1.'/push_notification/push_notification.php');
chdir(CWD1);
chdir('../');
if(file_exists('./global.php'.SUFFIX)){
	require_once('./global.php'.SUFFIX);
} else {
	require_once('./global.php');
}
function push_notify_func($xmlrpc_params){
	$params = php_xmlrpc_decode($xmlrpc_params);
	$device_token = $params[1];
	$user_name =    mobiquo_encode($params[0],'to_local');
	$user_id   = get_userid_by_name($user_name);

	$action    = $params[2];
	if(!$user_id or $user_id < 0){
		$return = array(7,'invalid user id');
		return return_fault($return);
	}
	$user_name_base64= base64_encode($params[0]);
	write_device_token($user_name_base64,$device_token,$action);
	return new xmlrpcresp(new xmlrpcval(true,'boolean'));
}
?>
