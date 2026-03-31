<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

require_role(['admin','supervisor']);

// STEP 1: Filters
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$status     = $_GET['status'] ?? '';


// ✅ STEP 2: WHERE (MUST COME FIRST)
$where = "WHERE 1=1";

if(!empty($start_date) && !empty($end_date)){
    $where .= " AND DATE(r.request_date) BETWEEN '$start_date' AND '$end_date'";
}

if(!empty($status)){
    $where .= " AND r.status = '$status'";
}


// ✅ STEP 4: SUMMARY (NOW CORRECT)
$summary_sql = "
SELECT 
    COUNT(*) as total_requests,
    SUM(status='approved') as approved,
    SUM(status='pending') as pending,
    SUM(status='rejected') as rejected
FROM inventory_requests r
$where
";

$summary = $conn->query($summary_sql)->fetch_assoc();


// ✅ STEP 2 MAIN QUERY
$sql = "
SELECT 
    r.id,
    r.request_date,
    r.status,
    r.approved_at,
    p.fullname,
    COUNT(ri.id) as total_items,
    SUM(ri.quantity) as total_quantity
FROM inventory_requests r
JOIN personnel p ON r.personnel_id = p.id
JOIN inventory_request_items ri ON r.id = ri.request_id
$where
GROUP BY r.id
ORDER BY r.request_date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | GenServis</title>

    <!-- ✅ COPY THIS FROM DASHBOARD -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>

<body>

<div class="container-fluid app-layout">
    <div class="row">

        <!-- ✅ Sidebar -->
        <?php render_sidebar($_SESSION['role']); ?>

        <!-- ✅ Main Content -->
        <main class="col-lg-10 col-md-9 p-4">

            <?php render_topbar(); ?>


<h3 class="mb-4">📊 Reports Module</h3>

<!-- FILTER -->

<form method="GET" class="row g-3 mb-3">
    <div class="col-md-3">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
    </div>

    <div class="col-md-3">
        <label>End Date</label>
        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
    </div>

    <div class="col-md-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="">All</option>
            <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
            <option value="approved" <?= $status=='approved'?'selected':'' ?>>Approved</option>
            <option value="rejected" <?= $status=='rejected'?'selected':'' ?>>Rejected</option>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
</form>

<!-- SUMMARY CARDS -->

<div class="row mb-3">

    <div class="col-md-3">
        <div class="card shadow-sm p-3 bg-primary text-white">
            <h6>Total Requests</h6>
            <h3><?= $summary['total_requests'] ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm p-3 bg-success text-white">
            <h6>Approved</h6>
            <h3><?= $summary['approved'] ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm p-3 bg-warning text-dark">
            <h6>Pending</h6>
            <h3><?= $summary['pending'] ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm p-3 bg-danger text-white">
            <h6>Rejected</h6>
            <h3><?= $summary['rejected'] ?></h3>
        </div>
    </div>

</div>

<p>
    Showing results 
    <?= $start_date ? "from <strong>" . date('M d, Y', strtotime($start_date)) . "</strong>" : "" ?>
    <?= $end_date ? "to <strong>" . date('M d, Y', strtotime($end_date)) . "</strong>" : "" ?>
</p>

<button onclick="window.print()" class="btn btn-secondary mb-3">
    🖨️ Print Report
</button>

<a href="export.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status=<?= urlencode($status) ?>" 
   class="btn btn-success mb-3">
   ⬇ Export CSV
</a>


<!-- TABLE -->

<table class="table table-bordered table-hover shadow-sm">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Personnel</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total Items</th>
            <th>Total Quantity</th>
        </tr>
    </thead>
    <tbody>
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['request_date'])) ?></td>
                    
                    <td>
                        <?php
                            $status = strtolower($row['status']);
                            ?>

                            <span class="badge rounded-pill bg-<?=
                                $status == 'approved' ? 'success' :
                                ($status == 'pending' ? 'warning' : 'danger')
                            ?>">
                                <?= ucfirst($status) ?>
                            </span>
                    </td>

                    <td><?= $row['total_items'] ?></td>
                    <td><?= $row['total_quantity'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No records found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
.table-hover tbody tr:hover {
    cursor: pointer;
    background-color: #f5f5f5;
}
</style>

<style>
.card:hover {
    transform: translateY(-3px);
    transition: 0.2s;
}
</style>

<style>
@media print {
    form,
    button,
    a {
        display: none;
    }

    body {
        background: white;
    }

    table {
        font-size: 12px;
    }
}
</style>

       </main>
    </div>
</div>

<script src="<?= htmlspecialchars(app_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>

</body>
</html>