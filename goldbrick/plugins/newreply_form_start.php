<?php
/**
 * Auto template edit for Media Manager
 * 
 * @active      	true
 * @execution   	1
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * $lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */

$find = '$attachmentoption';
	
$replace = '$attachmentoption
			$gb_oform';
	
$vbulletin->templatecache['newreply'] = str_replace($find, $replace, $vbulletin->templatecache['newreply']);
?>