<?php

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');

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
        $sql->setTable( rex::getTablePrefix() .'950_klasse');
        $sql->setWhere('id = ' . $id);

        if ($sql->delete())
        {
            echo '<div class="alert alert-success">Eintrag entfernt.</div>';
        }

        $func = '';
    }
}

if ($func == 'lizenzliste') {
    $id = (rex_request('id', 'int'));
    $testall = (rex_request('test', 'int'));

    // Funktionen
    include_once(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");

    echo '<div class="alert alert-warning">Liste der Spielberechtigungen. Für die Ausgabe als PDF oder CSV nutzen Sie bitte die Liste der Spielklassen.</div>';

    $spielerarray = array();
    
    $sql = 'select klasse FROM ' . rex::getTablePrefix() . '950_klasse WHERE 1=1 AND id = "'.$id.'"';
    $fristtutup = rex_sql::factory();
    $fristtutup->setDebug(false);
    $fristtutup->setQuery( $sql );
    $fristtreffer = $fristtutup->getRows();   
    if (empty($fristtreffer)) echo '<div class="alert alert-danger">Der gewünschte Eintrag wurde nicht gefunden.</div>';
    else
    {
        // PDF ausgeben
        $printcontent = '<table class="table table-striped">';
        $printcontent .= '<tr><th colspan=5>Aktuelle Spielberechtigungen</th></tr>';
        $printcontent .= '<tr><th>Spielklasse</th><th colspan=4>'.trim($fristtutup->getValue("klasse") ).'</th></tr>';
        $printcontent .= '<tr><th>Erstelldatum</th><th colspan=4>'.date("d.m.Y, H:i").'Uhr</th></tr>';
        $printcontent .= '<tr><td colspan=5></td></tr>';
        $printcontent .= '<tr><th>Name</th><th>Passnummer</th><th>Geburtsdatum</th><th>Altersklasse</th><th>Mannschaft</th></tr>';

        $headercontent =  $printcontent;
        $footercontent =  '</table>';


        $spielerarray[] = array("Spielklasse: ".trim($fristtutup->getValue("klasse") ).", Datum: ".date("d.m.Y, H:i")." Uhr");      
        $spielerarray[] = array("Name", "Passnummer", "Geburtsdatum", "Altersklasse", "Mannschaft");        
        
        // aktive Saison ermitteln
        $manntutup = rex_sql::factory();
        $sql = 'SELECT id, saisonjahr FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja" LIMIT 0,1';
        $manntutup->setQuery( $sql );
        $mannzahl = $manntutup->getRows();   
        if (!empty( $mannzahl )) $GLOBALS['aktivesaison_jahrende'] = $manntutup->getValue("saisonjahr");
        else $GLOBALS['aktivesaison_jahrende'] = date("Y");

        // mit Teamangabe
        $teamsrownumer = 0;
        $komfristtutup = rex_sql::factory();
        $komfristtutup->setDebug(false);
        $sql = 'select sp.spieler AS spieler, sp.passnr AS passnr, sp.stichtagsgeburtsjahr AS stichtagsgeburtsjahr, sp.geboren AS geboren, sp.spielrecht, sp.spielrecht1, sp.spielrecht2, ma.mannschaft AS team FROM ' . rex::getTablePrefix() . '950_spieler AS sp,  ' . rex::getTablePrefix() . '950_mannschaft AS ma WHERE 1=1 AND sp.spielrecht1 = "'. $id .'" AND ma.id = sp.mannschaftid AND sp.spielrecht="DKB Lizenz" ORDER by team ASC, spieler ASC';  
        //if ($testall == 1) $sql = 'select sp.spieler AS spieler, sp.passnr AS passnr, sp.geboren AS geboren, sp.spielrecht, sp.spielrecht1, sp.spielrecht2, ma.mannschaft AS team FROM ' . rex::getTablePrefix() . '950_spieler AS sp,  ' . rex::getTablePrefix() . '950_mannschaft AS ma WHERE 1=1 AND ma.id = sp.mannschaftid AND sp.spielrecht="DKB Lizenz" ORDER by team ASC, spieler ASC';  
        $komfristtutup->setQuery( $sql );
        $teamtreffer = $komfristtutup->getRows();   

        while($komfristtutup->hasNext()) 
        {
            $tmpaltersklasse = led_altersklasse( $komfristtutup->getValue("stichtagsgeburtsjahr") , $GLOBALS['aktivesaison_jahrende'] ) ;
            $printcontent .= '<tr><td>'.$komfristtutup->getValue("spieler").'</td><td>'.$komfristtutup->getValue("passnr").'</td><td>'.$komfristtutup->getValue("geboren").'</td><td>'.$tmpaltersklasse.'</td><td>'.$komfristtutup->getValue("team").'</td></tr>';
             
            $spielerarray[] = array( $komfristtutup->getValue("spieler") , $komfristtutup->getValue("passnr") , $komfristtutup->getValue("geboren"), $tmpaltersklasse, $komfristtutup->getValue("team"));      
            
            $teamsrownumer++;
            if (($teamsrownumer == 40) AND ($teamtreffer > 40))
            {
                $printcontent .= $footercontent.'<div class="page_break"></div>'.$headercontent;
                $teamsrownumer = 0;
            }
            $komfristtutup->next();
        }  

        // ohne Teamangabe
        $komfristtutup = rex_sql::factory();
        $komfristtutup->setDebug(false);
        $sql = 'select sp.spieler AS spieler, sp.passnr AS passnr, sp.geboren AS geboren, sp.spielrecht, sp.spielrecht1, sp.spielrecht2 FROM ' . rex::getTablePrefix() . '950_spieler AS sp WHERE 1=1 AND sp.spielrecht1 = "'. $id .'" AND sp.mannschaftid = 0 AND sp.spielrecht="DKB Lizenz" ORDER by spieler ASC';  
        $komfristtutup->setQuery( $sql );
        while($komfristtutup->hasNext()) 
        {
            $tmpaltersklasse = led_altersklasse( $komfristtutup->getValue("stichtagsgeburtsjahr") , $GLOBALS['aktivesaison_jahrende'] ) ;
            $printcontent .= '<tr><td>'.$komfristtutup->getValue("spieler").'</td><td>'.$komfristtutup->getValue("passnr").'</td><td>'.$komfristtutup->getValue("geboren").'</td><td>'.$tmpaltersklasse.'</td><td>-</td></tr>';
            $spielerarray[] = array( $komfristtutup->getValue("spieler") , $komfristtutup->getValue("passnr") , $komfristtutup->getValue("geboren"), $tmpaltersklasse, "-");     
            
            $teamsrownumer++;
            if (($teamsrownumer == 40) AND (1==1))
            {
                $printcontent .= $footercontent.'<div class="page_break"></div>'.$headercontent;
                $teamsrownumer = 0;
            }
            $komfristtutup->next();
        } 

        $printcontent .= $footercontent;

        $wirdpdf = (rex_request('pdf', 'int'));
        $wirdcsv = (rex_request('csv', 'int'));

        if (empty($wirdpdf)) 
        {
            if (empty($wirdcsv)) echo $printcontent;
            else
            {

                if (!function_exists("outputCSV")) 
                {
                    function outputCSV($data) {
                        $outputBuffer = fopen("php://output", 'w');
                        foreach($data as $val) {
                            $val = array_map(function($item) {
                                return iconv('UTF-8', 'Windows-1252', $item);
                            }, $val);
                            fputcsv($outputBuffer, $val, ";", '"');
                        }
                        fclose($outputBuffer);
                    }  
                }
                // Header ausgeben    
              // Bisherige Ausgabe von Redaxo löschen
              ob_end_clean();
              header('Content-type: text/csv;');
              header ('Content-Disposition: inline; filename="spielberechtigung_' . date("Ymd") . '.csv"');
              header('Cache-Control: must-revalidate, post-check=0, pre-check=0');  
              header("Pragma: no-cache");  
              header("Expires: 0");               
              outputCSV($spielerarray);	              
              exit;     

            }
        }
        else if (1==2)
        {
            
    
    //echo 'hier die PDF';
          
       $cssfirst = '<style> 
body,p, th, td { font-size: 9pt; box-sizing: border-box; } 
a { color: #000 !important; text-decoration: none !important; } 
th, td { text-align: left;  }        
.table {
 border-collapse: collapse !important;
}
.table td,
.table th {
 background-color: #fff !important;
}
.table-bordered th,
.table-bordered td {
 border: 1px solid #ddd !important;
}
.table {
width: 100%;
max-width: 100%;
margin-bottom: 20px;
}
.table > thead > tr > th,
.table > tbody > tr > th,
.table > tfoot > tr > th,
.table > thead > tr > td,
.table > tbody > tr > td,
.table > tfoot > tr > td {
padding: 4px;
line-height: 1.1;
vertical-align: top;
border-top: 1px solid #dddddd;
}
.table > thead > tr > th {
vertical-align: bottom;
border-bottom: 2px solid #dddddd;
}
.table > caption + thead > tr:first-child > th,
.table > colgroup + thead > tr:first-child > th,
.table > thead:first-child > tr:first-child > th,
.table > caption + thead > tr:first-child > td,
.table > colgroup + thead > tr:first-child > td,
.table > thead:first-child > tr:first-child > td {
border-top: 0;
}
.table > tbody + tbody {
border-top: 2px solid #dddddd;
}
.table .table {
background-color: #ffffff;
}
.table-condensed > thead > tr > th,
.table-condensed > tbody > tr > th,
.table-condensed > tfoot > tr > th,
.table-condensed > thead > tr > td,
.table-condensed > tbody > tr > td,
.table-condensed > tfoot > tr > td {
padding: 5px;
}
.table-bordered {
border: 1px solid #dddddd;
}
.table-bordered > thead > tr > th,
.table-bordered > tbody > tr > th,
.table-bordered > tfoot > tr > th,
.table-bordered > thead > tr > td,
.table-bordered > tbody > tr > td,
.table-bordered > tfoot > tr > td {
border: 1px solid #dddddd;
}
.table-bordered > thead > tr > th,
.table-bordered > thead > tr > td {
border-bottom-width: 2px;
}
.table-striped > tbody > tr:nth-of-type(odd) {
background-color: #f9f9f9;
}
.table-hover > tbody > tr:hover {
background-color: #f5f5f5;
}
table col[class*="col-"] {
position: static;
float: none;
display: table-column;
}
table td[class*="col-"],
table th[class*="col-"] {
position: static;
float: none;
display: table-cell;
}
ul.pagination { 
display: none; 
}    
a.btn { 
display: none; 
}

table.kopf {
 width: 100%;
}   
table.kopf tr td { 
 background: #CEDFF0 !important;
 margin: 0;
 padding: 0;      
 border-bottom: 0;
}
td.logospalte {
 width: 80px;   
 border-right: 5px solid #fff;
}
img.logo {
 width: 80px; height: 80px;
}
.title {
font-size: 2em; font-weight: bold; 
padding: 10px 10px 0 20px;
}    

.subtitle {
font-size: 1.2em; font-weight: bold; 
padding: 0 10px 10px 20px;
}
.page_break { page-break-before: always; }       
       </style>';

       $art_pdf_name = "spielberechtigung_" . date("Ymd") . ".pdf";
       $meinhtmlcode =  $cssfirst; 
        $meinhtmlcode .= $printcontent;
        //$meinhtmlcode .= '<br /><br />Wir arbeiten daran...';
        
        //header('Content-Type: application/pdf');       
            
        $pdf = new PdfOut();
        $pdf->setName( $art_pdf_name )
            ->setFont('Helvetica')
            ->setHtml($meinhtmlcode, true)
            ->setOrientation('portrait')
            ->setAttachment(true)
            ->setRemoteFiles(false)
            ->run();
          
    	die();       

        }
        else echo 'PDF-Ausgabe derzeit deaktiviert';
    }

    
}

if ($func == '') {


    
    // Suchfunktion
    ?>      
    <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">
    <div class="pull-left"><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="add" /><div class="input-group-btn"><button type="submit" class="btn btn-primary">+ neue Spielklasse anlegen</button></div></div></form></div>
          
    <div class="pull-right">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs">
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <input class="form-control" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Suchbegriff"><div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Suchen</button></div></div></form>
    </div>
    
    </div>
    <?php
    // Ende Suchfunktion
                                        
    $listensql = 'select id, klasse, id AS lizenz, geschlecht, spielerzahl from '. rex::getTablePrefix() .'950_klasse WHERE 1=1 ';
    if (!empty($suche)) $listensql .= " AND (klasse LIKE '%".$suche."%') ";
    $listensql .= ' ORDER BY klasse ASC';
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
    $list->setColumnLabel('klasse', 'Klasse');
    $list->setColumnParams('klasse', ['func' => 'edit', 'id' => '###id###']);  
    $list->setColumnLabel('geschlecht', 'm/w');    
    $list->setColumnLabel('spielerzahl', 'Spieler');
    $list->setColumnLabel('lizenz', 'Spielberechtigungen ausgeben');
    
    $list->setColumnFormat('lizenz', 'custom', function ($params) use($list) 
    {
        $tmp_id = $list->getValue('id'); 
        $link = '<a href="'.rex_url::BackendPage( "ergebnisdienst/klasse", array( "func" => "lizenzliste", "id" => $tmp_id, "pdf" => 1 ) ).'" class="btn btn-sm btn-primary">Spielrechte als pdf-Datei</a>';
        $link .= '&nbsp;<a href="'.rex_url::BackendPage( "ergebnisdienst/klasse", array( "func" => "lizenzliste", "id" => $tmp_id, "csv" => 1 ) ).'" class="btn btn-sm btn-warning">Spielrechte als csv-Datei</a>';
        return $link;
    });   
                                                       
  	$list->setColumnSortable('klasse', 'asc');  
  	$list->setColumnSortable('geschlecht', 'asc');  
  	$list->setColumnSortable('spielerzahl', 'asc');  

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
    $form = rex_form::factory(rex::getTablePrefix() . '950_klasse', '', 'id=' . $id);        
    
    
    $field = $form->addTextField('klasse');
    $field->setLabel("Klasse");        
    $field->setAttribute('required','required');    
    
    $field =$form->addSelectField('geschlecht');
    $field->setLabel("Geschlecht");
    $select =$field->getSelect();
    $select->setSize(1);
    $select->addOption("Männer", "m");
    $select->addOption("Frauen", "w");
    $select->setAttribute('style','width:40%');      
    
    $field =$form->addSelectField('spielerzahl');
    $field->setLabel("Spielerzahl");
    $select =$field->getSelect();
    $select->setSize(1);
    $select->addOption("6", "6");
    $select->addOption("4", "4");
    $select->setAttribute('style','width:40%');       
    
    $field = $form->addTextField('leiteremail');
    $field->setLabel("EMail für Benachrichtigungen");        
    $field->setAttribute('type','email');       

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
