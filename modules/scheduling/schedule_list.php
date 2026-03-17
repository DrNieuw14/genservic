<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

require_role(['supervisor','admin']);

// Fetch schedule with personnel name
$query = "
SELECT s.*, p.fullname 
FROM schedules s
JOIN personnel p ON p.id = s.personnel_id
ORDER BY s.schedule_date ASC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Schedule List</title>
</head>
<body>

<h2>Schedule List</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Name</th>
    <th>Date</th>
    <th>Shift</th>
    <th>Time</th>
    <th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>
    <td><?= $row['fullname'] ?></td>
    <td><?= $row['schedule_date'] ?></td>
    <td><?= $row['shift'] ?></td>
    <td>
        <?= $row['time_start'] ?> - <?= $row['time_end'] ?>
    </td>
    <td>
        <?= $row['is_restday'] ? 'Rest Day' : 'Working' ?>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>