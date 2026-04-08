<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

require_login();

$personnel_id = $_SESSION['personnel_id'] ?? 1;

// CTO SUMMARY
$cto = $conn->query("
    SELECT 
        SUM(equivalent_days) AS total_days,
        SUM(used_hours) AS used_hours,
        SEC_TO_TIME(SUM(TIME_TO_SEC(total_hours))) AS total_hours
    FROM cto_summary
    WHERE personnel_id='$personnel_id' AND status='Approved'
")->fetch_assoc();

$total_days = $cto['total_days'] ?? 0;
$used_hours = $cto['used_hours'] ?? 0;
$total_hours = $cto['total_hours'] ?? '00:00:00';

$balance_days = max(0, $total_days - ($used_hours / 8));
?>

<!DOCTYPE html>
<html>
<head>
    <title>CTO Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>📊 CTO Dashboard</h3>

<div class="card mt-3">
    <div class="card-body">
        <h5>CTO Summary</h5>
        <p>Earned: <?= number_format($total_days,2) ?> days</p>
        <p>Used: <?= number_format($used_hours / 8,2) ?> days</p>
        <p><strong>Balance: <?= number_format($balance_days,2) ?> days</strong></p>
        <p>Total Hours: <?= $total_hours ?></p>
    </div>
</div>

<a href="../../dashboard.php" class="btn btn-secondary mt-3">Back</a>

</body>
</html>