<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']);

if(isset($_POST['approve'])){

    $conn->begin_transaction();

    try {

        $id = $_POST['request_id'];

        // 1. GET REQUEST
        $stmt = $conn->prepare("SELECT * FROM inventory_requests WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $req = $stmt->get_result()->fetch_assoc();

        if(!$req){
            throw new Exception("Invalid request");
        }

        if($req['status'] !== 'Pending'){
            throw new Exception("Already processed");
        }

        if($req['quantity'] <= 0){
            throw new Exception("Invalid quantity");
        }

        // 2. CHECK STOCK
        $stmt = $conn->prepare("SELECT quantity FROM inventory_items WHERE id=?");
        $stmt->bind_param("i", $req['item_id']);
        $stmt->execute();
        $check = $stmt->get_result()->fetch_assoc();

        if(!$check){
            throw new Exception("Item not found");
        }

        if($check['quantity'] < $req['quantity']){
            throw new Exception("Not enough stock");
        }

        // 3. DEDUCT STOCK
        $stmt = $conn->prepare("
            UPDATE inventory_items 
            SET quantity = quantity - ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $req['quantity'], $req['item_id']);
        $stmt->execute();

        // 4. GET AREA
        $stmt = $conn->prepare("
            SELECT work_area 
            FROM work_schedule 
            WHERE personnel_id = ? 
            AND schedule_date = CURDATE()
        ");
        $stmt->bind_param("i", $req['personnel_id']);
        $stmt->execute();
        $area_data = $stmt->get_result()->fetch_assoc();
        $area = $area_data['work_area'] ?? 'Unknown';

        // 5. LOG USAGE
        $stmt = $conn->prepare("
            INSERT INTO inventory_logs 
            (item_id, personnel_id, area_name, quantity_used, log_date)
            VALUES (?, ?, ?, ?, CURDATE())
        ");
        $stmt->bind_param("iisi", $req['item_id'], $req['personnel_id'], $area, $req['quantity']);
        $stmt->execute();

        // 6. UPDATE REQUEST
        $stmt = $conn->prepare("
            UPDATE inventory_requests 
            SET status='Approved', approved_at=NOW() 
            WHERE id=?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // ✅ SUCCESS
        $conn->commit();

    } catch (Exception $e) {

        $conn->rollback();

        echo "<script>alert('".$e->getMessage()."'); window.location='requests_manage.php';</script>";
        exit();
    }
}

if(isset($_POST['reject'])){
    $id = $_POST['request_id'];

   $stmt = $conn->prepare("
    UPDATE inventory_requests 
    SET status='Rejected' 
    WHERE id=?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

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
        <button class="btn btn-primary w-100">Filter</button>
    </div>

    <div class="col-md-2">
        <a href="requests_manage.php" class="btn btn-secondary w-100">Reset</a>
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

        <form method="POST" style="display:inline;">
        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
        <button name="approve" class="btn btn-success btn-sm">Approve</button>
        </form>

        <form method="POST" style="display:inline;">
        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
        <button name="reject" class="btn btn-danger btn-sm">Reject</button>
        </form>

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