<?php
require_once '../../config/database.php';
require_once '../../config/layout.php';
require_once '../../config/auth.php';

// restrict access
require_role(['supervisor','admin']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Work Scheduling</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>

<body>




<div class="container-fluid app-layout">
<div class="row">

        <!-- Sidebar -->
        <?php render_sidebar($_SESSION['role']); ?>

        <!-- Main Content -->
        <main class="col-lg-10 col-md-9 p-4">

<div class="card border-0 shadow-sm mb-4">

<div class="card-header bg-white">
    <h4 class="mb-0">Work Scheduling System</h4>
</div>

<div class="card-body">

<?php if(isset($_POST['auto_generate']) && (empty($_POST['start_date']) || empty($_POST['end_date']))): ?>
    <div class="alert alert-danger">Please select start and end date</div>
<?php endif; ?>

<?php if(isset($_POST['assign_task'])): ?>
    <div class="alert alert-success">Work Schedule Assigned</div>
<?php endif; ?>

<?php if(isset($_POST['auto_generate']) && !empty($_POST['start_date']) && !empty($_POST['end_date'])): ?>
    <div class="alert alert-success">Auto Schedule Generated</div>
<?php endif; ?>

<form method="POST">

<label>Select Personnel</label>
<select name="user_id" class="form-control mb-3">
<?php
$query = "
SELECT personnel.id, users.fullname, users.username 
FROM personnel
JOIN users ON personnel.user_id = users.id
";
$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
echo "<option value='".$row['id']."'>".$row['fullname']." (".$row['username'].")</option>";
}
?>
</select>

<input type="text" name="work_area" placeholder="Assigned Area" class="form-control mb-3">

<select name="shift" class="form-control mb-3">
<option>Morning</option>
<option>Afternoon</option>
<option>Evening</option>
</select>

<input type="date" name="schedule_date" class="form-control mb-2">

<!-- ✅ ADD HERE -->
<hr>

<h5>Auto Schedule Settings</h5>

<select name="shift_type" class="form-control mb-3">
<option value="2">2 Shift</option>
<option value="3">3 Shift</option>
</select>

<label>Start Date</label>
<input type="date" name="start_date" class="form-control mb-3">

<label>End Date</label>
<input type="date" name="end_date" class="form-control mb-3">

<label>Rest Day</label>
<select name="rest_day" class="form-control mb-3">
<option value="Sunday">Sunday</option>
<option value="Saturday">Saturday</option>
</select>

<!-- BUTTONS -->
<button name="assign_task" class="btn btn-primary btn-sm me-2">Assign Task</button>
<button name="auto_generate" class="btn btn-success btn-sm">Auto Generate</button>

</form>
</div>
</div>

<div class="card border-0 shadow-sm">

<div class="card-header bg-white">
    <h5 class="mb-0">Work Schedule</h5>
</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Personnel</th>
<th>Area</th>
<th>Shift</th>
<th>Date</th>
</tr>
</thead>

<?php
$query = "
SELECT work_schedule.*, users.fullname
FROM work_schedule
JOIN personnel ON work_schedule.user_id = personnel.id
JOIN users ON personnel.user_id = users.id
";

$result = mysqli_query($conn,$query);
?>
<tbody>

<?php
while($row = mysqli_fetch_assoc($result)){
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['fullname']."</td>";
    echo "<td>".$row['work_area']."</td>";

    if($row['shift'] == 'REST'){
        echo "<td><span class='badge bg-danger'>REST</span></td>";
    } else {
        echo "<td><span class='badge bg-success'>".$row['shift']."</span></td>";
    }

    echo "<td>".$row['schedule_date']."</td>";
    echo "</tr>";
}
?>

</tbody>
</table>
</div>
</div>
</div>



</main>
    </div>
</div>

<?php


if(isset($_POST['assign_task'])){

    $user = $_POST['user_id'];
    $area = $_POST['work_area'];
    $shift = $_POST['shift'];
    $date = $_POST['schedule_date'];

    $query = "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date)
    VALUES('$user','$area','$shift','$date')";

    mysqli_query($conn,$query);

 
}

// ✅ CONTINUE SAME PHP BLOCK
if(isset($_POST['auto_generate'])){

    $shift_type = $_POST['shift_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $rest_day = $_POST['rest_day'];

    // stop if no dates
    if(empty($start_date) || empty($end_date)){
        return;
    }

    $personnel = mysqli_query($conn, "SELECT id FROM personnel");

    $current = strtotime($start_date);
    $end = strtotime($end_date);

    while($current <= $end){

        $date = date("Y-m-d", $current);
        $day_name = date("l", $current);

        while($row = mysqli_fetch_assoc($personnel)){

            $user_id = $row['id'];

            // duplicate check
            $check = mysqli_query($conn, "
            SELECT * FROM work_schedule 
            WHERE user_id='$user_id' 
            AND schedule_date='$date'
            ");

            if(mysqli_num_rows($check) > 0){
                continue;
            }

            // rest day
            if($day_name == $rest_day){
                mysqli_query($conn, "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date)
                VALUES('$user_id','Auto Assigned','REST','$date')");
                continue;
            }

            // shift logic
            if($shift_type == 2){
                $shift = rand(1,2);
                $shift_name = ($shift == 1) 
                    ? "Morning (6AM-2PM)" 
                    : "Afternoon (2PM-10PM)";
            } else {
                $shift = rand(1,3);
                if($shift == 1){
                    $shift_name = "Morning (6AM-2PM)";
                } elseif($shift == 2){
                    $shift_name = "Afternoon (2PM-10PM)";
                } else {
                    $shift_name = "Night (10PM-6AM)";
                }
            }

            mysqli_query($conn, "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date)
            VALUES('$user_id','Auto Assigned','$shift_name','$date')");
        }

        $current = strtotime("+1 day", $current);
        mysqli_data_seek($personnel, 0);
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>