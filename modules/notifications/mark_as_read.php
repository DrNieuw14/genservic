<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$id = $_POST['id'] ?? 0;

if($id){
    mysqli_query($conn, "
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = '$id'
    ");
}

echo json_encode(["success" => true]);
?>