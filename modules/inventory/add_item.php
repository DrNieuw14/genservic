<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

require_role(['admin','supervisor']);

$personnel = $conn->query("SELECT id, fullname FROM personnel");

if(isset($_POST['save'])){

    $name = $_POST['item_name'];
    $category = $_POST['category'];
    $qty = $_POST['quantity'];
    $unit = $_POST['unit'];
    $assigned = $_POST['assigned_personnel_id'] ?: NULL;

    $stmt = $conn->prepare("
        INSERT INTO inventory_items 
        (item_name, category, quantity, unit, assigned_personnel_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssisi", $name, $category, $qty, $unit, $assigned);
    $stmt->execute();

    header("Location: inventory.php");
}
?>

<h4>Add Inventory Item</h4>

<form method="POST">

<input name="item_name" class="form-control mb-2" placeholder="Item Name" required>

<select name="category" class="form-control mb-2">
<option value="Material">Material</option>
<option value="Equipment">Equipment</option>
</select>

<input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity" required>

<input name="unit" class="form-control mb-2" placeholder="Unit (pcs, liters)" required>

<select name="assigned_personnel_id" class="form-control mb-2">
<option value="">Assign Personnel</option>
<?php while($p = $personnel->fetch_assoc()): ?>
<option value="<?= $p['id'] ?>"><?= $p['fullname'] ?></option>
<?php endwhile; ?>
</select>

<button name="save" class="btn btn-primary">Save</button>

</form>