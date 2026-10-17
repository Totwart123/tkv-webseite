<?php

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');

// Lösch-Funktion
if ($func == 'delete') {
    $id = (rex_request('id', 'int'));
    $kennwort = (rex_request('kennwort', 'string'));
    
    include_once(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");
    // Sicherheitsabfrage
    if ( $kennwort != $loeschpasswort)
    {
        if (!empty( $kennwort )) echo '<div class="alert alert-danger clearfix" style="margin: 0 0 1rem 0;">Das eingegebene Passwort ist falsch!</div>'; 
        ?>      
        <div class="alert alert-info clearfix" xx-style="background-color: #01B4DD; border-color: #01B4DD; ">
            <p><strong>Bitte geben Sie das Kennwort ein, um das Löschen zu bestätigen.</strong></p><br />
            <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
            <input type="hidden" name="func" value="delete" />     
            <input type="hidden" name="id" value="<?php echo $id; ?>" />     
            <div class="input-group input-group">
                    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
                    <input class="form-control" name="kennwort" value="" type="password" placeholder="Kennwort" required />
                    <div class="input-group-btn"><button type="submit" class="btn btn-danger" style="margin-left: 1rem;">Ja, Eintrag wirklich löschen!</button></div>
                </div>
            </form>
        </div>
        <?php
    }
    else
    {
      $sql = rex_sql::factory();

      // $sql->setDebug();
      $sql->setTable( rex::getTablePrefix() .'950_saison');
      $sql->setWhere('id = ' . $id);

      if ($sql->delete())
      {
          echo '<div class="alert alert-success">Eintrag entfernt.</div>';
      }

      $func = '';
    }
}

if ($func == 'spielrechteweg') {
  $resetpwd = (rex_request('pwd', 'string'));
  if ($resetpwd == 'reset')
  {
    // löschen
        $datentutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix() .'950_spieler SET spielrecht1 = "",  spielrecht2 = "", mannschaftid="", sonderspielrecht="" WHERE 1=1';  
        $datentutup->setQuery( $sql );   

        echo '<div class="alert alert-success">Die Spielrechte wurden zurückgesetzt.</div>';
  }
  else echo '<div class="alert alert-danger">Passwort falsch! Geben Sie "reset" ein.</div>';

  $func = '';
}

// Funktion aktive Saison festlegen
if ($func == 'setsaison') {  
    $setzeaktiv = rex_request('aktivesaison', 'int');
    if (!empty($setzeaktiv))
    {
        $datentutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix() .'950_saison SET aktivesaison = "nein"';  
        $datentutup->setQuery( $sql );      
        $datentutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix() .'950_saison SET aktivesaison = "ja" WHERE id = "'.$setzeaktiv.'"';
        $datentutup->setQuery( $sql );
        
        echo '<div class="alert alert-success">Die aktive Saison wurde festgelegt.</div>';
    
    }
    $func = '';      
}   
// ENDE Funktion aktive Saison festlegen 

// Funktion Dateien bereinigen  

if ($func == 'bereinigung') { 
  
    // Mannschaftsnamen auslesen
    $saisondaten = array();            
    $saisonspielnummern = array();
    $behaltediesedateien = array();    
    $archiviertedateien = array();
    $fromdir = '../leduploads/';
    
    $manntutup = rex_sql::factory();
    $sql = 'SELECT id, aktivesaison, dateienarchiviert, saisonjahr FROM '.rex::getTablePrefix() .'950_saison';
    $manntutup->setQuery( $sql );
    $mannzahl = $manntutup->getRows();   
    for ($spi = 0; $spi<$mannzahl; $spi++) 
    { 
        $saisondaten[ $manntutup->getValue("id", $spi) ] = array( 
          "aktiv" =>$manntutup->getValue("aktivesaison", $spi),       
          "archiviert" =>$manntutup->getValue("dateienarchiviert", $spi),
          "jahr" => intval($manntutup->getValue("saisonjahr", $spi))
        );  
        $manntutup->next(); 
    }  
    
    //die noch nicht archiviert wurden und deren Dateien verschieben 
    foreach ($saisondaten AS $tmpsaisonid => $tmpsaisondaten)
    {
        if ($tmpsaisondaten['archiviert'] == 'ja') {}
        else 
        {
            // Dateien zu allen Spielnummern auslesen    
            $saisonspielnummern[ $tmpsaisonid ] = array();
            $datentutup = rex_sql::factory();
            $sql = 'SELECT '.rex::getTablePrefix() .'950_uploads.filename AS dateiname, '.rex::getTablePrefix() .'950_spieltabelle.spielnr AS spielnummer FROM '.rex::getTablePrefix() .'950_uploads, '.rex::getTablePrefix() .'950_spieltabelle WHERE '.rex::getTablePrefix() .'950_spieltabelle.saisonid = "'.$tmpsaisonid .'" AND '.rex::getTablePrefix() .'950_uploads.id = '.rex::getTablePrefix() .'950_spieltabelle.importid ';
            $datentutup->setQuery( $sql );
            $datenzahl = $datentutup->getRows();  
             
            if ($tmpsaisondaten['aktiv'] == 'ja') 
            {                                                       
                // aktive Saison
                // In array speichern, damit diese nicht gelöscht werden
                for ($spi = 0; $spi<$datenzahl; $spi++) 
                {
                    $dateiname = trim($datentutup->getValue("dateiname", $spi));
                    if (!in_array($dateiname, $behaltediesedateien)) $behaltediesedateien[] = $dateiname;     
                    $spielnummer = trim($datentutup->getValue("spielnummer", $spi));
                    if (!in_array($spielnummer, $saisonspielnummern[ $tmpsaisonid ])) $saisonspielnummern[ $tmpsaisonid ][] = $spielnummer;                
                    $datentutup->next();   
                }  
            }
            else
            {               
                //inaktive Saison
                // Ordner anlegen
                
                if (!empty($tmpsaisondaten['jahr'])) $tmpdir = $fromdir.$tmpsaisondaten['jahr'].'/';
                else $tmpdir = $fromdir.$tmpsaisonid.'/';
                if ( is_dir($tmpdir) ) 
                { 
                    //echo '<br />Das Verzeichnis '.$tmpdir.' existiert bereits.';
                }
                else
                {                                                       
                    mkdir( $tmpdir, 0700);
                    //echo '<br />Das Verzeichnis '.$tmpdir.' wurde neu angelegt.';
                }
                
                // Dateien zu allen Spielnummern auslesen     
                for ($spi = 0; $spi<$datenzahl; $spi++) 
                {
                    $dateiname = trim($datentutup->getValue("dateiname", $spi));
                    if (!in_array($dateiname, $archiviertedateien)) 
                    {
                        $archiviertedateien[] = $dateiname;   
                          
                        // Jetzt Datei verschieben
                        if(!file_exists($tmpdir.$dateiname))
                        {
                            if (!copy($fromdir.$dateiname, $tmpdir.$dateiname)) {
                               echo '<br />Die Datei  '.$dateiname.' konnte leider nicht kopiert werden.';
                               if (!in_array($dateiname, $behaltediesedateien)) $behaltediesedateien[] = $dateiname;     
                            }
                         }
                    }
                    $spielnummer = trim($datentutup->getValue("spielnummer", $spi));
                    if (!in_array($spielnummer, $saisonspielnummern[ $tmpsaisonid ])) $saisonspielnummern[ $tmpsaisonid ][] = $spielnummer;
                    $datentutup->next();   
                }     
                 
                $datentutup = rex_sql::factory();
                $sql = 'UPDATE '.rex::getTablePrefix() .'950_saison SET dateienarchiviert = "ja" WHERE id = "'.$tmpsaisonid.'"';
                $datentutup->setQuery( $sql );
            }
            
            //echo '<pre>';
            //print_r( $saisonspielnummern[ $tmpsaisonid ] );  
            //echo '</pre>';
        } 
    }  
    
    // alle unnötigen Dateien löschen
    $alledateien = scandir( $fromdir ); //Ordner auslesen
    $missingfiles = array();                     
    $knownfiles = array();
    foreach ($alledateien as $dateiname) { // Ausgabeschleife
       if (!empty($dateiname))
       {
         if ( is_dir ( $fromdir.$dateiname )) {}
         else if (!in_array($dateiname, $behaltediesedateien))
         {
            $resultdel = @unlink( $fromdir.$dateiname );
            if (!$resultdel) echo '<br />nicht gelöscht werden konnte: '.$dateiname;
         }
         else
         {
            if (!in_array($dateiname, $knownfiles))  $knownfiles[] =  $dateiname;
         }  
       }
    };    
    
    //fehlende Dateien ermitteln
    foreach ($behaltediesedateien as $dateiname)
    {
        if (!in_array($dateiname,$knownfiles))
        {
            if (!in_array($dateiname,$missingfiles)) $missingfiles[] =  $dateiname;
        }
    }
    
    //echo '<br />Anzahl zu behaltender Dateien: '.count($behaltediesedateien);    
    //echo '<br />Davon leider nicht vorhanden: '.count($missingfiles);
    //echo '<pre>';
    //print_r($missingfiles);
    //echo '</pre>';
    
    echo '<div class="alert alert-success">Die Dateien wurden bereinigt.</div>';
    $func = '';  
}   
// ENDE Funktion Dateien bereinigen

if ($func == '') {


    
    // Suchfunktion
    ?>     
    <div class="row">
      <div class="col-md-4"> 
        <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">  
          <strong>Dateien bereinigen</strong><br />
          <small>Mit Klick auf <em>Bereinigen</em> werden die Dateien im Ordner /leduploads bereinigt. 
          Es verbleiben ausschließlich die für die aktive Saison genutzten Dateien im Ordner, andere Dateien werden gelöscht. 
          Dateien für andere Saisons, die noch nicht archiviert wurden, werden in Unterordner verschoben.
          </small>
          <br />
          <br>
          <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs"><input type="hidden" name="func" value="bereinigung" /><div class="input-group-btn"><button type="submit" class="btn btn-primary">Bereinigen</button></div></div></form>
        </div>  
      </div>  
      <div class="col-md-4"> 
        <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">  
          <strong>Aktive Saison festlegen</strong><br />
          <small>Mit der Auswahl einer aktiven Saison werden alle Uploads von Spielergebnissen der Nutzer dieser festgelegten Saison zugeordnet.
          </small>
          <br />
          <br>
          <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
          <input type="hidden" name="func" value="setsaison" />
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
                        $tmpistaktiv = $datentutup->getValue("aktivesaison", $spi);
                        if ($tmpistaktiv == "ja") echo '<option value="'.$datentutup->getValue("id", $spi).'" selected=selected>'.$datentutup->getValue("saison", $spi).'</option>';  
                        else echo '<option value="'.$datentutup->getValue("id", $spi).'">'.$datentutup->getValue("saison", $spi).'</option>';      
                        $datentutup->next(); 
                    }
                } 
            
              ?>
            </select>
            <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
            <div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Festlegen</button></div>
            </div>
            </form>
        </div>  
      </div> 
      
      <div class="col-md-4"> 
        <div class="alert alert-danger clearfix" style=" ">  
          <strong>Spielrechte löschen</strong><br />
          <small>Mit Klick auf <em>Spielrechte löschen</em> werden für alle Spieler die Spielberechtigungen gelöscht. <br><br> Bitte geben Sie zur Bestätigung "reset" in das Bestätigungsfeld ein.
          </small>
          <br />

          <br>
          <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs"><input type="hidden" name="func" value="spielrechteweg" /><input class="form-control" name="pwd" value="" type="text" placeholder="reset eingeben!"><div class="input-group-btn"><button type="submit" class="btn btn-primary">Spielrechte löschen</button></div></div></form>
        </div>  
      </div>   
    </div>
    <?php
    // Ende Suchfunktion
                                        
    $listensql = 'select id, saison, aktivesaison, dateienarchiviert from '. rex::getTablePrefix() .'950_saison WHERE 1=1 ';
    //if (!empty($suche)) $listensql .= " AND (name LIKE '%".$suche."%' OR email LIKE '%".$suche."%') ";
    $listensql .= ' ORDER BY saison DESC';
    $list = rex_list::factory( $listensql );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("suche", $suche); 	   
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="NEU"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                    
    $list->removeColumn('id');
    $list->setColumnLabel('saison', 'Saison');
    $list->setColumnParams('saison', ['func' => 'edit', 'id' => '###id###']);  
    $list->setColumnLabel('aktivesaison', 'aktiv');    
    $list->setColumnLabel('dateienarchiviert', 'archiviert');
                                                       
  	$list->setColumnSortable('saison', 'asc');  
  	$list->setColumnSortable('aktivesaison', 'asc');  
  	$list->setColumnSortable('dateienarchiviert', 'asc');  

    $delete = 'deleteCol';
    $list->addColumn($delete, '<i class="rex-icon rex-icon-delete"></i> '.'Löschen', -1, ['<th></th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($delete, ['id' => '###id###', 'func' => 'delete']);

    $list->addLinkAttribute($delete, 'data-confirm', rex_i18n::msg('delete') . ' ?');
    $list->removeColumn('id');
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
    $form = rex_form::factory(rex::getTablePrefix() . '950_saison', '', 'id=' . $id);        
    
    $field = $form->addTextField('saison');
    $field->setLabel('Saison');
    $field->getValidator()->add('notEmpty', 'Bitte die Saison ausfüllen!');       
    
    $field = $form->addTextField('saisonjahr');
    $field->setLabel('Jahreszahl des Saisonende (für Stichtagsregel), Format YYYY');
    $field->getValidator()->add('notEmpty', 'Bitte die Jahreszahl ausfüllen!');    
    
    $field =$form->addSelectField('dateienarchiviert');
    $field->setLabel("Bereits archiviert?");
    $select =$field->getSelect();
    $select->setSize(1);                   
    $select->addOption("nein", "nein");
    $select->addOption("ja", "ja");
    $select->setAttribute('style','width:40%');       

    if ($func == 'edit') {
        $form->addParam('id', $id);
    }

    $content = $form->get();

    // Löschen-Button entfernen
    $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
    $dom = new DOMDocument();
    $dom->loadHTML($content);
    $xpath = new DOMXPath($dom);
    $buttonsToDelete = $xpath->query('//button[contains(@class, "btn-delete")]');
    foreach ($buttonsToDelete as $button) {
        $button->parentNode->removeChild($button);
    }
    $content = $dom->saveHTML();
    // Ende Löschen-Button entfernen

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', "$fieldset");
    $fragment->setVar('body', $content, false);
    $content = '<div id="lsnews">' . $fragment->parse('core/page/section.php') . '</div>';
    echo $content;

    if ($func == 'edit') {
       ?>
       <div class="text-right text-end clearfix" xx-style="background-color: #01B4DD; border-color: #01B4DD; ">
           <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
           <input type="hidden" name="func" value="delete" />     
           <input type="hidden" name="id" value="<?php echo $id; ?>" />     
           <button type="submit" class="btn btn-link btn-sm" style="color: red !important;">Diesen Eintrag löschen...</button></div>
           </form>
       </div>
       <?php
    }
    
  
  if($func == 'edit') {
  }
}
?>
