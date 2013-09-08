<?php
/**
 * Dailymotion site configuration
 * 
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'		=> array('#http\://www\.dailymotion\.com/(.+)#i' => 1),
	'profile'		=> 'flash',
	'width'			=> 400,
	'height'		=> 300,
	'srcregex'		=> array('#dailymotion\.com/swf/([a-zA-Z0-9]+)\&quot#i' => 1),
	'titleregex'	=> array('#\<title\>(.+)\<\/title\>#si' => 1),
	'thumb'			=>'',
	'extension'		=> 'swf',
	'loop'			=> 0,
	'flashvar'		=> '',
	'srcformat'		=> 'http://www.dailymotion.com/swf/%s'
);

$regex_fields = array(
	'srcregex'   => array('src',   'content'),
	'idregex'    => array('id',    'url'),
	'titleregex' => array('title', 'content'),
	'thumbregex' => array('thumb', 'content')
);

$format_fields = array(
	'srcformat'   => array('src',   'src'),
);

?>