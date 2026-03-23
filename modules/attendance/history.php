<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/layout.php';

require_role(['admin', 'supervisor', 'personnel']);

$startDate = clean_input($_GET['start_date'] ?? '');
$endDate = clean_input($_GET['end_date'] ?? '');
$nameSearch = clean_input($_GET['search'] ?? '');

$sql = "
SELECT a.id, p.fullname, a.date, a.time_in, a.time_out, a.status, a.undertime
FROM attendance a
JOIN personnel p ON p.id = a.personnel_id
WHERE 1 = 1
";

$params = [];
$types = '';

$isPersonnel = ($_SESSION['role'] === 'personnel');

if ($isPersonnel) {
    $sql .= ' AND a.personnel_id = ?';
    $params[] = $_SESSION['personnel_id'];
    $types .= 'i';
}

if (!empty($startDate) && !empty($endDate)) {
    $sql .= ' AND a.date BETWEEN ? AND ?';
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= 'ss';
}

if ($nameSearch !== '') {
    $sql .= ' AND p.fullname LIKE ?';
    $params[] = '%' . $nameSearch . '%';
    $types .= 's';
}

$sql .= ' ORDER BY a.date DESC, p.fullname ASC LIMIT 500';
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History | GenServis</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row">
        <?php render_sidebar($_SESSION['role']); ?>
        <main class="col-lg-10 col-md-9 p-4">

            <?php render_topbar(); ?>  <!-- ✅ ADD THIS -->

            <h3 class="mb-3">Attendance History</h3>
                       
           <?php if ($_SESSION['role'] === 'personnel'): ?>
                <p class="text-muted">Showing your attendance records only.</p>
            <?php endif; ?>

           <?php if ($_SESSION['role'] !== 'personnel'): ?>
            <form method="get" class="row g-2 mb-3">

                <div class="col-md-3">
                    <label class="form-label">Date</label>

                    <input type="text" id="dateRange" class="form-control"value="<?= (!empty($startDate) && !empty($endDate)) ? $startDate . ' to ' . $endDate : '' ?>"placeholder="Select Date Range">

                    <input type="hidden" name="start_date" id="start_date"
                    value="<?= htmlspecialchars($startDate ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <input type="hidden" name="end_date" id="end_date"
                    value="<?= htmlspecialchars($endDate ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Personnel Name</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name"
                    value="<?= htmlspecialchars($nameSearch, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                
            </form>
            <?php endif; ?>

            <?php if (!empty($startDate) && !empty($endDate)): ?>
                <p class="text-muted">
                    Showing records from <strong><?= $startDate ?></strong> to <strong><?= $endDate ?></strong>
                </p>
            <?php endif; ?>

            <div class="table-responsive card border-0 shadow-sm">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Personnel</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Late</th>
                        <th>Undertime</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['time_in'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['time_out'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php $status = $row['status']; ?>
                                <span class="badge <?= $status === 'Present' ? 'bg-success' : ($status === 'Late' ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                            <?= ($row['status'] === 'Late') ? '<span class="badge bg-warning text-dark">Yes</span>' : '-'; ?>
                            </td>

                            <td>
                            <?= !empty($row['undertime']) ? htmlspecialchars($row['undertime'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script src="<?= htmlspecialchars(app_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
const rangeInput = document.getElementById("dateRange");

if (rangeInput) {
    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",

        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {

                let start = selectedDates[0].toISOString().split('T')[0];
                let end = selectedDates[1].toISOString().split('T')[0];

                document.getElementById("start_date").value = start;
                document.getElementById("end_date").value = end;
            }
        }
    });
}
</script>

</body>
</html>