<?php
session_start();
include("../../config/database.php");

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM notifications 
          WHERE user_id = '$user_id' 
          ORDER BY created_at DESC 
          LIMIT 5";

$result = mysqli_query($conn, $query);

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);