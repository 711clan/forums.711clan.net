<?php
/*======================================================================*\
|| #################################################################### ||
|| # VB Image hosting 1.0.0 - FOR VB 3.6.x By Waiel Eid AKA Ranma2k	  # ||
|| # ---------------------------------------------------------------- # ||
|| # Relase date 8 Aug 2006| All right reserved to the auther you may # ||
|| # Not redistribute this code without a permission from the auther  # ||
|| # Only Valid Vbulletin users can use this script.	  	      	  # ||
|| # Hack Modified on 8th Aug 2006			  	     			 	  # ||
|| # ---------------- VB Image Hosting  Hack ------------------------ # ||
|| #      http://www.waieleid.com | http://www.animeotaku.info	      # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
// change the line below to the actual filename without ".php" extention.
// the reason for using actual filename without extention as a value of this constant is to ensure uniqueness of the value throughout every PHP file of any given vBulletin installation.
$vbimghost['ver']="1.0.0";

define('THIS_SCRIPT', 'vbimghost'); 

// #################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
        'vbimghost_main',
        'vbimghost_users',
        'vbimghost_upload',
        'vbimghost_imgbits',
        'vbimghost_imgshow',
        'vbimghost_filmsbit',
        'vbimghost_upfields',
        'vbimghost_usersbit',
        'vbimghost_noimgbit',
		'vbimghost_nouserbit',
		'vbimghost_imgrowbit',
		'vbimghost_displayimg',
		'vbimghost_imgbits_poprow',
		'vbimghost_imgbits_popmain',
		'vbimghost_popupload_pre',
		'vbimghost_imgcolbit'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/vbimghost_include.php');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$vbulletin->input->clean_array_gpc('r', array(
	'id' 	=> TYPE_UINT,
	'pp'	=> TYPE_UINT,
	'page'	=> TYPE_UINT,
	'imgid'	=> TYPE_UINT
	)
);

// for Forums using content management systems 
$vbulletin->GPC['do']=$_REQUEST['do'];

switch ($vbulletin->GPC['do']){
	case 'myimages':
	case 'userlist':
	case 'upload':
	case 'updateimg':
	case 'uploadfile':
	case 'deleteimage':
	case 'viewimages':
	case 'displayimg':
	case 'popupload':
	case 'popup':
		break;
	default:
		$vbulletin->GPC['do']='myimages';
}

// global variabeles 
$vbimghost['iperpage'] 		= $vbulletin->options['imgperpage'];
$vbimghost['uperpage'] 		= $vbulletin->options['usersperpage'];
$vbimghost['imgperrow']		= $vbulletin->options['vbimghost_imgperrow'];
$vbimghost['forumpath'] 	= $vbulletin->options['vbimghost_forumpath'];
$vbimghost['filmstripon']	= $vbulletin->options['vbimghost_filmstrip'];
$vbimghost['filmstripcount']= $vbulletin->options['vbimghost_filmstripc'];
$vbimghost['onoff']			= $vbulletin->options['vbimghost_onoff'];
$vbimghost['colrow']		= $vbulletin->options['vbimghost_rowcol'];

//imgage setting global
$vbimghost['imagedir'] 		= $vbulletin->options['imgdir'];
$vbimghost['imgdimw']  		= $vbulletin->options['imgwidth'];
$vbimghost['imgdimh']  		= $vbulletin->options['imgheight'];
$vbimghost['imageext'] 		= explode("|",strtolower($vbulletin->options['imgext']));
$vbimghost['imgmaxsize']	= $vbulletin->options['imgfilesize'];

// image resize globals
$vbimghost['imgresizeon'] 	= $vbulletin->options['vbimghost_resizeon'];
$vbimghost['imgresizew'] 	= $vbulletin->options['vbimghost_resizew'];
$vbimghost['imgresizeh'] 	= $vbulletin->options['vbimghost_resizeh'];
$vbimghost['imgresizeq']	= $vbulletin->options['vbimghost_resizeq'];

//thumb globals
$vbimghost['imgthumw'] 	 	= $vbulletin->options['imgthumw'];
$vbimghost['imgthumh'] 	 	= $vbulletin->options['imgthumh'];
$vbimghost['imgthumpre'] 	= $vbulletin->options['imgthumprefix'];
$vbimghost['thumbson']		= $vbulletin->options['vbimghost_tumpon'];

//watermark globals
$vbimghost['watermarkon']   = $vbulletin->options['vbimghost_watermarkon'];
$vbimghost['watermarktype']	= $vbulletin->options['vbimghost_watermarktype'];
$vbimghost['watermarkpos']  = $vbulletin->options['vbimghost_watermarkpos'];
$vbimghost['watermarktext']	= $vbulletin->options['vbimghost_watermarktext'];
$vbimghost['watermarktextc']= $vbulletin->options['vbimghost_watermarktextc'];
$vbimghost['watermarktexts']= $vbulletin->options['vbimghost_watermarktexts'];
$vbimghost['watermarkfile'] = $vbulletin->options['vbimghost_watermarkfile'];
$vbimghost['watermarkmarg'] = $vbulletin->options['vbimghost_wmm'];

$vbimghost['defaultview']	= $vbulletin->options['vbimghost_defaultview'];
$vbimghost['cr'] 			= construct_phrase($vbphrase['vbimghost_credit'],$vbimghost['ver']);
eval('$vbimghost[\'cr\'] = "' . $vbimghost['cr']. '";');

//=============================================================
if (!$vbulletin->options['vbimghost_onoff'] && !($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canadmin'])){
	standard_error($vbulletin->options['vbimghost_onoffmsg']);
}

if (!$vbimghost['onoff']){
	$vbimghost['warn']=construct_phrase($vbphrase['x_is_offline'],$vbphrase['vbimghost_title']);
}
//=============================================================
//    SHOW IMAGES ( OWNER IMAGES ) 
///============================================================
if($vbulletin->GPC['do']=='myimages'){
	
	if (!$show['member'])
		print_no_permission();
	
	if($vbulletin->GPC_exists['pp'])
		$perpage =  $vbulletin->GPC['pp'];
	else
		$perpage = $vbimghost['iperpage'];
	

	if($vbulletin->GPC_exists['page'])
		$page =  $vbulletin->GPC['page'];	
	else
		$page = 1;
	
	$init = $perpage * ($page -1);
	
	
	//get images for user
	$userid = $vbulletin->userinfo['userid'];
	$username =$vbulletin->userinfo['username'];
	
	//get number or image user have
	$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost Where userid=$userid";
	$result=$db->query($sql);
	$imagecount=$db->num_rows($result);
	$imageleft= $permissions['vbimghost_files'] - $imagecount;
	//construct the pages navigation
	$pages=construct_page_nav($page, $perpage,$imagecount,THIS_SCRIPT.".php?do=myimages", "&amp;pp=$perpage");
	
	// get the image info 
	$sql="Select imgid,imgfile,imgname,thumbname,imgfilesize,imgwidth,imgheight,imgdate,imgprivate FROM ".TABLE_PREFIX ."vbimghost Where userid=$userid ORDER by imgdate LIMIT $init,$perpage ";
	$result= $db->query($sql);

	$vbimghost_bits="";
	if ($imagecount ==0)
	{
		eval('$vbimghost_bits = "' . fetch_template('vbimghost_noimgbit') . '";');
	}
	$counter = 0;
	while($row = $db->fetch_Array($result)){
		$counter++;
		$vbimghost['id']=$row['imgid'];
		$vbimghost['imgname']=$row['imgname'];
		$vbimghost['path']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['imgfile'];
		$vbimghost['dispath']=$vbulletin->options['bburl']."/".THIS_SCRIPT.".php?do=displayimg&imgid=".$row['imgid'];
		if (file_exists($vbulletin->options['imgdir']."/".$row['thumbname'])){
			$vbimghost['thumbpath']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['thumbname'];
			$vbimghost['thumbsize']=0;
		}else{ 
			$vbimghost['thumbpath']=$vbimghost['path'];
			if ($row['imgwidth'] <= $vbimghost['imgthumw'])
				$vbimghost['thumbsize']=$row['imgwidth'];
			else 
				$vbimghost['thumbsize']=$vbimghost['imgthumw'];
			
		}
		$vbimghost['filesize']=$row['imgfilesize'];
		$vbimghost['width']=$row['imgwidth'];
		$vbimghost['height']=$row['imgheight'];
		$vbimghost['date']=date('Y-m-d', $row['imgdate']);
	
		if($row['imgprivate']){
			$vbimghost['private']=construct_phrase($vbphrase['private'])." (<a href=\"".THIS_SCRIPT.".php?do=updateimg&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_setpub'])."</a> ";
		}else{
			$vbimghost['private']=construct_phrase($vbphrase['public'])." (<a href=\"".THIS_SCRIPT.".php?do=updateimg&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_setpri'])."</a> ";
		}
		$vbimghost['private'].="| <a href=\"".THIS_SCRIPT.".php?do=deleteimage&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_delimg'])."</a>)";

		
		if ($vbimghost['colrow']){
			eval('$vbimghost_bits .= "'.fetch_template('vbimghost_imgrowbit') . '";');	
		}else{
			
			eval('$vbimgcolbits .= "'.fetch_template('vbimghost_imgbits').'";');
			if ($counter == $vbimghost['imgperrow']){
				eval('$vbimghost_bits .= "'.fetch_template('vbimghost_imgcolbit') . '";');
				$vbimgcolbits ="";
					$counter=0;
			}
		}
	}
	if (!$vbimghost['colrow']){
		if ($counter > 0){
			eval('$vbimghost_bits .= "'.fetch_template('vbimghost_imgcolbit') . '";');
		}
	}
	$navbits = array(
		THIS_SCRIPT.".php" => construct_phrase($vbphrase['vbimghost_title']),
		'' => construct_phrase($vbphrase['vbimghost_viewmyimg'])
	);
	
	

	$navbits=construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('$body = "' . fetch_template('vbimghost_imgshow') . '";');
	$vbulletin->url = THIS_SCRIPT.".php?do=myimages&amp;pp=".$perpage."&amp;page=".$page;
	
}

//=============================================================
//    VIEW IMAGES 
///============================================================
if($vbulletin->GPC['do']=='viewimages'){
	if($vbulletin->GPC_exists['id']){
		$userrid=$vbulletin->GPC['id'];
	}else{
		eval(standard_error(fetch_error('vbimghost_nouserspecified')));  
	}
	
	if($vbulletin->GPC_exists['pp'])
		$perpage =  $vbulletin->GPC['pp'];
	else
		$perpage = $vbimghost['iperpage'];
	

	if($vbulletin->GPC_exists['page'])
		$page =  $vbulletin->GPC['page'];
	else
		$page = 1;
	
	
	$init = $perpage * ($page -1);
	
	$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost  Where imgprivate=0 AND userid=$userrid";
	$result=$db->query($sql);
	$imagecount=$db->num_rows($result);

	$pages=construct_page_nav($page, $perpage,$imagecount,THIS_SCRIPT.".php?do=viewimages", "&amp;id=$userrid&amp;pp=$perpage");
	
	$usrinfo = fetch_userinfo($userrid);
	$username =$usrinfo['username'];
	// get the image info 
	$sql="Select imgid,imgfile,imgname,thumbname,imgfilesize,imgwidth,imgheight,imgdate,imgprivate FROM ".TABLE_PREFIX ."vbimghost  Where imgprivate=0 AND userid=$userrid ORDER by imgdate LIMIT $init,$perpage ";
	$result= $db->query($sql);
	$vbimghost_bits="";
	if ($imagecount ==0)
		eval('$vbimghost_bits = "' . fetch_template('vbimghost_noimgbit') . '";');
	$counter=0;
	while($row = $db->fetch_Array($result)){
		$counter++;
		$vbimghost['id']=$row['imgid'];
		$vbimghost['imgname']=$row['imgname'];
		$vbimghost['path']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['imgfile'];
		$vbimghost['dispath']=$vbulletin->options['bburl']."/".THIS_SCRIPT.".php?do=displayimg&imgid=".$row['imgid'];
		if (file_exists($vbulletin->options['imgdir']."/".$row['thumbname'])){
			$vbimghost['thumbpath']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['thumbname'];
			$vbimghost['thumbsize']=0;
		}else{ 
			$vbimghost['thumbpath']=$vbimghost['path'];
			if ($row['imgwidth'] <= $vbimghost['imgthumw'])
				$vbimghost['thumbsize']=$row['imgwidth'];
			else 
				$vbimghost['thumbsize']=$vbimghost['imgthumw'];
			
		}
		$vbimghost['filesize']=$row['imgfilesize'];
		$vbimghost['width']=$row['imgwidth'];
		$vbimghost['height']=$row['imgheight'];
		$vbimghost['date']=date('Y-m-d', $row['imgdate']);


		if ($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canadmin'])
			$vbimghost['private']="(<a href=\"".THIS_SCRIPT.".php?do=deleteimage&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_delimg'])."</a>)";
		else
			$vbimghost['private']=construct_phrase($vbphrase['vbimghost_noperm']);

		
		
		
		if ($vbimghost['colrow']){
			eval('$vbimghost_bits .= "'.fetch_template('vbimghost_imgrowbit') . '";');
		}else{
			eval('$vbimgcolbits .= "'.fetch_template('vbimghost_imgbits').'";');
			
			if ($counter == $vbimghost['imgperrow']){
				eval('$vbimghost_bits .= "'.fetch_template('vbimghost_imgcolbit') . '";');
				$vbimgcolbits ="";
				$counter=0;
			}
		}
	}
	
	if (!$vbimghost['colrow']){
		if ($counter > 0){
			eval('$vbimghost_bits .= "'.fetch_template('vbimghost_imgcolbit') . '";');
		}
	}
	$navbits = array(
		THIS_SCRIPT.".php" => construct_phrase($vbphrase['vbimghost_title']),
		'' => construct_phrase($vbphrase['vbimghost_viewusrimg'],$username)
	);

	$navbits=construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('$body = "' . fetch_template('vbimghost_imgshow') . '";');
}


//=============================================================
//    DISPLAY IMAGE
///============================================================

if($vbulletin->GPC['do']=="displayimg"){
	
	//view the image in page ... .
	if ($vbulletin->GPC_exists['imgid']){
			$imgid =  $vbulletin->GPC['imgid'];
	}else{
		eval(standard_error(fetch_error('vbimghost_noimgspecified')));  
	}
	
	//$maxwidth =substr($stylevar['formwidth'],0,3);
	
	$userid = $vbulletin->userinfo['userid'];
	
	$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost Where imgid=$imgid";
	$result=$db->query($sql);
	$imagecount=$db->num_rows($result);
	if ($imagecount ==0){
		eval(standard_error(fetch_error('vbimghost_wrongimageid')));
	}
	
	$row = $db->fetch_Array($result);
		$vbimghost['id']=$row['imgid'];
		$vbimghost['imgname']=$row['imgname'];
		$vbimghost['path']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['imgfile'];
		$vbimghost['dispath']=$vbulletin->options['bburl']."/".THIS_SCRIPT.".php?do=displayimg&imgid=".$row['imgid'];
		$vbimghost['filesize']=$row['imgfilesize'];
		$vbimghost['width']=$row['imgwidth'];
		$vbimghost['height']=$row['imgheight'];
		$vbimghost['date']=date('Y-m-d', $row['imgdate']);
		
		// prevent browsing using images ID 
		if(($userid != $row['userid']) && ($row['imgprivate'] )){
			print_no_permission();
		}
		
		if (file_exists($vbulletin->options['imgdir']."/".$vbimghost['imgthumpre'].$row['imgfile'])){
			$vbimghost['thumbpath']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$vbimghost['imgthumpre'].$row['imgfile'];
			$vbimghost['thumbsize']=0;
		}else{ 
			$vbimghost['thumbpath']=$vbimghost['path'];
			if ($row['imgwidth'] <= $vbimghost['imgthumw'])
				$vbimghost['thumbsize']=$row['imgwidth'];
			else 
				$vbimghost['thumbsize']=$vbimghost['imgthumw'];
		}
		
		if (( $userid!=0 &$userid == $row['userid']) || ($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canadmin'])){
			if($row['imgprivate']){
				$vbimghost['private']=construct_phrase($vbphrase['private'])." (<a href=\"".THIS_SCRIPT.".php?do=updateimg&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_setpub'])."</a> ";
			}else{
				$vbimghost['private']=construct_phrase($vbphrase['public'])." (<a href=\"".THIS_SCRIPT.".php?do=updateimg&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_setpri'])."</a> ";
			}
			$vbimghost['private'].="| <a href=\"".THIS_SCRIPT.".php?do=deleteimage&imgid=".$vbimghost['id']."\">".construct_phrase($vbphrase['vbimghost_delimg'])."</a>)";
		}else{
			$vbimghost['private'] = construct_phrase($vbphrase['vbimghost_noperm']);
		}
		
		
		$navbits = array(
		THIS_SCRIPT.".php" => construct_phrase($vbphrase['vbimghost_title']),
		'' => $vbimghost['imgname']
		);
		
	if ($vbimghost['filmstripon']){
		
		$vbimghost['filmstrip']=vbimghost_filmstrip($row['userid'],$imgid,$vbimghost);
	}
	
	$navbits=construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');		
	eval('$body = "'.fetch_template('vbimghost_displayimg') . '";');

}

//=============================================================
//    POPUP
///============================================================

if($vbulletin->GPC['do']=="popup"){
	if (!$show['member'])
		print_no_permission();
	
	$vbulletin->input->clean_gpc('r',"ed",TYPE_STR);

	$vbimghost['editor'] =$vbulletin->GPC['ed'];
	if($vbulletin->GPC_exists['pp'])
		$perpage =  $vbulletin->GPC['pp'];
	else
		$perpage = $vbimghost['iperpage'];
	

	if($vbulletin->GPC_exists['page'])
		$page =  $vbulletin->GPC['page'];	
	else
		$page = 1;
	
	$init = $perpage * ($page -1);
	
	//get images for user
	$userid = $vbulletin->userinfo['userid'];
	$username =$vbulletin->userinfo['username'];
	
	//get number or image user have
	$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost Where userid=$userid";
	$result=$db->query($sql);
	$imagecount=$db->num_rows($result);
	//construct the pages navigation
	$pages=construct_page_nav($page, $perpage,$imagecount,THIS_SCRIPT.".php?do=popup", "&amp;pp=$perpage"."&amp;ed=".$vbimghost['editor']);
	
	// get the image info 
	$sql="Select imgid,imgfile,thumbname,imgwidth FROM ".TABLE_PREFIX ."vbimghost Where userid=$userid ORDER by imgdate LIMIT $init,$perpage ";
	$result= $db->query($sql);

	$vbimghost[popimg]="";
	if ($imagecount ==0)
	{
		eval('$vbimghost[\'popimg\'] = "' . fetch_template('vbimghost_noimgbit') . '";');
	}
	$counter = 0;
	while($row = $db->fetch_Array($result)){
		
		$vbimghost['id']=$row['imgid'];
		//$vbimghost['imgname']=$row['imgname'];
		$vbimghost['path']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['imgfile'];
		$vbimghost['dispath']=$vbulletin->options['bburl']."/".THIS_SCRIPT.".php?do=displayimg&imgid=".$row['imgid'];
		if (file_exists($vbulletin->options['imgdir']."/".$row['thumbname'])){
			$vbimghost['thumbpath']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['thumbname'];
			$vbimghost['thumbsize']=0;
			$vbimghost['imgwidth']=$vbimghost['imgthumw'];
		}else{
			
			$vbimghost['thumbpath']=$vbimghost['path'];
			if ($row['imgwidth'] <= $vbimghost['imgthumw'])
				$vbimghost['thumbsize']=$row['imgwidth'];
			else 
				$vbimghost['thumbsize']=$vbimghost['imgthumw'];
			$vbimghost['imgwidth']=$vbimghost['thumbsize'];
		}
		$thump="1";
		if ($counter > $vbimghost['imgperrow']){
			$counter=1;
		}
		eval('$vbimghost[\'popimg\'] .= "'.fetch_template('vbimghost_imgbits_poprow').'";');
		$counter++;
	}
	while($counter){
		$thump="0";
		eval('$vbimghost[\'popimg\'] .= "'.fetch_template('vbimghost_imgbits_poprow').'";');
		$counter--;
	}
}
//=============================================================
//    UPDATE IMAGE
///============================================================
if ($vbulletin->GPC['do']=='updateimg'){

	$userid = $vbulletin->userinfo['userid'];
	$username =$vbulletin->userinfo['username'];
	$imgid = $vbulletin->GPC['imgid'];
	
	$sql = "Select userid,imgprivate FROM ".TABLE_PREFIX ."vbimghost Where imgid=$imgid";
	$tmp=$db->query($sql);
	$result=$db->fetch_Array($tmp);
	
	if ($userid == $result['userid']){
		$mark = abs($result['imgprivate']-1);
		$sql = "Update ".TABLE_PREFIX ."vbimghost SET imgprivate=$mark where imgid=$imgid";
		$result=$db->query($sql);
		
		if($_SERVER['HTTP_REFERER']==""){
				$vbulletin->url = THIS_SCRIPT.".php?do=myimages";			
		}else{
			$vbulletin->url = $_SERVER['HTTP_REFERER'];
		}
		
		eval(print_standard_redirect('vbimghost_updated', true, true));  
	}else{
		print_no_permission();
	}
		
}

//=============================================================
//    USER LIST
///============================================================
if ($vbulletin->GPC['do']=='userlist'){
	
	if($vbulletin->GPC_exists['pp'])
		$perpage =  $vbulletin->GPC['pp'];
	else
		$perpage = $vbimghost['uperpage'];
	

	if($vbulletin->GPC_exists['page'])
		$page =  $vbulletin->GPC['page'];
	else
		$page = 1;
	
	$init = $perpage * ($page -1);
	
	$sql = "select userid FROM ".TABLE_PREFIX ."vbimghost where imgprivate =0 group by userid";
	$result=$db->query($sql);
	$usercount = $db->num_rows($result);
	
	$pages=construct_page_nav($page, $perpage,$usercount,THIS_SCRIPT.".php?do=userlist", "&amp;pp=$perpage");
		
	if ($usercount==0){ 
		eval('$vbimghost_usersbit = "' . fetch_template('vbimghost_nouserbit') . '";');
	}else{
		$sql = "select user.username, img.userid,count(*) imgcnt FROM ".TABLE_PREFIX ."vbimghost as img LEFT JOIN ".TABLE_PREFIX."user as user ON img.userid = user.userid where imgprivate =0 group by user.username LIMIT $init,$perpage ";
		$result=$db->query($sql);
	
		while($row = $db->fetch_Array($result)){
			$vbimghost['userid']=$row['userid'];
			if ($row['userid'] == 0 ){
				$row['username']=construct_phrase($vbphrase['guest']);
			}
			$vbimghost['username'] = $row['username'];
			$vbimghost['imgcount'] = $row['imgcnt'];
			$vbimghost_usersbit.=eval('$vbimghost_usersbit .= "' . fetch_template('vbimghost_usersbit') . '";');
		}
	}
	
	$navbits = array(
		THIS_SCRIPT.".php" => construct_phrase($vbphrase['vbimghost_title']),
		'' => construct_phrase($vbphrase['vbimghost_listuser'])
	);
	
	$navbits=construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('$body = "' . fetch_template('vbimghost_users') . '";');
	
}

//=============================================================
//    Upload Step 1 
///============================================================


if($vbulletin->GPC['do']=='upload'){
	
	if($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canupload']){
		$userid = $vbulletin->userinfo['userid'];
		cache_permissions($vbulletin->userinfo);
		
		$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost  Where userid=$userid";
		$result= $db->query($sql);
		$vbimghost['imguserhave'] = $db->num_rows($result);
		$vbimghost['imguserleft']= $permissions['vbimghost_files'] - $vbimghost['imguserhave'];
		$vbimghost['imgext'] = str_replace("|"," , ",$vbulletin->options['imgext']);
		$vbimghost['imgusermax']= $permissions['vbimghost_files'];
		for ($i= 0;$i < $permissions['vbimghost_upslots'];$i++ ){
			$vbimghost['slots']= $vbimghost['imguserleft'] - $i;
			$count = $i;
			$vbimghost['upfields'].=eval('$vbimghost[\'upfields\'] .= "' . fetch_template('vbimghost_upfields') . '";');
		}
		
		$navbits = array(
			THIS_SCRIPT.".php" => construct_phrase($vbphrase['vbimghost_title']),
			'' => construct_phrase($vbphrase['vbimghost_upload'],$username)
		);
		
		$navbits=construct_navbits($navbits);
		eval('$navbar = "' . fetch_template('navbar') . '";');
		eval('$body = "' . fetch_template('vbimghost_upload') . '";');
		
	}else {
		print_no_permission();
	}
	
}
//=============================================================
//    Upload Step 2 
///============================================================


if ($vbulletin->GPC['do']=='uploadfile'){

	$userid = $vbulletin->userinfo['userid'];
	if($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canupload']){
		for ($i=0; $i<$permissions['vbimghost_upslots'];$i++){
			${'upfile'.$i}= $vbulletin->input->clean_gpc('f',"upfile".$i,TYPE_FILE);
			
			$upfilename = str_replace("'","",$vbulletin->GPC['upfile'.$i]['name']); 
    		$upfilesize = ($vbulletin->GPC['upfile'.$i]['size']/1024); 
    		$upfiletype = $vbulletin->GPC['upfile'.$i]['type']; 
    		$upfiletname = $vbulletin->GPC['upfile'.$i]['tmp_name']; 
    		$upfileerro = $vbulletin->GPC['upfile'.$i]['error']; 
    		$fileext =trim(strtolower(strrchr($upfilename, '.')));
    		//fix for getimagesize errors 
    		$tmpname = "tmp_".uniqid($userid);
			if(move_uploaded_file($upfiletname, $vbimghost['imagedir']."/".$tmpname)){
				$upfiletname = $vbimghost['imagedir']."/".$tmpname;
			}
			
			if ($upfilename!="")
    		{
    			
    			if(!$dime = getimagesize($upfiletname)){
    				eval(standard_error(construct_phrase(fetch_error('vbimghost_notimg'),$upfilename)));
    			}
    			
	    		if (!$dime){
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgsizeundefined'),$upfilename)));	
	    		}
	    		
	    		$fileext = vbimghost_imgtype($dime[2]);
	    		if(! in_array($fileext,$vbimghost['imageext'])){
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_extnotallowed'),$upfilename)));	
	    		}
	    		   		
	    		
	    		if($dime[0] > $vbimghost['imgdimw'])
	    		{
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgdimeerror'),$upfilename)));
	    		}
	    		if($dime[1] > $vbimghost['imgdimh'])
	    		{
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgdimeerror'),$upfilename)));
	    		}
	    		
	    		if ($upfilesize > $vbimghost['imgmaxsize']){
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgfilsize'),$upfilename)));
	    		}
	    		
	    		$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost  Where imgprivate=0 AND userid=$userid";
				$result=$db->query($sql);
				$imagecount=$db->num_rows($result);
	    		if (($permissions['vbimghost_files']- $imagecount) <= 0) {
					eval(standard_error(fetch_error('vbimghost_numexc')));
				}
				
				
				$imguname=uniqid($userid);
				$imguname=$imguname.$fileext;
	    		@copy($upfiletname, $vbimghost['imagedir']."/".$imguname);
	    		//remove temp file
	    		unlink($upfiletname);

	    		//BEGIN NEW CODE
				//Picture Resize (NEW)
				
	    		if($vbimghost['imgresizeon']){
	    			if ($vbimghost['imgresizew'] < $dime[0] && $$vbimghost['imgresizew'] < $dime[1]) {
					vbimghost_resize($vbimghost['imagedir'],$imguname,$dime[0],$dime[1],$vbimghost['imgresizew'],$vbimghost['imgresizeh'],$vbimghost['imgresizeq'],$imguname,$fileext);
					// get new dimention & file size
		    		$dime = getimagesize($vbimghost['imagedir']."/".$imguname);
    				$upfilesize = (filesize($vbimghost['imagedir']."/".$imguname)/1024);
	    			}
	    		}
	       		//Watermark
	    		if($vbimghost['watermarkon'])
					vbimghost_watermark($vbimghost['forumpath']."/".$vbimghost['imagedir'],$imguname,$vbimghost['watermarkfile'],$vbimghost['watermarkpos'],$vbimghost['watermarktype'],$vbimghost['watermarktext'],$vbimghost['watermarktextc'],$vbimghost['watermarktexts'],$fileext,$vbimghost['watermarkmarg']);
					
	    		$thumbname=$vbimghost['imgthumpre'].$imguname;
	    		if($vbimghost['thumbson']) 
	    			vbimghost_creatthumb($vbimghost['imagedir'],$imguname,$vbimghost['imagedir'],$dime[0],$dime[1],$vbimghost['imgthumw'],$vbimghost['imgthumh'],$thumbname,$fileext);
	    		
	    		$viewperm = $vbimghost['defaultview'];
	    		if($userid==0){
	    			// if guest set the view permission to public always
	    			$viewperm = 0;
	    		}
	       		
	    		$sql="INSERT INTO ".TABLE_PREFIX."vbimghost ( `imgid` , `userid` , `imgfile` , `imgname` , `thumbname` , `imgfilesize` , `imgwidth` , `imgheight` , `imgdate` , `imgprivate` ) 
	    		VALUES ('', '".$userid."', '".$imguname."', '".$upfilename."','". $thumbname ."' , '".$upfilesize."', '".$dime[0]."', '".$dime[1]."', '".TIMENOW."', '".$viewperm."')";
	
	    		$result= $db->query($sql);
	    		
	    	}else{
	    		if ($i==0){
	    			eval(standard_error(fetch_error('vbimghost_nofileupload')));
	    		}
	    	}
    	}
    	// redirect to correct page. 
    	$sql = "SELECT COUNT(imgid) FROM ".TABLE_PREFIX."vbimghost where userid='".$userid."'";
    	$result = $db->query($sql);
    	$row = $db->fetch_Array($result);
    	$tmp = ceil($row[0]/ $vbimghost['iperpage']);
    	if ($tmp < 1 ) $tmp = 1;
    	
    	$vbulletin->url = THIS_SCRIPT.".php?do=myimages&amp;pp=".$vbimghost['iperpage']."&amp;page=".$tmp;
    	eval(print_standard_redirect('vbimghost_imgupdone', true, true));	
	}else{
		print_no_permission();
	}
	
	
}
//=============================================================
//    POPUP UPLOAD 
///============================================================


if ($vbulletin->GPC['do']=='popupload'){
	
	if (!$show['member'])
		print_no_permission();
	$reload=0;
	$ed =$vbulletin->GPC['ed'];
	$vbulletin->input->clean_gpc('r',"seq",TYPE_UINT);
	$vbulletin->input->clean_gpc('r',"ed",TYPE_STR);
	$ed = $vbulletin->GPC['ed'];
	if(!$vbulletin->GPC_exists['seq']){
		
		$canupload=0;
		if ($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canupload'])
			$canupload=1;
		eval('print_output("' . fetch_template('vbimghost_popupload_pre') . '");'); 
	}else{
		if($vbulletin->GPC['seq']=='2'){
			//process the upload 
			$vbulletin->input->clean_gpc('f',"upfile",TYPE_FILE);
			$userid = $vbulletin->userinfo['userid'];
			
			$upfilename = str_replace("'","",$vbulletin->GPC['upfile']['name']); 
    		$upfilesize = ($vbulletin->GPC['upfile']['size']/1024); 
    		$upfiletype = $vbulletin->GPC['upfile']['type']; 
    		$upfiletname = $vbulletin->GPC['upfile']['tmp_name']; 
    		$upfileerro = $vbulletin->GPC['upfile']['error']; 
    		$fileext =trim(strtolower(strrchr($upfilename, '.')));
    		//fix for getimagesize errors 
    		$tmpname = "tmp_".uniqid($userid);
			if(move_uploaded_file($upfiletname, $vbimghost['imagedir']."/".$tmpname)){
				$upfiletname = $vbimghost['imagedir']."/".$tmpname;
			}
			
			if ($upfilename!="")
    		{
    			
    			if(!$dime = getimagesize($upfiletname)){
    				eval(standard_error(construct_phrase(fetch_error('vbimghost_notimg'),$upfilename)));
    			}
    			
	    		if (!$dime){
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgsizeundefined'),$upfilename)));	
	    		}
	    		
	    		$fileext = vbimghost_imgtype($dime[2]);
	    		if(! in_array($fileext,$vbimghost['imageext'])){
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_extnotallowed'),$upfilename)));	
	    		}
	    		   		
	    		
	    		if($dime[0] > $vbimghost['imgdimw'])
	    		{
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgdimeerror'),$upfilename)));
	    		}
	    		if($dime[1] > $vbimghost['imgdimh'])
	    		{
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgdimeerror'),$upfilename)));
	    		}
	    		
	    		if ($upfilesize > $vbimghost['imgmaxsize']){
	    			eval(standard_error(construct_phrase(fetch_error('vbimghost_imgfilsize'),$upfilename)));
	    		}
	    		
	    		$sql = "Select * FROM ".TABLE_PREFIX ."vbimghost  Where imgprivate=0 AND userid=$userid";
				$result=$db->query($sql);
				$imagecount=$db->num_rows($result);
	    		if (($permissions['vbimghost_files']- $imagecount) <= 0) {
					eval(standard_error(fetch_error('vbimghost_numexc')));
				}
				
				
				$imguname=uniqid($userid);
				$imguname=$imguname.$fileext;
	    		@copy($upfiletname, $vbimghost['imagedir']."/".$imguname);
	    		unlink($upfiletname);

	    		if($vbimghost['imgresizeon']){
	    			if ($vbimghost['imgresizew'] < $dime[0] && $$vbimghost['imgresizew'] < $dime[1]) {
					vbimghost_resize($vbimghost['imagedir'],$imguname,$dime[0],$dime[1],$vbimghost['imgresizew'],$vbimghost['imgresizeh'],$vbimghost['imgresizeq'],$imguname,$fileext);
					// get new dimention & file size
		    		$dime = getimagesize($vbimghost['imagedir']."/".$imguname);
    				$upfilesize = (filesize($vbimghost['imagedir']."/".$imguname)/1024);
	    			}
	    		}
	       		//Watermark
	    		if($vbimghost['watermarkon'])
					vbimghost_watermark($vbimghost['forumpath']."/".$vbimghost['imagedir'],$imguname,$vbimghost['watermarkfile'],$vbimghost['watermarkpos'],$vbimghost['watermarktype'],$vbimghost['watermarktext'],$vbimghost['watermarktextc'],$vbimghost['watermarktexts'],$fileext,$vbimghost['watermarkmarg']);
					
	    		$thumbname=$vbimghost['imgthumpre'].$imguname;
	    		if($vbimghost['thumbson']) 
	    			vbimghost_creatthumb($vbimghost['imagedir'],$imguname,$vbimghost['imagedir'],$dime[0],$dime[1],$vbimghost['imgthumw'],$vbimghost['imgthumh'],$thumbname,$fileext);
	    		
	    		$viewperm = $vbimghost['defaultview'];
	    		if($userid==0){
	    			// if guest set the view permission to public always
	    			$viewperm = 0;
	    		}
	       		
	    		$sql="INSERT INTO ".TABLE_PREFIX."vbimghost ( `imgid` , `userid` , `imgfile` , `imgname` , `thumbname` , `imgfilesize` , `imgwidth` , `imgheight` , `imgdate` , `imgprivate` ) 
	    		VALUES ('', '".$userid."', '".$imguname."','".$upfilename."','". $thumbname ."' , '".$upfilesize."', '".$dime[0]."', '".$dime[1]."', '".TIMENOW."', '0')";
	
	    		$result= $db->query($sql);
	    		$resultid = $db->insert_id();
	    		
	    	}else{
	    		if ($i==0){
	    			eval(standard_error(fetch_error('vbimghost_nofileupload')));
	    		}
	    	}
   			
	    	$reload=1;
	    	$vbimghost['path']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$imguname;
			$vbimghost['dispath']=$vbulletin->options['bburl']."/".THIS_SCRIPT.".php?do=displayimg&imgid=".$resultid;
			if (file_exists($vbulletin->options['imgdir']."/".$vbimghost['imgthumpre'].$imguname)){
				$vbimghost['thumbpath']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$vbimghost['imgthumpre'].$imguname;
				$vbimghost['thumbsize']=0;
			}else{ 
				$vbimghost['thumbpath']=$vbimghost['path'];
				if ($row['imgwidth'] <= $vbimghost['imgthumw'])
					$vbimghost['thumbsize']=$dime[0];
				else 
					$vbimghost['thumbsize']=$vbimghost['imgthumw'];		
			}
				
			$reload=1;
			eval('print_output("' . fetch_template('vbimghost_popupload_pre') . '");');	
		}
	}
	
	
}



//=============================================================
//    Delete Images
///============================================================


if($vbulletin->GPC['do']=='deleteimage'){
	$userid = $vbulletin->userinfo['userid'];
	$imgid = $vbulletin->GPC['imgid'];
	
	$sql = "Select userid,imgfile,thumbname FROM ".TABLE_PREFIX ."vbimghost Where imgid=$imgid";
	$tmp=$db->query($sql);
	$result=$db->fetch_Array($tmp);
	// check if he have permission;
	//fix the premission 
	if ($userid == $result['userid'] || ($permissions['vbimghost'] & $vbulletin->bf_ugp['vbimghost']['canadmin'])){
		//delete image
		$imgfile = $vbimghost['imagedir']."/".$result['imgfile'];
		$imgthumbfile = $vbimghost['imagedir']."/".$result['thumbname'];
		
		if(!unlink($imgfile)){
			eval(standard_error(fetch_error('vbimghost_imgnotdeleted')));
		}
		
		if (file_exists($imgthumbfile))
			unlink($imgthumbfile);
			
		//remove from database 
		$sql = "Delete FROM ".TABLE_PREFIX ."vbimghost Where imgid=$imgid";
		$result2=$db->query($sql);
	
		// redirect back .. 
		if ($userid == $result['userid']){
				$vbulletin->url = THIS_SCRIPT.".php?do=myimages";
		}else{
				$vbulletin->url = THIS_SCRIPT.".php?do=viewimages&amp;id=".$result['userid'];
		}
		eval(print_standard_redirect('vbimghost_deleted', true, true));  
	}else{
		print_no_permission();
	}
}

//=============================================================
//    Output Final Result.
///============================================================
switch ($vbulletin->GPC['do']){
	case "popup":
		eval('print_output("' . fetch_template('vbimghost_imgbits_popmain') . '");');
		break;
	case "popupload":
		break;
	default:
		eval('print_output("' . fetch_template('vbimghost_main') . '");');
		break;
}
?> 