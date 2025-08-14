<?php
session_start();

// --- PHPMailer Integration ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- Basic Validation ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are present and not empty
    $required_fields = ['name', 'email', 'phone', 'dob', 'membership_type'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 1;
            $_SESSION['message'] = "Please fill in all required fields.";
            header("Location: membership.php#join-form");
            exit();
        }
    }

    // Sanitize input data
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $dob = trim($_POST['dob']); // Date validation is handled below
    $membership_type = filter_var(trim($_POST['membership_type']), FILTER_SANITIZE_STRING);
    $chess_experience = filter_var(trim($_POST['chess_experience']), FILTER_SANITIZE_STRING);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Invalid email format.";
        header("Location: membership.php#join-form");
        exit();
    }

    // Validate date of birth format (Y-m-d)
    $d = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$d || $d->format('Y-m-d') !== $dob) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Invalid date of birth format. Please use YYYY-MM-DD.";
        header("Location: membership.php#join-form");
        exit();
    }

    // --- Data Storage ---
    $file_path = 'admin/data/registrations.csv';
    $file = fopen($file_path, 'a'); // 'a' for append

    if ($file) {
        $registration_id = uniqid();
        $registration_data = [
            $registration_id,
            $name,
            $email,
            $phone,
            $dob,
            $membership_type,
            $chess_experience,
            'pending', // Default status
            date('Y-m-d H:i:s') // Registration timestamp
        ];

        fputcsv($file, $registration_data);
        fclose($file);

        // --- Email Notification Logic ---
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'cp62.domains.co.za'; // Updated Host
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@riseandshinechess.co.za';
            $mail->Password   = 'Rise&Shine02';      // Updated Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // --- Admin Notification Email ---
            $mail->setFrom('info@riseandshinechess.co.za', 'Rise and Shine Chess Club');
            $mail->addAddress('info@riseandshinechess.co.za', 'Admin'); // Send to admin
            $mail->addReplyTo($email, $name);
            
            $mail->isHTML(true);
            $mail->Subject = 'New Membership Application: ' . $name;
            $mail->Body    = "A new membership application has been submitted.<br><br>" .
                             "<b>Name:</b> {$name}<br>" .
                             "<b>Email:</b> {$email}<br>" .
                             "<b>Phone:</b> {$phone}<br>" .
                             "<b>Date of Birth:</b> {$dob}<br>" .
                             "<b>Membership Type:</b> {$membership_type}<br>" .
                             "<b>Chess Experience:</b> {$chess_experience}<br>" .
                             "<b>Registration ID:</b> {$registration_id}<br><br>" .
                             "You can review and approve this application in the admin panel.";
            $mail->send();

            // --- User Confirmation Email ---
            $mail->clearAddresses(); // Clear recipients for the next email
            $mail->addAddress($email, $name); // Send to the user
            
            $mail->Subject = 'Your Membership Application has been Received!';
            $mail->Body    = "Dear {$name},<br><br>" .
                             "Thank you for applying for a membership at the Rise and Shine Chess Club! We have successfully received your application.<br><br>" .
                             "Your application details:<br>" .
                             "<b>Membership Type:</b> {$membership_type}<br>" .
                             "<b>Registration ID:</b> {$registration_id}<br><br>" .
                             "Our team will review your application and get back to you shortly. We look forward to welcoming you to the club!<br><br>" .
                             "Sincerely,<br>" .
                             "The Rise and Shine Chess Club Team";
            $mail->send();

        } catch (Exception $e) {
            // Optional: Log the error, but don't block the user
            // For example: error_log("Mailer Error: {$mail->ErrorInfo}");
        }

        // --- Set Success Message and Redirect ---
        $_SESSION['message'] = "Thank you for your application! We have received it and will be in touch shortly.";
        unset($_SESSION['error']);
        header("Location: success.php");
        exit();

    } else {
        // --- Set Error Message and Redirect ---
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Error: Could not save your application. Please try again later.";
        header("Location: membership.php#join-form");
        exit();
    }
} else {
    // Redirect if accessed directly without POST method
    header("Location: membership.php");
    exit();
}
?>
