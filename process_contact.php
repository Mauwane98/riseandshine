<?php
ob_start(); // FIX: Start output buffering
session_start();

// --- PHPMailer Integration ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- Basic Validation ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message_body = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($subject) || empty($message_body)) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Please fill in all fields with valid information.";
        header("Location: contact.php");
        exit();
    }

    // --- Data Storage ---
    $file_path = 'admin/data/messages.csv';
    $file = fopen($file_path, 'a');

    if ($file) {
        $message_data = [
            uniqid(),
            $name,
            $email,
            $subject,
            $message_body,
            date('Y-m-d H:i:s')
        ];
        fputcsv($file, $message_data);
        fclose($file);

        // --- Email Notification Logic ---
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'cp62.domains.co.za';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@riseandshinechess.co.za';
            $mail->Password   = 'Rise&Shine02';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Admin Notification Email
            $mail->setFrom('info@riseandshinechess.co.za', 'Contact Form');
            $mail->addAddress('info@riseandshinechess.co.za', 'Admin');
            $mail->addReplyTo($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'New Contact Form Message: ' . $subject;
            $mail->Body    = "You have received a new message from your website contact form.<br><br>" .
                             "<b>Name:</b> {$name}<br>" .
                             "<b>Email:</b> {$email}<br>" .
                             "<b>Subject:</b> {$subject}<br>" .
                             "<b>Message:</b><br>" . nl2br($message_body);
            $mail->send();

        } catch (Exception $e) {
            // Optional: Log the error, but don't block the user
            // error_log("Mailer Error from contact form: {$mail->ErrorInfo}");
        }

        // --- Set Success Message and Redirect ---
        $_SESSION['message'] = "Thank you for your message! We have received it and will get back to you shortly.";
        header("Location: success.php");
        exit();

    } else {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Error: Could not save your message. Please try again later.";
        header("Location: contact.php");
        exit();
    }
} else {
    // Redirect if accessed directly
    header("Location: contact.php");
    exit();
}
ob_end_flush(); // Send the output buffer
?>
