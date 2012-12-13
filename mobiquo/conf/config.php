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
$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();
if(file_exists('./global.php'.SUFFIX)){
	require_once('./global.php'.SUFFIX);
} else {
	require_once('./global.php');
}
chdir(CWD1);
class mobiquo_config
{
	function get_config(){

		global $vbulletin,$stylevar;
		$config = array();
		$config = $this->read_config_file();
			

		$config['forum_name'] = $vbulletin->options['bbtitle'];
		$config['forum_description'] = '';
		$config['log_url'] = '';

		if($config[is_open] ==1  && $vbulletin->options['bbactive']==1){
			$config['is_open'] = 1;
		} else {
			$config['is_open'] = 0;
		}
	 $config['support_md5'] = 1;
	 $config['report_post'] = 1;
	 $config['report_pm']   = 1;
	 $config['goto_unread'] = 1;
	 $config['goto_post']   = 1;
	 if(($vbulletin->usergroupcache['1']['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']) && $config[guest_okay] == 1){
	 	$config['guest_okay'] = 1;
	 }else{
	 	$config['guest_okay'] = 0;
	 }
	 $config['charset'] = $stylevar['charset'];
	 return $config;

	}
	function read_config_file(){
		$getcwd = getcwd();

		$file = CWD1.'/conf/config.txt';
		if(function_exists('file_get_contents')){
			$tmp = file_get_contents($file);
		}else{
			$handle = fopen($file,'rb');
			$tmp = fread($handle,filesize($file));
			fclose($handle);
		}
		$tmp = preg_replace('/\/\*.*?\*\//si','',$tmp);
		$tmpDatas = explode("\n",$tmp);
		foreach ($tmpDatas as $d){
			if(!empty($d) && (strpos($d,'=') !== false)){
				list($key,$value) = explode('=',$d);
				$key = trim($key);
				$value = trim($value);
				$datas[$key] = $value;
			}
		}
		return $datas;
	}
}
?>
