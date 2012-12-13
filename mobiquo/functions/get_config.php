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
require_once(CWD1.'/conf/config.php');
chdir(CWD1);
chdir('../');
$phrasegroups = array();


$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

if(file_exists('./global.php'.SUFFIX)){
	require_once('./global.php'.SUFFIX);
} else {
	require_once('./global.php');
}

function get_config_func(){
	global $xmlrpcerruser;
	$mobiquo_config = new mobiquo_config();
	$config =  $mobiquo_config->get_config();
	$return_config =   array(
                       'is_open' => new xmlrpcval($config['is_open'],'boolean'),
                       'guest_okay' => new xmlrpcval($config['guest_okay'],'boolean'),
                       'forum_name' => new xmlrpcval(mobiquo_encode($config['forum_name']),'base64'),
                       'forum_description' => new xmlrpcval(mobiquo_encode($config['forum_description']),'base64'),
                       'logo_url' => new xmlrpcval($config['logo_url'],'string'),
	);
	foreach($config as $key => $value){
		if(!$return_config[$key]){
			$return_config[$key] = new xmlrpcval(mobiquo_encode($value),'string');
		}
	}
	$return =  new xmlrpcresp(
	new xmlrpcval( $return_config,"struct"));
	return $return;
}

?>
