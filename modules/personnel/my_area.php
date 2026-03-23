<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../config/layout.php';

require_role(['personnel']);

// get logged-in user
$user_id = $_SESSION['user_id'] ?? 0;

// get personnel_id from users table
$stmt = $conn->prepare("
    SELECT personnel.id
    FROM users
    INNER JOIN personnel ON users.id = personnel.user_id
    WHERE users.id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$personnel_id = $user['id'] ?? 0;

// safety check
if($personnel_id == 0){
    die("Error: Your account is not linked to personnel.");
}

// get today's schedule
$stmt3 = $conn->prepare("
    SELECT work_area, shift, time_in, time_out
    FROM work_schedule
    WHERE personnel_id = ? 
    AND schedule_date = CURDATE()
    LIMIT 1
");

$stmt3->bind_param("i", $personnel_id);
$stmt3->execute();
$today_sched = $stmt3->get_result()->fetch_assoc();

// get assigned areas
$stmt2 = $conn->prepare("
    SELECT area_name 
    FROM personnel_areas 
    WHERE personnel_id = ?
");

$stmt2->bind_param("i", $personnel_id);
$stmt2->execute();
$areas = $stmt2->get_result();

$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$sql = "
SELECT 
    ws.schedule_date,
    ws.work_area,
    ws.shift,
    ws.time_in,
    ws.time_out,
    a.status
FROM work_schedule ws
LEFT JOIN attendance a 
    ON ws.personnel_id = a.personnel_id 
    AND ws.schedule_date = a.date
WHERE ws.personnel_id = ?
";

$params = [$personnel_id];
$types = "i";

if(!empty($start_date) && !empty($end_date)){
    $sql .= " AND ws.schedule_date BETWEEN ? AND ?";
    $types .= "ss";
    $params[] = $start_date;
    $params[] = $end_date;
}

$sql .= " ORDER BY ws.schedule_date ASC";

$stmt_sched = $conn->prepare($sql);
$stmt_sched->bind_param($types, ...$params);
$stmt_sched->execute();
$schedule = $stmt_sched->get_result();




?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Assigned Areas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
.area-box{
    display:inline-block;
    background:#198754; /* green */
    color:white;
    padding:10px 16px;
    border-radius:25px;
    margin:6px;
    font-size:14px;
    font-weight:500;
}
</style>

</head>

<body>

<div class="container-fluid app-layout">
<div class="row">

<?php render_sidebar($_SESSION['role']); ?>

<main class="col-lg-10 col-md-9 p-4">

<?php render_topbar(); ?>

<?php if($today_sched): ?>

<div class="card mb-3 border-0 shadow-sm">
<div class="card-body">


<h5 class="mb-3">📅 Today’s Assignment</h5>

<p class="text-muted mb-2">Assigned Work Areas</p>

<p>📍 <strong>Area:</strong> <?= htmlspecialchars($today_sched['work_area']) ?></p>

<p>🕘 <strong>Shift:</strong> <?= htmlspecialchars($today_sched['shift']) ?></p>

<p>⏰ <strong>Time:</strong> 
<?= $today_sched['time_in'] ? date("h:i A", strtotime($today_sched['time_in'])) : 'N/A' ?> 
- 
<?= $today_sched['time_out'] ? date("h:i A", strtotime($today_sched['time_out'])) : 'N/A' ?>
</p>

</div>
</div>

<?php else: ?>

<div class="alert alert-warning text-center">
No schedule assigned for today
</div>

<?php endif; ?>

<div class="card shadow-sm border-0 mt-3">

<div class="card-header bg-white">
    <h5 class="mb-0">My Work Schedule</h5>
</div>

<div class="card-body">

    <form method="GET" class="row g-2 mt-2 mb-3">

        <div class="col-md-4">
            <label class="form-label">Date</label>

            <input type="text" id="dateRange" class="form-control"
            value="<?= (!empty($_GET['start_date']) && !empty($_GET['end_date'])) 
                ? $_GET['start_date'] . ' to ' . $_GET['end_date'] 
                : '' ?>"
            placeholder="Select Date Range">

            <input type="hidden" name="start_date" id="start_date"
            value="<?= htmlspecialchars($_GET['start_date'] ?? '', ENT_QUOTES); ?>">

            <input type="hidden" name="end_date" id="end_date"
            value="<?= htmlspecialchars($_GET['end_date'] ?? '', ENT_QUOTES); ?>">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <a href="my_area.php" class="btn btn-secondary w-100">Reset</a>
        </div>

    </form>

</div>

<div class="card-body">

<?php if($schedule->num_rows > 0): ?>

<?php while($row = $schedule->fetch_assoc()): ?>

<div class="border-bottom py-3">

<!-- DATE -->
<p class="mb-1">
📅 <?= date("F d, Y", strtotime($row['schedule_date'])) ?>
</p>

<!-- AREA -->
<p class="mb-1">
📍 <?= htmlspecialchars($row['work_area']) ?>
</p>

<!-- SHIFT -->
<?php if($row['shift'] == 'REST'): ?>
<p class="mb-1">
🕘 <span class="badge bg-danger">REST DAY</span>
</p>
<?php else: ?>
<p class="mb-1">
🕘 <?= htmlspecialchars($row['shift']) ?>
</p>
<?php endif; ?>

<!-- TIME -->
<?php if($row['time_in'] && $row['time_out']): ?>
<p class="mb-1">
⏰ <?= date("h:i A", strtotime($row['time_in'])) ?> - 
<?= date("h:i A", strtotime($row['time_out'])) ?>
</p>
<?php endif; ?>

<!-- STATUS -->
<?php if($row['status']): ?>

<?php
$statusColor = "secondary";
if($row['status'] == "Present") $statusColor = "success";
if($row['status'] == "Late") $statusColor = "danger";
?>

<p class="mb-1">
📊 Status: 
<span class="badge bg-<?= $statusColor ?>">
<?= htmlspecialchars($row['status']) ?>
</span>
</p>

<?php endif; ?>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="alert alert-secondary text-center">
No schedule available
</div>

<?php endif; ?>

</div>
</div>



<div class="card shadow-sm border-0 mt-3">

<div class="card-header bg-white">
<h4 class="mb-0">My Assigned Areas</h4>
</div>

<div class="card-body">

<?php if($areas->num_rows > 0): ?>

<?php while($row = $areas->fetch_assoc()): ?>
<div class="area-box">
<span>📍 <?= htmlspecialchars($row['area_name']) ?></span>
</div>
<?php endwhile; ?>

<?php else: ?>

<div class="alert alert-secondary text-center">
No assigned areas yet
</div>

<?php endif; ?>

</div>

</div>

</main>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
const rangeInput = document.getElementById("dateRange");

if (rangeInput) {
    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",

        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {

                let start = selectedDates[0].toISOString().split('T')[0];
                let end = selectedDates[1].toISOString().split('T')[0];

                document.getElementById("start_date").value = start;
                document.getElementById("end_date").value = end;
            }
        }
    });
}
</script>


</body>
</html>