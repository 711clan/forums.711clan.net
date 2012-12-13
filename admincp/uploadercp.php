<?php
/*======================================================================*\
|| #################################################################### ||
|| #              ãÑßÒ ÑÝÚ ÇáãáÝÇÊ  uploade v 3.3                     # ||
|| #              for vBulletin Version 3.5.x                         # ||
|| #              http://7beebi.com    ãæÞÚ ÍÈíÈí                     # ||
|| #              webmaster@7beebi.com                                # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile: uploadercp.php,v $ - $Revision: 3.3.4 $');
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'uploadercp');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cpuser', 'user');
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminusers'))
{
        print_cp_no_permission();
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

        print_form_header('uploadercp','files');
        print_table_header(construct_phrase(
                        $vbphrase['showing_uploads_x_to_y_of_z'],
                        ($vbulletin->GPC['limitstart'] + 1),
                        iif($limitfinish > $countups['ups'], $countups['ups'], $limitfinish),
                        $countups['ups']
                ),5);

        $header = array();
        $header[] = "<a href=uploadercp.php?do=files&storeby=file_name>" . $vbphrase['file_name'] . "</a>";
        $header[] = "<a href=uploadercp.php?do=files&storeby=file_size%20DESC>" . $vbphrase['file_size'] . "</a>";
        $header[] = "<a href=uploadercp.php?do=files&storeby=username>" . $vbphrase['users'] . "</a>";
        $header[] = "<a href=uploadercp.php?do=files>" . $vbphrase['date'] . "</a>";
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
           $cell[] = "<a href=uploadercp.php?do=delfile&id=" . $uploader['id'] . ">" . $vbphrase['delete'] . "</a> <a href=uploadercp.php?do=ignfile&id=" . $uploader['id'] . ">" . $vbphrase['ignoreup'] . "</a>";
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
        print_form_header("uploadercp", "killfile", 0, 1, '', '75%');
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
                      print_cp_message($vbphrase['done_delete'], 'uploadercp.php?do=files', 1);
              }

              @ftp_close($conn_id);
        }

        elseif (@unlink(DIR . $file))
        {
              $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE id = " . $vbulletin->GPC['id'] . "");
              print_cp_message($vbphrase['done_delete'], 'uploadercp.php?do=files', 1);
        }

        print_cp_message($vbphrase['not_delete_cp']. '<br />' . DIR . $file);
}

elseif ($_REQUEST['do'] == "ignfile")
{
        $vbulletin->input->clean_gpc('r', 'id', TYPE_INT);

        $file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "uploader WHERE id=" . $vbulletin->GPC['id'] . "");
        $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE id=" . $vbulletin->GPC['id'] . "");
        print_cp_message($vbphrase['done_remove'], 'uploadercp.php?do=files', 1);
}

elseif ($_REQUEST['do'] == "users")
{
        $vbulletin->input->clean_array_gpc('r', array
        (
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

        if($vbulletin->GPC['storeby'] == '' OR empty($vbulletin->GPC['storeby']))
        {
           $vbulletin->GPC['storeby'] = 'userid';
        }

        $db->query_write("TRUNCATE TABLE " . TABLE_PREFIX . "uploaderstatistic");

        $uploaders = $db->query_read("SELECT uploader.*, user.username
        FROM " . TABLE_PREFIX . "uploader AS uploader
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploader.userid = user.userid)
        ORDER BY user.username");

        while ($uploader = $db->fetch_array($uploaders))
        {
           if($befor != $uploader['username'])
           {
               $db->query_write("INSERT INTO " . TABLE_PREFIX . "uploaderstatistic(userid, lastupload, filesize, fileurl, dateline) VALUES ('" . $uploader['userid'] . "', '" . $db->escape_string($uploader['file_name']) . "'," . $uploader['file_size'] . ",'" . $uploader['fileurl'] . "'," . $uploader['dateline'] . ")");
           }

           $befor = $uploader['username'];
        }

        $limitfinish = $vbulletin->GPC['limitstart'] + $vbulletin->GPC['limitnumber'];
        $countups = $db->query_first("SELECT COUNT(*) AS ups FROM " . TABLE_PREFIX . "uploaderstatistic");

        $statistics = $db->query_read("
                      SELECT uploaderstatistic.*, user.username, user.lastactivity
                      FROM " . TABLE_PREFIX . "uploaderstatistic AS uploaderstatistic
                      LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploaderstatistic.userid = user.userid)
                      ORDER BY uploaderstatistic." . $vbulletin->GPC['storeby'] . "
                      LIMIT " . $vbulletin->GPC['limitstart'] . ", " . $vbulletin->GPC['limitnumber'] . "");

        print_form_header('uploadercp','users');
        print_table_header(construct_phrase(
                        $vbphrase['showing_users_x_to_y_of_z'],
                        ($vbulletin->GPC['limitstart'] + 1),
                        iif($limitfinish > $countups['ups'], $countups['ups'], $limitfinish),
                        $countups['ups']
                ),5);

        $header = array();
        $header[] = "<a href='uploadercp.php?do=users&storeby=username'>" . $vbphrase['users'] . "</a>";
        $header[] = "<a href='uploadercp.php?do=users&storeby=lastupload'>" . $vbphrase['last_file_name'] . "</a>";
        $header[] = "<a href='uploadercp.php?do=users&storeby=filesize%20DESC'>" . $vbphrase['file_size'] . "</a>";
        $header[] = "<a href='uploadercp.php?do=users&storeby=dateline%20DESC'>" . $vbphrase['date'] . "</a>";
        $header[] = $vbphrase['last_activity'];
        print_cells_row($header, 1, 0, 1);
        $cell = array();

        while ($statistic = $db->fetch_array($statistics))
        {
               $cell[] = "<a href='uploadercp.php?do=statistic&userid=" . $statistic['userid'] . "' title='" . $vbphrase['info'] . "'>" . $statistic['username'] . "</a>";
               $cell[] = "<a dir=ltr href='" . $statistic['fileurl'] . "' target='_blank' title='" . $statistic['lastupload'] . "'>" . iif(strlen($statistic['lastupload']) > 30 ,substr($statistic['lastupload'], 0, 30) . "...", $statistic['lastupload']) . "";
               $cell[] = vb_number_format($statistic['filesize'], 1 , true);
               $cell[] = vbdate($vbulletin->options['dateformat'], $statistic['dateline'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $statistic['dateline']);
               $cell[] = iif($statistic['lastactivity'] <= strtotime("now -30 days"), '<font color="#FF0000">') . vbdate($vbulletin->options['dateformat'], $statistic['lastactivity'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $statistic['lastactivity']) . '</font>';
               print_cells_row($cell, 0, 0, 1, 1, 0, 1);
               unset($cell);
               $cell = array();
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

elseif ($_REQUEST['do'] == "statistic")
{
        $vbulletin->input->clean_gpc('r', 'userid', TYPE_INT);

        $userfiles = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->GPC['userid'] . "");
        $threads = $db->query_read("SELECT threadid, title FROM " . TABLE_PREFIX . "thread WHERE postuserid = " . $vbulletin->GPC['userid'] . "");
        $lastthread = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "thread WHERE postuserid = " . $vbulletin->GPC['userid'] . " ORDER BY dateline DESC");
        $user = $db->query_first("
        SELECT user.*, usergroup.title
        FROM " . TABLE_PREFIX . "user as user
        LEFT JOIN " . TABLE_PREFIX . "usergroup as usergroup ON (user.usergroupid = usergroup.usergroupid)
        WHERE user.userid = " . $vbulletin->GPC['userid'] . "
        ");

        $filesiz = 0;

        while ($userfile = $db->fetch_array($userfiles))
        {
                $countfiles++;
                $filesizs += $userfile['file_size'];
        }

        while ($thread = $db->fetch_array($threads))
        {
                $countthreads++;
        }

        print_form_header();
        print_table_header($vbphrase['uploader'] . ' - ' . $user['username']);
        print_label_row($vbphrase['username'], "<a href=user.php?do=edit&userid=" . $user['userid'] . " target='_blank'>" . fetch_musername($user) . "</a>");
        print_label_row($vbphrase['userid'], $user['userid']);
        print_label_row($vbphrase['user_title'], $user['usertitle']);
        print_label_row($vbphrase['usergroup'], $user['title']);
        print_label_row($vbphrase['email'], $user['email']);
        print_label_row($vbphrase['countfiles'], $countfiles . ' <a href="uploadercp.php?do=files&userid=' . $user['userid'] . '">' . $vbphrase['view'] . '</a> <a href="uploadercp.php?do=delall&userid=' . $user['userid'] . '">' . $vbphrase['delete_all'] . '</a>');
        print_label_row($vbphrase['filesizs'], vb_number_format($filesizs, 1 , true));
        print_label_row($vbphrase['posts'], $user['posts']);
        print_label_row($vbphrase['last_post'], vbdate($vbulletin->options['dateformat'], $user['lastpost'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $user['lastpost']));
        print_label_row($vbphrase['threads'], $countthreads);
        print_label_row($vbphrase['lastthread'], '<a href="../showthread.php?t=' . $lastthread['threadid'] . '" title=' . vbdate($vbulletin->options['dateformat'], $lastthread['dateline'], 1) . ' target="_blank">' . iif(strlen($lastthread['title']) > 30 ,substr($lastthread['title'], 0, 30) . "...", $lastthread['title']) . '</a>');
        print_label_row($vbphrase['ip_address'], $user['ipaddress']);
        print_label_row($vbphrase['join_date'], vbdate($vbulletin->options['dateformat'], $user['joindate'], 1));
        print_label_row($vbphrase['last_activity'], vbdate($vbulletin->options['dateformat'], $user['lastactivity'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $user['lastactivity']));
        construct_hidden_code('userid', $user['userid']);
        print_table_footer();
}

elseif ($_REQUEST['do'] == "delall")
{
        $vbulletin->input->clean_gpc('r', 'userid', TYPE_INT);

        $user = $db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC['userid'] . "");

        echo "<p>&nbsp;</p><p>&nbsp;</p>";
        print_form_header("uploadercp", "killall", 0, 1, '', '75%');
        construct_hidden_code('userid', $vbulletin->GPC['userid']);
        construct_hidden_code('username', $user['username']);
        print_table_header(construct_phrase($vbphrase['confirm_delall_x'], $user['username']));
        print_description_row("
        <blockquote><br />
        " . construct_phrase($vbphrase["are_you_sure_want_to_remove_all_user_x_files"], $user['username']) . "
        <br /></blockquote>\n\t");
        print_submit_row($vbphrase['yes'], 0, 2, $vbphrase['no']);
}

elseif ($_REQUEST['do'] == "killall")
{
        $vbulletin->input->clean_array_gpc('r', array
        (
                'userid'      => TYPE_INT,
                'username'    => TYPE_STR
        ));

        $files = $db->query_read("SELECT id, fileurl FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->GPC['userid'] . "");

        if ($vbulletin->options['uploaderexternal'])
        {
                $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);
                @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);
        }

        while ($file = $db->fetch_array($files))
        {
                $filepath = str_replace($vbulletin->options['bburl'], '', $file['fileurl']);

                if ($vbulletin->options['uploaderexternal'])
                {
                      if($vbulletin->options['sfolder'])
                      $ftppath = $vbulletin->GPC['userid'] . '/';

                      if (@ftp_delete($conn_id, $ftppath . @basename($file['fileurl'])))
                      {
                              $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE id = " . $file['id'] . "");
                      }
                }
                elseif (@unlink(DIR . $filepath))
                {
                     $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE id = " . $file['id'] . "");
                }

        }

        if ($vbulletin->options['uploaderexternal'])
        {
                @ftp_close($conn_id);
        }

        print_cp_message(construct_phrase($vbphrase['done_removeall_x'], $vbulletin->GPC['username']), 'uploadercp.php?do=statistics', 5);
}

elseif ($_REQUEST['do'] == "exc")
{
        $uploadxs = $db->query_read("SELECT uploaderx.*, user.username
                    FROM " . TABLE_PREFIX . "uploaderx AS uploaderx
                    LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploaderx.userid = user.userid)
                    ");

        print_form_header('uploadercp','addx');
        print_table_header($vbphrase['uploaderx'], 8);

        $header = array();
        $header[] = $vbphrase['username'];
        $header[] = $vbphrase['no_active'];
        $header[] = $vbphrase['no_posts_needs'];
        $header[] = $vbphrase['no_file_size'];
        $header[] = $vbphrase['no_folder_size'];
        $header[] = $vbphrase['no_types_files'];
        $header[] = $vbphrase['no_digifile_name'];
        $header[] = "";
        print_cells_row($header, 1, 0, 1);
        $cell = array();

        while ($uploadx = $db->fetch_array($uploadxs))
        {
                $cell[] = "<a href='uploadercp.php?do=statistic&userid=" . $uploadx['userid'] . "' title='" . $vbphrase['info'] . "'>" . $uploadx['username'] . "</a>";
                $cell[] = $vbphrase[iif($uploadx['active'], 'yes', 'no')];
                $cell[] = $vbphrase[iif($uploadx['posts_needs'], 'yes', 'no')];
                $cell[] = $vbphrase[iif($uploadx['file_size'], 'yes', 'no')];
                $cell[] = $vbphrase[iif($uploadx['folder_size'], 'yes', 'no')];
                $cell[] = $vbphrase[iif($uploadx['types_files'], 'yes', 'no')];
                $cell[] = $vbphrase[iif($uploadx['digifilename'], 'yes', 'no')];
                $cell[] = "<a href='uploadercp.php?do=editexc&userid=" . $uploadx['userid'] . "'>" . $vbphrase['edit'] . "</a> <a href='uploadercp.php?do=delexc&userid=" . $uploadx['userid'] . "'>" . $vbphrase['remove'] . "</a>";
                print_cells_row($cell, 0, 0, 1, 1, 0, 1);
                unset($cell);
                $cell = array();
        }
        print_submit_row($vbphrase['add'], 0, 8);
        print_table_footer();
}

elseif ($_REQUEST['do'] == "addx" OR $_REQUEST['do'] == "editexc")
{
        $vbulletin->input->clean_gpc('r', 'userid', TYPE_INT);

        if ($_REQUEST['do'] == "editexc" AND $vbulletin->GPC['userid'])
        $user = $db->query_first("SELECT uploaderx.*, user.username
                 FROM " . TABLE_PREFIX . "uploaderx AS uploaderx
                 LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploaderx.userid = user.userid)
                 WHERE uploaderx.userid = " . $vbulletin->GPC['userid'] . "");

        print_form_header('uploadercp','savex');
        print_table_header($vbphrase['addexc']);
        print_input_row($vbphrase['userid'], 'userid', iif($vbulletin->GPC['userid'], $vbulletin->GPC['userid']));

        if ($_REQUEST['do'] == "editexc" AND $vbulletin->GPC['userid'])
        print_label_row($vbphrase['username'], $user['username']);

        construct_hidden_code('what', $_REQUEST['do']);
        print_yes_no_row($vbphrase['no_active'], 'active', $user['active']);
        print_yes_no_row($vbphrase['no_posts_needs'], 'posts_needs', $user['posts_needs']);
        print_yes_no_row($vbphrase['no_file_size'], 'file_size', $user['file_size']);
        print_yes_no_row($vbphrase['no_folder_size'], 'folder_size', $user['folder_size']);
        print_yes_no_row($vbphrase['no_types_files'], 'types_files', $user['types_files']);
        print_yes_no_row($vbphrase['no_digifile_name'], 'digifilename', $user['digifilename']);
        print_submit_row($vbphrase['save'], 0);
        print_table_footer();
}

elseif ($_REQUEST['do'] == "savex")
{
        $vbulletin->input->clean_array_gpc('r', array
        (
                'userid'       => TYPE_INT,
                'active'       => TYPE_BOOL,
                'posts_needs'  => TYPE_BOOL,
                'file_size'    => TYPE_BOOL,
                'folder_size'  => TYPE_BOOL,
                'types_files'  => TYPE_BOOL,
                'digifilename' => TYPE_BOOL,
                'what'         => TYPE_STR,
        ));

        if (!$vbulletin->GPC['userid'])
        {
                print_cp_message($vbphrase['what_to_save']);
        }

        $usere = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "uploaderx WHERE userid = " . $vbulletin->GPC['userid'] . "");
        $user = $db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC['userid'] . "");

        if(!$usere['userid'])
        {
                $db->query_write("INSERT INTO " . TABLE_PREFIX . "uploaderx(userid, active, posts_needs, file_size, folder_size, types_files, digifilename) VALUES (" . $vbulletin->GPC['userid'] . ", " . $vbulletin->GPC['active'] . ", " . $vbulletin->GPC['posts_needs'] . ", " . $vbulletin->GPC['file_size'] . ", " . $vbulletin->GPC['folder_size'] . ", " . $vbulletin->GPC['types_files'] . ", " . $vbulletin->GPC['digifilename'] . ")");
                print_cp_message(construct_phrase($vbphrase['done_x_addx'], $user['username']), 'uploadercp.php?do=exc', 1);
        }
        elseif($usere['userid'] AND $vbulletin->GPC['what'] != 'editexc')
        {
                print_cp_message($vbphrase['duplicate'], 'uploadercp.php?do=editexc&userid=' . $vbulletin->GPC['userid'] . '', 5);
        }
        elseif($vbulletin->GPC['what'] == 'editexc')
        {
                $db->query_write("UPDATE " . TABLE_PREFIX . "uploaderx SET userid = " . $vbulletin->GPC['userid'] . ", active = " . $vbulletin->GPC['active'] . ", posts_needs = " . $vbulletin->GPC['posts_needs'] . ", file_size = " . $vbulletin->GPC['file_size'] . ", folder_size = " . $vbulletin->GPC['folder_size'] . ", types_files = " . $vbulletin->GPC['types_files'] . ", digifilename = " . $vbulletin->GPC['digifilename'] . " WHERE userid = " . $vbulletin->GPC['userid'] . "");
                print_cp_message(construct_phrase($vbphrase['done_x_editx'], $user['username']), 'uploadercp.php?do=exc', 1);
        }
}

elseif ($_REQUEST['do'] == "delexc")
{
        $vbulletin->input->clean_gpc('r', 'userid', TYPE_INT);

        $user = $db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC['userid'] . "");

        echo "<p>&nbsp;</p><p>&nbsp;</p>";
        print_form_header("uploadercp", "killexc", 0, 1, '', '75%');
        construct_hidden_code('userid', $vbulletin->GPC['userid']);
        print_table_header(construct_phrase($vbphrase['confirm_unexc_x'], $user['username']));
        print_description_row("
        <blockquote><br />
        " . construct_phrase($vbphrase["are_you_sure_want_to_remove_user_x"], $user['username'], $vbulletin->GPC['userid']) . "
        <br /></blockquote>\n\t");
        print_submit_row($vbphrase['yes'], 0, 2, $vbphrase['no']);
}

elseif ($_REQUEST['do'] == "killexc")
{
        $vbulletin->input->clean_gpc('r', 'userid', TYPE_INT);

        $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploaderx WHERE userid = " . $vbulletin->GPC['userid'] . "");
        print_cp_message($vbphrase['done_removedx'], 'uploadercp.php?do=exc', 1);
}

elseif ($_REQUEST['do'] == "statistics")
{
        $db->query_write("TRUNCATE TABLE " . TABLE_PREFIX . "uploaderstatistic");
        $uploaders = $db->query_read("SELECT uploader.*, user.username
                     FROM " . TABLE_PREFIX . "uploader AS uploader
                     LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploader.userid = user.userid)
                     ORDER BY uploader.userid");
        $size = 0;
        while ($uploader = $db->fetch_array($uploaders))
        {
           if($befor != $uploader['userid'])
           {
               $db->query_write("INSERT INTO " . TABLE_PREFIX . "uploaderstatistic(userid, lastupload, filesize, fileurl, dateline) VALUES ('" . $uploader['userid'] . "', '" . $db->escape_string($uploader['file_name']) . "'," . $uploader['file_size'] . ",'" . $uploader['fileurl'] . "'," . $uploader['dateline'] . ")");

               $cont = 0;
               $contitsel = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "uploader WHERE userid=" . $uploader['userid'] . "");
               while ($contitse=$db->fetch_array($contitsel))
               {
                     $cont++;
               }
           }

           $files++;
           $size += $uploader['file_size'];
           $befor = $uploader['userid'];

           if($contbefor < $cont)
           {
               $contbefor = $cont;
               $maxuser = $uploader['username'];
               $maxuserid = $uploader['userid'];
           }
        }

        $users = $db->query_first("SELECT COUNT(*) AS uploaders FROM " . TABLE_PREFIX . "uploaderstatistic");

        print_form_header();
        print_table_header($vbphrase['uploader'] . ' - ' . $vbphrase['statistics']);

        print_label_row(construct_phrase($vbphrase['all_files_x'], $files));
        print_label_row(construct_phrase($vbphrase['uploader_user_x'], $users['uploaders']));
        print_label_row(construct_phrase($vbphrase['all_files_size_x'], vb_number_format($size, 1 , true)));
        print_label_row(construct_phrase($vbphrase['server_upload_size_x'], vb_number_format(ini_get('upload_max_filesize'), 1 , true)));
        print_label_row(construct_phrase($vbphrase['most_user_x'], "<a href='uploadercp.php?do=statistic&userid=" . $maxuserid . "'  title='" . $vbphrase['info'] . "'>" . $maxuser . '</a>'));

        if($vbulletin->options['sfolder'] AND ini_get('safe_mode') == 1 OR strtolower(ini_get('safe_mode')) == 'on')
        print_label_row($vbphrase['uploader_safe_mode']);

        print_table_footer();

        $last5uplaod = $db->query_read("SELECT uploader.*, user.username
                       FROM " . TABLE_PREFIX . "uploader AS uploader
                       LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploader.userid = user.userid)
                       $userfilse
                       ORDER BY uploader.dateline DESC LIMIT 0, 5");

        print_form_header();
        print_table_header($vbphrase['last5uplaod'], 5);

        $header = array();
        $header[] = $vbphrase['file_name'];
        $header[] = $vbphrase['file_size'];
        $header[] = $vbphrase['users'];
        $header[] = $vbphrase['date'];
        $header[] = $vbphrase['last_activity'];
        print_cells_row($header, 1, 0, 1);
        $cell = array();

        while ($last = $db->fetch_array($last5uplaod))
        {
           $last['file_description'] = iif($last['description'], $last['description'], $last['file_name']);
           $cell[] = "<a dir=ltr href='" . $last['fileurl'] . "' target='_blank' title='" . $last['file_description'] . "'>" . iif(strlen($last['file_name']) > 30 ,substr($last['file_name'], 0, 30) . "...", $last['file_name']) . "</a>";
           $cell[] = vb_number_format($last['file_size'], 1 , true);
           $cell[] = "<a href='uploadercp.php?do=statistic&userid=" . $last['userid'] . "' target='_blank'>" . $last['username'] . "</a>";
           $cell[] = vbdate($vbulletin->options['dateformat'], $last['dateline'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $last['dateline']);
           $cell[] = "<a href=uploadercp.php?do=delfile&id=" . $last['id'] . ">" . $vbphrase['delete'] . "</a> <a href=uploadercp.php?do=ignfile&id=" . $last['id'] . ">" . $vbphrase['ignoreup'] . "</a>";
           print_cells_row($cell, 0, 0, 1, 1, 0, 1);
           unset($cell);
           $cell = array();
        }

        print_table_footer();

        $big5uplaod = $db->query_read("SELECT uploader.*, user.username
                      FROM " . TABLE_PREFIX . "uploader AS uploader
                      LEFT JOIN " . TABLE_PREFIX . "user AS user ON(uploader.userid = user.userid)
                      $userfilse
                      ORDER BY file_size DESC LIMIT 0, 5");

        print_form_header();
        print_table_header($vbphrase['big5uplaod'], 5);

        $header = array();
        $header[] = $vbphrase['file_name'];
        $header[] = $vbphrase['file_size'];
        $header[] = $vbphrase['users'];
        $header[] = $vbphrase['date'];
        $header[] = $vbphrase['last_activity'];
        print_cells_row($header, 1, 0, 1);
        $cell = array();

        while ($big = $db->fetch_array($big5uplaod))
        {
           $big['file_description'] = iif($big['description'], $big['description'], $big['file_name']);
           $cell[] = "<a dir=ltr href='" . $big['fileurl'] . "' target='_blank' title='" . $big['file_description'] . "'>" . iif(strlen($big['file_name']) > 30 ,substr($big['file_name'], 0, 30) . "...", $big['file_name']) . "</a>";
           $cell[] = vb_number_format($big['file_size'], 1 , true);
           $cell[] = "<a href='uploadercp.php?do=statistic&userid=" . $big['userid'] . "' target='_blank'>" . $big['username'] . "</a>";
           $cell[] = vbdate($vbulletin->options['dateformat'], $big['dateline'], 1) . ' ' . vbdate($vbulletin->options['timeformat'], $big['dateline']);
           $cell[] = "<a href=uploadercp.php?do=delfile&id=" . $big['id'] . ">" . $vbphrase['delete'] . "</a> <a href=uploadercp.php?do=ignfile&id=" . $big['id'] . ">" . $vbphrase['ignoreup'] . "</a>";
           print_cells_row($cell, 0, 0, 1, 1, 0, 1);
           unset($cell);
           $cell = array();
        }

        print_table_footer();
}

print_cp_footer();
/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile: uploadercp.php,v $ - $Revision: 3.3.4 $
|| ####################################################################
\*======================================================================*/
?>