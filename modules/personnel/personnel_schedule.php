<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

require_role(['admin','supervisor']);

$id = $_GET['id'] ?? 0;

// GET PERSON NAME
$stmt = $conn->prepare("
    SELECT users.fullname 
    FROM personnel
    JOIN users ON personnel.user_id = users.id
    WHERE personnel.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

// GET SCHEDULE
$stmt2 = $conn->prepare("
    SELECT * FROM work_schedule
    WHERE user_id = ?
    ORDER BY schedule_date ASC
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Personnel Schedule</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">

<h3><?= $user['fullname'] ?? 'Personnel' ?> - Schedule</h3>

<table class="table table-bordered table-striped mt-3">
<tr>
<th>Date</th>
<th>Area</th>
<th>Shift</th>
<th>Time</th>
</tr>

<?php while($row = $result->fetch_assoc()): 

$date = $row['schedule_date'];
if(empty($date)){
    $date = '-';
} else {
    $date = date("M d, Y", strtotime($date));
}

$time_in = $row['time_in'] ? date("h:i A", strtotime($row['time_in'])) : '-';
$time_out = $row['time_out'] ? date("h:i A", strtotime($row['time_out'])) : '-';

?>

<tr>
<td><?= $date ?></td>
<td><?= $row['work_area'] ?></td>

<td>
<?php if($row['shift'] == 'REST'): ?>
    <span class="badge bg-danger">REST</span>
<?php else: ?>
    <span class="badge bg-success"><?= $row['shift'] ?></span>
<?php endif; ?>
</td>

<td><?= $time_in ?> - <?= $time_out ?></td>
</tr>

<?php endwhile; ?>

</table>

<a href="../scheduling/schedule.php" class="btn btn-secondary">← Back</a>

</body>
</html>