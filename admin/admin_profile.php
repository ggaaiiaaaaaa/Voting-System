<?php
session_start();
require_once __DIR__ . "/../classes/user.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$userObj = new User();
$errors = [];
$success = "";

// Fetch current admin data
$admin = $userObj->fetchAdmin($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validations
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } elseif ($userObj->isUsernameExist($username, $_SESSION['user_id'])) {
        $errors['username'] = "Username already exists.";
    }
    
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif ($userObj->isEmailExist($email, $_SESSION['user_id'])) {
        $errors['email'] = "Email already exists.";
    }
    
    // Password change validation
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors['current_password'] = "Current password is required to set new password.";
        } elseif (!password_verify($current_password, $admin['password'])) {
            $errors['current_password'] = "Current password is incorrect.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match.";
        }
        
        if (strlen($new_password) < 6) {
            $errors['new_password'] = "Password must be at least 6 characters.";
        }
    }
    
    if (empty($errors)) {
        $userObj->username = $username;
        $userObj->email = $email;
        if (!empty($new_password)) {
            $userObj->password = $new_password;
        }
        
        if ($userObj->editAdmin($_SESSION['user_id'])) {
            $success = "Profile updated successfully!";
            $admin = $userObj->fetchAdmin($_SESSION['user_id']); // Refresh data
        } else {
            $errors['general'] = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 font-sans min-h-screen">
<div class="flex min-h-screen">

    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 p-8">
        <header class="relative z-40 flex justify-between items-center mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-2xl border border-white/20 animate-fade-in">
            <div>
                <h2 class="text-3xl font-bold text-white drop-shadow-lg">Admin Profile</h2>
                <p class="text-sm text-gray-300 mt-1">Manage your account settings and email notifications</p>
            </div>
        </header>

        <!-- ALERT MESSAGES -->
        <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-600 text-green-700 px-4 py-3 rounded animate-fade-in-up">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif (!empty($errors['general'])): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-600 text-red-700 px-4 py-3 rounded animate-fade-in-up">
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Account Information -->
            <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
                <h3 class="text-lg font-semibold text-white mb-4">Account Information</h3>
                
                <form method="POST" class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-1">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" 
                               value="<?= htmlspecialchars($admin['username']) ?>" 
                               class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <?php if (!empty($errors['username'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['username'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" 
                               value="<?= htmlspecialchars($admin['email'] ?? '') ?>" 
                               class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <?php if (!empty($errors['email'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-300 mt-1">This email will receive all admin notifications</p>
                    </div>

                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">
                        Update Profile
                    </button>
                </form>
            </section>

            <!-- Change Password -->
            <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up">
                <h3 class="text-lg font-semibold text-white mb-4">Change Password</h3>
                
                <form method="POST" class="space-y-4">
                    <!-- Hidden fields to maintain username and email -->
                    <input type="hidden" name="username" value="<?= htmlspecialchars($admin['username']) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">

                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-1">
                            Current Password
                        </label>
                        <input type="password" name="current_password"
                               class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <?php if (!empty($errors['current_password'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['current_password'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-1">
                            New Password
                        </label>
                        <input type="password" name="new_password"
                               class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <?php if (!empty($errors['new_password'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['new_password'] ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-300 mt-1">Minimum 6 characters</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-1">
                            Confirm New Password
                        </label>
                        <input type="password" name="confirm_password"
                               class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['confirm_password'] ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300 w-full md:w-auto">
                        Change Password
                    </button>
                </form>
            </section>
        </div>

        <!-- Account Info Display -->
        <section class="bg-white/10 backdrop-blur-sm shadow-2xl rounded-2xl p-6 border border-white/20 animate-fade-in-up mt-6">
            <h3 class="text-lg font-semibold text-white mb-4">Account Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-100">User ID</p>
                    <p class="font-medium text-gray-300"><?= htmlspecialchars($admin['id']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-100">Role</p>
                    <p class="font-medium text-gray-300">Administrator</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Account Status</p>
                    <p class="font-medium text-green-600">Active</p>
                </div>
                <div>
                    <p class="text-sm text-gray-100">Account Created</p>
                    <p class="font-medium text-gray-300"><?= isset($admin['created_at']) ? date('M d, Y', strtotime($admin['created_at'])) : 'N/A' ?></p>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>