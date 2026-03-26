<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/layout.php';

require_role(['admin','supervisor']);

if(isset($_POST['submit'])){

    $name = trim($_POST['category_name']);
    $desc = trim($_POST['description']);

    if(empty($name)){
        $error = "Category name is required";
    } else {

        // CHECK DUPLICATE
        $stmt = $conn->prepare("SELECT id FROM inventory_categories WHERE category_name=?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($exists){
            $error = "Category already exists";
        } else {

            $stmt = $conn->prepare("
                INSERT INTO inventory_categories (category_name, description)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ss", $name, $desc);
            $stmt->execute();
            $stmt->close();

            header("Location: index.php?success=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Category</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css')); ?>">
</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<h3>Add Category</h3>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Category Name</label>
<input type="text" name="category_name" class="form-control" required>
</div>

<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control"></textarea>
</div>

<button type="submit" name="submit" class="btn btn-success">Save</button>
<a href="index.php" class="btn btn-secondary">Back</a>

</form>

</main>
</div>
</div>

</body>
</html>