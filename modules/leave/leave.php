<?php
    session_start();

    if($_SESSION['role'] != 'supervisor' && $_SESSION['role'] != 'personnel'){
        echo "Access Denied";
        exit();
    }

    include("../../config/database.php");
    include("../../includes/notifications.php");

    if(isset($_POST['submit_leave'])){
        $personnel_id = intval($_POST['personnel_id']);
        $requested_days = floatval($_POST['requested_days']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);

        // Convert to hours
        $total_seconds = $requested_days * 8 * 3600;
        $equivalent_hours = gmdate("H:i:s", $total_seconds);

        // CHECK CTO BALANCE
        $check = "
        SELECT 
        SUM(equivalent_days) AS total_days,
        SUM(used_hours)/8 AS used_days
        FROM cto_summary
        WHERE personnel_id='$personnel_id' AND status='Approved'
        ";

        $res = mysqli_query($conn,$check);
        $data = mysqli_fetch_assoc($res);
        $total = $data['total_days'] ?? 0;
        $used = $data['used_days'] ?? 0;
        $available = $total - $used;

        if($requested_days > $available){
            echo "<div class='alert alert-danger'>Not enough CTO balance</div>";
            exit();
        }

        // INSERT
        $query = "INSERT INTO leave_requests 
        (personnel_id, requested_days, equivalent_hours, reason, status)
        VALUES ('$personnel_id','$requested_days','$equivalent_hours','$reason','Pending')";

        mysqli_query($conn,$query);

        // ===== CREATE NOTIFICATION =====
        $supervisors = mysqli_query($conn, "
        SELECT id FROM users WHERE role = 'supervisor'
        ");

        while($sup = mysqli_fetch_assoc($supervisors)){
            createNotification(
                $conn,
                $sup['id'],
                "New leave request submitted",
                "leave"
            );
        }
        
        // ===== REDIRECT =====
        header("Location: leave.php");
        exit();
    }

    // ===== GET CURRENT USER =====
    $personnel_id = $_SESSION['personnel_id'] ?? 1;

    // ===== GET CTO BALANCE =====
    $cto_query = "
    SELECT 
        SUM(equivalent_days) AS total_days,
        SUM(used_hours) AS used_hours,
        SEC_TO_TIME(SUM(TIME_TO_SEC(total_hours))) AS total_hours
    FROM cto_summary
    WHERE personnel_id = '$personnel_id' AND status='Approved'
    ";

    $cto_result = mysqli_query($conn, $cto_query);
    $cto = mysqli_fetch_assoc($cto_result);

    $total_days = isset($cto['total_days']) ? (float)$cto['total_days'] : 0;
    $used_hours = $cto['used_hours'] ?? 0;
    $total_hours = $cto['total_hours'] ?? '00:00:00';

    $balance_days = max(0, $total_days - ($used_hours / 8));
   
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Leave Request System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

        <body class="container mt-4">

            <h2>Leave Request System</h2>

            <!-- CTO DISPLAY -->
            <?php if($_SESSION['role'] == 'personnel'): ?>

                <div class="alert alert-info">
                    <strong>CTO Summary:</strong><br>
                    Earned: <?= number_format($total_days,2) ?> days<br>
                    Used: <?= number_format($used_hours / 8,2) ?> days<br>
                    <strong>Balance: <?= number_format($balance_days,2) ?> days</strong><br>
                    Hours Earned: <?= $total_hours ?>
                </div>

            <?php endif; ?>

            <?php if($_SESSION['role'] == 'personnel'): ?>

                <form method="POST" class="mb-4">

                    <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                                        <input type="number" step="0.5" name="requested_days" placeholder="Requested Days (e.g. 0.5, 1, 1.5)" class="form-control mb-2" required>
                    <textarea name="reason" placeholder="Reason for leave" class="form-control mb-2" required></textarea>
                    <button name="submit_leave" class="btn btn-primary">Submit Leave Request</button>
                
                </form>

            <?php endif; ?>

            <?php if($_SESSION['role'] == 'supervisor'): ?>
                <div class="alert alert-secondary">
                    You can review and approve leave requests below.
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?= (!isset($_GET['status'])) ? 'active' : '' ?>" href="leave.php">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['status'] ?? '') == 'Pending' ? 'active' : '' ?>" href="?status=Pending">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['status'] ?? '') == 'Approved' ? 'active' : '' ?>" href="?status=Approved">Approved</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['status'] ?? '') == 'Rejected' ? 'active' : '' ?>" href="?status=Rejected">Rejected</a>
                </li>
            </ul>

            <h4>Leave Records</h4>
            <table class="table table-bordered table-hover shadow">
            

                <tr class="table-dark">
                <th>ID</th>
                <th>Personnel</th>
                <th>Days</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Action</th>
                </tr>

                <?php
                    $status_filter = $_GET['status'] ?? '';
                    $where = "";

                    if($_SESSION['role'] == 'personnel'){
                        $where .= "leave_requests.personnel_id = '$personnel_id'";
                    }

                    if($status_filter != ''){
                        if($where != '') $where .= " AND ";
                        $where .= "leave_requests.status = '$status_filter'";
                    }

                    if($where != ''){
                        $where = "WHERE " . $where;
                    }

                    $query = "
                    SELECT leave_requests.*, personnel.fullname
                    FROM leave_requests
                    JOIN personnel ON leave_requests.personnel_id = personnel.id
                    $where
                    ORDER BY leave_requests.created_at DESC
                    ";

                        $result = mysqli_query($conn,$query);
                        if(mysqli_num_rows($result) == 0){
                            echo "<tr><td colspan='6' class='text-center text-muted'>No records found</td></tr>";
                        }

                        while($row = mysqli_fetch_assoc($result)){
                            $rowClass = '';
                            if($row['status'] == 'Pending') $rowClass = 'table-warning';
                            elseif($row['status'] == 'Approved') $rowClass = 'table-success';
                            elseif($row['status'] == 'Rejected') $rowClass = 'table-danger';

                            echo "<tr class='$rowClass'>";
                                echo "<td>".$row['id']."</td>";
                                echo "<td>".$row['fullname']."</td>";
                                echo "<td>".$row['requested_days']."</td>";
                                echo "<td>".$row['equivalent_hours']."</td>";

                                // STATUS BADGE
                                echo "<td>";
                                    if($row['status'] == "Pending"){
                                        echo "<span class='badge bg-warning'>Pending</span>";
                                    }elseif($row['status'] == "Approved"){
                                        echo "<span class='badge bg-success'>Approved</span>";
                                    }else{
                                        echo "<span class='badge bg-danger'>Rejected</span>";
                                    }
                                echo "</td>";

                                // ACTION
                                echo "<td>";
                                    if($_SESSION['role'] == 'supervisor' && $row['status'] == 'Pending'){
                                        echo "<a href='approve.php?id=".$row['id']."' class='btn btn-success btn-sm'>Approve</a> ";
                                        echo "<a href='reject.php?id=".$row['id']."' class='btn btn-danger btn-sm'>Reject</a>";
                                    }
                                echo "</td>";
                            echo "</tr>";
                            }
                ?>
            </table>

            <a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </body>
</html>

<?php




    // ===== SUBMIT LOGIC =====
    
?>