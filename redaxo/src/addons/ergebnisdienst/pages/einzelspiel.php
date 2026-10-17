<?php

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');        
$aktivesaison = rex_request('aktivesaison', 'int');        
$aktiveklasse = rex_request('aktiveklasse', 'int');    

echo '<div class="alert alert-warning">Bitte notieren sie sich bei Änderungen unbedingt Spielnummer und Spieljahr! Es sind immer zwei Einträge zu ändern für Heim-Mannschaft und Gast-Mannschaft. Ist ist empfehlenswert, die Bearbeitungsformulare in einem neuen Tab zu öffnen.</div>';


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

if ($func == '') {


    
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
            <div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Anzeigen</button></div>
            </div>
            </form>
      </div>    
    </div>
    <?php
    // Ende Suchfunktion
                                        
    $listensql = 'select id, spielnr, heimodergast, gesamtkegel, satzpunkte, mannschaftspunkte, tabellenpunkte from '. rex::getTablePrefix() .'950_spieltabelle WHERE 1=1 AND  saisonid = '.$aktivesaison.' ';
    if (!empty($suche)) $listensql .= " AND (spielnr LIKE '%".$suche."%') ";
    $listensql .= ' ORDER BY spielnr DESC, heimodergast DESC';
    $list = rex_list::factory( $listensql );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("suche", $suche); 	 	
    $list->addParam ("aktivesaison", $aktivesaison); 	
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="NEU"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                    
    $list->removeColumn('id');     
	  $list->setColumnLabel('spielnr', 'Spielnummer');   
    $list->setColumnParams('spielnr', ['func' => 'edit', 'id' => '###id###']);  
  	$list->setColumnLabel('heimodergast', 'Heim oder Gast');   
  	$list->setColumnLabel('gesamtkegel', 'Kegel');    
  	$list->setColumnLabel('satzpunkte', 'MP gesamt');    
  	$list->setColumnLabel('mannschaftspunkte', 'MP Spieler');    
  	$list->setColumnLabel('tabellenpunkte', 'TP');    
    
  	$list->setColumnSortable('spielnr', 'asc');  
    
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
    $form = rex_form::factory(rex::getTablePrefix() . '950_spieltabelle', '', 'id=' . $id);        
                
    
    $field = $form->addTextField('spielnr');
    $field->setLabel("Spielnummer");        
    $field->setAttribute('readonly','readonly');        
    
    $field = $form->addSelectField('saisonid');
    $field->setLabel("Saison");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select saison as label, id from '.rex::getTablePrefix() .'950_saison ORDER BY aktivesaison ASC, label ASC';       
    //$select->addOption("...", 0);
    $select->addSqlOptions($query);                             
    $select->setAttribute('required','required');      
    //$field->setAttribute('readonly','readonly');       
    
    $field = $form->addSelectField('klassenid');
    $field->setLabel("Spielklasse");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select klasse as label, id from '.rex::getTablePrefix() .'950_klasse ORDER BY label ASC';       
    //$select->addOption("...", 0);
    $select->addSqlOptions($query);                             
    $select->setAttribute('required','required');      
    //$field->setAttribute('readonly','readonly');    
    
    $field = $form->addTextField('heimodergast');
    $field->setLabel("Mannschaft");        
    $field->setAttribute('readonly','readonly');   
    
    $field = $form->addTextField('gesamtkegel');
    $field->setLabel("Kegel gesamt");    
    
    $field = $form->addTextField('satzpunkte');
    $field->setLabel("MP gesamt");  
    
    $field = $form->addTextField('mannschaftspunkte');
    $field->setLabel("MP Spieler");  
    
    $field = $form->addTextField('tabellenpunkte');
    $field->setLabel("Tabellenpunkte");   

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
  }
}
?>
