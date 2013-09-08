<?php

/*
Stop Forum Spam - vBulletin Integration by King Kovifor
Copyright 2009+ King Kovifor
Stop Forum Spam Database: http://www.stopforumspam.com
Code may not be redistributed or reused without prior consent of King Kovifor.
*/

class StopForumSpam
{
	// Tolerance Level
	var $tolerance_level;
	
	// vBulletin XML Parser
	var $username;
	var $ip;
	var $email;
	
	// This is set to false by default, after a new registration is run, if it is in the Stop Forum Spam database $found is set to true.
	var $found = false;
	
	// This is set to 0 by default, and is increased by 1 for every match in the Stop Forum Spam database. The verify() function resets to zero, incase it was called multiple times.
	var $tolerance = 0;
	
	// This is set to false by default, and if a new register fails the test (meets the required number of criteria or more), this will be set to true.
	var $takeaction = false;
	
	// Constructs the class set up, sets the $registry and $xml objects.
	function StopForumSpam($tolerance)
	{
		require_once('class_xml.php');
		$this->tolerance_level = $tolerance;
	}
	
	// Queries the Stop Forum Spam database using the username and verifies.
	private function fetch_username($username)
	{
		$xml = @file_get_contents('http://www.stopforumspam.com/api?username=' . $username);
		$xml_username = new vB_XML_Parser($xml);
			
		$this->username = $xml_username->parse();
	}
	
	// Queries the Stop Forum Spam database using the ip and verifies.
	private function fetch_ip($ip)
	{
		$xml = @file_get_contents('http://www.stopforumspam.com/api?ip=' . $ip);
		$xml_ip = new vB_XML_Parser($xml);
			
		$this->ip = $xml_ip->parse();
	
	}
	
	// Queries the Stop Forum Spam database using the email and verifies.
	private function fetch_email($email)
	{
		$xml = @file_get_contents('http://www.stopforumspam.com/api?email=' . $email);
		$xml_email = new vB_XML_Parser($xml);
			
		$this->email = $xml_email->parse();

	}
	
	// Uses data retrieved through the fetch_*() functions (it also calls them) and determines whether this is a known spammer.
	public function verify($username, $ip, $email)
	{
		$this->fetch_username($username);
		$this->fetch_ip($ip);
		$this->fetch_email($email);
		
		$this->tolerance = 0;
		
		if($this->username['appears'] == 'yes')
		{
			$this->found = true;
			$this->tolerance = $this->tolerance + 1;
		}
		
		if($this->ip['appears'] == 'yes')
		{
			$this->tolerance = $this->tolerance + 1;
		}
		
		if($this->email['appears'] == 'yes')
		{
			$this->tolerance = $this->tolerance + 1;
		}
		
		// Just making sure that if they are all no, there isn't a false positive.
		if($this->username['appears'] == 'no' AND $this->ip['appears'] == 'no' AND $this->email['appears'] == 'no')
		{
			$this->tolerance = 0;
		}
	}
	
	// Performs Specified Action
	public function take_action()
	{
		$this->takeaction = $this->check_tolerance();
	
		if($this->takeaction)
		{
			eval(standard_error(fetch_error('sfs_rejection')));
		}
		else
		{
			return false;
		}
	}
	
	// Verifies against admistrative specified tolerance.
	private function check_tolerance()
	{
		if($this->tolerance >= $this->tolerance_level)
		{
			$fails = true;
		}
		
		return $fails;
	}
}

