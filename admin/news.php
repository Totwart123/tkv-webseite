<?
include ("../header.inc.php");
include ("../config.inc.php");
include ("../libtkv.inc.php");
?>

<h2>Administration - Neuigkeiten</h2>


<?
$nl = $_GET['nl'];
$nl = new NewsList(1, 1);

$add = $_GET['add'];
$edit = $_GET['edit'];
$add_submit = $_POST['add_submit'];
$submit = $_POST['submit'];
$active = $_GET['active'];
$delete = $_GET['delete'];
$datum = $_POST['datum'];
$author = $_POST['author'];
$title = $_POST['title'];
$text = $_POST['text'];
if ($add) 
{
	$nl->add (1, 0);
} 
elseif ($add_submit) 
{
	$nl->add (1, 1, $datum, $author, $title, $text);
?>
Eine neue Nachricht wurde eingetragen.
<hr>
<div align="right"><font size="-1">[ <a href="news.php">zurück zur Administration</a> ]</font></div>
<?
} 
elseif ($edit) 
{
	if ($datum) 
	{
		$nl->edit ($edit, $datum, $author, $title, $text);	
		echo "Die Nachicht mit der ID $edit wurde geändert";
?>
<hr>
<div align="right"><font size="-1">[ <a href="news.php">zurück zur Administration</a> ]</font></div>

<?
	} 
	else 
	{
		$nl->edit ($edit);
	}
} 
elseif ($active) 
{
	$news = $nl->active ($active);
?>
<hr>
<div align="right"><font size="-1">[ <a href="news.php">zurück zur Administration</a> ]</font></div>
<?
} 
elseif ($delete) 
{
	$news = $nl->delete ($delete);
?>
<hr>
<div align="right"><font size="-1">[ <a href="news.php">zurück zur Administration</a> ]</font></div>
<?
}
else 
{
?>
[ <a href="news.php?add=1">Neuigkeit hinzufügen</a> ]<br>

<h3>Bestehende Neuigkeiten bearbeiten:</h3>
<?
	$nl->showAdmin();
?>

<div align="right"><font size="-1">
[ <a href="index.php">Neuigkeiten ansehen</a> ]
</font></div>

<?
}
?>

<?
include ("../footer.inc.php");
?>
