<?php

$func = rex_request('func', 'string');    
$suche = rex_request('suche', 'string');    
$showpwd = rex_request('showpwd', 'string');
$sort = rex_request('sort', 'string');   
$sorttype = rex_request('sorttype', 'string');   
if (!in_array( $sorttype, array("asc", "desc") )) $sorttype = "";

if ($func == 'delete') {
    $id = (rex_request('id', 'int'));
    $sql = rex_sql::factory();

    // $sql->setDebug();
    $sql->setTable( rex::getTablePrefix() .'950_nutzer');
    $sql->setWhere('id = ' . $id);

    if ($sql->delete())
    {
        echo '<div class="alert alert-success">Eintrag entfernt.</div>';
    }

    $func = '';
}

if ($func == '') {


    
    // Suchfunktion
    ?>      
    <div class="alert alert-info clearfix" style="background-color: #01B4DD; border-color: #01B4DD; ">
    <div class="pull-left"><form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-lg"><input type="hidden" name="func" value="add" /><div class="input-group-btn"><button type="submit" class="btn btn-primary">+ neues Login anlegen</button></div></div></form></div>
          
    <div class="pull-right">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><div class="input-group input-group-xs">
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <input class="form-control" name="suche" value="<?php if (!empty($suche)) echo $suche; ?>" type="text" placeholder="Name oder EMail"><div class="input-group-btn"><button type="submit" class="btn btn-primary btn-xs">Suchen</button></div></div></form>
    </div>   
    
    <div class="pull-right" style="margin-right: 10px; ">
    <form action="<?php print rex_url::currentBackendPage( ); ?>" method="post" class="form-inline"><input type="hidden" name="showpwd" value="showpwd" /><div class="input-group input-group-xs">
    <span class="input-group-addon" style="width:0px; padding-left:0px; padding-right:0px; border:none;"></span>
    <div class="input-group-btn"><button type="submit" class="btn btn-danger btn-xs">Passwörter anzeigen</button></div></div></form>
    </div>
    
    </div>
    <?php
    // Ende Suchfunktion
                                        
    $listensql = 'select id, name, email, gruppe, pwd from '. rex::getTablePrefix() .'950_nutzer WHERE 1=1 ';
    if (!empty($suche)) $listensql .= " AND (name LIKE '%".$suche."%' OR email LIKE '%".$suche."%') ";
    
    if ((empty($sort)) OR (empty($sorttype))) $listensql .= ' ORDER BY name ASC, email ASC';
    else $listensql .= " ORDER BY ".$sort." ".$sorttype." ";
    
    if($showpwd == "showpwd")  $list = rex_list::factory( $listensql, 10000 );
    else $list = rex_list::factory( $listensql );
    
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
    $list->setColumnLabel('name', 'Name');
    $list->setColumnParams('name', ['func' => 'edit', 'id' => '###id###']);  
    $list->setColumnLabel('email', 'EMail/Login');   
    
    $list->setColumnLabel('gruppe', 'Nutzergruppe');

    if($showpwd == "showpwd")  $list->setColumnLabel('pwd', 'Passwort');
    else $list->removeColumn('pwd');
    
  	$list->setColumnSortable('name', 'asc');  
  	$list->setColumnSortable('email', 'asc');  
  	$list->setColumnSortable('gruppe', 'asc');  
    
    $list->setColumnFormat('gruppe', 'custom', function ($params) use($list) 
    {
        $tmp_val = $list->getValue('gruppe'); 
        if ($tmp_val == 0) return 'normal';
        else if ($tmp_val == "ml") return 'Mannschaftsleiter';
        else if ($tmp_val == "sl") return 'Staffelleiter';
        else if ($tmp_val == "admin") return 'Administrator';
        else return '-';
        //else return '<span class="badge badge-danger" style="background: #28a745; border-color: #28a745;">'.$tmp_val.'</span>';
    });  

    if($showpwd == "showpwd") {}
    else
    {
    $delete = 'deleteCol';
    $list->addColumn($delete, '<i class="rex-icon rex-icon-delete"></i> '.'Löschen', -1, ['<th></th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($delete, ['id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute($delete, 'data-confirm', rex_i18n::msg('delete') . ' ?');   
    }
    
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
    $form = rex_form::factory(rex::getTablePrefix() . '950_nutzer', '', 'id=' . $id);        
    
    $field = $form->addTextField('email');
    $field->setLabel('EMail/Login');
    $field->getValidator()->add('notEmpty', 'Bitte die EMail ausfüllen!');        
    
    $field = $form->addTextField('name');
    $field->setLabel('Name');                    
            

    $field = $form->addTextField('pwd');             
    $field->setLabel("Passwort");                   
    $field->setAttribute('required','required');   
    
    $field =$form->addSelectField('gruppe');
    //$field->setAttribute('multiple','multiple');
    $field->setLabel("Nutzergruppe");
    $select =$field->getSelect();
    $select->setSize(1);
    $select->addOption("normal",0);
    $select->addOption("Mannschaftsleiter", 'ml' );    
    $select->addOption("Staffelleiter", 'sl' );    
    $select->addOption("Administrator", 'admin' );    
    $select->setAttribute('style','width:50%');

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
