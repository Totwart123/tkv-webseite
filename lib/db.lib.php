<?php

function db_error ($link) {
	warning (mysql_errno($link).": ".mysql_error($link));
}

function db_connect () {
	global $host, $user, $pass, $db;

	$link = mysql_connect ($host, $user, $pass);

	if (!mysql_select_db ($db, $link))
		db_error ($link);
		
	return $link;
}

function db_query ($sql) {
	global $link;
	
	if (!$link)
		$link = db_connect ();

	if (!($result = mysql_query ($sql)))
		db_error ($link);
		
	return $result;
}

function db_close () {
	global $link;
	
	if ($link)
		mysql_close ($link);
}

?>