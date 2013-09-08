<?php
/**
 * Pornotube site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'			=> array(
		'#http://(?:\w{3}\.|)pornotube\.com/media\.php\?m=(\d+)#i' => 1,
		'#http://(?:\w{3}\.|)pornotube\.com/channels\.php\?channelid=\d+\&m=(\d+)#i' => 1
	),
	
	'profile'			=> 'flash',
	'width'				=> 480,
	'height'			=> 400,
	'titleregex'		=> array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'			=> 'swf',
	'loop'				=> 0,
	'srcregex'			=> array('#Embedded\sPlayer\</div\>.+embed\ssrc=\&quot\;(http.+)\&quot\;\sloop\=#si' => 1),
	
	'srcformat'			=> '%s',
	'flashvar'			=> '',
	'postfield'			=> 'bMonth=04&bDay=10&bYear=1971&submit=CONTINUE+%C2%BB&verifyAge=true'
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