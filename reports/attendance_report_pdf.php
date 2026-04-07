<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/auth.php';

$role = $_SESSION['role'];
$personnel_id = $_SESSION['personnel_id'] ?? null;

use Dompdf\Dompdf;

$date = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "
SELECT p.fullname, a.date, a.time_in, a.time_out, a.status, a.undertime, a.overtime
FROM attendance a
JOIN personnel p ON p.id = a.personnel_id
WHERE 1=1
";

if ($role === 'personnel') {
    $sql .= " AND a.personnel_id = '$personnel_id'";
}

if (!empty($search)) {
    $sql .= " AND p.fullname LIKE '%$search%'";
}

if (!empty($date)) {
    $sql .= " AND a.date = '$date'";
}

$params = [];
$types = '';

if (!empty($date)) {
    $sql .= " AND a.date = ?";
    $params[] = $date;
    $types .= 's';
}

$stmt = $conn->prepare($sql);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$html = "
<h2 style='text-align:center;'>Attendance Report</h2>
<table border='1' width='100%' cellspacing='0' cellpadding='5'>
<tr>
<th>Date</th>
<th>Name</th>
<th>Time In</th>
<th>Time Out</th>
<th>Status</th>
</tr>
";

while ($row = $result->fetch_assoc()) {

    $time_in = $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-';
    $time_out = $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-';

    $html .= "
    <tr>
    <td>{$row['date']}</td>
    <td>{$row['fullname']}</td>
    <td>{$time_in}</td>
    <td>{$time_out}</td>
    <td>{$row['status']}</td>
    </tr>
    ";
}

$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("attendance_report.pdf", ["Attachment" => true]);