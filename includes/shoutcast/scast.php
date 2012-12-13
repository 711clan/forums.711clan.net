<?php
/* -----------------11/15/2006-----------------
This is a Nice hack to add Shoutcast Status on your VB3.6 Forum Home.
Hope it helps !!!

Zachariah @ http://www.gzhq.net

SHOUTcast is a free-of-charge audio homesteading solution. It permits anyone
on the internet to broadcast audio from their PC to listeners across the
Internet or any other IP-based network (Office LANs, college campuses, etc.).

http://www.shoutcast.com

Zerro Queries Added

=======================================================
Tested On:

vBulletin 3.6
SHOUTcast Server v1.9.5

=======================================================
*/

$scgroups = explode(',',$vbulletin->options['scgrp_full']);
if (is_member_of($vbulletin->userinfo,$scgroups) OR $scgroups[0] == 0){

$scdef = $vbulletin->options['scname_full'];
$scip = $vbulletin->options['scip_full'];
$scport = $vbulletin->options['scport_full'];
$scpass = $vbulletin->options['scpass_full'];
$file = $vbulletin->options['scxml_full'];
$cache_tolerance = $vbulletin->options['scupdate_full'];
$placecast = $vbulletin->options['scplace_full'];
$scoff = $vbulletin->options['scoff_full'];


// Check if Cache needs an update
if (file_exists($file)){
    clearstatcache();  // filemtime info gets cached so we must ensure that the cache is empty
  	$time_difference = time() - filemtime($file); //   echo "$file was last modified: " . date ("F d Y H:i:s.", filemtime($file)) . "( " . $time_difference . " seconds ago) <br>" . "The cache is set to update every " . $cache_tolerance . " seconds.<br>";
}else{
	$time_difference = $cache_tolerance;  // force update
}

// Parses shoutcasts xml to make an effective stats thing for any website
$scfp = @fsockopen($scip, $scport, $errno, $errstr, 2); // Connect to the server

switch ($placecast){
  case 2:
    $placecast = '<body>';
  break;
  case 3:
    $placecast = '$header';
  break;
  case 4:
    $placecast = '<!-- what\'s going on box -->';
  break;
  case 5:
    $placecast = '<!-- end what\'s going on box -->';
  break;
  case 6:
    $placecast = '<!-- end logged-in users -->';
  break;
  case 7:
    $placecast = '7';
  break;
  default:
    $placecast = '$navbar';
}
// If server is off line show template
if(!$scfp){
    if($scoff == 1){
        if ($placecast != 7){
    	    if ($placecast != '<!-- end logged-in users -->'){
    		    $search_text = $placecast;
    			$vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_off'),$vbulletin->templatecache['FORUMHOME']);
    		}else{
    			$search_text = $placecast;
    			$vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_who_off'),$vbulletin->templatecache['FORUMHOME']);
    		}
    	}else{
    		eval('$scast = "' . fetch_template('forumhome_shoutcast_off') . '";');
    	}
    }
}else{
	if($time_difference >= $cache_tolerance){ // update the cache if need be
       // Get XML feed from server
        if($scsuccs!=1){
            if($scpass){
                fputs($scfp,"GET /admin.cgi?pass=$scpass&mode=viewxml HTTP/1.0\r\nUser-Agent: SHOUTcast Song Status (Mozilla Compatible)\r\n\r\n");
            }else{
                fputs($scfp,"GET /7.html HTTP/1.0\r\nUser-Agent: XML Getter (Mozilla Compatible)\r\n\r\n");
            }
            while(!feof($scfp)){
          	    $xmlfeed .= fgets($scfp, 8192);
			}
		    fclose($scfp);
		}
        // Output to cache file
    	$tmpfile = fopen($file,"w+");
    	$fp = fwrite($tmpfile,$xmlfeed);
    	fclose($tmpfile);
    	flush ();
        // Outputs the cached file after new data
    	$xmlcache = fopen($file,"r");
    	$page = '';
    	if($xmlcache){
     	    while (!feof($xmlcache)){
       		    $page .= fread($xmlcache, 8192);
     		}
    	    fclose($xmlcache);
    	}
	}else{
        // outputs the cached file
		$xmlcache = fopen($file,"r");
		$page = '';
		if($xmlcache){
 		    while (!feof($xmlcache)){
   			    $page .= fread($xmlcache, 8192);
 			}
		fclose($xmlcache);
		}
	}
}
if($scpass){
    //define  xml elements
    $loop = array("AVERAGETIME", "CURRENTLISTENERS", "PEAKLISTENERS", "MAXLISTENERS", "SERVERGENRE", "SERVERURL", "SERVERTITLE", "SONGTITLE", "SONGURL", "IRC", "ICQ" ,"AIM", "WEBHITS", "STREAMHITS", "LISTEN", "STREAMSTATUS", "BITRATE", "CONTENT");
    $y=0;
    while($loop[$y]!=''){
        $pageed = ereg_replace(".*<$loop[$y]>", "", $page);
      	$scphp = strtolower($loop[$y]);
        $$scphp = ereg_replace("</$loop[$y]>.*", "", $pageed);
      	if($loop[$y]==SERVERGENRE || $loop[$y]==SERVERTITLE || $loop[$y]==SONGTITLE || $loop[$y]==SERVERTITLE)
       	$$scphp = urldecode($$scphp);
    	;
        $y++;
    }
    //get song info and history
    $pageed = ereg_replace(".*<SONGHISTORY>", "", $page);
    $pageed = ereg_replace("<SONGHISTORY>.*", "", $pageed);
    $songatime = explode("<SONG>", $pageed);
    $r=1;
    while($songatime[$r]!=""){
        $t=$r-1;
      	$playedat[$t] = ereg_replace(".*<PLAYEDAT>", "", $songatime[$r]);
      	$playedat[$t] = ereg_replace("</PLAYEDAT>.*", "", $playedat[$t]);
      	$song[$t] = ereg_replace(".*<TITLE>", "", $songatime[$r]);
      	$song[$t] = ereg_replace("</TITLE>.*", "", $song[$t]);
      	$song[$t] = urldecode($song[$t]);
      	$dj[$t] = ereg_replace(".*<SERVERTITLE>", "", $page);
      	$dj[$t] = ereg_replace("</SERVERTITLE>.*", "", $pageed);
    	$r++;
    }
    $averagemin = "";
    $hours = intval(intval($averagetime) / 3600);
    $averagemin .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) : $hours. 'h&nbsp;';
    $minutes = intval(($averagetime / 60) % 60);
    $averagemin .= str_pad($minutes, 2, "0", STR_PAD_LEFT). 'm&nbsp;';
    $seconds = intval($averagetime % 60). 's';
    $averagemin .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
}else{
    //define all the variables to get (delte any ones you don't want)
    $page = ereg_replace(".*<body>", "", $page); //extract data
    $page = ereg_replace("</body>.*", ",", $page); //extract data
    $numbers = explode(",",$page);  //extract data
    $currentlisteners=$numbers[0];
    $streamstatus=$numbers[1];
    $peaklisteners=$numbers[2];
    $maxlisteners=$numbers[3];
    $currentlisteners=$numbers[4];
    $bitrate=$numbers[5];
    $song[0]=$numbers[6];
}

