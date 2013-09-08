<?php
/**
 * Metacafe site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#(http://\S+\.xtube\.com/.+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 425,
	'height'      => 350,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcregex'		=> array('#\<embed\ssrc="(http://.+)"\s\w+="\w+"\s\w+="(?:[\w\d\#]+)"\s\w+="499"#i' => 1),
	'srcformat'   => '%s',
	'flashvar'    => '',
	'cookie'		=> 1,
	'thumb'       => ''
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'srcregex'   => array('src',   'content'),
	'titleregex' => array('title', 'content')
);

$format_fields = array(
	'srcformat'  => array('src',   'src')
);

?>