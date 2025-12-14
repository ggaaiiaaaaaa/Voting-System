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
    <title>iElect - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.8); }
        }

        @keyframes slide-in {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fade-in-scale {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        .animate-slide-in { animation: slide-in 0.8s ease-out; }
        .animate-fade-in-scale { animation: fade-in-scale 0.6s ease-out; }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 40px;
            height: 40px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            right: 10%;
            animation-delay: 6s;
        }

        .glass-effect {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 relative">

    <!-- Floating Shapes Background -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Login card -->
    <div class="glass-effect shadow-2xl rounded-3xl px-12 py-10 w-full max-w-md text-center border border-white/30 animate-fade-in-scale relative z-10">
        <div class="animate-slide-in">
            <h1 class="text-4xl font-bold text-white mb-2 bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">iElect</h1>
            <p class="text-gray-300 text-sm mb-8">Secure Voting System</p>
        </div>

        <?php if (!empty($errors['login'])): ?>
            <div class="bg-red-500/20 border-l-4 border-red-600 text-red-400 px-4 py-3 rounded-lg mb-6 animate-slide-in">
                <span class="block sm:inline"><?= $errors['login'] ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6 text-left animate-slide-in" style="animation-delay: 0.2s;">
            <!-- Username Input -->
            <div class="relative group">
                <label for="username" class="sr-only">Username</label>
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300 group-focus-within:text-blue-400 transition-colors duration-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" name="username" id="username"
                    value="<?= htmlspecialchars($username) ?>"
                    placeholder="Enter your username"
                    class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none input-focus transition-all duration-300">
                <?php if (!empty($errors['username'])): ?>
                    <p class="text-red-400 text-xs mt-2 ml-1 animate-slide-in"><?= $errors['username'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Password Input -->
            <div class="relative group">
                <label for="password" class="sr-only">Password</label>
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300 group-focus-within:text-blue-400 transition-colors duration-300" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="password" name="password" id="password"
                    placeholder="Enter your password"
                    class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none input-focus transition-all duration-300">
                <?php if (!empty($errors['password'])): ?>
                    <p class="text-red-400 text-xs mt-2 ml-1 animate-slide-in"><?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Login Button -->
            <button type="submit"
                class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white py-3 rounded-xl mt-4 font-semibold shadow-lg transform hover:scale-105 hover:shadow-2xl transition-all duration-300 animate-pulse-glow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Log In
            </button>
        </form>

        <div class="mt-6 text-center animate-slide-in" style="animation-delay: 0.4s;">
            <p class="text-gray-400 text-xs">Welcome back! Please sign in to continue.</p>
        </div>
    </div>
</body>
</html>