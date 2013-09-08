<?php
/**
 * Veoh site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'     => array('#http://(?:\w{3}\.|)veoh.com/videos/([\w\d]+)#i' => 1),
	'profile'     => 'flash',
	'width'       => 540,
	'height'      => 438,
	'titleregex'  => array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => 'http://www.veoh.com/videodetails2.swf?permalinkId=%s&id=anonymous&player=videodetailsembedded&videoAutoPlay=0',
	'flashvar'    => '',
	'thumb'       => ''
);

$regex_fields = array(
	'idregex'    => array('id',    'url'),
	'titleregex' => array('title', 'content')
);

$format_fields = array(
	'srcformat'  => array('src',   'id')
);

?>