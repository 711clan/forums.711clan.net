<?php
/**
 * Spikedhumor Video site configuration
 * 
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http\://www\.spikedhumor\.com/articles/(\d+)/#i' => 1),
	
	'profile'     => 'flash',
	'width'       => 464,
	'height'      => 392,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'flashvar'    => '',
	'srcformat'   => 'http://www.spikedhumor.com/player/vcplayer.swf?file=http://www.spikedhumor.com/videocodes/%s/data.xml&auto_play=false'
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'titleregex' => array('title', 'content')
);

$format_fields = array(
	'srcformat'   => array('src',   'id')
);

?>