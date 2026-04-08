<?php
include("../../config/database.php");

$id = $_GET['id'];

// GET REQUEST DETAILS
$get = mysqli_query($conn, "SELECT * FROM leave_requests WHERE id='$id'");
$data = mysqli_fetch_assoc($get);

$personnel_id = $data['personnel_id'];
$requested_days = $data['requested_days'];

// APPROVE REQUEST
mysqli_query($conn, "UPDATE leave_requests SET status='Approved' WHERE id='$id'");

// DEDUCT CTO
mysqli_query($conn, "
UPDATE cto_summary 
SET equivalent_days = equivalent_days - $requested_days
WHERE personnel_id = '$personnel_id' 
AND status='Approved'
LIMIT 1
");

header("Location: leave.php");

if($requested_days > $available_days){
    die("Invalid request");
}

?>