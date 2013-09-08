<?php
/**
 * Google site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'        => array('#http://video\.google\.(?:com|co\.uk|de|ca|es)/videoplay\?docid=([^(\&|$)]*)#i' => 1),
	'profile'     => 'flash',
	'width'          => 400,
	'height'         => 326,
	'titleregex'     => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'      => 'swf',
	'loop'           => 0,
	'srcformat'      => 'http://video.google.com/googleplayer.swf?docid=%s',
	'flashvar'       => '',
	'thumbregex'     => array('#options\.image = "(.+)";#U' => 1)
);

$regex_fields = array(
	'idregex'        => array('id',    'url'),
	'titleregex'     => array('title', 'content'),
	'thumbregex'     => array('thumb', 'content')
);

$format_fields = array(
	'srcformat'      => array('src',      'id'),
	'flashvarformat' => array('flashvar', 'id')
);

function goldbrick_hook_google_complete(&$info)
{
	$info['thumb'] = str_replace('\u003d', '=', $info['thumb']);
}

function goldbrick_hook_google_opened(&$info)
{
	// TODO check frame src for other video sites
}

?>