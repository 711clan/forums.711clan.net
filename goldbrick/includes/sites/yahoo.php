<?php
/**
 * Yahoo site configuration
 * 
 * @version			$Revision:108 $
 * @modifiedby		$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'			=> array('#http\://video\.yahoo\.com/video/play\?vid\=(\d+)#i' => 1),
	'flashvarregex'		=> array("#\<embed src='.+'\s*flashvars='(.+)\'\s*type=.+\</embed\>#si" => 1),
	'flashvarformat'	=>	'%s',
	'profile'			=> 'flashvar',
	'src'				=> 'http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf',
	'width'				=> 432,
	'height'			=> 351,
	'titleregex'		=> array('#\<title\>(.+)\s*- Yahoo! Video\<\/title\>#si' => 1),
	'extension'			=> 'swf',
	'thumbformat'		=> 'http://thmg01.video.search.yahoo.com/image/%s_01',
	'loop'				=> 0
);

$regex_fields = array(
	'idregex'			=> array('id',	   'url'),
	'flashvarregex'		=> array('flashvar',   'content'),
	'titleregex'		=> array('title', 'content')
);

$format_fields = array(
	'flashvarformat'	=> array('flashvar',   'flashvar'),
	'thumbformat'		=> array('thumb', 'id')
);
?>