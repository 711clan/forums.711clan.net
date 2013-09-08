<?php
/**
 * Break Video site configuration
 * 
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array(
		'#http://(?:www\.|)break\.com/index/([\w\d-]+)\.html#i' => 1,
		'#http\://my\.break\.com/content/view\.aspx\?contentid=(\d+)#i' => 1
	),
	'profile'     => 'flash',
	'width'       => 464,
	'height'      => 392,
	'srcregex'    => array('#embed\.break\.com/([a-zA-Z0-9]+)#i' => 1),
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'thumbregex'  => array('#\<link rel="videothumbnail" href="(.+)" type="image/jpeg"#si' => 1),
	'thumbformat' => '%s',
	'extension'   => 'swf',
	'loop'        => 0,
	'flashvar'    => '',
	'flashvarextra'	=> '',
	'srcformat'   => 'http://embed.break.com/%s'
);

$regex_fields = array(
	'srcregex'    => array('src',   'content'),
	'idregex'     => array('id',    'url'),
	'titleregex'  => array('title', 'content'),
	'thumbregex'  => array('thumb', 'content')
);

$format_fields = array(
	'srcformat'   => array('src',   'src'),
	'thumbformat' => array('thumb', 'thumb')
);
