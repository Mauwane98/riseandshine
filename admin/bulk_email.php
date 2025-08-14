<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();
check_permission(['Admin']);
require_once __DIR__ . '/helpers/log_activity.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

function getMemberEmailsByStatus($statusFilter = null) {
    $filePath = __DIR__ . '/data/registrations.csv';
    $emails = [];
    if (!file_exists($filePath)) return $emails;

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV: id, name, email, ..., status, ...
            if (count($data) >= 8) {
                $status = $data[7];
                $email = $data[2];
                if ($statusFilter) {
                    if ($status == $statusFilter) {
                        $emails[] = $email;
                    }
                } else {
                    $emails[] = $email;
                }
            }
        }
        fclose($handle);
    }
    return array_unique($emails); // Return only unique email addresses
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_group = $_POST['recipient_group'] ?? null;
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($recipient_group) || empty($subject) || empty($message)) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'Please fill out all fields.';
    } else {
        $recipients = [];
        if ($recipient_group === 'all') {
            $recipients = getMemberEmailsByStatus();
        } else {
            $recipients = getMemberEmailsByStatus($recipient_group);
        }

        if (empty($recipients)) {
            $_SESSION['error'] = 1;
            $_SESSION['message'] = 'There are no members in the selected group.';
        } else {
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

                // Use BCC to protect member privacy
                foreach ($recipients as $email) {
                    $mail->addBCC($email);
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br($message);

                $mail->send();

                log_action('Bulk Email Sent', "Subject: '{$subject}' to {$recipient_group} members.");
                $_SESSION['message'] = 'Bulk email has been sent successfully to ' . count($recipients) . ' member(s).';

            } catch (Exception $e) {
                $_SESSION['error'] = 1;
                $_SESSION['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
    header('Location: bulk_email.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Email Members - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar" id="admin-sidebar">
            <h3>Admin Panel</h3>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li class="active"><a href="bulk_email.php"><i class="fas fa-paper-plane"></i> Bulk Email</a></li>
                    <li><a href="users.php"><i class="fas fa-user-shield"></i> Admin Users</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Public Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Bulk Email Members</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_logged_in_user'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo isset($_SESSION['error']) ? 'error' : 'success'; ?>"><?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <section class="admin-form-container">
                <h3>Compose Message</h3>
                <form action="bulk_email.php" method="post" class="styled-form">
                    <div class="form-group">
                        <label for="recipient_group">Send To</label>
                        <select id="recipient_group" name="recipient_group" required class="form-control" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem;">
                            <option value="">-- Select Recipient Group --</option>
                            <option value="all">All Members (Approved, Pending, Declined)</option>
                            <option value="approved">Approved Members Only</option>
                            <option value="pending">Pending Members Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="12" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Send Bulk Email</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('admin-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');

        if (sidebar && toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }
    });
</script>
</body>
</html>
