<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/layout.php';

require_login();

// Total Areas
$total_areas = $conn->query("SELECT COUNT(*) as total FROM areas")->fetch_assoc()['total'];

// Total Area Assignments
$total_assignments = $conn->query("SELECT COUNT(*) as total FROM personnel_areas")->fetch_assoc()['total'];

// Attendance Today
$today = date('Y-m-d');
$total_attendance_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE DATE(time_in)='$today'
")->fetch_assoc()['total'];

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

$trendDates = [];
$trendTotals = [];

for ($i = 6; $i >= 0; $i--) {

    $date = date('Y-m-d', strtotime("-$i days"));
    $trendDates[] = $date;

    $sql = "SELECT COUNT(*) as total FROM attendance WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    $trendTotals[] = (int)$result['total'];

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GenServis</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row">
        <?php render_sidebar($_SESSION['role']); ?>
        <main class="col-lg-10 col-md-9 p-4">
         <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Dashboard Overview</h3>

            <div class="text-end">
                 <div>
                    Logged in as: 
                    <strong><?= htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    (<?= htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?>)
                </div>

            <span class="text-muted">
                Today: <?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
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
                                <div class="col-sm-6 col-xl-3">
                    <div class="card summary-card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Areas</p>
                            <h2 class="mb-0 accent-text"><?= $total_areas; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card summary-card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Area Assignments</p>
                            <h2 class="mb-0 accent-text"><?= $total_assignments; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header accent-bg text-dark">
                    Attendance Trend (Last 7 days)
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="90"></canvas>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="<?= htmlspecialchars(app_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($trendDates); ?>,
        datasets: [{
            label: 'Present Count',
            data: <?= json_encode($trendTotals); ?>,
            borderColor: '#006633',
            backgroundColor: 'rgba(0,102,51,0.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0
            }
        }
    }
}
});
</script>
</body>
</html>