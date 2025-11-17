<?php
session_start();
require_once __DIR__ . "/../classes/user.php";
require_once __DIR__ . "/../classes/student.php";

// Redirect already logged-in users
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'superadmin':
            header("Location: ../superadmin/superadmin_dashboard.php");
            exit;
        case 'admin':
            header("Location: ../admin/admin_dashboard.php");
            exit;
        case 'student':
            header("Location: ../student/student_dashboard.php");
            exit;
    }
}

$errors = [];
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim(htmlspecialchars($_POST["username"]));
    $password = trim(htmlspecialchars($_POST["password"]));

    if (empty($username)) {
        $errors["username"] = "Username/Student ID is required";
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
            $_SESSION["user_id"] = $student->id;
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
    <title>WMSU iElect - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('../assets/img/wmsu-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .overlay {
            background: rgba(139, 0, 35, 0.75); /* WMSU maroon tint */
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center relative">

    <!-- Background overlay -->
    <div class="overlay absolute inset-0 backdrop-blur-sm"></div>

    <!-- Login card -->
    <div class="relative z-10 bg-white rounded-2xl shadow-lg px-10 py-8 w-full max-w-md text-center border-t-4 border-[#D02C4D]">
        <img src="../assets/img/wmsu-logo.png" alt="WMSU Logo" class="mx-auto mb-4 w-20 h-20">
        <h1 class="text-2xl font-semibold text-[#D02C4D] mb-6">WMSU iElect</h1>

        <?php if (!empty($errors['login'])): ?>
            <p class="text-red-600 text-sm mb-3"><?= $errors['login'] ?></p>
        <?php endif; ?>

<form method="POST" action="login.php" class="space-y-4 text-left">
    <div>
        <label for="username" class="text-[#D02C4D] font-medium text-sm">Username / Student ID / Email</label>
        <input type="text" name="username" id="username"
            value="<?= htmlspecialchars($username) ?>"
            placeholder="Enter your username, student ID, or email"
            class="mt-1 w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
        <?php if (!empty($errors['username'])): ?>
            <p class="text-red-600 text-xs mt-1"><?= $errors['username'] ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="password" class="text-[#D02C4D] font-medium text-sm">Password</label>
        <input type="password" name="password" id="password"
            class="mt-1 w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
        <?php if (!empty($errors['password'])): ?>
            <p class="text-red-600 text-xs mt-1"><?= $errors['password'] ?></p>
        <?php endif; ?>
    </div>

    <div class="flex justify-between items-center">
        <a href="#" class="text-[#D02C4D] text-sm hover:underline">Forgot Password?</a>
    </div>

    <button type="submit"
        class="w-full bg-[#D02C4D] text-white py-2 rounded-md mt-3 hover:bg-[#A0223B] transition duration-200">
        Log In
    </button>
</form>
    </div>
</body>
</html>
