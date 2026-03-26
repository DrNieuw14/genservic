<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']);

$result = $conn->query("
    SELECT l.*, i.item_name, u.fullname, u.role
    FROM inventory_logs l
    LEFT JOIN inventory_items i ON l.item_id = i.id
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Inventory Logs</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css')); ?>">

<style>
.card {
    border-radius: 12px;
}
</style>

</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<h3 class="mb-3">📜 Inventory Logs</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
<th>Item</th>
<th>Action</th>
<th>Quantity</th>
<th>User</th>
<th>Date</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['item_name']) ?></td>

<td>
    <span class="badge bg-<?= 
        $row['action'] == 'added' ? 'success' :
        ($row['action'] == 'deleted' ? 'danger' : 'warning')
    ?>">
        <?= $row['action'] ?>
    </span>
</td>

<td><?= $row['quantity'] ?></td>

<td>
    <?= htmlspecialchars($row['fullname'] ?? 'Unknown') ?>
    <br>

    <?php
    $roleColor = 'secondary';

    if($row['role'] == 'admin') $roleColor = 'danger';
    elseif($row['role'] == 'supervisor') $roleColor = 'warning';
    elseif($row['role'] == 'personnel') $roleColor = 'info';
    ?>

    <small class="badge bg-<?= $roleColor ?>">
        <?= $row['role'] ?? '' ?>
    </small>
</td>

<td><?= $row['created_at'] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>
</div>

</main>
</div>
</div>

</body>
</html>