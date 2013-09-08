<?php
// Chat join
self::join_chatroom(self::$chatroom, self::$vbulletin->userinfo['userid']);

if (!(self::$instance['options']['activitytriggers'] & 8))
{
	// Un-idle us
	self::unIdle();
}
?>