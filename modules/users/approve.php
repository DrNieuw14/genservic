<?php

require_once '../../config/database.php';
require_once '../../config/auth.php';

require_role(['supervisor','admin']);

$id = $_GET['id'];

$stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: approval.php");
exit();

?>