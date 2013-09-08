<?php
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_session`
	(
		`userid` int(10) unsigned NOT NULL DEFAULT '0',
		`lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
		`instanceid` int(10) unsigned NOT NULL DEFAULT '0',
		`chatroomid` int(10) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`userid`, `instanceid`, `chatroomid`),
		KEY `last_activity` (`lastactivity`) USING BTREE,
		KEY `instance_activity` (`lastactivity`, `instanceid`) USING BTREE,
		KEY `chatroom_activity` (`lastactivity`, `chatroomid`) USING BTREE
	) ENGINE=" . self::get_high_concurrency_table_engine(self::$db) . ";
");
self::report('Created Table', 'dbtech_vbshout_session');
?>