<?php
/*======================================================================*\
|| #################################################################### ||
|| #  ãÑßÒ ÑÝÚ ÇáãáÝÇÊ  uploade v 3.3                     # ||
|| #              for vBulletin Version 3.5.x                         # ||
|| #              http://7beebi.com    ãæÞÚ ÍÈíÈí                     # ||
|| #              webmaster@7beebi.com                                # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
define('THIS_SCRIPT', 'uploader');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('uploader');
$specialtemplates = array();
// pre-cache templates used by all actions
$globaltemplates = array(
        'uploader',
        'uploader_files',
        'uploader_filebit',
        'uploader_msg',
        'uploader_editor',
        'uploader_editor_msg',
        'uploader_rules'
);
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./uploaderglobal.php');
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
($hook = vBulletinHook::fetch_hook('uploader_main_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
      $vbulletin->input->clean_gpc('r','page', TYPE_INT);
      $page = $vbulletin->GPC['page'];
      $perpage = $vbulletin->options['upsperpage'];

      ($hook = vBulletinHook::fetch_hook('uploader_display_start')) ? eval($hook) : false;

      $countups = $db->query_first("
                  SELECT COUNT(*) AS ups
                  FROM " . TABLE_PREFIX . "uploader AS uploader
                  WHERE userid=" . $vbulletin->userinfo['userid'] . "
                  ");

      if ($page < 1)
      {
              $page = 1;
      }
      else if ($page > ceil(($countups['ups'] + 1) / $perpage))
      {
              $page = ceil(($countups['ups'] + 1) / $perpage);
      }

      $limitlower = ($page - 1) * $perpage;

      $userfiles = $db->query_read("
                   SELECT *
                   FROM " . TABLE_PREFIX . "uploader
                   WHERE userid=" . $vbulletin->userinfo['userid'] . "
                   ORDER BY dateline DESC
                   LIMIT $limitlower, $perpage
                   ");

      while ($userfile = $db->fetch_array($userfiles))
      {
          $userfile['file_description'] = iif($userfile['description'],$userfile['description'],$userfile['file_name']);
          $userfile['file_name'] = iif(strlen($userfile['file_name']) > 30 ,substr($userfile['file_name'], 0, 30) . "...", $userfile['file_name']);
          $userfile['file_size'] = vb_number_format($userfile['file_size'], 1 , true);

          eval('$files .= "' . fetch_template('uploader_filebit') . '";');
      }

      if ($files)
      {
          $next = construct_page_nav($page, $perpage, $countups['ups'], "uploader.php?");
          eval('$myfiles = "' . fetch_template('uploader_files') . '";');
      }

      ($hook = vBulletinHook::fetch_hook('uploader_display_complete')) ? eval($hook) : false;

      eval('print_output("' . fetch_template('uploader') . '");');
}

elseif ($_REQUEST['do'] == 'editor')
{
      ($hook = vBulletinHook::fetch_hook('uploader_editor_start')) ? eval($hook) : false;

      eval('print_output("' . fetch_template('uploader_editor') . '");');

      ($hook = vBulletinHook::fetch_hook('uploader_editor_complete')) ? eval($hook) : false;
}

elseif ($_REQUEST['do'] == 'doupload')
{
      $vbulletin->input->clean_gpc('f', 'file', TYPE_FILE);

      $vbulletin->input->clean_gpc('p', 'description', TYPE_NOHTML);

      ($hook = vBulletinHook::fetch_hook('uploader_upload_start')) ? eval($hook) : false;

      $erorr = 1;

      $vbulletin->GPC['file']['name'] = strtolower($vbulletin->GPC['file']['name']);

      $tempnmae = preg_replace(array('/php/', '/htm/', '/cgi/'), '', $vbulletin->GPC['file']['name']);

      if($vbulletin->options['banduploader'] AND $tempnmae != $vbulletin->GPC['file']['name'] AND !in_array($vbulletin->userinfo['userid'], preg_split('#\s*,\s*#s', $vbulletin->config['SpecialUsers']['undeletableusers'], -1, PREG_SPLIT_NO_EMPTY)))
      {
              $db->query_write("UPDATE " . TABLE_PREFIX . "user SET usergroupid = " . $vbulletin->options['banduploader'] . " WHERE userid = " . $vbulletin->userinfo['userid'] . "");
              print_no_permission();
      }

      $ext = substr(strrchr($vbulletin->GPC['file']['name'], '.'), 1);

      if($ext == 'txt')
      {
              $vbulletin->GPC['file']['name'] = strrev(substr(strrchr(strrev($vbulletin->GPC['file']['name']), '.'), 1)) . ".doc";
              $ext ='doc' ;
      }

      $filerealname = $vbulletin->GPC['file']['name'];

      if($vbulletin->options['digifilename'] AND !$uploaderx['digifilename'])
      {
              $vbulletin->GPC['file']['name'] = TIMENOW . '.' . $ext;
      }

      if (!$vbulletin->options['sfolder'])
      {
              $vbulletin->GPC['file']['name'] = $vbulletin->userinfo['userid'] . '_' . $vbulletin->GPC['file']['name'];
      }

      if (!is_uploaded_file($vbulletin->GPC['file']['tmp_name']))
      {
            $msg = $vbphrase['no_file'];
      }
      elseif ($fileisin = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND file_size = " . $vbulletin->GPC['file']['size'] . " AND file_name LIKE '%" . $db->escape_string($ext) . "%'"))
      {
            $msg = $vbphrase['file_exists'] . "<br /><a href='" . $fileisin['fileurl'] . "' target='_blank'>" . $fileisin['fileurl'] . "</a>";
            $fileurl = $fileisin['fileurl'];
            $erorr = 0;
      }
      elseif (!in_array($ext, $types) AND !$uploaderx['types_files'])
      {
            $msg = construct_phrase($vbphrase['no_ext_x'], $ext);
      }
      elseif ($vbulletin->GPC['file']['size'] == 0)
      {
            $msg = $vbphrase['size_none'];
      }
      elseif ($vbulletin->GPC['file']['size'] > $size AND !$uploaderx['file_size'])
      {
            $msg = construct_phrase($vbphrase['no_size_x'], $sizetmp, vb_number_format($vbulletin->GPC['file']['size'], 1 , true));
      }
      elseif ($sizecont + $vbulletin->GPC['file']['size'] > $folder_size AND !$uploaderx['folder_size'] AND $uploaderperm['uploadermaxfoldesize'])
      {
            $msg = $vbphrase['reach_size'];
      }
      else
      {
            if ($vbulletin->options['sfolder'] AND !@is_dir($dirpath) AND $vbulletin->userinfo['userid'] != 0)
            {
                  if ($vbulletin->options['uploader_ftp'])
                  {
                           // set up basic connection
                           $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);

                           // login with username and password
                           @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);

                           // try to create the directory $dirpath
                           @ftp_mkdir($conn_id, $vbulletin->userinfo['userid']);

                           // change $dirpath's permissions  to 0777
                           @ftp_site($conn_id, 'CHMOD 0777 ' . $vbulletin->userinfo['userid']);

                           if(!$vbulletin->options['uploaderexternal'])
                           {
                                   // I have't finde any awy to make index.html in an external server
                                   $filehandle = @fopen($dirpath . '/index.html', 'w');
                                   @fwrite($filehandle, "\n\n");
                                   @fclose($filehandle);
                           }
                           else
                           {
                                   $file = 'index.html';
                                   $fp = @fopen(DIR . '/includes/index.html', 'r');
                                   @ftp_fput($conn_id, $ftppath . $file, $fp, FTP_BINARY);
                           }

                           // close the FTP stream
                           @ftp_close($conn_id);
                  }
                  elseif (!$vbulletin->options['uploader_ftp'])
                  {
                           @mkdir($dirpath, 0777);
                           $filehandle = @fopen($dirpath . '/index.html', 'w');
                           @fwrite($filehandle, "\n\n");
                           @fclose($filehandle);
                  }
            }

            if ($vbulletin->options['uploaderexternal'])
            {
                    $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);
                    @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);

                    if (!@ftp_put($conn_id, $ftppath . $vbulletin->GPC['file']['name'], $vbulletin->GPC['file']['tmp_name'], FTP_BINARY))
                    {
                         $msg = $vbphrase['bad_uploader'] . '<br /><a href="sendmessage.php?do=contactus&message=' . construct_phrase($vbphrase['contact_us_upload'], $vbulletin->options['bbtitle'], $vbulletin->GPC['file']['name'], vb_number_format($vbulletin->GPC['file']['size'], 1 , true), $vbulletin->userinfo['username']) . '">' . $vbphrase['contact_us'] . '</a>';
                         ($hook = vBulletinHook::fetch_hook('uploader_upload_failed')) ? eval($hook) : false;
                    }
                    else
                    {
                         $db->query_write("INSERT INTO " . TABLE_PREFIX . "uploader
                         (userid,file_name,file_size,fileurl,dateline, description)
                         VALUES ('" . $vbulletin->userinfo['userid'] . "','" . $db->escape_string($filerealname) . "'," . $vbulletin->GPC['file']['size'] . ",'" . $vbulletin->options['uploaderexternalurl'] . "/" . $ftppath . $db->escape_string($vbulletin->GPC['file']['name']) . "'," . TIMENOW . ", '" . $db->escape_string($vbulletin->GPC['description']) . "')");
                         //@ftp_chmod($conn_id, 0755, $ftppath . $vbulletin->GPC['file']['name']);  not workin cuse the FTP server
                         $msg = '' . $vbphrase['done_upload']. '<br /><a href=' . $vbulletin->options['uploaderexternalurl'] . "/" . $ftppath . $vbulletin->GPC['file']['name'] . ' target="_blank">' . $vbulletin->options['uploaderexternalurl'] . '/' . $ftppath . $vbulletin->GPC['file']['name'] . '</a><br />' . $vbphrase['no_ext'] . ' ' . $ext . '';
                         $fileurl = $vbulletin->options['uploaderexternalurl'] . '/' . $ftppath . $vbulletin->GPC['file']['name'];
                         $erorr = 0;
                         ($hook = vBulletinHook::fetch_hook('uploader_upload_success')) ? eval($hook) : false;
                    }

                    @ftp_close($conn_id);
            }
            else
            {
                    if (!@move_uploaded_file($ftppath . $vbulletin->GPC['file']['tmp_name'], '' . $dirpath . '/' . $vbulletin->GPC['file']['name'] . ''))
                    {
                         $msg = $vbphrase['bad_uploader'] . '<br /><a href="sendmessage.php?do=contactus&message=' . construct_phrase($vbphrase['contact_us_upload'], $vbulletin->options['bbtitle'], $vbulletin->GPC['file']['name'], vb_number_format($vbulletin->GPC['file']['size'], 1 , true), $vbulletin->userinfo['username']) . '">' . $vbphrase['contact_us'] . '</a>';
                         ($hook = vBulletinHook::fetch_hook('uploader_upload_success')) ? eval($hook) : false;
                    }
                    else
                    {
                         $db->query_write("INSERT INTO " . TABLE_PREFIX . "uploader
                         (userid,file_name,file_size,fileurl,dateline, description)
                         VALUES ('" . $vbulletin->userinfo['userid'] . "','" . $db->escape_string($filerealname) . "'," . $vbulletin->GPC['file']['size'] . ",'" . $vbulletin->options['bburl'] . "/" . $path . $db->escape_string($vbulletin->GPC['file']['name']) . "','" . TIMENOW . "', '" . $db->escape_string($vbulletin->GPC['description']) . "')");
                         @chmod('' . $dirpath . '/' . $vbulletin->GPC['file']['name'] . '', 0755);
                         $msg = '' . $vbphrase['done_upload']. '<br /><a href=' . $path . $vbulletin->GPC['file']['name'] . ' target="_blank">' . $vbulletin->options['bburl'] . '/' . $path . $vbulletin->GPC['file']['name'] . '</a><br />' . $vbphrase['no_ext'] . ' ' . $ext . '';
                         $fileurl = $vbulletin->options['bburl'] . '/' . $path . $vbulletin->GPC['file']['name'];
                         $erorr = 0;
                         ($hook = vBulletinHook::fetch_hook('uploader_upload_failed')) ? eval($hook) : false;
                    }
            }
      }

      if(!$erorr)
      {
              $msgview = '';
              $msgcode = '';

              switch ($ext)
              {
                   case 'gif':
                   case 'jpg':
                   case 'jpeg':
                   case 'jpe':
                   case 'png':
                   case 'bmp':

                         if (@getimagesize($fileurl) OR !$vbulletin->options['uploadergdcheck'])
                         {
                                 $msgview = '<img src="' . $fileurl . '" border="0" alt="" />';
                                 $msgcode = '[IMG]' . $fileurl . '[/IMG]';
                         }
                         else
                         {
                                 if ($vbulletin->options['uploaderexternal'])
                                 {
                                         $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);
                                         @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);

                                         if(@ftp_delete($conn_id, $ftppath . $vbulletin->GPC['file']['name']))
                                         $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND fileurl = '" . $db->escape_string($fileurl) . "'");

                                         @ftp_close($conn_id);
                                 }
                                 else
                                 {
                                         if (@unlink('' . $dirpath . '/' . $vbulletin->GPC['file']['name'] . ''));
                                         $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND fileurl = '" . $db->escape_string($fileurl) . "'");
                                 }

                                 $msg = $vbphrase['uploader_check_img_failed'];
                                 $erorr = 1;
                         }

                         break;

                   case 'swf':

                         if (@getimagesize($fileurl) OR !$vbulletin->options['uploadergdcheck'])
                         {
                                 $msgview = '<embed src="' . $fileurl . '" ' . $swfinfo[3] . '  quality="high" loop="false" menu="false" TYPE="application/x-shockwave-flash" wmode="transparent"  AllowScriptAccess="never" nojava="true" />';
                                 $msgcode = '[FLASH=' . $fileurl . ']width=' . $swfinfo[0] . ' height=' . $swfinfo[1] . '[/FLASH]';
                         }
                         else
                         {
                                 if ($vbulletin->options['uploaderexternal'])
                                 {
                                         $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);
                                         @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);

                                         if(@ftp_delete($conn_id, $ftppath . $vbulletin->GPC['file']['name']))
                                         $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND fileurl = '" . $db->escape_string($fileurl) . "'");

                                         @ftp_close($conn_id);
                                 }
                                 else
                                 {
                                         if (@unlink('' . $dirpath . '/' . $vbulletin->GPC['file']['name'] . ''));
                                         $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND fileurl = '" . $db->escape_string($fileurl) . "'");
                                 }

                                 $msg = $vbphrase['uploader_check_flash_failed'];
                                 $erorr = 1;
                         }

                         break;

                   case 'rm':
                   case 'ra':
                   case 'ram':

                         $msgview = '<embed SRC="' . $fileurl . '" type="audio/x-pn-realaudio-plugin" CONSOLE="Clip1" CONTROLS="ImageWindow,ControlPanel,StatusBar" HEIGHT="230" WIDTH="300" AUTOSTART="false" AllowScriptAccess="never" nojava="true" />';
                         $msgcode = '[RAMS]' . $fileurl . '[/RAMS]';
                         break;

                   case '3gp':
                   case 'rmvb':

                         $msgview = '<embed SRC="' . $fileurl . '" type="audio/x-pn-realaudio-plugin" CONSOLE="Clip1" CONTROLS="ImageWindow,ControlPanel,StatusBar" HEIGHT="230" WIDTH="300" AUTOSTART="false" AllowScriptAccess="never" nojava="true" />';
                         $msgcode = '[RAMV]' . $fileurl . '[/RAMV]';
                         break;

                   case 'mp3':
                   case 'mpg':
                   case 'mpeg':
                   case 'wave':
                   case 'mid':
                   case 'avi':
                   case 'wmv':
                   case 'asf':
                   case 'dat':

                          $msgview = '<object width="30%" classid="clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6" id="PTMediaPlayer">
                          <param name="URL" value="' . $fileurl . '" />
                          <param name="rate" value="1" />
                          <param name="currentPosition" value="0" />
                          <param name="playCount" value="1" />
                          <param name="autoStart" value="0" />
                          <param name="uiMode" value="mini" />
                          <param name="stretchToFit" value="-1" />
                          <param name="enableContextMenu" value="-1" />
                          </object>';
                          $msgcode = '[MEDIA]' . $fileurl . '[/MEDIA]';

                          break;

                  ($hook = vBulletinHook::fetch_hook('uploader_undefind_extension')) ? eval($hook) : false;

                  default:
                          $msgcode = $fileurl;
              }

              if(!$vbulletin->options['uploaderreadytag'])
              $msgcode = $fileurl;
      }

      ($hook = vBulletinHook::fetch_hook('uploader_upload_complete')) ? eval($hook) : false;

      eval('print_output("' . fetch_template('uploader' . $upeditor . '_msg') . '");');
}

elseif ($_REQUEST['do'] == 'delfile')
{
     if (!($permissions['uploaderperm'] & $vbulletin->bf_ugp['uploaderperm']['candeluploadedfiles']))
     {
             print_no_permission();
     }

     $vbulletin->input->clean_gpc('r', 'id', TYPE_STR);

     $erorr = 1;

     $filedb = $db->query_first("SELECT fileurl FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND id = '" . $db->escape_string($vbulletin->GPC['id']) . "'");

     ($hook = vBulletinHook::fetch_hook('uploader_delfile_start')) ? eval($hook) : false;

     if(!$filedb['fileurl'])
     {
             eval(standard_error(fetch_error('noid', $vbphrase['file'], 'sendmessage.php')));
     }

     $file = str_replace($vbulletin->options['bburl'], '', $filedb['fileurl']);

     if ($vbulletin->options['uploaderexternal'])
     {
           $conn_id = @ftp_connect($vbulletin->options['uploader_ftp_url']);
           @ftp_login($conn_id, $vbulletin->options['uploader_ftp_user'], $vbulletin->options['uploader_ftp_password']);
           if (@ftp_delete($conn_id, $ftppath . @basename($filedb['fileurl'])))
           {
                   $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND fileurl = '" . $db->escape_string($filedb['fileurl']) . "'");
                   ($hook = vBulletinHook::fetch_hook('uploader_delfile_success')) ? eval($hook) : false;
                   $msg = $vbphrase['done_delete'];
                   $erorr = 0;
           }
           @ftp_close($conn_id);
     }
     elseif (@unlink(DIR . $file))
     {
           $db->query_write("DELETE FROM " . TABLE_PREFIX . "uploader WHERE userid = " . $vbulletin->userinfo['userid'] . " AND fileurl = '" . $db->escape_string($filedb['fileurl']) . "'");
           ($hook = vBulletinHook::fetch_hook('uploader_delfile_success')) ? eval($hook) : false;
           $msg = $vbphrase['done_delete'];
           $erorr = 0;
     }

     ($hook = vBulletinHook::fetch_hook('uploader_delfile_failed')) ? eval($hook) : false;

     if($erorr)
     {
           $msg = $vbphrase['not_delete'];
     }

     eval('print_output("' . fetch_template('uploader' . $upeditor . '_msg') . '");');
}

($hook = vBulletinHook::fetch_hook('uploader_main_complete')) ? eval($hook) : false;

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile: uploader.php,v $ - $Revision: 3.3.4 $
|| ####################################################################
\*======================================================================*/
?>