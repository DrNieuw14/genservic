<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']);

if(isset($_POST['save'])){

    $personnel_id = $_POST['personnel_id'];
    $area = $_POST['assigned_area'];

    $stmt = $conn->prepare("UPDATE personnel SET assigned_area=? WHERE id=?");
    $stmt->bind_param("si",$area,$personnel_id);
    $stmt->execute();
}

$query = "SELECT * FROM personnel ORDER BY fullname ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Assign Area | GenServis</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">

</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<div class="card border-0 shadow-sm">

<div class="card-header bg-white">
<h4 class="mb-0">Assign Personnel Area</h4>
</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-light">

<tr>
<th>ID</th>
<th>Name</th>
<th>Department</th>
<th>Current Area</th>
<th>Assign New Area</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<form method="POST">

<tr>

<td><?= $row['id'] ?></td>

<td><?= htmlspecialchars($row['fullname']) ?></td>

<td><?= htmlspecialchars($row['department']) ?></td>

<td>

<span class="badge bg-secondary">
<?= htmlspecialchars($row['assigned_area'] ?: 'Not Assigned') ?>
</span>

</td>

<td>

<select name="assigned_area" class="form-select form-select-sm">

<option value="Building A">Building A</option>
<option value="Building B">Building B</option>
<option value="Office">Office</option>
<option value="Hallway">Hallway</option>
<option value="Parking Area">Parking Area</option>

</select>

</td>

<td>

<input type="hidden" name="personnel_id" value="<?= $row['id'] ?>">

<button type="submit" name="save" class="btn btn-primary btn-sm">
Update Area
</button>

</td>

</tr>

</form>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

</main>

</div>
</div>

</body>
</html>