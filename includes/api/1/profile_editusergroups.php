<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.1 - Licence Number VBC2DDE4FB
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2013 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'displaygroupbits' => array(
				'*' => array(
					'checked',
					'usergroup' => array(
						'opentag', 'title', 'closetag', 'description',
						'usertitle', 'usergroupid'
					)
				)
			),
			'joinrequestbits',
			'membergroupbits' => array(
				'*' => array(
					'usergroup' => array(
						'opentag', 'title', 'closetag', 'description',
						'usertitle', 'usergroupid'
					),
					'show' => array(
						'isleader', 'canleave'
					)
				)
			),
			'nonmembergroupbits' => array(
				'*' => array(
					'groupleaders',
					'usergroup' => array(
						'opentag', 'title', 'closetag', 'description',
						'usertitle', 'usergroupid'
					),
					'ismoderated', 'joinrequest', 'joinrequested'
				)
			),
			'primarygroup' => array(
				'opentag', 'title', 'closetag', 'description',
				'usertitle', 'usergroupid'
			), 'primarygroupid'
		)
	),
	'show' => array(
		'joinrequests', 'nonmembergroups', 'isleader', 'canleave', 'membergroups',
		'displaygroups'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/