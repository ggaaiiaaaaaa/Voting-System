<?php
session_start();
require_once __DIR__ . "/../classes/user.php";
require_once __DIR__ . "/../classes/student.php";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'superadmin') {
        header("Location: ../superadmin/superadmin_dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: ../student/student_dashboard.php");
        exit;
    }
}

$errors = [];
$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST["username"]));
    $password = trim(htmlspecialchars($_POST["password"]));

    if (empty($username)) {
        $errors["username"] = "Username is required";
    }
    if (empty($password)) {
        $errors["password"] = "Password is required";
    }

    if (empty($errors)) {
        $login_successful = false;

        $user = new User();
        $user->username = $username;
        $user->password = $password;

        if ($user->login()) {
            $_SESSION["user_id"] = $user->id;
            $_SESSION["role"] = $user->role;
            $login_successful = true;

            if ($user->role === 'superadmin') {
                header("Location: ../superadmin/superadmin_dashboard.php");
            } else {
                header("Location: ../admin/admin_dashboard.php");
            }
            exit;
        }

        $student = new Student();
        $student->student_id = $username;
        $student->password = $password;

        if ($student->login()) {
            $_SESSION["student_id"] = $student->id; 
            $_SESSION["role"] = 'student';
            $login_successful = true;
            header("Location: ../student/student_dashboard.php");
            exit;
        }

        if (!$login_successful) {
            $errors['login'] = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMSU iElect - Log In</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <h1 class="page-title">iElect</h1>

        <div class="login-container">
            <h2 class="form-title">Log In</h2>
            <p class="form-subtitle">Please enter your details</p>
            
            <?php if (!empty($errors['login'])): ?>
                <p class="error-main"><?= $errors['login'] ?></p>
            <?php endif; ?>

            <form action="login.php" method="post" novalidate>
                <div class="input-group">
                    <label for="username">Username/Student-ID:</label>
                    <input type="text" name="username" id="username" placeholder="" value="<?= htmlspecialchars($username) ?>">
                    <?php if (!empty($errors['username'])): ?>
                        <p class="error"><?= $errors['username'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" placeholder="">
                    <?php if (!empty($errors['password'])): ?>
                        <p class="error"><?= $errors['password'] ?></p>
                    <?php endif; ?>
                </div>

                <input type="submit" class="login-button" value="Log In">
            </form>
            
            <a href="#" class="forgot-password">Forgot Password</a>
        </div>
    </div>
</body>
</html>