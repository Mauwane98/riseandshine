<?php
// process_contact.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$timestamp = date('Y-m-d H:i:s');

if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.php?error=1');
    exit;
}

// --- Send email notification to admin ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'mail.riseandshinechess.co.za';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@riseandshinechess.co.za';
    $mail->Password   = 'YOUR_EMAIL_PASSWORD_HERE'; // Replace with actual password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('no-reply@riseandshinechess.co.za', 'Rise and Shine Chess Club Website');
    $mail->addAddress('info@riseandshinechess.co.za', 'Admin');
    $mail->addReplyTo($email, $name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Message: ' . $subject;
    $mail->Body    = "A new message has been submitted through the contact form.<br><br>" .
                     "<b>Name:</b> {$name}<br>" .
                     "<b>Email:</b> {$email}<br>" .
                     "<b>Subject:</b> {$subject}<br>" .
                     "<b>Message:</b><br>" . nl2br(htmlspecialchars($message));
    $mail->AltBody = "A new message has been submitted through the contact form.\n\n" .
                     "Name: {$name}\n" .
                     "Email: {$email}\n" .
                     "Subject: {$subject}\n" .
                     "Message:\n{$message}";

    $mail->send();
} catch (Exception $e) {
    // Log error but don't stop the process
    error_log("Contact form email error: {$mail->ErrorInfo}");
}

// --- Define path and ensure directory exists ---
$data_dir = __DIR__ . '/admin/data/';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}
$csvFile = $data_dir . 'messages.csv';

$header = ['Timestamp', 'Name', 'Email', 'Subject', 'Message'];
$data = [$timestamp, $name, $email, $subject, $message];

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
