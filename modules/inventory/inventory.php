<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']); // ✅ FIXED ACCESS

// ADD ITEM
if(isset($_POST['add_item'])){

    $item = trim($_POST['item_name']);
    $item = strtolower($item); // optional (prevents duplicates like "Mop" vs "mop")
    $qty = (int) $_POST['quantity'];
    $category_id = (int) $_POST['category_id'];
    $unit_id = (int) $_POST['unit_id'];

   if(empty($item) || $qty < 0 || $category_id <= 0 || $unit_id <= 0){
    $error = "Invalid input";
} else {

        $stmt = $conn->prepare("SELECT id FROM inventory_items WHERE item_name = ? AND category_id = ?");
        $stmt->bind_param("si", $item, $category_id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($exists){
            $error = "Item already exists";
        } else {

            $stmt = $conn->prepare("
                INSERT INTO inventory_items (item_name, quantity, category_id, unit_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("siii", $item, $qty, $category_id, $unit_id);
            
            $stmt->execute();
            
            $item_id = $conn->insert_id; // ✅ VERY IMPORTANT

            $stmt->close();

            // ✅ LOG SYSTEM
            $user_id = $_SESSION['user_id'] ?? 0;

            $log = $conn->prepare("
                INSERT INTO inventory_logs (item_id, action, quantity, user_id)
                VALUES (?, 'added', ?, ?)
            ");

            $log->bind_param("iii", $item_id, $qty, $user_id);
            $log->execute();
            $log->close();

            $redirect = "inventory.php?success=1";

            if(isset($_GET['category'])){
                $redirect .= "&category=" . (int)$_GET['category'];
            }

            header("Location: $redirect");
            exit();
        }
    }
}

    // FETCH CATEGORIES (ONCE)
    $categories = [];

    $units = [];

    $res = $conn->query("SELECT * FROM inventory_units ORDER BY unit_name ASC");

    while($row = $res->fetch_assoc()){
        $units[] = $row;
    }

    $res = $conn->query("SELECT * FROM inventory_categories");

    while($row = $res->fetch_assoc()){
        $categories[] = $row;
    }

// FETCH ITEMS
$where = "";

if(isset($_GET['category']) && $_GET['category'] != ""){
    $cat_id = (int) $_GET['category'];
    $where = "WHERE i.category_id = $cat_id";
}

$result = $conn->query("
    SELECT i.*, c.category_name, u.unit_name
    FROM inventory_items i
    LEFT JOIN inventory_categories c ON i.category_id = c.id
    LEFT JOIN inventory_units u ON i.unit_id = u.id
    $where
    ORDER BY i.item_name ASC
");

?>

<!DOCTYPE html>
<html>
    <head>

        <title>Inventory Management</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- ✅ THIS IS THE FIX -->
        <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">

        <style>
            .table-hover tbody tr:hover {
                background-color: #f8f9fa;
            }

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

<?php if(isset($_GET['updated'])): ?>
    <div class="alert alert-info">Item updated successfully</div>
<?php endif; ?>

<?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-warning">Item deleted</div>
<?php endif; ?>

<form method="GET" class="mb-3">
    <div class="row g-2">

        <div class="col-md-4">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php
                    foreach($categories as $c){
                        $selected = (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $selected>{$c['category_name']}</option>";
                    }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <a href="inventory.php" class="btn btn-secondary w-100">Reset</a>
        </div>

    </div>
</form>

        <!-- ADD ITEM FORM -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">

                <h6 class="text-muted mb-3">Add New Item</h6>

                <div class="row g-2 align-items-end">

            <!-- ITEM NAME -->
            <div class="col-md-4">
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" class="form-control" required>
            </div>

            <!-- CATEGORY -->
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>

                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- QUANTITY -->
            <div class="col-md-2">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="0">
            </div>

            <!-- UNIT -->
            <div class="col-md-2">
                <label class="form-label">Unit</label>
                <select name="unit_id" class="form-control" required>
                    <option value="">-- Unit --</option>

                    <?php foreach($units as $u): ?>
                        <option value="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['unit_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- BUTTON -->
            <div class="col-md-1">
                <button type="submit" name="add_item" class="btn btn-success w-100">
                    +
                </button>
            </div>

        </div>
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
<th>Category</th>
<th>Quantity</th>
<th>Status</th>
<th>Action</th>
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

        <td>
            <?= $row['id'] ?>
        </td>

        <td>
            <?= htmlspecialchars($row['item_name']) ?>
        </td>

        <td>
            <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?>
        </td>
        
        <td>
            <strong>
                <?= $row['quantity'] . ' ' . ($row['unit_name'] ?? '') ?>
            </strong>
        </td>

        <td>
            <span class="badge bg-<?= $color ?>">
            <?= $icon ?> <?= $status ?>
            </span>
        </td>

        <td>
            <a href="edit_item.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="delete_item.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
            onclick="return confirm('Delete this item?')">Delete</a>
        </td>
    </tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="6" class="text-center text-muted">No items available</td>
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