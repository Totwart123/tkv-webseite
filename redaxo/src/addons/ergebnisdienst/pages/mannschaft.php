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
        $sql->setTable( rex::getTablePrefix() .'950_mannschaft');
        $sql->setWhere('id = ' . $id);

        if ($sql->delete())
        {
            echo '<div class="alert alert-success">Eintrag entfernt.</div>';
        }

        $func = '';
    }
}

if ($func == '') {


    
    // Suchfunktion
    ?>      
    <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">
    <div class="pull-left"><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="add" /><div class="input-group-btn"><button type="submit" class="btn btn-primary">+ neue Mannschaft anlegen</button></div></div></form></div>
          
    <div class="pull-right">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs">
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <input class="form-control" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Suchbegriff"><div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Suchen</button></div></div></form>
    </div>
    
    </div>
    <?php
    // Ende Suchfunktion
                                        
    $listensql = 'select id, mannschaft from '. rex::getTablePrefix() .'950_mannschaft WHERE 1=1 ';
    if (!empty($suche)) $listensql .= " AND (mannschaft LIKE '%".$suche."%') ";
    $listensql .= ' ORDER BY mannschaft ASC';
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
    $list->setColumnLabel('mannschaft', 'Mannschaft');
    $list->setColumnParams('mannschaft', ['func' => 'edit', 'id' => '###id###']);  
                                                       
  	$list->setColumnSortable('mannschaft', 'asc');  

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
    $form = rex_form::factory(rex::getTablePrefix() . '950_mannschaft', '', 'id=' . $id);        
    
    
    $field = $form->addTextField('mannschaft');
    $field->setLabel("Mannschaft");        
    $field->setAttribute('required','required');      

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
