<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']); // ✅ FIXED ACCESS

// ADD ITEM
if(isset($_POST['add_item'])){

    $item = trim($_POST['item_name']);
    $item = htmlspecialchars($item, ENT_QUOTES);
    $qty = (int) $_POST['quantity'];

    if(empty($item) || $qty < 0){
        $error = "Invalid input";
    } else {


                $stmt = $conn->prepare("SELECT id FROM inventory_items WHERE item_name=?");
        $stmt->bind_param("s", $item);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();

        if($exists){
            $error = "Item already exists";
        } else {

        $stmt = $conn->prepare("
            INSERT INTO inventory_items (item_name, quantity)
            VALUES (?, ?)
        ");
        $stmt->bind_param("si", $item, $qty);
        $stmt->execute();

        header("Location: inventory.php?success=1");
        exit();
    }
}
}
// FETCH ITEMS
$result = $conn->query("
    SELECT * FROM inventory_items
    ORDER BY item_name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <style>
    .table-hover tbody tr:hover {
    background-color: #f8f9fa;
    }

    .card {
    border-radius: 12px;
}
    </style>

<title>Inventory Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">📦 Inventory Management</h3>
</div>

<!-- ALERTS -->
<?php if(isset($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Item added successfully</div>
<?php endif; ?>

<!-- ADD ITEM FORM -->
<div class="card shadow-sm border-0 mb-4">
<div class="card-body">

<h6 class="text-muted mb-3">Add New Item</h6>

<form method="POST" class="row g-2">

<div class="col-md-5">
<input type="text" name="item_name" placeholder="Enter item name" class="form-control" required>
</div>

<div class="col-md-3">
<input type="number" name="quantity" placeholder="Enter quantity" class="form-control" required min="0">

<small class="text-muted">
Tip: Items below 5 will show as Low Stock
</small>
</div>

<div class="col-md-2">
<button name="add_item" class="btn btn-success w-100">+ Add Item</button>
</div>

</form>

</div>
</div>

<!-- INVENTORY TABLE -->
<div class="card shadow-sm border-0">
<div class="card-header bg-white">
<h5 class="mb-0">Inventory List</h5>
</div>

<div class="card-body">

<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Item Name</th>
<th>Quantity</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<?php
$status = "Available";
$color = "success";
$icon = "✔";

if($row['quantity'] <= 0){
    $status = "Out of Stock";
    $color = "danger";
    $icon = "❌";
}
elseif($row['quantity'] < 5){
    $status = "Low Stock";
    $color = "warning";
    $icon = "⚠";
}
?>

<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['item_name']) ?></td>
<td><strong><?= $row['quantity'] ?></strong></td>
<td>

<span class="badge bg-<?= $color ?>">
<?= $icon ?> <?= $status ?>
</span>

</td>
</tr>

<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="4" class="text-center text-muted">No items available</td>
</tr>
<?php endif; ?>

</tbody>

</table>

</div>
</div>

</main>
</div>
</div>

</body>
</html>