<?php
include("../../config/database.php");

// ===== CURRENT MONTH =====
$current_month = date('Y-m');

// ===== GET ALL PERSONNEL =====
$personnel_query = mysqli_query($conn, "SELECT id FROM personnel");

while ($person = mysqli_fetch_assoc($personnel_query)) {

    $personnel_id = $person['id'];

    // ===== COMPUTE TOTAL HOURS =====
    $query = "
        SELECT 
            SUM(
                IFNULL(TIME_TO_SEC(overtime)/3600, 0)
            ) as total_hours
        FROM attendance
        WHERE personnel_id = '$personnel_id'
        AND DATE_FORMAT(date, '%Y-%m') = '$current_month'
    ";

    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    $total_hours = $data['total_hours'] ?? 0;

    // ===== CONVERT HOURS TO DAYS =====
    $equivalent_days = $total_hours / 8;

    // ===== STATUS LOGIC =====
    $status = ($total_hours >= 8) ? 'Approved' : 'Pending';

    // ===== INSERT OR UPDATE CTO =====
    $insert = "
    INSERT INTO cto_summary 
    (personnel_id, month, total_hours, equivalent_days, status)
    VALUES 
    ('$personnel_id', '$current_month', '$total_hours', '$equivalent_days', '$status')
    
    ON DUPLICATE KEY UPDATE 
        total_hours = VALUES(total_hours),
        equivalent_days = VALUES(equivalent_days),
        status = VALUES(status)
";

mysqli_query($conn, $insert);
}

echo "CTO Auto Generated Successfully!";
?>