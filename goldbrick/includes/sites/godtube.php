<?php
/**
 * Break Video site configuration
 * 
 * @version			$Revision: 108 $
 * @modifiedby		$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'			=> array(
		'#http\://(?:|www\.)godtube\.com/view_video\.php\?viewkey\=([a-z0-9]+)#i' => 1
	),
	'profile'			=> 'flashvarextra',
	'width'				=> 330,
	'height'			=> 270,
	'titleregex'		=> array('#\<title\>(.+)\<\/title\>#si' => 1),
	'thumbregex'		=> array('#\<img\s\w+\="\d+"\ssrc="(http://thumb-\d\.godtube\.com/e1/thumb/(?:[a-z0-9_]+)\.jpg)"\s\w+="\d+"\s\/\>\s+\<p\>now\s+playing\<\/p\>#si' => 1),
	
	'thumbformat'		=> '%s',
	'extension'			=> 'swf',
	'loop'				=> 0,
	'src'				=> 'http://godtube.com/flvplayer.swf',
	'flashvarformat'	=> '%s',
	'flashvarextra'		=> 'viewkey'
);

$regex_fields = array(
	'idregex'	  => array('id',	'url'),
	'titleregex'  => array('title', 'content'),
	'thumbregex'  => array('thumb', 'content')
);

$format_fields = array(
	'flashvarformat'	=> array('flashvar', 'id'),
	'thumbformat' => array('thumb', 'thumb')
);
?>
