<?php
include("../../config/database.php");
?>

<!DOCTYPE html>
<html>

<head>
<title>Work Scheduling</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">

<h2>Work Scheduling System</h2>

<form method="POST" class="mb-4">

<label>Select Personnel</label>

<select name="user_id" class="form-control mb-2">

<?php
$query = "SELECT * FROM personnel";
$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
echo "<option value='".$row['id']."'>".$row['fullname']."</option>";
}
?>

</select>

<input type="text" name="work_area" placeholder="Assigned Area (Building A, Library, etc.)" class="form-control mb-2">

<select name="shift" class="form-control mb-2">
<option>Morning</option>
<option>Afternoon</option>
<option>Evening</option>
</select>

<input type="date" name="schedule_date" class="form-control mb-2">

<button name="assign_task" class="btn btn-primary">Assign Task</button>

</form>

<h4>Work Schedule</h4>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Personnel</th>
<th>Area</th>
<th>Shift</th>
<th>Date</th>
</tr>

<?php

$query = "SELECT work_schedule.*, personnel.fullname
FROM work_schedule
JOIN personnel ON work_schedule.user_id = personnel.id";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){

echo "<tr>";
echo "<td>".$row['id']."</td>";
echo "<td>".$row['fullname']."</td>";
echo "<td>".$row['work_area']."</td>";
echo "<td>".$row['shift']."</td>";
echo "<td>".$row['schedule_date']."</td>";
echo "</tr>";

}

?>

</table>

<a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>

</html>

<?php

if(isset($_POST['assign_task'])){

$user = $_POST['user_id'];
$area = $_POST['work_area'];
$shift = $_POST['shift'];
$date = $_POST['schedule_date'];

$query = "INSERT INTO work_schedule(user_id,work_area,shift,schedule_date)
VALUES('$user','$area','$shift','$date')";

mysqli_query($conn,$query);

echo "<p class='text-success'>Work Schedule Assigned</p>";

}

?>