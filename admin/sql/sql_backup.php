<?php
// Bitte hier Ihre Daten eintragen
$host= 'db180.puretec.de';
$user= 'dbo271172053';
$pass= 'PHmPxUud';
$db= 'db271172053';

// Befehl ausführen und in Zipfile speichern
system(sprintf(
'mysqldump --opt -h%s -u%s -p"%s" %s | gzip > %s/dumpDB.sql.gz',
$host,
$user,
$pass,
$db,
getenv('DOCUMENT_ROOT')
));
echo '+DONE';
?>