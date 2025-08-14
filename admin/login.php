<?php
// admin/login.php

session_start();
require_once __DIR__ . '/helpers/log_activity.php';
$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// --- Load users from CSV file ---
function get_all_users($filePath) {
    if (!file_exists($filePath)) return [];
    $users = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $users[$row[0]] = ['password_hash' => $row[1], 'role' => $row[2]];
            }
        }
        fclose($handle);
    }
    return $users;
}

$users_file = __DIR__ . '/data/users.csv';
$all_users = get_all_users($users_file);

// --- Handle Login Attempt ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($all_users[$username]) && password_verify($password, $all_users[$username]['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_logged_in_user'] = $username; // Store username for logging
        $_SESSION['admin_user_role'] = $all_users[$username]['role']; // Store role for future use
        session_regenerate_id(true);
        
        log_action('Login Success', "User '$username' logged in.");
        header('Location: index.php');
        exit;
    } else {
        log_action('Login Failed', "Failed login attempt for username: '$username'.");
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- The rest of the HTML is the same as your existing login.php -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Rise and Shine Chess Club</title>
    <style>
        :root {
            --primary-dark: #0d1321;
            --secondary-dark: #1d2d44;
            --accent: #fca311;
            --text-light: #e5e5e5;
            --error: #e74c3c;
            --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            font-family: var(--font-main);
            background-color: var(--secondary-dark);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: var(--primary-dark);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            color: var(--accent);
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #2a3a50;
            border: 1px solid #445;
            border-radius: 6px;
            color: var(--text-light);
            font-size: 1rem;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background-color: var(--accent);
            color: var(--primary-dark);
            font-weight: 700;
            border-radius: 30px;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .error-message {
            background-color: var(--error);
            color: #fff;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Panel Login</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
