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
require_once(CWD1.'/include/functions_logout_user.php');
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
if(file_exists(DIR. '/includes/functions_login.php'.SUFFIX)){
	require_once(DIR . '/includes/functions_login.php'.SUFFIX);
} else {
	require_once(DIR . '/includes/functions_login.php');
}

function login_func($params) {
	global $xmlrpcerruser;
	$decode_params = php_xmlrpc_decode($params);
	$username = mobiquo_encode($decode_params[0],'to_local');
	$password = mobiquo_encode($decode_params[1],'to_local');
	global $vbulletin;
	global $config;
	chdir(CWD1);
	chdir('../');
	if ($username && $password)
	{

		$return  = array();
		$vbulletin->GPC['username'] =$username;
		if(strlen($password) == 32){
			$vbulletin->GPC['md5password'] = $password;
			$vbulletin->GPC['md5password_utf'] = $password;
		} else {
			$vbulletin->GPC['password'] = $password;
		}


		$strikes = mobiquo_verify_strike_status($vbulletin->GPC['username']);
		if ($vbulletin->GPC['username'] == '')
		{
			$return = array( 7,'invalid user name/id.');
			return return_fault($return);
		}

		if(!$strikes){

			$return_text= "Wrong username or password. You have used up your failed login quota! Please wait 15 minutes before trying again.";
			$return =new xmlrpcval( array('result' => new xmlrpcval(false,"boolean"),
											  'result_text' =>  new xmlrpcval(mobiquo_encode($return_text),'base64')
			),"struct");
			return new xmlrpcresp($return);
		}
		// make sure our user info stays as whoever we were (for example, we might be logged in via cookies already)
		$original_userinfo = $vbulletin->userinfo;

		if (!verify_authentication($vbulletin->GPC['username'], $vbulletin->GPC['password'], $vbulletin->GPC['md5password'], $vbulletin->GPC['md5password_utf'], $vbulletin->GPC['cookieuser'], true))
		{
			exec_strike_user($vbulletin->userinfo['username']);
			if ($vbulletin->options['usestrikesystem'])
			{
				$return_text= sprintf("You have entered an invalid username or password. Please enter the correct details and try again. Don't forget that the password is case sensitive.
You have used %3s out of 5 login attempts. After all 5 have been used, you will be unable to login for 15 minutes",$strikes['strikes'] + 1);
			}
			else
			{
				$return_text= "You have entered an invalid username or password. Please press the back button, enter the correct details and try again. Don't forget that the password is case sensitive.";
			}

			$return =new xmlrpcval( array('result' => new xmlrpcval(false,"boolean"),
											  'result_text' =>  new xmlrpcval(mobiquo_encode($return_text),'base64')
			),"struct");
			return new xmlrpcresp($return);
		} else {

			exec_unstrike_user($vbulletin->GPC['username']);


			$member_groups = preg_split("/,/",$vbulletin->userinfo['membergroupids']);
			$group_block = false;

			if(trim($config['allowed_usergroup']) != ""){
				$group_block = true;
				$support_group = explode(",", $config['allowed_usergroup']);

				foreach($support_group as $support_group_id){

					if($vbulletin->userinfo['usergroupid'] == $support_group_id || in_array($support_group_id,$member_groups)) {
						$group_block = false;
					}

				}
			}

			if($group_block){
				$return_text = 'The usergroup you belong to does not have permission to login. Please contact your administrator. ';
				$return = new xmlrpcresp(
				new xmlrpcval(
				array(
      	                  'result' => new xmlrpcval(false,"boolean"),
						  'result_text' =>  new xmlrpcval(mobiquo_encode($return_text),'base64')
				),
                              "struct"
                              )
                              );

			} else {
				process_new_login($vbulletin->GPC['logintype'], $vbulletin->GPC['cookieuser'], $vbulletin->GPC['cssprefs']);
				$vbulletin->session->save();

				$return = new xmlrpcresp(
				new xmlrpcval(
				array(
	      	                  'result' => new xmlrpcval(true,"boolean"),
							  'result_text' =>  new xmlrpcval('','base64')
				),
                         "struct"));
			}
		}
	}
	else
	{  $return =new xmlrpcval( array('result' => new xmlrpcval(false,"boolean"),
										 'result_text' =>  new xmlrpcval('','base64')
	),"struct");

	}

	return $return;

}


?>