$listenamp = 'http://'.$scip.':'.$scport.'/listen.pls';
$listenlnk = 'http://'.$scip.':'.$scport.'';

// Player template requests
if ($_REQUEST['do'] == 'mp'){ // MP popup link
    eval('print_output("' . fetch_template('forumhome_shoutcast_mp') . '");');
}
if ($_REQUEST['do'] == 'rp'){ // RP popup link
    eval('print_output("' . fetch_template('forumhome_shoutcast_rp') . '");');
}
if ($_REQUEST['do'] == 'qt'){ // QT popup link
    eval('print_output("' . fetch_template('forumhome_shoutcast_qt') . '");');
}

// Server is online display stats
if($scfp){
    if($streamstatus == "1"){
	    if ($placecast != 7){
		    if ($placecast != '<!-- end logged-in users -->'){
                $search_text = $placecast;
                if($scpass){
                    $vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_full'),$vbulletin->templatecache['FORUMHOME']);
                }else{
                    $vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_lite'),$vbulletin->templatecache['FORUMHOME']);
                }
            }else{
			    $search_text = $placecast;
                if($scpass){
                    $vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_who_full'),$vbulletin->templatecache['FORUMHOME']);
                    $vbulletin->templatecache['FORUMHOME'] = str_replace('<!-- end what\'s going on box -->', $search_text.fetch_template('forumhome_shoutcast_who_link'),$vbulletin->templatecache['FORUMHOME']);
                }else{
                    $vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_who_lite'),$vbulletin->templatecache['FORUMHOME']);
                }
             }
		}else{
            if($scpass){
                eval('$scast = "' . fetch_template('forumhome_shoutcast_full') . '";');
            }else{
                eval('$scast = "' . fetch_template('forumhome_shoutcast_lite') . '";');
            }
        }
	// No source feed
	}else{
        if($scoff == 1){
        if ($placecast != 7){
		    if ($placecast != '<!-- end logged-in users -->'){
			    $search_text = $placecast;
				$vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_off'),$vbulletin->templatecache['FORUMHOME']);
			}else{
				$search_text = $placecast;
				$vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.fetch_template('forumhome_shoutcast_who_off'),$vbulletin->templatecache['FORUMHOME']);
			}
		}else{
		    eval('$scast = "' . fetch_template('forumhome_shoutcast_off') . '";');
        }
      }
	}
}
}
?>