<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/
@set_time_limit(0);
ignore_user_abort(1);

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// Rebuild the cache
VBSHOUT_CACHE::build('button');
VBSHOUT_CACHE::build('chatroom');
VBSHOUT_CACHE::build('command');
VBSHOUT_CACHE::build('instance');

($hook = vBulletinHook::fetch_hook('dbtech_vbshout_repaircache')) ? eval($hook) : false;

define('CP_REDIRECT', 'vbshout.php?do=options');
print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_cache'], $vbphrase['dbtech_vbshout_repaired']);