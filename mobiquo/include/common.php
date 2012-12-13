<?php
defined('CWD1') or exit;
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
function gFaultXmlRequest($faultCode,$faultString){
	global $stylevar;

	$faultMethod = 'return_fault';
	$xml = new xmlrpcmsg($faultMethod);
	$xml->addParam($faultCode);
	$xml->addParam($faultString);
	$rxml = $xml->serialize($stylevar['charset']);
	return $rxml;
}


function encodeCharset($str,$out,$in=''){
	if(empty($in)){
		$in = 'UTF-8';
	}
	if(strtolower($out) == strtolower($in)){
		return $str;
	}else{
		return mb_convert_encoding($str,$out,$in);
	}
}
function get_userid_by_name($name){
	global $vbulletin;
	global $db;

	$username = htmlspecialchars_uni($name);
	$q = "
                SELECT posts, userid, username
                FROM " . TABLE_PREFIX . "user AS user
                WHERE username " .  "= '" . $db->escape_string($username) . "'" ;
	chdir(CWD1);
	chdir('../');
	if(file_exists( DIR . '/includes/functions_bigthree.php'.SUFFIX)){
		require_once( DIR . '/includes/functions_bigthree.php'.SUFFIX);
	} else {
		require_once( DIR . '/includes/functions_bigthree.php');
	}
	$coventry = fetch_coventry();

	$users = $db->query_read_slave($q);
	if ($db->num_rows($users))
	{
		$userids = array();
		while ($user = $db->fetch_array($users))
		{
			$postsum += $user['posts'];
			$display['users']["$user[userid]"] = $user['username'];
			$userids[] = (in_array($user['userid'], $coventry) AND !can_moderate()) ? -1 : $user['userid'];
		}

		$userids = implode(', ', $userids);

		if ($vbulletin->GPC['starteronly'])
		{
			if ($vbulletin->GPC['showposts'])
			{
				$post_query_logic[50] = "post.userid IN($userids)";
			}
			$thread_query_logic[] = "thread.postuserid IN($userids)";
		}
		// add the userids to the $post_query_logic search conditions
		else
		{
			if ($vbulletin->GPC['showposts'])
			{
				$post_query_logic[50] = "post.userid IN($userids)";
			}
			else
			{       // use the (threadid, userid) index of post to limit the join
				$post_join_query_logic = " AND post.userid IN($userids)";
			}
		}
	}else {

		return '-1';

	}
	return $userids;
}
function mobiquo_chop($string){
	global $stylevar,$vbulletin;
	$string = preg_replace('/<br \/\>/','',$string);
	$string = preg_replace('/(^\s+)|(\s+$)/','',$string);
	$string = preg_replace('/\n/','',$string);
	$string = preg_replace('/\r/','',$string);
	$string = strip_quotes($string);

	$string = htmlspecialchars_uni(fetch_censored_text(fetch_trimmed_title(
	strip_bbcode($string, false, true),
	200
	)));
	return $string;
}
function unescape_htmlentitles($str) {
	global $stylevar;
	preg_match_all("/(?:%u.{4})|.{4};|&#\d+;|.+|\\r|\\n/U",$str,$r);
	$ar = $r[0];

	foreach($ar as $k=>$v) {
		if(substr($v,0,2) == "&#") {
			$ar[$k] =@html_entity_decode($v,ENT_QUOTES, 'UTF-8');
		}
	}
	return join("",$ar);
}

function escape_latin_code($str,$target_encoding){
	preg_match_all("/&#\d+;|&\w+;|.+|\\r|\\n/U",$str,$r);
	$ar = $r[0];

	foreach($ar as $k=>$v) {
		if(substr($v,0,2) != "&#" && substr($v,0,1) == "&") {
			$ar[$k] =@html_entity_decode($v,ENT_QUOTES,$target_encoding);
		}
	}
	return join("",$ar);
}
function return_fault($params){
	$faultCode = $params[0];
	$faultString = $params[1];
	return new xmlrpcresp('',$faultCode,$faultString);
}

function post_content_clean($str){
	$bbcode_array = array('B','I','U','COLOR','SIZE','FONT','HIGHLIGHT','LEFT','RIGHT','CENTER','INDENT','EMAIL','THREAD','POST','LIST','CODE','PHP','HTML','NOPARSE','ATTACH','BUG','SCREENCAST');

	foreach($bbcode_array as $bbcode){
		if($bbcode == 'I' or $bbcode == 'U'){
			$str =preg_replace("/\[\/?$bbcode\]/siU",'',$str);
		} else{
			$str =preg_replace("/\[\/?$bbcode.*\]/siU",'',$str);
		}
	}

	$str = preg_replace ("/(\[url\])(.*\.((jpeg)|(jpg)|(png)|(gif)))(\[\/url\])/siU","[IMG]$2[/IMG]",$str);
	$str = preg_replace ("/(\[ame.*\])(.*)(\[\/ame\])/siU","[URL]$2[/URL]",$str);
	$str = preg_replace ("/(\[video.*\])(.*)(\[\/video\])/siU","[URL]$2[/URL]",$str);
	$str = preg_replace ("/(\[vedio.*\])(.*)(\[\/vedio\])/siU","[URL]$2[/URL]",$str);
	$str = preg_replace ("/(\[youtube.*\])(.*youtube.com.*)(\[\/youtube\])/siU","[URL]$2[/URL]",$str);
	$str = preg_replace ("/(\[youtube.*\])(.*)(\[\/youtube\])/siU","[URL]http://www.youtube.com/watch?v=$2[/URL]",$str);
	$str = preg_replace ("/(\[thumb.*\])(.*)(\[\/thumb\])/siU","[IMG]$2[/IMG]",$str);

	$str =clean_quote($str);
	$str = preg_replace('/(\[quote).*(\])/siU','$1$2',$str);
	$str = preg_replace('/(\[quote\])\s*/si','$1',$str);
	$str = preg_replace('/\s*(\[\/quote\])/siU','$1',$str);
	//      $str = preg_replace('/(\[quote=.*?);.*(\])/siU','$1$2',$str);

	$str = trim($str);
	$str = strip_tags($str);
	return $str;

}

