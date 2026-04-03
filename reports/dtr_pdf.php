<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// GET PARAMETERS
$personnel_id = $_GET['personnel_id'] ?? '';
$month = $_GET['month'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$_GET['pdf'] = true;

// REUSE YOUR DTR PAGE
$logo_path = __DIR__ . '/../assets/logo.png';
$logo_base64 = '';

if(file_exists($logo_path)){
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}



ob_start();
?>

<?php
include 'dtr_report.php';
$html = ob_get_clean();

$html = '<html><body>' . $html . '</body></html>';

// LOAD PDF
$dompdf->loadHtml($html);

// SET PAPER
$dompdf->setPaper('A4', 'portrait');

// RENDER
$dompdf->render();

// DOWNLOAD
$dompdf->stream("DTR_Report.pdf", ["Attachment" => false]);