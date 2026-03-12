<?php
session_start();

if($_SESSION['role'] != 'supervisor' && $_SESSION['role'] != 'personnel'){
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

<title>Leave Request System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="container mt-4">

<h2>Leave Request System</h2>

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

<input type="text" name="leave_type" placeholder="Leave Type (Sick Leave, Vacation Leave)" class="form-control mb-2">

<input type="date" name="start_date" class="form-control mb-2">

<input type="date" name="end_date" class="form-control mb-2">

<textarea name="reason" placeholder="Reason for leave" class="form-control mb-2"></textarea>

<button name="submit_leave" class="btn btn-primary">Submit Leave Request</button>

</form>

<h4>Leave Records</h4>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Personnel</th>
<th>Leave Type</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php

if($_SESSION['role'] == 'personnel'){

$query = "SELECT leave_requests.*, personnel.fullname
FROM leave_requests
JOIN personnel ON leave_requests.user_id = personnel.id
WHERE leave_requests.user_id = 1";   // replace with correct personnel id

}else{

$query = "SELECT leave_requests.*, personnel.fullname
FROM leave_requests
JOIN personnel ON leave_requests.user_id = personnel.id";

}

$result = mysqli_query($conn,$query);

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){

echo "<tr>";
echo "<td>".$row['id']."</td>";
echo "<td>".$row['fullname']."</td>";
echo "<td>".$row['leave_type']."</td>";
echo "<td>".$row['start_date']."</td>";
echo "<td>".$row['end_date']."</td>";
$status = $row['status'];

if($status == "Pending"){
echo "<span class='badge bg-warning'>Pending</span>";
}
elseif($status == "Approved"){
echo "<span class='badge bg-success'>Approved</span>";
}
else{
echo "<span class='badge bg-danger'>Rejected</span>";

}


echo "<td>";

if($_SESSION['role'] == 'supervisor'){
    echo "<a href='approve.php?id=".$row['id']."' class='btn btn-success btn-sm'>Approve</a> ";
    echo "<a href='reject.php?id=".$row['id']."' class='btn btn-danger btn-sm'>Reject</a>";
}

echo "</td>";
echo "</tr>";


}

?>

</table>

<a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>

</html>

<?php

if(isset($_POST['submit_leave'])){

$user_id = $_POST['user_id'];
$type = $_POST['leave_type'];
$start = $_POST['start_date'];
$end = $_POST['end_date'];
$reason = $_POST['reason'];

$query = "INSERT INTO leave_requests(user_id,leave_type,start_date,end_date,reason,status)
VALUES('$user_id','$type','$start','$end','$reason','Pending')";

mysqli_query($conn,$query);

echo "<p class='text-success'>Leave Request Submitted</p>";

}

?>