function in_text_clean($str){
	$str = preg_replace('/\n/siU','<br>',$str);
	$str = preg_replace('/\r/siU','<br>',$str);
	return $str;
}
function clean_quote($text){
	$lowertext = strtolower($text);

	// find all [quote tags
	$start_pos = array();
	$curpos = 0;
	do
	{
		$pos = strpos($lowertext, '[quote', $curpos);
		if ($pos !== false AND ($lowertext[$pos + 6] == '=' OR $lowertext[$pos + 6] == ']'))
		{
			$start_pos["$pos"] = 'start';
		}

		$curpos = $pos + 6;
	}
	while ($pos !== false);

	if (sizeof($start_pos) == 0)
	{
		return $text;
	}

	// find all [/quote] tags
	$end_pos = array();
	$curpos = 0;
	do
	{
		$pos = strpos($lowertext, '[/quote]', $curpos);
		if ($pos !== false)
		{
			$end_pos["$pos"] = 'end';
			$curpos = $pos + 8;
		}
	}
	while ($pos !== false);

	if (sizeof($end_pos) == 0)
	{
		return $text;
	}

	// merge them together and sort based on position in string
	$pos_list = $start_pos + $end_pos;
	ksort($pos_list);

	do
	{
		// build a stack that represents when a quote tag is opened
		// and add non-quote text to the new string
		$stack = array();
		$newtext = '';
		$substr_pos = 0;
		foreach ($pos_list AS $pos => $type)
		{

			$stacksize = sizeof($stack);
			if ($type == 'start')
			{
				//
				// empty stack, so add from the last close tag or the beginning of the string
					
				if ($stacksize == 0 or $stacksize ==1)
				{
					$newtext .= substr($text, $substr_pos, $pos - $substr_pos);
					$substr_pos = $pos ;


				}
					
				array_push($stack, $pos);
			}
			else
			{
				// pop off the latest opened tag
				if ($stacksize >1)
				{
					$substr_pos = $pos + 8;
				}
				array_pop($stack);
			}
		}

		$newtext .= substr($text, $substr_pos);


		// check to see if there's a stack remaining, remove those points
		// as key points, and repeat. Allows emulation of a non-greedy-type
		// recursion.
		if ($stack)
		{
			foreach ($stack AS $pos)
			{
				unset($pos_list["$pos"]);
			}
		}
	}
	while ($stack);
	return $newtext;
}

function mobiquo_iso8601_encode($timet, $timezone,$utc=0)
{
	$timezone = preg_replace('/\+/','',$timezone);
	if(!$utc)
	{
		$t=strftime("%Y%m%dT%H:%M:%S", $timet);
		if($timezone >= 0){
			$timezone = sprintf("%02d",$timezone);
			$timezone = '+'.$timezone;
		}
		else{
			$timezone = $timezone * (-1);
			$timezone = sprintf("%02d",$timezone);
			$timezone = '-'.$timezone;
		}
		$t=$t.$timezone.':00';
	}
	else
	{
		if(function_exists('gmstrftime'))
		{
			// gmstrftime doesn't exist in some versions
			// of PHP
			$t=gmstrftime("%Y%m%dT%H:%M:%S", $timet);
		}
		else
		{
			$t=strftime("%Y%m%dT%H:%M:%S", $timet-date('Z'));
		}
	}
	return $t;
}

function get_suffix(){
	chdir(CWD1);
	chdir('../');
	$dir = '.';
	$suffix;
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(preg_match_all("/global\.php(.*)/",$file,$r)){
					$suffix =  $r[1][0];
				}
			}
			closedir($dh);
		}
	}
	chdir(CWD1);
	if(substr($suffix,0,1) == "."){
		return '';
	}
	return $suffix;
}


function get_icon_real_url($iconurl){
	global $vbulletin;
	$real_url = $iconurl;
	if( preg_match('/^http/',$iconurl)){
		$real_url = unhtmlspecialchars($iconurl);
	}
	else{
		if(preg_match('/^\//',$iconurl)){
			$base_url = preg_replace("/http:\/\//siU",'',$vbulletin->options[homeurl]);
			$path = explode('/',$base_url);
			$host = $path[0];
			unset($path);
			$base_host = "http://".$host;
			$real_url=$base_host.unhtmlspecialchars($iconurl);
		} else {
			$real_url=$vbulletin->options[bburl].'/'.unhtmlspecialchars($iconurl);
		}
	}
	return $real_url;
}
?>
