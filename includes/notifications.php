<?php
function createNotification($conn, $user_id, $message, $type = 'general'){
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $message, $type);
    $stmt->execute();
}
?>