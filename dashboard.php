<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

require_login();

$today = date('Y-m-d');

$summarySql = "
SELECT
    (SELECT COUNT(*) FROM personnel) AS total_personnel,
    (SELECT COUNT(*) FROM attendance WHERE date = ?) AS present_today,
    (SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'Late') AS late_today
";
$summaryStmt = $conn->prepare($summarySql);
$summaryStmt->bind_param('ss', $today, $today);
$summaryStmt->execute();
$summary = $summaryStmt->get_result()->fetch_assoc();
$summaryStmt->close();

$totalPersonnel = (int) ($summary['total_personnel'] ?? 0);
$presentToday = (int) ($summary['present_today'] ?? 0);
$lateToday = (int) ($summary['late_today'] ?? 0);
$absentToday = max(0, $totalPersonnel - $presentToday);

$trendSql = "
SELECT a.date, COUNT(*) AS total_present
FROM attendance a
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
GROUP BY a.date
ORDER BY a.date ASC
";
$trendResult = $conn->query($trendSql);
$trendDates = [];
$trendTotals = [];
while ($row = $trendResult->fetch_assoc()) {
    $trendDates[] = $row['date'];
    $trendTotals[] = (int) $row['total_present'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GenServis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row">
        <?php render_sidebar($_SESSION['role']); ?>
        <main class="col-lg-10 col-md-9 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Dashboard Overview</h3>
                <span class="text-muted">Today: <?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card summary-card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Personnel</p>
                            <h2 class="mb-0"><?= $totalPersonnel; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card summary-card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Present Today</p>
                            <h2 class="text-success mb-0"><?= $presentToday; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card summary-card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Absent Today</p>
                            <h2 class="text-danger mb-0"><?= $absentToday; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card summary-card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Late Personnel</p>
                            <h2 class="text-warning mb-0"><?= $lateToday; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    Attendance Trend (Last 7 days)
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="90"></canvas>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="/assets/js/app.js"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($trendDates); ?>,
        datasets: [{
            label: 'Present Count',
            data: <?= json_encode($trendTotals); ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>

require __DIR__ . '/admin/dashboard.php';
