<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['personnel']);

// get items
$items = $conn->query("
    SELECT id, item_name, quantity 
    FROM inventory_items 
    ORDER BY item_name ASC
");

if(isset($_POST['request'])){

    $item_id = (int) $_POST['item_id'];
    $qty = (int) $_POST['quantity'];
    $personnel_id = $_SESSION['personnel_id'] ?? 0;

    if($personnel_id == 0){
        die("Error: User not linked to personnel");
}

    // 🔥 VALIDATION
    if(empty($item_id) || $qty <= 0){
    $error = "Invalid input";
} else {

    // 🔥 CHECK IF ITEM EXISTS
    $stmt = $conn->prepare("SELECT id, quantity FROM inventory_items WHERE id=?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $checkItem = $stmt->get_result()->fetch_assoc();

    if(!$checkItem){
        $error = "Invalid item selected";
    } elseif($checkItem['quantity'] <= 0){
        $error = "Item is out of stock";
    } else {
             $stmt = $conn->prepare("
            INSERT INTO inventory_requests 
            (personnel_id, item_id, quantity, request_date)
            VALUES (?, ?, ?, CURDATE())
        ");

        $stmt->bind_param("iii", $personnel_id, $item_id, $qty);
        $stmt->execute();

        $success = "Request submitted!";
        } // ✅ closes item check
    } // ✅ closes main else   
}
?>

<!DOCTYPE html>
<html>
        
    <head>
        <title>Request Materials</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                background-color: #f1f5f9;
            }

            .sidebar {
                background-color: #166534;
                min-height: 100vh;
            }

            .sidebar h5 {
                color: #facc15;
                font-weight: bold;
            }

            .sidebar .nav-link {
                color: #d1fae5;
                padding: 8px;
                border-radius: 6px;
            }

            .sidebar .nav-link:hover {
                background-color: #14532d;
                color: #fff;
            }

            .card {
                border-radius: 10px;
            }

            .btn-primary {
                background-color: #166534;
                border-color: #166534;
            }

            .btn-primary:hover {
                background-color: #14532d;
            }
        </style>
    </head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<div class="card shadow-sm border-0">

<div class="card-header bg-white">
<h4 class="fw-bold">Request Cleaning Materials</h4>
</div>

<div class="card-body p-4">

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if(isset($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label class="form-label">Item</label>
<select name="item_id" class="form-control" required>

    <option value="">Select Item</option>

    <?php while($item = $items->fetch_assoc()): ?>
        <option value="<?= $item['id'] ?>" <?= $item['quantity'] <= 0 ? 'disabled' : '' ?>>
            <?= $item['item_name'] ?> 
                (Stock: <?= $item['quantity'] ?><?= $item['quantity'] <= 0 ? ' - Out of stock' : '' ?>)
        </option>
    <?php endwhile; ?>

</select>

</div>

<div class="mb-3">
    <label class="form-label">Quantity</label>
    <input type="number" name="quantity" class="form-control" required min="1">
</div>

    <button name="request" class="btn btn-primary px-4">
        Submit Request
    </button>

</form>

</div>

</div>

</main>

</div>
</div>

</body>
</html>