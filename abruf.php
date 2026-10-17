<?php

// API-Konfiguration
$apiUrl = "https://www.tkv-kegeln.de/api.php"; // URL zur API
const VALID_API_KEY = "mein_geheimer_api_key"; // Erlaubter API-Key
const SALT = "geheimes_salt"; // Salt zur Hash-Berechnung
const TIMESTAMP_TOLERANCE = 300; // Toleranz für Zeitstempel in Sekunden (5 Minuten)

// Zeitstempel generieren
$timestamp = time();

// API-Aufruf vorbereiten
$queryParams = http_build_query([
    'apiKey' => VALID_API_KEY,
    'timestamp' => $timestamp
]);

$apiEndpoint = $apiUrl . '?' . $queryParams;

// API-Aufruf ausführen
$response = file_get_contents($apiEndpoint);

// Fehlerbehandlung bei fehlgeschlagenem API-Aufruf
if ($response === false) {
    die("Fehler beim Aufruf der API.");
}

// JSON-Antwort dekodieren
$data = json_decode($response, true);

// Fehlerbehandlung für die API-Antwort
if (isset($data['error'])) {
    die("API-Fehler: " . htmlspecialchars($data['error']));
}

// Zeitstempel validieren
$returntimestamp = $data['timestamp'];
if (!ctype_digit($returntimestamp) || abs(time() - (int)$returntimestamp) > TIMESTAMP_TOLERANCE) {
    die("Ungültiger oder abgelaufener Zeitstempel.");
}

// Hash berechnen
$returnhash = $data['hash'];
$hash = hash('sha256', VALID_API_KEY . $returntimestamp . SALT);
if ( $returnhash != $hash) {
    die("Ungültiger Hashwert.");
}

// Spiele-Daten extrahieren
$spiele = $data['spiele'] ?? [];

// HTML-Ausgabe
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spieleliste</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: 20px auto;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Spieleliste</h1>
    <?php if (!empty($spiele)): ?>
        <table>
            <thead>
                <tr>
                    <th>Spielnummer</th>
                    <th>UUID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($spiele as $spiel): ?>
                    <tr>
                        <td><?= htmlspecialchars($spiel['spielnummer']) ?></td>
                        <td><?= htmlspecialchars($spiel['uuid']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">Keine Spiele verfügbar.</p>
    <?php endif; ?>
</body>
</html>
