<?php
include("../../config/database.php");

$id = $_GET['id'];

mysqli_query($conn, "UPDATE leave_requests SET status='Rejected' WHERE id='$id'");

header("Location: leave.php");
?>