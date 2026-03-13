<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/layout.php';

require_role(['admin', 'supervisor', 'personnel']);

$feedback = ['type' => '', 'message' => ''];
$today = date('Y-m-d');
$currentTime = date('H:i:s');

$personnelList = [];
$personnelSql = 'SELECT id, fullname FROM personnel ORDER BY fullname ASC';
$personnelResult = $conn->query($personnelSql);
while ($row = $personnelResult->fetch_assoc()) {
    $personnelList[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean_input($_POST['action'] ?? '');
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId <= 0) {
        $feedback = ['type' => 'danger', 'message' => 'Please select personnel.'];
    } elseif ($action === 'time_in') {
        $checkSql = 'SELECT id FROM attendance WHERE user_id = ? AND date = ? LIMIT 1';
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('is', $userId, $today);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();

        if ($exists) {
            $feedback = ['type' => 'warning', 'message' => 'Duplicate Time In is not allowed.'];
        } else {
            $status = ($currentTime > '08:00:00') ? 'Late' : 'Present';
            $insertSql = 'INSERT INTO attendance (user_id, date, time_in, status) VALUES (?, ?, ?, ?)';
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('isss', $userId, $today, $currentTime, $status);
            $insertStmt->execute();
            $insertStmt->close();
            $feedback = ['type' => 'success', 'message' => "Time In recorded with status: {$status}."];
        }
    } elseif ($action === 'time_out') {
        $updateSql = 'UPDATE attendance SET time_out = ? WHERE user_id = ? AND date = ? AND time_out IS NULL';
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sis', $currentTime, $userId, $today);
        $updateStmt->execute();
        $affected = $updateStmt->affected_rows;
        $updateStmt->close();

        if ($affected > 0) {
            $feedback = ['type' => 'success', 'message' => 'Time Out recorded successfully.'];
        } else {
            $feedback = ['type' => 'warning', 'message' => 'No open Time In found for today.'];
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
            <h3 class="mb-3">Attendance Monitoring</h3>

            <?php if ($feedback['message'] !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($feedback['type'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Personnel</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select personnel...</option>
                                <?php foreach ($personnelList as $person): ?>
                                    <option value="<?= (int) $person['id']; ?>"><?= htmlspecialchars($person['fullname'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button class="btn btn-success" type="submit" name="action" value="time_in">Time In</button>
                            <button class="btn btn-danger" type="submit" name="action" value="time_out">Time Out</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="<?= htmlspecialchars(app_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>