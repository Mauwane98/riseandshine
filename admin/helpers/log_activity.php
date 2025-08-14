<?php
// admin/helpers/log_activity.php

/**
 * Logs an administrative action to a CSV file.
 *
 * @param string $action The type of action performed (e.g., 'Login', 'Event Deleted').
 * @param string $details A description of the action.
 */
function log_action(string $action, string $details = '') {
    $log_file = __DIR__ . '/../data/activity_log.csv';
    $timestamp = date('Y-m-d H:i:s');

    // Get the username from the session if available
    $username = $_SESSION['admin_logged_in_user'] ?? 'System';

    $log_entry = [$timestamp, $username, $action, $details];

    // Create the file with a header if it doesn't exist
    if (!file_exists($log_file)) {
        if (($handle = fopen($log_file, 'w')) !== false) {
            fputcsv($handle, ['Timestamp', 'User', 'Action', 'Details']);
            fclose($handle);
        }
    }

    // Append the new log entry
    if (($handle = fopen($log_file, 'a')) !== false) {
        fputcsv($handle, $log_entry);
        fclose($handle);
    }
}

// Also update the login script to store the username in the session
// We can't edit that file now, but here's a note on what to change in login.php:
// After successful login:
// $_SESSION['admin_logged_in'] = true;
// $_SESSION['admin_logged_in_user'] = $username; // <-- ADD THIS LINE
// log_action('Login', "User '$username' logged in."); // <-- AND THIS ONE
