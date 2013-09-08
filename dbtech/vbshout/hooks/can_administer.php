<?php
foreach($do AS $field)
{
    if ($admin['dbtech_vbshoutadminperms']  & $vbulletin->bf_misc_dbtech_vbshoutadminperms["$field"])
    {
        $return_value = true;
    }
} 
?>