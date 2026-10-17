<?php

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['error' => 'method_not_allowed']);
  return;
}

$configuredApiKey = (string) rex_config::get('ergebnisdienst', 'api_key', '');
$providedApiKey = (string) ($_SERVER['HTTP_X_API_KEY'] ?? '');

if ($configuredApiKey === '') {
  http_response_code(500);
  echo json_encode(['error' => 'api_key_not_configured']);
  return;
}

if (!hash_equals($configuredApiKey, $providedApiKey)) {
  http_response_code(401);
  echo json_encode(['error' => 'unauthorized']);
  return;
}

$urlPrefix = trim((string) rex_config::get('ergebnisdienst', 'liveticker_url_prefix', ''));
$urlPrefix = trim($urlPrefix, "\"'");
if ($urlPrefix === '') {
  http_response_code(500);
  echo json_encode(['error' => 'liveticker_url_prefix_not_configured']);
  return;
}

$rawBody = file_get_contents('php://input');
$jsonBody = [];
if (is_string($rawBody) && trim($rawBody) !== '') {
  $decoded = json_decode($rawBody, true);
  if (is_array($decoded)) {
    $jsonBody = $decoded;
  }
}

$uuid = '';
if (isset($jsonBody['id']) && is_string($jsonBody['id'])) {
  $uuid = trim($jsonBody['id']);
}

$spielnr = '';
if (isset($jsonBody['spielnr']) && is_string($jsonBody['spielnr'])) {
  $spielnr = trim($jsonBody['spielnr']);
}

if (!empty($jsonBody)) {
  $unexpectedKeys = array_diff(array_keys($jsonBody), ['id', 'spielnr']);
  if (!empty($unexpectedKeys)) {
    http_response_code(422);
    echo json_encode(['error' => 'unexpected_body_fields', 'allowed' => ['id', 'spielnr']]);
    return;
  }
}

if ($uuid === '') {
  http_response_code(422);
  echo json_encode(['error' => 'missing_id']);
  return;
}

if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid) !== 1) {
  http_response_code(422);
  echo json_encode(['error' => 'id_is_not_uuid']);
  return;
}

if ($spielnr === '') {
  http_response_code(422);
  echo json_encode(['error' => 'missing_target', 'hint' => 'Provide spielnr in JSON body']);
  return;
}

// Validate target game first: it must exist and belong to the active season.
$table = rex::getTablePrefix() . '950_spielplan';
$saisonSql = rex_sql::factory();
$saisonSql->setQuery(
  'SELECT id FROM ' . rex::getTablePrefix() . '950_saison WHERE aktivesaison = "ja" ORDER BY id DESC LIMIT 1'
);
$activeSaisonId = (int) $saisonSql->getValue('id');

if ($activeSaisonId <= 0) {
  http_response_code(500);
  echo json_encode(['error' => 'no_active_saison_configured']);
  return;
}

$targetLookup = rex_sql::factory();
$targetLookup->setQuery(
  'SELECT id FROM ' . $table . ' WHERE spielnr = :spielnr AND saisonid = :saisonid ORDER BY id DESC LIMIT 1',
  [
    'spielnr' => $spielnr,
    'saisonid' => $activeSaisonId,
  ]
);
$targetId = (int) $targetLookup->getValue('id');

if ($targetId <= 0) {
  $existsLookup = rex_sql::factory();
  $existsLookup->setQuery(
    'SELECT id, saisonid FROM ' . $table . ' WHERE spielnr = :spielnr ORDER BY id DESC LIMIT 1',
    [
      'spielnr' => $spielnr,
    ]
  );
  $existsId = (int) $existsLookup->getValue('id');

  if ($existsId <= 0) {
    http_response_code(404);
    echo json_encode(['error' => 'spielnr_not_found']);
    return;
  }

  http_response_code(409);
  echo json_encode([
    'error' => 'spiel_not_in_active_saison',
    'active_saison_id' => $activeSaisonId,
    'found_saison_id' => (int) $existsLookup->getValue('saisonid')
  ]);
  return;
}

if (strpos($urlPrefix, '{id}') !== false) {
  $tickerUrl = str_replace('{id}', $uuid, $urlPrefix);
} else {
  $tickerUrl = rtrim($urlPrefix, '/') . '/' . $uuid;
}

$tickerUrl = trim($tickerUrl);
$tickerUrl = trim($tickerUrl, "\"'");

$db = rex_sql::factory();
$db->setTable($table);
$db->setWhere('id = ' . (int) $targetId);
$db->setValue('tickerurl', $tickerUrl);
$db->update();

echo json_encode([
  'ok' => true,
  'id' => $uuid,
  'spielnr' => $spielnr,
  'spielplan_id' => $targetId,
  'tickerurl' => $tickerUrl
]);
