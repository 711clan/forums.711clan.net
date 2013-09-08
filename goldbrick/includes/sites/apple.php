<?php
/**
 * Apple Trailers site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(
	'idregex'		=> array('#http://(?:\w{3}\.|)apple\.com/trailers/([\w\d-_/]+)#i' => 1),
	'srcregex'		=> array(
		'#http://movies\.apple\.com/movies/(.+_h.640)\.mov#i' =>  1,
		'#http://movies\.apple\.com/movies/(.+_h\.480)\.mov#i' => 1,
		'#http://movies\.apple\.com/movies/(.+-h\.ref)\.mov#i' => 1
	),
	
	'profile'		=> 'quick_time',
	'srcformat'		=> 'http://movies.apple.com/movies/%s.mov',
	
	'widthregex'	=> array(
		'#http://(?:images|movies)\.apple\.com/movies/.+\.mov\',\s\{\s+width:\s(\d{3})\,#' => 1,
		"#(http://(?:images|movies)\.apple\.com/movies/.+\.mov)','(\d{3})','(\d{3})'#" => 2
	),
	
	'heightregex'	=> array(
		'#http://(?:images|movies)\.apple\.com/movies/.+\.mov\',\s\{\s+width\:\s\d{3}\,\s+height\:\s(\d{3})#' => 1,
		'#(http://(?:images|movies)\.apple\.com/movies/.+\.mov)\',\'(\d{3})\'\,\'(\d{3})\'#' => 3
	),
	
	
	'widthformat'	=> '%s',
	'heightformat'	=> '%s',
	'titleregex'	=> array('#\<title\>(.+)\<\/title\>#si' => 1),
	'extension'		=> 'mov',
	'increase_size'	=> '5',
	'loop'			=> 0
);

$regex_fields = array(
	'idregex'		=> array('id',    'url'),
	'srcregex'		=> array('src',   'content'),
	'titleregex'	=> array('title', 'content'),
	'widthregex'	=> array('width', 'content'),
	'heightregex'	=> array('height', 'content')
);

$format_fields = array(
	'srcformat'		=> array('src',   'src'),
	'widthformat'	=> array('width',	'width'),
	'heightformat'	=> array('height',	'height')
);
?>