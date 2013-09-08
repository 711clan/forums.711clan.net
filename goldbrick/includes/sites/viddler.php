<?php
/**
 * viddler Video site configuration
 * 
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http://(?:\w{3}\.|)viddler\.com/explore/([\w\d-_/]+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 464,
	'height'      => 392,
	'srcregex'    => array('#\<link\srel="video_src"\shref="(http.+)/"/\>#i' => 1),
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => '%s'
);

$regex_fields = array(
	'srcregex'    => array('src',   'content'),
	'idregex'     => array('id',    'url'),
	'titleregex'  => array('title', 'content')
);

$format_fields = array(
	'srcformat'   => array('src',   'src')
);
