<?php
declare(strict_types=1);

/**
 * miniwebstatus – REDAXO-SERVANT
 * -----------------------------------------------------------------------------
 * Variante von servant.php für REDAXO-5-Installationen. Verhält sich nach außen
 * identisch zum normalen Servant (hash-gesicherter Aufruf, hybrid verschlüsselte
 * JSON-Antwort), liefert aber neben Domain und PHP-Version zusätzlich:
 *
 *   - die MySQL-/MariaDB-Serverversion,
 *   - Name und Version von REDAXO (Core),
 *   - Namen und Versionsnummern aller registrierten AddOns (inkl. Plugins),
 *     jeweils mit Angabe, ob sie aktiviert (verfügbar) sind.
 *
 * Auslieferung je Domain:  rex_servant.php + public.pem
 * (Der private Schlüssel und das Master-Passwort liegen NIE auf dem Servant.)
 *
 * Die Datei muss REDAXO laden können. Standardmäßig wird sie im REDAXO-
 * Wurzelverzeichnis (dort liegt der Ordner "redaxo/") erwartet; alternativ kann
 * der Pfad zu redaxo/src/core/boot.php über die Konstante REDAXO_BOOT_FILE
 * gesetzt werden.
 *
 * PHP 8.0+  •  benötigt die OpenSSL-Erweiterung.  •  getestet mit REDAXO 5.
 * =============================================================================
 */

/* ============================== KONFIGURATION ============================== *
 * Diese Werte müssen mit dem Master übereinstimmen.
 * ------------------------------------------------------------------------- */

// Gemeinsames Geheimnis für die Hash-Absicherung des Aufrufs.
// MUSS identisch zum Wert im Master sein. Bitte ändern!
const SHARED_SECRET = 'tGHWD54646hGGedefdvge534634564XXXWWq2s11#gbfgb';

// Pfad zum öffentlichen Schlüssel (RSA, PEM). Siehe README.md zur Erzeugung.
const PUBLIC_KEY_FILE = __DIR__ . '/public.pem';

// Erlaubte Zeitabweichung (Sekunden) zwischen Master-Aufruf und Servant.
// Verhindert Replay-Angriffe; setzt eine grob synchronisierte Uhr voraus.
const MAX_CLOCK_SKEW = 300;

// Pfad zur REDAXO-Boot-Datei. Standard: diese Datei liegt im REDAXO-
// Wurzelverzeichnis (neben dem Ordner "redaxo/"). Bei abweichender Ablage hier
// den absoluten Pfad zu redaxo/src/core/boot.php eintragen.
const REDAXO_BOOT_FILE = __DIR__ . '/redaxo/src/core/boot.php';

/* =========================================================================== */

/**
 * Beendet die Ausführung mit einem sauberen HTTP-Fehler (Klartext, damit der
 * Master sinnvolle Fehlermeldungen anzeigen kann).
 */
function fail(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

/**
 * Ermittelt die aufgerufene Domain aus den Server-Variablen.
 */
function detect_domain(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';
    // Nur Host (evtl. Port entfernen) und auf ein vernünftiges Maß kürzen.
    $host = preg_replace('/:\d+$/', '', (string) $host);
    return substr((string) $host, 0, 255);
}

/**
 * Prüft die Hash-Absicherung des Aufrufs.
 *
 * Erwartet werden die Parameter:
 *   t = Unix-Zeitstempel (Sekunden)
 *   h = hash_hmac('sha256', t, SHARED_SECRET)
 */
function verify_request(): void
{
    $ts   = $_GET['t'] ?? '';
    $hash = $_GET['h'] ?? '';

    if ($ts === '' || $hash === '') {
        fail(400, 'Missing authentication parameters.');
    }
    if (!ctype_digit((string) $ts)) {
        fail(400, 'Invalid timestamp.');
    }

    // Zeitfenster prüfen (Replay-Schutz).
    if (abs(time() - (int) $ts) > MAX_CLOCK_SKEW) {
        fail(403, 'Request expired.');
    }

    $expected = hash_hmac('sha256', (string) $ts, SHARED_SECRET);
    if (!hash_equals($expected, (string) $hash)) {
        fail(403, 'Authentication failed.');
    }
}

/**
 * Verschlüsselt Klartext hybrid: AES-256-GCM für die Daten, der zufällige
 * AES-Schlüssel wird per RSA-OAEP mit dem öffentlichen Schlüssel geschützt.
 *
 * @return array{v:int,key:string,iv:string,tag:string,data:string}
 */
function encrypt_for_master(string $plaintext): array
{
    $pubPem = @file_get_contents(PUBLIC_KEY_FILE);
    if ($pubPem === false) {
        fail(500, 'Public key not found on servant.');
    }
    $pubKey = openssl_pkey_get_public($pubPem);
    if ($pubKey === false) {
        fail(500, 'Invalid public key on servant.');
    }

    $aesKey = random_bytes(32);           // AES-256
    $iv     = random_bytes(12);           // GCM empfiehlt 96-Bit-IV
    $tag    = '';

    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $aesKey,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );
    if ($ciphertext === false) {
        fail(500, 'Encryption failed.');
    }

    $encKey = '';
    if (!openssl_public_encrypt($aesKey, $encKey, $pubKey, OPENSSL_PKCS1_OAEP_PADDING)) {
        fail(500, 'Key encryption failed.');
    }

    return [
        'v'    => 1,
        'key'  => base64_encode($encKey),
        'iv'   => base64_encode($iv),
        'tag'  => base64_encode($tag),
        'data' => base64_encode($ciphertext),
    ];
}

