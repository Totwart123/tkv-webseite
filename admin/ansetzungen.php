<?php
include ("../header.inc.php");
include ("../lib/error.lib.php");
?>

<?php

if ($action == "add")
{
	$filename = "../ergebnisse";
	
	if ($ansetzung_name[0] == "h" ||
		$ansetzung_name[0] == "H")
		$filename .= "/Herren";
	if ($ansetzung_name[0] == "d" ||
		$ansetzung_name[0] == "D")
		$filename .= "/Damen";
	if ($ansetzung_name[0] == "s" ||
		$ansetzung_name[0] == "S")
		$filename .= "/Sen";
	if ($ansetzung_name[0] == "j" ||
		$ansetzung_name[0] == "J")
		$filename .= "/Jgd";
	
	if ($ansetzung_name[1] == "b" ||
                $ansetzung_name[1] == "B")
                $filename .= "/BL";	
	if ($ansetzung_name[1] == "l" ||
		$ansetzung_name[1] == "L")
		$filename .= "/Liga";
	if ($ansetzung_name[1] == "k" ||
		$ansetzung_name[1] == "K")
		$filename .= "/Klasse";
		
	$filename .= $ansetzung_name[2];
	
	if ($ansetzung_name[3] != 0)
		$filename .= "/St-" . $ansetzung_name[3];
		
	if (!dir ("../ergebnisse/$filename"))
		$add_error = 1;
	
	$filename .= "/ansetzung.rtf";
	
	if (!$add_error) {
		$fp1 = fopen($ansetzung, "r");
		$fp2 = fopen($filename, "w");
	
		while ($buf = fread ($fp1, 1024)) {
			fwrite ($fp2, $buf);
		}
		status ("Die Ansetzung wurde in $filename geschrieben");
	}
}

//if (!$action || $add_error)
{

	if ($add_error)
		warning ("Der Dateiname ist nicht korrekt nach dem Schema benannt!");

?>

<h3><a href="">Administration</a>/Ergebnisse</h3>


<form action="<?php echo $SCRIPT_NAME ?>"
	method="POST"
	enctype="multipart/form-data">
	
	<input type="hidden"
		name="MAX_FILE_SIZE"
		value="<?=MAX_FILE_SIZE ?>">	
	<input type="file"
		name="ansetzung"
		
		size="50">
	<input type="hidden" name="action" value="add"><br>
	<input value="Eintragen" type="submit">
</form>


<?php
}
?>

<?php
include ("../footer.inc.php");
?>


