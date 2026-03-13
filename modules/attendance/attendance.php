<?php
session_start();

if($_SESSION['role'] != 'personnel'){
echo "Access Denied";
exit();
}
?>
<?php
include("../../config/database.php");
?>

<!DOCTYPE html>
<html>

<head>

<title>Attendance Monitoring</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="container mt-4">

<h2>Attendance Monitoring</h2>

<form method="POST">

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

<button name="timein" class="btn btn-success">Time In</button>

<button name="timeout" class="btn btn-danger">Time Out</button>

</form>

<br>

<a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>

</html>

<?php

if(isset($_POST['timein'])){

$user_id = $_POST['user_id'];
$date = date("Y-m-d");
$time = date("H:i:s");

/* Check if already timed in today */
$check = mysqli_query($conn,"SELECT * FROM attendance 
WHERE user_id='$user_id' AND date='$date'");

if(mysqli_num_rows($check) == 0){

$status = "Present";

/* Late detection */
if($time > "08:00:00"){
$status = "Late";
}

$query = "INSERT INTO attendance(user_id,date,time_in,status)
VALUES('$user_id','$date','$time','$status')";

mysqli_query($conn,$query);

echo "<p class='text-success'>Time In recorded.</p>";

}else{

echo "<p class='text-warning'>You already timed in today.</p>";

}

}


if(isset($_POST['timeout'])){

$user_id = $_POST['user_id'];
$date = date("Y-m-d");
$time = date("H:i:s");

/* Check if time in exists */
$check = mysqli_query($conn,"SELECT * FROM attendance 
WHERE user_id='$user_id' AND date='$date'");

if(mysqli_num_rows($check) > 0){

$query = "UPDATE attendance 
SET time_out='$time' 
WHERE user_id='$user_id' AND date='$date'";

mysqli_query($conn,$query);

echo "<p class='text-danger'>Time Out recorded.</p>";

}else{

echo "<p class='text-warning'>Please Time In first.</p>";

}

}

?>