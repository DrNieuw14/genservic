<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/layout.php';

require_role(['admin','supervisor']);

$id = (int) $_GET['id'];

$result = $conn->query("SELECT * FROM inventory_categories WHERE id = $id");
$row = $result->fetch_assoc();

if(!$row){
    die("Category not found");
}

if(isset($_POST['update'])){

    $name = trim($_POST['category_name']);
    $desc = trim($_POST['description']);

    $stmt = $conn->prepare("
        UPDATE inventory_categories
        SET category_name=?, description=?
        WHERE id=?
    ");
    $stmt->bind_param("ssi", $name, $desc, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Category</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css')); ?>">
</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<h3>Edit Category</h3>

<form method="POST">

<div class="mb-3">
<label>Category Name</label>
<input type="text" name="category_name" class="form-control"
value="<?= htmlspecialchars($row['category_name']) ?>" required>
</div>

<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control"><?= htmlspecialchars($row['description']) ?></textarea>
</div>

<button type="submit" name="update" class="btn btn-primary">Update</button>
<a href="index.php" class="btn btn-secondary">Back</a>

</form>

</main>
</div>
</div>

</body>
</html>