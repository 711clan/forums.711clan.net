<?php
/*======================================================================*\
|| #################################################################### ||
|| #              ãÑßÒ ÑÝÚ ÇáãáÝÇÊ  uploade v 3.3                     # ||
|| #              for vBulletin Version 3.5.X                         # ||
|| #              http://7beebi.com    ãæÞÚ ÍÈíÈí                     # ||
|| #              webmaster@7beebi.com                                # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile: uploadermod.php,v $ - $Revision: 3.3.4 $');
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'uploadermod');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cpuser', 'user');
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK Mod PERMISSIONS #######################
if (!($permissions['uploaderperm'] & $vbulletin->bf_ugp['uploaderperm']['canmoduploadedfiles']))
{
        print_stop_message('no_permission');
}

// ############################# LOG ACTION ###############################
log_admin_action(iif($_REQUEST['id'],'do=' . $_REQUEST['do'] . '&id=' . $_REQUEST['id']));

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
print_cp_header($vbphrase['uploader']);

if ($_REQUEST['do'] == "files")
{
        $vbulletin->input->clean_array_gpc('r', array
        (
                'userid'       => TYPE_INT,
                'storeby'      => TYPE_STR,
                'limitstart'   => TYPE_INT,
                'limitnumber'  => TYPE_INT,
        ));

        if (empty($vbulletin->GPC['limitstart']))
        {
             $vbulletin->GPC['limitstart'] = 0;
        }
        else
        {
             $vbulletin->GPC['limitstart']--;
        }

        if (empty($vbulletin->GPC['limitnumber']) OR $vbulletin->GPC['limitnumber'] == 0)
        {
            $vbulletin->GPC['limitnumber'] = 15;
        }

        if ($vbulletin->GPC['userid'] OR $vbulletin->GPC['userid'] != 0)
        {
            $userfilse = "WHERE uploader.userid = " . $vbulletin->GPC['userid'] . "";
        }

        $countups = $db->query_first("SELECT COUNT(*) AS ups FROM " . TABLE_PREFIX . "uploader $userfilse");
        $limitfinish = $vbulletin->GPC['limitstart'] + $vbulletin->GPC['limitnumber'];

        if($vbulletin->GPC['storeby'] == '' OR empty($vbulletin->GPC['storeby']))
        {
           $vbulletin->GPC['storeby'] = 'dateline DESC';
        }

        $uploaders = $db->query_read("SELECT uploader.*, user.username
                     FROM " . TABLE_PREFIX . "uploader AS uploader
                     LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploader.userid = user.userid)
                     $userfilse
                     ORDER BY " . $vbulletin->GPC['storeby'] . "
                     LIMIT " . $vbulletin->GPC['limitstart'] . ", " . $vbulletin->GPC['limitnumber'] . "");

        print_form_header('uploadermod','files');
        print_table_header(construct_phrase(
                        $vbphrase['showing_uploads_x_to_y_of_z'],
                        ($vbulletin->GPC['limitstart'] + 1),
                        iif($limitfinish > $countups['ups'], $countups['ups'], $limitfinish),
                        $countups['ups']
                ),5);

        $header = array();
        $header[] = "<a href=uploadermod.php?do=files&storeby=file_name>" . $vbphrase['file_name'] . "</a>";
        $header[] = "<a href=uploadermod.php?do=files&storeby=file_size%20DESC>" . $vbphrase['file_size'] . "</a>";
        $header[] = "<a href=uploadermod.php?do=files&storeby=username>" . $vbphrase['users'] . "</a>";
        $header[] = "<a href=uploadermod.php?do=files>" . $vbphrase['date'] . "</a>";
        $header[] = $vbphrase['options'];
        print_cells_row($header, 1, 0, 1);
        $cell = array();

        while ($uploader = $db->fetch_array($uploaders))
        {
           $uploader['file_description'] = iif($uploader['description'], $uploader['description'], $uploader['file_name']);
           $cell[] = "<a dir=ltr href='" . $uploader['fileurl'] . "' target='_blank' title='" . $uploader['file_description'] . "'>" . iif(strlen($uploader['file_name']) > 30 ,substr($uploader['file_name'], 0, 30) . "...", $uploader['file_name']) . "</a>";
           $cell[] = vb_number_format($uploader['file_size'], 1 , true);
           $cell[] = "<a href=user.php?do=edit&userid=" . $uploader['userid'] . " target='_blank'>" . $uploader['username'] . "</a>";
           $cell[] = vbdate($vbulletin->options['dateformat'], $uploader['dateline'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $uploader['dateline']);
           $cell[] = "<a href=uploadermod.php?do=delfile&id=" . $uploader['id'] . ">" . $vbphrase['delete'] . "</a>";
           print_cells_row($cell, 0, 0, 1, 1, 0, 1);
           unset($cell);
           $cell = array();
        }
        if ($vbulletin->GPC['userid'] OR $vbulletin->GPC['userid'] != 0)
        {
            construct_hidden_code('userid', $vbulletin->GPC['userid']);
        }
        construct_hidden_code('limitnumber', $vbulletin->GPC['limitnumber']);

        if ($vbulletin->GPC['limitstart'] == 0 AND $countups['ups'] > $vbulletin->GPC['limitnumber'])
        {
            construct_hidden_code('limitstart', $vbulletin->GPC['limitstart'] + $vbulletin->GPC['limitnumber'] + 1);
            print_submit_row($vbphrase['next_page'], 0, $colspan);
        }
        elseif ($limitfinish < $countups['ups'])
        {
            construct_hidden_code('limitstart', $vbulletin->GPC['limitstart'] + $vbulletin->GPC['limitnumber'] + 1);
            print_submit_row($vbphrase['next_page'], 0, $colspan, $vbphrase['prev_page'], '', true);
        }
        elseif ($limitfinish >= $countups['ups'] AND $countups['ups'] > $vbulletin->GPC['limitnumber'])
        {
            print_submit_row($vbphrase['first_page'], 0, $colspan, $vbphrase['prev_page'], '', true);
        }

        print_table_footer();
}

elseif ($_REQUEST['do'] == "delfile")
{
        $vbulletin->input->clean_gpc('r', 'id', TYPE_INT);

        $file = $db->query_first("SELECT uploader.*, user.username
                FROM " . TABLE_PREFIX . "uploader AS uploader
                LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploader.userid = user.userid)
                WHERE id = " . $vbulletin->GPC['id'] . "");

        echo "<p>&nbsp;</p><p>&nbsp;</p>";
        print_form_header("uploadermod", "killfile", 0, 1, '', '75%');
        construct_hidden_code('id', $vbulletin->GPC['id']);
        print_table_header(construct_phrase($vbphrase['confirm_delfile_x'], $file['file_name']));
        print_description_row("
        <blockquote><br />
        " . construct_phrase($vbphrase["are_you_sure_want_to_delfile_x"], $file['file_name'], $file['username']) . "
        <br /></blockquote>\n\t");
        print_submit_row($vbphrase['yes'], 0, 2, $vbphrase['no']);
}

elseif ($_REQUEST['do'] == "killfile")
{
        $vbulletin->input->clean_gpc('r', 'id', TYPE_INT);

        $filedb = $db->query_first("SELECT fileurl, userid FROM " . TABLE_PREFIX . "uploader WHERE id = " . $vbulletin->GPC['id'] . "");

        $file = str_replace($vbulletin->options['bburl'], '', $filedb['fileurl']);

        if ($vbulletin->options['uploaderexternal'])
        {
              $ftppath = '';
              if($vbulletin->options['sfolder'])
              $ftppath = $filedb['userid'] . '/';

              $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);
              @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);

              if (@ftp_delete($conn_id, $ftppath . @basename($filedb['fileurl'])))
              {
                      $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE id = " . $vbulletin->GPC['id'] . "");
                      @ftp_close($conn_id);
                      print_cp_message($vbphrase['done_delete'], 'uploadermod.php?do=files', 1);
              }

              @ftp_close($conn_id);
        }

        elseif (@unlink(DIR . $file))
        {
              $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE id = " . $vbulletin->GPC['id'] . "");
              print_cp_message($vbphrase['done_delete'], 'uploadermod.php?do=files', 1);
        }

        print_cp_message($vbphrase['not_delete_cp']. '<br />' . DIR . $file);
}

print_cp_footer();
/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile: uploadermod.php,v $ - $Revision: 3.3.4 $
|| ####################################################################
\*======================================================================*/
?>