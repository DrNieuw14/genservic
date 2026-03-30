<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

require_role(['admin', 'supervisor']);

$request_id = (int) ($_POST['request_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

    if(empty($reason)){
    die("Rejection reason is required");
}

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

if ($res['status'] !== 'Pending') {
    header("Location: request_manage.php?error=already_processed");
    exit();
}

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("User not authenticated.");
}

try {

    $status = "Rejected";
    $approved_by = $user_id;

    $stmt = $conn->prepare("
    UPDATE inventory_requests 
    SET status='Rejected', rejection_reason=?, approved_by=?, approved_at=NOW()
    WHERE id=?
    ");

    $stmt->bind_param("sii", $reason, $user_id, $request_id);
    $stmt->execute();

    header("Location: request_manage.php?success=rejected");
    exit();

} catch (Exception $e) {

    $conn->rollback();
    header("Location: request_manage.php?error=" . urlencode($e->getMessage()));
    exit();
}

