<?php

//	+-----------------------------------------------------------------------+
//	|	Name		AnyMedia BBCode											|
//	|	Package		vBulletin 3.5.4											|
//	|	Version		3.0.4													|
//	|	Author		Crist Chsu												|
//	|	E-Mail		Crist@vBulletin-Chinese.com								|
//	|	Blog		http://www.QuChao.com									|
//	|	Date		2006-6-7												|
//	|	Link		http://www.vbulletin.org/forum/showthread.php?t=106239	|
//	+-----------------------------------------------------------------------+

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'anymedia');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// #############################################################################
$vbulletin->input->clean_array_gpc('p', array(
	'action' => TYPE_STR,
	'vid' => TYPE_STR
));

if (empty($vbulletin->GPC['action'])) {
	exit;
}

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// fetch the real url to the youtube action
if ($vbulletin->GPC['action'] == 'youtube'){
	if(!$vbulletin->GPC['vid']) {
		exit;
	} else {
		$content = fetchContent('http://www.youtube.com/watch?v=' . $vbulletin->GPC['vid']);
		if (preg_match('/player2\.swf\?video_id=([^(\")]*)/', $content, $match)) {
			$content = fetchContent('http://www.youtube.com/get_video?video_id=' . $match[1], true);
			if (preg_match('/Location: ([^(\n)]*)/', $content, $match)) {
				echo 'url=' . $match[1];
			} else {
				exit;
			}
		} else {
			exit;
		}
	}
} else {
	exit;
}

//	{{{	fetchContent()

/**
 * Fetch the remote content.
 * @param	string	url of the page
 * @param	string	get the http header?
 * @return	string	HTML of the page
 */
function fetchContent($url, $getHeader = false)
{
	$content = "";
	if (ini_get('allow_url_fopen') && !$getHeader) {
		//ByFile
		$handle = @fopen($url,"r");
		if(!$handle){
			return false;
		}
		while($buffer = fgets($handle, 4096)) {
		  $content .= $buffer;
		}
		fclose($handle);
		return $content;
	} elseif (function_exists('curl_init')) {
		//ByCurl
		$handle = curl_init();
		curl_setopt ($handle, CURLOPT_URL, $url);
		curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);
		if ($getHeader) {
			curl_setopt ($handle, CURLOPT_HEADER, 1);
			curl_setopt ($handle, CURLOPT_NOBODY, 1);
		}
		$content = curl_exec($handle);
		curl_close($handle);
		return $content;
	} elseif (function_exists('fsockopen')) {
		//BySocket
		if (!($pos = strpos($url, '://'))) {
			return false;
		}
		$host = substr($url, $pos+3, strpos($url, '/', $pos+3) - $pos - 3);
		$uri = substr($url, strpos($url, '/', $pos+3));
		$request = "GET " . $uri . " HTTP/1.0\r\n"
				   ."Host: " . $host . "\r\n"
				   ."Accept: */*\r\n"
				   ."User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n"
				   ."\r\n";
		$handle = @fsockopen($host, 80, $errno, $errstr, 30);
		if (!$handle) {
			return false;
		}
		@fputs($handle, $request);
		while (!feof($handle)){
			$content .= fgets($handle, 4096);
		}
		fclose($handle);
		$separator = strpos($content, "\r\n\r\n");
		if($getHeader) {
			if($separator === false) {
				return false;
			} else {
				return substr($content, 0, $separator);
			}
		} else {
			if($separator === false) {
				return $content;
			} else {
				return substr($content, $separator + 4);
			}
		}
	} else {
		return false;
	}
}

//	}}}
?>