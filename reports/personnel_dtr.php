
<?php
$is_pdf = isset($_GET['pdf']);

require_once '../config/database.php';

if(!$is_pdf){
    require_once '../config/auth.php';
    require_once '../config/layout.php';
    require_role(['personnel']);
    
} else {
    ob_clean();
}
?>

<?php if(!$is_pdf): ?>
<div class="container-fluid app-layout">
    <div class="row">

        <?php render_sidebar($_SESSION['role']); ?>

        <main class="col-lg-10 col-md-9 p-4">

            <?php render_topbar(); ?>
<?php endif; ?>




<?php
$personnel_id = $_SESSION['personnel_id'];
$month = $_GET['month'] ?? date('Y-m');
$personnel = ['fullname' => ''];

?>

<?php if(!$is_pdf): ?>
<div class="card p-3 mb-3">
    <form method="GET" class="row">
      
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

        <div class="col-md-3">
            <label>Select Month</label>

            <input type="text" id="month_picker" class="form-control" placeholder="📅 Select Month">

            <!-- hidden actual value -->
            <input type="hidden" name="month" id="month"
                value="<?= $_GET['month'] ?? '' ?>">
        </div>

        <div class="col-md-3">
            <label>Date Range (Optional)</label>

            <input type="text" id="date_range" class="form-control"
                placeholder="Select Date Range">

            <input type="hidden" name="start_date" id="start_date">
            <input type="hidden" name="end_date" id="end_date">
        </div>

        <div class="col-md-2 mt-4">
            <button class="btn btn-primary">Generate</button>
        </div>

    </form>
</div>
<?php endif; ?>

<?php

$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

