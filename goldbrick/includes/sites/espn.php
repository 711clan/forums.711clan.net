<?php
/**
 * Espn site configuration
 * 
 * 
 */
$info = array(
	'idregex'     => array('#http\://sports\.espn\.go\.com/broadband/video/videopage\?videoId=(\d+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 440,
	'height'      => 361,
	'titleregex'  => array('#\<input type="hidden" name="subject" value="(.+)" id="subject" /\>#i' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => 'http://sports.espn.go.com/broadband/player.swf?mediaId=%s',
	'thumb'       => ''
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'srcregex'   => array('src',   'id'),
	'titleregex' => array('title', 'content')
);
?>