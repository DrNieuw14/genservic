<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

require_role(['admin', 'supervisor']);

$request_id = $_GET['id'] ?? 0;

if (!$request_id) {
    die("Invalid request ID");
}

$check = $conn->prepare("SELECT status FROM inventory_requests WHERE id = ?");
$check->bind_param("i", $request_id);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if (!$res) {
    die("Request not found.");
}

if ($res['status'] !== 'pending') {
    die("Request already processed.");
}

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("User not authenticated.");
}

try {

    $status = "rejected";
    $approved_by = $user_id;

    $update = $conn->prepare("UPDATE inventory_requests 
        SET status=?, approved_by=?, approved_at=NOW() 
        WHERE id=?");

    $update->bind_param("sii", $status, $approved_by, $request_id);
    $update->execute();

    header("Location: request_manage.php?success=rejected");
    exit();

} catch (Exception $e) {

    header("Location: request_manage.php?error=" . urlencode($e->getMessage()));
    exit();
}

