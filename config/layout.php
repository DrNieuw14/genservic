<?php

/**
 * Shared navigation renderer.
 */
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/database.php';




/* =========================
   TOPBAR (MOVE HERE ✅)
========================= */
function render_topbar(): void
{
    if (!isset($_SESSION['fullname'])) return;

    $fullname = $_SESSION['fullname'] ?? 'Unknown';
    $role = $_SESSION['role'] ?? 'user';
    $today = date('Y-m-d'); // ← matches your exact format
?>
    <div class="d-flex justify-content-end mb-3">
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


/* =========================
   SIDEBAR
========================= */
function render_sidebar(string $role): void
{
    
    global $conn; // ✅ ADD THIS 

    $currentPage = basename($_SERVER['PHP_SELF']);
    $requestCount = 0;

    if ($conn instanceof mysqli) {
        $result = $conn->query("SELECT COUNT(*) AS total FROM inventory_requests WHERE status = 'pending'");
        if ($result) {
            $row = $result->fetch_assoc();
            $requestCount = $row['total'];
        }
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

                <a class="nav-link" href="<?= htmlspecialchars(app_url('reports/attendance_report.php'), ENT_QUOTES, 'UTF-8'); ?>">Reports</a>

                <?php if ($role === 'admin'): ?>
                    
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/personnel/personnel.php'), ENT_QUOTES, 'UTF-8'); ?>">Personnel Masterlist</a>
                
                <?php endif; ?>

                <?php if ($role === 'supervisor' || $role === 'admin'): ?>
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/users/approval.php'), ENT_QUOTES, 'UTF-8'); ?>">
                        Account Approval

                        <?php if($pendingCount > 0): ?>
                            <span class="badge bg-danger ms-2">
                                <?= $pendingCount ?>
                            </span>
                        <?php endif; ?>

                    </a>
                <?php endif; ?>

                <?php if ($role === 'admin' || $role === 'supervisor'): ?>

                    <a class="nav-link <?= $currentPage == 'inventory.php' ? 'active' : '' ?>" href="<?= htmlspecialchars(app_url('modules/inventory/inventory.php')); ?>">
                        <i class="bi bi-box"></i> Inventory
                    </a>

                    <a class="nav-link ms-3" href="<?= htmlspecialchars(app_url('modules/inventory/categories/index.php')); ?>">
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

                    <a class="nav-link ms-3 <?= $currentPage == 'logs.php' ? 'active' : '' ?>" href="<?= htmlspecialchars(app_url('modules/inventory/logs.php')); ?>">
                        <i class="bi bi-clock-history"></i> Logs
                    </a>
                    
                <?php endif; ?>

                <?php if ($role === 'personnel'): ?>
                    <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/inventory/request.php'), ENT_QUOTES, 'UTF-8'); ?>">
                        Request Materials
                    </a>

                <?php endif; ?>

                <a class="nav-link" href="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8'); ?>">Logout</a>

            </nav>
    </aside>

<?php
}
?>
