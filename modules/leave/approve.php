<?php
include("../../config/database.php");

$id = $_GET['id'];

$query = "UPDATE leave_requests SET status='Approved' WHERE id='$id'";
mysqli_query($conn,$query);

header("Location: leave.php");
?>