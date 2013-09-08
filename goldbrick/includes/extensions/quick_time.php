<?php
/**
 * Quicktime extensions configuration
 * .mov mp4 etc
 * 
 * @version			$Revision: 110 $
 * @modifiedby		$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 15:17:53 -0700 (Mon, 29 Oct 2007) $
 */
switch ($identifier[0]) {
	case 'mov':
		
		$mime = 'video/quicktime';
		
		break;
	
	case 'mpeg':
		
		$mime = 'video/x-mpeg';
		
		break;
	
	case 'mpg':
		
		$mime ='video/x-mpeg';
		break;
		
	case 'mp4':
		
		$mime = 'video/mp4';
	
		break;
		
	default:
		
		$mime = 'error';
	
		break;
}


$info = array(	
	'profile'		=> 'quick_time',
	'width'			=> $gb_options['width'],
	'height'		=> $gb_options['height'],
	'mime'			=> $mime,
	'extension'		=> $identifier[0],
	'loop'			=> $gb_options['loop'],
	'src'			=> $identifier['url'],
	'id'			=> '',
	'thumb'			=> '',
	'title'			=> $gb_options['title'],
	'increase_size' => '5'
);
?>