<?
include ("../header.inc.php");
include ("../conf/config.inc.php");

include ("../lib/error.lib.php");
include ("../lib/db.lib.php");
?>

<h2>Administration - Termine</h2>

<?
if ($add || $change) {
	if ($change) {
		$sql = "SELECT datum, termin, info FROM termine
			WHERE id = $change";

		$result = db_query ($sql);
		$row = mysql_fetch_array ($result);
	}
?>

<!-- Formular zum Hinzufügen/Ändern von Terminen - Anfang -->

<form action="<? echo $SCRIPT_NAME; ?>" method="POST">
	<table>
		<tr>
			<td>
				<strong>Datum:</strong><br>
				<font size="-1">(YYYY-MM-DD)</font>
			</td>
			<td>
				<input type="text" name="datum" value="<? echo $row ["datum"];?>">
			</td>
		</tr>
		<tr>
			<td><strong>Termin</strong>:</td>
			<td>
				<input type="text" name="termin" size="55" value="<? echo $row ["termin"];?>">				
			</td>
		</tr>
		<tr>
			<td>
				<strong>Nähere Informationen</strong>:<br>
				<font size="-1">(es darf HTML verwendet werden)</font>
			</td>
			<td width="100%">
				<textarea name="info" rows="10" cols="55"><? echo $row["info"]; ?></textarea>
			</td>
		</tr>
	</table>
	<input type="hidden" name="id" value="<? echo $change; ?>">
	<?
	if ($add) {
	?>
	<input name="submit_add" value="Absenden" type="submit">
	<?
	} else {
	?>	
	<input name="submit_change" value="Absenden" type="submit">
	<?
	}
	?>
	<input value="Formular löschen" type="reset">	
</form>

<!-- Formular zum Hinzufügen/Ändern von Terminen - Ende -->

<?
} elseif ($submit_add) {	
	$sql = "INSERT INTO termine
		(id, datum, termin, info) VALUES
		(NULL, \"$datum\", \"$termin\", \"$info\")";
	
	db_query ($sql);
	
	echo "Ein Termin wurde hinzugefügt.";
	
	echo "<hr>";
	echo '<div align="right"><font size="-1">[ <a href="'.$SCRIPT_NAME.'">zurück zu Administration der Termine</a> ]</font></div>';

} elseif ($submit_change) {
	$sql = "UPDATE termine SET
		datum = \"$datum\",
		termin = \"$termin\",
		info = \"$info\"
		WHERE id = $id";
	
	db_query ($sql);

	echo "Der Termin mit der ID $id (\"$termin\") wurde geändert.";
	
	echo "<hr>";
	echo '<div align="right"><font size="-1">[ <a href="'.$SCRIPT_NAME.'">zurück zu Administration der Termine</a> ]</font></div>';
} elseif ($delete) {

	global $dbHost, $dbUser, $dbPass, $dbName;
	
	mysql_connect ($dbHost, $dbUser, $dbPass);
	mysql_select_db ($dbName);
	
	$query .= "DELETE FROM termine WHERE id = $delete";
	
	mysql_query ($query);
	mysql_close ();
	
	echo "Der Termin mit der ID $delete wurde gelöscht.";
	
	echo "<hr>";
	echo '<div align="right"><font size="-1">[ <a href="'.$SCRIPT_NAME.'">zurück zu Administration der Termine</a> ]</font></div>';
	
} else {
?>


[ <a href="<? echo $SCRIPT_NAME; ?>?add=1">Termin hinzufügen</a> ]

<h3>Bestehende Termine bearbeiten:</h3>

<table border="0" width="100%">
	<tr bgcolor="#7398D8">
		<th>Datum</th>
		<th width="82%">Termin</th>
		<th>Administration</th>
	</tr>
	<?
	$sql = "SELECT id, DATE_FORMAT(datum, '%d. %m. %Y')
		date, termin, info FROM termine
		ORDER BY datum ASC";
	
	$result = db_query ($sql);
	
	while ($row = mysql_fetch_array ($result)) {
	
	$bar++;	
	?>
	<tr bgcolor="#CEDFF0" valign="top">
		<td><strong><? echo $row["date"]; ?></strong></td>
		<td>
			<?
			echo $row["termin"];				
			?>
		</td>
		<td>
			[	
			<a href="<? echo $SCRIPT_NAME . "?change=" . $row["id"]; ?>">ändern</a> |
			<a href="<? echo $SCRIPT_NAME . "?delete=" . $row["id"]; ?>">löschen</a> 
			]	
		</td>
	</tr>
	<?
	}
	?>	
</table>


<hr>

<div align="right"><font size="-1">[ <a href="../termine.php">Termine ansehen</a> ]</font></div>
<?
}
include ("../footer.inc.php");
?>