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
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $errors['login'] ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5 text-left">
            <!-- Username Input -->
            <div class="relative">
                <label for="username" class="sr-only">Username</label>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" name="username" id="username"
                    value="<?= htmlspecialchars($username) ?>"
                    placeholder="Enter your email"
                    class="w-full pl-10 px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                <?php if (!empty($errors['username'])): ?>
                    <p class="text-red-600 text-xs mt-1"><?= $errors['username'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Password Input -->
            <div class="relative">
                <label for="password" class="sr-only">Password</label>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="password" name="password" id="password"
                    placeholder="Enter your password"
                    class="w-full pl-10 px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#D02C4D] focus:outline-none">
                <?php if (!empty($errors['password'])): ?>
                    <p class="text-red-600 text-xs mt-1"><?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Login Button -->
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-[#D02C4D] text-white py-2.5 rounded-md mt-3 hover:bg-[#A0223B] transition duration-200 font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Log In
            </button>
        </form>
    </div>
</body>
</html>