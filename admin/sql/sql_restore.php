<?php
// Bitte hier Ihre Daten eintragen
$host= 'db463630892.db.1and1.com';
$user= 'dbo463630892';
$pass= 'PHmPxUud';
$db= 'db463630892';

// gzip entpacken und in die Datenbank einlesen
system(sprintf(
'gunzip -c %s/dumpDB.sql.gz | mysql -h %s -u %s -p%s %s',
getenv('DOCUMENT_ROOT'),
$host,
$user,
$pass,
$db
));
echo '+DONE';
?> 