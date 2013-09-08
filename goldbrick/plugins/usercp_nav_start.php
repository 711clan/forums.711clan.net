<?php
if ($vbulletin->options['gb_enabled'])
{
	eval('$template_hook[usercp_navbar_bottom] .= "' . fetch_template('gb_usercp_options') . '";');
}
?>