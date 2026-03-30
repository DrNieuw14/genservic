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

$repeat_items = [];

if(isset($_GET['repeat_id'])){
    $rid = (int) $_GET['repeat_id'];

    $stmt = $conn->prepare("
        SELECT item_id, quantity 
        FROM inventory_request_items 
        WHERE request_id = ?
    ");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $res = $stmt->get_result();

    while($row = $res->fetch_assoc()){
        $repeat_items[] = $row;
    }
}

if(isset($_POST['request'])){

    $personnel_id = $_SESSION['personnel_id'] ?? 0;
    $item_ids = $_POST['item_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    if($personnel_id == 0){
        die("Error: User not linked to personnel");
    }

    if(empty($item_ids)){
        $error = "No items selected";
    } else {

        $conn->begin_transaction();

        try {

            // 🔥 INSERT REQUEST HEADER
            $stmt = $conn->prepare("
                INSERT INTO inventory_requests (personnel_id, request_date)
                VALUES (?, CURDATE())
            ");
            $stmt->bind_param("i", $personnel_id);
            $stmt->execute();

            $request_id = $conn->insert_id;

            // 🔥 PREPARE ITEM INSERT
            $stmtItem = $conn->prepare("
                INSERT INTO inventory_request_items (request_id, item_id, quantity)
                VALUES (?, ?, ?)
            ");

            // 🔥 LOOP ITEMS
            foreach($item_ids as $index => $item_id){

                $item_id = (int)$item_id;
                $qty = (int)$quantities[$index];

                if($item_id <= 0 || $qty <= 0){
                    throw new Exception("Invalid item or quantity");
                }

                // 🔥 CHECK STOCK
                $check = $conn->prepare("SELECT quantity FROM inventory_items WHERE id=?");
                $check->bind_param("i", $item_id);
                $check->execute();
                $result = $check->get_result()->fetch_assoc();

                if(!$result){
                    throw new Exception("Item not found");
                }

                if($result['quantity'] < $qty){
                    throw new Exception("Not enough stock for item ID: $item_id");
                }

                // 🔥 INSERT ITEM
                $stmtItem->bind_param("iii", $request_id, $item_id, $qty);
                $stmtItem->execute();
            }

            $conn->commit();
            $success = "Request submitted successfully!";

        } catch(Exception $e){
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
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

            .item-row {
                margin-bottom: 10px;
            }

            .item-row:not(:first-child) label {
                display: none;
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

    <div id="item-container">

        <?php if(!empty($repeat_items)): ?>

            <?php foreach($repeat_items as $r_item): ?>

            <div class="row mb-2 item-row align-items-end">

                <div class="col-md-6">
                    <label class="form-label">Item</label>
                    <select name="item_id[]" class="form-control" required>
                        <option value="">Select Item</option>

                        <?php 
                        $items->data_seek(0);
                        while($item = $items->fetch_assoc()): 
                        ?>
                            <option value="<?= $item['id'] ?>"
                                <?= $item['id'] == $r_item['item_id'] ? 'selected' : '' ?>>
                                <?= $item['item_name'] ?> (Stock: <?= $item['quantity'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity[]" 
                        class="form-control" 
                        value="<?= $r_item['quantity'] ?>" 
                        required min="1">
                </div>

                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-danger remove-row w-100">
                        X
                    </button>
                </div>
             </div>

        <?php endforeach; ?>

        <?php else: ?>

            <div class="row mb-2 item-row">

                <div class="col-md-6">
                    <label class="form-label">Item</label>
                    <select name="item_id[]" class="form-control" required>

                        <option value="">Select Item</option>

                        <?php 
                        $items->data_seek(0);
                        while($item = $items->fetch_assoc()): 
                        ?>
                            <option value="<?= $item['id'] ?>" <?= $item['quantity'] <= 0 ? 'disabled' : '' ?>>
                                <?= $item['item_name'] ?> 
                                (Stock: <?= $item['quantity'] ?><?= $item['quantity'] <= 0 ? ' - Out of stock' : '' ?>)
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity[]" class="form-control" required min="1">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-row">X</button>
                </div>

            </div>
        <?php endif; ?>

            <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-secondary" id="add-row">
                    + Add Item
                </button>

                <button name="request" class="btn btn-primary">
                    Submit Request
                </button>
            </div>

</form>

<hr>
<h5 class="mt-4">My Requests</h5>

<table class="table table-bordered mt-2">
    <thead>
        <tr>
            <th>ID</th>
            <th>Items</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

<?php
$personnel_id = $_SESSION['personnel_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.status,
        r.request_date,
        r.rejection_reason,
        i.item_name,
        ri.quantity

    FROM inventory_requests r
    JOIN inventory_request_items ri ON r.id = ri.request_id
    JOIN inventory_items i ON ri.item_id = i.id

    WHERE r.personnel_id = ?
    ORDER BY r.id DESC
");

$stmt->bind_param("i", $personnel_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){
    $rid = $row['id'];

    if(!isset($data[$rid])){
        $data[$rid] = [
            'id' => $row['id'],
            'status' => $row['status'],
            'date' => $row['request_date'],
            'reason' => $row['rejection_reason'],
            'items' => []
        ];
    }

    $data[$rid]['items'][] = [
        'name' => $row['item_name'],
        'qty' => $row['quantity']
    ];
}
?>

<?php if(empty($data)): ?>
<tr>
    <td colspan="4" class="text-center">No requests found.</td>
</tr>
<?php else: ?>
    <?php foreach($data as $req): ?>
<tr>
    <td><?= $req['id'] ?></td>

    <td>
        <?php foreach($req['items'] as $item): ?>
            <div>
                • <?= htmlspecialchars($item['name']) ?> 
                (<?= $item['qty'] ?>)
            </div>
        <?php endforeach; ?>
    </td>

    <td><?= $req['date'] ?></td>

    <td>
        <strong><?= $req['status'] ?></strong>

        <?php if($req['status'] == 'Rejected'): ?>
            <div class="alert alert-danger mt-2 p-2">
                <strong>Reason:</strong><br>
                <?= htmlspecialchars($req['reason']) ?>
            </div>

            <a href="request.php?repeat_id=<?= $req['id']; ?>" 
               class="btn btn-warning btn-sm mt-1">
               Request Again
            </a>
        <?php endif; ?>
    </td>
</tr>
    <?php endforeach; ?>
<?php endif; ?>

    </tbody>
</table>


</main>

</div>
</div>

</body>
</html>

<script>
document.getElementById('add-row').addEventListener('click', function(){
    let container = document.getElementById('item-container');
    let row = document.querySelector('.item-row').cloneNode(true);

    row.querySelectorAll('input').forEach(input => input.value = '');
    row.querySelector('select').selectedIndex = 0;

    container.appendChild(row);
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-row')){
        let rows = document.querySelectorAll('.item-row');
        if(rows.length > 1){
            e.target.closest('.item-row').remove();
        }
    }
});
</script>