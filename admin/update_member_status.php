<?php
// admin/update_member_status.php

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);
require_once __DIR__ . '/helpers/log_activity.php';
require_once __DIR__ . '/helpers/email.php'; // Include the email helper

// --- Get parameters from URL ---
$email_to_update = $_GET['email'] ?? null;
$new_status = $_GET['status'] ?? null;

// --- Validate parameters ---
if (!$email_to_update || !in_array($new_status, ['approved', 'declined', 'suspended'])) {
    header('Location: members.php?error=invalidparams');
    exit;
}

$registrations_file = __DIR__ . '/data/registrations.csv';

// --- Read the entire CSV file into an array ---
$all_data = [];
if (($handle = fopen($registrations_file, 'r')) !== false) {
    $header = fgetcsv($handle);
    if ($header !== false) {
        $all_data[] = $header;
        while (($row = fgetcsv($handle)) !== false) {
            if (is_array($row) && count($row) === count($header)) {
                $all_data[] = $row;
            }
        }
    }
    fclose($handle);
} else {
    header('Location: members.php?error=fileread');
    exit;
}

// --- Find the member and update their status ---
$header = $all_data[0];
$email_column_index = array_search('Email', $header);
$status_column_index = array_search('Status', $header);
$updated = false;
$member_to_notify = null;

if ($email_column_index !== false && $status_column_index !== false) {
    for ($i = 1; $i < count($all_data); $i++) {
        if (isset($all_data[$i][$email_column_index]) && $all_data[$i][$email_column_index] === $email_to_update) {
            $all_data[$i][$status_column_index] = ucfirst($new_status);
            $member_to_notify = array_combine($header, $all_data[$i]); // Get member details for email
            $updated = true;
            break;
        }
    }
}

// --- Write the updated data back to the CSV file ---
if ($updated) {
    if (($handle = fopen($registrations_file, 'w')) !== false) {
        foreach ($all_data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        log_action('Member Status Changed', "Set status to '" . ucfirst($new_status) . "' for member: " . $member_to_notify['Full Name']);

        // --- Send Automated Email ---
        $template_id = '';
        if ($new_status === 'approved') {
            $template_id = 'template_welcome';
        } elseif ($new_status === 'declined') {
            $template_id = 'template_rejection';
        } elseif ($new_status === 'suspended') {
            $template_id = 'template_suspension';
        }

        if ($template_id) {
            send_template_email($template_id, $member_to_notify);
        }

    } else {
        header('Location: members.php?error=filewrite');
        exit;
    }
}

header('Location: members.php');
exit;
