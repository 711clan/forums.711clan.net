<?php
$status = self::$vbulletin->input->clean_gpc('p', 'status', TYPE_UINT);

// Chat leave
self::leave_chatroom(self::$chatroom, self::$vbulletin->userinfo['userid']);

if (!(self::$instance['options']['activitytriggers'] & 16))
{
	// Un-idle us
	self::unIdle();
}
?>