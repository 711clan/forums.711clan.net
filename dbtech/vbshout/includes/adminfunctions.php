<?php
// #############################################################################
/**
 * Constructs a bitfield row
 *
 * @param	string	The label text
 * @param	string	The name of the row for the form
 * @param	string	What bitfields we are using
 * @param	integer	The value of the setting
 */	
function print_bitfield_row($text, $name, $bitfield, $value)
{
	global $vbulletin, $vbphrase;

	require_once(DIR . '/includes/adminfunctions.php');
	require_once(DIR . '/includes/adminfunctions_options.php');
	
	// make sure all rows use the alt1 class
	$bgcounter--;

	$value = intval($value);
	$HTML = '';
	$bitfielddefs =& fetch_bitfield_definitions($bitfield);

	if ($bitfielddefs === NULL)
	{
		print_label_row($text, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$HTML .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
		$HTML .= "<div id=\"ctrl_{$name}\" class=\"smallfont\">\r\n";
		$HTML .= "<input type=\"hidden\" name=\"{$name}[0]\" value=\"0\" />\r\n";
		foreach ($bitfielddefs AS $key => $val)
		{
			$val = intval($val);
			$HTML .= "<table style=\"width:175px; float:left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"{$name}[$val]\" id=\"{$name}_$key\" value=\"$val\"" . (($value & $val) ? ' checked="checked"' : '') . iif($vbulletin->debug, " title=\"name=&quot;{$name}[$val]&quot; value=&quot;$val&quot;\"") . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"{$name}_$key\" class=\"smallfont\">" . fetch_phrase_from_key($key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$HTML .= "</div>\r\n";
		#$HTML .= "</fieldset>";
		print_label_row($text, $HTML, '', 'top', $name, 40);
	}		
}

// #############################################################################
/**
 * Prints a table row but not from a bitfield.
 *
 * @param	string	The label text
 * @param	string	The name of the row for the form
 * @param	string	What bitfields we are using
 * @param	integer	The value of the setting
 */	
function print_table_row($text, $name, $array, $value)
{
	// make sure all rows use the alt1 class
	$bgcounter--;

	$value = intval($value);
	$HTML = '';

	#$HTML .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
	$HTML .= "<div id=\"ctrl_{$name}\" class=\"smallfont\">\r\n";
	$HTML .= "<input type=\"hidden\" name=\"{$name}[0]\" value=\"0\" />\r\n";
	foreach ($array AS $key => $val)
	{
		$bit = intval($val['bitfield']);
		$HTML .= "<table style=\"width:175px; float:left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
		<td><input type=\"checkbox\" name=\"{$name}[$bit]\" id=\"{$name}_$key\" value=\"$bit\"" . (($value & (int)$bit) ? ' checked="checked"' : '') . iif($vbulletin->debug, " title=\"name=&quot;{$name}[$bit]&quot; value=&quot;$bit&quot;\"") . " /></td>
		<td width=\"100%\" style=\"padding-top:4px\"><label for=\"{$name}_$key\" class=\"smallfont\">" . $val['title'] . "</label></td>\r\n</tr></table>\r\n";
	}

	$HTML .= "</div>\r\n";
	#$HTML .= "</fieldset>";
	print_label_row($text, $HTML, '', 'top', $name, 40);	
}