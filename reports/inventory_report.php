<?php
include("../config/database.php");
?>

<!DOCTYPE html>
<html>

<head>

<title>Attendance Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="container mt-4">

<h2>Attendance Report</h2>

<button onclick="window.print()" class="btn btn-primary mb-3">
Print Report
</button>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Personnel</th>
<th>Date</th>
<th>Time In</th>
<th>Time Out</th>
</tr>

<?php

$query = "SELECT attendance.*, personnel.fullname
FROM attendance
JOIN personnel ON attendance.user_id = personnel.id
ORDER BY attendance.date DESC";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){

echo "<tr>";
echo "<td>".$row['id']."</td>";
echo "<td>".$row['fullname']."</td>";
echo "<td>".$row['date']."</td>";
echo "<td>".$row['time_in']."</td>";
echo "<td>".$row['time_out']."</td>";
echo "</tr>";

}

?>

</table>

<a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>

</html>