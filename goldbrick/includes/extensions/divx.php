<?php
/**
 * Divx Media extensions configuration
 * .wmv
 * 
 * @version			$Revision: 110 $
 * @modifiedby		$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 15:17:53 -0700 (Mon, 29 Oct 2007) $
 */
switch ($identifier[0]) {
	case 'divx':
		
		$mime = 'video/divx';
		
		break;
	
	case 'avi':
		
		$mime = 'video/divx';
		
		break;
	
	default:
		
		$mime = 'error';
	
		break;
}


$info = array(	
	'profile'		=> 'divx',
	'width'			=> $gb_options['width'],
	'height'		=> $gb_options['height'],
	'extension'		=> $identifier[0],
	'loop'			=> $gb_options['loop'],
	'mime'			=> $mime,
	'src'			=> $url,
	'id'			=> '',
	'thumb'			=> '',
	'title'			=> '',
	'increase_size' => '5'
);
?>