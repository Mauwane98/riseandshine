<?php
// Validate and process contact form submission
require_once 'honeypot.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user IP for rate limiting
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Check rate limiting
    if (!HoneypotProtection::checkRateLimit($user_ip, 'contact')) {
        header('Location: contact.php?error=' . urlencode('Too many submissions. Please wait before trying again.'));
        exit();
    }

    // Validate anti-spam protection
    if (!HoneypotProtection::validateSubmission($_POST)) {
        // Log potential spam attempt
        error_log("Spam attempt detected from IP: " . $user_ip . " at " . date('Y-m-d H:i:s'));
        header('Location: contact.php?error=' . urlencode('Security validation failed. Please try again.'));
        exit();
    }

    $name = HoneypotProtection::sanitizeInput($_POST['name']);
    $email = HoneypotProtection::sanitizeInput($_POST['email']);
    $subject = HoneypotProtection::sanitizeInput($_POST['subject']);
    $message = HoneypotProtection::sanitizeInput($_POST['message']);

    // Validate email format
    if (!HoneypotProtection::validateEmail($email)) {
        header('Location: contact.php?error=' . urlencode('Please enter a valid email address.'));
        exit();
    }

    // Additional validation
    if (strlen($name) < 2 || strlen($name) > 100) {
        header('Location: contact.php?error=' . urlencode('Name must be between 2 and 100 characters.'));
        exit();
    }

    if (strlen($subject) < 5 || strlen($subject) > 200) {
        header('Location: contact.php?error=' . urlencode('Subject must be between 5 and 200 characters.'));
        exit();
    }

    if (strlen($message) < 10 || strlen($message) > 2000) {
        header('Location: contact.php?error=' . urlencode('Message must be between 10 and 2000 characters.'));
        exit();
    }

    $timestamp = date('Y-m-d H:i:s');

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
} else {
    // If not a POST request, redirect to the contact form
    header('Location: contact.php');
    exit;
}