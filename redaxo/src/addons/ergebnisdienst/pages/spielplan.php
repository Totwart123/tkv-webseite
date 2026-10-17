<?php

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');

$spielid = rex_request('spielid', 'int');    
$spielnummerid = rex_request('spielnummer', 'string');    
$aktivesaison = rex_request('aktivesaison', 'int');        
$aktiveklasse = rex_request('aktiveklasse', 'int');   


// Zusatzfunktion 2024 bei zahlungspflichtigen Spielverlegungen
$datentutup = rex_sql::factory();
$sql = 'SELECT id, spielnr, zahlungspflichtig FROM '.rex::getTablePrefix() .'950_spielplan WHERE spielverlegung = "|1|" AND zahlungspflichtig > "" AND spielverlegunginfogesendet = "" ';
$datentutup->setQuery( $sql );
$datenzahl = $datentutup->getRows();     
if (!empty($datenzahl))
{
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   
    for ($spi = 0; $spi<$datenzahl; $spi++) 
    { 
        // Daten auslesen
        $tmpid = $datentutup->getValue("id");
        $tmp_spielnr = $datentutup->getValue("spielnr");
        $tmp_spielinfo = $datentutup->getValue("zahlungspflichtig");

        // Mail senden
        $mail = new rex_mailer(); 
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(false);
        $mail->From = $realmailsender;
        $mail->FromName = $realmailsender;  
        $mail->Sender = $realmailsender;    
        $mail->AddAddress(  $mailrecipientspielverlegung , $mailrecipientspielverlegung ); 
        //$mail->addCC('');  
        $mail->Subject = "Kostenpflichtige Spielverlegung";
        $tmpmailtext = "Das Spiel ".$tmp_spielnr." wurde kostenpflichtig verlegt. \n\n";         
        $tmpmailtext .= "Zahlungspflichtig: ".$tmp_spielinfo."\n\n";  
        $tmpmailtext .= "Bitte den Zahlungseingang überwachen. \n\n";                 
        $tmpmailtext .= "Änderung am ".date("d.m.Y H:i")." Uhr - von ".rex::getUser()->getValue('name')." [".rex::getUser()->getLogin()."]\n\n";
        $mail->Body = $tmpmailtext;
        
        $mail_status = $mail->Send();    
        
        // Update der Datenbank 
        $ptutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix() .'950_spielplan SET spielverlegunginfogesendet = "'.date("Y-m-d H:i").'" WHERE id ='.$tmpid;
        $ptutup->setQuery( $sql );

        // Nächster Eintrag
        $datentutup->next(); 
    }
    echo '<div class="alert alert-success clearfix">Die Geschäftsstelle wurde per EMail über '.$datenzahl.' zahlungspflichtige Spielverlegung(en) informiert.</div>';              
}   

          
// FUNKTION Löschen
if ($func == 'delete') {
    $id = (rex_request('id', 'int'));
    $sql = rex_sql::factory();

    // $sql->setDebug();
    $sql->setTable( rex::getTablePrefix() .'950_spielplan');
    $sql->setWhere('id = ' . $id);

    if ($sql->delete())
    {
        echo '<div class="alert alert-success">Eintrag entfernt.</div>';
    }

    $func = '';
}   
// ENDE FUNKTION Löschen


