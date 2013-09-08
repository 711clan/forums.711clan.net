<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'randomgame');

require_once('./global.php');

$rangame = $db->query_first("SELECT g.gid, g.gtitle, g.gname, g.gcat, cat.password, cat.active FROM games_list AS g, games_cats AS cat WHERE g.active=1 AND cat.active=1 AND g.gcat=cat.c_id AND trim(password)='' ORDER BY RAND() LIMIT 1");

echo "
	<div align=\"center\" class=\"smallfont\">
		<a href=\"arcade.php?act=Arcade&do=play&gameid={$rangame['gid']}\">{$rangame['gtitle']}<br /><img src=\"arcade/images/{$rangame[gname]}1.gif\" border=\"0\"></a>
	</div>
";
exit;
?>