<?
include ("../header.inc.php");
include ("../conf/config.inc.php");

include ("../lib/error.lib.php");
include ("../lib/db.lib.php");

if ($confirm) {
	if (db_query ("DELETE FROM vereine  WHERE id=$id AND v_subid=$subid"))
		echo "Der Verein wurde gelöscht!";

} else {

$result = db_query ("SELECT * FROM vereine  WHERE id=$id AND v_subid=$subid");

$row = mysql_fetch_array ($result);
 
echo "Soll der Verein <strong>" . $row["name"] . "</strong> wirklich gelöscht werden?<br><br>";

echo "[ <a href=\"$SCRIPT_NAME?id=$id&subid=$subid&confirm=1\">ja</a> | <a href=\"../kvs.php?id=$id&subid=$subid\">nein</a> ]";

}

include ("../footer.inc.php");
?>