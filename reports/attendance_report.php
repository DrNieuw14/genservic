<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

require_role(['admin', 'supervisor']);

$dateFilter = clean_input($_GET['date'] ?? '');
$nameSearch = clean_input($_GET['search'] ?? '');

$sql = "SELECT a.id, p.fullname, a.date, a.time_in, a.time_out, a.status FROM attendance a JOIN personnel p ON p.id = a.user_id WHERE 1=1";
$params = [];
$types = '';
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
$queryString = http_build_query(['date' => $dateFilter, 'search' => $nameSearch]);
?>

<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports | GenServis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>

</body>
<body>
<div class="container-fluid app-layout">
    <div class="row">
        <?php render_sidebar($_SESSION['role']); ?>
        <main class="col-lg-10 col-md-9 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Attendance Reports</h3>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-danger" href="/reports/download.php?type=pdf&<?= htmlspecialchars($queryString, ENT_QUOTES, 'UTF-8'); ?>">Download PDF</a>
                    <a class="btn btn-outline-success" href="/reports/download.php?type=excel&<?= htmlspecialchars($queryString, ENT_QUOTES, 'UTF-8'); ?>">Download Excel</a>
                </div>
            </div>

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

            <div class="table-responsive card border-0 shadow-sm">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th><th>Personnel</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['time_in'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['time_out'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>