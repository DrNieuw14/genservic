<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=inventory_report.csv');

$output = fopen("php://output", "w");

// Header row
fputcsv($output, ['ID','Personnel','Date','Status','Total Items','Total Quantity']);

// Query
$sql = "
SELECT 
    r.id,
    p.fullname,
    r.request_date,
    r.status,
    COUNT(ri.id),
    SUM(ri.quantity)
FROM inventory_requests r
JOIN personnel p ON r.personnel_id = p.id
JOIN inventory_request_items ri ON r.id = ri.request_id
GROUP BY r.id
ORDER BY r.request_date DESC
";

$result = $conn->query($sql);

// Data rows
while($row = $result->fetch_row()){
    fputcsv($output, $row);
}

fclose($output);
exit;