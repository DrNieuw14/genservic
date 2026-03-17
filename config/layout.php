<?php
/**
 * Shared navigation renderer.
 */
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/database.php';


function render_sidebar(string $role): void
{

$pendingCount = 0;

if ($role === 'supervisor' || $role === 'admin') {

    global $conn;

    $result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE status='pending'");
    $row = $result->fetch_assoc();
    $pendingCount = $row['total'];

}
?>
<aside class="col-lg-2 col-md-3 sidebar p-3 text-white">
    <h5 class="mb-3">GenServis</h5>

    <nav class="nav flex-column">

        <a class="nav-link" href="<?= htmlspecialchars(app_url('dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>

        <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/attendance/attendance.php'), ENT_QUOTES, 'UTF-8'); ?>">Attendance</a>

        <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/attendance/history.php'), ENT_QUOTES, 'UTF-8'); ?>">Attendance History</a>

        <a class="nav-link" href="<?= htmlspecialchars(app_url('modules/personnel/assign_area.php'), ENT_QUOTES, 'UTF-8'); ?>">
            Assign Area
            </a>

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

        <a class="nav-link" href="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8'); ?>">Logout</a>

    </nav>

</aside>
<?php
}
?>