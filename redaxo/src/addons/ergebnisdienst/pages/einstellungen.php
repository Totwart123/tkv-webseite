<?php
/**
 * Backend-Seite: Einstellungen des Ergebnisdienstes.
 *
 * Ein Formular / ein Speichern-Button fuer:
 *  1. PDF-Export an/aus  -> Flag-Datei im Addon-Data-Verzeichnis
 *       <data>/addons/ergebnisdienst/pdf_export_disabled.flag
 *       (vorhanden = deaktiviert; Button verschwindet im Frontend,
 *        getpdf.php liefert kein PDF mehr aus)
 *  2. API-Key und Liveticker-URL-Prefix -> rex_config('ergebnisdienst', ...)
 *       (gleiche Keys wie die API-Endpunkte -> Werte bleiben kompatibel)
 */

$flagFile = rex_path::addonData('ergebnisdienst', 'pdf_export_disabled.flag');
$csrf     = rex_csrf_token::factory('ergebnisdienst_einstellungen');

// --- Formular verarbeiten -------------------------------------------------
if (rex_post('form_submit', 'boolean') && $csrf->isValid()) {
    $saveOk = true;

    // 1. PDF-Export-Schalter (Flag-Datei)
    $pdfEnabled = rex_post('pdf_export_enabled', 'boolean', false);
    if ($pdfEnabled) {
        if (is_file($flagFile)) {
            rex_file::delete($flagFile);
        }
    } else {
        rex_dir::create(dirname($flagFile));
        if (rex_file::put($flagFile, date('c')) === false) {
            $saveOk = false;
        }
    }

    // 2. API-Einstellungen (rex_config)
    rex_config::set('ergebnisdienst', 'api_key', trim(rex_post('api_key', 'string', '')));
    rex_config::set('ergebnisdienst', 'liveticker_url_prefix', trim(rex_post('liveticker_url_prefix', 'string', '')));

    // Meldung ganz oben ausgeben
    if ($saveOk) {
        echo rex_view::success('Einstellungen wurden gespeichert.');
    } else {
        echo rex_view::error('Die Einstellungen konnten nicht vollstaendig gespeichert werden (Schreibrechte pruefen).');
    }
}

// --- Aktuelle Werte -------------------------------------------------------
$pdfEnabled = !is_file($flagFile);
$apiKey     = (string) rex_config::get('ergebnisdienst', 'api_key', '');
$prefix     = (string) rex_config::get('ergebnisdienst', 'liveticker_url_prefix', '');
$checked    = $pdfEnabled ? ' checked' : '';

// --- Formular aufbauen ----------------------------------------------------
$body = '
<fieldset>
    <div class="rex-form-group form-group checkbox">
        <label>
            <input type="checkbox" name="pdf_export_enabled" value="1"' . $checked . ' />
            PDF-Export aktiviert
        </label>
        <p class="help-block rex-note">
            Ohne Haken verschwindet der PDF-Button im Ergebnisdienst und getpdf.php
            liefert kein PDF mehr aus.
        </p>
    </div>

    <div class="rex-form-group form-group">
        <label class="control-label" for="ergebnisdienst-api-key">API-Key</label>
        <input class="form-control" type="text" id="ergebnisdienst-api-key" name="api_key" value="' . rex_escape($apiKey) . '" autocomplete="off" />
        <p class="help-block rex-note">Wird fuer GET /api/spielplan benoetigt. Uebergabe nur via Header X-API-Key.</p>
    </div>

    <div class="rex-form-group form-group">
        <label class="control-label" for="ergebnisdienst-liveticker-prefix">Liveticker URL Prefix</label>
        <input class="form-control" type="text" id="ergebnisdienst-liveticker-prefix" name="liveticker_url_prefix" value="' . rex_escape($prefix) . '" autocomplete="off" />
        <p class="help-block rex-note">Wird fuer POST /api/spielplan/liveticker genutzt. Entweder Basis-URL (UUID wird angehaengt) oder Platzhalter {id} verwenden.</p>
    </div>
</fieldset>

<div class="alert alert-info">
    <strong>Endpoint:</strong> GET /api/spielplan<br>
    <strong>Beispiel:</strong> Header X-API-Key: DEIN_KEY<br>
    <strong>POST:</strong> /api/liveticker
</div>

<fieldset class="rex-form-action">
    <button class="btn btn-save rex-form-aligned" type="submit" name="form_submit" value="1">Speichern</button>
</fieldset>';

$output = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
    ' . $csrf->getHiddenField() . '
    ' . $body . '
</form>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');
