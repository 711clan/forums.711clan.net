<?php
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
}

$show['vb414compat'] = version_compare($vbulletin->versionnumber, '4.1.4 Alpha 1', '>=');

$smilieJs = '';
foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['permissions_parsed']['canshout'])
	{
		// Skip this
		continue;
	}

	// Add the smilie JS
	$smilieJs .= 'if (window.opener) { window.opener.vB_Editor[\'dbtech_vbshout_editor' . $instanceid . '\'] = {}; window.opener.vB_Editor[\'dbtech_vbshout_editor' . $instanceid . '\'].init_smilies = function(smilie_container) { vBShout_initSmilies(smilie_container, ' . $instanceid . '); }; };';
}

// Sneak the CSS into the headinclude
$templater = vB_Template::create('dbtech_vbshout_css');
	$templater->register('jQueryVersion', 	VBSHOUT::$jQueryVersion);
	$templater->register('jQueryPath',		VBSHOUT::jQueryPath());
	$templater->register('versionnumber', 	VBSHOUT::$versionnumber);
	$templater->register('smilieJs', 		VBSHOUT::js($smilieJs, false, false));
$headinclude .= $templater->render();

// Global loading code
require(DIR . '/dbtech/vbshout/includes/global.php');
?>