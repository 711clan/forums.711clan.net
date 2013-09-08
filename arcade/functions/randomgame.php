<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'randomgame');
define('SKIP_SESSIONCREATE', 1);
define('NOCOOKIES', 1);
define('DIE_QUIETLY', 1);
define('LOCATION_BYPASS', 1);
define('NOPMPOPUP', 1);
define('ACTUALPATH', (($getcwd = getcwd()) ? $getcwd : '.'));

/*
define('FORUMPATH','/var/www/vhosts/httpdocs/board');

global $vbulletin, $vboptions;

$phrasegroups = array();
$specialtemplates = array();
$actiontemplates = array();
$globaltemplates = array();

chdir(FORUMPATH);
require_once('./global.php');
chdir(ACTUALPATH);
*/

require_once('dbclass.php');
$DB = new db_driver;

$ranquery = $DB->query("SELECT g.gid, g.gtitle, g.gname, g.gcat, cat.password, cat.active FROM ibf_games_list AS g, ibf_games_cats AS cat WHERE g.active=1 AND cat.active=1 AND g.gcat=cat.c_id AND trim(password)='' ORDER BY RAND() LIMIT 1");
$rangame = $DB->fetch_row($ranquery);

echo "
	<div align=\"center\" class=\"smallfont\">
		<a href=\"arcade.php?act=Arcade&do=play&gameid={$rangame['gid']}\">{$rangame['gtitle']}<br /><img src=\"arcade/images/{$rangame[gname]}1.gif\" border=\"0\"></a>
	</div>
";
exit;
?>