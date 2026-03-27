<?php
    require_once '../../config/database.php';
    require_once '../../config/auth.php';
    require_once '../../config/layout.php';

    require_role(['admin','supervisor']);

    $id = (int) $_GET['id'];

    if($id <= 0){
    die("Invalid item ID.");
}

    $stmt = $conn->prepare("SELECT * FROM inventory_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$item = $result->fetch_assoc();
   
    if(!$item){
        die("Item not found.");
}

    $units = $conn->query("SELECT * FROM inventory_units ORDER BY unit_name ASC");

    if(isset($_POST['update'])){

        $old_qty = $item['quantity']; 

        $name = trim($_POST['item_name']);
        $qty = (int) $_POST['quantity'];
        $category_id = (int) $_POST['category_id'];
        $unit_id = (int) $_POST['unit_id'];

        $stmt = $conn->prepare("
            UPDATE inventory_items 
            SET item_name=?, quantity=?, category_id=?, unit_id=?
            WHERE id=?
        ");
        $stmt->bind_param("siiii", $name, $qty, $category_id, $unit_id, $id);
        
        $stmt->execute();
        $stmt->close(); 
        $user_id = $_SESSION['user_id'] ?? 0;

        $log = $conn->prepare("
            INSERT INTO inventory_logs (item_id, action, quantity, user_id)
            VALUES (?, 'updated', ?, ?)
        ");

        $log->bind_param("iii", $id, $qty, $user_id);
        $log->execute();
        $log->close();

        header("Location: inventory.php?updated=1");
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Item</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Your App Style -->
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<div class="card shadow-sm border-0">

<div class="card-header bg-white">
    <h5 class="mb-0">✏️ Edit Item</h5>
</div>

<div class="card-body">
    <form method="POST" class="row g-3">

        <div class="col-md-4">
            <label class="form-label">Item Name</label>
            <input type="text" name="item_name" class="form-control"
            value="<?= htmlspecialchars($item['item_name']) ?>" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control" required>
                <?php
            $cat = $conn->query("SELECT * FROM inventory_categories");
            while($c = $cat->fetch_assoc()):
            ?>
                <option value="<?= $c['id'] ?>"
                    <?= ($c['id'] == $item['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['category_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control text-center"
        value="<?= $item['quantity'] ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Unit</label>
        <select name="unit_id" class="form-control" required>
            <?php while($u = $units->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"
                    <?= ($u['id'] == $item['unit_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['unit_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-12 mt-3 d-flex gap-2">
        <button name="update" class="btn btn-success">
            💾 Update Item
        </button>

        <a href="inventory.php" class="btn btn-secondary">
            ← Back
        </a>
    </div>

</form>
</div> <!-- card-body -->
</div> <!-- card -->

</main>
</div>
</div>
 </body>
</html>