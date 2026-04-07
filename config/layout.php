<?php

/**
 * Shared navigation renderer.
 */
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/database.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GenServis</title>

    <!-- ✅ Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- OPTIONAL: Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- YOUR CSS -->
    <link rel="stylesheet" href="<?= app_url('assets/css/app.css?v=2'); ?>">
</head>
<body>
<?php

/* =========================
   TOPBAR (MOVE HERE ✅)
========================= */
function render_topbar(): void
{
    if (!isset($_SESSION['fullname'])) return;

    $fullname = $_SESSION['fullname'] ?? 'Unknown';
    $role = $_SESSION['role'] ?? 'user';
    $today = date('Y-m-d');

    // detect PDF mode safely
    $is_pdf = isset($_GET['pdf']);
?>
    <div class="d-flex justify-content-between align-items-center mb-3">

        <!-- LEFT SIDE: Back button -->
       <div>
            <?php if(!$is_pdf): ?>
                <a href="<?= app_url('dashboard.php'); ?>" class="btn btn-secondary btn-sm">
                    ⬅ Back to Dashboard
                </a>
            <?php endif; ?>
        </div>

        <!-- RIGHT SIDE: user info -->
        <div class="text-end">
            <div>
                <strong>Logged in as:</strong> <?= htmlspecialchars($fullname) ?> (<?= htmlspecialchars($role) ?>)
            </div>
            <div>
                <strong>Today:</strong> <?= $today ?>
            </div>
        </div>

    </div>
<?php
}

function render_footer(): void
{
?>
    </body>
</html>
<?php
}



/* =========================
   SIDEBAR
========================= */
function render_sidebar(string $role): void
{
    
    
    global $conn; // ✅ ADD THIS 

    $currentPage = basename($_SERVER['PHP_SELF']);

    $requestCount = 0;

if ($conn instanceof mysqli) {

    $userId = $_SESSION['user_id'] ?? 0;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total 
        FROM inventory_requests 
        WHERE status = 'pending'
        AND approved_by IS NULL
    ");

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $row = $result->fetch_assoc();
        $requestCount = $row['total'];
    }

    $stmt->close();
}

    $pendingCount = 0;

    if ($conn instanceof mysqli) {
        $result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE status='pending'");
        if ($result) {
            $row = $result->fetch_assoc();
            $pendingCount = $row['total'];
    }
}

    
    ?>
    <aside class="col-lg-2 col-md-3 sidebar p-3 text-white">
        <h5 class="mb-3 fw-bold">GenServis</h5>

                

        <nav class="nav flex-column">

            <small class="text-uppercase fw-bold text-light mt-3 d-block"> 🏠 Main</small>

            <a class="nav-link" href="<?= htmlspecialchars(app_url('dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>
            <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/attendance/attendance.php'), ENT_QUOTES, 'UTF-8'); ?>">Attendance</a>
            <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/attendance/history.php'), ENT_QUOTES, 'UTF-8'); ?>">Attendance History</a>

            <?php if ($role === 'personnel'): ?>

        <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/personnel/my_area.php'), ENT_QUOTES, 'UTF-8'); ?>">
            My Area
        </a>

        <?php else: ?>

            <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/personnel/assign_area.php'), ENT_QUOTES, 'UTF-8'); ?>">
                Assign Area
            </a>

        <?php endif; ?>

                <?php if ($role === 'supervisor' || $role === 'admin'): ?>
                    
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/scheduling/schedule.php'), ENT_QUOTES, 'UTF-8'); ?>">
                        Work Scheduling
                    </a>

                <?php endif; ?>

                <!-- REPORTS MAIN -->
                    <small class="text-uppercase fw-bold text-light mt-3 d-block">📊 Reports</small>

                    <a class="nav-link mb-1" href="<?= app_url('reports/index.php'); ?>">
                        Inventory Reports
                    </a>

                    <a class="nav-link mb-1" href="<?= app_url('reports/attendance_report.php'); ?>">
                        Attendance Reports
                    </a>

                    <!-- DTR REPORTS -->

                    <?php if ($role === 'personnel'): ?>

                        <a class="nav-link mb-1 <?= $currentPage == 'personnel_dtr.php' ? 'active' : '' ?>"
                            href="<?= app_url('reports/personnel_dtr.php'); ?>">
                            <i class="bi bi-calendar-check"></i> My DTR
                        </a>

                    <?php else: ?>

                        <a class="nav-link mb-1 <?= $currentPage == 'dtr_report.php' ? 'active' : '' ?>"
                            href="<?= app_url('reports/dtr_report.php'); ?>">
                            <i class="bi bi-calendar-check"></i> DTR Reports
                        </a>

                    <?php endif; ?>

                <?php if ($role === 'admin'): ?>
                    
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/personnel/personnel.php'), ENT_QUOTES, 'UTF-8'); ?>">Personnel Masterlist</a>
                
                <?php endif; ?>

                <?php if ($role === 'supervisor' || $role === 'admin'): ?>
                    
                <?php endif; ?>

                <?php if ($role === 'admin' || $role === 'supervisor'): ?>

                    <small class="text-uppercase fw-bold text-light mt-3 d-block">📦 Inventory</small>

                    <a class="nav-link <?= $currentPage == 'inventory.php' ? 'active' : '' ?>" href="<?= htmlspecialchars(app_url('modules/inventory/inventory.php')); ?>">
                        <i class="bi bi-box"></i> Inventory
                    </a>

                    <a class="nav-link mb-1" href="<?= htmlspecialchars(app_url('modules/inventory/categories/index.php')); ?>">
                        <i class="bi bi-tags"></i> Categories
                    </a>

                    <?php
                        $class = 'nav-link ms-3 ' . ($currentPage == 'request_manage.php' ? 'active' : '');
                    ?>

                    <a class="<?= $class ?>" href="<?= htmlspecialchars(app_url('modules/inventory/request_manage.php')); ?>">
                        Requests

                        <?php if($requestCount > 0): ?>
                            <span class="badge bg-danger ms-2">
                                <?= $requestCount ?>
                            </span>
                        <?php endif; ?>

                    </a>

                    <a class="nav-link mb-1 <?= $currentPage == 'logs.php' ? 'active' : '' ?>" href="<?= htmlspecialchars(app_url('modules/inventory/logs.php')); ?>">
                        <i class="bi bi-clock-history"></i> Logs
                    </a>
                    
                <?php endif; ?>

                <?php if ($role === 'personnel'): ?>
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/inventory/request.php'), ENT_QUOTES, 'UTF-8'); ?>">
                        Request Materials
                    </a>

                <?php endif; ?>

                <small class="text-uppercase fw-bold text-light mt-3 d-block">⚙️ Management</small>
                <!-- ACCOUNT APPROVAL (MOVED HERE) -->
                <?php if ($role === 'supervisor' || $role === 'admin'): ?>
                    
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/users/approval.php'), ENT_QUOTES, 'UTF-8'); ?>">
                        👤 Account Approval

                        <?php if($pendingCount > 0): ?>
                            <span class="badge bg-danger ms-2">
                                <?= $pendingCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <hr class="text-light">
                

                <a class="nav-link" href="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8'); ?>">🚪Logout</a>

            </nav>
    </aside>

<?php
}
?>
