<?
include ("../header.inc.php");
include ("../conf/config.inc.php");
include ("../lib/error.lib.php");
include ("../lib/db.lib.php");


if ($id) {
	$query = "SELECT name FROM vereine WHERE id = $id";
	$r=db_query($query);
	
	$tmp = mysql_fetch_array ($r);
	
	$nme = $tmp["name"];
		
	echo "<h2>Klubs des $nme</h2>";
} else
	echo "<h2>Kreisvereine im TKV</h2>";

if (!$id)
{
?>
[ <a href="funktionaere_s.php">Funktionäre suchen</a> | <a href="funktionaere.php">Liste der Funktionäre</a> ]<hr noshade size="2">
<?
}
?>

<?


if ($id)
	$query = "SELECT name, id, v_subid FROM vereine WHERE (id % 1000) AND id > $id AND id < $id+1000";
else
	$query = "SELECT name, id, v_subid FROM vereine WHERE !(id % 1000)";

$query .= " ORDER BY id";	

$r = db_query ($query);

//echo $query;

echo "<table width=\"100%\">";

$baz=0;

while ($res = mysql_fetch_array ($r))
{
	if ($baz)
	{
		echo "<tr bgcolor=\"#CCCCCC\"><td>";
		$baz--;
	}
	else
	{
		echo "<tr><td>";
		$baz++;
	}
	if ($id)
		printf ('<a href="kvs.php?id=%d&subid=%d">%02d %03d - %s</a><br>',
			$res["id"],			
			$res["v_subid"],			
			$res["id"]/1000,
			$res["id"]%1000,
			$res["name"]);
	else
		printf ('<a href="kvs.php?id=%d">%02d 000- %s</a><br>',
			$res["id"],
			$res["id"]/1000,
			$res["name"]);
	echo "</td></tr>";
}

echo "</table>";
?>


<?
include ("../footer.inc.php");
?>
