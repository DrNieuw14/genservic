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
SELECT 
    r.id,
    r.status,
    r.request_date,
    p.fullname,
    i.item_name,
    ri.quantity,
    i.quantity AS current_stock

FROM inventory_requests r

JOIN personnel p ON p.id = r.personnel_id
JOIN inventory_request_items ri ON ri.request_id = r.id
JOIN inventory_items i ON i.id = ri.item_id

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

$data = [];

while($row = $requests->fetch_assoc()){
    $rid = $row['id'];

    if(!isset($data[$rid])){
        $data[$rid] = [
            'id' => $row['id'],
            'fullname' => $row['fullname'],
            'request_date' => $row['request_date'],
            'status' => $row['status'],
            'items' => []
        ];
    }

    $data[$rid]['items'][] = [
    'name' => $row['item_name'],
    'qty' => $row['quantity'],
    'stock' => $row['current_stock']
    ];
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
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✅ Request approved successfully!
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                ❌ <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

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
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
<tbody>
    
<?php foreach($data as $req): ?>
<tr>
    <td><?= $req['id'] ?></td>

    <td><?= htmlspecialchars($req['fullname']) ?></td>

    <td>
        <?php foreach($req['items'] as $item): ?>
            <div>
                • <?= htmlspecialchars($item['name']) ?> 
                <strong>(<?= $item['qty'] ?>)</strong>
                <br>
                <small class="text-muted">
                    Remaining stock: <?= $item['stock'] ?>
                </small>
            </div>
        <?php endforeach; ?>
    </td>

    <td><?= $req['request_date'] ?></td>

    <td>
        <?php
        $color = "secondary";
        if($req['status'] == "Pending") $color = "warning";
        if($req['status'] == "Approved") $color = "success";
        if($req['status'] == "Rejected") $color = "danger";
        ?>
        <span class="badge bg-<?= $color ?>">
            <?= $req['status'] ?>
        </span>
    </td>

    <td>
        <?php if($req['status'] == "Pending"): ?>
            <a href="approve_request.php?id=<?= $req['id']; ?>" 
               class="btn btn-success btn-sm"
               onclick="return confirm('Approve this request?')">
               Approve
            </a>

            <button 
                class="btn btn-danger btn-sm"
                onclick="openRejectModal(<?= $req['id']; ?>)">
                Reject
            </button>
        <?php else: ?>
            <span class="text-muted">No Action</span>
        <?php endif; ?>
    </td>

</tr>
    <?php endforeach; ?>
</tbody>
</tbody>
</table>
</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="reject_request.php">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Reject Request</h5>
        </div>

        <div class="modal-body">

          <input type="hidden" name="request_id" id="reject_id">

          <label>Reason for rejection</label>
          <textarea name="reason" class="form-control" required></textarea>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Submit</button>
        </div>

      </div>
    </form>
  </div>
</div>

</body>

<script>
function openRejectModal(id){
    document.getElementById('reject_id').value = id;
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
</html>