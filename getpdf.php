<?php
    // PDF-Export des Ergebnisdienstes.
    //
    // Aufruf ausschliesslich per POST (Formular-Button in led.ergebnisse.modul.php).
    // Hintergrund: Als einfacher GET-Link wurde getpdf.php von Crawlern/Bots in
    // allen Parameter-Kombinationen abgeklappert, was jeweils ein teures Rendering
    // ausloeste. POST-Aktionen werden von Crawlern nicht verfolgt.

    // --- 1. Nur POST zulassen -------------------------------------------------
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        header('Content-Type: text/plain; charset=utf-8', true, 405);
        echo 'Method Not Allowed';
        exit;
    }

    // --- 2. Parameter validieren (Whitelist) ----------------------------------
    // article_id ist Pflicht und muss numerisch sein.
    $article_id = isset($_POST['article_id']) ? (int) $_POST['article_id'] : 0;
    if ($article_id <= 0) {
        header('Content-Type: text/plain; charset=utf-8', true, 400);
        echo 'Bad Request';
        exit;
    }

    // Numerische Filter werden auf int gecastet, die restlichen auf kurze Strings
    // begrenzt. Nur diese Schluessel werden an die interne index.php weitergegeben.
    $params = array(
        'article_id' => $article_id,
        'tabelle'    => isset($_POST['tabelle'])    ? (int) $_POST['tabelle']    : 0,
        'tag'        => isset($_POST['tag'])        ? (int) $_POST['tag']        : 0,
        'mannschaft' => isset($_POST['mannschaft']) ? (int) $_POST['mannschaft'] : 0,
        'plan'       => isset($_POST['plan'])       ? (int) $_POST['plan']       : 0,
        'einzel'     => isset($_POST['einzel'])     ? (int) $_POST['einzel']     : 0,
        'spiel'      => isset($_POST['spiel'])      ? substr((string) $_POST['spiel'], 0, 32) : '',
        'sort'       => isset($_POST['sort'])       ? substr((string) $_POST['sort'], 0, 32) : '',
        'pdf'        => 'pdf',
    );

    // --- 3. Ziel-URL (interne Ansicht) zusammenbauen --------------------------
    $scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'];
    $zielurl = $scheme . '://' . $host . '/index.php?' . http_build_query($params);

    // --- 4. Dompdf laden ------------------------------------------------------
    require_once ( "./redaxo/src/addons/ergebnisdienst/vendor/dompdf/autoload.inc.php");

    use Dompdf\Dompdf;
    use Dompdf\Options;

    $pdf_options = new Options();
    $pdf_options->setDpi(100);
    $pdf_options->set('defaultFont', 'Helvetica');
    $art_pdf_name = 'ergebnisdienst';
    $dompdf = new Dompdf($pdf_options);

    // --- 5. HTML-Parser laden -------------------------------------------------
    require_once ( "./redaxo/src/addons/ergebnisdienst/vendor/simplehtmldom/HtmlWeb.php");
    use simplehtmldom\HtmlWeb;

    // Ziel-URL laden
    $client = new HtmlWeb();
    $html   = $client->load( $zielurl );

    $cssfirst = '<style> 
  body,p, th, td { font-size: 0.71em; box-sizing: border-box; } 
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
          
          </style>';

    // Logo als Base64 einbetten (falls vorhanden)
    $base64 = '';
    $path   = './media/logo.png';
    if (is_file($path)) {
        $type   = pathinfo($path, PATHINFO_EXTENSION);
        $data   = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    $meinhtmlcode  = '<table class="table kopf"><tr><td class="logospalte"><img class="logo" src="'.$base64.'" alt="" /></td><td class="headspalte"><div class="title">THÜRINGER KEGLER-VERBAND e.V.</div><div class="subtitle">Kegeln - damit die Freizeit Freude macht!</div></td></tr></table><br /><br />';
    $meinhtmlcode .= $cssfirst;

    $realcontent   = $html->find('#echtergebnisliste', 0);
    $meinhtmlcode .= $realcontent;

    header('Content-Type: application/pdf');
    $dompdf->loadHtml( $meinhtmlcode );
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($art_pdf_name, array('Attachment' => true)); // true = Download erzwingen

    die();
?>
