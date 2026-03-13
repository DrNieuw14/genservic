<?php
/**
 * Shared navigation renderer.
 */
function render_sidebar(string $role): void
{
    ?>
    <aside class="col-lg-2 col-md-3 sidebar p-3 text-white">
        <h5 class="mb-3">GenServis</h5>
        <nav class="nav flex-column">
            <a class="nav-link" href="/dashboard.php">Dashboard</a>
            <a class="nav-link" href="/attendance/attendance.php">Attendance</a>
            <a class="nav-link" href="/attendance/history.php">Attendance History</a>
            <a class="nav-link" href="/reports/attendance_report.php">Reports</a>
            <?php if ($role === 'admin'): ?>
                <a class="nav-link" href="/modules/personnel/personnel.php">Personnel Masterlist</a>
            <?php endif; ?>
            <a class="nav-link" href="/logout.php">Logout</a>
        </nav>
    </aside>
    <?php
}