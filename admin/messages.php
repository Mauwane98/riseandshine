<?php
// admin/messages.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);
require_once __DIR__ . '/helpers/log_activity.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Configuration ---
$data_dir = __DIR__ . '/data/';
$messages_file = $data_dir . 'messages.csv';
$message = '';

// --- Helper function to read/save messages ---
function get_messages($filePath) {
    if (!file_exists($filePath)) return [];
    $messages = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if ($header === false) { fclose($handle); return []; }
        while (($row = fgetcsv($handle)) !== false) {
            if (is_array($row) && count($row) === count($header)) {
                $messages[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return array_reverse($messages);
}

function save_messages($filePath, $messages) {
    $messages_to_save = array_reverse($messages);
    if (($handle = fopen($filePath, 'w')) !== false) {
        if (!empty($messages_to_save)) {
            fputcsv($handle, array_keys($messages_to_save[0]));
            foreach ($messages_to_save as $msg) { fputcsv($handle, $msg); }
        } else {
            fputcsv($handle, ['Timestamp', 'Name', 'Email', 'Message']);
        }
        fclose($handle);
    }
}


// --- Handle Form Actions (Delete/Reply) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'delete_message') {
        $message_id_to_delete = $_POST['message_id'];
        $all_messages = get_messages($messages_file);
        
        $sender_name = 'Unknown';
        foreach ($all_messages as $msg) {
            if ($msg['Timestamp'] === $message_id_to_delete) {
                $sender_name = $msg['Name'];
                break;
            }
        }

        $messages_to_keep = array_filter($all_messages, fn($msg) => $msg['Timestamp'] !== $message_id_to_delete);
        save_messages($messages_file, $messages_to_keep);
        $message = "Message deleted successfully.";
        log_action('Message Deleted', "Deleted a message from '$sender_name'.");
    }

    if ($_POST['action'] === 'send_reply') {
        require '../PHPMailer/src/PHPMailer.php';
        require '../PHPMailer/src/SMTP.php';
        require '../PHPMailer/src/Exception.php';

        $recipient_email = $_POST['recipient_email'];
        $recipient_name = $_POST['recipient_name'];
        $reply_subject = $_POST['reply_subject'];
        $reply_body = $_POST['reply_body'];

        if (filter_var($recipient_email, FILTER_VALIDATE_EMAIL) && !empty($reply_subject) && !empty($reply_body)) {
            $mail = new PHPMailer(true);
            try {
                // --- Use your actual SMTP settings ---
                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth   = true;
                $mail->Username   = '06d977bf35aa13';
                $mail->Password   = 'ef556c79ff96ea';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('no-reply@riseandshinechessclub.com', 'Rise & Shine Chess Club');
                $mail->addAddress($recipient_email, $recipient_name);
                $mail->addReplyTo('riseandshinechess@gmail.com', 'Rise & Shine Chess Club');

                $mail->isHTML(true);
                $mail->Subject = $reply_subject;
                $mail->Body    = nl2br($reply_body);
                $mail->AltBody = $reply_body;

                $mail->send();
                $message = 'Reply sent successfully!';
                log_action('Message Reply Sent', "Replied to a message from '$recipient_name'.");
            } catch (Exception $e) {
                $message = "Reply could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Error: Invalid data provided for reply.";
        }
    }
}

$all_messages = get_messages($messages_file);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages | Admin Panel</title>
    <style>
        :root {
            --primary-dark: #0d1321; --secondary-dark: #1d2d44; --accent: #fca311;
            --text-light: #e5e5e5; --bg-main: #f4f7fc; --text-dark: #333;
            --border-color: #e1e1e1; --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --status-pending: #e67e22; --status-approved: #2ecc71;
            --status-suspended: #f39c12; --status-declined: #e74c3c;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: var(--bg-main); color: var(--text-dark); font-size: 16px; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--primary-dark); color: var(--text-light); padding: 20px; display: flex; flex-direction: column; transition: transform 0.3s ease-in-out; }
        .sidebar h3 { color: var(--accent); text-align: center; margin-bottom: 30px; font-size: 1.5rem; }
        .sidebar nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar nav a { display: block; color: var(--text-light); text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; font-weight: 600; transition: background-color 0.3s, color 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: var(--accent); color: var(--primary-dark); }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; }
        .main-header { background: #fff; padding: 15px 30px; border-bottom: 1px solid var(--border-color); box-shadow: var(--shadow); display: flex; align-items: center; gap: 20px; }
        .main-header h1 { margin: 0; font-size: 1.8rem; flex-grow: 1; }
        .content { padding: 30px; flex: 1; }
        .mobile-menu-button { display: none; background: none; border: none; font-size: 2rem; color: var(--primary-dark); cursor: pointer; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: var(--shadow); }
        .message-card { background: #fff; border-radius: 8px; box-shadow: var(--shadow); margin-bottom: 20px; border-left: 5px solid var(--secondary-dark); }
        .message-header { padding: 15px 20px; background-color: #f9f9f9; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;}
        .message-header strong { font-size: 1.1rem; }
        .message-header .timestamp { font-size: 0.9rem; color: #666; }
        .message-body { padding: 20px; line-height: 1.6; }
        .message-body a { color: var(--accent); font-weight: 600; }
        .message-footer { padding: 10px 20px; text-align: right; display: flex; gap: 10px; justify-content: flex-end; }
        .action-btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .reply-btn { background-color: #3498db; color: #fff; }
        .delete-btn { background-color: var(--status-declined); color: #fff; }
        .message.feedback { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.feedback.success { background-color: var(--status-approved); }
        .message.feedback.error { background-color: var(--status-declined); }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 1000; padding: 20px;}
        .modal-content { background: #fff; padding: 30px; border-radius: 8px; width: 100%; max-width: 600px; position: relative; }
        .modal-content h2 { margin-top: 0; color: var(--primary-dark); }
        .modal-close { position: absolute; top: 15px; right: 15px; font-size: 1.5rem; cursor: pointer; border: none; background: none; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group label { font-weight: 600; margin-bottom: 5px; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; box-sizing: border-box; }
        .form-group textarea { min-height: 150px; resize: vertical; }
        @media (max-width: 992px) {
            .sidebar { position: fixed; top: 0; left: 0; height: 100%; z-index: 1000; transform: translateX(-100%); }
            .sidebar.is-open { transform: translateX(0); }
            .mobile-menu-button { display: block; }
            .main-content { width: 100%; }
            .content { padding: 20px; }
            .main-header h1 { font-size: 1.5rem; }
        }
        @media (max-width: 576px) { .content { padding: 15px; } .card { padding: 15px; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h3>Rise & Shine Admin</h3>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="members.php">Members</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="messages.php" class="active">Messages</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="email_templates.php">Email Templates</a></li>
                    <?php if (isset($_SESSION['admin_user_role']) && $_SESSION['admin_user_role'] === 'Admin'): ?>
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="users.php">Users</a></li>
                        <li><a href="activity_log.php">Activity Log</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <button class="mobile-menu-button" id="mobile-menu-btn">&#9776;</button>
                <h1>Contact Form Messages</h1>
            </header>
            
            <section class="content">
                <?php if ($message): ?>
                    <div class="message feedback <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <?php if (!empty($all_messages)): ?>
                    <?php foreach ($all_messages as $msg): ?>
                        <div class="message-card">
                            <div class="message-header">
                                <div><strong>From:</strong> <?= htmlspecialchars($msg['Name'] ?? 'N/A') ?></div>
                                <div class="timestamp"><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($msg['Timestamp'] ?? ''))) ?></div>
                            </div>
                            <div class="message-body">
                                <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($msg['Email'] ?? '') ?>"><?= htmlspecialchars($msg['Email'] ?? 'N/A') ?></a></p>
                                <p><?= nl2br(htmlspecialchars($msg['Message'] ?? '')) ?></p>
                            </div>
                            <div class="message-footer">
                                <button class="action-btn reply-btn" data-email="<?= htmlspecialchars($msg['Email']) ?>" data-name="<?= htmlspecialchars($msg['Name']) ?>">Reply</button>
                                <form action="messages.php" method="POST" onsubmit="return confirm('Are you sure?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?= htmlspecialchars($msg['Timestamp']) ?>">
                                    <button type="submit" class="action-btn delete-btn">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card"><p style="text-align:center;">There are no messages to display.</p></div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- Reply Modal -->
    <div class="modal-overlay" id="reply-modal">
        <div class="modal-content">
            <button class="modal-close" id="modal-close-btn">&times;</button>
            <h2>Send Reply</h2>
            <form action="messages.php" method="POST">
                <input type="hidden" name="action" value="send_reply">
                <input type="hidden" id="recipient-email-input" name="recipient_email">
                <input type="hidden" id="recipient-name-input" name="recipient_name">
                <div class="form-group">
                    <label for="reply-to">To:</label>
                    <input type="text" id="reply-to-display" disabled>
                </div>
                <div class="form-group">
                    <label for="reply-subject">Subject</label>
                    <input type="text" id="reply-subject" name="reply_subject" required>
                </div>
                <div class="form-group">
                    <label for="reply-body">Message</label>
                    <textarea id="reply-body" name="reply_body" required></textarea>
                </div>
                <button type="submit" class="action-btn reply-btn">Send Reply</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mobile-menu-btn').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('is-open');
            });

            const replyModal = document.getElementById('reply-modal');
            const closeBtn = document.getElementById('modal-close-btn');
            
            document.querySelectorAll('.reply-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const email = this.getAttribute('data-email');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('recipient-email-input').value = email;
                    document.getElementById('recipient-name-input').value = name;
                    document.getElementById('reply-to-display').value = `${name} <${email}>`;
                    
                    replyModal.style.display = 'flex';
                });
            });

            if(closeBtn) {
                closeBtn.addEventListener('click', () => replyModal.style.display = 'none');
            }
            
            replyModal.addEventListener('click', function(e) {
                if (e.target === replyModal) {
                    replyModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
