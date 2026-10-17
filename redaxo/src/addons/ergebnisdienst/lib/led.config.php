<?php
    // Allgemeine Einstellungen und Funktionen Ergebnisdienst 
    // Version TKV      
    
    $istdkbc = false;     
    $mitpokal = true;    
     
    $realmailsender = 'support@tkv-kegeln.de';  // EMail des angezeigten Absenders
    
    $mailrecipient = 'support@tkv-kegeln.de';   // EMail des Empfängers für Informationen bei Mehrfach-Spiel-Upload und für Spielberichte, wenn kein anderer Liga-Verantwortlicher eingetragen ist    
    
    $mailrecipientspieler = 'support@tkv-kegeln.de';   // EMail des Empfängers für Eintragungen neuer Spieler
                                                                              
    $mailrecipientspielverlegung = 'gs@tkv-kegeln.de';   // EMail des Empfängers für kostenpflichtige Spielverlegungen

    $auswertungsmodulid = 26;   // Modul ID für den Link nach Upload
    
    // Änderung 2022a
    $specialansicht = false;
    $specialspielstring = "60";
    $turniermannschaftsid = 440;

    // Änderung 2024
    $loeschpasswort = "zipp"; // Passwort zum Löschen von Spielern, Mannschaften etc., wenn leer, erfolgt keine Sicherheitsabfrage
    $erlaubtespielberichtgroesse = 254800; //maximale Dateigröße für Spielberichte beim Upload in Bytes
    
   
?>