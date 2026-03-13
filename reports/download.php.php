<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

require_role(['admin', 'supervisor']);

$type = clean_input($_GET['type'] ?? 'excel');
$dateFilter = clean_input($_GET['date'] ?? '');
$nameSearch = clean_input($_GET['search'] ?? '');

$sql = "
SELECT p.fullname, a.date, a.time_in, a.time_out, a.status
FROM attendance a
JOIN personnel p ON p.id = a.user_id
WHERE 1=1
";
$params = [];
$types = '';
if ($dateFilter !== '') {
    $sql .= ' AND a.date = ?';
    $params[] = $dateFilter;
    $types .= 's';
}
if ($nameSearch !== '') {
    $sql .= ' AND p.fullname LIKE ?';
    $params[] = '%' . $nameSearch . '%';
    $types .= 's';
}
$sql .= ' ORDER BY a.date DESC, p.fullname ASC';
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Ymd_His') . '.xls"');
    echo "Personnel\tDate\tTime In\tTime Out\tStatus\n";
    foreach ($rows as $row) {
        echo implode("\t", [
            $row['fullname'],
            $row['date'],
            $row['time_in'] ?: '-',
            $row['time_out'] ?: '-',
            $row['status'],
        ]) . "\n";
    }
    exit();
}

if ($type === 'pdf') {
    $lines = ["Attendance Report", "Generated: " . date('Y-m-d H:i:s'), ''];
    foreach ($rows as $row) {
        $lines[] = sprintf(
            '%s | %s | IN: %s | OUT: %s | %s',
            $row['fullname'],
            $row['date'],
            $row['time_in'] ?: '-',
            $row['time_out'] ?: '-',
            $row['status']
        );
    }

    $escape = static function (string $text): string {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    };

    $content = "BT\n/F1 10 Tf\n50 790 Td\n";
    $first = true;
    foreach ($lines as $line) {
        if (!$first) {
            $content .= "0 -14 Td\n";
        }
        $content .= '(' . $escape($line) . ") Tj\n";
        $first = false;
    }
    $content .= "ET";

    $objects = [];
    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj";
    $objects[] = "4 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "\nendstream endobj";
    $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $obj) {
        $offsets[] = strlen($pdf);
        $pdf .= $obj . "\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= 'xref\n0 ' . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Ymd_His') . '.pdf"');
    echo $pdf;
    exit();
}

http_response_code(400);
echo 'Invalid report type.';