<?php
require_once __DIR__ . '/../../../config/database.php';


$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM inventory_categories WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
exit;