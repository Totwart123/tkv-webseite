<?php

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
  http_response_code(405);
  header('Allow: GET');
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

// Always restrict the API to the active season.
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

$where = [];
$where[] = 'sp.saisonid = ' . $activeSaisonId;
$where[] = 'hm.mannschaft <> "Dummy"';
$where[] = 'gm.mannschaft <> "Dummy"';
$where[] = 'hm.mannschaft <> "Freilos"';
$where[] = 'gm.mannschaft <> "Freilos"';
$where[] = 'sp.uhrzeit <> ""';

$whereSql = '';
$whereSql = 'WHERE ' . implode(' AND ', $where);


$sql = rex_sql::factory();
$query = '
  SELECT
    sp.spielnr,
    sp.spieltagnr,
    sp.datum,
    sp.uhrzeit,
    hm.mannschaft AS heimmannschaft,
    gm.mannschaft AS gastmannschaft,
    k.klasse AS klasse
  FROM ' . rex::getTablePrefix() . '950_spielplan sp
  JOIN ' . rex::getTablePrefix() . '950_mannschaft hm ON hm.id = sp.heimmannschaftid
  JOIN ' . rex::getTablePrefix() . '950_mannschaft gm ON gm.id = sp.gastmannschaftid
  JOIN ' . rex::getTablePrefix() . '950_klasse k ON k.id = sp.klassenid
  ' . $whereSql . '
  ORDER BY sp.echtdatum ASC, sp.uhrzeit ASC, sp.id ASC';
$sql->setQuery($query);

$rows = $sql->getArray();
echo json_encode([
  'data' => $rows,
  'meta' => [
    'count' => count($rows),
    'active_saison_id' => $activeSaisonId
  ]
]);
