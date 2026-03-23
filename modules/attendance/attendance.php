<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/layout.php';

date_default_timezone_set('Asia/Manila');

require_role(['admin', 'supervisor', 'personnel']);

$statusToday = "Not Timed In";
$timeInToday = null;
$timeOutToday = null;
$undertime = null;
$overtime = null;
$totalHours = null;



$isPersonnel = ($_SESSION['role'] === 'personnel');

$feedback = ['type' => '', 'message' => ''];
$today = date('Y-m-d');


if ($isPersonnel) {
    $selectedUserId = $_SESSION['personnel_id'];
} else {

    // priority: POST → GET → default
    if (isset($_POST['user_id'])) {
        $selectedUserId = $_POST['user_id'];
    } elseif (isset($_GET['user_id'])) {
        $selectedUserId = $_GET['user_id'];
    } else {
        $result = $conn->query("
    SELECT p.id 
    FROM personnel p
    JOIN users u ON p.user_id = u.id
    WHERE u.status = 'approved'
    LIMIT 1
");
        $row = $result->fetch_assoc();
        $selectedUserId = $row['id'] ?? 0;
    }
}

$userId = (int) $selectedUserId;

// ✅ HANDLE DROPDOWN AUTO SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if (!empty($_POST['user_id'])) {
        $userId = (int) $_POST['user_id'];
        $selectedUserId = $userId;
    }
}

if ($userId <= 0) {
    $userId = 0;
}


/* =========================
   GET ATTENDANCE DATA
========================= */
if ($userId > 0) {
    $stmt = $conn->prepare("
        SELECT time_in, time_out, status, undertime, overtime
        FROM attendance
                WHERE personnel_id = ? AND date = ?
        LIMIT 1
    ");
    
    $stmt->bind_param("is", $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
   

    if ($row = $result->fetch_assoc()) {
    $statusToday = $row['status'];
    $timeInToday = $row['time_in'];
    $timeOutToday = $row['time_out'];
    $undertime = $row['undertime'] ?? null;
    $overtime = $row['overtime'] ?? null;

    if(empty($timeOutToday)){
        $undertime = null;
    }
        

        if ($timeInToday && $timeOutToday) {
            $seconds = max(0, strtotime($timeOutToday) - strtotime($timeInToday));
            $totalHours = gmdate("H:i:s", $seconds);
        }
    }

    $stmt->close();
}

// =========================
// MONTHLY SUMMARY (ADD HERE)
// =========================
$monthlyHours = "00:00:00";
$monthlyOT = "00:00:00";

if ($userId > 0) {

    $summarySql = "
    SELECT 
        SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(time_out, time_in)))) AS total_hours,
        SEC_TO_TIME(SUM(TIME_TO_SEC(COALESCE(overtime,'00:00:00')))) AS total_overtime
    FROM attendance
    WHERE personnel_id = ? 
    AND MONTH(date) = MONTH(CURRENT_DATE())
    AND YEAR(date) = YEAR(CURRENT_DATE())
    AND time_out IS NOT NULL
    ";

    $stmt = $conn->prepare($summarySql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();

    if ($summary) {
        $monthlyHours = $summary['total_hours'] ?? "00:00:00";
        $monthlyOT = $summary['total_overtime'] ?? "00:00:00";
    }

    $stmt->close();
}



$currentTime = date('H:i:s');

$shiftName = null;
$shiftStart = null;
$shiftEnd = null;

if ($userId > 0) {
    $stmt = $conn->prepare("
    SELECT shift, time_in, time_out
    FROM work_schedule
    WHERE personnel_id = ? AND schedule_date = ?
    LIMIT 1
");
$stmt->bind_param("is", $userId, $today);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $shiftName = $row['shift'];
        $shiftStart = $row['time_in'];
        $shiftEnd = $row['time_out'];
    }

    $stmt->close();
}
    

$areaName = "Not Assigned";
$displayName = "Unknown";

if ($userId > 0) {
    $stmt = $conn->prepare("
    SELECT 
        u.fullname,
        GROUP_CONCAT(pa.area_name SEPARATOR ', ') AS assigned_area
    FROM personnel p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN personnel_areas pa ON p.id = pa.personnel_id
    WHERE p.id = ?
    GROUP BY p.id
");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $displayName = $row['fullname'];
        $areaName = $row['assigned_area'] ?? '';
    } else {
        $displayName = "No Record Found";
        $areaName = "Not Assigned";
    }

    $stmt->close();
}
    
$personnelList = [];

$personnelSql = "
    SELECT p.id, u.fullname 
    FROM personnel p
    JOIN users u ON p.user_id = u.id
    WHERE u.status = 'approved'
    ORDER BY u.fullname ASC
