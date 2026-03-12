<?php
session_start();

if($_SESSION['role'] != 'admin'){
echo "Access Denied";
exit();
}
?>
<?php
include("../../config/database.php");
?>

<!DOCTYPE html>
<html>

<head>

<title>Inventory Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="container mt-4">

<h2>Inventory Management</h2>

<form method="POST" class="mb-4">

<input type="text" name="item_name" placeholder="Item Name" class="form-control mb-2">

<input type="number" name="quantity" placeholder="Quantity" class="form-control mb-2">

<button name="add_item" class="btn btn-success">Add Item</button>

</form>

<h4>Inventory List</h4>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Item Name</th>
<th>Quantity</th>
<th>Status</th>
</tr>

<?php

$query = "SELECT * FROM inventory";
$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){

$status = ($row['quantity'] < 5) ? "Low Stock" : "Available";

echo "<tr>";
echo "<td>".$row['id']."</td>";
echo "<td>".$row['item_name']."</td>";
echo "<td>".$row['quantity']."</td>";
echo "<td>".$status."</td>";
echo "</tr>";

}

?>

</table>

<a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>

</html>

<?php

if(isset($_POST['add_item'])){

$item = $_POST['item_name'];
$qty = $_POST['quantity'];

$query = "INSERT INTO inventory(item_name,quantity) VALUES('$item','$qty')";
mysqli_query($conn,$query);

echo "<p class='text-success'>Item Added Successfully</p>";

}

?>