<?php

// Public read-only API endpoint: GET /api/spielplan
$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$isSpielplanLivetickerApi =
  preg_match('#/api/liveticker/?$#', $requestUriPath) === 1;
$isSpielplanApi =
  preg_match('#/api/spielplan/?$#', $requestUriPath) === 1;

if ($isSpielplanLivetickerApi) {
  require __DIR__ . '/lib/api/liveticker_endpoint.php';
  exit;
}

if ($isSpielplanApi) {
  require __DIR__ . '/lib/api/spielplan_endpoint.php';
  exit;
}

if (rex::isBackend() && rex::getUser()) {

  //////////
  // JS
  //////////
  $current_page = rex_be_controller::getCurrentPage();
  rex_view::addJSFile($this->getAssetsUrl('js/custom.js'));

  ///////////
  // CSS
  ///////////
  rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));


  ///////////
  // Subpages
  ///////////
  
  $page = $this->getProperty('page');
  $page['subpages']['spielplan'] = ['title' => 'Spielplan', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page);    
  
  $page = $this->getProperty('page');
  $page['subpages']['geprueft'] = ['title' => 'geprüft', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['spieler'] = ['title' => 'Spieler', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['spielrecht'] = ['title' => 'Spielrecht', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['mannschaft'] = ['title' => 'Mannschaft', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['klubs'] = ['title' => 'Klubs', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['klasse'] = ['title' => 'Klasse', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['saison'] = ['title' => 'Saison', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page); 
  
  $page = $this->getProperty('page');
  $page['subpages']['logins'] = ['title' => 'Logins', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page);   
  
  $page = $this->getProperty('page');
  $page['subpages']['turnier'] = ['title' => 'Turnier', 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page);   
  
  $page = $this->getProperty('page');
  $page['subpages']['einzelspiel'] = ['title' => 'Einzelspiel', 'hidden' => true, 'perm' => 'ergebnisdienst[]'];
  $this->setProperty('page', $page);     
  
  $page = $this->getProperty('page');
  $page['subpages']['uploadliste'] = ['title' => 'Uploads', 'hidden' => false, 'perm' => 'ergebnisdienst[]', 'itemclass' => 'pull-right'];
  $this->setProperty('page', $page); 

  // Hinweis: Der fruehere Reiter "API" wurde in "Einstellungen" integriert.
  $page = $this->getProperty('page');
  $page['subpages']['einstellungen'] = ['title' => 'Einstellungen', 'perm' => 'ergebnisdienst[]', 'itemclass' => 'pull-right'];
  $this->setProperty('page', $page);
  
  // Einzelspiel soll nicht ins Menu


  if ($this->getConfig('install') == 'true' && rex::getUser()) {
     $current_page = rex_be_controller::getCurrentPage();
  }



}
