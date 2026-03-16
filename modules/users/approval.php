<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['supervisor','admin']);

$query = "SELECT * FROM users WHERE status='pending'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Account Approval | GenServis</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">

<style>
.table tbody tr:hover{
background:#f8f9fa;
}
</style>

</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<div class="card border-0 shadow-sm">

<div class="card-header bg-white d-flex justify-content-between align-items-center">

<h4 class="mb-0">Pending Account Approvals</h4>

<span class="badge bg-danger">
<?= $result->num_rows ?> Pending
</span>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover table-bordered align-middle">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Username</th>
<th>Name</th>
<th>Role</th>
<th>Created</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['username']); ?></td>

<td><?= htmlspecialchars($row['fullname']); ?></td>

<td><span class="badge bg-primary">
<?= htmlspecialchars($row['role']); ?>
</span></td>

<td><?= $row['created_at']; ?></td>

<td>

<a href="approve.php?id=<?= $row['id']; ?>" class="btn btn-success btn-sm me-1">
Approve
</a>

<a href="reject.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm">
Reject
</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="6" class="text-center">No pending accounts</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div> <!-- table-responsive -->
</div> <!-- card-body -->
</div> <!-- card -->

</main>

</div>
</div>

</body>
</html>