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
    
    // Ensure the data directory exists
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');

    // Get the username from the session if available (compatible with current login script)
    $username = $_SESSION['username'] ?? $_SESSION['admin_logged_in_user'] ?? 'System';

    $log_entry = [$timestamp, $username, $action, $details];
    $header_row = ['Timestamp', 'User', 'Action', 'Details'];
    $file_exists = file_exists($log_file);

    // Open the file in append mode
    $handle = fopen($log_file, 'a');

    if ($handle === false) {
        // Error handling: could not open the file for writing.
        // In a production environment, you might trigger an error or log this failure.
        return;
    }

    // If the file is new or empty, add the header row
    if (!$file_exists || filesize($log_file) === 0) {
        fputcsv($handle, $header_row);
    }

    // Append the new log entry
    fputcsv($handle, $log_entry);
    
    // Close the file handle
    fclose($handle);
}
?>
