<?php 

$dd = array(
	'arcade_categories' => array(
		'fields' => array(
			'categoryid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'catname' => array(
				'type' => 'varchar',
				'length' => '250',
				'notnull' => 1
			),
			'displayorder' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'isactive' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '1',
				'unsigned' => 1, 
				'notnull' => 1
			)
		),
		'key' => 'categoryid'
	),
	'arcade_challenges' => array(
		'fields' => array(
			'challengeid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'fromuserid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'touserid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'winnerid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'loserid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'gameid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'datestamp' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'fromsessionid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'tosessionid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'fromscore' => array(
				'type' => 'float',
				'length' => '15,3',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'toscore' => array(
				'type' => 'float',
				'length' => '15,3',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'status' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
		),
		'key' => 'challengeid'
	),
	'arcade_games' => array(
		'fields' => array(
			'gameid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'shortname' => array(
				'type' => 'varchar',
				'length' => '100',
				'notnull' => 1
			),
			'title' => array(
				'type' => 'varchar',
				'length' => '100',
				'notnull' => 1
			),
			'description' => array(
				'type' => 'mediumtext',
				'notnull' => 1
			),
			'file' => array(
				'type' => 'varchar',
				'length' => '100',
				'notnull' => 1
			),
			'width' => array(
				'type' => 'smallint',
				'length' => '4',
				'default' => '550',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'height' => array(
				'type' => 'smallint',
				'length' => '4',
				'default' => '400',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'miniimage' => array(
				'type' => 'varchar',
				'length' => '100',
				'notnull' => 1
			),
			'stdimage' => array(
				'type' => 'varchar',
				'length' => '100',
				'notnull' => 1
			),
			'gamepermissions' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '7',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'highscorerid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'highscore' => array(
				'type' => 'float',
				'length' => '15,3',
				'default' => '',
				'unsigned' => 0, 
				'notnull' => 1
			),
			'gamehash' => array(
				'type' => 'varchar',
				'length' => '50',
				'notnull' => 1
			),
			'categoryid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '1',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'timesplayed' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'dateadded' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'system' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'votepoints' => array(
				'type' => 'int',
				'length' => '3',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'votecount' => array(
				'type' => 'int',
				'length' => '6',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'sessioncount' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minpoststotal' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minpostsperday' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minpoststhisday' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minreglength' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minrep' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'notnull' => 1
			),
			'isreverse' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'cost' => array(
				'type' => 'decimal',
				'length' => '30,5',
				'default' => '0',
				'unsigned' => 1, 
				'notnull' => 0
			)
			
		),
		'key' => 'gameid'
	),
	'arcade_news' => array(
		'fields' => array(
			'newsid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'newstext' => array(
				'type' => 'mediumtext',
				'notnull' => 1
			),
			'newstype' => array(
				'type' => 'varchar',
				'length' => '20',
				'default' => '',
				'notnull' => 1
			),
			'datestamp' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			)
		),
		'key' => 'newsid'
	),
	'arcade_ratings' => array(
		'fields' => array(
			'ratingid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'gameid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'userid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'rating' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			)
		),
		'key' => 'ratingid'
	),
	'arcade_favorites' => array(
		'fields' => array(
			'favid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'gameid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'userid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			)
		),
		'key' => 'favid'
	),
	'arcade_sessions' => array(
		'fields' => array(
			'sessionid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1, 
				'autoincrement' => 1
			),
			'gameid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'gamename' => array(
				'type' => 'varchar',
				'length' => '20',
				'default' => '',
				'notnull' => 1
			),
			'userid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'start' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'finish' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'ping' => array(
				'type' => 'float',
				'length' => '7,2',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'comment' => array(
				'type' => 'varchar',
				'length' => '250',
				'default' => '',
				'notnull' => 1
			),
			'valid' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'score' => array(
				'type' => 'float',
				'length' => '15,3',
				'default' => '',
				'unsigned' => 0, 
				'notnull' => 1
			),
			'sessiontype' => array(
				'type' => 'tinyint',
				'length' => '1',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'challengeid' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			)
		),
		'key' => 'sessionid'
	),
	'usergroup' => array(
		'fields' => array(
			'arcadepermissions' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minpoststoplay' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '0',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'minreptoplay' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '-1000',
				'unsigned' => 0, 
				'notnull' => 1
			),
			'minreglengthtoplay' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '0',
				'unsigned' => 0, 
				'notnull' => 1
			)
		)
	),
	'user' => array(
		'fields' => array(
			'arcadeoptions' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'challengecache' => array(
				'type' => 'int',
				'length' => '10',
				'default' => '',
				'unsigned' => 1, 
				'notnull' => 1
			),
			'favcache' => array(
				'type' => 'text',
				'notnull' => 1
			)
		)
	)
	
);

?>