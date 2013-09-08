<?php
foreach (convert_bits_to_array($user['dbtech_vbshoutadminperms'], $vbulletin->bf_misc_dbtech_vbshoutadminperms) AS $field => $value)
{
	print_yes_no_row($vbphrase["$field"], "dbtech_vbshoutadminperms[$field]", $value);
}
?>