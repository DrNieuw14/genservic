<?php
session_start();
include("../../config/database.php");

if(!isset($_SESSION['user'])){
header("Location: ../../login.php");
exit();
}
?>

<?php

if($_SESSION['role'] == 'personnel'){

$query = "SELECT attendance.*, personnel.fullname
FROM attendance
JOIN personnel ON attendance.user_id = personnel.id
ORDER BY date DESC";

}else{

$query = "SELECT attendance.*, personnel.fullname
FROM attendance
JOIN personnel ON attendance.user_id = personnel.id
ORDER BY date DESC";

}

$result = mysqli_query($conn,$query);

?>

<!DOCTYPE html>
<html>

<head>

<title>Attendance History</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="container mt-4">

<h2>Attendance History</h2>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Personnel</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php

while($row = mysqli_fetch_assoc($result)){

echo "<tr>";

echo "<td>".$row['id']."</td>";
echo "<td>".$row['fullname']."</td>";
echo "<td>".$row['date']."</td>";

echo "<td><span class='badge bg-success'>Present</span></td>";

echo "</tr>";

}

?>

</table>

<a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>
</html>