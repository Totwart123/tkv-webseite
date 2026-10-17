<?
include ("../header.inc.php");
include ("../config.inc.php");

global $dbHost, $dbUser, $dbPass, $dbName;
	
mysql_connect ($dbHost, $dbUser, $dbPass);
mysql_select_db ($dbName);

if ($change)
{
if ($kb1_drucker=="on")
	$kb1_drucker=1;
else 
	$kb1_drucker=0;
if ($kb2_drucker=="on")
	$kb2_drucker=1;
else 
	$kb2_drucker=0;

$query = "UPDATE vereine SET name='$name', website='$website', kb1_name='$kb1_name', kb1_addr='$kb1_addr', kb1_tel='$kb1_tel', kb1_nrb='$kb1_nrb', kb1_art='$kb1_art', kb1_drucker='$kb1_drucker', kb2_name='$kb2_name', kb2_addr='$kb2_addr', kb2_tel='$kb2_tel', kb2_nrb='$kb2_nrb', kb2_art='$kb2_art', kb2_drucker='$kb2_drucker' WHERE id=$id and vs_id=$vs";
$r=mysql_query($query);

//echo $query; echo "<br>";
echo "Die Daten des Vereins $name (ID $id) wurden geändert.";
}
else
{
$query = "SELECT * FROM vereine WHERE id = '$id' AND vs_id = '$vs'";

//echo $query;
$r=mysql_query($query);
	
$res = mysql_fetch_array ($r);
?>
<h2>Verein ändern</h2>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">

<table>
	<tr>
		<td><strong>Name:</strong></td>
		<td>
			<input type="text" name="name"
				value='<? echo $res["name"]; ?>'>
		</td>		
	</tr>
	<tr>
		<td><strong>Website:</strong><br>"http://" nicht vergessen</td>
		<td>
			<input type="text" name="website"
				value="<? echo $res["website"]; ?>">
		</td>		
	</tr>
</table>

<?php

if ($id%1000){
?>
<h3>Kegelbahn 1</h3>

<table>
	<tr>
		<td><strong>Name:</strong></td>
		<td>
			<input type="text" name="kb1_name"
				value="<? echo $res["kb1_name"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Adresse:</strong></td>
		<td>
			<input type="text" size="60" name="kb1_addr"
				value="<? echo $res["kb1_addr"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Tel.:</strong></td>
		<td>
			<input type="text" name="kb1_tel"
				value="<? echo $res["kb1_tel"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Anzahl der Bahnen:</strong></td>
		<td>
			<input type="text" name="kb1_nrb"
				value="<? echo $res["kb1_nrb"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Art der Bahnen:</strong></td>
		<td>
			<select name="kb1_art">			
				<option value="Kunststoff">Kunststoff</option>	
				<option value="Asphalt">Asphalt</option>
				<option value="Bowling">Bowling</option>
			</select>
		</td>				
	</tr>
	<tr>
		<td><strong>Drucker:</strong></td>
		<td>
			<input type="checkbox" name="kb1_drucker" checked>
		</td>				
	</tr>
</table>


<h3>Kegelbahn 2</h3>

<table>
	<tr>
		<td><strong>Name:</strong></td>
		<td>
			<input type="text" name="kb2_name"
				value="<? echo $res["kb2_name"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Adresse:</strong></td>
		<td>
			<input type="text" size="60" name="kb2_addr"
				value="<? echo $res["kb2_addr"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Tel.:</strong></td>
		<td>
			<input type="text" name="kb2_tel"
				value="<? echo $res["kb2_tel"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Anzahl der Bahnen:</strong></td>
		<td>
			<input type="text" name="kb2_nrb"
				value="<? echo $res["kb2_nrb"]; ?>">
		</td>				
	</tr>
	<tr>
		<td><strong>Art der Bahnen:</strong></td>
		<td>
			<select name="kb2_art">			
				<option value="Kunststoff">Kunststoff</option>	
				<option value="Asphalt">Asphalt</option>
				<option value="Bowling">Bowling</option>
			</select>
		</td>				
	</tr>
	<tr>
		<td><strong>Drucker:</strong></td>
		<td>
			<input type="checkbox" name="kb2_drucker" checked>
		
</table>

<?php
}
?>
<input type="hidden" name="id" value="<? echo $id; ?>">
<input type="hidden" name="vs" value="<? echo $vs; ?>">

<input name="change" value="Daten ändern" type="submit">

</form>

<?
}
mysql_close();
include ("../footer.inc.php");
?>
