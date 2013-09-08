<?php
/**
 * Checks a submitted post for cachable links, caches them, and then wraps them
 * in [media] tags.
 * 
 * @active      	true
 * @execution   	1
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * $lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */

$gb_options = array(
		'width'		=> $vbulletin->options['gb_width'],
		'height'	=> $vbulletin->options['gb_height'],
		'autoplay'	=> $vbulletin->options['gb_autoplay'],
		'loop'		=> $vbulletin->options['gb_loop']
);

eval('$gb_oform = "' . fetch_template('gb_options_form') . '";');
?>