<?php
/**
 * Vimeo site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http://(?:\w{3}.|)vimeo.com/(\d+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 400,
	'height'      => 300,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'thumbregex'  => array('# \<link rel="image_src" href="(.+\.jpg)" type="" /\>#i' => 1),
	'thumbformat' => '%s',
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => 'http://www.vimeo.com/moogaloop.swf?clip_id=%s&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color='
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