<?php

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

define (VBSFS_NO_TEST, -1);
define (VBSFS_PASS, 0);
define (VBSFS_FAIL, 1);
define (VBSFS_HIT_BUT_PASS, 2);
define (VBSFS_REMOTE_ERROR, 3);

define (VBSFS_BLOCKED, 1);
define (VBSFS_ALLOWED, 0);

//sfsuserHash is used to update the log system with a user hash after a sucessful add_member
//sfsCacheHit is used so that we dont update the database if we have a valid cache hit.
global $sfsuserHash, $sfsCacheHit;


// this function connects to stopforumspam.com and attempts to get the XML file for the query
// if it fails with CURL (if installed and selected), it falls back to get_file_contents.
// if that fails, the the function returns VBSFS_REMOTE_ERROR
function getSFSXML($url) {
		global $vbulletin;

		$curl_installed = (function_exists('curl_init'));
		$curl_failed = false;
						
    if (($vbulletin->options['vbstopforumspam_curluse']) && ($curl_installed)) {

        $cUrl = curl_init();
        curl_setopt($cUrl, CURLOPT_URL, $url);
        curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cUrl, CURLOPT_TIMEOUT, '15');

        if (($vbulletin->options['vbstopforumspam_curlip']) && ($vbulletin->options['vbstopforumspam_curlport'])) {
            curl_setopt($cUrl, CURLOPT_PROXY, "{$vbulletin->options['vbstopforumspam_curlip']}:{$vbulletin->options['vbstopforumspam_curlport']}");
        }
                       
        $pageContent = curl_exec($cUrl);   
     
				if (curl_errno($cUrl)) {         	
					  // curl failed, close the object and fail back to get_file_contents
            curl_close($cUrl);   
    				if (!($pageContent = @file_get_contents($url))) 	return VBSFS_REMOTE_ERROR;        
        } else {        	
        	  // be safe, close the curl object
        		curl_close($cUrl);
        }
    } else {        
    		if (!($pageContent = @file_get_contents($url))) return VBSFS_REMOTE_ERROR;
    }
    
    // parse with php4
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
		xml_parse_into_struct($parser, $pageContent, $vals, $index); 
		xml_parser_free($parser);    
    
    // result will (well, at time of coding, should) always return a appears value
    $appears = ($vals[$index['APPEARS'][0]]['value']);
    
		// test to see if we have a conncetion or page error - basic test is "is the result valid XML with a appears in the schema"
    if (!$appears) return VBSFS_REMOTE_ERROR;
    
    if ( $appears == 'yes') {
      // let mysql do the datediff as all dates are in mysql format anyway
    	$lastseen = $vals[$index['LASTSEEN'][0]]['value'];
    	$sql = "SELECT DATEDIFF(NOW(), '" . $lastseen . "') AS DAYS;";
    	$result = $vbulletin->db->query($sql);
    	$line = $vbulletin->db->fetch_array($result);
    	$days = $line['DAYS'];    	
    	$spambot = ($days < $vbulletin->options['vbstopforumspam_expire_day']) ;
    } else 
    	$spambot = VBSFS_PASS;
    
    return $spambot;
}

// This function returns true if a spambot is found in the cache database.
// It first purges old records
function sfsCacheHit ($data, $field) {
	global $vbulletin, $sfsPurged, $sfsCacheHit;
	
	$sfsCacheHit = false;
	
	if (!$sfsPurged) {
			// purge cache database of old records
			$sql = "DELETE FROM " . TABLE_PREFIX . "vbstopforumspam_remotecache WHERE date < DATE_SUB(NOW(), INTERVAL " . (int)$vbulletin->options['vbstopforumspam_remote_cache'] . " MINUTE);";
			$vbulletin->db->query($sql);
			$sfsPurged = true;
	}
		
	// get rows from database that contain the data (cache hit)
	$sql = "SELECT spambot FROM " . TABLE_PREFIX . "vbstopforumspam_remotecache WHERE field = '$field' AND data = '" . addslashes($data) . "' LIMIT 1 ;";
	$result = $vbulletin->db->query($sql);

	$line = $vbulletin->db->fetch_array($result);
  if (!empty($line)) {  		  
  	  $sfsCacheHit = true;
      
  		// if we have data in the results, check if we have a spam hit
  		if ($line['spambot'] > 0) return VBSFS_FAIL;
  		if ($line['spambot'] == 0) return VBSFS_HIT_BUT_PASS;
  		// return VBSFS_FAIL if we have a spam hit or all return 2 if we have a hit but its not a spambot  			
  }  
	return VBSFS_PASS;
}


function updateCache($data, $spambot, $field) {
	global $vbulletin, $sfsCacheHit;		
	
	if (!$sfsCacheHit) { 
		$sql = "INSERT HIGH_PRIORITY IGNORE INTO " . TABLE_PREFIX . "vbstopforumspam_remotecache (date, data, spambot, field) VALUES (now(), '" . addslashes($data) . "', '$spambot', '$field');";
		$result = $vbulletin->db->query($sql);	
	}
}	


