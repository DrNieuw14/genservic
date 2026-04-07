<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

require_role(['admin', 'supervisor', 'personnel']);

$dateFilter = clean_input($_GET['date'] ?? '');
$nameSearch = clean_input($_GET['search'] ?? '');

$sql = "
SELECT 
    a.id,
    p.fullname,
    a.date,
    a.time_in,
    a.time_out,
    a.status,
    a.undertime,
    a.overtime
FROM attendance a 
JOIN personnel p ON p.id = a.personnel_id 
WHERE 1=1
";
$params = [];
$types = '';

$isPersonnel = ($_SESSION['role'] === 'personnel');

if ($isPersonnel) {
    $sql .= ' AND a.personnel_id = ?';
    $params[] = $_SESSION['personnel_id'];
    $types .= 'i';
}

if ($dateFilter !== '') {
    $sql .= ' AND a.date = ?';
    $params[] = $dateFilter;
    $types .= 's';
}
if ($nameSearch !== '') {
    $sql .= ' AND p.fullname LIKE ?';
    $params[] = '%' . $nameSearch . '%';
    $types .= 's';
}
$sql .= ' ORDER BY a.date DESC LIMIT 1000';
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$total_present = 0;
$total_absent = 0;
$total_late = 0;
$total_undertime = 0;

$tempRows = [];

while ($row = $result->fetch_assoc()) {

    $status = $row['status'];

    if (!$row['time_in']) {
    $status = 'Absent';
} else {
    $status = $row['status'];
}

    if ($status == 'Present' || $status == 'Early') $total_present++;
    if ($status == 'Absent') $total_absent++;
    if ($status == 'Late') $total_late++;
    if (!empty($row['undertime']) && $row['undertime'] != '00:00:00') {
    $total_undertime++;
    }

    $row['final_status'] = $status;
    $tempRows[] = $row;
}
$grouped = [];

foreach ($tempRows as $r) {
    $grouped[$r['date']][] = $r;
}
$queryString = http_build_query(['date' => $dateFilter, 'search' => $nameSearch]);
?>

<html lang="en">
    <head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Attendance Reports | GenServis</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
    </head>

    <body>
            <div class="container-fluid app-layout">
                <div class="row">
                    <?php render_sidebar($_SESSION['role']); ?>
                    <main class="col-lg-10 col-md-9 p-4">
                        <?php render_topbar(); ?>

            <div class="text-center mb-3">
                <h3 class="mb-0">Attendance Summary Report</h3>
            </div>
                            <?php if ($_SESSION['role'] !== 'personnel'): ?>
                                <form method="get" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($dateFilter, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="search" placeholder="Search personnel" value="<?= htmlspecialchars($nameSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Apply</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <div class="row mb-3">

            <div class="col-md-3">
            <div class="card bg-success text-white p-3">
            Present <h4><?= $total_present ?></h4>
            </div>
            </div>

            <div class="col-md-3">
            <div class="card bg-danger text-white p-3">
            Absent <h4><?= $total_absent ?></h4>
            </div>
            </div>

            <div class="col-md-3">
            <div class="card bg-warning p-3">
            Late <h4><?= $total_late ?></h4>
            </div>
            </div>

            <div class="col-md-3">
            <div class="card bg-info text-white p-3">
            Undertime <h4><?= $total_undertime ?></h4>
            </div>
            </div>

            </div>


                        <div class="table-responsive card border-0 shadow-sm">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th style="width:120px;">Date</th>
                                    <th>Personnel</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Undertime</th>
                                    <th>Overtime</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>

            <?php foreach ($grouped as $date => $rows): ?>

            <!-- DATE HEADER -->

            <tr style="background:#343a40; color:white; font-weight:bold;">
                <td colspan="7">
                    📅 <?= date('F d, Y', strtotime($date)) ?>
                </td>
            </tr>


            <!-- SUMMARY -->
            <?php
            $present = 0;
            $late = 0;
            $absent = 0;

            foreach ($rows as $r) {
                if ($r['final_status'] == 'Absent') $absent++;
                elseif ($r['final_status'] == 'Late') $late++;
                else $present++;
            }
            ?>

            <tr class="table-secondary">
                <td colspan="7">
                    ✅ Present: <?= $present ?> |
                    ⚠️ Late: <?= $late ?> |
                    ❌ Absent: <?= $absent ?>
                </td>
            </tr>

            <!-- ROWS -->
            <?php foreach ($rows as $row): 

            $class = '';

            switch ($row['final_status']) {
                case 'Absent': $class = 'table-danger'; break;
                case 'Late': $class = 'table-warning'; break;
                case 'Early': $class = 'table-info'; break;
                case 'Rest Day (Worked)': $class = 'table-dark'; break;
                default: $class = 'table-success';
            }
            ?>

            <tr class="<?= $class ?>">
            <td></td>
            <td><?= htmlspecialchars($row['fullname']); ?></td>
            <td><?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?></td>
            <td><?= htmlspecialchars($row['time_out'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['undertime'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['overtime'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['final_status']); ?></td>
            </tr>

            <?php endforeach; ?>

            <?php endforeach; ?>

        </tbody>
            </table>
                    <div class="d-flex justify-content-end mt-3 gap-2">
        <a class="btn btn-danger"
        href="<?= htmlspecialchars(app_url('reports/attendance_report_pdf.php') . '?' . $queryString, ENT_QUOTES, 'UTF-8'); ?>">
        Download PDF
        </a>

        <a class="btn btn-success"
        href="<?= htmlspecialchars(app_url('reports/attendance_report_excel.php') . '?' . $queryString, ENT_QUOTES, 'UTF-8'); ?>">
        Download Excel
        </a>
        </div>
                </div>
            </main>
        </div>
        </div>
        <script src="<?= htmlspecialchars(app_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
    </body>
</html>