<?php
/**
 * Windows Media extensions configuration
 * .wmv
 * 
 * @version			$Revision: 110 $
 * @modifiedby		$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 15:17:53 -0700 (Mon, 29 Oct 2007) $
 */
switch ($identifier[0]) {
	case 'wma':
		
		$mime = 'application/x-mplayer2';
		
		break;
	
	case 'wav':
		
		$mime = 'application/x-mplayer2';
		
		break;
	
	case 'ogg':
		
		$mime ='application/x-mplayer2';
		
		break;
		
	case 'ape':
		
		$mime = 'application/x-mplayer2';
	
		break;
		
	case 'mid':
	
		$mime = 'application/x-mplayer2';
		
		break;
		
	case 'midi':
	
		$mime = 'application/x-mplayer2';
		
		break;
		
	case 'asf':
	
		$mime = 'application/x-mplayer2';
		
		break;
		
	case 'asx':
	
		$mime = 'application/x-mplayer2';
		
		break;
		
	case 'wm':
	
		$mime = 'application/x-mplayer2';
		
		break;
		
	case 'wmv':
	
		$mime = 'application/x-mplayer2';
		
		break;
		
	default:
		
		$mime = 'error';
	
		break;
}


$info = array(	
	'profile'		=> 'windows_media',
	'width'			=> $gb_options['width'],
	'height'		=> $gb_options['height'],
	'extension'		=> $identifier[0],
	'loop'			=> $gb_options['loop'],
	'mime'			=> $mime,
	'src'			=> '',
	'id'			=> '',
	'thumb'			=> '',
	'title'			=> ''
);
?>