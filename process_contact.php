<?php
// process_contact.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$timestamp = date('Y-m-d H:i:s');

if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.php?error=1');
    exit;
}

// --- Define path and ensure directory exists ---
$data_dir = __DIR__ . '/admin/data/';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}
$csvFile = $data_dir . 'messages.csv';

$header = ['Timestamp', 'Name', 'Email', 'Message'];
$data = [$timestamp, $name, $email, $message];

// Create the file with a header if it doesn't exist
if (!file_exists($csvFile)) {
    $handle = fopen($csvFile, 'w');
    if ($handle) {
        fputcsv($handle, $header);
        fclose($handle);
    }
}

// Append the new message to the CSV file
$handle = fopen($csvFile, 'a');
if ($handle) {
    fputcsv($handle, $data);
    fclose($handle);
}

// Redirect to a success page
header('Location: success.php'); // Assuming you have a generic success page
exit;
