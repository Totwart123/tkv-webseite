<?
include ("../header.inc.php");
include ("../conf/config.inc.php");
include ("../lib/error.lib.php");
include ("../lib/db.lib.php");

if ($change)
{
	$sql = "UPDATE funktionaere SET vorname='$vorname',
		name='$name', strasse='$strasse', plz='$plz', 
		ort='$ort', tel='$tel', fax='$fax', handy='$handy',
		email='$email' WHERE id=$id";	
	$result = db_query($sql);
	echo "Die Daten des Funktionaers $vorname $name (ID $id) wurden ge‰ndert.";
}
else
{
	$sql = "SELECT * FROM funktionaere WHERE id = $id";
	$result = db_query($sql);
	
	$row = mysql_fetch_array ($result);
?>
<h2>Funktion‰r ‰ndern</h2>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
	<table>
		<tr>
			<td><strong>Vorname:</strong></td>
			<td>
				<input type="text" name="vorname" size="50"
					value="<? echo $row["vorname"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>Name:</strong></td>
			<td>
				<input type="text" name="name" size="50"
					value="<? echo $row["name"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>Straﬂe:</strong></td>
			<td>
				<input type="text" name="strasse" size="50"
					value="<? echo $row["strasse"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>PLZ:</strong></td>
			<td>
				<input type="text" name="plz" size="50"
					value="<? echo $row["plz"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>Ort:</strong></td>
			<td>
				<input type="text" name="ort" size="50"
					value="<? echo $row["ort"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>Tel.:</strong></td>
			<td>
				<input type="text" name="tel" size="50"
					value="<? echo $row["tel"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>Fax:</strong></td>
			<td>
				<input type="text" name="fax" size="50"
					value="<? echo $row["fax"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>Handy:</strong></td>
			<td>
				<input type="text" name="handy" size="50"
					value="<? echo $row["handy"]; ?>">
			</td>
		</tr>
		<tr>
			<td><strong>E-Mail:</strong></td>
			<td>
				<input type="text" name="email" size="50"
					value="<? echo $row["email"]; ?>">
			</td>
		</tr>
		
	</table>
	<input type="hidden" name="id" value="<? echo $id; ?>">
	<input name="change" value="Daten ‰ndern" type="submit">
</form>

<?
}
include ("../footer.inc.php");
?>