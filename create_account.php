<?php
include("config/database.php");

if(isset($_POST['register'])){

$first = $_POST['first_name'];
$middle = $_POST['middle_initial'];
$last = $_POST['last_name'];
$birth = $_POST['birthdate'];
$gender = $_POST['gender'];

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$fullname = $first." ".$middle." ".$last;

$check = mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");

if(mysqli_num_rows($check) > 0){

echo "<p style='color:red'>Username already exists</p>";

}else{

$query = "INSERT INTO users(first_name,middle_initial,last_name,birthdate,gender,fullname,username,password,role,status)
VALUES('$first','$middle','$last','$birth','$gender','$fullname','$username','$password','$role','pending')";
mysqli_query($conn,$query);

echo "<p style='color:green'>Account created successfully. Waiting for supervisor approval.</p>";

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