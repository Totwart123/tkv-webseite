<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Dateneingabe</title>
</head>
<body>

<?php
if ($submit) {
	if (!($fd = fopen("../ergebnisse/$filename", "w")))
	{
		echo "Fehler beim &Ouml;nnen von ../ergebnisse/filename!";
	} else
	{
		fwrite ($fd, $staffelbericht);
		fclose($fd);
	}
	echo "Dateneingabe erfolgreich!";
} else {
echo "<h1>Dateneingabe für $filename</h1>";
?>

<form action="<?php echo $PHP_SELF . "?filename=" . $filename;?>" method="POST">
	Kopieren Sie bitte den Staffelbericht in das folgende Eingabefeld:<br>
	<textarea name="staffelbericht" rows="30" cols="80"></textarea>
	<br>
	<input name="submit" value="Submit" type="submit">
	<input value="Formular löschen" type="reset">	
</form>

<?
}
?>

</body>
</html>