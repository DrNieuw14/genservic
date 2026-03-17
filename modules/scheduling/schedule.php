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
<select name="user_id" id="personnel_select" class="form-control mb-3">

<?php
$query = "
SELECT 
    personnel.id, 
    users.fullname, 
    users.username,
    GROUP_CONCAT(personnel_areas.area_name SEPARATOR ', ') AS areas
FROM personnel
JOIN users ON personnel.user_id = users.id
LEFT JOIN personnel_areas ON personnel.id = personnel_areas.personnel_id
GROUP BY personnel.id
";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
    echo "<option value='".$row['id']."' data-area='".$row['areas']."'>"
        .$row['fullname']." (".$row['username'].")</option>";
}
?>

</select>

<label>Assigned Area</label>
<input type="text" id="work_area" name="work_area" class="form-control mb-3" readonly>

<select name="shift" class="form-control mb-3">
<option value="Morning">Morning (6AM-3PM)</option>
<option value="2nd Shift">2nd Shift (8AM-5PM)</option>
<option value="3rd Shift">3rd Shift (9AM-6PM)</option>
</select>

<!-- ✅ ADD HERE -->
<label>Time In</label>
<input type="time" name="time_in" class="form-control mb-3">

<label>Time Out</label>
<input type="time" name="time_out" class="form-control mb-3">

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
<th>Time In</th>
<th>Time Out</th>
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
    echo "<td>".$row['time_in']."</td>";
    echo "<td>".$row['time_out']."</td>";
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

    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];

    $query = "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date,time_in,time_out)
    VALUES('$user','$area','$shift','$date','$time_in','$time_out')";

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
    mysqli_query($conn, "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date,time_in,time_out)
    VALUES('$user_id','Auto Assigned','REST','$date',NULL,NULL)");
    continue;
}

            // shift logic
            if($shift_type == 2){

    $shift = rand(1,2);

    if($shift == 1){
        $shift_name = "Morning";
        $time_in = "06:00:00";
        $time_out = "15:00:00";
    } else {
        $shift_name = "2nd Shift";
        $time_in = "08:00:00";
        $time_out = "17:00:00";
    }

} else {

    $shift = rand(1,3);

    if($shift == 1){
        $shift_name = "Morning";
        $time_in = "06:00:00";
        $time_out = "15:00:00";
    } elseif($shift == 2){
        $shift_name = "2nd Shift";
        $time_in = "08:00:00";
        $time_out = "17:00:00";
    } else {
        $shift_name = "3rd Shift";
        $time_in = "09:00:00";
        $time_out = "18:00:00";
    }
}

            mysqli_query($conn, "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date,time_in,time_out)
VALUES('$user_id','Auto Assigned','$shift_name','$date','$time_in','$time_out')");
        }

        $current = strtotime("+1 day", $current);
        mysqli_data_seek($personnel, 0);
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("personnel_select").addEventListener("change", function() {
    let selected = this.options[this.selectedIndex];
    let area = selected.getAttribute("data-area");

    document.getElementById("work_area").value = area ? area : "No Area Assigned";
document.getElementById("work_area").style.color = area ? "black" : "red";
});

// auto-fill on load
window.onload = function() {
    let select = document.getElementById("personnel_select");
    let selected = select.options[select.selectedIndex];
    let area = selected.getAttribute("data-area");

    document.getElementById("work_area").value = area ? area : "No Area Assigned";
document.getElementById("work_area").style.color = area ? "black" : "red";
};
</script>

<script>
document.querySelector("select[name='shift']").addEventListener("change", function(){

    let shift = this.value;

    let timeIn = document.querySelector("input[name='time_in']");
    let timeOut = document.querySelector("input[name='time_out']");

    if(shift === "Morning"){
        timeIn.value = "06:00";
        timeOut.value = "15:00";
    }
    else if(shift === "2nd Shift"){
        timeIn.value = "08:00";
        timeOut.value = "17:00";
    }
    else if(shift === "3rd Shift"){
        timeIn.value = "09:00";
        timeOut.value = "18:00";
    }
});
</script>

</body>
</html>