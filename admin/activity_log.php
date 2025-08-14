<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();

$log_file = __DIR__ . '/data/activity_log.csv';

function getActivityLog() {
    global $log_file;
    $log_entries = [];
    if (file_exists($log_file) && ($handle = fopen($log_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header row
        while (($data = fgetcsv($handle)) !== FALSE) {
            // CSV format: Timestamp, User, Action, Details
            if(count($data) >= 3) { // At least Timestamp, User, Action
                $entry = [
                    'timestamp' => htmlspecialchars($data[0]),
                    'user' => htmlspecialchars($data[1]),
                    'action' => htmlspecialchars($data[2]),
                    'details' => isset($data[3]) ? htmlspecialchars($data[3]) : ''
                ];
                $log_entries[] = $entry;
            }
        }
        fclose($handle);
    }
    return array_reverse($log_entries); // Show most recent first
}

$activityLog = getActivityLog();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin</title>
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
                    <li><a href="users.php"><i class="fas fa-user-shield"></i> Admin Users</a></li>
                    <li class="active"><a href="activity_log.php"><i class="fas fa-history"></i> Activity Log</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Public Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Admin Activity Log</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <section class="admin-table-container">
                <h3>Recent Actions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activityLog)): ?>
                            <tr><td colspan="4">No activity has been logged yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($activityLog as $entry): ?>
                                <tr>
                                    <td><?php echo $entry['timestamp']; ?></td>
                                    <td><?php echo $entry['user']; ?></td>
                                    <td><?php echo $entry['action']; ?></td>
                                    <td><?php echo $entry['details']; ?></td>
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
