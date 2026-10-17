<?php        
    // Version 20200528

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');
  $sort = rex_request('sort', 'string');   
  $sorttype = rex_request('sorttype', 'string');   

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
        $sql->setTable( rex::getTablePrefix() .'950_spieler');
        $sql->setWhere('id = ' . $id);
    
        if ($sql->delete())
        {
            echo '<div class="alert alert-success">Eintrag entfernt.</div>';
        }
    
        $func = '';

    }
}

/*
if ($func == 'lizenz') {
    
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");         
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   
    
    $passnr = led_passnummer(trim(rex_request('suche', 'string')));
    $spielerid = 0;
    

    $mptutup = rex_sql::factory();
    $mptutup->setDebug(false);
    $sql = 'SELECT * FROM '.rex::getTablePrefix().'950_spieler WHERE 1=1 AND passnr= "'.$passnr.'"';
    $mptutup->setQuery( $sql );
    $tmptreffer = $mptutup->getRows();  
    if (empty($tmptreffer)) echo '<div class="alert alert-danger">Die Passnummer <strong>'.$passnr.'</strong> wurde nicht gefunden.</div>';
    else if ($tmptreffer > 1) echo '<div class="alert alert-danger">Die Passnummer <strong>'.$passnr.'</strong> ist mehrfach vorhanden, bitte erst bereinigen!</div>';
    else
    {
        $spielerid = $mptutup->getValue("id");
        echo '<div class="alert alert-warning" style="background: #000; border-color: #000;">Lizenz wird geprüft für Spieler mit Passnummer <strong>'.$passnr.'</strong></div>';
        ?>
        <table class="table table-striped">
        <?php
        
        echo '<tr><th style="width: auto;">Name: </th><td style="width: 80%;">'.$mptutup->getValue("spieler").'</td></tr>';

        $tmpspielrecht = $mptutup->getValue("spielrecht");

        if ($tmpspielrecht == "DKB Lizenz")
        {
            // Lizenz eingetragen
            echo '<tr><th style="width: auto;">Spielrecht: </th><td style="width: 80%; color: green;">'.$tmpspielrecht.'</td></tr>';
            echo '<tr><td colspan=2><br /></td></tr>';

            $tmpklasse01 = '-';
            $tmpspielrecht1 = $mptutup->getValue("spielrecht1");
            if (!empty( $tmpspielrecht1 ))
            {
                $kltutup = rex_sql::factory();
                $kltutup->setDebug(false);
                $sql = 'SELECT klasse AS label FROM '.rex::getTablePrefix().'950_klasse WHERE 1=1 AND id= "'.$tmpspielrecht1.'"';
                $kltutup->setQuery( $sql );
                $kltreffer = $kltutup->getRows();  
                if (!empty($kltreffer)) $tmpklasse01 = $kltutup->getValue("label");
            }
            echo '<tr><th style="width: auto;">1. Spielberechtigung: </th><td style="width: 80%; ">'.$tmpklasse01.' [ID: '.$tmpspielrecht1.']</td></tr>';

            $tmpklasse02 = '-';
            $tmpspielrecht2 = $mptutup->getValue("spielrecht2");
            if (!empty( $tmpspielrecht2 ))
            {
                $kltutup = rex_sql::factory();
                $kltutup->setDebug(false);
                $sql = 'SELECT klasse AS label FROM '.rex::getTablePrefix().'950_klasse WHERE 1=1 AND id= "'.$tmpspielrecht2.'"';
                $kltutup->setQuery( $sql );
                $kltreffer = $kltutup->getRows();  
                if (!empty($kltreffer)) $tmpklasse02 = $kltutup->getValue("label");
            }
            echo '<tr><th style="width: auto;">2. Spielberechtigung: </th><td style="width: 80%; ">'.$tmpklasse02.' [ID: '.$tmpspielrecht2.']</td></tr>';

            $tmpmannschaft = '-';
            $tmpteam = $mptutup->getValue("mannschaftid");
            if (!empty( $tmpteam ))
            {
                $kltutup = rex_sql::factory();
                $kltutup->setDebug(false);
                $sql = 'SELECT mannschaft AS label FROM '.rex::getTablePrefix().'950_mannschaft WHERE 1=1 AND id= "'.$tmpteam.'"';
                $kltutup->setQuery( $sql );
                $kltreffer = $kltutup->getRows();  
                if (!empty($kltreffer)) $tmpmannschaft = $kltutup->getValue("label");
            }
            echo '<tr><th style="width: auto;">Mannschaft: </th><td style="width: 80%;">'.$tmpmannschaft.' [ID: '.$tmpteam.']</td></tr>';
        }
        else echo '<tr><th style="width: auto;">Spielrecht: </th><td style="width: 80%; color: red;">Mitgliedschaft im  TKV ist nicht hinterlegt. Bitte prüfen.</td></tr>';

        ?>
        </table>
        <br />
        <div style="padding:: 1rem 0;">
    <div class="pull-left"><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="id" value="<?php echo $spielerid; ?>" /><input type="hidden" name="func" value="edit" /><div class="input-group-btn"><button type="submit" class="btn btn-primary">Spieler bearbeiten</button></div></div></form></div>
    <div class=""><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="" /><div class="input-group-btn"><button type="submit" class="btn btn-secondary">Zurück zur Liste</button></div></div></form></div>
        </div>
    <hr style="clear: both; width: 100%;" />
        <?php
    }
    
    if (empty( $spielerid ))
    {
        ?>
        <div class=""><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="" /><div class="input-group-btn"><button type="submit" class="btn btn-secondary">Zurück zur Liste</button></div></div></form></div>
        <hr style="clear: both; width: 100%;" />
        <?php
    } 
    
    

    //$func = '';
}
*/

