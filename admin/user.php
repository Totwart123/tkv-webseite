<?php

/* Konfiguration laden */
include ("../conf/config.inc.php");

/* PHP-Libs einfügen */
include ("../lib/error.lib.php");
include ("../lib/db.lib.php");
include ("../lib/auth.lib.php");

include ("../lib/user.lib.php");

/* Authentifizierung */

$privs = auth();

if (!$privs)
	deny();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title></title>
<link href="../tkv.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php

if ($action == "add") {
	echo $password . $password2;
					
	$add_error = add_user ($username,$password, $password2);
	
	if (!$add_error)		
		status ("Der Benutzer <em>$username</em> wurde
				in die Benutzer-Datenbank eingetragen!");		
} 

else if ($action == "edit") 
{

	$edit_error = edit_user ($id, $u_name);
					
	if (!$edit_error)
	{
	
		status ("Die Daten des Benutzers <em>$u_name</em> wurden
				geändert!");
	
	}
	
}

else if ($action == "change_pass")
{

	$change_pass_error = change_pass_user ($id, $password, $password2);
	
}

else if ($action == "delete")
{

	$delete_error = delete_user ($id);

	if (!$delete_error)
	{
	
		status ("Der Benutzer #$id wurde gelöscht!");
	
	}

}

//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//-\\-//

if ($view == "add" || $add_error)
{
	
	if ($add_error & NOUSER)
	{
	
		warning ("Es wurde kein Benutzername angegeben!");
	
	}
	if ($add_error & PASSWORDMISMATCH)
	{
	
		warning ("Die Passwörter waren nicht gleich!");
	
	}
	
	if ($add_error & PASSWORDLENGTH)
	{
	
		warning ("Das Passwort war zu kurz!");
	
	}
?>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
	<table>
		<tr>
			<td><strong>Username:</strong></td>
			<td>
				<input type="text" name="username" size="16"
					maxlength="16" value="<?php echo $username ?>">
			</td>
		</tr>		
		<tr>
			<td><strong>Passwort:</strong></td>
			<td>
				<input type="password" name="password" size="16"
					maxlength="16">
			</td>
		</tr>
		<tr>
			<td><strong>Passwort (Wdh.):</strong></td>
			<td>
				<input type="password" name="password2" size="16"
					maxlength="16">
			</td>
		</tr>
	</table>	
	<input name="action" value="add" type="submit">
</form>

<?php
}


else if ($view == "edit" || $edit_error)
{

	if ($edit_error & NOUSER)
	{
	
		warning ("Es wurde kein Benutzername angegeben!");
		
	}
	
	if ($edit_error & PASSWORDMISMATCH)
	{
		
		warning ("Die Passwörter waren nicht gleich!");
		
	}		
	if ($edit_error & PASSWORDLENGTH)
	{
		
		warning ("Das Passwort war zu kurz!");
		
	}	
		
	$sql = "SELECT * FROM users WHERE 
			u_id = '$id'"; 
			
	$result = db_query($sql, $link);
		
	$row = mysql_fetch_array ($result)
?>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
	<input type="hidden" name="change" value="<?php echo $id ?>">
	<table>
		<tr>
			<td>
				<strong>Username:</strong>
			</td>
			<td>
				<input type="text" name="u_name" size="16"
					maxlength="16" value="<?php echo $row["u_name"] ?>">
			</td>
		</tr>		
	</table>
	<input type="hidden" name="id" value="<?php echo $id ?>">
	<input name="action" value="edit" type="submit">
</form>

[ <a href="<?php echo $SCRIPT_NAME ?>?view=change_pass&id=<?php echo $id ?>">Passwort ändern</a> ]

<?php
}

else if ($view == "change_pass" || $change_pass_error)
{

	if ($change_pass_error & PASSWORDMISMATCH)
	{
		
		warning ("Die Passwörter waren nicht gleich!");
		
	}		
	if ($change_pass_error & PASSWORDLENGTH)
	{
		
		warning ("Das Passwort war zu kurz!");
	
	}
?>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
	<table>
		<tr>
			<td><strong>Passwort:</strong></td>
			<td>
				<input type="password" name="password" size="16"
					maxlength="16">
			</td>
		</tr>
		<tr>
			<td><strong>Passwort (Wdh.):</strong></td>
			<td>
				<input type="password" name="password2" size="16"
					maxlength="16">
			</td>
		</tr>
	</table>	
	<input type="hidden" name="id" value="<?php echo $id ?>">	
	<input name="action" value="change_pass" type="submit">
</form>

<?php
}

else if ($view == "delete")
{

	$sql = "SELECT * FROM users WHERE u_id = '$id'";

	$result = db_query ($sql);
	if (($row = mysql_fetch_array ($result)))
	{
?>
Soll der Benuter <strong><?php echo $row["u_name"] ?></strong> wirklich gelöscht werden?


<!-- <p>[ <a href="<?php echo $SCRIPT_NAME ?>?action=delete&id=<?php echo $id ?>">ja</a> | <a href="<?php echo $SCRIPT_NAME ?>">nein</a> ]</p> -->

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
	<input type="hidden" name="id" value="<?php echo $id ?>">	
	<input name="action" value="delete" type="submit">
	<input name="action" value="nein" type="submit">
</form>


<?php
	}
	
	else
	{
	
		warning ("Es existiert kein Benutzer #$id!");
		$view = 0;
	
	}

}

if (!$view && !$add_error && !$edit_error && !$change_pass_error) {

	$sql = "SELECT * FROM users"; 

	$result = db_query ($sql);

?>

<table border="1">
	<tr>
		<th>Benutzername</th>
	</tr>
	<?php
	while ($row = mysql_fetch_array ($result)) {
	?>
	<tr <?php /* unterscheidung nach aktiv */?> >
		<td><?php echo $row["u_name"] ?></td>
		<td><a href="mailto:<?php echo $row["u_email"] ?>"><?php echo $row["u_email"] ?></a></td>
		<td>
			[ <a href="<?php echo $SCRIPT_NAME ?>?view=edit&id=<?php echo $row["u_id"] ?>"> bearbeiten</a>
			| <a href="<?php echo $SCRIPT_NAME ?>?view=delete&id=<?php echo $row["u_id"] ?>">löschen</a> ]
		</td>
	</tr>
	<?php
	}
	?>
</table>
<font size="-1">

<br>

[ <a href="<?php echo $SCRIPT_NAME ?>?view=add">Benutzer hinzufügen</a> ]

<?php
}

db_close ($link);
?>

</body>
</html>
