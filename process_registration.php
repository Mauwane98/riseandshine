<?php
// process_registration.php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    die("Invalid request or CSRF token.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// --- PATHS AND DIRECTORIES ---
$data_dir = __DIR__ . '/admin/data/';
$upload_dir = __DIR__ . '/admin/uploads/';
$csvFile = $data_dir . 'registrations.csv';

if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// --- HELPERS (omitted for brevity, assuming they exist) ---
function clean_input($val) { return htmlspecialchars(stripslashes(trim($val)), ENT_QUOTES, 'UTF-8'); }
function sendEmail($to, $name, $subject, $html, $plain = '') { /* ... email sending logic ... */ }

// --- VALIDATION (omitted for brevity, assuming it exists) ---
$errors = [];
$fullName = clean_input($_POST['fullName'] ?? '');
// ... other validation logic ...

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_form_data'] = $_POST;
    header('Location: join.php');
    exit;
}

// --- FILE UPLOAD & DATA SAVING ---
$fee = ($age < 10) ? 100 : (($age <= 16) ? 150 : 200);
$safeName = uniqid() . '-' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['proof']['name']));
$targetPath = $upload_dir . $safeName;

if (!move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) {
    // ... error handling ...
    header('Location: join.php');
    exit;
}

// Store data in CSV
$timestamp = date('Y-m-d H:i:s');
$rowData = [$fullName, $age, $email, $phone, $experience, $fee, $safeName, $timestamp, 'Pending'];
$header = ['Full Name', 'Age', 'Email', 'Phone', 'Experience', 'Joining Fee', 'Proof File', 'Timestamp', 'Status'];

if (!file_exists($csvFile)) {
    $handle = fopen($csvFile, 'w');
    fputcsv($handle, $header);
    fclose($handle);
}

$handle = fopen($csvFile, 'a');
fputcsv($handle, $rowData);
fclose($handle);

// --- EMAIL NOTIFICATIONS (omitted for brevity) ---
// ... send emails ...

header('Location: success.php');
exit;
