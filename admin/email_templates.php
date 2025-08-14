<?php
// admin/email_templates.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin']); // Only Admins can manage templates

require_once __DIR__ . '/helpers/log_activity.php';

$templates_file = __DIR__ . '/data/email_templates.csv';
$message = '';
$error = '';
$template_to_edit = null;

// --- Helper functions for CSV operations ---
function get_templates($filePath) {
    if (!file_exists($filePath)) { // Create default templates if file doesn't exist
        $default_templates = [
            ['id' => 'template_welcome', 'name' => 'Member Approval', 'subject' => 'Welcome to Rise & Shine Chess Club!', 'body' => "Dear {name},\n\nCongratulations! Your application to join the Rise & Shine Chess Club has been approved. We are thrilled to have you with us.\n\nOur weekly sessions are held at [Your Venue] every [Day] at [Time]. We look forward to seeing you there!\n\nBest regards,\nThe Rise & Shine Team"],
            ['id' => 'template_rejection', 'name' => 'Application Unsuccessful', 'subject' => 'Update on your Rise & Shine Chess Club Application', 'body' => "Dear {name},\n\nThank you for your interest in joining the Rise & Shine Chess Club. After reviewing your application, we are unable to approve it at this time due to [Reason - e.g., an issue with the proof of payment provided].\n\nPlease feel free to contact us if you have any questions.\n\nBest regards,\nThe Rise & Shine Team"],
            ['id' => 'template_suspension', 'name' => 'Membership Suspension', 'subject' => 'Notice of Membership Suspension', 'body' => "Dear {name},\n\nThis email is to inform you that your membership with the Rise & Shine Chess Club has been temporarily suspended due to [Reason - e.g., a violation of club policy].\n\nPlease contact the club administration to resolve this matter.\n\nBest regards,\nThe Rise & Shine Team"]
        ];
        save_templates($filePath, $default_templates);
        return $default_templates;
    }
    
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

function save_templates($filePath, $templates) {
    if (($handle = fopen($filePath, 'w')) !== false) {
        fputcsv($handle, ['ID', 'Name', 'Subject', 'Body']);
        foreach ($templates as $template) {
            fputcsv($handle, [$template['id'], $template['name'], $template['subject'], $template['body']]);
        }
        fclose($handle);
    }
}

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $templates = get_templates($templates_file);

    if ($action === 'save_template') {
        $id = $_POST['id'] ?? uniqid('template_');
        $name = trim($_POST['name']);
        $subject = trim($_POST['subject']);
        $body = trim($_POST['body']);

        if (empty($name) || empty($subject) || empty($body)) {
            $error = "All fields are required.";
        } else {
            $existing_template_index = -1;
            foreach ($templates as $index => $template) {
                if ($template['id'] === $id) {
                    $existing_template_index = $index;
                    break;
                }
            }

            if ($existing_template_index >= 0) { // Update existing
                $templates[$existing_template_index] = ['id' => $id, 'name' => $name, 'subject' => $subject, 'body' => $body];
                $message = "Template updated successfully!";
                log_action('Email Template Updated', "Updated template: '$name'");
            } else { // Add new
                $templates[] = ['id' => $id, 'name' => $name, 'subject' => $subject, 'body' => $body];
                $message = "Template created successfully!";
                log_action('Email Template Created', "Created new template: '$name'");
            }
            save_templates($templates_file, $templates);
        }
    }
    
    if ($action === 'delete_template') {
        $id_to_delete = $_POST['id'];
        if (in_array($id_to_delete, ['template_welcome', 'template_rejection', 'template_suspension'])) {
            $error = "You cannot delete the default system templates.";
        } else {
            $template_name = 'Unknown';
            $templates_to_keep = array_filter($templates, function($template) use ($id_to_delete, &$template_name) {
                if ($template['id'] === $id_to_delete) {
                    $template_name = $template['name'];
                    return false;
                }
                return true;
            });
            save_templates($templates_file, $templates_to_keep);
            $message = "Template deleted successfully!";
            log_action('Email Template Deleted', "Deleted template: '$template_name'");
        }
    }
    
    $all_templates = get_templates($templates_file);
} else {
    $all_templates = get_templates($templates_file);
}

// --- Handle GET request for editing ---
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    foreach ($all_templates as $template) {
        if ($template['id'] === $_GET['id']) {
            $template_to_edit = $template;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Templates | Admin Panel</title>
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
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        table th, table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        table thead th { background-color: #f9f9f9; font-weight: 700; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 5px; font-family: inherit; font-size: 1rem;
        }
        .form-group textarea { min-height: 200px; resize: vertical; }
        .btn-submit { padding: 10px 20px; background-color: var(--accent); color: var(--primary-dark); border: none; border-radius: 5px; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.success { background-color: var(--status-approved); }
        .message.error { background-color: var(--status-declined); }
        .placeholders-info { background-color: #f0f8ff; border-left: 5px solid #3498db; padding: 15px; margin-bottom: 20px; font-size: 0.9rem; }
        .action-buttons a, .action-buttons button { text-decoration: none; display: inline-block; padding: 5px 10px; border-radius: 5px; color: #fff; font-weight: 600; font-size: 0.9rem; margin-right: 5px; border: none; cursor: pointer; }
        .edit-btn { background-color: #3498db; }
        .delete-btn { background-color: var(--status-declined); }
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
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="email_templates.php" class="active">Email Templates</a></li>
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
                <h1>Manage Email Templates</h1>
            </header>
            
            <section class="content">
                <div class="card" style="margin-bottom: 30px;">
                    <h2><?= $template_to_edit ? 'Edit Template' : 'Create New Template' ?></h2>
                     <?php if ($message): ?><div class="message success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                     <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                    <div class="placeholders-info">
                        <strong>Available Placeholders:</strong> <code>{name}</code> will be replaced with the member's full name.
                    </div>
                    <form action="email_templates.php" method="POST">
                        <input type="hidden" name="action" value="save_template">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($template_to_edit['id'] ?? '') ?>">
                        <div class="form-group">
                            <label for="name">Template Name</label>
                            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($template_to_edit['name'] ?? '') ?>" placeholder="e.g., Member Approval Email">
                        </div>
                        <div class="form-group">
                            <label for="subject">Email Subject</label>
                            <input type="text" id="subject" name="subject" required value="<?= htmlspecialchars($template_to_edit['subject'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="body">Email Body</label>
                            <textarea id="body" name="body" required><?= htmlspecialchars($template_to_edit['body'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn-submit"><?= $template_to_edit ? 'Update Template' : 'Save Template' ?></button>
                    </form>
                </div>

                <div class="card">
                    <h2>Existing Templates</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($all_templates)): ?>
                                    <?php foreach ($all_templates as $template): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($template['name']) ?></td>
                                            <td><?= htmlspecialchars($template['subject']) ?></td>
                                            <td class="action-buttons">
                                                <a href="email_templates.php?action=edit&id=<?= $template['id'] ?>" class="edit-btn">Edit</a>
                                                <?php if (!in_array($template['id'], ['template_welcome', 'template_rejection', 'template_suspension'])): ?>
                                                <form action="email_templates.php" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_template">
                                                    <input type="hidden" name="id" value="<?= $template['id'] ?>">
                                                    <button type="submit" class="delete-btn">Delete</button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align:center;">No email templates found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('is-open');
        });
    </script>
</body>
</html>
