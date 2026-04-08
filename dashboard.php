<?php
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/auth.php';
    require_once __DIR__ . '/config/layout.php';

    require_login();

    // ======================
    // INVENTORY DASHBOARD DATA
    // ======================

    // TOTAL REQUESTS
    $total_requests = $conn->query("
        SELECT COUNT(*) as total 
        FROM inventory_requests
    ")->fetch_assoc()['total'] ?? 0;

    // PENDING
    $pending_requests = $conn->query("
        SELECT COUNT(*) as total 
        FROM inventory_requests 
        WHERE status = 'pending'
    ")->fetch_assoc()['total'] ?? 0;

    // APPROVED
    $approved_requests = $conn->query("
        SELECT COUNT(*) as total 
        FROM inventory_requests 
        WHERE status = 'approved'
    ")->fetch_assoc()['total'] ?? 0;

    // REJECTED
    $rejected_requests = $conn->query("
        SELECT COUNT(*) as total 
        FROM inventory_requests 
        WHERE status = 'rejected'
    ")->fetch_assoc()['total'] ?? 0;

    // LOW STOCK
    $low_stock_count = $conn->query("
        SELECT COUNT(*) as total 
        FROM inventory_items 
        WHERE quantity <= min_stock
    ")->fetch_assoc()['total'] ?? 0;

    // LOW STOCK ITEMS LIST
    $low_stock_items = $conn->query("
        SELECT i.item_name, i.quantity, i.min_stock, u.unit_name
        FROM inventory_items i
        LEFT JOIN inventory_units u ON i.unit_id = u.id
        WHERE i.quantity <= i.min_stock
        ORDER BY i.quantity ASC
    ");

    // RECENT REQUESTS
    $recent_requests = $conn->query("
        SELECT r.id, r.request_date, r.status, p.fullname
        FROM inventory_requests r
        LEFT JOIN personnel p ON r.personnel_id = p.id
        ORDER BY r.request_date DESC
        LIMIT 5
    ");

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

            $userFullName = "Unknown";

            if (isset($_SESSION['user_id'])) {
                $stmt = $conn->prepare("
                    SELECT fullname 
                    FROM users 
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($row = $res->fetch_assoc()) {
                    $userFullName = $row['fullname'];
                }
                $stmt->close();
                }

    $personnel_id = $_SESSION['personnel_id'] ?? 1;

    // ======================
    // SUPERVISOR LEAVE NOTIFICATION
    // ======================
    $leaveNotif = 0;

    if ($_SESSION['role'] == 'supervisor') {
        $leaveNotif = $conn->query("
            SELECT COUNT(*) as total 
            FROM leave_requests 
            WHERE status = 'Pending'
        ")->fetch_assoc()['total'] ?? 0;
    }

    // ======================
    // CTO CHART DATA
    // ======================

    // ======================
    // CTO USAGE vs EARNED DATA
    // ======================

    $usageMonths = [];
    $earnedHours = [];
    $usedHours = [];

    $query = "
    SELECT 
        m.month,
        COALESCE(e.earned, 0) AS earned,
        COALESCE(u.used, 0) AS used
        FROM (
        SELECT DATE_FORMAT(date, '%Y-%m') AS month FROM attendance
        UNION
        SELECT DATE_FORMAT(created_at, '%Y-%m') FROM leave_requests
    ) m
    LEFT JOIN (
        SELECT 
        DATE_FORMAT(date, '%Y-%m') AS month,
        SUM(TIME_TO_SEC(extra_hours))/3600 AS earned
        FROM attendance
        WHERE personnel_id = '$personnel_id'
        GROUP BY month
    ) e ON m.month = e.month
    LEFT JOIN (
        SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        SUM(TIME_TO_SEC(equivalent_hours))/3600 AS used
        FROM leave_requests
        WHERE personnel_id = '$personnel_id' AND status='Approved'
        GROUP BY month
    ) u ON m.month = u.month
    ORDER BY m.month ASC
    ";

    $result = $conn->query($query);

    while($row = $result->fetch_assoc()){
        $usageMonths[] = $row['month'];
        $earnedHours[] = round($row['earned'],2);
        $usedHours[] = round($row['used'],2);
    }

    // prevent empty chart
    if(empty($usageMonths)){
        $usageMonths = ['No Data'];
        $earnedHours = [0];
        $usedHours = [0];
    }

    $ctoMonths = [];
    $ctoHours = [];

    $ctoChartQuery = "
    SELECT 
        month,
        TIME_TO_SEC(total_hours)/3600 AS hours
    FROM cto_summary
    WHERE personnel_id = '$personnel_id'
    ORDER BY month ASC
    ";

    $result = $conn->query($ctoChartQuery);

    while($row = $result->fetch_assoc()){
        $ctoMonths[] = $row['month'];
        $ctoHours[] = round($row['hours'],2);
    }

    if(empty($ctoMonths)){
        $ctoMonths = ['No Data'];
        $ctoHours = [0];
    }


    // ======================
    // CTO DASHBOARD DATA
    // ======================


    // TOTAL EXTRA HOURS (MONTH)
    $cto_extra = $conn->query("
        SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(extra_hours))) AS total_extra
        FROM attendance
        WHERE personnel_id = '$personnel_id'
        AND MONTH(date) = MONTH(CURRENT_DATE())
    ")->fetch_assoc()['total_extra'] ?? '00:00:00';

    // CTO BALANCE
    $cto_data = $conn->query("
        SELECT 
        SUM(equivalent_days) AS total_days,
        SEC_TO_TIME(SUM(TIME_TO_SEC(total_hours))) AS total_hours
        FROM cto_summary
        WHERE personnel_id='$personnel_id' AND status='Approved'
    ")->fetch_assoc();

    $cto_days = $cto_data['total_days'] ?? 0;
    $cto_hours = $cto_data['total_hours'] ?? '00:00:00';

    // PENDING REQUESTS
    $cto_pending = $conn->query("
        SELECT COUNT(*) as total
        FROM leave_requests
        WHERE personnel_id='$personnel_id' AND status='Pending'
    ")->fetch_assoc()['total'] ?? 0;

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
                                    <h2 class="mb-0 fw-bold">Dashboard Overview</h2>

                                    <?php if($_SESSION['role'] == 'supervisor' && $leaveNotif > 0): ?>
                                        <div class="alert alert-warning mt-3">
                                            🔔 You have <strong><?= $leaveNotif ?></strong> pending leave request(s)
                                            <a href="modules/leave/leave.php" class="btn btn-dark btn-sm ms-2">
                                                Review Now
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-end">
                                        <div>
                                            Logged in as:                             
                                            <strong><?= htmlspecialchars($userFullName, ENT_QUOTES, 'UTF-8'); ?></strong>
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
                                        <div class="card-body p-4">
                                            <p class="text-muted mb-1 fs-6">Total Personnel</p>
                                            <h1 class="mb-0 fw-bold"><?= $totalPersonnel; ?></h1>
                                        </div>
                                    </div>
                                </div>
                            
                            <div class="col-sm-6 col-xl-3">
                            <div class="card summary-card border-0 shadow-sm">
                                <div class="card-body p-4">
                                        <p class="text-muted mb-1">Present Today</p>
                                        <h2 class="text-success mb-0"><?= $presentToday; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card summary-card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <p class="text-muted mb-1">Absent Today</p>
                                        <h2 class="text-danger mb-0"><?= $absentToday; ?></h2>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-xl-3">
                                <div class="card summary-card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <p class="text-muted mb-1">Late Personnel</p>
                                        <h2 class="text-warning mb-0"><?= $lateToday; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================= -->
                            <!-- CTO DASHBOARD -->
                            <!-- ========================= -->

                            <div class="col-12 mt-4">
                                <h5 class="mb-3">⏱️ CTO Overview</h5>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-info text-white">
                                    <div class="card-body p-4">
                                        <p class="mb-1">⏱️ Extra Hours</p>
                                        <h2><?= $cto_extra ?></h2>
                                        <small>This Month</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-success text-white">
                                    <div class="card-body p-4">
                                        <p class="mb-1">📅 CTO Credits</p>
                                        <h2><?= number_format($cto_days,2) ?> days</h2>
                                        <small><?= $cto_hours ?> hrs</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-warning text-dark">
                                    <div class="card-body p-4">
                                        <p class="mb-1">📄 Pending Leave</p>
                                        <h2><?= $cto_pending ?></h2>
                                        <small>Awaiting approval</small>
                                    </div>
                                </div>
                            </div>

                            <?php if($_SESSION['role'] == 'supervisor'): ?>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="card border-0 shadow-sm bg-danger text-white">
                                        <div class="card-body p-4">
                                            <p class="mb-1">🔔 Leave Requests</p>
                                            <h2><?= $leaveNotif ?></h2>
                                            <small>Pending approval</small><br>
                                            <a href="modules/leave/leave.php" class="btn btn-light btn-sm mt-2">
                                                Approve Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <div class="col-12">
                                <?php if($cto_days >= 1): ?>
                                    <div class="alert alert-success mt-3">
                                        🎉 You have available leave credits!
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-sm-6 col-xl-3">
                                <div class="card summary-card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <p class="text-muted mb-1">Total Areas</p>
                                        <h2 class="mb-0 accent-text"><?= $total_areas; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card summary-card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <p class="text-muted mb-1">Area Assignments</p>
                                        <h2 class="mb-0 accent-text"><?= $total_assignments; ?></h2>
                                    </div>
                                </div>
                            </div>

        <!-- ========================= -->
        <!-- INVENTORY DASHBOARD -->
        <!-- ========================= -->

                            <div class="col-12 mt-4">
                                <h5 class="mb-3">📦 Inventory Overview</h5>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-primary text-white">
                                    <div class="card-body p-4">
                                        <p class="mb-1">📦 Total Requests</p>
                                        <h2><?= $total_requests; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-warning text-dark">
                                    <div class="card-body p-4">
                                        <p class="mb-1">⏳ Pending</p>
                                        <h2><?= $pending_requests; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-success text-white">
                                    <div class="card-body p-4">
                                        <p class="mb-1">✅ Approved</p>
                                        <h2><?= $approved_requests; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-danger text-white">
                                    <div class="card-body p-4">
                                        <p class="mb-1">❌ Rejected</p>
                                        <h2><?= $rejected_requests; ?></h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-0 shadow-sm bg-dark text-white">
                                    <div class="card-body p-4">
                                        <p class="mb-1">Low Stock Items</p>
                                        <h2><?= $low_stock_count; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                            <div class="alert alert-light border mb-3">
                                📊 Monitor critical inventory levels and recent request activities in real time
                            </div>

                            <h6 class="mt-3 mb-2 text-muted">📊 Monitoring Overview</h6>

                            <div class="row g-4">

            <!-- LEFT: LOW STOCK -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <span>📦 Low Stock</span>
                        <span class="badge bg-light text-dark"><?= $low_stock_count ?></span>
                        <a href="inventory.php" class="text-white small text-decoration-none">
                            View All →
                        </a>
                    </div>

                    <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $low_stock_items->fetch_assoc()): ?>
                                <tr style="cursor:pointer;" onclick="window.location='inventory.php'">
                                    <td><?= htmlspecialchars($row['item_name']); ?></td>
                                    <td>
                                        <?php if ($row['quantity'] <= 3): ?>
                                            <span class="badge bg-danger"><?= $row['quantity']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><?= $row['quantity']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT: RECENT REQUESTS -->

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <span>📄 Recent Requests</span>
                        <span class="badge bg-light text-dark"><?= $low_stock_count ?></span>
                        <a href="requests.php" class="text-white small text-decoration-none">
                            View All →
                        </a>
                    </div>

                    <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if ($recent_requests->num_rows > 0): ?>

                                    <?php while($row = $recent_requests->fetch_assoc()): ?>
                                    <tr style="cursor:pointer;" onclick="window.location='modules/inventory/request_manage.php?id=<?= $row['id'] ?>'">
                                        <td>#<?= $row['id']; ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($row['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            📭 No recent requests available
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>                     
    </div>
                            <hr class="my-4">
                                    
                                    <div class="text-end text-muted small mb-2">
                                        Last updated: <?= date('h:i A'); ?>
                                    </div>

                                                     
                                <!-- Attendance Trend -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header accent-bg text-dark">
                                            📊 Attendance Trend (Last 7 Days)
                                        </div>
                                        <div class="card-body p-4">
                                            <canvas id="trendChart" height="120"></canvas>
                                        </div>
                                    </div>

                                    <!-- CTO Monthly -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white">
                                            📊 CTO Monthly Hours
                                        </div>
                                        <div class="card-body">
                                            <canvas id="ctoChart" height="100"></canvas>
                                        </div>
                                    </div>

                                    <!-- CTO Usage vs Earned -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-dark text-white">
                                            📊 CTO Usage vs Earned
                                        </div>
                                        <div class="card-body">
                                            <canvas id="ctoUsageChart" height="100"></canvas>
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

                    new Chart(document.getElementById('ctoChart'), {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode($ctoMonths); ?>,
                            datasets: [{
                                label: 'CTO Hours',
                                data: <?= json_encode($ctoHours); ?>,
                                backgroundColor: 'rgba(54, 162, 235, 0.6)'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    new Chart(document.getElementById('ctoUsageChart'), {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode($usageMonths); ?>,
                            datasets: [
                                {
                                    label: 'Earned Hours',
                                    data: <?= json_encode($earnedHours); ?>,
                                    backgroundColor: 'rgba(40, 167, 69, 0.7)'
                                },
                                {
                                    label: 'Used Hours',
                                    data: <?= json_encode($usedHours); ?>,
                                    backgroundColor: 'rgba(220, 53, 69, 0.7)'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    
            </script>

            
        
    </body>
</html>

