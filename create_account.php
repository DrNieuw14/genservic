<?php
include("config/database.php");

$error = "";
$success = "";

if(isset($_POST['register'])){

$first = trim($_POST['first_name']);
$middle = trim($_POST['middle_initial']);
$last = trim($_POST['last_name']);



$birth = $_POST['birthdate'] ?? '';
$gender = $_POST['gender'] ?? '';
$role = $_POST['role'] ?? '';

$username = trim($_POST['username'] ?? '');

if(empty($username) && empty($error)){
    $error = "Username is required.";
}
$first = ucwords(strtolower($first));
$middle = strtoupper($middle);
$last = ucwords(strtolower($last));

if(strtolower($first) === strtolower($last)){
    $error = "First name and last name cannot be the same.";
}



$fullname = trim($first . " " . ($middle ? $middle . " " : "") . $last);

$stmt_check = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$stmt_check->close();

if(empty($error) && $result_check->num_rows > 0){
    $error = "Username already exists";
}

if(empty($birth) && empty($error)){
    $error = "Please complete birthdate (Age, Month, Day).";
}

if((empty($gender) || empty($role)) && empty($error)){
    $error = "Please select gender and role.";
}

if (empty($_POST['password']) && empty($error)) {
    $error = "Password is required.";
}

if (empty($error)) {

    $passwordInput = $_POST['password'] ?? '';
    $password = password_hash($passwordInput, PASSWORD_DEFAULT);

    $conn->begin_transaction();

    try {

        // ✅ INSERT USER
        $stmt = $conn->prepare("INSERT INTO users 
        (first_name, middle_initial, last_name, birthdate, gender, fullname, username, password, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

        $stmt->bind_param("sssssssss",
            $first,
            $middle,
            $last,
            $birth,
            $gender,
            $fullname,
            $username,
            $password,
            $role
        );

        if(!$stmt->execute()){
            throw new Exception("User Error: " . $stmt->error);
        }

        // ✅ GET USER ID
        $user_id = $conn->insert_id;

        // ✅ INSERT PERSONNEL
        $employee_id = 'UTL' . date('Ymd') . rand(100,999);

        $stmt2 = $conn->prepare("INSERT INTO personnel 
        (employee_id, fullname, position, department, user_id)
        VALUES (?, ?, 'Utility Staff', 'Maintenance', ?)");

        $stmt2->bind_param("ssi", $employee_id, $fullname, $user_id);

        

        if(!$stmt2->execute()){
            throw new Exception("Personnel Error: " . $stmt2->error);
        }

        // ✅ COMMIT ONLY AFTER BOTH SUCCESS
        $conn->commit();

        $success = "Account created successfully. Waiting for supervisor approval.";

        // ✅ RESET FORM AFTER SUCCESS
        $_POST = [];

    } catch (Exception $e) {

        // ❌ ROLLBACK EVERYTHING
        $conn->rollback();
        $error = $e->getMessage();
    }
}
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Create Account</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="background:#f4f6f9;">

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">

<div class="card shadow-lg border-0" style="width:450px; border-radius:15px;">

    <!-- HEADER -->
    <div class="card-header text-white text-center"
         style="background:#0d6b3c; border-radius:15px 15px 0 0;">
        <h4 class="mb-0">Create Account</h4>
    </div>

    

<div class="card-body p-4">

<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if(!empty($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">

<label class="form-label">First Name</label>
<input type="text" name="first_name"
value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
class="form-control mb-2" required>

<label class="form-label">Middle Initial</label>
<input type="text" name="middle_initial"
value="<?= htmlspecialchars($_POST['middle_initial'] ?? '') ?>"
class="form-control mb-2">

<label class="form-label">Last Name</label>
<input type="text" name="last_name"
value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
class="form-control mb-2" required>

<div class="mb-3">
    <small class="text-muted">Preview:</small>
    <div class="fw-bold" id="fullnamePreview">—</div>
</div>

<label class="form-label">Birthdate</label>
<div class="row mb-3">

    <!-- AGE -->
    <div class="col-md-3">
        <input type="number" id="age" class="form-control" placeholder="Age" min="1" max="100" required>
    </div>

    <!-- MONTH -->
    <div class="col-md-5">
        <select id="month" class="form-control" required>
            <option value="">Select Month</option>
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
    </div>

    <!-- DAY -->
    <div class="col-md-4">
        <select id="day" class="form-control" required>
            <option value="">Day</option>
            <?php for($i=1;$i<=31;$i++): ?>
                <option value="<?= str_pad($i,2,'0',STR_PAD_LEFT) ?>">
                    <?= $i ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

</div>

<!-- HIDDEN BIRTHDATE (IMPORTANT) -->
<input type="hidden" name="birthdate" id="birthdate">

<small class="text-muted">
Selected Birthdate:
<span id="birthPreview">—</span>
</small>

<label class="form-label">Gender</label>
<select name="gender" class="form-control mb-2">
<option value="Male" <?= (($_POST['gender'] ?? '')=='Male')?'selected':'' ?>>Male</option>
<option value="Female" <?= (($_POST['gender'] ?? '')=='Female')?'selected':'' ?>>Female</option>
</select>

<label class="form-label">Username</label>
<input type="text" name="username"
value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
class="form-control mb-2" required>

<label class="form-label">Password</label>
<div class="input-group mb-2">
    <input type="password" name="password" id="password"
    class="form-control" required>

    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
        Show
    </button>
</div>

<label class="form-label">Role</label>
<select name="role" class="form-control mb-2">
<option value="personnel" <?= (($_POST['role'] ?? '')=='personnel')?'selected':'' ?>>Personnel</option>
<option value="supervisor" <?= (($_POST['role'] ?? '')=='supervisor')?'selected':'' ?>>Supervisor</option>
</select>


<button type="submit" name="register"
class="btn w-100 text-white"
style="background:#0d6b3c; border:none;">
Create Account
</button>

</form>

<br>

<a href="login.php" class="btn btn-secondary w-100 mt-2">
Back to Login
</a>

</div> <!-- card-body -->
</div> <!-- card -->
</div> <!-- container -->
</body>

<script>
document.addEventListener("DOMContentLoaded", function(){

    function computeBirthday() {
        let age = document.getElementById("age").value;
        let month = document.getElementById("month").value;
        let day = document.getElementById("day").value;

        if(age && month && day){
            let today = new Date();
            let year = today.getFullYear() - age;

            let fullDate = year + "-" + month + "-" + day;

            document.getElementById("birthdate").value = fullDate;

            // pretty preview
            let dateObj = new Date(fullDate);
            let options = { year: 'numeric', month: 'long', day: 'numeric' };

            document.getElementById("birthPreview").innerText =
                dateObj.toLocaleDateString('en-US', options);
        }
    }

    // ✅ EVENTS (MUST BE INSIDE)
    document.getElementById("age").addEventListener("input", computeBirthday);
    document.getElementById("month").addEventListener("change", computeBirthday);
    document.getElementById("day").addEventListener("change", computeBirthday);

    // ✅ FORM VALIDATION
    document.querySelector("form").addEventListener("submit", function(e){
        let birth = document.getElementById("birthdate").value;

        if(!birth){
            alert("Please complete your birthdate.");
            e.preventDefault();
        }
    });

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const toggle = document.getElementById("togglePassword");
    const pass = document.getElementById("password");

    if(toggle && pass){
        toggle.addEventListener("click", function() {
            if (pass.type === "password") {
                pass.type = "text";
                this.innerText = "Hide";
            } else {
                pass.type = "password";
                this.innerText = "Show";
            }
        });
    }

});
</script>

<script>


document.addEventListener("DOMContentLoaded", function(){
        
    function updateFullname() {
        let first = document.querySelector("input[name='first_name']").value;
        let middle = document.querySelector("input[name='middle_initial']").value;
        let last = document.querySelector("input[name='last_name']").value;

        let fullname = first + " " + (middle ? middle + " " : "") + last;

        document.getElementById("fullnamePreview").innerText = fullname.trim() || "—";
    }

    document.querySelectorAll("input[name='first_name'], input[name='middle_initial'], input[name='last_name']")
    .forEach(input => {
        input.addEventListener("input", updateFullname);
    });

    updateFullname();

});

</script>



</html>