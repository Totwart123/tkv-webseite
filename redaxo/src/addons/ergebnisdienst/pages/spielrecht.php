<?php        
    // Version 20250526

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');
$sort = rex_request('sort', 'string');
$sorttype = rex_request('sorttype', 'string');

$mindestspielzahl = 5; // mehr als das

if ($func == 'delete') {
    echo '<div class="alert alert-success">Das Löschen ist hier nicht erlaubt.</div>';
    $func = '';
}

// testweise Aerguemnte ausgeben
//foreach ($_SERVER as $key => $value) {
    /*
    foreach ($_REQUEST as $key => $value) {
    echo $key . " => " . $value . "<br>";
}
*/

if ($func == 'einsatzzahl') {  

    // Aktive Saison auslesen
    $aktivesaison = 0;
    $sql = 'SELECT id, saison, aktivesaison FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja"';
    $datentutup = rex_sql::factory();
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl))
    {
        $aktivesaison = $datentutup->getValue("id", 0);                 
    }  

    // Alle Einträge zurücksetzen
    $sql = 'UPDATE rex_950_spieler SET saisonspieldatum = "", spieleanderesrecht = 0 WHERE 1=1';
    //echo '<pre>'.$sql.'</pre><br />';
    $datentutup = rex_sql::factory();
    $datentutup->setQuery( $sql );


    // Aktive Spieler ermitteln und markieren
    $sql = '
    UPDATE rex_950_spieler s
    INNER JOIN (
    SELECT s.passnr AS passnummer
    FROM rex_950_spieler s
    LEFT JOIN rex_950_spielergebnisse e ON s.passnr = e.spielerid OR s.passnr = e.ersatzspielernummer
    WHERE s.spielrecht1 > 0 AND s.spielrecht2 = 0 AND e.saisonid = "'.$aktivesaison.'"
    AND (e.spielnr >= "600000" OR e.spielnr <= "399999")
    AND e.klassenid != s.spielrecht1
    GROUP BY s.passnr
    ORDER BY passnummer
    ) t ON s.passnr = t.passnummer
    SET s.saisonspieldatum = "'.date("Y-m-d H:i").'"';
    //echo '<pre>'.$sql.'</pre><br />';

    $datentutup = rex_sql::factory();
    $datentutup->setQuery( $sql );

    // Anzahl der Spiele eintragen
    $sql = '
    UPDATE rex_950_spieler s
    INNER JOIN (
        SELECT 
            s.passnr AS passnummer, 
            COUNT(e.spielerid) AS spielezahl 
        FROM rex_950_spieler s 
        LEFT JOIN rex_950_spielergebnisse e ON s.passnr = e.spielerid OR s.passnr = e.ersatzspielernummer 
        WHERE s.spielrecht1 > 0 
        AND s.spielrecht2 = 0 
        AND e.saisonid = "'.$aktivesaison.'" 
        AND (e.spielnr >= "600000" OR e.spielnr <= "399999") 
        AND s.saisonspieldatum > "0" 
        AND e.klassenid != s.spielrecht1 
        GROUP BY s.passnr 
        HAVING spielezahl > '.$mindestspielzahl.'
    ) t ON s.passnr = t.passnummer
    SET s.spieleanderesrecht = t.spielezahl';
    //echo '<pre>'.$sql.'</pre><br />';

    $datentutup = rex_sql::factory();
    $datentutup->setQuery( $sql );

    echo '<div class="alert alert-success">Die Anzahl der Spiele für diese Saison wurde aktualisiert.</div>';
    $func = '';
}

// liveauswertung
if ($func == 'einsatzlistelive') {  
    // Aktive Saison auslesen
    $aktivesaison = 0;
    $sql = 'SELECT id, saison, aktivesaison FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja"';
    $datentutup = rex_sql::factory();
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl))
    {
        $aktivesaison = $datentutup->getValue("id", 0);                 
    }   

    // Tabelle
    $datenstart = 0;
    $datenlimit = 50;
    $mindestspielzahl = 5; // mehr als das

    echo '<div class="alert alert-success">Liste der Spieler mit den meisten Einsätzen außerhalb ihrer 1. Spielberechtigung.</div>';
    echo '<p><strong>Liste der Spieler, welche in der aktuellen Saison zu Spieleinsätzen außerhalb der Mannschaft kamen, für die eine 1. Spielberechtigung erteilt wurde und die keine 2. Spielberechtigung haben.</strong></p><br />';

                                        
    $listensql = '
