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

 // Creating thumbnail functions 

 function  vbimghost_creatthumb($imageDirectory,$imageName,$thumbDirectory,$imageWidth,$imageHeight,$thumbWidth,$thumbHeight,$thumbName,$imageExt){
 	
 	//Get Resize presentage for width and hieght 
 	
 	$hper = $imageHeight / $thumbHeight;
 	$wper = $imageWidth / $thumbWidth;
 	
 	
 	if( $hper > 1 || $wper > 1 ){
 		//check to rezise by widht or hieght 
 		if ($wper > $hper)
 			// get ratio using width
 			$ratio = $thumbWidth / $imageWidth;
 		else
 			// get ratio using height
 			$ratio = $thumbHeight / $imageHeight;
 			
 		// calculate new width and hight for the thumb image 
 		$width = $ratio * $imageWidth;
 		$height = $ratio * $imageHeight;
 		$width = round($width);
 		$height= round($height);
 		
 		switch ($imageExt){
 			case (".gif"):
 					$srcImg = imagecreatefromgif("$imageDirectory/$imageName");
 					$colortrans = imagecolortransparent($srcImg);
 					$thumbImg = imagecreate($width, $height);
 					imagepalettecopy($thumbImg,$srcImg);
 					imagefill($thumbImg,0,0,$colortrans);
 					imagecolortransparent($thumbImg,$colortrans);
 					imagecopyresized($thumbImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					imagegif($thumbImg,"$thumbDirectory/$thumbName");
 				break;
 			case (".jpg"):
 			case (".jpeg"):
 					$srcImg = imagecreatefromjpeg("$imageDirectory/$imageName");
 					if (function_exists('imagecreatetruecolor')){
 						$thumbImg = imagecreatetruecolor($width,$height);
 						imagecopyresampled($thumbImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}else{
 						$thumbImg = imagecreate($width,$height);
 						imagecopyresized($thumbImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}
 					imagejpeg($thumbImg,"$thumbDirectory/$thumbName",75);
 				break;
 			case (".png"):
 					$srcImg = imagecreatefrompng("$imageDirectory/$imageName");
 					if (function_exists('imagecreatetruecolor')){
 						$thumbImg = imagecreatetruecolor($width,$height);
 						imagecopyresampled($thumbImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}else{
 						$thumbImg = imagecreate($width,$height);
 						imagecopyresized($thumbImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}
 					imagepng($thumbImg,"$thumbDirectory/$thumbName");
 					
 				break;
 			default:
 					return false;
 					break;
 		}
 		
 	}else{ 		
 		//image is smaller than the thumbs  no need to resize
 		return false;
 	}
 	// free resource 
 	// Thanks to jimmy for pointing it out 
 	imagedestroy($srcImg);
 	imagedestroy($thumbImg);
 	return true;
 }

 // Resizing Picture functions 
function  vbimghost_resize($imageDirectory,$imageName,$imageWidth,$imageHeight,$resizeWidth,$resizeHeight,$resizeQuality,$resizeName,$imageExt){
 	
 	//Get Resize presentage for width and hieght 
 	
 	$hper = $imageHeight / $resizeHeight;
 	$wper = $imageWidth / $resizeWidth;
 	
 	
 		//check to rezise by widht or hieght 
 		if ($wper > $hper)
 			// get ratio using width
 			$ratio = $resizeWidth / $imageWidth;
 		else
 			// get ratio using height
 			$ratio = $resizeHeight / $imageHeight;
 			
 		// calculate new width and hight for the thumb image 
 		$width = $ratio * $imageWidth;
 		$height = $ratio * $imageHeight;
 		$width = round($width);
 		$height= round($height);
 		
 		switch ($imageExt){
 			case (".gif"):
 					$srcImg = imagecreatefromgif("$imageDirectory/$imageName");
 					$colortrans = imagecolortransparent($srcImg);
 					$resizeImg = imagecreate($width, $height);
 					imagepalettecopy($resizeImg,$srcImg);
 					imagefill($resizeImg,0,0,$colortrans);
 					imagecolortransparent($resizeImg,$colortrans);
 					imagecopyresized($resizeImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					imagegif($resizeImg,"$imageDirectory/$resizeName");
 				break;
 			case (".jpg"):
 			case (".jpeg"):
 					$srcImg = imagecreatefromjpeg("$imageDirectory/$imageName");
 					if (function_exists('imagecreatetruecolor')){
 						$resizeImg = imagecreatetruecolor($width,$height);
 						imagecopyresampled($resizeImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}else{
 						$resizeImg = imagecreate($width,$height);
 						imagecopyresized($resizeImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}
 					imagejpeg($resizeImg,"$imageDirectory/$resizeName",$resizeQuality);
 				break;
 			case (".png"):
 					$srcImg = imagecreatefrompng("$imageDirectory/$imageName");
 					if (function_exists('imagecreatetruecolor')){
 						$resizeImg = imagecreatetruecolor($width,$height);
 						imagecopyresampled($resizeImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}else{
 						$resizeImg = imagecreate($width,$height);
 						imagecopyresized($resizeImg,$srcImg,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
 					}
 					imagepng($resizeImg,"$imageDirectory/$resizeName");
 					
 				break;
 			default:
 					return false;
 					break;
 		}
 		
 	// free resource 
 	// Thanks to jimmy for pointing it out 
 	imagedestroy($srcImg);
 	imagedestroy($resizeImg);
 	return true;
 }
 
// vbimghost_watermark($vbimghost['imagedir'],$imguname,$vbimghost['watermarkfile'],$vbimghost['watermarkpos'],$fileext);
 
function vbimghost_watermark($imgdir,$imgname,$wmimg,$wmpos,$wmtype,$wmtext,$wmtextc,$wmtexts,$imgext,$wmmargin){
	$WatermarkPos = $wmpos;
	if ($wmtype){
		//watermark text 
		$img = $imgdir."/".$imgname;
		switch ($imgext){
			case (".png"):
				$img2 = imagecreatefrompng($img);
				break;
			case (".jpg"):
			case (".jpeg"):
				
				$img2 = imagecreatefromjpeg($img);
				$tempimage = imagecreatetruecolor(imagesx($img2), imagesy($img2));
				imagecopy($tempimage, $img2, 0, 0, 0, 0, imagesx($img2), imagesy($img2));
				$img2=$tempimage;
				break;
			default:
				return false;
				break;
		}
		
		$color = eregi_replace("#","", $wmtextc);
  		$red = hexdec(substr($color,0,2));
  		$green = hexdec(substr($color,2,2));
  		$blue = hexdec(substr($color,4,2));
  		$text_color = imagecolorallocate($img2, $red, $green, $blue);
		$text_height=imagefontheight($wmtexts);
  		$text_width=strlen($wmtext)*imagefontwidth($wmtexts);
  		
  		if($WatermarkPos) {
  			
  			 $pos_x=(int)(imagesx($img2) / 2) - ($text_width / 2);
  			 $pos_y= (int)(imagesy($img2) / 2) - ($text_height / 2);
  		}else{
  			$pos_x = imagesx($img2) - $text_width  - $wmmargin;
			$pos_y = imagesy($img2) - $text_height - $wmmargin;
  		}
  		 imagestring($img2, $wmtexts, $pos_x, $pos_y, $wmtext, $text_color);
  		switch ($imgext){
			case (".png"):
					imagepng($img2,$img,100);
					break;
			case (".jpg"):
			case (".jpeg"):
				
					imagejpeg($img2,$img,100);
					imagedestroy($tempimage);
				break;
			default:
				return false;
				break;
		}
  		 
	}else{
		//water mark image 
		$tmp =  $imgdir."/".$wmimg;
		$wmimg = $tmp;
		$val=getimagesize($wmimg);
		switch (vbimghost_imgtype($val[2])){
			case (".png"):
				$wmc = imagecreatefrompng($wmimg);
				break;
			case (".jpg"):
			case (".jpeg"):
				$wmc = imagecreatefromjpeg($wmimg);
				break;
			default:
				return false;
				break;
		}
		
		switch ($imgext){
			case (".png"):
					$img = imagecreatefrompng($imgdir."/".$imgname);
					imagealphablending($wmc, false);
					imagesavealpha($wmc, true);
					if($img && $wmc) {
						if($WatermarkPos) {
							$pos_x = (imagesx($img) / 2) - (imagesx($wmc) / 2);
							$pos_y = (imagesy($img) / 2) - (imagesy($wmc) / 2);
						} else {
							$pos_x = imagesx($img) - imagesx($wmc) - $wmmargin;
							$pos_y = imagesy($img) - imagesy($wmc) - $wmmargin;
						}
						imagecopy($img,$wmc, $pos_x, $pos_y, 0, 0, imagesx($wmc), imagesy($wmc));
					}
					imagepng($img,$imgdir."/".$imgname);				
				break;
			case (".jpg"):
 			case (".jpeg"):
 					$img = imagecreatefromjpeg($imgdir."/".$imgname);
					$tempimage = imagecreatetruecolor(imagesx($img), imagesy($img));
					imagecopy($tempimage, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
					$img=$tempimage;
					imagealphablending($wmc, false);
					imagesavealpha($wmc, true);
					if($img && $wmc) {
						if($WatermarkPos) {
							$pos_x = (imagesx($img) / 2) - (imagesx($wmc) / 2);
							$pos_y = (imagesy($img) / 2) - (imagesy($wmc) / 2);
						} else {
							$pos_x = imagesx($img) - imagesx($wmc) - $wmmargin;
							$pos_y = imagesy($img) - imagesy($wmc) - $wmmargin;
						}
						imagecopy($img,$wmc, $pos_x, $pos_y, 0, 0, imagesx($wmc), imagesy($wmc));
					}
					imagejpeg($img,$imgdir."/".$imgname);
					imagedestroy($tempimage);
 				break;
 			default:
 				return false;
 				break;
		}
		
	}//else
	
}
 

 	//Watermark Function
function vbimghost_imgtype ($val){
	
	$types = array(
       1 => '.GIF',
       2 => '.JPG',
       3 => '.PNG',
       4 => '.SWF',
       5 => '.PSD',
       6 => '.BMP',
       7 => '.TIFF',
       8 => '.TIFF',
       9 => '.JPC',
       10 => '.JP2',
       11 => '.JPX',
       12 => '.JB2',
       13 => '.SWC',
       14 => '.IFF',
       15 => '.WBMP',
       16 => '.XBM'
   );
   if ($val > 16 )
   		return  false;
   else
  	 	return strtolower($types[$val]);
}

function vbimghost_filmstrip ($userid,$cimgid,$vbimghost){
	
	//input owner  userid
	//		curent imgid
	//		global settings
	
	global $vbulletin,$db;
	$imagearray = array();
	$filmk = array();
	if ($userid != $vbulletin->userinfo['userid']){
		$wherecon = " AND imgprivate='0'";
	}else{
		$wherecon ="";
	}
	$imgids = $db->query_read("
			SELECT  imgid,imgfile,imgwidth,imgdate
			FROM " . TABLE_PREFIX . "vbimghost
			WHERE userid='".$userid."'".$wherecon."
			ORDER BY imgdate DESC
			");
	
	while($result = $vbulletin->db->fetch_array($imgids))
	{
		$imagearray[] = $result;
		$filmk[] = $result['imgid'];
	}
	
	$key = array_search($cimgid, $filmk);
	$half = ceil($vbimghost['filmstripcount'] / -2);
	$count = $vbimghost['filmstripcount'];
	
	while ($half < $vbimghost['filmstripcount'] AND $count !=0 ){
		$val = $key+$half;
		$rimgid = $filmk[$val];
		
		if($rimgid){
			
				if ($half != $key){
			
					$filmarr["$rimgid"] = $imagearray[$val];
	
					if ($half == 0)
					{
						$filarr["$rimgid"]['filmarrow'] = '&nbsp;';
					}elseif ($half > 0){
						$filarr["$rimgid"]['filmarrow'] = str_repeat('&gt;', abs($half));
					}else{
						$filarr["$rimgid"]['filmarrow'] = str_repeat('&lt;', abs($half));
					}
					
				}
		
		}
		$half++;
		$count--;
	}
	
	
	if (!empty($filarr))
	{
		$fwidth = round(100 / sizeof($filmarr)) . '%';
		foreach ($filmarr AS $fimgid => $imgfo){
			$imgfo['url'] = "vbimghost.php?do=displayimg&amp;imgid=".$fimgid;
			$thumb=0;
			if (file_exists($vbimghost['imagedir']."/".$vbimghost['imgthumpre'].$imgfo['imgfile'])){
				$imgfo['thumb']= $vbulletin->options['bburl']."/".$vbimghost['imagedir']."/".$vbimghost['imgthumpre'].$imgfo['imgfile'];
			}else{
				$thumb=1;
				if ($imgfo['imgwidth'] > $vbulletin->options['imgthumw'])
					$imgfo['imgwidth'] = $vbulletin->options['imgthumw'];
				$imgfo['thumb']=$vbulletin->options['bburl']."/".$vbimghost['imagedir']."/".$imgfo['imgfile'];
				
			}
			$imgfo['filmarrow']= $filarr[$fimgid]['filmarrow'];
			eval('$filmbits .= "' . fetch_template('vbimghost_filmsbit') . '";');
			
		}
	return $filmbits;
	}
		
}
	

?>