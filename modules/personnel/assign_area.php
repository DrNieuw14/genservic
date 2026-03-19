<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['admin','supervisor']);

if(isset($_POST['remove_area'])){

    $personnel_id = $_POST['personnel_id'];
    $area_name = $_POST['remove_area'];

    $stmt = $conn->prepare("
        DELETE FROM personnel_areas 
        WHERE personnel_id=? AND area_name=?
    ");

    $stmt->bind_param("is",$personnel_id,$area_name);
    $stmt->execute();

    header("Location: assign_area.php");
    exit();
}

if(isset($_POST['save'])){

    $personnel_id = $_POST['personnel_id'];
    $areas = $_POST['assigned_area'] ?? [];

    if(empty($areas)){
        header("Location: assign_area.php");
        exit();
    }

    foreach($areas as $area){

        $check = $conn->prepare("
            SELECT 1 FROM personnel_areas 
            WHERE personnel_id=? AND area_name=? LIMIT 1
        ");
        $check->bind_param("is",$personnel_id,$area);
        $check->execute();
        $result_check = $check->get_result();

        if($result_check->num_rows == 0){

            $stmt = $conn->prepare("
                INSERT INTO personnel_areas (personnel_id, area_name) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("is",$personnel_id,$area);
            $stmt->execute();
        }
    }

    header("Location: assign_area.php");
    exit();
}

if(isset($_POST['add_area'])){

    $area_name = trim($_POST['area_name']);

    if($area_name !== ''){
        $stmt = $conn->prepare("INSERT INTO areas (area_name) VALUES (?)");
        $stmt->bind_param("s",$area_name);
        $stmt->execute();
    }

    header("Location: assign_area.php");
    exit();
}

$conn->query("SET SESSION group_concat_max_len = 10000");

$query = "
SELECT 
    personnel.id,
    users.first_name,
    users.middle_initial,
    users.last_name,
    users.gender,
    personnel.department,
    COALESCE(GROUP_CONCAT(personnel_areas.area_name SEPARATOR ', '), '') AS assigned_area
FROM personnel
INNER JOIN users ON personnel.user_id = users.id
LEFT JOIN personnel_areas ON personnel.id = personnel_areas.personnel_id
WHERE users.first_name IS NOT NULL 
AND users.first_name != ''
AND users.status = 'approved'
GROUP BY 
    personnel.id,
    users.first_name,
    users.middle_initial,
    users.last_name,
    users.gender,
    personnel.department
ORDER BY users.first_name ASC
";

$result = $conn->query($query);

if(!$result){
    die("Query Error: " . $conn->error);
}
$area_list = $conn->query("SELECT area_name FROM areas ORDER BY area_name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Assign Area | GenServis</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">

<style>

.area-tag{
    display:inline-flex;
    align-items:center;
    background:#495057;
    color:white;
    border-radius:20px;
    padding:4px 10px;
    margin:3px;
    font-size:13px;
}

.area-tag button{
    background:none;
    border:none;
    color:#ff4d4d;
    font-weight:bold;
    margin-left:6px;
    cursor:pointer;
}

.area-tag button:hover{
    color:#ff0000;
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

<h4 class="mb-0">Assign Personnel Area</h4>

<button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addAreaModal">
+ Add Area
</button>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-light">

<tr>
<th>ID</th>
<th>Name</th>
<th>Gender</th>
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

<td>
<?php
$fullname = trim(
    ($row['first_name'] ?? '') . ' ' .
    ($row['middle_initial'] ? $row['middle_initial'] . '. ' : '') .
    ($row['last_name'] ?? '')
);

echo !empty($fullname)
    ? htmlspecialchars($fullname)
    : "<span class='text-muted'>No Name</span>";
?>
</td>

<td>
<?php
$genderClass = "secondary";

if ($row['gender'] === "Male") {
    $genderClass = "primary"; // blue
} elseif ($row['gender'] === "Female") {
    $genderClass = "dark"; // dark gray
}
?>

<span class="badge bg-<?= $genderClass ?>">
<?= htmlspecialchars($row['gender']) ?>
</span>
</td>

<td><?= htmlspecialchars($row['department']) ?></td>

<td>

<?php
if(!empty($row['assigned_area'])){

    $assigned_areas = explode(",", $row['assigned_area']);

foreach($assigned_areas as $area){

    $area = trim($area);

    echo "
    <span class='area-tag'>
        ".htmlspecialchars($area)."
        <button 
            type='submit'
            name='remove_area'
            value='".htmlspecialchars($area)."'
            title='Remove area'>
            ×
        </button>
    </span>
    ";
}

}else{

    echo "<span class='badge bg-secondary'>Not Assigned</span>";

}
?>

</td>

<td>

<?php
$assignedArray = !empty($row['assigned_area']) 
    ? array_map('trim', explode(",", $row['assigned_area'])) 
    : [];
?>

<select name="assigned_area[]" class="form-select form-select-sm" multiple>

<?php
$area_list->data_seek(0);
while($area = $area_list->fetch_assoc()):
?>

<option value="<?= htmlspecialchars($area['area_name']) ?>"
<?= in_array($area['area_name'], $assignedArray) ? 'selected' : '' ?>>
<?= htmlspecialchars($area['area_name']) ?>
</option>

<?php endwhile; ?>

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
<!-- Add Area Modal -->

<div class="modal fade" id="addAreaModal" tabindex="-1">

<div class="modal-dialog">
<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h5 class="modal-title">Add New Area</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="text" name="area_name" class="form-control"
placeholder="Enter new area name" required>

</div>

<div class="modal-footer">

<button type="submit" name="add_area" class="btn btn-success">
Save Area
</button>

</div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>