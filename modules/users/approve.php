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

    // STEP 3: Generate employee ID
    $count = $conn->query("SELECT COUNT(*) as total FROM personnel")->fetch_assoc()['total'] + 1;

    $employee_id = "UTL" . str_pad($count,3,"0",STR_PAD_LEFT);

    // STEP 4: Insert into personnel table
    $stmt = $conn->prepare("
        INSERT INTO personnel 
        (employee_id, fullname, position, department, status, user_id)
        VALUES (?, ?, 'Utility Staff', 'Maintenance', 'Active', ?)
    ");

    $stmt->bind_param("ssi",$employee_id,$fullname,$user_id);
    $stmt->execute();

}

header("Location: approval.php");
exit();
?>