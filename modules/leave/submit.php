<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
include(__DIR__ . "/../../includes/notifications.php");

$personnel_id = $_SESSION['personnel_id'] ?? 0;
$requested_days = $_POST['days'] ?? 1;

// ===== INSERT LEAVE REQUEST =====
mysqli_query($conn, "
    INSERT INTO leave_requests (personnel_id, requested_days, status, created_at)
    VALUES ('$personnel_id', '$requested_days', 'Pending', NOW())
");

// ===== NOTIFY SUPERVISORS =====
$supervisors = mysqli_query($conn, "
    SELECT id FROM users WHERE role = 'supervisor'
");

while($sup = mysqli_fetch_assoc($supervisors)){
    createNotification(
        $conn,
        $sup['id'],
        "New leave request submitted",
        "leave"
    );
}

// ===== REDIRECT =====
header("Location: leave.php");
exit();
?>