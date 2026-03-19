<?php
include("config/database.php");

$error = "";
$success = "";

if(isset($_POST['register'])){

$first = trim($_POST['first_name']);
$middle = trim($_POST['middle_initial']);
$last = trim($_POST['last_name']);



$birth = $_POST['birthdate'];
$gender = $_POST['gender'];

$username = trim($_POST['username']);
$first = ucwords(strtolower($first));
$middle = strtoupper($middle);
$last = ucwords(strtolower($last));

if(strtolower($first) === strtolower($last)){
    $error = "First name and last name cannot be the same.";
}

$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$fullname = trim($first . " " . $middle . " " . $last);

$stmt_check = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$stmt_check->close();

if(empty($error) && $result_check->num_rows > 0){
    $error = "Username already exists";
}

if(empty($error)){

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

<body class="container mt-4">

<h2>Create Account</h2>

<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if(!empty($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">

<input type="text" name="first_name" placeholder="First Name" class="form-control mb-2" required>

<input type="text" name="middle_initial" placeholder="Middle Initial" class="form-control mb-2">

<input type="text" name="last_name" placeholder="Last Name" class="form-control mb-2" required>

<input type="date" name="birthdate" class="form-control mb-2" required>

<select name="gender" class="form-control mb-2">
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>

<input type="text" name="username" placeholder="Username" class="form-control mb-2" required>

<input type="password" name="password" placeholder="Password" class="form-control mb-2" required>

<select name="role" class="form-control mb-2">
<option value="personnel">Personnel</option>
<option value="supervisor">Supervisor</option>
</select>

<button type="submit" name="register" class="btn btn-success">Create Account</button>

</form>

<br>

<a href="login.php" class="btn btn-secondary">Back to Login</a>

</body>
</html>