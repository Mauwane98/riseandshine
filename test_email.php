
<?php
// test_email.php - Simple email test script

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
    $mail->SMTPDebug  = 2; // Enable verbose debug output

    //Recipients
    $mail->setFrom('info@riseandshinechess.co.za', 'Rise and Shine Chess Club');
    $mail->addAddress('test@example.com', 'Test User');

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Email Test - Rise and Shine Chess Club';
    $mail->Body    = 'This is a test email to verify SMTP configuration is working properly.';
    $mail->AltBody = 'This is a test email to verify SMTP configuration is working properly.';

    $mail->send();
    echo 'Test email sent successfully!';
} catch (Exception $e) {
    echo "Email test failed. Error: {$mail->ErrorInfo}";
}
?>
