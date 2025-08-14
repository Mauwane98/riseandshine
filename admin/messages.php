<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();
require_once __DIR__ . '/helpers/log_activity.php';

$messages_file = __DIR__ . '/data/messages.csv';

function getMessages() {
    global $messages_file;
    $messages = [];
    if (file_exists($messages_file) && ($handle = fopen($messages_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if(count($data) >= 6) {
                 $messages[$data[0]] = ['id' => $data[0], 'name' => $data[1], 'email' => $data[2], 'subject' => $data[3], 'message' => $data[4], 'date' => $data[5]];
            }
        }
        fclose($handle);
    }
    return $messages;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $messages = getMessages();
    if (isset($messages[$id_to_delete])) {
        $deleted_message_subject = $messages[$id_to_delete]['subject'];
        unset($messages[$id_to_delete]);
        
        $handle = fopen($messages_file, 'w');
        fputcsv($handle, ['id', 'name', 'email', 'subject', 'message', 'timestamp']);
        foreach ($messages as $message) {
            fputcsv($handle, $message);
        }
        fclose($handle);

        log_action('Message Delete', "Deleted message: " . $deleted_message_subject);
        $_SESSION['message'] = 'Message deleted successfully!';
    }
    header('Location: messages.php');
    exit;
}

$allMessages = array_reverse(getMessages());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages - Admin</title>
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
                    <li class="active"><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
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
                <h2>Contact Messages</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_logged_in_user'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <section class="admin-table-container">
                <h3>Received Inquiries</h3>
                <table>
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allMessages)): ?>
                            <tr><td colspan="4">No messages found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allMessages as $msg): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong><br><small><?php echo htmlspecialchars($msg['email']); ?></small></td>
                                <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($msg['date'])); ?></td>
                                <td class="action-links">
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="action-reply" title="Reply"><i class="fas fa-reply"></i></a>
                                    <a href="?delete=<?php echo $msg['id']; ?>" onclick="return confirm('Are you sure you want to delete this message?');" class="action-delete" title="Delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <tr class="message-body-row">
                                <td colspan="4">
                                    <div class="message-body">
                                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
