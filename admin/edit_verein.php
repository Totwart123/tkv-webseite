<?php
include ("../header.inc.php");
include ("../conf/config.inc.php");
include ("../lib/error.lib.php");
include ("../lib/db.lib.php");

if (!$subid)
	$subid=0;
	
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

	$sql = "UPDATE vereine SET id='$v_id', v_subid='$v_subid', name='$name',
		website='$website', kb1_name='$kb1_name',
		kb1_addr='$kb1_addr', kb1_tel='$kb1_tel',
		kb1_nrb='$kb1_nrb', kb1_art='$kb1_art',
		kb1_drucker='$kb1_drucker', kb2_name='$kb2_name',
		kb2_addr='$kb2_addr', kb2_tel='$kb2_tel', kb2_nrb='$kb2_nrb',
		kb2_art='$kb2_art', kb2_drucker='$kb2_drucker'
		WHERE id='$id' AND v_subid='$subid'";

	$result=db_query($sql);
	status ("Die Daten des Vereins $name (ID $v_id) wurden geändert.");
}
else
{
	$sql = "SELECT * FROM vereine WHERE id = '$id' AND v_subid = '$subid'";
	$result=db_query($sql);	
	$row = mysql_fetch_array ($result);
?>
<h2>Verein ändern</h2>

<form action="<?php echo $SCRIPT_NAME ?>" method="POST">
	<table>
		<tr>
			<td><strong>Nummer:</strong></td>
			<td>
				<input type="text" name="v_id" size="10"
					value='<? echo $row["id"]; ?>'>
			</td>		
		</tr>
		<tr>
			<td><strong>Unter-Nummer:</strong></td>
			<td>
				<input type="text" name="v_subid" size="10"
					value='<? echo $row["v_subid"]; ?>'>
			</td>		
		</tr>
		<tr>
			<td><strong>Name:</strong></td>
			<td>
				<input type="text" name="name" size="50"
					value='<? echo $row["name"]; ?>'>
			</td>		
		</tr>
		<tr>
			<td><strong>Website:</strong><br>"http://" nicht vergessen</td>
			<td>
				<input type="text" name="website" size="50"
					value="<? echo $row["website"]; ?>">
			</td>		
		</tr>
	</table>

	<?php
	if ($id%1000)
	{
	?>

	<h3>Kegelbahn 1</h3>

	<table>
		<tr>
			<td><strong>Name:</strong></td>
			<td>
				<input type="text" name="kb1_name" size="50"
					value="<? echo $row["kb1_name"]; ?>">
			</td>				
		</tr>
		<tr>
			<td><strong>Adresse:</strong></td>
			<td>
				<input type="text" size="60" name="kb1_addr"
					value="<? echo $row["kb1_addr"]; ?>">
			</td>				
		</tr>
		<tr>
			<td><strong>Tel.:</strong></td>
			<td>
				<input type="text" name="kb1_tel"
					value="<? echo $row["kb1_tel"]; ?>">
			</td>				
		</tr>
		<tr>
			<td><strong>Anzahl der Bahnen:</strong></td>
			<td>
				<input type="text" name="kb1_nrb"
					value="<? echo $row["kb1_nrb"]; ?>">
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
				<input type="text" name="kb2_name" size="50"
					value="<? echo $row["kb2_name"]; ?>">
			</td>				
		</tr>
		<tr>
			<td><strong>Adresse:</strong></td>
			<td>
				<input type="text" size="60" name="kb2_addr"
					value="<? echo $row["kb2_addr"]; ?>">
			</td>				
		</tr>
		<tr>
			<td><strong>Tel.:</strong></td>
			<td>
				<input type="text" name="kb2_tel"
					value="<? echo $row["kb2_tel"]; ?>">
			</td>				
		</tr>
		<tr>
			<td><strong>Anzahl der Bahnen:</strong></td>
			<td>
				<input type="text" name="kb2_nrb"
					value="<? echo $row["kb2_nrb"]; ?>">
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
			</td>
		</tr>		
	</table>
	<?php
	}
	?>	
	<input type="hidden" name="id" value="<?php echo $id ?>">	
	<input type="hidden" name="subid" value="<?php echo $subid ?>">
	<input name="change" value="Daten ändern" type="submit">
</form>

<?
}
include ("../footer.inc.php");
?>
