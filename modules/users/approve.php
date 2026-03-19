<?php
require_once '../../config/database.php';

if(isset($_GET['id'])){

    $user_id = intval($_GET['id']);

    // STEP 1: Approve the account
    $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();

    // STEP 2: Get user info
    $stmt = $conn->prepare("SELECT fullname FROM users WHERE id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $fullname = $user['fullname'];

    $stmt = $conn->prepare("
    UPDATE personnel 
    SET status='Active' 
    WHERE user_id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

}

header("Location: approval.php");
exit();
?>