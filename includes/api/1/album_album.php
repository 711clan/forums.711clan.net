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

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'albuminfo' => array(
			'albumid', 'title', 'description'
		),
		'albumtype', 'posthash', 'poststarttime',
		'pagenav' => $VB_API_WHITELIST_COMMON['pagenav'],
		'pagenumber', 'totalpages', 'start', 'end', 'total',
		'userinfo' => array(
			'userid', 'username'
		),
		'picturebits' => array(
			'*' => array(
				'picture' => array(
					'attachmentid', 'title', 'caption_preview', 'thumbnail_dateline',
					'hasthumbnail', 'pictureurl'
				),
				'show' => array(
					'moderation'
				)
			)
		)
	),
	'show' => array(
		'add_group_row', 'edit_album_option', 'add_picture_option'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/
