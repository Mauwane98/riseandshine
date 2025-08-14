<?php
session_start();
require_once __DIR__ . '/helpers/log_activity.php'; 

$users_file = __DIR__ . '/data/users.csv';
$error_message = '';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $users = [];
    if (file_exists($users_file) && ($handle = fopen($users_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if(count($data) >= 2) {
                // CSV structure: username, password_hash, role (optional)
                $users[$data[0]] = [
                    'username' => $data[0], 
                    'password_hash' => $data[1],
                    'role' => $data[2] ?? 'Admin' // Default to 'Admin' if role column doesn't exist
                ];
            }
        }
        fclose($handle);
    }

    if (isset($users[$username]) && password_verify($password, $users[$username]['password_hash'])) {
        // Set the new session variables to match the auth helper
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_logged_in_user'] = $username;
        $_SESSION['admin_user_role'] = $users[$username]['role']; // Set the user's role

        log_action('Login', "User '$username' logged in.");
        header('Location: index.php');
        exit;
    } else {
        log_action('Login Failure', "Failed login attempt for username: " . $username);
        $error_message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Rise and Shine</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <img src="../logo.png" alt="Logo" class="login-logo">
            <h2>Admin Panel Login</h2>
            
            <?php if ($error_message): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">Login</button>
                </div>
            </form>
            <a href="../index.php" class="back-to-site">← Back to Public Site</a>
        </div>
    </div>
</body>
</html>
