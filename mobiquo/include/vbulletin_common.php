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
function mobiquo_verify_id($idname, &$id, $alert = true, $selall = false, $options = 0)
{
	// verifies an id number and returns a correct one if it can be found
	// returns 0 if none found
	global $vbulletin, $threadcache, $vbphrase;

	if (empty($vbphrase["$idname"]))
	{
		$vbphrase["$idname"] = $idname;
	}
	$id = intval($id);
	switch ($idname)
	{
		case 'thread': $fault_code = 6; $fault_string = "invalid thread id is $id";break;
		case 'forum':  $fault_code =4; $fault_string = "invalid forum id is $id";break;
		case 'post':break;
		case 'user':  $fault_code =7;  $fault_string = "invalid user id is $id";break;

	}
	if (empty($id))
	{
		if ($alert)
		{
			return     return_fault(array($fault_code,$fault_string));
		}
		else
		{
			return 0;
		}
	}

	$selid = ($selall ? '*' : $idname . 'id');

	switch ($idname)
	{
		case 'thread':
		case 'forum':
		case 'post':
			$function = 'fetch_' . $idname . 'info';
			$tempcache = $function($id);
			if (!$tempcache AND $alert)
			{
				return  return_fault(array($fault_code,$fault_string));

			}

			return ($selall ? $tempcache : $tempcache[$idname . 'id']);

		case 'user':
			$tempcache = fetch_userinfo($id, $options);
			if (!$tempcache AND $alert)
			{
				return  return_fault(array($fault_code,$fault_string));

			}
			return ($selall ? $tempcache : $tempcache[$idname . 'id']);

		default:
			if (!$check = $vbulletin->db->query_first("SELECT $selid FROM " . TABLE_PREFIX . "$idname WHERE $idname" . "id = $id"))
			{
				if ($alert)
				{
					return                return_fault(array($fault_code,$fault_string));

				}

				return ($selall ? array() : 0);
			}
			else
			{
				return ($selall ? $check : $check["$selid"]);
			}
	}
}

function mobiquo_encode($str,$mode = ''){
	global $stylevar;
	$in_encoding = $stylevar['charset'];
	$target_encoding = 'UTF-8';
	$support_encoding = false;
	if($mode == 'to_local'){
		$target_encoding = $stylevar['charset'];
		$in_encoding = 'UTF-8';
		if(function_exists('mb_list_encodings') ){
			$encode_list = mb_list_encodings();
			foreach($encode_list as $encode){
				if(strtolower($encode) == strtolower($target_encoding)){
					$support_encoding  = true;
					break;
				}
			}
		}
	} else {
		$str =strip_tags($str);
		if(function_exists('mb_list_encodings') ){
			$encode_list = mb_list_encodings();
			foreach($encode_list as $encode){
				if(strtolower($encode) == strtolower($in_encoding)){
					$support_encoding  = true;
					break;
				}
			}
		}
		if(function_exists('htmlspecialchars_decode')){
			$str =htmlspecialchars_decode($str);
		} else {
			$str = unhtmlspecialchars($str);
		}
		//          $str =strip_tags($str);
	}
	if(strtolower($target_encoding) == strtolower($in_encoding) ){
		if($mode !='to_local'){
			$str =  unescape_htmlentitles($str);
		}
		$str = escape_latin_code($str,$target_encoding);
		return $str;
	}else{
		if ($mode == 'to_local'){
			if(function_exists('mb_convert_encoding')){

				$str =  @mb_convert_encoding($str,'HTML-ENTITIES','UTF-8');
			}

		}


		if (function_exists('mb_convert_encoding') AND $support_encoding == true AND $encoded_data = @mb_convert_encoding($str, $target_encoding, $in_encoding))
		{

			// if($mode != 'to_local'){
			$encoded_data =escape_latin_code($encoded_data ,$target_encoding);
			if($mode != 'to_local'){

				$encoded_data = unescape_htmlentitles($encoded_data);
			}
		}
		else if (function_exists('iconv') AND $encoded_data = @iconv($in_encoding, $target_encoding, $str))
		{
			// return $encoded_data;
			$encoded_data =escape_latin_code($encoded_data ,$target_encoding);
			if($mode != 'to_local'){
				$encoded_data = unescape_htmlentitles($encoded_data);
			}
		}
		else {
			$str = escape_latin_code($str ,$target_encoding);
			if($target_encoding == 'ISO-8859-1' && $mode == 'to_local'){
				$str = utf8_decode($str);
			}
			if($mode != 'to_local'){
				return unescape_htmlentitles($str);
			} else {
					
				return $str;
			}
		}
		return  $encoded_data;
	}
}
function mobiquo_get_user_icon($userid){
	global $vbulletin;
	$fetch_userinfo_options = (
	FETCH_USERINFO_AVATAR
	);
	$userinfo = mobiquo_verify_id('user',$userid, 1, 1, $fetch_userinfo_options);
	if(!is_array($userinfo)){
		$userinfo = array();
	}
	$icon_url = "";
	if($vbulletin->options['avatarenabled']){
		fetch_avatar_from_userinfo($userinfo,true,false);

		if($userinfo[avatarurl]){

			$icon_url = get_icon_real_url($userinfo[avatarurl]);
		} else {
			$icon_url = '';
		}
	}
	return $icon_url;
}

function get_vb_message($tempname){
	if (!function_exists('fetch_phrase'))
	{
		require_once(DIR . '/includes/functions_misc.php');
	}
	$phrase =fetch_phrase('redirect_friendspending','frontredirect', 'redirect_', true, false, $languageid, false);


	return $phrase;
}
?>