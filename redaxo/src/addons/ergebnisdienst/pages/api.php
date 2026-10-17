<?php

echo rex_view::title('API');

$form = rex_config_form::factory('ergebnisdienst');
$field = $form->addTextField('api_key');
$field->setLabel('API-Key');
$field->setAttribute('autocomplete', 'off');
$field->setNotice('Wird fuer GET /api/spielplan benoetigt. Uebergabe nur via Header X-API-Key.');
$prefixField = $form->addTextField('liveticker_url_prefix');
$prefixField->setLabel('Liveticker URL Prefix');
$prefixField->setAttribute('autocomplete', 'off');
$prefixField->setNotice('Wird fuer POST /api/spielplan/liveticker genutzt. Entweder Basis-URL (UUID wird angehaengt) oder Platzhalter {id} verwenden.');

echo $form->get();

echo '<div class="alert alert-info" style="margin-top: 15px;">';
echo '<strong>Endpoint:</strong> GET /api/spielplan<br>';
echo '<strong>Beispiel:</strong> Header X-API-Key: DEIN_KEY<br>';
echo '<strong>POST:</strong> /api/liveticker';
echo '</div>';
