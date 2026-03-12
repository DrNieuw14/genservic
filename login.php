<?php
session_start();
include("config/database.php");


if(isset($_POST['register'])){

$first = $_POST['first_name'];
$middle = $_POST['middle_initial'];
$last = $_POST['last_name'];
$birth = $_POST['birthdate'];
$gender = $_POST['gender'];

$username = $_POST['new_username'];
$password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

$role = $_POST['role'];

$fullname = $first." ".$middle." ".$last;

/* CHECK IF USERNAME EXISTS */

$check = mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");

if(mysqli_num_rows($check) > 0){

echo "<p style='color:red'>Username already exists</p>";

}else{

$query = "INSERT INTO users(first_name,middle_initial,last_name,birthdate,gender,fullname,username,password,role)
VALUES('$first','$middle','$last','$birth','$gender','$fullname','$username','$password','$role')";

mysqli_query($conn,$query);

echo "<p style='color:green'>Account Created Successfully</p>";

}

}

if(isset($_POST['login'])){

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){

$user = $result->fetch_assoc();

if(password_verify($password, $user['password'])){

$_SESSION['user'] = $username;
$_SESSION['role'] = $user['role'];

session_regenerate_id(true);

header("Location: dashboard.php");
exit();

}
else{

echo "Invalid password";

}

}else{

echo "User not found";

}

}
?>

<!DOCTYPE html>
<html>

<head>
<title>GENSERVIS Login</title>
</head>

<body>

<h2>GENSERVIS Login</h2>

<form method="POST">

<label>Username</label><br>
<input type="text" name="username"><br><br>

<label>Password</label><br>
<input type="password" name="password"><br><br>

<button type="submit" name="login">Login</button>

<p>
<a href="create_account.php">Create Account</a>
</p>





</form>

</body>
</html>