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
<title>Account Approval</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 p-4">

<h3>Pending Account Approvals</h3>

<table class="table table-bordered table-striped">
<thead>
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
<td><?= htmlspecialchars($row['role']); ?></td>
<td><?= $row['created_at']; ?></td>

<td>

<a href="approve.php?id=<?= $row['id']; ?>" class="btn btn-success btn-sm">
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

</main>
</div>
</div>
</body>
</html>