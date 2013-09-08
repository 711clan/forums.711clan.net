<?php
/**
 * Photobucket site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'           => array('#http://photobucket\.com/mediadetail/\?media=%2Fplayer\.swf#i' => 1),
	'profile'           => 'flashvar',
	'width'             => 430,
	'height'            => 346,
	'titleregex'  		=> array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'         => 'swf',
	'loop'              => 0,
	'src'               => 'http://lads.myspace.com/videos/vplayer.swf',
	'flashvarformat'    => 'm=%s&v=2&type=video',
	'thumb'				=> ''
);

$regex_fields = array(
	'idregex'           => array('id',    'url'),
	'titleregex'        => array('title', 'content'),
);
	
$format_fields = array(
	'flashvarformat'    => array('flashvar', 'id')
);
krumo(get_defined_vars());
die('test');
?>