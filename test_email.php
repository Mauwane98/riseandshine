
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
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = '06d977bf35aa13';
    $mail->Password   = 'ef556c79ff96ea';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPDebug  = 2; // Enable verbose debug output

    //Recipients
    $mail->setFrom('no-reply@riseandshinechess.co.za', 'Rise and Shine Chess Club');
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
