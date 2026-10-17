<?php

DEFINE ("PRIV_STAFFEL", (1 << 0));
DEFINE ("PRIV_USER", (1 << 1));

function auth ($needed_privs = 0) {
	$auth = 0;
	global $PHP_AUTH_USER, $PHP_AUTH_PW;
	
	if (isset($PHP_AUTH_USER) && isset($PHP_AUTH_PW)) { 

		$sql = "SELECT * FROM users WHERE 
				u_name = '$PHP_AUTH_USER' AND 
				u_password = PASSWORD('$PHP_AUTH_PW')";
				
		$result = db_query ($sql, $link); 

		$row = mysql_fetch_array ( $result ); 
		
		if ($row != 0) {
			$auth = 1;
		}
		
	} 

	return $row["u_privs"];;	
}

function deny () {
	header( 'WWW-Authenticate: Basic realm="Restricted Access"' ); 
	header( 'HTTP/1.0 401 Unauthorized' );
	exit;
}

function get_id ()
{
	global $PHP_AUTH_USER;
	
	$sql = "SELECT u_id FROM users WHERE 
		u_name = '$PHP_AUTH_USER'";				
	$result = db_query ($sql); 
	$row = mysql_fetch_array ($result);
	
	return $row["u_id"]; 
}		

?>