";

$personnelResult = $conn->query($personnelSql);

while ($row = $personnelResult->fetch_assoc()) {
    $personnelList[] = $row;
}
    

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = clean_input($_POST['action'] ?? '');

    $userId = $isPersonnel
    ? (int) $_SESSION['personnel_id']
    : (int) ($_POST['user_id'] ?? 0);

       // ✅ refresh selected user after action
    if (!$isPersonnel && isset($_POST['user_id'])) {
        $userId = (int) $_POST['user_id'];
    }

    if ($userId <= 0) {
        $feedback = ['type' => 'danger', 'message' => 'Please select personnel.'];

    } elseif ($action === 'time_in') {

        $checkSql = 'SELECT id FROM attendance WHERE personnel_id = ? AND date = ? LIMIT 1';
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('is', $userId, $today);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();

        if ($exists) {
            $feedback = ['type' => 'warning', 'message' => 'Duplicate Time In is not allowed.'];
        } else {
            if ($shiftName === 'REST') {
                $status = 'Rest Day (Worked)';
            } 
            
            elseif ($shiftStart) {

                if (strtotime($currentTime) < strtotime($shiftStart)) {
                    $status = 'Early';
                }
                elseif (strtotime($currentTime) > strtotime($shiftStart)) {
                    $status = 'Late';
                }
                else {
                    $status = 'Present';
                }

        

            } else {
                $status = 'No Schedule';
            }

            $insertSql = 'INSERT INTO attendance (personnel_id, date, time_in, status)
                          VALUES (?, ?, ?, ?)';
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('isss', $userId, $today, $currentTime, $status);
            $insertStmt->execute();
            $insertStmt->close();

            $feedback = ['type' => 'success', 'message' => "Time In recorded with status: {$status}."];
        }

    } elseif ($action === 'time_out') {

    // 🔥 RELOAD latest time_in (IMPORTANT FIX)
    $checkSql = "SELECT time_in FROM attendance WHERE personnel_id=? AND date=? LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $userId, $today);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    $row = $res->fetch_assoc();
    $timeInToday = $row['time_in'] ?? null;
    $checkStmt->close();

            // ❌ prevent time out without time in
        if (!$timeInToday) {
            $feedback = ['type' => 'danger', 'message' => 'You must Time In first.'];
        } else {

        $endTime = $shiftEnd;
        $undertime = null;
        $overtime = null;

        if ($shiftName === 'REST' && $timeInToday) {
            // ALL TIME is overtime
            $seconds = strtotime($currentTime) - strtotime($timeInToday);
            $overtime = gmdate("H:i:s", $seconds);
        }
        elseif ($endTime) {

            if (strtotime($currentTime) < strtotime($endTime)) {
                $seconds = strtotime($endTime) - strtotime($currentTime);
                $undertime = gmdate("H:i:s", $seconds);
            } elseif (strtotime($currentTime) > strtotime($endTime)) {
                $seconds = strtotime($currentTime) - strtotime($endTime);
                $overtime = gmdate("H:i:s", $seconds);
            }

        }
      
        $updateSql = 'UPDATE attendance
                      SET time_out = ?, undertime = ?, overtime = ?
                      WHERE personnel_id = ? AND date = ? AND time_out IS NULL';

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sssis', $currentTime, $undertime, $overtime, $userId, $today);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            $feedback = ['type' => 'success', 'message' => 'Time Out recorded successfully.'];
        } else {
            $feedback = ['type' => 'warning', 'message' => 'No open Time In found for today.'];
        }

        $updateStmt->close();
        }
    }
}
       
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | GenServis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row">
        <?php render_sidebar($_SESSION['role']); ?>
        
        <main class="col-lg-10 col-md-9 p-4">
            
        <?php render_topbar(); ?>   <!-- ✅ ADD THIS LINE -->

            <h3 class="mb-3">Attendance Monitoring</h3>

            <?php if ($feedback['message'] !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($feedback['type'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="attendanceForm" class="row g-3 align-items-end">
                        <div class="col-md-6">

<?php if (!$isPersonnel): ?>

<label class="form-label">Personnel</label>
<select name="user_id" class="form-select" required onchange="this.form.submit()">
<option value="">Select personnel...</option>

<?php foreach ($personnelList as $person): ?>
<option value="<?= (int) $person['id']; ?>"
    <?= ($person['id'] == $userId) ? 'selected' : ''; ?>>
<?= htmlspecialchars($person['fullname'], ENT_QUOTES, 'UTF-8'); ?>
</option>
<?php endforeach; ?>

</select>

<?php else: ?>

<input type="hidden" name="user_id" value="<?= (int) $_SESSION['personnel_id']; ?>">

<label class="form-label">Personnel</label>
<div class="border rounded p-3 bg-light">

    <h5 class="mb-2">
        👤 <?= htmlspecialchars($displayName); ?>
    </h5>

    
    <p class="mb-1">
📍 Area:
<?php if (!empty($areaName)): ?>
    <?php foreach (explode(',', $areaName) as $area): ?>
        <span class="badge bg-secondary me-1">
            <?= htmlspecialchars(trim($area)); ?>
        </span>
    <?php endforeach; ?>
<?php else: ?>
    <span class="text-muted">No area assigned</span>
<?php endif; ?>
</p>
    

    <p class="mb-1">
    📅 <?= date('F d, Y'); ?>
    </p>

    <p class="mb-1">
🕘 Shift:
<?php if ($shiftName): ?>
    <span class="badge bg-info text-dark">
        <?= htmlspecialchars($shiftName); ?>
    </span>
    (<?= date('h:i A', strtotime($shiftStart)); ?> - 
     <?= date('h:i A', strtotime($shiftEnd)); ?>)
<?php else: ?>
    <span class="text-muted">No schedule today</span>
<?php endif; ?>
</p>


    <p class="mb-1">
    🕒 Current Time: 
    <strong id="liveClock"></strong>
    </p>

    <p class="mb-1">
        🕒 Status: 
        <strong>
                <?php
                    $badge = "secondary";

                    if ($statusToday == "Present") $badge = "success";
                    elseif ($statusToday == "Late") $badge = "warning";
                    elseif ($statusToday == "Completed") $badge = "primary";
                    elseif ($statusToday == "Rest Day (Worked)") $badge = "dark";
                    elseif ($statusToday == "No Schedule") $badge = "secondary";
                    elseif ($statusToday == "Early") $badge = "info";
                    ?>

                    <span class="badge bg-<?= $badge ?>">
                        <?= htmlspecialchars($statusToday); ?>
                    </span>
        </strong>
    </p>

    <?php if ($timeInToday): ?>
        <p class="mb-1">⏰ Time In: <?= date("h:i:s A", strtotime($timeInToday)); ?></p>
    <?php endif; ?>

    <?php if ($timeOutToday): ?>
        <p class="mb-0">🏁 Time Out: <?= date("h:i:s A", strtotime($timeOutToday)); ?></p>
    <?php endif; ?>

        <?php if ($totalHours): ?>
        <p class="mb-0">🧮 Total Hours: <?= $totalHours; ?></p>
    <?php endif; ?>

    <?php if ($timeOutToday && !empty($undertime)): ?>
    <p class="text-warning">⏳ Undertime: <?= $undertime; ?></p>
<?php endif; ?>

    <?php if ($timeOutToday && !empty($overtime)): ?>
        <p class="text-success">🔥 Overtime: <?= $overtime; ?></p>
    <?php endif; ?>

    <hr>

    <p>📊 Monthly Total Hours: 
        <strong><?= $monthlyHours; ?></strong>
    </p>

    <p>🔥 Monthly Overtime: 
        <strong><?= $monthlyOT; ?></strong>
    </p>

</div>


<?php endif; ?>

</div>
                        <div class="col-md-6 d-flex gap-2">   
                            <button class="btn btn-success"
                                    type="button"
                                    id="timeInBtn"
                                    <?= ($timeInToday) ? 'disabled' : ''; ?>>
                                    Time In
                                </button>

                            <button class="btn btn-danger"
                                    type="button"
                                    id="timeOutBtn"
                                    <?= (!$timeInToday || $timeOutToday) ? 'disabled' : ''; ?>>
                                    Time Out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="<?= htmlspecialchars(app_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>

<script>
function updateClock() {
    const now = new Date();

    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    let ampm = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours ? hours : 12;

    hours = String(hours).padStart(2, '0');
    minutes = String(minutes).padStart(2, '0');
    seconds = String(seconds).padStart(2, '0');

    const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;

    document.getElementById("liveClock").innerText = timeString;
}

setInterval(updateClock, 1000);
updateClock();
</script>

<script>
    function sendAttendance(action) {
        const formData = new FormData();

        formData.append("action", action);
        formData.append("user_id", "<?= $userId ?>");

        fetch("attendance.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            // 🔥 for now (safe version)
            location.reload();
        })
        .catch(error => console.error("Error:", error));
    }

    // BUTTON EVENTS
    document.getElementById("timeInBtn").addEventListener("click", function() {
        sendAttendance("time_in");
    });

    document.getElementById("timeOutBtn").addEventListener("click", function() {
        sendAttendance("time_out");
    });
</script>


</body>
</html>
