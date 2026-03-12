<?php
session_start();
include("config/database.php");
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$errorMessage = "";
$usernameValue = "";

if (isset($_POST['login'])) {
    $usernameValue = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameValue === '' || $password === '') {
        $errorMessage = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");

        if ($stmt) {
            $stmt->bind_param("s", $usernameValue);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_id'] = $user['id'];

                    header("Location: dashboard.php");
                    exit();
                }
            }

            $errorMessage = "Invalid username or password.";
            $stmt->close();
        } else {
            $errorMessage = "A server error occurred. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GENSERVIC Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d6efd, #6610f2);
        }

        .login-card {
            max-width: 420px;
            width: 100%;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <h2 class="text-center mb-4">GENSERVIC Login</h2>

            <?php if ($errorMessage !== ""): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($usernameValue, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                        minlength="3"
                        maxlength="50"
                    >
                    <div class="form-text">Username must be 3-50 characters.</div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required
                            minlength="6"
                        >
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            Show Password
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="login" class="btn btn-primary btn-lg">Login</button>
                </div>
            </form>

            <p class="text-center mt-4 mb-0">
                Don't have an account?
                <a href="create_account.php">Create Account</a>
            </p>
        </div>
    </div>

    <script>
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePasswordBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            togglePasswordBtn.textContent = isPassword ? 'Hide Password' : 'Show Password';
        });
    </script>
</body>
</html>