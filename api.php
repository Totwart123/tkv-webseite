<?php

// Aufruf per URL: https://www.tkv-kegeln.de/api.php?apiKey=mein_geheimer_api_key&timestamp=1675896745


// API-Konfiguration
const VALID_API_KEY = "mein_geheimer_api_key"; // Erlaubter API-Key
const SALT = "geheimes_salt"; // Salt zur Hash-Berechnung
const TIMESTAMP_TOLERANCE = 300; // Toleranz für Zeitstempel in Sekunden (5 Minuten)

header('Content-Type: application/json');

// Eingaben auslesen
$apiKey = $_GET['apiKey'] ?? null;
$timestamp = $_GET['timestamp'] ?? null;

// Überprüfung der Eingaben
if (!$apiKey || !$timestamp) {
    http_response_code(400);
    echo json_encode(["error" => "API-Key und Zeitstempel sind erforderlich."]);
    exit;
}

// Zeitstempel validieren
if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > TIMESTAMP_TOLERANCE) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültiger oder abgelaufener Zeitstempel."]);
    exit;
}

// API-Key validieren
if ($apiKey !== VALID_API_KEY) {
    http_response_code(403);
    echo json_encode(["error" => "Ungültiger API-Key."]);
    exit;
}

// Hash berechnen
$hash = hash('sha256', $apiKey . $timestamp . SALT);

// Hier dann die Liste der Spielnummern und UUIDs für die kommenden 14 Tage?
$spiele = [
    ["spielnummer" => 1, "uuid" => "123e4567-e89b-12d3-a456-426614174000"],
    ["spielnummer" => 2, "uuid" => "123e4567-e89b-12d3-a456-426614174001"],
    ["spielnummer" => 3, "uuid" => "123e4567-e89b-12d3-a456-426614174002"]
];

// Antwort generieren
$response = [
    "timestamp" => $timestamp,
    "hash" => $hash,
    "spiele" => $spiele
];

// Erfolgreiche Antwort senden
http_response_code(200);
echo json_encode($response);

?>
