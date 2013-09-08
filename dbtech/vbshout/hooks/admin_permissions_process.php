<?php
$vbulletin->input->clean_gpc('p', 'dbtech_vbshoutadminperms', TYPE_ARRAY_INT);
foreach ((array)$vbulletin->GPC['dbtech_vbshoutadminperms'] AS $field => $value)
{
	$admindm->set_bitfield('dbtech_vbshoutadminperms', $field, $value);
}
?>