SELECT s.passnr AS passnummer, s.spieler AS spielername, k.klasse AS spielrechtklasse, s.klub AS klub, s.spielrecht1 AS spielrechtid, s.sonderspielrecht AS sonderrecht, COUNT(e.spielerid) AS spielezahl 
FROM rex_950_spieler s LEFT JOIN rex_950_spielergebnisse e ON s.passnr = e.spielerid OR s.passnr = e.ersatzspielernummer 
LEFT JOIN rex_950_klasse k ON s.spielrecht1 = k.id 
WHERE s.spielrecht1 > 0 AND s.spielrecht2 = 0 AND e.saisonid = "'.$aktivesaison.'" 
AND ( e.spielnr >= "600000" OR e.spielnr <= "399999" ) 
AND s.saisonspieldatum > "0" 
AND e.klassenid != s.spielrecht1 GROUP BY s.passnr HAVING spielezahl > '.$mindestspielzahl.' ORDER BY spielezahl DESC    
    ';
    $list = rex_list::factory( $listensql, 150 );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("func", 'einsatzlistelive'); 	   
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    $thIcon = '';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    //$list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                  
    $list->setColumnLabel('passnummer', 'Passnummer');
    $list->setColumnLabel('spielername', 'Spieler');
    $list->setColumnLabel('spielrechtklasse', 'Spielrecht 1');
    $list->setColumnLabel('klub', 'Klub (aktuelles Spieljahr)');
    $list->setColumnLabel('sonderrecht', 'Sonderspielrecht');
    $list->setColumnLabel('spielezahl', 'Spiele außerhalb');
    $list->removeColumn('spielrechtid');
                                                       
  	$list->setColumnSortable('passnummer', 'asc');   
	$list->setColumnSortable('spielername', 'asc');  
	$list->setColumnSortable('spielrechtklasse', 'asc');  
	$list->setColumnSortable('klub', 'asc');  
	$list->setColumnSortable('sonderrecht', 'asc');  
	$list->setColumnSortable('spielezahl', 'asc');  
    
    $list->setColumnFormat('spielername', 'custom', function ($params) use($list) 
    {
        $tmp_val = $list->getValue('spielername'); 
        $tmp_pass = $list->getValue('passnummer'); 
        $retval = '<a href="'.rex_geturl( 464, 1, array("passnummer" => $tmp_pass ) ).'" target="_blank">'.$tmp_val.'</a>';
        return $retval;
    }); 

    $content = '<div id="lsnews">' . $list->get() . '</div>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', 'Eintrag');
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
    echo $content;

}

if ($func == 'einsatzliste') {  
    // Aktive Saison auslesen
    $aktivesaison = 0;
    $sql = 'SELECT id, saison, aktivesaison FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja"';
    $datentutup = rex_sql::factory();
    $datentutup->setQuery( $sql );
    $datenzahl = $datentutup->getRows();     
    if (!empty($datenzahl))
    {
        $aktivesaison = $datentutup->getValue("id", 0);                 
    }   

    // Tabelle

    echo '<div class="alert alert-success">Liste der Spieler mit den meisten Einsätzen außerhalb ihrer 1. Spielberechtigung.</div>';
    echo '<p><strong>Liste der Spieler, welche in der aktuellen Saison zu mehr als '.$mindestspielzahl.' Spieleinsätzen außerhalb der Mannschaft kamen, für die eine 1. Spielberechtigung erteilt wurde und die keine 2. Spielberechtigung haben.</strong></p><br />';

                                        
    $listensql = '
    SELECT s.passnr AS passnummer, s.spieler AS spielername, k.klasse AS spielrechtklasse, s.klub AS klub, s.spielrecht1 AS spielrechtid, s.sonderspielrecht AS sonderrecht, s.spieleanderesrecht AS spielezahl 
    FROM rex_950_spieler s 
    LEFT JOIN rex_950_klasse k ON s.spielrecht1 = k.id 
    WHERE s.saisonspieldatum > "0" 
    AND s.spieleanderesrecht > '.$mindestspielzahl.'
    ';                        
    if (empty($sort)) $listensql .= ' ORDER BY s.spieleanderesrecht DESC';
    else $listensql .= ' ORDER BY '.$sort.' '.$sorttype;
    //echo $listensql;
    $list = rex_list::factory( $listensql, 150 );
    $list->addTableAttribute('class', 'table-striped');              
    $list->addParam ("func", 'einsatzliste'); 	          
    $list->addParam ("sort", $sort); 	          
    $list->addParam ("sorttype", $sorttype); 	   
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">Keine Einträge vorhanden</div>');

    // icon column
    $thIcon = '';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    //$list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
                                  
    $list->setColumnLabel('passnummer', 'Passnummer');
    $list->setColumnLabel('spielername', 'Spieler');
    $list->setColumnLabel('spielrechtklasse', 'Spielrecht 1');
    
    $list->setColumnLabel('klub', 'Klub (aktuelles Spieljahr)');
	
    $list->setColumnLabel('sonderrecht', 'Sonderspielrecht');
    $list->setColumnLabel('spielezahl', 'Spiele außerhalb');
    $list->removeColumn('spielrechtid');
                                                       
  	$list->setColumnSortable('passnummer', 'asc');   
	$list->setColumnSortable('spielername', 'asc');  
	$list->setColumnSortable('spielrechtklasse', 'asc');  
    $list->setColumnSortable('klub', 'asc');  
	$list->setColumnSortable('sonderrecht', 'asc');  
	$list->setColumnSortable('spielezahl', 'asc');  
    
    $list->setColumnFormat('spielername', 'custom', function ($params) use($list) 
    {
        $tmp_val = $list->getValue('spielername'); 
        $tmp_pass = $list->getValue('passnummer'); 
        $retval = '<a href="'.rex_geturl( 464, 1, array("passnummer" => $tmp_pass ) ).'" target="_blank">'.$tmp_val.'</a>';
        return $retval;
    }); 

    $content = '<div id="lsnews">' . $list->get() . '</div>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', 'Eintrag');
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
    echo $content;

}

