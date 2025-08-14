<?php
session_start();
require_once __DIR__ . '/helpers/log_activity.php';

// FIX: Updated to use the new session variable from your auth helper
if(isset($_SESSION['admin_logged_in_user'])) {
    log_action('Logout', "User '{$_SESSION['admin_logged_in_user']}' logged out.");
}

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the login page.
header('Location: login.php');
exit;
?>
