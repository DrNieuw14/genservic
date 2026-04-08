<?php
session_start();

if($_SESSION['role'] != 'supervisor'){
    echo "Access Denied";
    exit();
}

include("../../config/database.php");

// ===== GET LOGS =====
$query = "
SELECT audit_logs.*, users.fullname
FROM audit_logs
LEFT JOIN users ON audit_logs.user_id = users.id
ORDER BY audit_logs.created_at DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">

    <h2>Audit Trail Logs</h2>

    <table class="table table-bordered table-hover shadow">

        <tr class="table-dark">
            <th>User</th>
            <th>Action</th>
            <th>Module</th>
            <th>Date</th>
        </tr>

        <?php
        if(mysqli_num_rows($result) == 0){
            echo "<tr><td colspan='4' class='text-center text-muted'>No logs found</td></tr>";
        }

        while($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
                echo "<td>".($row['fullname'] ?? 'System')."</td>";
                echo "<td>".$row['action']."</td>";
                echo "<td><span class='badge bg-primary'>".$row['module']."</span></td>";
                echo "<td>".$row['created_at']."</td>";
            echo "</tr>";
        }
        ?>

    </table>

    <a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

</body>
</html>