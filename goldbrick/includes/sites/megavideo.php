<?php
/**
 * MegaVideo site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'    => array('#http\://(?:www\.|)megavideo\.com/\?v=([\d\w]*)#i' => 1),
	'srcregex'   => array('#\<object\s*width=.*\<param\s*name="movie"\s*value=\"(.+)"\>\<\/param\>\<param\s*name#si' => 1),
	'profile'     => 'flash',
	'srcformat'  => '%s',
	'width'      => 432,
	'height'     => 351,
	'titleregex' => array('#fo\.addVariable\("videoname","(.+)"\)#i' => 1),	
	'extension'  => 'swf',
	'loop'       => 0
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