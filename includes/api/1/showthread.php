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

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'pagenumbers', 'totalposts',
		'activeusers' => $VB_API_WHITELIST_COMMON['activeusers'],
		'bookmarksites' => $VB_API_WHITELIST_COMMON['bookmarksites'],
		'FIRSTPOSTID',
		'firstunread', 'forumrules', 'LASTPOSTID', 'nextthreadinfo',
		'numberguest', 'numberregistered',
		'pagenav' => $VB_API_WHITELIST_COMMON['pagenav'],
		'pagenumber',
		'perpage',
		'poll' => array(
			'pollbits' => array(
				'*' => array(
					'option' => array('question', 'votes', 'percentraw'),
				),
			),
			'pollenddate', 'pollendtime',
			'pollinfo' => array(
				'pollid', 'question', 'numbervotes', 'multiple'
			),
			'pollstatus'
		),
		'postbits' => $VB_API_WHITELIST_COMMON['postbits'],
		'prevthreadinfo', 'postid', 'similarthreads',
		'tag_list',
		'thread' => $VB_API_WHITELIST_COMMON['threadinfo'],
		'threadlist', 'totalonline',
	),
	'show' => array(
		'threadinfo', 'threadedmode', 'linearmode', 'hybridmode', 'viewpost',
		'managepost', 'approvepost', 'managethread', 'approveattachment',
		'inlinemod', 'spamctrls', 'rating', 'editpoll', 'pollenddate', 'multiple',
		'publicwarning', 'largereplybutton', 'multiquote_global', 'firstunreadlink',
		'tag_box', 'manage_tag', 'activeusers', 'deleteposts', 'editthread',
		'movethread', 'stickunstick', 'openclose', 'moderatethread', 'deletethread',
		'adminoptions', 'addpoll', 'search', 'subscribed', 'threadrating', 'ratethread',
		'closethread', 'approvethread', 'unstick', 'reputation', 'sendtofriend',
		'next_prev_links'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/