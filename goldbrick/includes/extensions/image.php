<?php
/**
 * Image extensions configuration
 * .jpg, png, gif ect
 * 
 * @version			$Revision: 110 $
 * @modifiedby		$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 15:17:53 -0700 (Mon, 29 Oct 2007) $
 */


switch ($identifier[0]) {
	case 'gif':
		
		$mime = 'image/gif';
		
		break;
	
	case 'jpg':
		
		$mime = 'image/pjpeg';
		
		break;
	
	case 'jpeg':
		
		$mime ='image/pjpeg';
		break;
		
	case 'bmp':
		
		$mime = 'image/bmp';
	
		break;
		
	case 'png':
		
		$mime = 'image/x-png';
	
		break;
	
	default:
		
		$mime = 'error';
	
		break;
}

$info = array(	
	'profile'		=> 'image',
	'width'			=> '',
	'height'		=> '',
	'mime'			=> $mime,
	'extension'		=> $identifier[0],
	'src'			=> $url,
	'id'			=> ''
);
?>