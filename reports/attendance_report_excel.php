<?php
require_once __DIR__ . '/../config/auth.php';

$role = $_SESSION['role'];
$personnel_id = $_SESSION['personnel_id'] ?? null;

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report.xls");

echo "Date\tName\tTime In\tTime Out\tStatus\n";

$date = $_GET['date'] ?? '';

$sql = "
SELECT p.fullname, a.date, a.time_in, a.time_out, a.status
FROM attendance a
JOIN personnel p ON p.id = a.personnel_id
WHERE 1=1
";

if ($role === 'personnel') {
    $sql .= " AND a.personnel_id = '$personnel_id'";
}

if (!empty($date)) {
    $sql .= " AND a.date = '$date'";
}

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {

    $time_in = $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-';
    $time_out = $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-';

    echo "{$row['date']}\t{$row['fullname']}\t{$time_in}\t{$time_out}\t{$row['status']}\n";
}