<?php
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/
defined('CWD1') or exit;
chdir(CWD1);
chdir('../');
// ####################### SET PHP ENVIRONMENT ###########################

@set_time_limit(0);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('GET_EDIT_TEMPLATES', true);
define('THIS_SCRIPT', 'newattachment');
define('CSRF_PROTECTION', false);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('posting');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(

);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/functions_file.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

function attach_image_func($xmlrpc_params)
{

	global $vbulletin;
	global $db;
	global $xmlrpcerruser;
	global $forumperms,$permissions;
	chdir(CWD1);
	chdir('../');


	$decode_params = php_xmlrpc_decode($xmlrpc_params);

	$image     = isset($decode_params[0]) ? $decode_params[0] : '';
	$name      = isset($decode_params[1]) ? $decode_params[1] : '';
	$type      = isset($decode_params[2]) ? $decode_params[2] : 'image/jpeg';
	$forumid  = isset($decode_params[3]) ? $decode_params[3] : '';
	if (!$vbulletin->userinfo['userid']) // Guests can not post attachments
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}

	// Variables that are reused in templates


	$foruminfo = mobiquo_verify_id('forum', $forumid, 1, 1);
	if ($forumid)
	{
		$forumid = mobiquo_verify_id('forum', $forumid);
	}

	$forumperms = fetch_permissions($foruminfo['forumid']);

	// No permissions to post attachments in this forum or no permission to view threads in this forum.
	if (empty($vbulletin->userinfo['attachmentextensions']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canpostattachment']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}

	if ((!$postid AND !$foruminfo['allowposting']) OR $foruminfo['link'] OR !$foruminfo['cancontainthreads'])
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}

	if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canpostnew'])) // newthread.php
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}
	error_reporting(0);
	$vbulletin->options['safeupload'] = false;
	if($vbulletin->options['attachfile']){
		$tmpfname = tempnam(ini_get("upload_tmp_dir"), 'php');
		$tmp_file = fopen ($tmpfname, "w");

		$tmp_size =  fwrite($tmp_file, $image);
		fclose($tmp_file);
	} else {
		$tmp_file = tmpfile();
			
		$tmp_size  = fwrite($tmp_file, $image);
		$meta  = stream_get_meta_data ($tmp_file) ;
		$tmpfname = $meta['uri'];
	}



	$tmpfsize =  filesize($tmpfname) ;
	if($tmpfsize == 0){
		if(file_exists($tmpfname)){
			@unlink($tmpfname);
		}
		$tmpfname = tempnam(CWD1, '');

		$tmp_file = fopen ($tmpfname, "w");

		$tmp_size =  fwrite($tmp_file, $image);
		fclose($tmp_file);
		$tmpfsize =  filesize($tmpfname) ;

	}


	$tmpfsize =  filesize($tmpfname) ;
	$_FILES['fileupload'] = array(
        'name' => $name,                           
        'type' => $type,            
        'tmp_name' => $tmpfname,                   
        'error' => 0,                              
        'size' => $tmpfsize             
	);

	$_POST['add_file'] = true;

	$parentattach = '';
	$parentclickattach = '';
	$new_attachlist_js = '';

	// check if there is a forum password and if so, ensure the user has it set
	verify_forum_password($foruminfo['forumid'], $foruminfo['password']);


	$posthash  = md5(TIMENOW . $vbulletin->userinfo['userid'] . $vbulletin->userinfo['salt']);

	$show['errors'] = false;


	$attachcount = $currentattaches['count'];



	$show['postowner'] = false;
	$attach_username = $vbulletin->userinfo['username'];


	if (!$foruminfo['allowposting'] AND !$attachcount)
	{
		$return = array(20,'security error (user may not have permission to access this feature)');
		return return_fault($return);
	}


	$errors = array();
	require_once(CWD1."/include/mobiquo_class_upload.php");
	require_once(DIR . '/includes/class_image.php');


	$postinfo = array('posthash' => $posthash);


	// check for any funny business
	$filecount = 1;
	if (!empty($vbulletin->GPC['attachment']['tmp_name']))
	{
		foreach ($vbulletin->GPC['attachment']['tmp_name'] AS $filename)
		{
			if (!empty($filename))
			{
				if ($filecount > $vbulletin->options['attachboxcount'])
				{
					@unlink($filename);
				}
				$filecount++;
			}
		}
	}


	// These are created each go around to insure memory has been freed
	$attachdata =& datamanager_init('Attachment', $vbulletin, ERRTYPE_ARRAY);
	$upload =& new vB_Upload_Attachment($vbulletin);
	$image =& vB_Image::fetch_library($vbulletin);

	$upload->data =& $attachdata;
	$upload->image =& $image;
	if ($uploadsum > 1)
	{
		$upload->emptyfile = false;
	}

	$upload->foruminfo =& $foruminfo;



	$upload->postinfo =& $postinfo;


	$attachment = array(
					'name'     =>& $name,
					'tmp_name' =>& $tmpfname,
					'error'    =>	0,
					'size'     =>& $tmpfsize,
	);



	if (!$foruminfo['allowposting'])
	{
		$error = $vbphrase['this_forum_is_not_accepting_new_attachments'];
		$errors[] = array(
					'filename' => $attachment['name'],
					'error'    => $error
		);
	}
	else if ($vbulletin->options['attachlimit'] AND $attachcount > $vbulletin->options['attachlimit'])
	{
		$error = construct_phrase($vbphrase['you_may_only_attach_x_files_per_post'], $vbulletin->options['attachlimit']);
		$errors[] = array(
					'filename' => $attachment['name'],
					'error'    => $error
		);
	}
	else
	{


		if ($attachmentid = $upload->process_upload($attachment))
		{
			if ($vbulletin->userinfo['userid'] != $postinfo['userid'] AND can_moderate($threadinfo['forumid'], 'caneditposts'))
			{
				$postinfo['attachmentid'] =& $attachmentid;
				$postinfo['forumid'] =& $foruminfo['forumid'];
				require_once(DIR . '/includes/functions_log_error.php');
				log_moderator_action($postinfo, 'attachment_uploaded');
			}
		}
		else
		{
			$attachcount--;
		}

		if ($error = $upload->fetch_error())
		{
			$errors[] = array(
						'filename' => is_array($attachment) ? $attachment['name'] : $attachment,
						'error'    => $error,
			);
		}

	}
	if(file_exists($tmpfname)){
		@unlink($tmpfname);
	}
	if (!empty($errors))
	{
		$errorlist = '';
		foreach ($errors AS $error)
		{
			$filename = htmlspecialchars_uni($error['filename']);
			$errorlist .= $error['error'];
		}
		$show['errors'] = true;
		$return = array(18,$errorlist);
		return return_fault($return);
	} else {
		return new xmlrpcresp(
		new xmlrpcval(
		array(
                                        'attachment_id' => new xmlrpcval($posthash,'string'),
		),
                                'struct'
                                )
                                );
	}
}


?>