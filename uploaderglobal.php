<?php
/*======================================================================*\
|| #################################################################### ||
|| #              убпв бнк ЧсуснЧЪ  uploade v 3.3                     # ||
|| #              for vBulletin Version 3.5.x                         # ||
|| #              http://7beebi.com    уцок ЭШэШэ                     # ||
|| #              webmaster@7beebi.com                                # ||
|| #################################################################### ||
\*======================================================================*/

($hook = vBulletinHook::fetch_hook('uploader_global_start')) ? eval($hook) : false;

$navbits[$parent] = $vbphrase['uploader'];

$navbits = construct_navbits($navbits);
eval('$navbar = "' . fetch_template('navbar') . '";');
construct_forum_jump();

$vbulletin->input->clean_gpc('p', 'editor', TYPE_INT);

$upeditor = '';
$description = 0;
$candelfiles = 0;
$sizecont = '';
$files = '';
$mypath = '';
$fl = '';
$ftppath = '';

if($vbulletin->GPC['editor'] OR ($_REQUEST['do'] == 'editor'))
{
        $upeditor = '_editor';
}

if (($permissions['uploaderperm'] & $vbulletin->bf_ugp['uploaderperm']['canadddescription']))
{
      $description = 1;
}

if (($permissions['uploaderperm'] & $vbulletin->bf_ugp['uploaderperm']['candeluploadedfiles']))
{
      $candelfiles = 1;
}
$uploaderx = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "uploaderx WHERE userid = " . $vbulletin->userinfo['userid'] . "");

$uploaderperm = $db->query_first("SELECT uploadermaxfilesize, uploadermaxfoldesize, uploaderfilestypes FROM " . TABLE_PREFIX . "usergroup WHERE usergroupid = " . $vbulletin->userinfo['usergroupid'] . "");

if (!($permissions['uploaderperm'] & $vbulletin->bf_ugp['uploaderperm']['canuploadfiles']))
{
        print_no_permission();
}
elseif ($vbulletin->options['uploader_active'] == 0 AND !$uploaderx['active'])
{
        $msg = $vbulletin->options['uploader_disabled'];
        eval('print_output("' . fetch_template('uploader' . $upeditor . '_msg') . '");');
}
elseif (in_array($vbulletin->userinfo['userid'], preg_split('#\s*,\s*#s', $vbulletin->options['uploaderbandusers'], -1, PREG_SPLIT_NO_EMPTY)))
{
        print_no_permission();
}
elseif ($vbulletin->options['posts_needs'] > $vbulletin->userinfo['posts']  AND !$uploaderx['posts_needs'])
{
        $msg = construct_phrase($vbphrase['no_post_x'], $vbulletin->options['posts_needs']);
        eval('print_output("' . fetch_template('uploader' . $upeditor . '_msg') . '");');
}

$limits = $db->query_read("SELECT file_size FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . "");
while ($limit = $db->fetch_array($limits))
{
   $sizecont += $limit['file_size'];
}
$sizecont_temp = vb_number_format($sizecont, 1 , true);

$size = $uploaderperm['uploadermaxfilesize'];
$sizetmp = vb_number_format($size, 1 , true);

$folder_size = $uploaderperm['uploadermaxfoldesize'];
$size_sizetmp = vb_number_format($folder_size, 1 , true);

$types = preg_split('#\s*,\s*#s', $uploaderperm['uploaderfilestypes'], -1, PREG_SPLIT_NO_EMPTY);
$typestemp = str_replace(array(', ', ',', '  ',), ' ', $uploaderperm['uploaderfilestypes']);

if ($vbulletin->options['sfolder'])
{
        $path = $vbulletin->options['folder_name'] . '/' . $vbulletin->userinfo['userid'] . '/';
        $dirpath = DIR . '/' . $vbulletin->options['folder_name'] . '/' . $vbulletin->userinfo['userid'];
}
else
{
        $path = $vbulletin->options['folder_name'] . '/';
        $dirpath = DIR . '/' . $vbulletin->options['folder_name'];
}

if ($vbulletin->options['uploaderexternal'] AND $vbulletin->options['sfolder'])
{
        $ftppath = $vbulletin->userinfo['userid'] . '/';
}

$rules['active'] = $vbphrase[iif($uploaderx['active'], 'yes', 'no')];
$rules['posts'] = $vbphrase[iif($uploaderx['posts_needs'], 'yes', 'no')];
$rules['filesize'] = $vbphrase[iif($uploaderx['file_size'], 'yes', 'no')];
$rules['foldersize'] = $vbphrase[iif($uploaderx['folder_size'], 'yes', 'no')];
$rules['types'] = $vbphrase[iif($uploaderx['types_files'], 'yes', 'no')];

eval('$uploader_rules = "' . fetch_template('uploader_rules') . '";');

($hook = vBulletinHook::fetch_hook('uploader_global_complete')) ? eval($hook) : false;

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile: uploader_global.php,v $ - $Revision: 3.3.4 $
|| ####################################################################
\*======================================================================*/
?>