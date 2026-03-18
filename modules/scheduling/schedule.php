<?php
require_once '../../config/database.php';
require_once '../../config/layout.php';
require_once '../../config/auth.php';

// restrict access
require_role(['supervisor','admin']);

$edit_mode = false;
$edit_data = null;

if(isset($_GET['edit_id'])){
    $edit_mode = true;
    $edit_id = $_GET['edit_id'];

    $stmt = $conn->prepare("SELECT * FROM work_schedule WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <style>
.table td, .table th {
    vertical-align: middle;
}

tr:hover {
    background-color: #f1f3f5;
}
</style>
<meta charset="UTF-8">
<title>Work Scheduling</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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



<?php if(isset($_POST['assign_task'])): ?>
    
    <div class="alert alert-success">Work Schedule Assigned</div>
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
    COALESCE(GROUP_CONCAT(personnel_areas.area_name SEPARATOR ', '), 'No Area Assigned') AS areas
FROM personnel
JOIN users ON personnel.user_id = users.id
LEFT JOIN personnel_areas ON personnel.id = personnel_areas.personnel_id
GROUP BY personnel.id
";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
    echo "<option value='".$row['id']."' 
    ".($edit_mode && $edit_data['user_id']==$row['id']?'selected':'')."
    data-area='".htmlspecialchars($row['areas'], ENT_QUOTES)."'>"
    .$row['fullname']." (".$row['username'].")</option>";
        
}
?>

</select>

<label>Assigned Area</label>
<input type="text" id="work_area" name="work_area" class="form-control mb-3" readonly
value="<?= $edit_mode ? $edit_data['work_area'] : '' ?>">

<select name="shift" id="shift_select" class="form-control mb-3">
<option value="Morning" <?= ($edit_mode && $edit_data['shift']=='Morning')?'selected':'' ?>>Morning (6AM-3PM)</option>
<option value="2nd Shift" <?= ($edit_mode && $edit_data['shift']=='2nd Shift')?'selected':'' ?>>2nd Shift (8AM-5PM)</option>
<option value="3rd Shift" <?= ($edit_mode && $edit_data['shift']=='3rd Shift')?'selected':'' ?>>3rd Shift (10AM-7PM)</option>
<option value="Morning Shift" <?= ($edit_mode && $edit_data['shift']=='Morning Shift')?'selected':'' ?>>Morning Shift (Custom)</option>
</select>

<!-- ✅ ADD HERE -->
<label>Time In</label>
<input type="text" id="time_in" name="time_in" class="form-control mb-3"
value="<?= $edit_mode ? date('H:i', strtotime($edit_data['time_in'])) : '' ?>">

<label>Time Out</label>
<input type="text" id="time_out" name="time_out" class="form-control mb-3"
value="<?= $edit_mode ? date('H:i', strtotime($edit_data['time_out'])) : '' ?>">

<?php if($edit_mode): ?>
<label>Schedule Date</label>
<input type="date" name="schedule_date" class="form-control mb-2"
value="<?= $edit_data['schedule_date'] ?>">
<?php endif; ?>

<!-- ✅ ADD HERE -->
<hr>

<h5>Schedule Settings</h5>

<label>Select Schedule Range</label>
<input type="text" id="date_range" class="form-control mb-3" placeholder="Select Date Range">

<input type="hidden" name="start_date" id="start_date">
<input type="hidden" name="end_date" id="end_date">

<label class="fw-bold d-block mb-2">Select Rest Day</label>

<div class="d-flex flex-wrap gap-2">
</div>

<?php
$days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];

foreach($days as $day){
    echo "<div class='form-check form-check-inline'>
        <input class='form-check-input' type='checkbox' name='rest_days[]' value='$day'>
        <label class='form-check-label'>$day</label>
    </div>";
}
?>

<!-- BUTTONS -->
<?php if($edit_mode): ?>
    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
    <button name="update_task" class="btn btn-warning btn-sm">Update Task</button>
<?php else: ?>
    <button name="assign_task" class="btn btn-primary btn-sm me-2">Assign Task</button>
<?php endif; ?>


</form>
</div>
</div>

<div class="card border-0 shadow-sm">

<div class="card-header bg-white">
    <h5 class="mb-0">Work Schedule</h5>
</div>

<div class="card-body">

<div class="card-body">

<!-- 🔍 SEARCH BAR -->
<div class="row mb-3">
    
    <div class="col-md-6">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search personnel, area, shift...">
    </div>

    <div class="col-md-3">
        <select id="filterShift" class="form-control">
            <option value="">All Shifts</option>
            <option value="Morning">Morning</option>
            <option value="2nd Shift">2nd Shift</option>
            <option value="3rd Shift">3rd Shift</option>
            <option value="REST">REST</option>
        </select>
    </div>

</div>

<div class="table-responsive">

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Area</th>
<th>Shift</th>
<th>Date</th>
<th>Time In</th>
<th>Time Out</th>
<th>Action</th>
</tr>

</thead>

<?php
$query = "
SELECT work_schedule.*, users.fullname, personnel.id AS personnel_id
FROM work_schedule
JOIN personnel ON work_schedule.user_id = personnel.id
JOIN users ON personnel.user_id = users.id
ORDER BY users.fullname, schedule_date ASC
";

