<?php
/**
 * Meegarotic site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'			=> array('#http://(?:\w{3}\.|)megarotic\.com/video/\?v=([\w\d]+)#i' => 1),
	'profile'			=> 'flash',
	'width'				=> 424,
	'height'			=> 337,
	'titleregex'		=> array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'			=> 'swf',
	'loop'				=> 0,
	'srcregex'			=> array('#\<embed\ssrc\=\"http\://video\.megarotic\.com/v/([a-z0-9]+)\"\s#i' => 1),
	
	'srcformat'			=> 'http://video.megarotic.com/v/%s',
	'flashvar'			=> '',
	'postfield'			=> 'ageverify=1&month=2&day=7&year=1973'
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'srcregex'   => array('src',   'content'),
	'titleregex' => array('title', 'content')
);

$format_fields = array(
	'srcformat'  => array('src',   'src')
);

?>