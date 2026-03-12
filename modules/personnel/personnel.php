<?php
include("../../config/database.php");
?>

<!DOCTYPE html>
<html>

<head>

<title>Personnel Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="container mt-4">

<h2>Utility Personnel</h2>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Name</th>
<th>Position</th>
<th>Assigned Area</th>
</tr>

<?php

$query = "SELECT * FROM personnel";
$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){

echo "<tr>";
echo "<td>".$row['id']."</td>";
echo "<td>".$row['fullname']."</td>";
echo "<td>".$row['position']."</td>";
echo "<td>".$row['assigned_area']."</td>";
echo "</tr>";

}

?>

</table>

<a href="../../dashboard.php" class="btn btn-secondary">Back</a>

</body>

</html>