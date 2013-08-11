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

define('VB_API_LOADLANG', true);

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'vboptions' => array('usecoppa', 'webmasteremail'),
	'session' => array('sessionhash'),
	'response' => array(
		'birthdayfields', 'checkedoff',
		'customfields_option' => array(
			'*' => $VB_API_WHITELIST_COMMON['customfield']
		),
		'customfields_other' => array(
			'*' => $VB_API_WHITELIST_COMMON['customfield']
		),
		'customfields_profile' => array(
			'*' => $VB_API_WHITELIST_COMMON['customfield']
		),
		'day', 'email',
		'emailconfirm', 'errorlist', 'human_verify',
		'month', 'parentemail', 'password', 'passwordconfirm', 'referrername',
		'timezoneoptions', 'url', 'year'
	),
	'vbphrase' => array(
		'coppa_rules_description', 'forum_rules_registration', 'forum_rules_description'
	),
	'show' => array(
		'coppa', 'birthday', 'referrer', 'customfields_profile', 'customfields_option',
		'noemptyoption', 'customfields_other', 'email'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/