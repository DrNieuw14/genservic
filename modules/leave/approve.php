<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
include(__DIR__ . "/../../config/audit.php");
include(__DIR__ . "/../../includes/notifications.php");

$id = $_GET['id'];
$approver = $_SESSION['user_id'] ?? 0;

// ===== GET REQUEST DETAILS =====
$get = mysqli_query($conn, "SELECT * FROM leave_requests WHERE id='$id'");
$data = mysqli_fetch_assoc($get);

$personnel_id = $data['personnel_id'];
$requested_days = $data['requested_days'];

// Convert to hours
$hours_to_deduct = $requested_days * 8;

// ===== CHECK AVAILABLE CTO =====
$check = mysqli_query($conn, "
    SELECT 
        SUM(equivalent_days) AS total_days,
        SUM(used_hours)/8 AS used_days
    FROM cto_summary
    WHERE personnel_id = '$personnel_id'
");

$cto = mysqli_fetch_assoc($check);

$total_days = $cto['total_days'] ?? 0;
$used_days = $cto['used_days'] ?? 0;
$available_days = $total_days - $used_days;

// ===== VALIDATION =====
if($requested_days > $available_days){
    die("Not enough CTO balance");
}

// ===== APPROVE REQUEST =====
mysqli_query($conn, "
    UPDATE leave_requests 
    SET status='Approved', approved_by='$approver', approved_at=NOW()
    WHERE id='$id'
");

logAction($conn, $approver, "Approved leave ID: $id", "Leave");

// ===== DEDUCT CTO =====
mysqli_query($conn, "
    UPDATE cto_summary
    SET used_hours = used_hours + $hours_to_deduct
    WHERE personnel_id = '$personnel_id'
");

// ===== GET USER ID =====
$query = mysqli_query($conn, "
    SELECT id FROM users 
    WHERE personnel_id = '$personnel_id'
    LIMIT 1
");

$user = mysqli_fetch_assoc($query);
$user_id = $user['id'] ?? 0;

// ===== CREATE NOTIFICATION =====
if($user_id){
    createNotification(
        $conn,
        $user_id,
        "Your leave request has been APPROVED",
        "leave"
    );
}

// ✅ VERY IMPORTANT — NO OUTPUT BEFORE THIS
header("Location: leave.php");
exit();