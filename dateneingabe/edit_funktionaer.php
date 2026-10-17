<?
include ("../header.inc.php");
include ("../config.inc.php");

global $dbHost, $dbUser, $dbPass, $dbName;
	
mysql_connect ($dbHost, $dbUser, $dbPass);
mysql_select_db ($dbName);

if ($change)
{
$query = "UPDATE funktionaere SET vorname='$vorname', name='$name', strasse='$strasse', plz='$plz', ort='$ort', tel='$tel', fax='$fax', handy='$handy', email='$email' WHERE id=$id";
$r=mysql_query($query);
//echo $query;
echo "Die Daten des Funktionaers $vorname $name (ID $id) wurden ge‰ndert.";
}
else
{
$query = "SELECT * FROM funktionaere WHERE id = $id";
$r=mysql_query($query);
	
$res = mysql_fetch_array ($r);
?>
<h2>Funktionaer ‰ndern</h2>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">

<table>
	<tr>
		<td><strong>Vorname:</strong></td>
		<td>
			<input type="text" name="vorname"
				value="<? echo $res["vorname"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>Name:</strong></td>
		<td>
			<input type="text" name="name"
				value="<? echo $res["name"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>Straﬂe:</strong></td>
		<td>
			<input type="text" name="strasse"
				value="<? echo $res["strasse"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>PLZ:</strong></td>
		<td>
			<input type="text" name="plz"
				value="<? echo $res["plz"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>Ort:</strong></td>
		<td>
			<input type="text" name="ort"
				value="<? echo $res["ort"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>Tel.:</strong></td>
		<td>
			<input type="text" name="tel"
				value="<? echo $res["tel"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>Fax:</strong></td>
		<td>
			<input type="text" name="fax"
				value="<? echo $res["fax"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>Handy:</strong></td>
		<td>
			<input type="text" name="handy"
				value="<? echo $res["handy"]; ?>">
		</td>
	</tr>
	<tr>
		<td><strong>E-Mail:</strong></td>
		<td>
			<input type="text" name="email"
				value="<? echo $res["email"]; ?>">
		</td>
	</tr>
	
</table>

<input type="hidden" name="id" value="<? echo $id; ?>">

<input name="change" value="Daten ‰ndern" type="submit">

</form>

<?
}
mysql_close();
include ("../footer.inc.php");
?>
