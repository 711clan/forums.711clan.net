<?php
/**
 * Stage6 site configuration
 * 
 * @version     	$Revision:108 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * @lastmodified	$Date:2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
$info = array(	
	'idregex'       => array('#http\://?(?:video\.)|stage6\.com/\S+/video/(\d+)\S+#i' => 1),
	'widthregex'    => array('#\<li\>\<span\>(\d+)\sx\s\d+\</span\>Resolution\</li\>#i' => 1),
	'heightregex'   => array('#\<li\>\<span\>\d+\sx\s(\d+)\</span\>Resolution\</li\>#i' => 1),
	'titleregex'    => array('#\<title\>Stage6 \&middot\; (.+)\s*\&nbsp\;-\&nbsp\;Video and Download\&nbsp\;\&middot\;\&nbsp\;.+\</title\>#i' => 1),
	'thumbformat'   => 'http://images.stage6.com/video_images/%st.jpg',
	'extension'     => 'divx',
	'profile'       => 'divx',
	'loop'          => 0,
	'flashvar'   	 => '',
	'flashvarextra'	=> '',
	'srcformat'     => 'http://video.stage6.com/%s/.divx',
	'increase_size' => '5'
);

$regex_fields = array(
	'idregex'       => array('id',     'url'),
	'titleregex'    => array('title',  'content'),
	'widthregex'    => array('width',  'content'),
	'heightregex'   => array('height', 'content')
);

$format_fields = array(
	'srcformat'     => array('src',    'id'),
	'thumbformat'   => array('thumb',  'id'),
);
?>