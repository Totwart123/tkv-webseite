<?php

// 950_turnierdaten
//$turniermannschaftsid = 440;
include( rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');

$spielid = rex_request('spielid', 'int');    
$spielnummerid = rex_request('spielnummer', 'string');    
$aktivesaison = rex_request('aktivesaison', 'int');        
$aktiveklasse = rex_request('aktiveklasse', 'int');    
          
// FUNKTION Löschen
if ($func == 'delete') {
    $id = (rex_request('id', 'int'));
    $sql = rex_sql::factory();

    // $sql->setDebug();
    $sql->setTable( rex::getTablePrefix() .'950_turnierdaten');
    $sql->setWhere('id = ' . $id);

    if ($sql->delete())
    {
        echo '<div class="alert alert-success">Eintrag entfernt.</div>';
    }

    $func = '';
}   
// ENDE FUNKTION Löschen

// FUNKTION speichern
if ($func == 'savethis') {
    $id = (rex_request('id', 'int'));
    
    if (!empty( $id ))
    {
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE id = "'.$id.'" ';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();    
        if (!empty( $datenzahl ))
        {
            $spielnr = $datentutup->getValue("spielnr");
            $spielklasse = $datentutup->getValue("klassenid");
            $spielsaison = $datentutup->getValue("saisonid");

            //alte Einträge löschen
            $sql = rex_sql::factory();
            $sql = 'DELETE FROM '.rex::getTablePrefix() .'950_turnierdaten WHERE klassenid = "'.$spielklasse.'" AND saisonid = "'.$spielsaison.'" AND spielnr = "'.$spielnr.'" ';
            $datentutup->setQuery( $sql );
    
            // neue Einträge sichern
            $alleteamnummern = explode( ',' , rex_request('teamnummern', 'string'));
            foreach( $alleteamnummern AS $curteam )
            {
                $curteam = intval( $curteam );
                if (!empty($curteam))
                {
                    //echo '<br />ID: '.$curteam.' ';
                    $curteam_tp = rex_request('team_tp_'.$curteam , 'int');
                    $curteam_kegel = rex_request('team_kegel_'.$curteam , 'int');
                    
                    /* jetzt speichern */
                    $spielsql = rex_sql::factory();
                    $spielsql->setTable(''.rex::getTablePrefix() .'950_turnierdaten');
                    $spielsql->setValue('spielnr', $spielnr );   
                    $spielsql->setValue('klassenid', $spielklasse );
                    $spielsql->setValue('saisonid', $spielsaison );
                    $spielsql->setValue('teamid', $curteam);                                         
                    $spielsql->setValue('kegel', $curteam_kegel );                                      
                    $spielsql->setValue('tp', $curteam_tp );        
                    $spielsql->insert();  
                }

            }
            
            $func = 'edit';
            echo '<div class="alert alert-success">Eintrag gespeichert.</div>';
            
        }
        else 
        {
        
            $func = '';
            echo '<div class="alert alert-danger">Eintrag nicht gefunden, es wurde nichts gespeichert.</div>';
        }


    }
    else 
    {
    
        $func = '';
        echo '<div class="alert alert-danger">Eintrag nicht gefunden, es wurde nichts gespeichert.</div>';
    }
}   
// ENDE FUNKTION speichern

// FUNKTION Sicherheitsabfrage Ergebnisse entfernen         
// ENDE FUNKTION Sicherheitsabfrage Ergebnisse entfernen   

         
// FUNKTION Ergebnisse entfernen   
// ENDE FUNKTION Ergebnisse entfernen   
    
        
// FUNKTION Sicherheitsabfrage Ergebnisse zu Spielnummer entfernen   
// ENDE FUNKTION Ergebnisse zu Spielnummer entfernen   

       
// FUNKTION  Ergebnisse zu Spielnummer entfernen         
// ENDE FUNKTION Ergebnis aus Tabelle entfernen   

if ($func == '') {
                     
  
    // Saison festlegen
    if (empty($aktivesaison))
    {
          // Aktive Saison auslesen
          $datentutup = rex_sql::factory();
          $sql = 'SELECT id, saison, aktivesaison FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja"';
          $datentutup->setQuery( $sql );
          $datenzahl = $datentutup->getRows();     
          if (!empty($datenzahl))
          {
              $aktivesaison = $datentutup->getValue("id", 0);                 
          }    
    }  
    
    // Suchfunktion
    ?>     
      <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">    
        <div class="pull-right"> 
            <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs">
            <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
            <input class="form-control" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Spielnummer"><div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Suchen</button></div></div>
            </form>
        </div>  
        <div class="pull-left">
          <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
          <input type="hidden" name="func" value="" />
          <div class="input-group input-group-xs">
            <select class="form-control" name="aktivesaison">
              <?php
            
                // Saisons auslesen
                $datentutup = rex_sql::factory();
                $sql = 'SELECT id, saison, aktivesaison FROM '.rex::getTablePrefix() .'950_saison ORDER BY saison DESC';
                $datentutup->setQuery( $sql );
                $datenzahl = $datentutup->getRows();     
                if (!empty($datenzahl))
                {
                
                    for ($spi = 0; $spi<$datenzahl; $spi++) 
                    { 
                        $tmpistaktiv = $datentutup->getValue("id", $spi);
                        if ($tmpistaktiv == $aktivesaison) echo '<option value="'.$datentutup->getValue("id", $spi).'" selected=selected>'.$datentutup->getValue("saison", $spi).'</option>';  
                        else echo '<option value="'.$datentutup->getValue("id", $spi).'">'.$datentutup->getValue("saison", $spi).'</option>';      
                        $datentutup->next(); 
                    }
                } 
            
              ?>
            </select>
            <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>    
            <select class="form-control" name="aktiveklasse">
              <option value="">Spielklasse wählen...</option>
                <?php
                
                // Saisons auslesen
                $datentutup = rex_sql::factory();
                $sql = 'SELECT id, klasse FROM '.rex::getTablePrefix() .'950_klasse ORDER BY klasse DESC';
                $datentutup->setQuery( $sql );
                $datenzahl = $datentutup->getRows();     
                if (!empty($datenzahl))
                {
                
                    for ($spi = 0; $spi<$datenzahl; $spi++) 
                    { 
                        $tmpistaktiv = $datentutup->getValue("id", $spi);   ;
                        if ($tmpistaktiv == $aktiveklasse) echo '<option value="'.$datentutup->getValue("id", $spi).'" selected=selected>'.$datentutup->getValue("klasse", $spi).'</option>';  
                        else echo '<option value="'.$datentutup->getValue("id", $spi).'">'.$datentutup->getValue("klasse", $spi).'</option>';      
                        $datentutup->next();  
                    }
                } 
                
                
                ?>
            </select>
            <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
            <div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Anzeigen</button></div>
            </div>
            </form>
      </div>    
    </div>
    <?php
    // Ende Suchfunktion   
  
    $listensql = 'select id, spielnr, datum, uhrzeit, spieltagnr from '. rex::getTablePrefix() .'950_spielplan WHERE 1=1 ';
    if (!empty($suche))  $listensql .= ' AND spielnr LIKE "%'.$suche.'%" ';                             
    if (!empty($aktivesaison))  $listensql .= ' AND saisonid = '.$aktivesaison.' ';                      
    if (!empty($aktiveklasse))  $listensql .= ' AND klassenid = '.$aktiveklasse.' ';                 
    $listensql .= ' AND heimmannschaftid = '.$turniermannschaftsid.' ';
    $listensql .= ' ORDER BY spielnr ASC, dateformat ASC, uhrzeit ASC';   
  
    $list = rex_list::factory( $listensql, 150 );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("suche", $suche); 	   
    $list->addParam ("aktivesaison", $aktivesaison); 		
    $list->addParam ("aktiveklasse", $aktiveklasse); 	
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="NEU"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                    
    $list->removeColumn('id');    
  	$list->setColumnLabel('spielnr', 'SpielNr.');	
    $list->setColumnParams('spielnr', ['func' => 'edit', 'id' => '###id###']);
             
  	$list->setColumnLabel('datum', 'Datum');      
  	$list->setColumnLabel('uhrzeit', 'Uhrzeit');      
  	$list->setColumnLabel('spieltagnr', 'Spieltag');
    
  	$list->setColumnSortable('spielnr');
  	$list->setColumnSortable('datum');      
  	$list->setColumnSortable('spieltagnr');
    
    $content = '<div id="lsnews">' . $list->get() . '</div>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', 'Eintrag');
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
    echo $content;
}
elseif ($func == 'add') {
    
    echo '<div class="alert alert-danger">Für neue Turnier-Ergebnisse bitte einen Eintrag im Spielplan anlegen mit der Heimmannschaft "Turnier". Anschließend wird dieser Eintrag hier aufgelistet.</div>';      

}
elseif ($func == 'edit') {
    
    echo '<div class="alert alert-info">Turnier-Ergebnisse für den Eintrag:</div>';      
    $id = rex_request('id', 'int');    
    
    if (!empty( $id ))
    {
        
        // Spielauswahl einschränken auf den Tag
        $datentutup = rex_sql::factory();
        $sql = 'SELECT * FROM '.rex::getTablePrefix() .'950_spielplan WHERE id = "'.$id.'" ';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();    
        if (!empty( $datenzahl ))
        {
            // Hier Details ausgeben
            echo '<table class="table">';
            echo '<tr><td><p><strong>Spielnummer:</strong></p></td><td><p>'.$datentutup->getValue("spielnr").'</p></td></tr>';
            echo '<tr><td><p><strong>Datum:</strong></p></td><td><p>'.$datentutup->getValue("datum").'</p></td></tr>';
            echo '<tr><td><p><strong>Anmerkung:</strong></p></td><td><p>'.$datentutup->getValue("anmerkung").'</p></td></tr>';
            echo '</table>';
            echo '<br /><h2>Ergebnisse:</h2><br />';
            // formular anlegen
            echo '<form action="'.rex_url::currentBackendPage( ).'" method="post">';
            echo '<input type="hidden" name="func" value="savethis" />';
            echo '<input type="hidden" name="id" value="'.$id.'" />';
            // mannschaften auflisten

            
            // Mannschaftsnamen auslesen
            if (!@is_array($mannschaftsnamen))
            {
                $mannschaftsnamen = array();
                $manntutup = rex_sql::factory();
                $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
                $manntutup->setQuery( $sql );
                $mannzahl = $manntutup->getRows();   
                for ($spi = 0; $spi<$mannzahl; $spi++) 
                { 
                    $mannschaftsnamen[ $manntutup->getValue("id", $spi) ] = $manntutup->getValue("mannschaft", $spi);   
                    $manntutup->next( );
                }   
            }     
            
            // Spielfreie Mannschaften auslesen     
            //$spielfreieteams = array();
            if (!@is_array($spielfreieteams))
            {
                $spielfreieteams = array();
                $manntutup = rex_sql::factory();
                $sql = 'SELECT id, mannschaft FROM '.rex::getTablePrefix() .'950_mannschaft';
                $manntutup->setQuery( $sql );
                $mannzahl = $manntutup->getRows();   
                for ($spi = 0; $spi<$mannzahl; $spi++) 
                { 
                    $tmp_testname = trim(strtolower( $manntutup->getValue("mannschaft", $spi) ));
                    if ($tmp_testname == "spielfrei") $spielfreieteams[] =  $manntutup->getValue("id", $spi);
                    //echo $tmp_testname."<br />";
                    $manntutup->next( );
                }   
            }
            //print_r( $spielfreieteams );
        
            // Manschaften auflisten
            $teamliste = array();
            $teamtutup = rex_sql::factory();

            $sql = 'SELECT spielnr, heimmannschaftid, gastmannschaftid FROM '.rex::getTablePrefix() .'950_spielplan WHERE klassenid = "'. $datentutup->getValue("klassenid") .'" AND saisonid = "'. $datentutup->getValue("saisonid") .'"';
            $teamtutup->setQuery( $sql );
            $teamzahl = $teamtutup->getRows();   
            for ($spi = 0; $spi<$teamzahl; $spi++) 
            { 
                $tmp_id = $teamtutup->getValue("heimmannschaftid", $spi);
                if (!in_array( $tmp_id , $spielfreieteams ))
                {
                    if (!in_array( $tmp_id , $teamliste )) $teamliste[] = $tmp_id;  
                }    
                $tmp_id = $teamtutup->getValue("gastmannschaftid", $spi);      
                if (!in_array( $tmp_id , $spielfreieteams ))
                {
                    if (!in_array( $tmp_id , $teamliste )) $teamliste[] = $tmp_id;  
                }
                $teamtutup->next( );
            } 

            echo '<table class="table table-striped">';
            echo '<thead><tr><th>Mannschaft</th><th>Tabellenpunkte</th><th>Kegel</th></tr></thead>';
            echo '<tbody>';

            $teamnummern = array();
            $ergebnisse = array();

            $sql = 'SELECT teamid, tp, kegel FROM '.rex::getTablePrefix() .'950_turnierdaten WHERE klassenid = "'. $datentutup->getValue("klassenid") .'" AND saisonid = "'. $datentutup->getValue("saisonid") .'" AND spielnr="'.$datentutup->getValue("spielnr").'"';
            $teamtutup->setQuery( $sql );
            $teamzahl = $teamtutup->getRows();   
            for ($spi = 0; $spi<$teamzahl; $spi++) 
            { 
                $ergebnisse[ $teamtutup->getValue("teamid", $spi) ] = array( "tp" => $teamtutup->getValue("tp", $spi), "kegel" => $teamtutup->getValue("kegel", $spi));
                $teamtutup->next( );
            } 

            $teamcounter = 1;
            foreach( $teamliste AS $curteam)
            {
                if ( (!empty($curteam)) AND ( $curteam != $turniermannschaftsid))
                {
                    if (!isset( $ergebnisse[ $curteam ] )) $ergebnisse[ $curteam ] = array( "tp" => '', "kegel" => '');
                    
                    echo '<tr>';
                    echo '<td><input type="hidden" name="team_id_'.$teamcounter.'" value="'.$curteam.'">'.$mannschaftsnamen[ $curteam ].'</td>';
                    echo '<td><div class="input-group"><input type="text" class="form-control"  name="team_tp_'.$curteam.'" value="'.$ergebnisse[ $curteam ]['tp'].'" /><div class="input-group-addon">TP</div></div></td>';
                    echo '<td><div class="input-group"><input type="text" class="form-control"  name="team_kegel_'.$curteam.'" value="'.$ergebnisse[ $curteam ]['kegel'].'" /><div class="input-group-addon">Kegel</div></div></td>';
                    echo '</tr>';
                    $teamnummern[] = $curteam;
                    $teamcounter++;
                }
            }
            echo '</tbody>';
            echo '</table>';
            echo '<input type="hidden" name="teamnummern" value="'.implode("," , $teamnummern ).'" />';

            // ENDE mannschaften auflisten
            echo '<button class="btn btn-success" type="submit">Speichern</button>';
            echo '</form>';
            // ENDE formular anlegen
        }
        else echo '<div class="alert alert-danger">Es gibt keinen passenden Eintrag [Error: 409].</div>';     
    }
    else echo '<div class="alert alert-danger">Es gibt keinen passenden Eintrag [Error: 408].</div>';     

    // mannschaften auflisten
    //TP und Kegel
    // speichern button
    // alte Werte aus DB auslesen

}
?>
