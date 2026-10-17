<?php

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');

$spielid = rex_request('spielid', 'int');    
$spielnummerid = rex_request('spielnummer', 'string');    
$aktivesaison = rex_request('aktivesaison', 'int');        
$aktiveklasse = rex_request('aktiveklasse', 'int');    
          
// FUNKTION prüfung speichern
if ($func == 'geprueft') {
    
    $pruefid = rex_request('id', 'int'); 
    // Aktive Saison auslesen
    $datentutup = rex_sql::factory();
    $sql = 'SELECT id, importid FROM '.rex::getTablePrefix() .'950_spielplan WHERE id = "'.$pruefid.'" AND importid > 0 ';
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl))
    {
        $aktiverimport = $datentutup->getValue("importid", 0);   
            
        $komfristtutup = rex_sql::factory();
        $komfristtutup->setDebug(false);
        $currentUser = rex::getUser();
        $tmpusername = $currentUser->getValue('name');

        $sql = 'UPDATE ' . rex::getTablePrefix() . '950_spielplan SET geprueft = 1, prueferlogin = "'.rex::getUser()->getLogin().'", pruefer = "'.$tmpusername.'", pruefdatum = "'.date("Y-m-d h:i:s").'", pruefimportid = "'.$aktiverimport.'" WHERE id = "'. $pruefid .'"';  
        $komfristtutup->setQuery( $sql );    

        echo '<div class="alert alert-success">Das Spiel wurde als geprüft markiert.</div>';         
    } 
    else
    {
        // ungueltig
        echo '<div class="alert alert-danger">Es wurde kein gültiges Spiel gewählt.</div>';
    } 
    $func = '';
}   
// ENDE FUNKTION prüfung speichern

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

    if ((empty($aktivesaison)) AND (empty($suche)))
    {
        // Warnung
        echo '<div class="alert alert-danger">Bitte wählen Sie eine Saison.</div>';
    }
    else if ((empty($aktiveklasse))  AND (empty($suche)))
    {
        // Warnung
        echo '<div class="alert alert-danger">Bitte wählen Sie eine Spielklasse.</div>';
    }
    else
    {
        // jetzt geht es los
  
    $listensql = 'select id, spielnr, spieltagnr, datum, uhrzeit, importid, pruefimportid, geprueft, pruefer from '. rex::getTablePrefix() .'950_spielplan WHERE 1=1 ';
    if (!empty($suche))  $listensql .= ' AND spielnr LIKE "%'.$suche.'%" ';                             
    if (!empty($aktivesaison))  $listensql .= ' AND saisonid = '.$aktivesaison.' ';                      
    if (!empty($aktiveklasse))  $listensql .= ' AND klassenid = '.$aktiveklasse.' ';
    $listensql .= ' ORDER BY spielnr ASC, dateformat ASC, uhrzeit ASC';   

    //echo $listensql;
  
    $list = rex_list::factory( $listensql, 150 );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("suche", $suche); 	   
    $list->addParam ("aktivesaison", $aktivesaison); 		
    $list->addParam ("aktiveklasse", $aktiveklasse); 	
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    //$thIcon = '<br />';
    //$tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    //$list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    //$list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                    
    $list->removeColumn('id');    
  	$list->setColumnLabel('spielnr', 'SpielNr.');	
    //$list->setColumnParams('spielnr', ['func' => 'edit', 'id' => '###id###']);
             
  	$list->setColumnLabel('datum', 'Datum');      
  	$list->setColumnLabel('uhrzeit', 'Uhrzeit');      
  	$list->setColumnLabel('spieltagnr', 'Spieltag');
  	$list->setColumnLabel('geprueft', 'geprüft');
  	$list->setColumnLabel('pruefer', 'Prüfer');
    
  	$list->setColumnSortable('spielnr');
  	$list->setColumnSortable('datum');      
  	$list->setColumnSortable('spieltagnr');
  	$list->setColumnSortable('geprueft');
  	$list->setColumnSortable('pruefer');
       
    //$list->removeColumn('importid');  
  	$list->setColumnLabel('importid', 'Ansicht');

    $list->removeColumn('pruefimportid');  
    
    $list->setColumnFormat('geprueft', 'custom', function ($params) use($list) 
    {
        $tmp_val = $list->getValue('geprueft'); 
        $tmp_importval = $list->getValue('importid'); 
        $tmp_pruefimportval = $list->getValue('pruefimportid'); 
        if ((empty($tmp_val))) 
        {
        if (empty($tmp_importval)) return '-';
        else return '<span style="color: orange; text-decoration: line-through;">nein</span>';
        }
        else if (($tmp_val == 1)) 
        {
        if ($tmp_importval == $tmp_pruefimportval) return '<span style="color: green;">ja</span>';
        else return '<span style="color: red;">wurde neu importiert!</span>';
        }
        else return $tmp_val;
    });  
    
    $list->setColumnFormat('importid', 'custom', function ($params) use($list) 
    {
        $tmp_val = $list->getValue('spielnr'); 
        if ((empty($tmp_val))) return '-';
        else return '<a class="btn btn-dark btn-sm" href="'.rex_geturl(226,1,array("spielid" => $tmp_val )).'" target="_blank" style="color: #fff; background: #000;">Ergebnisse zeigen</a>';
    });     
    
    $list->setColumnFormat('pruefer', 'custom', function ($params) use($list, $aktivesaison, $aktiveklasse) 
    {
        $tmp_id = $list->getValue('id'); 
        $tmp_pruefer = $list->getValue('pruefer'); 
        $tmp_val = $list->getValue('geprueft'); 
        $tmp_importval = $list->getValue('importid'); 
        $tmp_pruefimportval = $list->getValue('pruefimportid'); 
        if ((empty($tmp_val))) 
        {
            if (empty($tmp_importval)) return '-';
            else return '<a class="btn btn-dark btn-sm" href="'.rex_url::BackendPage( "ergebnisdienst/geprueft", array( "func"=> "geprueft", "id" => $tmp_id, "aktivesaison" => $aktivesaison, "aktiveklasse" => $aktiveklasse ) ).'" style="color: #fff; background: green;">als geprüft markieren</a>';
        }
        else
        {
            if ($tmp_importval == $tmp_pruefimportval) return '<strong style="color: green;">'.$tmp_pruefer.'</strong>';
            else return '<a class="btn btn-dark btn-sm" href="'.rex_url::BackendPage( "ergebnisdienst/geprueft", array( "func"=> "geprueft", "id" => $tmp_id, "aktivesaison" => $aktivesaison, "aktiveklasse" => $aktiveklasse ) ).'" style="color: #fff; background: red;">als geprüft markieren</a>';
        }
    });  
    
    $content = '<div id="lsnews">' . $list->get() . '</div>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', 'Eintrag');
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
    echo $content;
    }
}
elseif ($func == 'edit' || $func == 'add') {
}
?>
