<?php
// admin/notifications.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);
require_once __DIR__ . '/helpers/log_activity.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$registrations_file = __DIR__ . '/data/registrations.csv';
$templates_file = __DIR__ . '/data/email_templates.csv';
$message = '';
$error = '';

// --- Helper to get approved members ---
function get_approved_members($filePath) {
    if (!file_exists($filePath)) return [];
    $members = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if ($header === false) { fclose($handle); return []; }
        while (($row = fgetcsv($handle)) !== false) {
            if (is_array($row) && count($row) === count($header)) {
                $reg = array_combine($header, $row);
                if (isset($reg['Status']) && strtolower($reg['Status']) === 'approved') {
                    $members[] = $reg;
                }
            }
        }
        fclose($handle);
    }
    return $members;
}

// --- Helper to get email templates ---
function get_templates($filePath) {
    if (!file_exists($filePath)) return [];
    $templates = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 4) {
                $templates[] = ['id' => $row[0], 'name' => $row[1], 'subject' => $row[2], 'body' => $row[3]];
            }
        }
        fclose($handle);
    }
    return $templates;
}


// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
    
    if (empty($subject) || empty($body)) {
        $error = "Subject and message body cannot be empty.";
    } else {
        $approved_members = get_approved_members($registrations_file);
        
        if (empty($approved_members)) {
            $error = "There are no approved members to send notifications to.";
        } else {
            require '../PHPMailer/src/PHPMailer.php';
            require '../PHPMailer/src/SMTP.php';
            require '../PHPMailer/src/Exception.php';

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
                $mail->addReplyTo('riseandshinechess@gmail.com', 'Rise & Shine Chess Club');
                
                foreach ($approved_members as $member) {
                    $mail->addBCC($member['Email'], $member['Full Name']);
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br($body);
                $mail->AltBody = $body;

                $mail->send();
                $message = 'Notification sent successfully to ' . count($approved_members) . ' member(s).';
                log_action('Bulk Notification Sent', "Subject: '$subject'");
            } catch (Exception $e) {
                $error = "Notification could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}

$approved_member_count = count(get_approved_members($registrations_file));
$email_templates = get_templates($templates_file);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notifications | Admin Panel</title>
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
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px;}
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 5px; font-family: inherit; font-size: 1rem;
        }
        .form-group textarea { min-height: 250px; resize: vertical; }
        .btn-submit {
            padding: 10px 20px; background-color: var(--accent); color: var(--primary-dark);
            border: none; border-radius: 5px; font-weight: 700; cursor: pointer;
            margin-top: 10px;
        }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.success { background-color: var(--status-approved); }
        .message.error { background-color: var(--status-declined); }
        .recipient-info {
            background-color: #f0f8ff;
            border-left: 5px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }
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
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="notifications.php" class="active">Notifications</a></li>
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
                <h1>Send Member Notifications</h1>
            </header>
            
            <section class="content">
                <div class="card">
                    <h2>Compose Email</h2>

                    <?php if ($message): ?>
                        <div class="message success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="message error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="recipient-info">
                        This email will be sent to all <strong><?= $approved_member_count ?></strong> approved member(s).
                    </div>

                    <form action="notifications.php" method="POST">
                        <div class="form-group">
                            <label for="template-selector">Load from Template (Optional)</label>
                            <select id="template-selector">
                                <option value="">-- Select a template --</option>
                                <?php foreach($email_templates as $template): ?>
                                    <option value="<?= htmlspecialchars($template['id']) ?>"><?= htmlspecialchars($template['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="body">Message Body</label>
                            <textarea id="body" name="body" required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Send Notification</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('is-open');
        });

        const templates = <?= json_encode(array_column($email_templates, null, 'id')) ?>;

        document.getElementById('template-selector').addEventListener('change', function() {
            const templateId = this.value;
            if (templateId && templates[templateId]) {
                const selectedTemplate = templates[templateId];
                document.getElementById('subject').value = selectedTemplate.subject;
                document.getElementById('body').value = selectedTemplate.body;
            } else {
                document.getElementById('subject').value = '';
                document.getElementById('body').value = '';
            }
        });
    </script>
</body>
</html>
