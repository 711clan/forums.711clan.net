<?php
/**
 * YouTube site configuration
 * 
 * @version     	$Revision: 110 $
 * @modifiedby  	$LastChangedBy: digitallyepic_siradrian $
 * @lastmodified	$Date: 2007-10-29 15:17:53 -0700 (Mon, 29 Oct 2007) $
 */
$info = array(	
	'idregex'     => array(
		'#http\://(?:\w{2,3}\.|)youtube\.com/watch\?v=([-_a-z0-9]{11})#i' => 1,
		'#http\://(?:\w{2,3}\.|)youtube\.com/watch\?search=\&mode=related\&v=([-_a-z0-9]{11})#i' => 1
	),
	'profile'     => 'flash',
	'width'       => 425,
	'height'      => 350,
	'titleregex'  => array('#\<title\>YouTube - (.+)\<\/title\>#si' => 1),
	'extension'   => 'swf',
	'loop'        => 0,
	'srcformat'   => 'http://www.youtube.com/v/%s',
	'thumbformat' => 'http://img.youtube.com/vi/%s/default.jpg',
	'header'	=> 'Set-Cookie: 18plus=1; expires=Wed, 25-Oct-2017 02:49:15 GMT; path=/; domain=.megarotic.com',
	'activeregex' => array('#\<div class\="errorBox"\>#i' => false)
);
?>