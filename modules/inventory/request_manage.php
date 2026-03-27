<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>GenServis</title>

            <!-- Bootstrap -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

            <style>
                /* BODY BACKGROUND */
                body {
                    background-color: #f1f5f9;
                }

                /* SIDEBAR (GREEN THEME) */
                .sidebar {
                    background-color: #166534; /* dark green */
                    min-height: 100vh;
                }

                /* SIDEBAR TITLE */
                .sidebar h5 {
                    color: #facc15; /* yellow like sample */
                    font-weight: bold;
                }

                /* SIDEBAR LINKS */
                .sidebar .nav-link {
                    color: #d1fae5;
                    padding: 8px;
                    border-radius: 6px;
                }

                /* HOVER EFFECT */
                .sidebar .nav-link:hover {
                    background-color: #14532d;
                    color: #ffffff;
                }

                /* ACTIVE MENU (OPTIONAL) */
                .sidebar .nav-link.active {
                    background-color: #14532d;
                    color: white;
                }

                /* CARD STYLE */
                .card {
                    border-radius: 10px;
                }

                /* BUTTON STYLE (MATCH GREEN BUTTON) */
                .btn-success {
                    background-color: #166534;
                    border-color: #166534;
                }

                .btn-success:hover {
                    background-color: #14532d;
                }
            </style>
        </head>

<body>

<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']);





// fetch requests
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sql = "
SELECT r.*, i.item_name, p.fullname
FROM inventory_requests r
JOIN inventory_items i ON i.id = r.item_id
JOIN personnel p ON p.id = r.personnel_id
WHERE 1=1
";

$params = [];
$types = "";

// search filter
if(!empty($search)){
    $sql .= " AND p.fullname LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// status filter
if(!empty($status)){
    $sql .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY r.id DESC";



$stmt = $conn->prepare($sql);

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$requests = $stmt->get_result();

?>
<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

    <main class="col-lg-10 col-md-9 p-4">

    <?php render_topbar(); ?>

        <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
        <h4 class="mb-0">Material Requests</h4>
    </div>

    <div class="card-body">

        <!-- 🔍 FILTER -->
        <form method="GET" class="row g-2 mb-3">

        <div class="col-md-3">
        <input type="text" name="search" class="form-control"
        placeholder="Search personnel..."
        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>

    <div class="col-md-3">
        <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="Pending" <?= $status=='Pending'?'selected':'' ?>>Pending</option>
            <option value="Approved" <?= $status=='Approved'?'selected':'' ?>>Approved</option>
            <option value="Rejected" <?= $status=='Rejected'?'selected':'' ?>>Rejected</option>
        </select>
    </div>

    <div class="col-md-2">
        <button class="btn btn-success w-100">Filter</button>
    </div>

    <div class="col-md-2">
        <a href="request_manage.php" class="btn btn-secondary w-100">Reset</a>
    </div>

    </form>

    <div class="table-responsive">

        <table class="table table-bordered table-striped align-middle">

            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Personnel</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
<tbody>
    <?php while($row = $requests->fetch_assoc()): ?>

    <tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['fullname']) ?></td>
    <td><?= htmlspecialchars($row['item_name']) ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= $row['request_date'] ?></td>
    
    <td>
        <?php
        $color = "secondary";
        if($row['status'] == "Pending") $color = "warning";
        if($row['status'] == "Approved") $color = "success";
        if($row['status'] == "Rejected") $color = "danger";
        ?>
            <span class="badge bg-<?= $color ?>">
            <?= $row['status'] ?>
        </span>
    </td>

    <td>
        <?php if($row['status'] == "Pending"): ?>

        <a href="approve_request.php?id=<?= $row['id']; ?>" 
            class="btn btn-success btn-sm"
            onclick="return confirm('Approve this request?')">
            Approve
        </a>

        <a href="reject_request.php?id=<?= $row['id']; ?>" 
            class="btn btn-danger btn-sm"
            onclick="return confirm('Reject this request?')">
        Reject
        </a>

        <?php else: ?>
        <span class="text-muted">No Action</span>
        <?php endif; ?>

    </td>

    </tr>

    <?php endwhile; ?>
</tbody>
</table>
</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>