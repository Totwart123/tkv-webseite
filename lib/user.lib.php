<?php

$min_password_lenght = 5;

function add_user ($name, $password, $password2)
{

	$error = 0;
	global $min_password_length;
	
	if (!$name)
	{
	
		$error = NOUSER;
	
	}
	
	
	if ($password != $password2)
	{
		
		$error |= PASSWORDMISMATCH;
	
	}
			
	if (strlen ($password) < $min_password_length)
	{
		
		$error |= PASSWORDLENGTH;
	
	}	
			
	if ($error)
	{
	
		return $error;
		
	}
	
		
	$sql = "INSERT INTO users VALUES
			(NULL, '$name', PASSWORD('$password'))";
		
			
			
	if (!db_query($sql))
	{
	
		$error |= DBERR;	
		
	}
	
	return $error;
	
}

function delete_user ($id)
{

	$sql = "DELETE FROM users WHERE u_id = '$id'";

	if (db_query ($sql))
	{
	
		$error |= DBERR;
		
	}
	
	return $error;
	
}

function edit_user ($id, $name)
{

	$error = 0;
	global $min_password_length;
	
	if (!$name)
	{
	
		$error = NOUSER;
	
	}
	
	if ($error)
	{
	
		return $error;
		
	}
		
	$sql = "UPDATE users SET u_name = '$name'
			WHERE u_id = '$id'";
			
	if (!db_query($sql))
	{
	
		$error |= DBERR;	
	
	}
	
	return $error;

}

function change_pass_user ($id, $password, $password2)
{

	$error = 0;
	global $min_password_length;
	
	if ($password != $password2)
	{
		
		$error |= PASSWORDMISMATCH;
	
	}
			
	if (strlen ($password) < $min_password_length)
	{
		
		$error |= PASSWORDLENGTH;
	
	}				
		
	if ($error)
	{
	
		return $error;
		
	}
		
	$sql = "UPDATE users SET  u_password = PASSWORD('$password')
			WHERE u_id = '$id'";
			
	if (!db_query($sql))
	{
	
		$error |= DBERR;	
	
	}
		
	return $error;

}

?>
