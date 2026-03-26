
<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
?>

<?php
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../../config/auth.php';
    require_once __DIR__ . '/../../../config/layout.php';

    require_role(['admin','supervisor']);

    // FETCH DATA
    $where = "";

        if(!empty($_GET['search'])){
            $search = $conn->real_escape_string($_GET['search']);
            $where = "WHERE category_name LIKE '%$search%'";
        }

        $result = $conn->query("
            SELECT * FROM inventory_categories
            $where
            ORDER BY id DESC
        ");

        if(!$result){
            die("Query Error: " . $conn->error);
        }

        if(!$result){
            die("Query Error: " . $conn->error);
        }
?>


<!DOCTYPE html>
<html>
    <head>

        <title>Category Management</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css')); ?>">

        <style>
            .card {
                border-radius: 12px;
            }

            .table td, .table th {
                vertical-align: middle;
            }

            .table tbody tr {
                transition: 0.2s;
            }

            .table tbody tr:hover {
                background-color: #f1f3f5;
            }

            .table {
            font-size: 14px;
}
        </style>

    </head>

    <body>

        <div class="container-fluid app-layout">
        <div class="row">

        <?php render_sidebar($_SESSION['role']); ?>

        <main class="col-lg-10 col-md-9 p-4">

        <?php render_topbar(); ?>

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">🏷 Category Management</h3>
            <a href="create.php" class="btn btn-success shadow-sm">➕ Add Category</a>
        </div>

        <!-- ALERTS -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Category added successfully</div>
        <?php endif; ?>

        <?php if(isset($_GET['updated'])): ?>
            <div class="alert alert-info">Category updated</div>
        <?php endif; ?>

        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-warning">Category deleted</div>
        <?php endif; ?>

        <form method="GET" class="mb-3">
            <div class="row g-2">

                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search category..."
                        value="<?= $_GET['search'] ?? '' ?>">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-dark w-100">Search</button>
                </div>

                <div class="col-md-2">
                    <a href="index.php" class="btn btn-secondary w-100">Reset</a>
                </div>
            </div>
        </form>

        <!-- CARD -->
        <div class="card shadow-sm border-0">

        <?php $total = $result->num_rows; ?>

        <div class="card-header bg-white d-flex justify-content-between align-items-center pe-3">
            <h5 class="mb-0">Category List</h5>
            <span class="badge bg-primary"><?= $total ?> Total</span>
        </div>

        <div class="card-body">

        <table class="table table-hover align-middle">

        <thead class="table-light">
        <tr>
        <th>ID</th>
        <th>Category Name</th>
        <th>Description</th>
        <th>Action</th>
        </tr>
        </thead>

        <tbody>

        <?php if($result->num_rows > 0): ?>

        <?php while($row = $result->fetch_assoc()): ?>

        <tr>

        <td><?= $row['id'] ?></td>

        <td>
            <strong><?= strlen($row['description']) > 50 ? htmlspecialchars(substr($row['description'],0,50)) . '...' : htmlspecialchars($row['description'] ?? '-') ?>
            </strong>
        </td>

        <td>
            <?= htmlspecialchars($row['description'] ?? '-') ?>
        </td>

        <td>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>

            <a href="delete.php?id=<?= $row['id'] ?>" 
            class="btn btn-sm btn-danger"
            onclick="return confirm('Delete this category?')">
            Delete
            </a>
        </td>

        </tr>

        <?php endwhile; ?>

        <?php else: ?>

        <tr>
        <td colspan="4" class="text-center text-muted py-4">
            <div class="py-4">
                <div style="font-size:40px;">📂</div>
                <div class="fw-semibold mt-2">No categories yet</div>
                <small class="text-muted">Click "Add Category" to get started</small>
            </div>
            <small>Click "Add Category" to get started</small>
        </td>
        </tr>

        <?php endif; ?>

        </tbody>

        </table>

        </div>
        </div>

        </main>
        </div>
        </div>

    </body>
</html>

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.display = 'none';
        });
    }, 3000);
</script>