// FUNKTION Sicherheitsabfrage Ergebnisse entfernen                  
if($func == "manualwegok"){
  
    if ((!empty($spielnummerid)) AND (!empty($spielid)))
    {         
                        
            // Saisons auslesen
            $datentutup = rex_sql::factory();
            $sql = 'SELECT '.rex::getTablePrefix() .'950_spielplan.saisonid AS saisonid, '.rex::getTablePrefix() .'950_saison.saison AS saisonname, '.rex::getTablePrefix() .'950_klasse.klasse AS klassenname FROM '.rex::getTablePrefix() .'950_spielplan, '.rex::getTablePrefix() .'950_saison, '.rex::getTablePrefix() .'950_klasse WHERE '.rex::getTablePrefix() .'950_spielplan.saisonid = '.rex::getTablePrefix() .'950_saison.id AND '.rex::getTablePrefix() .'950_spielplan.klassenid = '.rex::getTablePrefix() .'950_klasse.id AND '.rex::getTablePrefix() .'950_spielplan.id="'.$spielid.'"';
            $datentutup->setQuery( $sql );
            $datenzahl = $datentutup->getRows();     
            if (!empty($datenzahl))
            {
                $tmpsaisonname = $datentutup->getValue("saisonname", 0);
                $tmpklassenname = $datentutup->getValue("klassenname", 0);   
                $usedsaison = $datentutup->getValue("saisonid", 0);     
            
                echo '<br /><br /><h1>Sicherheitsabfrage</h1><br />';
                echo '<strong>Spielnummer: '.$spielnummerid.'</strong><br />';    
                echo '<strong>Saison: '.$tmpsaisonname.'</strong><br />'; 
                echo '<strong>Klasse: '.$tmpklassenname.'</strong><br />'; 
                echo '<div style="clear: both; padding-top: 20px; width: 100%;">';
                ?>
                  
                <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="pull-left" style="margin-right: 1rem;">
                <input type="hidden" name="func" value="manualweg" />   
                <input type="hidden" name="spielnummer" value="<?php echo $spielnummerid; ?>" />
                <input type="hidden" name="spielid" value="<?php echo $spielid; ?>" />
                <button type="submit" class="btn btn-danger">Ja, ich möchte alle eingetragenen Spielergebnisse l&ouml;schen!</button>
                </form>
                
                <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="pull-left">
                <input type="hidden" name="func" value="edit" />   
                <input type="hidden" name="id" value="<?php echo $spielid; ?>" />
                <button type="submit" class="btn btn-abort">Abbrechen</button>
                </form>
                
                <?php
                echo '</div>';                     
            }  
            else 
            {
                //echo $sql;
                echo '<div class="alert alert-danger">Das gewünschte Spiel konnte nicht gefunden werden [ERROR 40A]</div>';
            }
          
    }
    else $func = '';
} 
// ENDE FUNKTION Sicherheitsabfrage Ergebnisse entfernen   

         
// FUNKTION Ergebnisse entfernen  
if($func == "manualweg"){   
        $usedsaison = $usedklasse = 0;
        $datentutup = rex_sql::factory();
        $sql = 'SELECT '.rex::getTablePrefix() .'950_spielplan.saisonid AS saisonid, '.rex::getTablePrefix() .'950_spielplan.klassenid AS klassenid, '.rex::getTablePrefix() .'950_saison.saison AS saison FROM '.rex::getTablePrefix() .'950_spielplan, '.rex::getTablePrefix() .'950_saison WHERE '.rex::getTablePrefix() .'950_spielplan.id = "'.$spielid.'" AND '.rex::getTablePrefix() .'950_spielplan.spielnr="'.$spielnummerid.'" AND '.rex::getTablePrefix() .'950_spielplan.saisonid = '.rex::getTablePrefix() .'950_saison.id';
        $datentutup->setQuery( $sql );
        $datenzahl = $datentutup->getRows();     
        if (!empty($datenzahl)) 
        {
            $usedsaison = $datentutup->getValue("saisonid", 0);    
            $usedklasse = $datentutup->getValue("klassenid", 0);  
        }
        if ((!empty($spielnummerid)) AND (!empty($spielid)) AND (!empty($usedsaison)) AND (!empty($usedklasse)))
        {
            $datentutup = rex_sql::factory();
            $sql = "DELETE FROM ".rex::getTablePrefix() ."950_spieltabelle WHERE spielnr = '".$spielnummerid."' AND saisonid = '".$usedsaison."' AND klassenid = '".$usedklasse."'";
            $datentutup->setQuery( $sql );  
            
            $datentutup = rex_sql::factory();
            $sql = "DELETE FROM ".rex::getTablePrefix() ."950_spielergebnisse WHERE spielnr = '".$spielnummerid."' AND saisonid = '".$usedsaison."' AND klassenid = '".$usedklasse."'";
            $datentutup->setQuery( $sql );   
             
            //echo $sql;
            echo '<div class="alert alert-success">Alle eingetragenen Ergebnisse wurden entfernt.</div>';
              
        }
        $func = '';
}  
// ENDE FUNKTION Ergebnisse entfernen   
    
        
// FUNKTION Sicherheitsabfrage Ergebnisse zu Spielnummer entfernen                
if($func == "manualok"){
  
    if ((!empty($spielnummerid)) AND (!empty($spielid)))
    {                   
                        
            // Saisons auslesen
            $datentutup = rex_sql::factory();
            $sql = 'SELECT '.rex::getTablePrefix() .'950_spielplan.saisonid AS saisonid, '.rex::getTablePrefix() .'950_saison.saison AS saisonname, '.rex::getTablePrefix() .'950_klasse.klasse AS klassenname FROM '.rex::getTablePrefix() .'950_spielplan, '.rex::getTablePrefix() .'950_saison, '.rex::getTablePrefix() .'950_klasse WHERE '.rex::getTablePrefix() .'950_spielplan.saisonid = '.rex::getTablePrefix() .'950_saison.id AND '.rex::getTablePrefix() .'950_spielplan.klassenid = '.rex::getTablePrefix() .'950_klasse.id AND '.rex::getTablePrefix() .'950_spielplan.id="'.$spielid.'"';
            $datentutup->setQuery( $sql );
            $datenzahl = $datentutup->getRows();     
            if (!empty($datenzahl))
            {
                $tmpsaisonname = $datentutup->getValue("saisonname", 0);
                $tmpklassenname = $datentutup->getValue("klassenname", 0);   
                $usedsaison = $datentutup->getValue("saisonid", 0);     
            
                echo '<br /><br /><h1>Sicherheitsabfrage</h1><br />';
                echo '<strong>Spielnummer: '.$spielnummerid.'</strong><br />';    
                echo '<strong>Saison: '.$tmpsaisonname.'</strong><br />'; 
                echo '<strong>Klasse: '.$tmpklassenname.'</strong><br />'; 
                echo '<div style="clear: both; padding-top: 20px; width: 100%;">';
                   
                echo '<div class="alert alert-danger">Wenn Sie jetzt die Ergebnisse per Hand eintragen, werden die  importierten Ergebnisse für das Spiel überschrieben und bisher per Hand eingetragene Ergebnisse gelöscht. <br /><strong>Wollen Sie dies wirklich?</strong></div>';        
                echo '<div style="clear: both; padding-top: 20px; width: 100%;">';
                ?>
                  
                <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="pull-left" style="margin-right: 1rem;">
                <input type="hidden" name="func" value="manual" />   
                <input type="hidden" name="spielnummer" value="<?php echo $spielnummerid; ?>" />
                <input type="hidden" name="spielid" value="<?php echo $spielid; ?>" />
                <button type="submit" class="btn btn-danger">Ja, ich möchte die Spielergebnisse per Hand eintragen!</button>
                </form>
                
                <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="pull-left">
                <input type="hidden" name="func" value="edit" />   
                <input type="hidden" name="id" value="<?php echo $spielid; ?>" />
                <button type="submit" class="btn btn-abort">Abbrechen</button>
                </form>
                
                <?php
            }
            else $func = '';
          
    }
    else $func = '';
} 
// ENDE FUNKTION Ergebnisse zu Spielnummer entfernen   

       
// FUNKTION  Ergebnisse zu Spielnummer entfernen   
if($func == "manual"){
   
    include ( rex_path::src().'/addons/ergebnisdienst/lib/led.functions.php');
    if ((!empty($spielnummerid)) AND (!empty($spielid)))
    {
        echo '<div class="alert alert-info">Die bisherigen Ergebnisse wurden gelöscht. Sie können jetzt Ergebnisse per Hand eintragen.</div>';
                    
          // Spielid auslesen       
          $mptutup = rex_sql::factory();
          $sql = 'SELECT heimmannschaftid, gastmannschaftid, saisonid, klassenid FROM '.rex::getTablePrefix() .'950_spielplan WHERE id = "'.$spielid .'"';
          $mptutup->setQuery( $sql );
          $tmptreffer = $mptutup->getRows();   
          if (!empty($tmptreffer))
          {
              $datentutup = rex_sql::factory();
              $sql = "DELETE FROM ".rex::getTablePrefix() ."950_spieltabelle WHERE spielnr = '".led_spielnummer($spielnummerid)."' AND saisonid='".$mptutup->getValue("saisonid", 0)."' AND klassenid='".$mptutup->getValue("klassenid", 0)."' AND importid = 'manual'";
              $datentutup->setQuery( $sql );   
              //echo $sql;
              
              $datentutup = rex_sql::factory();
              $sql = "INSERT INTO ".rex::getTablePrefix() ."950_spieltabelle (spielnr,saisonid,klassenid,importid,teamid,gegnerid,heimodergast) VALUES ('".led_spielnummer($spielnummerid)."','".$mptutup->getValue("saisonid", 0)."','".$mptutup->getValue("klassenid", 0)."','manual','".$mptutup->getValue("heimmannschaftid", 0)."','".$mptutup->getValue("gastmannschaftid", 0)."','heim')";
              $datentutup->setQuery( $sql );     
              
              $datentutup = rex_sql::factory();
              $sql = "INSERT INTO ".rex::getTablePrefix() ."950_spieltabelle (spielnr,saisonid,klassenid,importid,teamid,gegnerid,heimodergast) VALUES ('".led_spielnummer($spielnummerid)."','".$mptutup->getValue("saisonid", 0)."','".$mptutup->getValue("klassenid", 0)."','manual','".$mptutup->getValue("gastmannschaftid", 0)."','".$mptutup->getValue("heimmannschaftid", 0)."','gast')";
              $datentutup->setQuery( $sql );   
              //echo $sql;       
                  
              echo '<div style="clear: both; padding-top: 20px; width: 100%;">';     
              ?>  
                <form action="<?php print rex_url::backendPage( 'ergebnisdienst/einzelspiel' ); ?>" method="post">
                <input type="hidden" name="func" value="" />   
                <input type="hidden" name="aktivesaison" value="<?php echo $mptutup->getValue("saisonid", 0); ?>" />   
                <input type="hidden" name="suche" value="<?php echo led_spielnummer($spielnummerid); ?>" />
                <button type="submit" class="btn btn-success">Jetzt Ergebnisse f&uuml;r Heim- und Gastmannschaft eintragen</button>
                </form>
                
                <?php
              
               echo '</div>';
        }
          
    }
}            
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
elseif ($func == 'edit' || $func == 'add') {
    $fieldset = $func == 'edit' ? 'Bearbeiten' : 'Hinzufügen';
    $id = rex_request('id', 'int');
    $form = rex_form::factory(rex::getTablePrefix() . '950_spielplan', '', 'id=' . $id);        
    
    
	$field = $form->addTextField('spielnr');
    $field->setLabel("Spiel-Nummer");                                  
    $field->setAttribute('required','required');         
    
	$field = $form->addTextField('datum');
    $field->setLabel("angesetztes Datum dd.mm.yyyy");                                  
    $field->setAttribute('required','required');       
    
	$field = $form->addTextField('uhrzeit');
    $field->setLabel("Zeit hh:mm");                                  
    $field->setAttribute('required','required');           
    /*   
    */      
    $field = $form->addSelectField('saisonid');
    $field->setLabel("Saison");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select saison as label, id from '.rex::getTablePrefix() . '950_saison ORDER BY aktivesaison ASC, label ASC';       
    //$select->addOption("...", 0);
    $select->addSqlOptions($query);                             
    $select->setAttribute('required','required');  
    
    $field = $form->addSelectField('klassenid');
    $field->setLabel("Klasse");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select klasse as label, id from '.rex::getTablePrefix() . '950_klasse ORDER BY label ASC';       
    $select->addOption("...", 0);
    $select->addSqlOptions($query);                             
    $select->setAttribute('required','required');     
    
    $field = $form->addSelectField('heimmannschaftid');
    $field->setLabel("Heim-Mannschaft");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select mannschaft as label, id from '.rex::getTablePrefix() . '950_mannschaft ORDER BY label ASC';       
    $select->addOption("...", 0);
    $select->addSqlOptions($query);             
    $select->setAttribute('required','required');               
    
    $field = $form->addSelectField('gastmannschaftid');
    $field->setLabel("Gast-Mannschaft");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select mannschaft as label, id from '.rex::getTablePrefix() . '950_mannschaft ORDER BY label ASC';       
    $select->addOption("...", 0);
    $select->addSqlOptions($query);               
    $select->setAttribute('required','required');        
    
	$field = $form->addTextField('tickerurl');
    $field->setLabel("URL oder ID Live-Ticker");      
    
	$field = $form->addTextField('streamurl');
    $field->setLabel("URL oder ID Live-Stream");                  
    
	$field = $form->addTextField('spieltagnr');
    $field->setLabel("Spieltag");      
    $field->setAttribute('style','width:4em');   
    
    $field = $form->addTextAreaField('anmerkung');
    $field->setLabel("Anmerkung");            
    $field->setAttribute('style','height:4em');   
    
	$field = $form->addMediaField('spielbericht');
    $field->setLabel("Informationen/Spielbericht (PDF-Datei)");  

    if($func == 'edit') {
        
    $form->addFieldset('Infos bei Spielverlegung bzw Nichtantritt');       

    // Checkbox-Feld hinzufügen 
        $field = $form->addCheckboxField('spielverlegung');
        $field->setLabel('Kostenpflichtige Spielverlegung bzw. Nichtantritt');
        $field->addOption( 'ja, kostenpflichtig' , 1 );  
        
        $field = $form->addTextField('zahlungspflichtig');
        $field->setLabel("Zahlungspflichtig (Text)");     
    }

    if ($func == 'edit') {
        $form->addParam('id', $id);
    }

    $content = $form->get();
    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', "$fieldset");
    $fragment->setVar('body', $content, false);
    $content = '<div id="lsnews">' . $fragment->parse('core/page/section.php') . '</div>';
    echo $content;
    
  
	if($func == 'edit') {
		// Jetzt Ergebnisse anzeigen 
    
          // Spielid auslesen       
          $mptutup = rex_sql::factory();
          $sql = 'SELECT spielnr, saisonid, klassenid, spielverlegunginfogesendet FROM '.rex::getTablePrefix() . '950_spielplan WHERE id = "'.$id .'"';
          $mptutup->setQuery( $sql );
          $tmptreffer = $mptutup->getRows();   
          if (!empty($tmptreffer))
          {
            // NEU: Info, falls kostenpflichtig verlegt
            $ergebnisspielverlegunginfogesendet =  $mptutup->getValue("spielverlegunginfogesendet", 0);  

            if (!empty( $ergebnisspielverlegunginfogesendet )) echo '<div class="alert alert-info" style="background: #999; border-color: #888;">Das Spiel wurde kostenpflichtig verlegt und die Info dazu per Mail am '.date("d.m.Y", strtotime( $ergebnisspielverlegunginfogesendet ) ).' gesendet.</div>';
              
              echo '<div class=" clearfix"><br /><h3>Vorhandene Ergebnisse:</h3>';
                                                                        
              $ergebnispspielsaison =  $mptutup->getValue("saisonid", 0);     
              $ergebnispspielklasse =  $mptutup->getValue("klassenid", 0);
              $ergebnispspielnr =  $mptutup->getValue("spielnr", 0);
              // Kegel auslesen  HEIM  
              $mptutup = rex_sql::factory();
              $sql = 'SELECT * FROM '.rex::getTablePrefix() . '950_spieltabelle WHERE spielnr = "'.$ergebnispspielnr .'" AND saisonid= "'.$ergebnispspielsaison.'" AND klassenid= "'.$ergebnispspielklasse.'" AND heimodergast = "heim" ORDER BY spielnr ASC';
              $mptutup->setQuery( $sql );
              $tmptreffer = $mptutup->getRows();   
                                      
              // Kegel auslesen  GAST      
              $mpgasttutup = rex_sql::factory();
              $sql = 'SELECT * FROM '.rex::getTablePrefix() . '950_spieltabelle WHERE spielnr = "'.$ergebnispspielnr .'" AND saisonid= "'.$ergebnispspielsaison.'" AND klassenid= "'.$ergebnispspielklasse.'" AND heimodergast = "gast" ORDER BY spielnr ASC';
              $mpgasttutup->setQuery( $sql );
              $tmpgasttreffer = $mpgasttutup->getRows();   
              
              if ((!empty($tmptreffer)) AND (!empty($tmpgasttreffer)))
              {
                  // Jetzt Ergebnisse ausgeben
                  echo '<table class="table table-striped">';
                  echo '<tr><td colspan="3"><strong style="color: #414A8A;">';
                  echo 'Kegel gesamt: </strong></td><td><strong style="color: #414A8A;">'.$mptutup->getValue("gesamtkegel", 0)."&nbsp;:&nbsp;".$mpgasttutup->getValue("gesamtkegel", 0)."</strong><br />";   
                  echo '</td></tr>';         
                  echo '<tr><td colspan="3"><strong style="color: #414A8A;">';
                  echo 'MP Spieler: </strong></td><td><strong style="color: #414A8A;">'.$mptutup->getValue("mannschaftspunkte", 0)."&nbsp;:&nbsp;".$mpgasttutup->getValue("mannschaftspunkte", 0)."</strong><br />";   
                  echo '</td></tr>';                                                                                
                  echo '<tr><td colspan="3"><strong style="color: #414A8A;">';
                  echo 'MP gesamt: </strong></td><td><strong style="color: #414A8A;">'.$mptutup->getValue("satzpunkte", 0)."&nbsp;:&nbsp;".$mpgasttutup->getValue("satzpunkte", 0)."</strong><br />";  
                  echo '</td></tr>';         
                  echo '<tr><td colspan="3"><strong style="color: #414A8A;">';
                  echo 'Tabellenpunkte: </strong></td><td><strong style="color: #414A8A;">'.$mptutup->getValue("tabellenpunkte", 0)."&nbsp;:&nbsp;".$mpgasttutup->getValue("tabellenpunkte", 0)."</strong><br />"; 
                  echo '</td></tr>';   
                  echo '</table>';
                  
              }  
              else echo '<div class="alert alert-warning">Keine Ergebnisse vorhanden.</div>';      
                  
              echo '</div>';
              echo '<div class=" clearfix">';
              ?>
              <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="pull-left">
              <input type="hidden" name="func" value="manualok" />   
              <input type="hidden" name="spielnummer" value="<?php echo $ergebnispspielnr; ?>" />
              <input type="hidden" name="spielid" value="<?php echo $id; ?>" />
              <button type="submit" class="btn btn-success btn-xs">Spielergebnisse per Hand eintragen</button>
              </form>
              
              <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="pull-right">
              <input type="hidden" name="func" value="manualwegok" />   
              <input type="hidden" name="spielnummer" value="<?php echo $ergebnispspielnr; ?>" />
              <input type="hidden" name="spielid" value="<?php echo $id; ?>" />
              <button type="submit" class="btn btn-danger btn-xs">Alle Ergebnisse entfernen</button>
              </form>
              
              <?php
              echo '</div>';
          }
  }
}
?>
