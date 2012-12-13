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
@set_time_limit(0);
 
define('MAIN_SCRIPT', 'vbimghost');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');
 
// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');
 
$vbulletin->input->clean_array_gpc('r', array(
	'mname' => TYPE_STR,
	'thumbsgo'=> TYPE_UINT,
	'do'=> TYPE_STR,
	'perpage' => TYPE_UINT,
	'page' => TYPE_UINT));
	
	
// print cp header. 
print_cp_header();



// ########################## FUNCTION USED ############################

 function get_userid($username)
{
	global $vbulletin;
	
	if ($user = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $vbulletin->db->escape_string(trim($username)) . "'"))
		return $user['userid'];
	else
		return false;
}
//NEW function required when viewing ALL pictures in admincp
 function get_username($userid)
{
	global $vbulletin;
	
	if ($user = $vbulletin->db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = '" . $vbulletin->db->escape_string(trim($userid)) . "'"))
		return $user['username'];
	else
		return false;
}



// ##########################   CODE START   ############################
if (empty($vbulletin->GPC['do'])){
	print_cp_message($vbphrase['vbimghost_selectmenu']);
}


if ($vbulletin->GPC['do']=='members'){
	
	print_form_header('vbimghost','memberview');
	print_table_header('Member Search');
	print_input_row($vbphrase['vbimghost_entername'],'mname');
	print_input_row($vbphrase['vbimghost_imgperp'],'perpage',20);
	print_submit_row($vbphrase['vbimghost_viewimg']);
}

if($vbulletin->GPC['do']=='memberview'){
	if($vbulletin->GPC['perpage']<1){
		$vbulletin->GPC['perpage'] = 20;
	}
	
	if ($vbulletin->GPC['page'] < 1)
	{
		$vbulletin->GPC['page'] = 1;
	}
		
	
	$musername = $vbulletin->GPC['mname'];
	$perpage = trim($vbulletin->GPC['perpage']);
	$page = trim($vbulletin->GPC['page']);

	if (strtolower($vbulletin->GPC['mname'])=="" || $vbulletin->GPC['mname']==$vbphrase['all_users']){
		$mname= "";
		$musername =$vbphrase['all_users'];
		$sql="Select * FROM ".TABLE_PREFIX ."vbimghost";
		$csql ="SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "vbimghost";
	}else{

		$mname=$vbulletin->GPC['mname'];
		if(!($muserid=get_userid($musername))){
			$muserid=0;
			$mname=construct_phrase($vbphrase['guest']);
		}
		$sql="Select img.userid userid,username,imgid,imgname,thumbname,imgfile,imgwidth,imgfilesize,imgheight,imgdate,imgprivate FROM ".TABLE_PREFIX ."vbimghost img , ".TABLE_PREFIX ."user usr where img.userid=$muserid AND usr.userid=$muserid";
		$csql="SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "vbimghost where userid=$muserid";
	}

	$count = $db->query_first($csql);
		$totalitems = $count['total'];
		$totalpages = ceil($totalitems / $vbulletin->GPC['perpage']);
		
		if($page > $totalpages)
			$page = $totalpages;
		$startat = ($page - 1) * $perpage;
		if ($startat < 0)
			$startat = 0;
		print_form_header('vbimghost', 'memberview');
		construct_hidden_code('mname',$musername);		
		$pagebuttons = "\n\t" . $vbphrase['pages'] . ": ($totalpages)\n";
		
		$sql = $sql." LIMIT $startat,$perpage";
		$result=$db->query($sql);
		$imagecount=$db->num_rows($result);
		if ($imagecount==0){
			print_cp_message($vbphrase['vbimghost_usernoimg']);
		}
 		print_table_header(construct_phrase($vbphrase['vbimghost_viewimgfor'],$musername));

		while($row = $db->fetch_Array($result)){
			$vbimghost['id']=$row['imgid'];
			$vbimghost['imgname']=$row['imgname'];
			$vbimghost['path']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['imgfile'];
			if (file_exists($vbulletin->options['vbimghost_forumpath']."/".$vbulletin->options['imgdir']."/".$row['thumbname'])){
				$vbimghost['thumbpath']=$vbulletin->options['bburl']."/".$vbulletin->options['imgdir']."/".$row['thumbname'];
				$vbimghost['thumbsize']=0;
			}else{ 
				$vbimghost['thumbpath']=$vbimghost['path'];
				if ($row['imgwidth'] <= $vbulletin->options['imgthumw'])
					$vbimghost['thumbsize']=$row['imgwidth'];
				else 
					$vbimghost['thumbsize']=$vbulletin->options['imgthumw'];
			}
			$vbimghost['filesize']=$row['imgfilesize'];
			$vbimghost['width']=$row['imgwidth'];
			$vbimghost['height']=$row['imgheight'];
			$vbimghost['date']=date('Y-m-d', $row['imgdate']);
			if ($row['imgprivate']){
				$vbimghost['private']=construct_phrase($vbphrase['private'])." (<a href=\"".MAIN_SCRIPT.".php?do=deleteimg&imgid=".$vbimghost['id']."&mname=".$musername."&page=".$page."&perpage=".$perpage."\">".construct_phrase($vbphrase['vbimghost_delimg'])."</a>)";
			}
			else{
				$vbimghost['private']=construct_phrase($vbphrase['public'])."(<a href=\"".MAIN_SCRIPT.".php?do=deleteimg&imgid=".$vbimghost['id']."&mname=".$musername."&page=".$page."&perpage=".$perpage."\">".construct_phrase($vbphrase['vbimghost_delimg'])."</a>)";
			}
			
			if(!($vbimghost[uname]=get_username($row[userid]))){
					$vbimghost[uname]=construct_phrase($vbphrase['guest']);
			}

			echo"<tr >
				<td class=\"alt1\" align=\"center\" valign=\"center\" width=\"50%\"><a href=\"$vbimghost[path]\"><img src=\"$vbimghost[thumbpath]\" ";
			if($vbimghost['thumbsize'])
				   echo "width=\"$vbimghost[thumbsize]\"";
			echo"border=\"0\"></a></td>
				<td class=\"alt2\" align=\"center\" valign=\"center\" width=\"50%\">
					<table class=\"tborder\" cellpadding=\"$stylevar[cellpadding]\" cellspacing=\"$stylevar[cellspacing]\" border=\"1\">
					<thead>
					<tr>
						<td class=\"thead\" colspan=\"2\">$vbimghost[imgname]</td>
					</tr>
					</thead>
					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_username]</td>
						<td class=\"alt2\">$vbimghost[uname]</td>
					</tr>
					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_filesize]</td>
						<td class=\"alt2\">$vbimghost[filesize] KB</td>
					</tr>
					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_imgdime]</td>
						<td class=\"alt2\">$vbimghost[width] x $vbimghost[height] </td>
					</tr>
					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_update]</td>
						<td class=\"alt2\">$vbimghost[date] </td>
					</tr>
					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_imgflink]</td>
						<td class=\"alt2\"><input type=\"text\" size=\"80\" value=\"[url='$vbimghost[dispath]'][img]$vbimghost[thumbpath][/img][/url]\"></td>
					</tr>
					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_imglink]</td>
						<td class=\"alt2\"><input type=\"text\" size=\"80\" value=\"<a href='$vbimghost[dispath]' border=0><img src='$vbimghost[thumbpath]'></a>\"></td>
					</tr>

					<tr>
						<td class=\"alt1\">$vbphrase[vbimghost_perm] </td>
						<td class=\"alt2\">$vbimghost[private]</td>
					</tr>
					</table>
				</td>
				</tr>";
		} 
		
			echo $vbimghost_bits;
			for ($i = 1; $i <= $totalpages; $i++)
			{
				$pagebuttons .= "\t<input type=\"submit\" class=\"button\" name=\"page\" value=\"$i\"" . iif($i == $page, ' disabled="disabled"') . ">\n";
			}
			$pagebuttons .= "\t&nbsp; &nbsp; &nbsp; &nbsp;";
			print_table_footer(2, "\n\t$pagebuttons " . $vbphrase['per_page'] . "<input type=\"text\" name=\"perpage\" value=\"".$vbulletin->GPC['perpage'] ."\" size=\"3\" tabindex=\"1\"><input type=\"submit\" class=\"button\" value=\"" . $vbphrase['go'] . "\" tabindex=\"1\">\n\t");
}

