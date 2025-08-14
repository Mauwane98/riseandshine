<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/log_activity.php';

// Use PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Require login and Admin role to access this script
require_login();
check_permission(['Admin']);

$registrations_file = __DIR__ . '/data/registrations.csv';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id_to_update = $_GET['id'];
    $new_status = $_GET['status'];

    if (!in_array($new_status, ['approved', 'declined'])) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'Invalid status provided.';
        header('Location: members.php');
        exit;
    }

    $all_members = [];
    $member_to_update = null;
    $header = [];

    // Read all data into memory
    if (($handle = fopen($registrations_file, "r")) !== FALSE) {
        $header = fgetcsv($handle); // Store header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $all_members[] = $data;
        }
        fclose($handle);
    }

    // Find and update the specific member's status
    foreach ($all_members as &$member_row) {
        // CSV: id, name, email, phone, age, experience, proof, status, date
        if (isset($member_row[0]) && $member_row[0] == $id_to_update) {
            $member_row[7] = $new_status; // Update status column
            $member_to_update = [
                'name' => $member_row[1],
                'email' => $member_row[2]
            ];
            break;
        }
    }

    // Write the updated data back to the file
    if ($member_to_update) {
        if (($handle = fopen($registrations_file, "w")) !== FALSE) {
            fputcsv($handle, $header); // Write header back
            foreach ($all_members as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }

        log_action('Member Status Change', "Status for {$member_to_update['name']} set to {$new_status}");
        $_SESSION['message'] = "Membership status for " . htmlspecialchars($member_to_update['name']) . " has been updated.";

        // --- Send Email Notification ---
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

            $mail->setFrom('info@riseandshinechess.co.za', 'Rise and Shine Chess Club');
            $mail->addAddress($member_to_update['email'], $member_to_update['name']);
            $mail->isHTML(true);

            if ($new_status == 'approved') {
                $mail->Subject = 'Your Membership Application has been Approved!';
                $mail->Body    = "Dear {$member_to_update['name']},<br><br>Congratulations! Your application for membership at the Rise and Shine Chess Club has been approved. We are thrilled to welcome you to our community.<br><br>We look forward to seeing you at our next club night!<br><br>Sincerely,<br>The Rise and Shine Chess Club Team";
            } else { // Declined
                $mail->Subject = 'Update on Your Membership Application';
                $mail->Body    = "Dear {$member_to_update['name']},<br><br>Thank you for your interest in the Rise and Shine Chess Club. After reviewing your application, we are unable to approve it at this time. <br><br>If you believe this is a mistake or have any questions, please feel free to contact us.<br><br>Sincerely,<br>The Rise and Shine Chess Club Team";
            }
            $mail->send();
        } catch (Exception $e) {
            // Don't block the admin, but add a note that email failed
            $_SESSION['message'] .= " (Email notification could not be sent.)";
        }

    } else {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'Member ID not found.';
    }
}

header('Location: members.php');
exit;
?>
