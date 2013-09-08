<?php
/**
 * Flash extensions configuration
 * .flv, mp3, swf ect
 * 
 * @version			$Revision: 110 $
 * @modifiedby		$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 15:17:53 -0700 (Mon, 29 Oct 2007) $
 */

switch ($identifier[0]) {
	case 'swf':
	
		$mime = 'application/x-shockwave-flash';
	
		break;

	case 'flv':
	
		$mime = 'application/x-shockwave-flash';
	
		break;

	case 'mp3':
	
		$mime ='audio/mpeg';
		break;

	default:
	
		$mime = 'error';
		break;
}

$info = array(	
	'profile'		=> 'flashext',
	'width'			=> $gb_options['width'],
	'height'		=> $gb_options['height'],
	'extension'		=> $identifier[0],
	'loop'			=> $gb_options['loop'],
	'src'			=> $vbulletin->options['bburl'] . '/players/flvplayer.swf',
	'mime'			=> $mime,
	'flashvar'		=> '',
	'flashvarextra'	=> '',
	'id'			=> '',
	'thumb'			=> '',
	'title'			=> $gb_options['title']
);

if ($identifier[0] == 'mp3')
{
	$info['src']		= $vbulletin->options['bburl'] . '/players/mp3player.swf';
	$info['profile']	= 'mp3';
	$info['width']		= 400;
	$info['height']		= 50;
}
?>