function checkSFSSpam($data, $field) {
	global $vbulletin;
			
	// is the spambot in the query database?  this is what happens when you code after a bottle of wine :)

  if ($vbulletin->options['vbstopforumspam_testfield_'. $field]) {
		switch (sfsCacheHit($data, $field)) {
			case 1:
			  // spambot found in cache
				$spambot = VBSFS_FAIL;
				break;
			case 2:
			  // found in cache but clean
				$spambot = VBSFS_PASS;
				break;
			case 0:  //false = not in cache database, need to test
	  		$spambot = getSFSXML($vbulletin->options['vbstopforumspam_url_check_' . $field] . urlencode($data));
	  		break;
	  	} //switch	  	  	  
	  		  	
	  	// if we have a connection/page error, we dont want to store the result in the cache as a false negative
	  	if ($spambot !== VBSFS_REMOTE_ERROR) {
	  			updateCache($data, $spambot, $field);
	  	}
	  	
  } else return VBSFS_NO_TEST; // no test performed
	
 	return $spambot;
}


function sfsLog($field, $data, $username, $email, $ip, $action) {
	global $vbulletin, $sfsuserHash;
	
	$prefix = "Result on field " . $field . " - ";
	if ($field	== "") $prefix = "";
	$message = $prefix . $data ;	
	$sfsuserHash = md5(date('l jS \of F Y h:i:s A') . $username); // just something random enough to hash as a unique log entry
	$sql = "INSERT INTO " . TABLE_PREFIX . "vbstopforumspam_log (date, ipaddress, email, username, message, blocked, userhash) VALUES (now(), '" . addslashes($ip) . "' , '" . addslashes($email) ."', '" . addslashes($username) . "', '" . addslashes($message) . "', $action, '$sfsuserHash');";	
		
	$logresult = $vbulletin->db->query($sql);
}


function sfsActions($field, $data, $username, $email, $ip, $resultcode) {
	global $vbulletin;
		
		$resultcode = (int)$resultcode;
		
		if ($resultcode == VBSFS_REMOTE_ERROR) {	  	// remote connection/page error
	  		if ($vbulletin->options['vbstopforumspam_result'] == 0) { // Set to allow listed registrations/spammers.  Need to test for this first
	  			  //log and let them in
		  			sfsLog($field, $data . " [REMOTEERR] Allowed by policy", $username, $email, $ip, VBSFS_ALLOWED);
		  	} else { //we have connection error, test the block/allow on error policy		  		
		  		if ($vbulletin->options['vbstopforumspam_result_timeout'] == 1) { // set to reject registrations on connection error
				  		sfsLog($field, $data . " [REMOTEERR] Unverfied and rejected by policy ", $username, $email, $ip, VBSFS_BLOCKED);
		  				standard_error(fetch_error('vbstopformspam_reject_connectionerror',$query));  				  		
		  		} else { // connection error but set to allow registrations on connection error
		  			  sfsLog($field, $data . " [REMOTEERR] Unverfied but allowed by policy", $username, $email, $ip, VBSFS_ALLOWED);		  			
		  		}			  		
		  	}		  			  	
	  } elseif ($resultcode == VBSFS_FAIL) { // we dont have a remote connection/page error, so here we need to sort the failed tests out.	  	
		  	if ($vbulletin->options['vbstopforumspam_result'] == 0) { // Set to allow listed registrations/spammers, so log and let them in		  
		  			sfsLog($field, $data . " - Spammer but allowed by policy", $username, $email, $ip, VBSFS_ALLOWED);
				 } else { // no connection error and not allowed by policy, log and reject
		  			sfsLog($field, $data . " - Spammer and rejected by policy", $username, $email, $ip, VBSFS_BLOCKED);
		  			standard_error(fetch_error('vbstopformspam_reject',$query));
		  	 }
	 }
}

function sfsProcess() {
	
	global $vbulletin;
			
	$ip = $vbulletin->session->vars['host'];
	$username = $vbulletin->userinfo['username'];
	$email = $vbulletin->GPC['email'];

	// todo handle the null error results.
	$result = checkSFSSpam($username, 'username');
	if ($result !== VBSFS_NO_TEST) {		
		sfsActions('username', $username, $username, $email,$ip, $result); 
	}
	
	$result = checkSFSSpam($ip, 'ip');	
	if ($result !== VBSFS_NO_TEST) {
			sfsActions('ip', $ip, $username, $email, $ip, $result);
	}
	
	$result = checkSFSSpam($email, 'email');
	if ($result !== VBSFS_NO_TEST) {
			sfsActions ('email', $email, $username, $email, $ip, $result);
	}		
	
	// if we got here, then the registration was allowed regardless of being a spambot, so log it above the warnings if any                         
  sfsLog($field, $data . "Allowed registration", $username, $email, $ip, VBSFS_ALLOWED);	
                           	
}


function sfsUpdateLog() {
	global $vbulletin, $sfsuserHash;
	$sql = "UPDATE " . TABLE_PREFIX . "vbstopforumspam_log SET user_id = '" . $vbulletin->userinfo['userid'] . "' WHERE userhash = '$sfsuserHash';";
	$logresult = $vbulletin->db->query($sql);
	//register_addmember_complete
}