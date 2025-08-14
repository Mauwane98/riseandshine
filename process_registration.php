<?php
ob_start();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- CSRF Token Validation ---
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token validation failed.');
}

$errors = [];
$oldData = $_POST;
$upload_dir = 'admin/uploads/'; // Directory for proof of payment uploads

// --- Form Validation ---
$fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
$age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_STRING);
$digitalSignature = filter_input(INPUT_POST, 'digitalSignature', FILTER_SANITIZE_STRING);

if (empty($fullName)) $errors[] = 'Full Name is required.';
if ($age === false || $age < 5) $errors[] = 'A valid age is required.';
if ($email === false) $errors[] = 'A valid email address is required.';
if (empty($experience)) $errors[] = 'Please select your chess experience level.';
if (empty($digitalSignature) || $digitalSignature !== $fullName) $errors[] = 'Digital signature must match your full name.';
if (!isset($_POST['joiningFee'])) $errors[] = 'You must agree to the joining fee payment.';
if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Proof of payment is required.';

// --- Handle Validation Errors ---
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_form_data'] = $oldData;
    header('Location: join.php');
    exit;
}

// --- Handle File Upload ---
$proof_filename = '';
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
if (in_array($_FILES['proof']['type'], $allowed_types) && $_FILES['proof']['size'] < 5000000) { // 5MB limit
    $proof_filename = uniqid() . '-' . basename($_FILES['proof']['name']);
    if (!move_uploaded_file($_FILES['proof']['tmp_name'], $upload_dir . $proof_filename)) {
        $errors[] = 'There was an error uploading your proof of payment.';
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_form_data'] = $oldData;
        header('Location: join.php');
        exit;
    }
} else {
    $errors[] = 'Invalid file type or size for proof of payment (must be JPG, PNG, or PDF under 5MB).';
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_form_data'] = $oldData;
    header('Location: join.php');
    exit;
}

// --- Data Storage ---
$file_path = 'admin/data/registrations.csv';
if (($handle = fopen($file_path, 'a')) !== FALSE) {
    $registration_data = [
        uniqid(),
        $fullName,
        $email,
        $phone,
        $age,
        $experience,
        $proof_filename,
        'pending', // Default status
        date('Y-m-d H:i:s')
    ];
    fputcsv($handle, $registration_data);
    fclose($handle);
} else {
    // Handle file writing error
    $_SESSION['form_errors'] = ['An internal error occurred. Please try again later.'];
    $_SESSION['old_form_data'] = $oldData;
    header('Location: join.php');
    exit;
}

// --- Email Notifications ---
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'cp62.domains.co.za';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@riseandshinechess.co.za';
    $mail->Password   = 'Rise&Shine02';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Admin Notification
    $mail->setFrom('info@riseandshinechess.co.za', 'Rise and Shine Chess Club');
    $mail->addAddress('info@riseandshinechess.co.za', 'Admin');
    $mail->isHTML(true);
    $mail->Subject = 'New Membership Application: ' . $fullName;
    $mail->Body    = "A new membership application has been submitted.<br><br>" .
                     "<b>Name:</b> {$fullName}<br>" .
                     "<b>Age:</b> {$age}<br>" .
                     "<b>Email:</b> {$email}<br>" .
                     "<b>Phone:</b> {$phone}<br>" .
                     "<b>Experience:</b> {$experience}<br>" .
                     "Proof of payment is attached.<br><br>" .
                     "Please review the application in the admin panel.";
    $mail->addAttachment($upload_dir . $proof_filename);
    $mail->send();

    // User Confirmation
    $mail->clearAllRecipients();
    $mail->clearAttachments();
    $mail->addAddress($email, $fullName);
    $mail->Subject = 'Your Membership Application has been Received!';
    $mail->Body    = "Dear {$fullName},<br><br>" .
                     "Thank you for applying for a membership at the Rise and Shine Chess Club! We have successfully received your application and proof of payment.<br><br>" .
                     "Our team will review your application and get back to you shortly. We look forward to welcoming you to the club!<br><br>" .
                     "Sincerely,<br>" .
                     "The Rise and Shine Chess Club Team";
    $mail->send();

} catch (Exception $e) {
    // Optional: Log mail errors without stopping the user
}

// --- Success ---
unset($_SESSION['csrf_token']); // Unset token on success
$_SESSION['message'] = "Thank you for your application! We have received it and will be in touch shortly.";
header("Location: success.php");
exit();

ob_end_flush();
?>
