<?php
/**
 * Jumpcut site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http://(?:\w{3}\.|)jumpcut\.com/view\?id=([\w\d]+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 400,
	'height'      => 300,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => 'http://www.jumpcut.com/media/flash/jump.swf?id=%s&asset_type=movie&asset_id=%s&eb=1');

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'titleregex' => array('title', 'content')
);

$format_fields = array(
	'srcformat'   => array('src',   'id')
);

?>