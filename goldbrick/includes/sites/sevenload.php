<?php
/**
 * Sevenload site configuration
 * 
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'        => array('#http://\w{2,3}\.sevenload\.com/videos/([a-zA-Z0-9]+)#i' => 1),
	'profile'     => 'flash',
	'width'          => 425,
	'height'         => 350,
	'titleregex'     => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'      => 'swf',
	'loop'           => 0,
	'srcformat'      => 'http://en.sevenload.com/pl/%s/425x350/swf',
	'flashvar'       => '',
	'thumbregex'     => array("#stills\['2'\]\s+=\s?'(.+.jpg)#U" => 1),
);

$regex_fields = array(
	'idregex'        => array('id',    'url'),
	'titleregex'     => array('title', 'content'),
	'thumbregex'     => array('thumb', 'content')
);

$format_fields = array(
	'srcformat'      => array('src',      'id')

);

?>