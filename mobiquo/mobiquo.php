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
include("./include/xmlrpc.inc");
include("./include/xmlrpcs.inc");
define('DISABLE_HOOKS', true);
define('CWD1', (($getcwd = getcwd()) ? $getcwd : '.'));
error_reporting(0);
$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();
@ob_start();
require_once("./include/common.php");
require_once('./include/vbulletin_common.php');


include("./method_register.php");

define('SUFFIX', (($suffix = get_suffix()) ? $suffix : ''));
define('IN_MOBIQUO',true);

$request = $HTTP_RAW_POST_DATA ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
$parsersR = php_xmlrpc_decode_xml($request);

$requestMethod = $parsersR->methodname;
$requestParams = $parsersR->params;

if($requestMethod == 'get_config' || $requestMethod == 'authorize_user'){

	define('THIS_SCRIPT', 'register');
	define('CSRF_PROTECTION', false);
	define('CSRF_SKIP_LIST', 'login');

}

if($requestMethod == 'authorize_user' or $requestMethod =='logout_user'){
	require_once('./functions/authorize_user.php');
	chdir(CWD1);
}
if($requestMethod == 'login'){
	require_once('./functions/login.php');
	chdir(CWD1);
}
if($requestMethod == 'get_topic'){
	require_once('./functions/get_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'get_thread' or $requestMethod == 'get_thread_by_post' or $requestMethod == 'get_thread_by_unread'){
	require_once('./functions/get_thread.php');
	chdir(CWD1);
}
if($requestMethod == 'get_forum'){
	require_once('./functions/get_forum.php');
	chdir(CWD1);
}
if($requestMethod == 'get_user_topic' or $requestMethod == 'get_user_reply_post' ){
	require_once('./functions/get_user_topics.php');
	chdir(CWD1);
}

if($requestMethod == 'get_participated_topic'){

	require_once('./functions/get_participated_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'get_user_info'){

	require_once('./functions/get_user_info.php');
	chdir(CWD1);
}
if($requestMethod == 'get_new_topic'){
	require_once('./functions/get_new_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'get_config'){
	require_once('./functions/get_config.php');
	chdir(CWD1);
}

require_once('./functions/return_fault.php');
chdir(CWD1);

if($requestMethod == 'create_topic'){
	require_once('./functions/create_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'new_topic'){
	require_once('./functions/new_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'reply_topic'){
	require_once('./functions/reply_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'reply_post'){
	require_once('./functions/reply_post.php');
	chdir(CWD1);
}
if($requestMethod == 'get_board_stat'){
	require_once('./functions/get_board_stat.php');
	chdir(CWD1);
}
if($requestMethod == 'get_subscribed_topic'){
	require_once('./functions/get_subscribed_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'get_subscribed_forum'){
	require_once('./functions/get_subscribed_forum.php');
	chdir(CWD1);
}
if($requestMethod == 'get_inbox_stat' or $requestMethod == 'get_box_info' or $requestMethod =='get_box'or $requestMethod =='get_message' or $requestMethod == 'delete_message'
or $requestMethod == 'create_message' or $requestMethod == 'mark_pm_unread' or $requestMethod == 'get_quote_pm' or $requestMethod == 'report_pm'){
	require_once('./functions/get_pm_stat.php');
	chdir(CWD1);
}

if($requestMethod == 'subscribe_topic'){
	require_once('./functions/subscribe_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'subscribe_forum'){
	require_once('./functions/subscribe_forum.php');
	chdir(CWD1);
}
if($requestMethod == 'unsubscribe_forum'){
	require_once('./functions/unsubscribe_forum.php');
	chdir(CWD1);
}
if($requestMethod == 'unsubscribe_topic'){
	require_once('./functions/unsubscribe_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'get_online_users'){
	require_once('./functions/get_online_users.php');
	chdir(CWD1);
}
if($requestMethod == 'push_notify'){
	require_once('./functions/push_notify.php');
	chdir(CWD1);
}
if($requestMethod == 'save_raw_post'){
	require_once('./functions/save_raw_post.php');
	chdir(CWD1);
}
if($requestMethod == 'get_raw_post'){
	require_once('./functions/get_raw_post.php');
	chdir(CWD1);
}
if($requestMethod == 'attach_image'){
	require_once('./functions/attach_image.php');
	chdir(CWD1);
}
if($requestMethod == 'search_topic' or $requestMethod == 'search_post'){
	require_once('./functions/search_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'mark_all_as_read'){
	require_once('./functions/mark_all_as_read.php');
	chdir(CWD1);
}
if($requestMethod == 'get_unread_topic'){
	require_once('./functions/get_unread_topic.php');
	chdir(CWD1);
}
if($requestMethod == 'get_quote_post'){
	require_once('./functions/get_quote_post.php');
	chdir(CWD1);
}
if($requestMethod == 'report_post'){
	require_once('./functions/report_post.php');
	chdir(CWD1);
}
if($requestMethod == 'login_forum'){
	require_once('./functions/login_forum.php');
	chdir(CWD1);
}
if($requestMethod == 'get_announcement'){
	require_once('./functions/get_announcement.php');
	chdir(CWD1);
}
if($requestMethod == 'get_friend_list'){
	require_once('./functions/get_friend_list.php');
	chdir(CWD1);
}
if($requestMethod == 'add_friend'){
	require_once('./functions/add_friend.php');
	chdir(CWD1);
}
if($requestMethod == 'remove_friend'){
	require_once('./functions/remove_friend.php');
	chdir(CWD1);
}
$rpcServer = new xmlrpc_server($methodContainer,false);

$rpcServer->compress_response = 'true';
$rpcServer->response_charset_encoding ='UTF-8';


if(!array_key_exists($requestMethod,$methodContainer)){
	$request =  gFaultXmlRequest(new xmlrpcval(5,'int'),new xmlrpcval('no matched method','string'));
	$response = $rpcServer->service($request);
	exit(0);
}

if(isset($vbulletin) && $vbulletin->userinfo['userid'] != 0){
	header('Mobiquo_is_login:true');
} else {
	header('Mobiquo_is_login:false');
}


require_once('./conf/config.php');
//$xmlrpc_response = $rpcServer->service($request);
$mobiquo_config = new mobiquo_config();
$config =$mobiquo_config->get_config();


if(trim($config['hide_forum_id']) != ""){
	$hideForumList = explode(",", $config['hide_forum_id']);
	foreach($hideForumList as $forumid){
		$vbulletin->userinfo['forumpermissions'][$forumid] = 655374;
	}
}
if($config['guest_okay'] == 0 &&  $vbulletin->userinfo['userid'] == 0 && $requestMethod != 'get_config' && $requestMethod != 'authorize_user'){
	$request =  gFaultXmlRequest(new xmlrpcval(20,'int'),new xmlrpcval('security error (user may not have permission to access this feature)','string'));
	$response = $rpcServer->service($request);
} else {

	if($config['shorten_quote'] == 1){
		define('SHORTENQUOTE', 1);

	}
	if($config['disable_search'] == 1){
		if($requestMethod == 'search_topic' or $requestMethod == 'search_post'){
			   $request =  gFaultXmlRequest(new xmlrpcval(20,'int'),new xmlrpcval('security error (user may not have permission to access this feature)','string'));  
	   		   $response = $rpcServer->service($request);
		}

	}
	if($requestMethod == 'logout_user' || $requestMethod == 'get_config'){
		define('RPC_NOAUTH',true);
	}else{
		define('RPC_NOAUTH',false);
	}



	if(!$config['is_open'] && !RPC_NOAUTH){
		$request =  gFaultXmlRequest(new xmlrpcval(2,'int'),new xmlrpcval('server not available','string'));
		$response = $rpcServer->service($request);
			
	}else{
		$response = $rpcServer->service($request);
	}
}



?>
