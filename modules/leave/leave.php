<?php
    session_start();

    if($_SESSION['role'] != 'supervisor' && $_SESSION['role'] != 'personnel'){
        echo "Access Denied";
        exit();
    }

    include("../../config/database.php");

    // ===== GET CURRENT USER =====
    $personnel_id = $_SESSION['personnel_id'] ?? 1;

    // ===== GET CTO BALANCE =====
    $cto_query = "
    SELECT 
        SUM(equivalent_days) AS total_days,
        SEC_TO_TIME(SUM(TIME_TO_SEC(total_hours))) AS total_hours
        FROM cto_summary
        WHERE personnel_id = '$personnel_id' AND status='Approved'
    ";

    $cto_result = mysqli_query($conn, $cto_query);
    $cto = mysqli_fetch_assoc($cto_result);

    $total_days = isset($cto['total_days']) ? (float)$cto['total_days'] : 0;
    $total_hours = $cto['total_hours'] ?? '00:00:00';
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
            <div class="alert alert-info">
                <strong>Available CTO:</strong><br>
                Days: <?= number_format($total_days,2) ?> <br>
                Hours: <?= $total_hours ?>

                <p class="mt-2">
                    <small class="text-muted">
                        1 day = 8 hours
                    </small>
                </p>
            </div>


            <form method="POST" class="mb-4">
                <?php if($_SESSION['role'] == 'supervisor'): ?>
                <label>Select Personnel</label>
                <select name="personnel_id" class="form-control mb-2">
                    <?php
                        $query = "SELECT * FROM personnel";
                        $result = mysqli_query($conn,$query);
                        while($row = mysqli_fetch_assoc($result)){
                        echo "<option value='".$row['id']."'>".$row['fullname']."</option>";
                        }
                    ?>
                </select>

                <?php else: ?>
                <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                <?php endif; ?>

                <input type="number" step="0.5" name="requested_days" placeholder="Requested Days (e.g. 0.5, 1, 1.5)" class="form-control mb-2" required>
                <textarea name="reason" placeholder="Reason for leave" class="form-control mb-2" required></textarea>
                <button name="submit_leave" class="btn btn-primary">Submit Leave Request</button>

            </form>

            <h4>Leave Records</h4>
            <table class="table table-bordered">

                <tr>
                <th>ID</th>
                <th>Personnel</th>
                <th>Days</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Action</th>
                </tr>

                <?php
                    if($_SESSION['role'] == 'personnel'){
                        $query = "SELECT leave_requests.*, personnel.fullname
                        FROM leave_requests
                        JOIN personnel ON leave_requests.personnel_id = personnel.id
                        WHERE leave_requests.personnel_id = '$personnel_id'";
                    }else{
                        $query = "SELECT leave_requests.*, personnel.fullname
                        FROM leave_requests
                        JOIN personnel ON leave_requests.personnel_id = personnel.id";
                        }

                        $result = mysqli_query($conn,$query);

                        while($row = mysqli_fetch_assoc($result)){
                            $rowClass = ($row['status'] == 'Pending') ? 'table-warning' : '';
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
    if(isset($_POST['submit_leave'])){
        $personnel_id = intval($_POST['personnel_id']);
        $requested_days = floatval($_POST['requested_days']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);

        // Convert to hours
        $total_seconds = $requested_days * 8 * 3600;
        $equivalent_hours = gmdate("H:i:s", $total_seconds);

        // CHECK CTO BALANCE
        $check = "
        SELECT SUM(equivalent_days) AS total_days
        FROM cto_summary
        WHERE personnel_id='$personnel_id' AND status='Approved'
        ";

        $res = mysqli_query($conn,$check);
        $data = mysqli_fetch_assoc($res);
        $available = $data['total_days'] ?? 0;

        if($requested_days > $available){
            echo "<div class='alert alert-danger'>Not enough CTO balance</div>";
            exit();
        }

        // INSERT
        $query = "INSERT INTO leave_requests 
        (personnel_id, requested_days, equivalent_hours, reason, status)
        VALUES ('$personnel_id','$requested_days','$equivalent_hours','$reason','Pending')";

        mysqli_query($conn,$query);

        echo "<script>alert('Leave Request Submitted'); window.location='leave.php';</script>";
    }
?>