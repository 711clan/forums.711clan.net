<?php
/**
 * Adds Goldbrick header to headinclude for css and javascript
 * 
 * @active      	true
 * @execution   	1
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * $lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */

if (
		
		in_array(THIS_SCRIPT,
				array(
					'showthread',
					'showpost',
					'goldbrick',
					'newreply',
					'member',
					'blog',
					'adv_index'
				)
		)
	)
{
	eval('$headinclude .= "' . fetch_template('gb_header') . '";');
}
?>