if ($vbulletin->GPC['do']=='thumbs'){
	print_form_header('vbimghost','thumbsstart');
	print_table_header($vbphrase['vbimghost_thumbuild']);
	print_yes_no_row($vbphrase['vbimghost_startproc'], 'thumbsgo',1);  
	print_input_row($vbphrase['vbimghsot_thumtopro'],'perpage',200);
	print_description_row($vbphrase['vbimghost_prottime'], 0, 2, 'alt1');
	print_submit_row($vbphrase['vbimghost_startp']);
}


 
if ($vbulletin->GPC['do']=='thumbsstart'){
	if ($vbulletin->GPC['thumbsgo']){
		
		if(!$vbulletin->options['vbimghost_tumpon']){
			print_cp_message($vbphrase['vbimghost_thumdis']);
		}
		require_once(DIR . '/includes/vbimghost_include.php');
		
		if($vbulletin->GPC['perpage']<1){
			$vbulletin->GPC['perpage'] = 200;
		}
		
		if ($vbulletin->GPC['page'] < 1)
		{
			$vbulletin->GPC['page'] = 1;
		}
		
		$startat = (trim($vbulletin->GPC['page']) - 1) * $vbulletin->GPC['perpage'];	
		
		
		$perpage = trim($vbulletin->GPC['perpage']);
		$page = trim($vbulletin->GPC['page']);
		$count = $db->query_first("SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "vbimghost");
		$totalitems = $count['total'];
		$totalpages = ceil($totalitems / $vbulletin->GPC['perpage']);
		
		if ($vbulletin->GPC['page'] <= $totalpages){
			$sql = "Select imgfile,imgname,thumbname,imgwidth,imgheight FROM ".TABLE_PREFIX ."vbimghost LIMIT $startat,$perpage";
			$result=$db->query($sql);
			
			$imagecount=$db->num_rows($result);
			if ($imagecount == 0 ){
				print_cp_message($vbphrase['vbimghost_noimgf']);
			}
			print_form_header('vbimghost','thumbsstart');
			construct_hidden_code('thumbsgo',1);
			construct_hidden_code('page',$page+1);		
			construct_hidden_code('perpage',$vbulletin->GPC['perpage']);
			$pagebuttons = "\n\t" . $vbphrase['pages'] . ": ($page / $totalpages)\n";
		 	print_table_header($vbphrase['vbimghost_rethumb']);
		 	while($row = $db->fetch_Array($result)){
		 		$imgname = $row['imgname'];
		 		$imgfile = $row['imgfile'];
		 		$imgw = $row['imgwidth'];
		 		$imgh = $row['imgheight'];
		 		$thumbname = $row['thumbname'];
		 		$imagedir = $vbulletin->options['vbimghost_forumpath']."/".$vbulletin->options['imgdir'];
		 		$thumw = $vbulletin->options['imgthumw'];
		 		$thumh = $vbulletin->options['imgthumh'];
		 		//$imgext = strtolower(strrchr($imgname, '.'));
		 		$dim = getimagesize("$imagedir/$imgfile");
		 		$imgext = vbimghost_imgtype($dim[2]);
		 		$imgfiletmp = explode('.',$row['imgfile']);
		 		$imgfile = $imgfiletmp[0].$imgext;
		 		if (vbimghost_creatthumb($imagedir,$imgfile,$imagedir,$imgw,$imgh,$thumw,$thumh,$thumbname,$imgext)){
		 			echo "<tr><td>".$vbphrase['vbimghost_thumcfor']." $imgname </td></tr>";
		 		}else{
		 			echo "<tr><td>".$vbphrase['vbimghost_thumncfor']." $imgname </td></tr>";
		 		}
		 	}
		 	print_table_footer(2, "\n\t$pagebuttons <input type=\"submit\" class=\"button\" value=\"" . $vbphrase['next_page']. "\" tabindex=\"1\">\n\t");
		}else{
			define('CP_REDIRECT', 'vbimghost.php?do=thumbs');
			print_stop_message('vbimghost_thumbsdone');
		}
	}else{
		define('CP_REDIRECT', 'vbimghost.php?do=thumbs');
		print_stop_message('vbimghost_themnc');
	}
	
}

if ($vbulletin->GPC['do']=='deleteimg'){
	$vbulletin->input->clean_array_gpc('r', array(
		'imgid'=> TYPE_UINT));
	$imgid = $vbulletin->GPC['imgid'];
	$perpage = $vbulletin->GPC['perpage'];
	$page = $vbulletin->GPC['page'];
	$musername= $vbulletin->GPC['mname'];
	
		
	$sql = "Select imgfile,thumbname FROM ".TABLE_PREFIX ."vbimghost Where imgid=$imgid";
	$tmp=$db->query($sql);
	$result=$db->fetch_Array($tmp);
		//delete image
		$imgfile = $vbulletin->options['vbimghost_forumpath']."/".$vbulletin->options['imgdir']."/".$result['imgfile'];
		$imgthumbfile = $vbulletin->options['vbimghost_forumpath']."/".$vbulletin->options['imgdir']."/".$result['thumbname'];
		if(!unlink($imgfile)){
			print_cp_message($vbphrase['vbimghost_unabletodeleteimg']);
		}
		
		//remove thumbnail 
		if (file_exists($imgthumbfile))
			unlink($imgthumbfile);
		
		//remove from database 
		$sql = "Delete FROM ".TABLE_PREFIX ."vbimghost Where imgid=$imgid";
		$result2=$db->query($sql);
		
		define('CP_REDIRECT', 'vbimghost.php?do=memberview&page='.$page.'&perpage='.$perpage.'&mname='.$musername);
		print_stop_message('vbimghost_deleted2');
		// redirect back .. 
}


// print cp footer 
print_cp_footer();



?>