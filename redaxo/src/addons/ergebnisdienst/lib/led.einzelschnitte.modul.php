<?php
    // Output Listen Ergebnisdienst  alle Einzelschnitte
    // Version 20180212
    // optional $zeigebereich

    $booking = array();   
    $booking['sort']  = rex_request('sort', 'string');   
    if (!empty($zeigebereich))   $booking['saison']  = $zeigebereich;
    else $booking['saison']  = 1;     
    $booking['top']  = trim(rex_request('top', 'string'));   
    if ($booking['top'] != "alle") $booking['top'] = intval( $booking['top'] );
    
    $booking['pdf']  = rex_request('pdf', 'string');     
    $booking['csv']  = rex_request('export', 'string');   
    $booking['tag']  = '';   
    $booking['mannschaft']  = '';   
    $booking['klasse']  = '';  
    $booking['klassename']  = '';    
    
    // Grundfunktionen
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");   
    
    // Saison und klasse auslesen
    $booking['saisonname'] = $booking['saisonspieltage'] = '';
    $datentutup = rex_sql::factory();
    $sql = 'SELECT saison, spieltage, saisonjahr FROM '.rex::getTablePrefix() .'950_saison WHERE id = "'.$booking['saison'].'"';
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl)) 
    {
        $booking['saisonname'] = $datentutup->getValue("saison", 0);     
        $booking['saisonspieltage'] = trim($datentutup->getValue("spieltage", 0));   
        $booking['saisonjahr'] = trim($datentutup->getValue("saisonjahr", 0)); 
    }  
    
                                
    echo '<div class="navibuttons clearfix"><div class="btn-group" role="group">';     
    //echo '&nbsp;<a class="btn btn-default pull-right makeprint" href="#">Druckansicht</a>';          
    //echo '&nbsp;<a class="btn btn-default pull-right" href="'.str_replace("index", "getpdf", rex_geturl( rex_article::getCurrentId(), rex_clang::getCurrentId(), array( 'sort' => $booking['sort'] , 'pdf' => 'pdf' )) ).'">PDF</a>';   
    echo '</div></div>';
    
    
    

    // Spielplan nach Spieltagen ausgeben   
    echo '<div id="echtergebnisliste" class="clearfix">';  
    
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.liste.einzelschnitt.php");   
    
    echo '</div>';
?>

</div>