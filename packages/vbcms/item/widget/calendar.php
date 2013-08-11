<?php if (!defined('VB_ENTRY')) die('Access denied.');
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

/**
 * Test Widget Item
 *
 * @package vBulletin
 * @author Edwin Brown, vBulletin Development Team
 * @version $Revision: 64477 $
 * @since $Date: 2012-07-17 14:36:53 -0700 (Tue, 17 Jul 2012) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Widget_Calendar extends vBCms_Item_Widget
{

	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'Calendar';

	/** The default configuration **/
	protected $config = array(
		'url'           => '',
		'template_name' => 'vbcms_widget_calendar_page',
	);

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:57, Sun Aug 11th 2013
|| # SVN: $Revision: 64477 $
|| ####################################################################
\*======================================================================*/