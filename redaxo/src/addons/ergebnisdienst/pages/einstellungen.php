<?php
/**
 * Backend-Seite: Schalter fuer den PDF-Export des Ergebnisdienstes.
 *
 * Quelle der Wahrheit ist eine Flag-Datei im Addon-Data-Verzeichnis:
 *   <data>/addons/ergebnisdienst/pdf_export_disabled.flag
 *
 * - Datei vorhanden  -> PDF-Export deaktiviert
 * - Datei fehlt      -> PDF-Export aktiviert
 *
 * Bei deaktiviertem Export verschwindet der PDF-Button im Frontend
 * (led.ergebnisse.modul.php) und getpdf.php liefert kein PDF mehr aus.
 */

$flagFile = rex_path::addonData('ergebnisdienst', 'pdf_export_disabled.flag');
$csrf     = rex_csrf_token::factory('ergebnisdienst_pdf_export');

// --- Formular verarbeiten -------------------------------------------------
if (rex_post('form_submit', 'boolean') && $csrf->isValid()) {
    $enable = rex_post('pdf_export_enabled', 'boolean', false);

    if ($enable) {
        // Aktivieren -> Flag entfernen
        if (is_file($flagFile)) {
            rex_file::delete($flagFile);
        }
        echo rex_view::success('PDF-Export wurde aktiviert.');
    } else {
        // Deaktivieren -> Flag anlegen (Verzeichnis sicherstellen)
        rex_dir::create(dirname($flagFile));
        if (rex_file::put($flagFile, date('c')) !== false) {
            echo rex_view::success('PDF-Export wurde deaktiviert.');
        } else {
            echo rex_view::error('Die Einstellung konnte nicht gespeichert werden (Schreibrechte pruefen).');
        }
    }
}

// --- Aktuellen Status ermitteln -------------------------------------------
$enabled = !is_file($flagFile);
$checked = $enabled ? ' checked' : '';
$status  = $enabled
    ? '<span class="text-success"><strong>aktiviert</strong></span>'
    : '<span class="text-danger"><strong>deaktiviert</strong></span>';

// --- Formular aufbauen ----------------------------------------------------
$formContent = '
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
</fieldset>';

$buttons = '
<fieldset class="rex-form-action">
    <button class="btn btn-save rex-form-aligned" type="submit" name="form_submit" value="1">
        Speichern
    </button>
</fieldset>';

$output = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
    ' . $csrf->getHiddenField() . '
    ' . $formContent . '
    ' . $buttons . '
</form>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'PDF-Export &ndash; aktueller Status: ' . $status, false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');

// ==========================================================================
// API-Einstellungen (zuvor eigener Reiter "API")
// rex_config_form liest/schreibt dieselben rex_config-Keys (api_key,
// liveticker_url_prefix) im Namespace 'ergebnisdienst' -> bestehende Werte
// bleiben erhalten.
// ==========================================================================
$apiForm = rex_config_form::factory('ergebnisdienst');

$field = $apiForm->addTextField('api_key');
$field->setLabel('API-Key');
$field->setAttribute('autocomplete', 'off');
$field->setNotice('Wird fuer GET /api/spielplan benoetigt. Uebergabe nur via Header X-API-Key.');

$prefixField = $apiForm->addTextField('liveticker_url_prefix');
$prefixField->setLabel('Liveticker URL Prefix');
$prefixField->setAttribute('autocomplete', 'off');
$prefixField->setNotice('Wird fuer POST /api/spielplan/liveticker genutzt. Entweder Basis-URL (UUID wird angehaengt) oder Platzhalter {id} verwenden.');

$apiInfo  = '<div class="alert alert-info" style="margin-top: 15px;">';
$apiInfo .= '<strong>Endpoint:</strong> GET /api/spielplan<br>';
$apiInfo .= '<strong>Beispiel:</strong> Header X-API-Key: DEIN_KEY<br>';
$apiInfo .= '<strong>POST:</strong> /api/liveticker';
$apiInfo .= '</div>';

echo $apiForm->get();
echo $apiInfo;
