<?php
/**
 * CollegeHumor site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http\://www\.collegehumor\.com/video\:(\d+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 400,
	'height'      => 300,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'thumbregex'  => array('#\<link\s*rel\="image_src"\s*href\="([\.\w:\d/]*)#i' => 1),
	'thumbformat' => '%s',
	'extension'   => 'swf',
	'loop'        => 0,
	'flashvar'    => '',
	'srcformat'   => 'http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=%s'
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'titleregex' => array('title', 'content'),
	'thumbregex' => array('thumb', 'content')
);

$format_fields = array(
	'srcformat'   => array('src',   'id'),
	'thumbformat' => array('thumb', 'thumb')
);

?>