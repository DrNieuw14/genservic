<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

require_role(['admin','supervisor']);

$id = (int) $_GET['id'];

$stmt = $conn->prepare("DELETE FROM inventory_items WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: inventory.php?deleted=1");
exit();