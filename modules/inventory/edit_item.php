<?php
    require_once '../../config/database.php';
    require_once '../../config/auth.php';

    require_role(['admin','supervisor']);

    $id = (int) $_GET['id'];

    $result = $conn->query("
        SELECT * FROM inventory_items WHERE id = $id
    ");
    $item = $result->fetch_assoc();

    if(isset($_POST['update'])){

        $old_qty = $item['quantity']; 

        $name = trim($_POST['item_name']);
        $qty = (int) $_POST['quantity'];
        $category_id = (int) $_POST['category_id'];

        $stmt = $conn->prepare("
            UPDATE inventory_items 
            SET item_name=?, quantity=?, category_id=? 
            WHERE id=?
        ");
        $stmt->bind_param("siii", $name, $qty, $category_id, $id);
        
        $stmt->execute();
        $stmt->close(); 
        $user_id = $_SESSION['user_id'] ?? 0;

        $log = $conn->prepare("
            INSERT INTO inventory_logs (item_id, action, quantity, user_id)
            VALUES (?, 'updated', ?, ?)
        ");

        $log->bind_param("iii", $id, $qty, $user_id);
        $log->execute();
        $log->close();

        header("Location: inventory.php?updated=1");
        exit();
    }
?>

<h3>Edit Item</h3>

<form method="POST">

    <input type="text" name="item_name" value="<?= $item['item_name'] ?>" required>

        <select name="category_id">
            <?php
            $cat = $conn->query("SELECT * FROM inventory_categories");
            
            while($c = $cat->fetch_assoc()){
                $selected = ($c['id'] == $item['category_id']) ? 'selected' : '';
                echo "<option value='{$c['id']}' $selected>{$c['category_name']}</option>";
            }
            ?>
        </select>

    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" required>

    <button name="update">Update</button>

</form>