if ($func == 'savepassnr') {
    $oldnr = (rex_request('oldnr', 'string'));    
    $neuenr = (rex_request('neuenr', 'string'));
    
    if ((!empty( $oldnr )) AND  (!empty( $neuenr )))
    {                           
        $mptutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix().'950_spielergebnisse SET spielerid = "'.$neuenr.'" WHERE spielerid = "'.$oldnr.'" ';
        $mptutup->setQuery( $sql );  
        //echo  $sql."<br /><br />";
        
        $mptutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix().'950_spielergebnisse SET ersatzspielernummer = "'.$neuenr.'" WHERE ersatzspielernummer = "'.$oldnr.'" ';
        $mptutup->setQuery( $sql );    
        //echo  $sql."<br /><br />";
        
        $mptutup = rex_sql::factory();
        $sql = 'DELETE FROM '.rex::getTablePrefix().'950_spieler WHERE passnr = "'.$neuenr.'" ';
        $mptutup->setQuery( $sql );  
        //echo  $sql."<br /><br />";     
        
        $mptutup = rex_sql::factory();
        $sql = 'UPDATE '.rex::getTablePrefix().'950_spieler SET passnr = "'.$neuenr.'" WHERE passnr = "'.$oldnr.'" ';
        $mptutup->setQuery( $sql );    
        //echo  $sql."<br /><br />";
        
        echo '<div class="alert alert-success">Die Passnummer (alt: '.$oldnr.') wurde geändert (neu: '.$neuenr.').</div>';
        
        
        $mptutup = rex_sql::factory();
        $mptutup->setDebug(false);
        $sql = 'SELECT id, passnr, spieler FROM '.rex::getTablePrefix().'950_spieler WHERE passnr = "'.$neuenr.'" ';
        $mptutup->setQuery( $sql );     
        //echo  $sql."<br /><br />";
        
        $tmptreffer = $mptutup->getRows();  
        if (!empty($tmptreffer))
        {
            echo '<div class="alert alert-success">Der Spieler mit dem Namen <em>'.$mptutup->getValue("spieler", 0).'</em> wurde aktualisiert.</div>';
        }
        else echo '<div class="alert alert-warning">Bitte legen Sie einen neuen Spieler mit der neuen Passnummer an. Bisher ist dieser nicht eingetragen.</div>';
    }
    else echo '<div class="alert alert-danger">Die angegebenen Nummern waren nicht korrekt.</div>';
    $func = '';
}  

if ($func == 'changepassnr') {

  ?>
  
<div class="panel panel-info">
  <div class="panel-heading">Passnummer ändern</div> 
  <div class="panel-body">
    <div class="container">
    <form  action="<?php print rex_url::currentBackendPage( ); ?>" method="post" enctype="multipart/form-data">      
      <input name="func" value="savepassnr" type="hidden" />
        <legend>Passnummer ändern</legend>                                                       
        <p class="help-block">
        Sie können hier für Spieler die bisherige Passnummer in allen Ergebnislisten austauschen gegen eine neue Passnummer. <br>Falls die neue Passnummer schon angelegt wurde, wird diese wieder gelöscht und die alte Passnummer aktualisiert.
        </p> 
        <div class="form-group">
        <label for="oldnr" class="control-label">Alte Passnummer</label>
        <input required type="text" class="form-control" id="oldnr" name="oldnr" placeholder="">
        </div>     
        <div class="form-group">
        <label for="neuenr" class="control-label">Neue Passnummer</label>
        <input required type="text" class="form-control" id="neuenr" name="neuenr" placeholder="">
        </div>
        <button type="submit" class="btn btn-success">Starten</button>
    </form> 
    </div>  
  </div>
  
</div>
  <?php
}



