<?php
    session_start();
    require_once '../../config/database.php';

    // Only supervisor can access
    if($_SESSION['role'] != 'supervisor'){
        echo "Access Denied";
        exit();
    }

    // ======================
    // CTO LEADERBOARD QUERY
    // ======================

    $query = "
    SELECT 
        p.fullname,
        p.department,
        SEC_TO_TIME(SUM(TIME_TO_SEC(a.extra_hours))) AS total_hours,
        SUM(TIME_TO_SEC(a.extra_hours))/3600/8 AS total_days
        FROM attendance a
        JOIN personnel p ON a.personnel_id = p.id
        WHERE a.extra_hours IS NOT NULL
        AND MONTH(a.date) = MONTH(CURRENT_DATE())
        AND YEAR(a.date) = YEAR(CURRENT_DATE())
        GROUP BY a.personnel_id
        ORDER BY SUM(TIME_TO_SEC(a.extra_hours)) DESC
        LIMIT 10
    ";

    $result = mysqli_query($conn, $query);
    
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CTO Leaderboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body class="container mt-4">

        <h2>🏆 CTO Leaderboard</h2>
        <p class="text-muted">Top 10 employees with highest extra hours</p>
        <p class="text-muted">
            Top performers for <?= date('F Y'); ?>
        </p>

        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Rank</th>
                    <th>Employee</th>
                    <th>Total Hours</th>
                    <th>Equivalent Days</th>
                    <th>Department</th>
                </tr>
            </thead>

            <tbody>
                <?php
                    $rank = 1;

                    if(mysqli_num_rows($result) == 0){
                        echo "<tr><td colspan='5' class='text-muted'>No data available</td></tr>";
                    }

                    while($row = mysqli_fetch_assoc($result)){
                        $rowClass = '';

                        if($rank == 1){
                            $rowClass = 'table-success'; // green
                            } elseif($rank == 2){
                            $rowClass = 'table-info'; // blue
                            } elseif($rank == 3){
                            $rowClass = 'table-warning'; // yellow
                        }

                        echo "<tr class='$rowClass'>";

                        // Rank badge
                        if($rank == 1){
                            echo "<td>🥇</td>";
                        } elseif($rank == 2){
                            echo "<td>🥈</td>";
                        } elseif($rank == 3){
                            echo "<td>🥉</td>";
                        } else {
                            echo "<td>$rank</td>";
                        }

                        echo "<td>".$row['fullname']."</td>";
                        echo "<td>".($row['total_hours'] ?? '00:00:00')."</td>";
                        echo "<td>".number_format($row['total_days'],2)."</td>";
                        echo "<td>".($row['department'] ?? 'N/A')."</td>";
                        echo "</tr>";
                        $rank++;
                    }
                ?>
            </tbody>
        </table>

        <a href="../../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

    </body>
</html>