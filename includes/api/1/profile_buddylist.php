<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.1 - Licence Number VBC2DDE4FB
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2013 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'buddycount',
			'buddylist' => array(
				'*' => array(
					'container', 'friendcheck_checked',
					'user' => array(
						'userid', 'usertitle', 'avatarurl', 'avatarwidth', 'avatarheight',
						'username', 'type', 'checked'
					),
					'show' => array(
						'incomingrequest', 'outgoingrequest', 'friend_checkbox'
					)
				)
			),
			'buddy_username',
			'incominglist' => array(
				'*' => array(
					'container', 'friendcheck_checked',
					'user' => array(
						'userid', 'usertitle', 'avatarurl', 'avatarwidth', 'avatarheight',
						'username', 'type', 'checked'
					),
					'show' => array(
						'incomingrequest', 'outgoingrequest', 'friend_checkbox'
					)
				)
			),
			'perpage', 'pagenumber', 'pagenav'
		)
	),
	'show' => array(
		'friend_controls', 'incomingrequest', 'outgoingrequest', 'friend_checkbox',
		'incominglist', 'buddylist', 'avatars'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/