// Prüfung doppelte Spielnummern
$mptutup = rex_sql::factory();
$mptutup->setDebug(false);
$sql = 'SELECT DISTINCT(passnr) AS doppelpass, COUNT(id) AS cnt FROM '.rex::getTablePrefix().'950_spieler WHERE 1=1 GROUP BY passnr HAVING cnt > 1 ORDER BY `cnt` DESC ';
$mptutup->setQuery( $sql );
$tmptreffer = $mptutup->getRows();  
if (!empty($tmptreffer))
{
    ?>
    <div class="alert alert-danger">
    <strong>Folgende Passnummern sind mehrfach vergeben:</strong><br><ul>  
    <?php
    
            
                for ($spi = 0; $spi<$tmptreffer; $spi++) 
                { 
                    $tmpistdoppelt = $mptutup->getValue("doppelpass", $spi);
                    echo '<li>'.$tmpistdoppelt.'</li>';    
                    $mptutup->next();
                }
    ?>
    </ul>
    </div>
    <br />
    <?php
}     
// ENDE Prüfung doppelte Spielnummern

if ($func == '') {

    // Funktionen
    include_once(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");
    // Suchfunktion
    ?>      
    <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">
    <div class="pull-left"><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="add" /><div class="input-group-btn"><button type="submit" class="btn btn-primary">+ neuen Spieler anlegen</button></div></div></form></div>
    <div class="pull-left" style="margin-left: 2px;"><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="changepassnr" /><div class="input-group-btn"><button type="submit" class="btn btn-warning"># Passnummer ändern</button></div></div></form></div>
          
    <div class="pull-right">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs">
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <input class="form-control" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Name oder Passnr."><div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Suchen</button></div></div></form>
    </div>
    
    </div>
    <?php
    // Ende Suchfunktion

    // Prüffunktion
    /*
    if (1==2)
    {
    ?>      
    <div class="alert alert-info clearfix" style="background-color: #091E56; border-color: #091E56; ">
    <div class="pull-left"><p style="color: #fff;"></p></div>
          
    <div class="pull-right">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg">
    <input type="hidden" name="func" value="lizenz" />
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <input class="form-control" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Passnr.">
    <div class="input-group-btn"><button type="submit" class="btn btn-success">Spielrecht prüfen</button></div></div></form>
    </div>
    
    </div>
    <?php
    }
    */
    // Ende Prüffunktion
                                        
    $listensql = 'select id, spieler, passnr, stichtagsgeburtsjahr, stichtagsgeburtsjahr AS altersklasse from '. rex::getTablePrefix() .'950_spieler WHERE 1=1 ';
    if (!empty($suche)) $listensql .= " AND (spieler LIKE '%".$suche."%' OR passnr LIKE '%".$suche."%') ";
    
      
    if ((empty($sort)) OR (empty($sorttype))) $listensql .= ' ORDER BY spieler ASC, passnr ASC';
    else $listensql .= " ORDER BY ".$sort." ".$sorttype." ";

    $list = rex_list::factory( $listensql );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("suche", $suche); 	   
    $list->addParam ("sort", $sort); 	          
    $list->addParam ("sorttype", $sorttype); 	 

    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="NEU"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                    
    $list->removeColumn('id');
    $list->setColumnLabel('spieler', 'Spieler');
    $list->setColumnParams('spieler', ['func' => 'edit', 'id' => '###id###']);   
    $list->setColumnLabel('passnr', 'Pass-Nr.');
    $list->setColumnLabel('stichtagsgeburtsjahr', 'Geburtsjahr');
    $list->setColumnLabel('altersklasse', 'Altersklasse');
                                                       
  	$list->setColumnSortable('spieler', 'asc');   
	$list->setColumnSortable('passnr', 'asc');  
	$list->setColumnSortable('stichtagsgeburtsjahr', 'asc');  
	$list->setColumnSortable('altersklasse', 'asc');  
    

    // aktive Saison ermitteln
    $manntutup = rex_sql::factory();
    $sql = 'SELECT id, saisonjahr FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja" LIMIT 0,1';
    $manntutup->setQuery( $sql );
    $mannzahl = $manntutup->getRows();   
    if (!empty( $mannzahl )) $GLOBALS['aktivesaison_jahrende'] = $manntutup->getValue("saisonjahr");
    else $GLOBALS['aktivesaison_jahrende'] = date("Y");
    //echo $aktivesaison_jahrende;
    
    $list->setColumnFormat('altersklasse', 'custom', function ($params) use($list) 
    {
        $tmp_val = $list->getValue('stichtagsgeburtsjahr'); 
        /*
        if ((empty($tmp_val))) $retval =  '-';
        else 
        {
            $tmpalter = intval( $GLOBALS['aktivesaison_jahrende'] - $tmp_val -1);
            if ( $tmpalter < 10 ) $retval = "u10";
            else if ( $tmpalter < 14 ) $retval = "u14";
            else if ( $tmpalter < 19 ) $retval = "u18";
            else if ( $tmpalter < 24 ) $retval = "u23";
            else if ( $tmpalter < 50 ) $retval = "allgemein";
            else if ( $tmpalter < 60 ) $retval = "Sen A";
            else if ( $tmpalter < 70 ) $retval = "Sen B";
            else $retval = "Sen C";

            //$retval .= ' ('.$tmpalter.')';
        }
        */
        $retval = led_altersklasse( $tmp_val, $GLOBALS['aktivesaison_jahrende'] ) ;
        return $retval;
    }); 

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
    $form = rex_form::factory(rex::getTablePrefix() . '950_spieler', '', 'id=' . $id);    
    
	$field = $form->addTextField('spieler');
    $field->setLabel("Name, Vorname");                                  
    $field->setAttribute('required','required');        
    
	$field = $form->addTextField('passnr');
    $field->setLabel("Pass-Nr.");                                  
    $field->setAttribute('required','required');            
    
	$field = $form->addTextField('geboren');
    $field->setLabel("geboren, Format dd.mm.YYYY");    
    
	$field = $form->addTextField('stichtagsgeburtsjahr');
    $field->setLabel("Geburtstagsjahr nach Stichtagsregel");      
    
    $field =$form->addSelectField('geschlecht');
    $field->setLabel("Geschlecht");
    $select =$field->getSelect();
    $select->setSize(1);
    $select->addOption("Männer", "m");
    $select->addOption("Frauen", "w");   
    $select->addOption("ohne Zuordnung", "d");
    $select->setAttribute('style','width:40%');    
    
	$field = $form->addTextField('spielrecht');
    $field->setLabel("Spielrecht (DKB Lizenz)");     
    
	$field = $form->addTextField('klub');
    $field->setLabel("Klub (aktuelles Spieljahr)"); 
    
	//$field = $form->addTextField('spielrecht1');
    //$field->setLabel("1. Spielberechtigung (ID)");    
    /*
    */     
    $field = $form->addSelectField('spielrecht1');
    $field->setLabel("1. Spielberechtigung");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select klasse as label, id from '.rex::getTablePrefix() . '950_klasse ORDER BY label ASC';       
    $select->addOption("...", 0);
    $select->addSqlOptions($query);                             
    //$select->setAttribute('required','required');    
    
	//$field = $form->addTextField('spielrecht2');
    //$field->setLabel("2. Spielberechtigung (ID)");   
    
    $field = $form->addSelectField('spielrecht2');
    $field->setLabel("2. Spielberechtigung");                
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select klasse as label, id from '.rex::getTablePrefix() . '950_klasse ORDER BY label ASC';       
    $select->addOption("...", 0);
    $select->addSqlOptions($query);                             
    //$select->setAttribute('required','required');  
    
	//$field = $form->addTextField('mannschaftsid');
    //$field->setLabel("Mannschaft des Clubs (ID)");      
    $field = $form->addSelectField('mannschaftid');
    $field->setLabel("Mannschaft");     
    $select = $field->getSelect();
    $select->setSize(1);
    $select->setMultiple(false);
    $query = 'select mannschaft as label, id from '.rex::getTablePrefix() . '950_mannschaft ORDER BY label ASC';       
    $select->addOption("...", 0);
    $select->addSqlOptions($query);  

	$field = $form->addTextField('sonderspielrecht');
    $field->setLabel("Sonderspielrecht");     

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

    //$formElements = [];
    //$fragment->setVar('elements', $formElements, false);

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
