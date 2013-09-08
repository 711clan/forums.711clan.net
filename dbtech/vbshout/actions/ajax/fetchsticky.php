<?php
// Fetch sticky
self::$fetched['editor'] = '/sticky ' . self::$instance['sticky_raw'];

if (!(self::$instance['options']['activitytriggers'] & 4))
{
	// Un-idle us
	self::unIdle();
}
?>