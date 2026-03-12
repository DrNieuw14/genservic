<?php
session_start();
include("config/database.php");

if(!isset($_SESSION['user'])){
header("Location: login.php");
exit();
}

$inventory_query = "SELECT COUNT(*) as low_stock FROM inventory WHERE quantity < 5";
$inventory_result = mysqli_query($conn,$inventory_query);
$inventory_data = mysqli_fetch_assoc($inventory_result);
$low_inventory = $inventory_data['low_stock'];

$leave_query = "SELECT COUNT(*) as leave_total FROM leave_requests WHERE status='Pending'";
$leave_result = mysqli_query($conn,$leave_query);
$leave_data = mysqli_fetch_assoc($leave_result);
$total_leave = $leave_data['leave_total'];

$date = date("Y-m-d");
$attendance_query = "SELECT COUNT(*) as present FROM attendance WHERE date='$date'";
$attendance_result = mysqli_query($conn,$attendance_query);
$attendance_data = mysqli_fetch_assoc($attendance_result);

$present_today = $attendance_data['present'];
$personnel_query = "SELECT COUNT(*) as total FROM personnel";
$personnel_result = mysqli_query($conn,$personnel_query);
$personnel_data = mysqli_fetch_assoc($personnel_result);

$total_personnel = $personnel_data['total'];

$chart_query = "SELECT date, COUNT(*) as total 
FROM attendance 
GROUP BY date 
ORDER BY date ASC 
LIMIT 7";

$chart_result = mysqli_query($conn,$chart_query);

$dates = [];
$totals = [];

while($row = mysqli_fetch_assoc($chart_result)){
$dates[] = $row['date'];
$totals[] = $row['total'];
}

/* Leave Status Chart Query */

$leave_chart_query = "
SELECT status, COUNT(*) as total 
FROM leave_requests 
GROUP BY status
";

$leave_chart_result = mysqli_query($conn,$leave_chart_query);

$leave_labels = [];
$leave_totals = [];

while($row = mysqli_fetch_assoc($leave_chart_result)){
$leave_labels[] = $row['status'];
$leave_totals[] = $row['total'];
}


?>

<!DOCTYPE html>
<html>

<head>

<title>GENSERVIS Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
overflow-x:hidden;
}

.sidebar{
height:100vh;
background:#198754;
color:white;
padding-top:20px;
}

.sidebar a{
color:white;
display:block;
padding:10px;
text-decoration:none;
}

.sidebar a:hover{
background:#157347;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark bg-success">
<div class="container-fluid">

<span class="navbar-brand">GENSERVIS</span>

<span class="text-white">
Welcome <?php echo $_SESSION['user']; ?> (<?php echo $_SESSION['role']; ?>)
</span>

<a href="logout.php" class="btn btn-light btn-sm">Logout</a>

</div>
</nav>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 sidebar">

<h5 class="text-center">Menu</h5>

<a href="dashboard.php">Dashboard</a>

<?php if($_SESSION['role'] == 'admin'){ ?>

<a href="modules/personnel/personnel.php">Personnel</a>
<a href="modules/inventory/inventory.php">Inventory</a>
<a href="reports/attendance_report.php">Reports</a>

<?php } ?>

<?php if($_SESSION['role'] == 'supervisor'){ ?>

<a href="modules/leave/leave.php">Leave Approval</a>
<a href="reports/attendance_report.php">Reports</a>

<?php } ?>

<?php if($_SESSION['role'] == 'personnel'){ ?>

<a href="modules/attendance/attendance.php">Attendance</a>
<a href="modules/attendance/history.php">Attendance History</a>
<a href="modules/leave/leave.php">Submit Leave</a>


<?php } ?>

</div>

<div class="col-md-10 p-4">

<h3>Dashboard Overview</h3>

<?php if($_SESSION['role'] == 'admin'){ ?>

<div class="row">

<div class="col-md-3">
<div class="card bg-primary text-white">
<div class="card-body">
<h5>Total Personnel</h5>
<h2><?php echo $total_personnel; ?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-success text-white">
<div class="card-body">
<h5>Present Today</h5>
<h2><?php echo $present_today; ?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-warning text-dark">
<div class="card-body">
<h5>Pending Leave</h5>
<h2><?php echo $total_leave; ?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card bg-danger text-white">
<div class="card-body">
<h5>Low Inventory</h5>
<h2><?php echo $low_inventory; ?></h2>
</div>
</div>
</div>

</div>

<div class="row mt-4">

<div class="col-md-6">

<div class="card">

<div class="card-header">
Attendance Trend
</div>

<div class="card-body">

<canvas id="attendanceChart"></canvas>

</div>

</div>

</div>

</div>

<div class="row mt-4">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header">
Leave Request Status
</div>

<div class="card-body">

<canvas id="leaveChart"></canvas>

</div>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

</div>

<script>

const chartElement = document.getElementById('attendanceChart');

if(chartElement){

const ctx = chartElement;

new Chart(ctx, {
type: 'line',
data: {
labels: <?php echo json_encode($dates); ?>,
datasets: [{
label: 'Daily Attendance',
data: <?php echo json_encode($totals); ?>,
borderWidth: 2,
tension: 0.3
}]
},
options: {
responsive: true,
scales: {
y: {
beginAtZero: true
}
}
}
});

}

</script>

<script>

const leaveChartElement = document.getElementById('leaveChart');

if(leaveChartElement){

const labels = <?php echo json_encode($leave_labels); ?>;
const totals = <?php echo json_encode($leave_totals); ?>;

let colors = [];

labels.forEach(status => {
    if(status === "Pending"){
        colors.push("#ffc107");
    } else if(status === "Approved"){
        colors.push("#198754");
    } else if(status === "Rejected"){
        colors.push("#dc3545");
    }
});

new Chart(leaveChartElement, {
type: 'pie',
data: {
labels: labels,
datasets: [{
data: totals,
backgroundColor: colors
}]
},
options:{
responsive:true
}
});

}








</script>



</body>

</html>