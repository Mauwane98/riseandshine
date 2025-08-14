<?php
// admin/helpers/email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';

/**
 * Sends an email to a member using a pre-defined template.
 *
 * @param string $template_id The ID of the email template (e.g., 'template_welcome').
 * @param array $member An associative array of the member's details (must include 'Full Name' and 'Email').
 * @return bool True on success, false on failure.
 */
function send_template_email(string $template_id, array $member): bool {
    $templates_file = __DIR__ . '/../data/email_templates.csv';
    if (!file_exists($templates_file)) return false;

    $template_to_send = null;
    if (($handle = fopen($templates_file, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0]) && $row[0] === $template_id) {
                $template_to_send = ['id' => $row[0], 'name' => $row[1], 'subject' => $row[2], 'body' => $row[3]];
                break;
            }
        }
        fclose($handle);
    }

    if (!$template_to_send) return false; // Template not found

    // Replace placeholders
    $subject = str_replace('{name}', $member['Full Name'], $template_to_send['subject']);
    $body = str_replace('{name}', $member['Full Name'], $template_to_send['body']);

    $mail = new PHPMailer(true);
    try {
        // --- Real SMTP settings ---
        $mail->isSMTP();
        $mail->Host       = 'mail.riseandshinechess.co.za';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@riseandshinechess.co.za';
        $mail->Password   = 'YOUR_EMAIL_PASSWORD_HERE'; // Replace with actual password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('info@riseandshinechess.co.za', 'Rise & Shine Chess Club');
        $mail->addAddress($member['Email'], $member['Full Name']);
        $mail->addReplyTo('info@riseandshinechess.co.za', 'Rise & Shine Chess Club');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($body);
        $mail->AltBody = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // In a real application, you might log this error more formally
        error_log("Mailer Error for {$member['Email']}: {$mail->ErrorInfo}");
        return false;
    }
}
