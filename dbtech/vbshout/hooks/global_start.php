<?php
global $vbulletin;

// Fetch required classes
require_once(DIR . '/dbtech/vbshout/includes/class_core.php');
require_once(DIR . '/dbtech/vbshout/includes/class_cache.php');
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
}

if (is_object($this))
{
	// Loads the cache class
	VBSHOUT_CACHE::init($vbulletin, $this->datastore_entries);
}
else if (is_object($bootstrap))
{
	// Loads the cache class
	VBSHOUT_CACHE::init($vbulletin, $bootstrap->datastore_entries);
}
else
{
	// Loads the cache class
	VBSHOUT_CACHE::init($vbulletin, $specialtemplates);
}

// Initialise
VBSHOUT::init($vbulletin);

if (VBSHOUT::$permissions['canview'])
{
	$show['vbshout'] = $vbulletin->options['dbtech_vbshout_navbar'];
	$show['vbshout_ispro'] = VBSHOUT::$isPro;
	if ($vbulletin->options['dbtech_vbshout_integration'] & 1)
	{
		$show['vbshout_ql'] = true;
	}
	if ($vbulletin->options['dbtech_vbshout_integration'] & 2)
	{
		$show['vbshout_com'] = true;
	}
}

// Show branding or not
$show['vbshout_branding'] = $vbulletin->options['dbtech_vbshout_branding_free'] != '25599748-ea0f1f752375abaade8e08dc6eff634f';
$show['dbtech_vbshout_producttype'] = (VBSHOUT::$isPro ? ' (Pro)' : ' (Lite)');

if ($show['vbshout_branding'] AND !$show['_dbtech_branding_override'])
{
	$brandingVariables = array(
		'flavour' 			=> 'Shoutbox provided by ',
		'productid' 		=> 2,
		'utm_source' 		=> str_replace('www.', '', $_SERVER['HTTP_HOST']),		
		'utm_content' 		=> (VBSHOUT::$isPro ? 'Pro' : 'Lite'),
		'referrerid' 		=> $vbulletin->options['dbtech_vbshout_referral'],
		'title' 			=> 'vBShout',
		'displayversion' 	=> $vbulletin->options['dbtech_vbshout_displayversion'],
		'version' 			=> VBSHOUT::$version,
		'producttype' 		=> $show['dbtech_vbshout_producttype'],
		'showhivel' 		=> (!VBSHOUT::$isPro AND !$vbulletin->options['dbtech_vbshout_nohivel'])
	);

	$str = $brandingVariables['flavour'] . '
		<a rel="nofollow" href="http://www.dragonbyte-tech.com/vbecommerce.php' . ($brandingVariables['productid'] ? '?productid=' . $brandingVariables['productid'] . '&do=product&' : '?') . 'utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=Footer%2BLinks&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">' . $brandingVariables['title'] . ($brandingVariables['displayversion'] ? ' v' . $brandingVariables['version'] : '') . $brandingVariables['producttype'] . '</a> - 
		<a href="http://www.dragonbyte-tech.com/?utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=Footer%2BLinks&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">vBulletin Mods &amp; Addons</a> Copyright &copy; ' . date('Y') . ' DragonByte Technologies Ltd.' . 
		($brandingVariables['showhivel'] ? ' Runs best on <a href="http://www.hivelocity.net/?utm_source=Iain%2BKidd&utm_medium=back%2Blink&utm_term=Dedicated%2BServer%2BSponsor&utm_campaign=Back%2BLinks%2Bfrom%2BIain%2BKidd" target="_blank">HiVelocity Hosting</a>.' : '');
	$vbulletin->options['copyrighttext'] = (trim($vbulletin->options['copyrighttext']) != '' ? $str . '<br />' . $vbulletin->options['copyrighttext'] : $str);
}

if (defined('IN_CONTROL_PANEL'))
{
	if (!function_exists('fetch_tag_list'))
	{
		require_once(DIR . '/includes/class_bbcode.php');
	}
	
	// Store all possible BBCode tags
	VBSHOUT::$tag_list = fetch_tag_list('', true);
}