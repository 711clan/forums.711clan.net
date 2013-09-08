<?php
/**
 * Filecabi site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http\://(?:\w{2,3}\.|)filecabi\.net/video/([a-zA-Z0-9_-]+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 460,
	'height'      => 360,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => 'http://www.filecabi.net/movieplayer.swf?video=http%%3A%%2F%%2Fwww.filecabi.net%%2Fplayvideo.php%%3Fcid%%3D%s',
	'flashvar'    => '',
	'thumbformat' => 'http://www.filecabi.net/p/%s.jpg'
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'srcregex'   => array('src',   'id'),
	'titleregex' => array('title', 'content')
);

$format_fields = array(
	'srcformat'  => array('src',   'id'),
	'thumbformat'  => array('thumb',   'id')
);

?>