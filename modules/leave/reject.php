<?php
session_start();
include("../../config/database.php");
include("../../config/audit.php");

$id = $_GET['id'];
$approver = $_SESSION['user_id'] ?? 0;

// ===== REJECT REQUEST =====
mysqli_query($conn, "
    UPDATE leave_requests 
    SET status='Rejected'
    WHERE id='$id'
");

// ===== LOG ACTION (MUST BE BEFORE REDIRECT) =====
logAction($conn, $approver, "Rejected leave ID: $id", "Leave");

// ===== REDIRECT =====
header("Location: leave.php");
exit();
?>