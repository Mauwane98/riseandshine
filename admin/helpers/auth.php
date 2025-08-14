<?php
// admin/helpers/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if the logged-in user has the required role to access a page.
 * If not, it redirects them or terminates the script.
 *
 * @param array $allowed_roles An array of roles that are allowed to access the page (e.g., ['Admin']).
 */
function check_permission(array $allowed_roles) {
    // 1. Check if user is logged in at all
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }

    // 2. Check if the user's role is in the list of allowed roles
    $user_role = $_SESSION['admin_user_role'] ?? 'Guest'; // Default to a role with no permissions
    if (!in_array($user_role, $allowed_roles)) {
        // For a better user experience, you could redirect to a dedicated 'access-denied.php' page
        http_response_code(403); // Forbidden
        die("Access Denied: You do not have permission to view this page.");
    }
}