/**
 * Lädt die REDAXO-Umgebung über die Core-Boot-Datei.
 *
 * Es wird bewusst nur der Core gebootet (nicht der Frontend-Controller), damit
 * keine Artikelausgabe erfolgt. Eventuelle Ausgaben des Bootvorgangs werden
 * verworfen, um die verschlüsselte JSON-Antwort nicht zu verunreinigen.
 */
function load_redaxo(): void
{
    if (!is_readable(REDAXO_BOOT_FILE)) {
        fail(500, 'REDAXO not found on servant (boot.php missing).');
    }

    // REDAXO erwartet vor dem Booten die globale $REX-Konfiguration mit den
    // Pfadangaben – genau wie die index.php im REDAXO-Wurzelverzeichnis sie
    // setzt. Fehlt sie, bricht boot.php mit
    //   "Missing required global variable $REX['HTDOCS_PATH']"
    // ab. Wir leiten die Pfade aus dem Ort der boot.php ab
    // (<htdocs>/<backend>/src/core/boot.php) und booten nur den Core
    // (LOAD_PAGE = false), ohne Frontend-Ausgabe.
    global $REX;
    if (!is_array($REX ?? null)) {
        $REX = [];
    }
    $backendDir = dirname(REDAXO_BOOT_FILE, 3);        // .../<htdocs>/<backend>
    $REX['REDAXO']         = false;                    // Frontend-Kontext, kein Backend
    $REX['HTDOCS_PATH']    = dirname($backendDir) . '/';
    $REX['BACKEND_FOLDER'] = basename($backendDir);
    $REX['LOAD_PAGE']      = false;                    // nur Core booten, keine Ausgabe

    ob_start();
    try {
        require REDAXO_BOOT_FILE;
    } catch (\Throwable $e) {
        ob_end_clean();
        fail(500, 'REDAXO bootstrap failed: ' . $e->getMessage());
    }
    ob_end_clean();

    if (!class_exists('rex') || !class_exists('rex_addon')) {
        fail(500, 'REDAXO environment could not be initialized.');
    }
}

/**
 * Ermittelt die MySQL-/MariaDB-Serverversion über REDAXO.
 */
function detect_mysql_version(): ?string
{
    if (!class_exists('rex_sql')) {
        return null;
    }
    try {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT VERSION() AS version');
        $version = $sql->getValue('version');
        return is_string($version) && $version !== '' ? $version : null;
    } catch (\Throwable $e) {
        return null;
    }
}

/**
 * Ermittelt die REDAXO-Core-Version.
 */
function detect_redaxo_version(): ?string
{
    if (class_exists('rex') && method_exists('rex', 'getVersion')) {
        $version = rex::getVersion();
        if (is_string($version) && $version !== '') {
            return $version;
        }
    }
    return null;
}

/**
 * Sammelt alle registrierten AddOns (inkl. ihrer Plugins) mit Name, Slug,
 * Version und Aktivierungsstatus (verfügbar = installiert und aktiviert).
 *
 * Der Slug eines AddOns entspricht seinem Schlüssel (Verzeichnisname) und damit
 * dem Paketnamen im REDAXO-Verzeichnis (redaxo.org); er wird für den Update-
 * Abgleich genutzt. Plugins eines AddOns erhalten keinen Slug, da sie nicht
 * eigenständig im Verzeichnis liegen.
 *
 * @return list<array{name:string,slug:string,version:string,active:bool}>
 */
function collect_addons(): array
{
    if (!class_exists('rex_addon') || !method_exists('rex_addon', 'getRegisteredAddons')) {
        return [];
    }

    $components = [];
    foreach (rex_addon::getRegisteredAddons() as $addon) {
        $components[] = [
            'name'    => (string) $addon->getName(),
            'slug'    => (string) $addon->getName(),
            'version' => (string) $addon->getVersion(),
            'active'  => (bool) $addon->isAvailable(),
        ];

        // Plugins des AddOns (falls vorhanden) mit ausweisen.
        if (method_exists($addon, 'getRegisteredPlugins')) {
            foreach ($addon->getRegisteredPlugins() as $plugin) {
                $components[] = [
                    'name'    => $addon->getName() . ' / ' . $plugin->getName(),
                    'slug'    => '',
                    'version' => (string) $plugin->getVersion(),
                    'active'  => (bool) $plugin->isAvailable(),
                ];
            }
        }
    }

    return $components;
}

/* ================================ ABLAUF ================================== */

if (!extension_loaded('openssl')) {
    fail(500, 'OpenSSL extension not available.');
}

verify_request();

load_redaxo();

// Nutzdaten: Domain, PHP-, MySQL- und REDAXO-/AddOn-Angaben.
$payload = [
    'domain'          => detect_domain(),
    'php_version'     => PHP_VERSION,
    'php_version_id'  => PHP_VERSION_ID,
    'mysql_version'   => detect_mysql_version(),
    'redaxo'          => [
        'name'    => 'REDAXO',
        'version' => detect_redaxo_version(),
    ],
    'addons'          => collect_addons(),
    'sapi'            => PHP_SAPI,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? null,
    'timestamp'       => time(),
];

$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fail(500, 'Could not encode payload.');
}

$envelope = encrypt_for_master($json);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode($envelope, JSON_UNESCAPED_SLASHES);
