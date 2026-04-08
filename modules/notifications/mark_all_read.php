<?php
session_start();
include("../../config/database.php");

$user_id = $_SESSION['user_id'];

mysqli_query($conn, "
    UPDATE notifications 
    SET is_read = 1 
    WHERE user_id = '$user_id'
");
?>