?>

<script>
        document.addEventListener("DOMContentLoaded", function() {
            const eingabefeld = document.getElementById("suche");
            if (eingabefeld !== null) eingabefeld.focus();
            else
            {
                const deteingabefeld = document.querySelector('.form-focus');
                if (deteingabefeld !== null) deteingabefeld.focus();
            }
        });
    </script>
<?php

$testid = rex_request('id', 'int');  
$testmsg = rex_request('_msg', 'string');  
if ((!empty($testmsg) AND (!empty($testid))) AND ($func == '') )
{
    echo '<div class="alert alert-info clearfix" style="">'.$testmsg.'</div>';
    

    include(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");         
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   
    
    $spielerid = rex_request('id', 'int');

    $mptutup = rex_sql::factory();
    $mptutup->setDebug(false);
    $sql = 'SELECT * FROM '.rex::getTablePrefix().'950_spieler WHERE 1=1 AND id= "'.$spielerid.'"';
    $mptutup->setQuery( $sql );
    $tmptreffer = $mptutup->getRows();  

    if (empty($tmptreffer)) {}
    else if ($tmptreffer > 1) {}
    else
    {
        $spielerid = $mptutup->getValue("id");
        ?>
        <table class="table table-striped">
        <?php
        
        echo '<tr><th style="width: auto;">Passnummer: </th><td style="width: 80%;">'.$mptutup->getValue("passnr").'</td></tr>';
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
            
            $tmpsonder = $mptutup->getValue("sonderspielrecht");
            if (!empty( $tmpsonder ))
            {
                echo '<tr class="bg-danger" style="background: #f8d7da; color: #721c24; "><th style="width: auto;">Sonderspielrecht: </th><td style="width: 80%;">'.$tmpsonder.'</td></tr>';
            }

            echo '</table><br />';
        }
    }
}

 
if ($func == '') {

    // Prüffunktion
    ?>      
    <div class="alert alert-info clearfix" style="background-color: #091E56; border-color: #091E56; ">
    <div class="pull-left"><p style="color: #fff;"></p></div>
          
    <div class="pull-right">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg">
    <input type="hidden" name="func" value="edit" />
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <input class="form-control" id="suche" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Passnr.">
    <div class="input-group-btn"><button type="submit" class="btn btn-success">Spielrecht prüfen</button></div></div></form>
    </div>
    
    </div>
    <div class="alert alert-info clearfix" style="background-color: #ddd; border-color: #ddd; ">
        <div class="d-block">
            <?php

            // letztes Datum ermitteln
            $manntutup = rex_sql::factory();
            $sql = 'SELECT DISTINCT(saisonspieldatum) AS letztesdatum FROM '.rex::getTablePrefix() .'950_spieler WHERE saisonspieldatum > "0" LIMIT 0,1';
            $manntutup->setQuery( $sql );
            $mannzahl = $manntutup->getRows();   
            if (!empty( $mannzahl )) 
            {
                $tmpletztesdatum = $manntutup->getValue("letztesdatum");
                ?>
                <div class="d-xxx" style="display: inline-block; padding-right: 1rem;">
                    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
                    <input type="hidden" name="func" value="einsatzliste" />
                    <button type="submit" class="btn btn-sm btn-success">Liste der Spieleinsätze (erstellt am: <?php echo date("d.m.Y H:i", strtotime( $tmpletztesdatum )); ?>)</button>
                    </form>
                </div>
                <?php
            }
            ?>
            <div class="d-xxx" style="display: inline-block; padding-right: 1rem;">
                <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
                <input type="hidden" name="func" value="einsatzzahl" />
                <button type="submit" class="btn btn-sm btn-danger">Liste neu erstellen (bitte Geduld...)</button>
                </form>
            </div>
            <?php
            $user = rex::getUser();
            if ($user->isAdmin()) 
            {
                ?>
                <div class="d-xxx" style="display: inline-block; padding-right: 1rem;">
                    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline">
                    <input type="hidden" name="func" value="einsatzlistelive" />
                    <button type="submit" class="btn btn-sm btn-info">Live</button>
                    </form>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
    // Ende Prüffunktion
    
}
elseif ($func == 'edit') {

    include(rex_path::src()."/addons/ergebnisdienst/lib/led.functions.php");         
    include(rex_path::src()."/addons/ergebnisdienst/lib/led.config.php");   
    
    $passnr = led_passnummer(trim(rex_request('suche', 'string')));
    $spielerid = rex_request('id', 'int');
    if (!empty($passnr)) 
    {
        $spielerid = 0;
        $mptutup = rex_sql::factory();
        $mptutup->setDebug(false);
        $sql = 'SELECT * FROM '.rex::getTablePrefix().'950_spieler WHERE 1=1 AND passnr= "'.$passnr.'"';
        $mptutup->setQuery( $sql );
        $tmptreffer = $mptutup->getRows();  
    }
    else
    {
        $mptutup = rex_sql::factory();
        $mptutup->setDebug(false);
        $sql = 'SELECT * FROM '.rex::getTablePrefix().'950_spieler WHERE 1=1 AND id= "'.$spielerid.'"';
        $mptutup->setQuery( $sql );
        $tmptreffer = $mptutup->getRows();  
    }
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


    // aktive Saison ermitteln
    $manntutup = rex_sql::factory();
    $sql = 'SELECT id, saisonjahr FROM '.rex::getTablePrefix() .'950_saison WHERE aktivesaison = "ja" LIMIT 0,1';
    $manntutup->setQuery( $sql );
    $mannzahl = $manntutup->getRows();   
    if (!empty( $mannzahl )) $GLOBALS['aktivesaison_jahrende'] = $manntutup->getValue("saisonjahr");
    else $GLOBALS['aktivesaison_jahrende'] = date("Y");
    //echo $aktivesaison_jahrende;
    
    $tmp_val = $mptutup->getValue('stichtagsgeburtsjahr'); 
    $retval = led_altersklasse( $tmp_val, $GLOBALS['aktivesaison_jahrende'] );
        
        
        echo '<tr><th style="width: auto;">Altersklasse: </th><td style="width: 80%;">'.$retval.'</td></tr>';

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
            
            $tmpsonder = $mptutup->getValue("sonderspielrecht");
            if (!empty( $tmpsonder ))
            {
                echo '<tr class="bg-danger" style="background: #f8d7da; color: #721c24; "><th style="width: auto;">Sonderspielrecht: </th><td style="width: 80%;">'.$tmpsonder.'</td></tr>';
            }
            
            echo '<tr><th style="width: auto;">Klub (aktuelles Spieljahr): </th><td style="width: 80%;">'.$mptutup->getValue("klub").'</td></tr>';

            echo '</table><br />';
            ?>
            <style>
                .rex-form-panel-footer button { display: none !important; }
                .rex-form-panel-footer button.btn-save { display: inline-block !important; }
            </style>
            <?php
            // Hier kommt das Edit-Formular hin


            // hier die Edit-Funktion
            $id = $spielerid;
                    

            $fieldset = $func == 'edit' ? 'Bearbeiten' : 'Hinzufügen';
            $form = rex_form::factory(rex::getTablePrefix() . '950_spieler', '', 'id=' . $id);        
            
            
            $field = $form->addTextField('spielrecht1');
            $field->setLabel("1. Spielberechtigung (ID)");                                           
            $field->setAttribute('class','form-control form-focus');                                         
            //$select->setAttribute('required','required');    
            
            //$field = $form->addTextField('spielrecht2');
            //$field->setLabel("2. Spielberechtigung (ID)");   
            
            $field = $form->addTextField('mannschaftid');
            $field->setLabel("Mannschaft (ID)"); 

            $field = $form->addTextField('spielrecht2');
            $field->setLabel("2. Spielberechtigung (ID)");           
 
            $field = $form->addTextField('sonderspielrecht');
            $field->setLabel("Sonderspielrecht");    


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


            // Ende Edit-Formular
        }
        else 
        {
            echo '<tr><th style="width: auto;">Spielrecht: </th><td style="width: 80%; color: red;">Mitgliedschaft im  TKV ist nicht hinterlegt. Bitte prüfen.</td></tr>';
            echo '<tr><th style="width: auto;">Klub (aktuelles Spieljahr): </th><td style="width: 80%;">'.$mptutup->getValue("klub").'</td></tr>';
            echo '</table><br />';
            
        }
        

        ?>
        <div style="padding:: 1rem 0;">
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
    

}
?>
