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

    $conn->begin_transaction();
    $sql = "
    SELECT ri.*, i.quantity AS current_stock, i.item_name 
    FROM inventory_request_items ri
    JOIN inventory_items i ON ri.item_id = i.id
    WHERE ri.request_id = ?
    ";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    throw new Exception("Request has no items.");
}

$items = [];

while ($row = $result->fetch_assoc()) {

    if ($row['current_stock'] < $row['quantity']) {
        throw new Exception("Not enough stock for " . $row['item_name']);
    }

    $items[] = $row;
}

foreach ($items as $item) {

    $new_qty = $item['current_stock'] - $item['quantity'];

    $update = $conn->prepare("UPDATE inventory_items SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_qty, $item['item_id']);
    $update->execute();
}



foreach ($items as $item) {

    $action = "Request Approved - Deducted";
    $qty = $item['quantity'];
    $item_id = $item['item_id'];

    

    $log = $conn->prepare("INSERT INTO inventory_logs (item_id, action, quantity, user_id, request_id) VALUES (?, ?, ?, ?, ?)");
    $log->bind_param("isiii", $item_id, $action, $qty, $user_id, $request_id);
    $log->execute();
}

$status = "approved";
$approved_by = $user_id;

$updateReq = $conn->prepare("UPDATE inventory_requests 
    SET status=?, approved_by=?, approved_at=NOW() 
    WHERE id=?");

$updateReq->bind_param("sii", $status, $approved_by, $request_id);
$updateReq->execute();

$conn->commit();

header("Location: request_manage.php?success=approved");
exit();

} catch (Exception $e) {

    $conn->rollback();

    header("Location: request_manage.php?error=" . urlencode($e->getMessage()));
    exit();
}