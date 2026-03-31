<?php
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/layout.php';

require_role(['admin','supervisor']);
?>

<?php
$personnel_id = $_GET['personnel_id'] ?? '';
$month = $_GET['month'] ?? '';
?>

<div class="card p-3 mb-3">
    <form method="GET" class="row">

        <div class="col-md-4">
            <label>Personnel</label>
            <select name="personnel_id" class="form-control" required>
                <option value="">Select Personnel</option>
                <?php
                $res = $conn->query("SELECT id, fullname FROM personnel ORDER BY fullname ASC");
                
                while($row = $res->fetch_assoc()):
                ?>
                <option value="<?= $row['id'] ?>" 
                    <?= (($_GET['personnel_id'] ?? '') == $row['id']) ? 'selected' : '' ?>>
                    <?= $row['fullname'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label>Month</label>
            <input type="month" name="month" class="form-control"
                value="<?= $_GET['month'] ?? '' ?>" required>
        </div>

        <div class="col-md-2 mt-4">
            <button class="btn btn-primary">Generate</button>
        </div>

    </form>
</div>

<?php

if($personnel_id && $month){

    $year = date('Y', strtotime($month));
    $month_num = date('m', strtotime($month));

    // Get personnel info
    $stmt = $conn->prepare("SELECT fullname FROM personnel WHERE id=?");
    $stmt->bind_param("i", $personnel_id);
    $stmt->execute();
    $personnel = $stmt->get_result()->fetch_assoc();
?>


<?php
    $stmt = $conn->prepare("
    SELECT 
        a.date,
        a.time_in,
        a.time_out,
        a.undertime,
        s.time_in AS schedule_time_in
    FROM attendance a
    LEFT JOIN work_schedule s 
        ON a.personnel_id = s.personnel_id 
        AND a.date = s.schedule_date
    WHERE a.personnel_id = ?
    AND MONTH(a.date) = ?
    AND YEAR(a.date) = ?
    ");

    $stmt->bind_param("iii", $personnel_id, $month_num, $year);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<?php
    $total_days = cal_days_in_month(CAL_GREGORIAN, $month_num, $year);

    $dtr = [];

    for($i=1; $i <= $total_days; $i++){
        $dtr[$i] = [
            'am_in' => '',
            'am_out' => '',
            'pm_in' => '',
            'pm_out' => '',
            'late' => ''
        ];
    }

    // Map attendance
    $total_late_minutes = 0;
    $total_minutes = 0;
    while($row = $result->fetch_assoc()){
        $day = date('j', strtotime($row['date']));

        $late_minutes = '';

        if($row['time_in'] && $row['schedule_time_in']){

            $actual_time_in = strtotime($row['time_in']);
            $scheduled_time = strtotime($row['schedule_time_in']);

            if($actual_time_in > $scheduled_time){
                $late_minutes = ($actual_time_in - $scheduled_time) / 60;
            }

            if($late_minutes){
                $total_late_minutes += $late_minutes;
            }
        }

        if($row['time_in'] && $row['time_out']){

            $time_in  = strtotime($row['time_in']);
            $time_out = strtotime($row['time_out']);

            $diff = ($time_out - $time_in) / 60; // convert to minutes

            $total_minutes += $diff;
        }

        $dtr[$day]['am_in']  = $row['time_in'];
        $dtr[$day]['pm_out'] = $row['time_out'];
        $dtr[$day]['late']   = $late_minutes ? round($late_minutes) . ' min' : '';
        
    }
    $total_hours = floor($total_minutes / 60);
    $total_remaining_minutes = $total_minutes % 60;
    $total_late_hours = floor($total_late_minutes / 60);
    $total_late_remaining = $total_late_minutes % 60;
?>

<div id="print-area" class="card p-4" style="width:100%;">

    <div class="text-center mb-2" style="line-height:1.2;">
        <strong>Republic of the Philippines</strong><br>
        <strong>Your Organization Name</strong><br>
    </div>

    <h3 class="text-center mb-3"><strong>DAILY TIME RECORD</strong></h3>

    <div class="mb-3">
        <strong>Name:</strong> <?= $personnel['fullname'] ?><br>
        <strong>Month:</strong> <?= date('F Y', strtotime($month)) ?><br>
        <strong>Official Hours:</strong> 8:00 AM – 5:00 PM
    </div>

    <table class="table table-bordered text-center" style="font-size:11px;">
        <thead>
            <tr>
                <th rowspan="2">Day</th>
                <th colspan="2">AM</th>
                <th colspan="2">PM</th>
                <th rowspan="2">Late / Undertime</th>
            </tr>

            <tr>
                <th>Arrival</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Departure</th>
            </tr>
        </thead>

        <tbody>

            <?php for($i=1; $i <= $total_days; $i++): ?>
            <tr>
                <td><?= $i ?></td>
                
                <td><?= $dtr[$i]['am_in'] ? date('h:i A', strtotime($dtr[$i]['am_in'])) : '' ?></td>

                <td><?= $dtr[$i]['am_out'] ? date('h:i A', strtotime($dtr[$i]['am_out'])) : '' ?></td>

                <td><?= $dtr[$i]['pm_in'] ? date('h:i A', strtotime($dtr[$i]['pm_in'])) : '' ?></td>

                <td><?= $dtr[$i]['pm_out'] ? date('h:i A', strtotime($dtr[$i]['pm_out'])) : '' ?></td>
                
                <td class="text-danger fw-bold">
                    <?= $dtr[$i]['late'] ?>
                </td>
            </tr>

            <?php endfor; ?>

        </tbody>
    </table>

<div class="mt-3">
    <strong>Total Days in Month:</strong> <?= $total_days ?>
</div>

<div class="mt-2">
    <strong>Total Hours Worked:</strong> 
    <?= $total_hours ?> hrs <?= $total_remaining_minutes ?> mins
</div>

<div class="mt-2 text-danger">
    <strong>Total Late:</strong> 
    <?= $total_late_hours ?> hrs <?= $total_late_remaining ?> mins
</div>
    
<!-- ✅ INSERT SIGNATURE HERE -->
<div class="mt-3 d-flex justify-content-between signature-section">

    <div style="width:300px;">
        ___________________________<br>
            Signature of Employee<br>
            Date: ___________
        </div>

        <div style="width:300px; text-align:right;">
            ___________________________<br>
            Supervisor
        </div>

    </div>

    <div class="mt-4">
        <p>
            I certify on my honor that the above is a true and correct report of my daily attendance.
        </p>
    </div>

</div> <!-- ✅ NOW close print-area -->


<button onclick="window.print()" class="btn btn-success mt-3">
    🖨 Print DTR
</button>

<?php } ?>