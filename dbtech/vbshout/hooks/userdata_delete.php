<?php
// Delete all shouts from this user
$this->dbobject->query_write("
	DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
	WHERE userid = " . $this->existing['userid'] . "
		OR (
			type = 2
				AND id = " . $this->existing['userid'] . "
		)
");

// Update log commands from this user
$this->dbobject->query_write("
	UPDATE " . TABLE_PREFIX . "dbtech_vbshout_log
	SET username = " . $this->dbobject->sql_prepare($this->existing['username']) . "
	WHERE userid = " . $this->existing['userid'] . "
");
?>