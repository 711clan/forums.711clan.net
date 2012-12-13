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
function return_fault_func($xmlrpc_params){
	$params = php_xmlrpc_decode($xmlrpc_params);
	$faultCode = $params[0];
	$faultString = $params[1];
	return new xmlrpcresp('',$faultCode,$faultString);

}
?>
