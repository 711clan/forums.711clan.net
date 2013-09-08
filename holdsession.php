<?php
//------------------------------------------------------------------------------------------------
// This script is part of ibProArcade for vBulletin (C) MrZeropage
// It refreshes your session while playing in the arcade to avoid being logged out during a game
//------------------------------------------------------------------------------------------------

define('VB_AREA', 'Forum'); 
define('THIS_SCRIPT', 'holdsession'); 

require('./includes/init.php'); 

if (empty($vbulletin)) 
{ 
	// this is vBulletin 3.0.x 
	// must load functions.php and initialize session as it is not done automatically 
	require('./includes/functions.php'); 
	require('./includes/sessions.php'); 
	$session['sessionurl_q'] = "?$session[sessionurl]"; 
} 
else 
{ 
	$vboptions =& $vbulletin->options; 
	$session =& $vbulletin->session->vars; 
	$bbuserinfo =& $vbulletin->userinfo; 
} 

$gameid = intval($_POST['gameid']);

$secs = $vboptions['cookietimeout'] - 60; 
if ($secs < 60) 
{ 
    $secs = 60; 
}

echo "<meta http-equiv=\"refresh\" content=\"$secs; URL=\"$vboptions[bburl]/holdsession.php$session[sessionurl_q]act=arcade&do=play&gameid=$gameid\">"; 

?> 