if($personnel_id && ($month || ($start_date && $end_date))){

    

    $year = date('Y', strtotime($month));
    $month_num = date('m', strtotime($month));

    if($start_date && $end_date){

    $start_day = date('j', strtotime($start_date));
    $end_day   = date('j', strtotime($end_date));

    } else {

    $start_day = 1;
    $end_day   = cal_days_in_month(CAL_GREGORIAN, $month_num, $year);
    }        

    // Get personnel info
    $stmt = $conn->prepare("SELECT fullname FROM personnel WHERE id=?");
    $stmt->bind_param("i", $personnel_id);
    $stmt->execute();
    $personnel = $stmt->get_result()->fetch_assoc();

    if($start_date && $end_date){

        $stmt = $conn->prepare("
            SELECT 
                ws.schedule_date AS date,
                a.time_in,
                a.time_out,
                a.undertime,
                ws.time_in AS schedule_time_in,
                ws.time_out AS schedule_time_out,
                ws.shift
                FROM work_schedule ws
            LEFT JOIN attendance a 
                ON a.personnel_id = ws.personnel_id 
                AND a.date = ws.schedule_date
            WHERE ws.personnel_id = ?
            AND ws.schedule_date BETWEEN ? AND ?
            ORDER BY ws.schedule_date ASC
        ");

        $stmt->bind_param("iss", $personnel_id, $start_date, $end_date);

    } else {

        $start_month = date('Y-m-01', strtotime($month));
        $end_month   = date('Y-m-t', strtotime($month));

        $stmt = $conn->prepare("
            SELECT 
                ws.schedule_date AS date,
                a.time_in,
                a.time_out,
                a.undertime,
                ws.time_in AS schedule_time_in,
                ws.time_out AS schedule_time_out,
                ws.shift
            FROM work_schedule ws
            LEFT JOIN attendance a 
                ON a.personnel_id = ws.personnel_id 
                AND a.date = ws.schedule_date
            WHERE ws.personnel_id = ?
            AND ws.schedule_date BETWEEN ? AND ?
            ORDER BY ws.schedule_date ASC
        ");

        $stmt->bind_param("iss", $personnel_id, $start_month, $end_month);
    }

    // ✅ CORRECT PLACE
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $result = null;
}

    

    $dtr = [];

    // initialize empty DTR using date keys
    $current_init = strtotime($start_date && $end_date ? $start_date : date('Y-m-01', strtotime($month)));
    $end_init = strtotime($start_date && $end_date ? $end_date : date('Y-m-t', strtotime($month)));

    while($current_init <= $end_init){

        $dateKey = date('Y-m-d', $current_init);

        $dtr[$dateKey] = [
            'am_in' => '',
            'am_out' => '',
            'pm_in' => '',
            'pm_out' => '',
            'late' => '',
            'is_rest_day' => false // default
        ];

        $current_init = strtotime("+1 day", $current_init);
    }

    // Map attendance
    $total_late_minutes = 0;
    $total_minutes = 0;
    $working_days = 0;
    $rest_days = 0;
    $absent_days = 0;
    $counted_days = [];
    
    if($result){
        while($row = $result->fetch_assoc()){
        $day = $row['date'];

        $is_rest_day = (isset($row['shift']) && $row['shift'] === 'REST');

        // 👉 prevent duplicate counting
        if(!isset($counted_days[$day])){

            $counted_days[$day] = true;

            if($is_rest_day){
                $rest_days++;
            } else {
                $working_days++;

                if(empty($row['time_in'])){
                    $absent_days++;
                }
            }

        }

        $late_minutes = '';
        $undertime_minutes = 0;

        if($row['time_in'] && $row['schedule_time_in']){
            $actual_time_in = strtotime($row['time_in']);
            $scheduled_time = strtotime($row['schedule_time_in']);

            if($actual_time_in > $scheduled_time){
                $late_minutes = ($actual_time_in - $scheduled_time) / 60;
                $total_late_minutes += $late_minutes;
            }
        }

        if($row['time_in'] && $row['time_out']){
            $time_in_calc  = strtotime($row['time_in']);
            $time_out_calc = strtotime($row['time_out']);
            $total_minutes += ($time_out_calc - $time_in_calc) / 60;
        }

        if($row['time_out'] && $row['schedule_time_out']){

            $time_out_calc = strtotime($row['time_out']);
            $scheduled_out = strtotime($row['schedule_time_out']);

            if($time_out_calc < $scheduled_out){
                $undertime_minutes = ($scheduled_out - $time_out_calc) / 60;
            }

        }

        $time_in = $row['time_in'] ? strtotime($row['time_in']) : null;
        $time_out = $row['time_out'] ? strtotime($row['time_out']) : null;

        $noon = strtotime("12:00:00");

        $dtr[$day]['am_in'] = $row['time_in'] ?? '';

        if($time_out && $time_out <= $noon){
            $dtr[$day]['am_out'] = $row['time_out'];
        } elseif($time_out) {
            $dtr[$day]['am_out'] = date('H:i:s', $noon);
        }

        if($time_in && $time_in >= $noon){
            $dtr[$day]['pm_in'] = $row['time_in'];
        } elseif($time_in) {
            $dtr[$day]['pm_in'] = date('H:i:s', $noon);
        }

        $dtr[$day]['pm_out'] = $row['time_out'] ?? '';
        $dtr[$day]['is_rest_day'] = $is_rest_day;
        
        $dtr[$day]['late'] = $late_minutes ? round($late_minutes) . ' min' : '';
        $dtr[$day]['undertime'] = $undertime_minutes ? round($undertime_minutes) . ' min' : '';
        }
}
    $total_hours = floor((int)$total_minutes / 60);
    $total_remaining_minutes = (int)$total_minutes % 60;
    
    $total_late_hours = floor((int)$total_late_minutes / 60);
    $total_late_remaining = (int)$total_late_minutes % 60;

    $schedule_stmt = $conn->prepare("
    SELECT MIN(time_in) AS start_time, MAX(time_out) AS end_time
    FROM work_schedule
    WHERE personnel_id = ?
    AND schedule_date BETWEEN ? AND ?
    ");

if($start_date && $end_date){
    $schedule_stmt->bind_param("iss", $personnel_id, $start_date, $end_date);
} else {
    $start_month = date('Y-m-01', strtotime($month));
    $end_month = date('Y-m-t', strtotime($month));
    $schedule_stmt->bind_param("iss", $personnel_id, $start_month, $end_month);
}

$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result()->fetch_assoc();


$shift_label = "Based on Assigned Schedule";

if($schedule_result['start_time'] && $schedule_result['end_time']){
    $shift_label = date('h:i A', strtotime($schedule_result['start_time'])) . 
                   ' - ' . 
                   date('h:i A', strtotime($schedule_result['end_time']));
}
    
    
?>
<?php if($personnel_id && ($month || ($start_date && $end_date))): ?>
<div class="d-flex justify-content-between align-items-center mb-3">

    <h4 class="mb-0">DTR Report</h4>

    <?php if(!$is_pdf): ?>
        
    <?php endif; ?>

</div>

<div id="print-area" style="width:100%; padding:15px;">

    <div style="text-align:center; line-height:1.3; margin-bottom:5px;">
    <strong>Republic of the Philippines</strong><br>
    <strong>Cavite State University</strong><br>
    <span style="font-size:12px;">Maintenance Department</span>
</div>

    <h3 class="text-center" style="margin-bottom:5px; font-size:14px;"><strong>DAILY TIME RECORD</strong></h3>

    <table style="width:100%; margin-bottom:5px; font-size:10px;">
<tr>
    <td><strong>Name:</strong> <?= $personnel['fullname'] ?? '' ?></td>
    <td><strong>Period:</strong> 
        <?= $start_date && $end_date 
            ? date('F d, Y', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date))
            : date('F Y', strtotime($month)) ?>
    </td>
</tr>
<tr>
    <td><strong>Official Hours:</strong> <?= $shift_label ?></td>
    <td><strong>Generated:</strong> <?= date('F d, Y') ?></td>
</tr>
</table>

    <table class="table table-bordered text-center" style="font-size:11px;">
        <thead>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Day</th>
                <th colspan="2">AM</th>
                <th colspan="2">PM</th>
                <th rowspan="2">Late</th>
                <th rowspan="2">Undertime</th>
                
            </tr>

            <tr>
                <th>Arrival</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Departure</th>
            </tr>
        </thead>

       <tbody>

            <?php 
            $current = strtotime($start_date && $end_date ? $start_date : date('Y-m-01', strtotime($month)));
            $end = strtotime($start_date && $end_date ? $end_date : date('Y-m-t', strtotime($month)));

            while($current <= $end):

                $dateKey = date('Y-m-d', $current);
                $day = date('j', $current);

                $dateFormatted = date('M d', strtotime($dateKey));
                $dayName = date('D', strtotime($dateKey));

                $rowData = $dtr[$dateKey] ?? [
                    'am_in'=>'',
                    'am_out'=>'',
                    'pm_in'=>'',
                    'pm_out'=>'',
                    'late'=>''
                ];
                
                $status = '';

                if(!empty($rowData['is_rest_day'])){
                    $status = 'REST DAY';
                } elseif(empty($rowData['am_in'])){
                    $status = 'ABSENT';
                } else {
                    $status = 'PRESENT';
                }
                


            ?>

            <tr>
                <td><?= $dateFormatted ?></td>
                <td><?= $dayName ?></td>

               <td>
                    <?php
                        if($status === 'REST DAY'){
                            echo '<span class="text-primary fw-bold">REST DAY</span>';
                        } elseif($status === 'ABSENT'){
                            echo '<span class="text-danger">ABSENT</span>';
                        } else {
                            echo date('h:i A', strtotime($rowData['am_in']));
                        }
                    ?>
                </td>

                <td>
                <?php
                if($status === 'REST DAY'){
                    echo '-';
                } elseif(empty($rowData['am_out'])){
                    echo '<span class="text-danger">-</span>';
                } else {
                    echo date('h:i A', strtotime($rowData['am_out']));
                }
                ?>
                </td>


                <td>
                <?php
                if($status === 'REST DAY'){
                    echo '-';
                } elseif(empty($rowData['pm_in'])){
                    echo '<span class="text-danger">-</span>';
                } else {
                    echo date('h:i A', strtotime($rowData['pm_in']));
                }
                ?>
                </td>

                <td>
                <?php
                if($status === 'REST DAY'){
                    echo '-';
                }
                elseif(empty($rowData['pm_out'])){
                    echo '<span class="text-danger">-</span>';
                } else {
                    echo date('h:i A', strtotime($rowData['pm_out']));
                }
                ?>
                </td>
                <td>
                    <?php
                    if(!empty($rowData['late'])){
                        echo '<span class="text-danger fw-bold">'.$rowData['late'].'</span>';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>

                <td>
                    <?php
                    if(!empty($rowData['undertime'])){
                        echo '<span class="text-warning fw-bold">'.$rowData['undertime'].'</span>';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>

            <?php
            $current = strtotime("+1 day", $current);
            endwhile;
            ?>

        </tbody>
    </table>


<table style="margin-top:5px; width:40%; font-size:9px;">
<tr>
    <td><strong>Working Days</strong></td>
    <td><?= $working_days ?></td>
</tr>
<tr>
    <td><strong>Rest Days</strong></td>
    <td><?= $rest_days ?></td>
</tr>
<tr>
    <td><strong>Absent Days</strong></td>
    <td><?= $absent_days ?></td>
</tr>
<tr>
    <td><strong>Total Hours</strong></td>
    <td><?= $total_hours ?>h <?= $total_remaining_minutes ?>m</td>
</tr>
<tr>
    <td><strong>Total Late</strong></td>
    <td><?= $total_late_hours ?>h <?= $total_late_remaining ?>m</td>
</tr>
</table>
    
<!-- ✅ INSERT SIGNATURE HERE -->
<table style="width:100%; margin-top:10px; text-align:center; font-size:10px;">
<tr>
    <td>Prepared by:</td>
    <td>Checked by:</td>
    <td>Approved by:</td>
</tr>

<tr>
    <td style="padding-top:20px;">_________________________</td>
    <td style="padding-top:20px;">_________________________</td>
    <td style="padding-top:20px;">_________________________</td>
</tr>

<tr>
    <td>Admin / HR</td>
    <td>Supervisor</td>
    <td>Manager</td>
</tr>
</table>


<?php if(!$is_pdf): ?>
<button onclick="window.print()" class="btn btn-success mt-3">
    🖨 Print DTR
</button>
<?php endif; ?>

<?php if(!$is_pdf): ?>
<a href="dtr_pdf.php?personnel_id=<?= $personnel_id ?>&month=<?= $month ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
   class="btn btn-danger mt-3">
   📄 Download PDF
</a>
<?php endif; ?>

<?php endif; ?>

<?php if(!$is_pdf): ?>
        </main>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
flatpickr("#month_picker", {
    dateFormat: "Y-m",        // actual value sent to PHP
    altInput: true,           // ✨ shows formatted text
    altFormat: "F Y",         // ✨ example: March 2026
    defaultDate: "<?= $_GET['month'] ?? date('Y-m') ?>",

    onChange: function(selectedDates, dateStr) {
        if(dateStr){
            document.getElementById("month").value = dateStr;
        }
    }
});
</script>

<script>
flatpickr("#date_range", {
    mode: "range",
    dateFormat: "Y-m-d",

    onChange: function(selectedDates) {
        if(selectedDates.length === 2){

            let start = selectedDates[0].toISOString().split('T')[0];
            let end = selectedDates[1].toISOString().split('T')[0];

            document.getElementById("start_date").value = start;
            document.getElementById("end_date").value = end;
        }
    }
});
</script>

<style>
body {
    font-family: Arial, sans-serif;
}

/* ✅ SCREEN (normal UI) */
@media screen {
    body {
        font-size: 14px;
    }

    .table {
        font-size: 13px;
    }
}

/* ✅ PDF / PRINT (compact) */
@media print {
    body {
        font-size: 10px;
    }

    .table {
        font-size: 9px;
    }
}

table {
    border-collapse: collapse;
}

.table th, .table td {
    border: 1px solid #000;
    padding: 3px;
}

.table th {
    background-color: #eaeaea;
}

.text-center {
    text-align: center;
}

.text-danger {
    color: red;
}

.text-primary {
    color: blue;
}

.text-warning {
    color: orange;
}

.table {
    width: 100%;
    table-layout: fixed;
}

</style>