$result = mysqli_query($conn,$query);
?>

<tbody>

<?php
$current_name = "";

while($row = mysqli_fetch_assoc($result)){

    // SHOW NAME ONLY ONCE
    if($current_name != $row['fullname']){
        echo "<tr style='background:#e9ecef; font-weight:bold;'>
        <td colspan='7'>
            <div class='d-flex justify-content-between align-items-center'>

                <span>
                    👤 ".$row['fullname']."
                </span>

                <a href='../personnel/personnel_schedule.php?id=".$row['personnel_id']."' 
                   class='btn btn-sm btn-outline-primary'>
                   View Schedule
                </a>

            </div>
        </td>
      </tr>";

        $current_name = $row['fullname'];
    }

    // FORMAT DATE
    $date = date("M d, Y", strtotime($row['schedule_date']));

    // FORMAT TIME
    $time_in = $row['time_in'] ? date("h:i A", strtotime($row['time_in'])) : '-';
    $time_out = $row['time_out'] ? date("h:i A", strtotime($row['time_out'])) : '-';

    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['work_area']."</td>";

    if($row['shift'] == 'REST'){
        echo "<td><span class='badge bg-danger'>REST</span></td>";
    } else {
        echo "<td><span class='badge bg-success'>".$row['shift']."</span></td>";
    }

    echo "<td>".$date."</td>";
    echo "<td>".$time_in."</td>";
    echo "<td>".$time_out."</td>";

    echo "<td>
    <a href='schedule.php?edit_id=".$row['id']."' class='btn btn-warning btn-sm'>Edit</a>
    </td>";

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

    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $rest_days = $_POST['rest_days'] ?? [];

    if(empty($start_date) || empty($end_date)){
        echo "<div class='alert alert-danger'>Please select start and end date</div>";
        return;
    }

    // ✅ PREPARE ONCE
    $stmt = $conn->prepare("INSERT INTO work_schedule(user_id,work_area,shift,schedule_date,time_in,time_out) VALUES(?,?,?,?,?,?)");

    $current = strtotime($start_date);
    $end = strtotime($end_date);

    while($current <= $end){

        $date = date("Y-m-d", $current);
        $day_name = date("l", $current);

        // prevent duplicate
        $check = mysqli_query($conn, "
        SELECT * FROM work_schedule 
        WHERE user_id='$user' 
        AND schedule_date='$date'
        ");

        if(mysqli_num_rows($check) > 0){
            $current = strtotime("+1 day", $current);
            continue;
        }

        // REST OR SHIFT
        if(in_array($day_name, $rest_days)){
            $shift_name = "REST";
            $time_in = NULL;
            $time_out = NULL;
        } else {

            $shift_name = $shift;

            if($shift == "Morning"){
                $time_in = "06:00:00";
                $time_out = "15:00:00";
            }
            elseif($shift == "2nd Shift"){
                $time_in = "08:00:00";
                $time_out = "17:00:00";
            }
            elseif($shift == "3rd Shift"){
                $time_in = "10:00:00";
                $time_out = "19:00:00";
            }
            elseif($shift == "Morning Shift"){
                $time_in = "07:00:00";
                $time_out = "16:00:00";
            }
        }

        // ✅ SINGLE INSERT
        // handle NULL properly
if($time_in === NULL){
    $time_in = NULL;
}
if($time_out === NULL){
    $time_out = NULL;
}

// insert
// FIX TIME FORMAT FIRST
if($time_in !== NULL){
    $time_in = date("H:i:s", strtotime($time_in));
}
if($time_out !== NULL){
    $time_out = date("H:i:s", strtotime($time_out));
}

// THEN bind
$stmt->bind_param("isssss",$user,$area,$shift_name,$date,$time_in,$time_out);
$stmt->execute();

        $current = strtotime("+1 day", $current);
    }

    echo "<script>alert('Schedule Generated Successfully'); window.location='schedule.php';</script>";
}

if(isset($_POST['update_task'])){

    $id = $_POST['id'];
    $user = $_POST['user_id'];
    $area = $_POST['work_area'];
    $shift = $_POST['shift'];
    $date = $_POST['schedule_date'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];

    $stmt = $conn->prepare("UPDATE work_schedule 
        SET user_id=?, work_area=?, shift=?, schedule_date=?, time_in=?, time_out=? 
        WHERE id=?");

    $stmt->bind_param("isssssi",$user,$area,$shift,$date,$time_in,$time_out,$id);

    if($stmt->execute()){
        echo "<script>alert('Schedule Updated Successfully'); window.location='schedule.php';</script>";
    }
}
?>
</body>
<script>
document.getElementById("personnel_select").addEventListener("change", function() {
    let selected = this.options[this.selectedIndex];
    let area = selected.getAttribute("data-area");

    let input = document.getElementById("work_area");

    if(area && area !== "NULL"){
        input.value = area;
        input.style.color = "black";
    } else {
        input.value = "No Area Assigned";
        input.style.color = "red";
    }
});

// auto-load on page open
window.onload = function() {
    document.getElementById("personnel_select").dispatchEvent(new Event("change"));

    setTimeout(() => {
        document.getElementById("shift_select").dispatchEvent(new Event("change"));
    }, 300);
};
</script>

<script>
    
function generatePreview(){

    let start = document.getElementById("start_date").value;
    let end = document.getElementById("end_date").value;
    let shift = document.getElementById("shift_select").value;

    let restDays = [];
    document.querySelectorAll("input[name='rest_days[]']:checked")
        .forEach(cb => restDays.push(cb.value));

    let previewBox = document.getElementById("schedule_preview");

    if(!start || !end){
        previewBox.innerHTML = "<small class='text-muted'>Select dates first...</small>";
        return;
    }

    let current = new Date(start);
    let endDate = new Date(end);

    let html = "";
    let workCount = 0;
    let restCount = 0;

    while(current <= endDate){

        let dateStr = current.toISOString().split('T')[0];
        let dayName = current.toLocaleDateString('en-US', { weekday: 'long' });
        let dayShort = current.toLocaleDateString('en-US', { weekday: 'short' });

        if(restDays.includes(dayName)){
            restCount++;

            html += `<div class="d-flex justify-content-between border-bottom py-1">
                <span>${dayShort} | ${dateStr}</span>
                <span class="badge bg-danger">REST</span>
            </div>`;
        } else {
            workCount++;

            html += `<div class="d-flex justify-content-between border-bottom py-1">
                <span>${dayShort} | ${dateStr}</span>
                <span class="badge bg-success">${shift}</span>
            </div>`;
        }

        current.setDate(current.getDate() + 1);
    }

    previewBox.innerHTML = `
    <div class="mb-2 fw-bold">
        Work Days: ${workCount} | Rest Days: ${restCount}
    </div>
    ${html}
    `;
}

// TRIGGERS
document.getElementById("start_date").addEventListener("change", generatePreview);
document.getElementById("end_date").addEventListener("change", generatePreview);
document.getElementById("shift_select").addEventListener("change", generatePreview);

document.querySelectorAll("input[name='rest_days[]']").forEach(cb => {
    cb.addEventListener("change", generatePreview);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
// DATE RANGE PICKER
flatpickr("#date_range", {
    mode: "range",
    dateFormat: "Y-m-d",

    onChange: function(selectedDates) {
        if(selectedDates.length === 2){

            let start = selectedDates[0].toISOString().split('T')[0];
            let end = selectedDates[1].toISOString().split('T')[0];

            document.getElementById("start_date").value = start;
            document.getElementById("end_date").value = end;

            generatePreview();
        }
    }
});

// TIME PICKERS (SEPARATE)
flatpickr("#time_in", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K",
    time_24hr: false
});

flatpickr("#time_out", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K",
    time_24hr: false
});

document.getElementById("shift_select").addEventListener("change", function(){

    let shift = this.value;

    let timeIn = document.getElementById("time_in")._flatpickr;
    let timeOut = document.getElementById("time_out")._flatpickr;

    if(!timeIn || !timeOut) return;

    if(shift === "Morning"){
        timeIn.setDate("06:00");
        timeOut.setDate("15:00");
    }
    else if(shift === "2nd Shift"){
        timeIn.setDate("08:00");
        timeOut.setDate("17:00");
    }
    else if(shift === "3rd Shift"){
        timeIn.setDate("10:00");
        timeOut.setDate("19:00");
    }
    else if(shift === "Morning Shift"){
        timeIn.setDate("07:00");
        timeOut.setDate("16:00");
    }

});

document.getElementById("time_out").addEventListener("change", function(){

    let timeIn = document.getElementById("time_in").value;
    let timeOut = this.value;

    if(timeIn && timeOut && timeOut <= timeIn){
        alert("Time Out must be later than Time In");
        this.value = "";
    }

});
</script>

<script>
document.getElementById("searchInput").addEventListener("keyup", function(){

    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");

    let showGroup = false;

    rows.forEach(row => {

        let text = row.innerText.toLowerCase();

        // detect header row (name row)
        let isHeader = row.querySelector("a") !== null;

        if(isHeader){
            if(text.includes(value)){
                row.style.display = "";
                showGroup = true;
            } else {
                row.style.display = "none";
                showGroup = false;
            }
        } else {
            if(text.includes(value) || showGroup){
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }

    });

});
</script>


<script>
document.getElementById("filterShift").addEventListener("change", function(){

    let filterValue = this.value.toLowerCase();
    let searchValue = document.getElementById("searchInput").value.toLowerCase();

    let rows = document.querySelectorAll("tbody tr");

    let showGroup = false;

    rows.forEach(row => {

        let text = row.innerText.toLowerCase();
        let isHeader = row.querySelector("a") !== null;

        let matchSearch = text.includes(searchValue);
        let matchFilter = filterValue === "" || text.includes(filterValue);

        if(isHeader){
            if(matchSearch && matchFilter){
                row.style.display = "";
                showGroup = true;
            } else {
                row.style.display = "none";
                showGroup = false;
            }
        } else {
            if((matchSearch && matchFilter) || showGroup){
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }

    });

});
</script>


</html>