<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();

// --- Helper function to count rows in a CSV file ---
function countCsvRows($filePath, $skipHeader = true) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return 0;
    }
    $rowCount = 0;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rowCount++;
        }
        fclose($handle);
    }
    return $skipHeader ? max(0, $rowCount - 1) : $rowCount;
}

// --- Helper function to count upcoming events ---
function countUpcomingEvents($filePath) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return 0;
    }
    $upcomingCount = 0;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (isset($data[2]) && strtotime($data[2]) >= time()) {
                $upcomingCount++;
            }
        }
        fclose($handle);
    }
    return $upcomingCount;
}

// --- Get Statistics ---
$pendingMembersCount = countCsvRows(__DIR__ . '/data/registrations.csv');
$upcomingEventsCount = countUpcomingEvents(__DIR__ . '/data/events.csv');
$newMessagesCount = countCsvRows(__DIR__ . '/data/messages.csv');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Rise and Shine</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar" id="admin-sidebar">
            <h3>Admin Panel</h3>
            <nav>
                <ul>
                    <li class="active"><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
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
                <h2>Dashboard</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_logged_in_user'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <section class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="stat-info">
                        <p>Pending Members</p>
                        <span><?php echo $pendingMembersCount; ?></span>
                    </div>
                    <a href="members.php" class="stat-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <p>Upcoming Events</p>
                        <span><?php echo $upcomingEventsCount; ?></span>
                    </div>
                     <a href="events.php" class="stat-link">Manage Events <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                    <div class="stat-info">
                        <p>New Messages</p>
                        <span><?php echo $newMessagesCount; ?></span>
                    </div>
                     <a href="messages.php" class="stat-link">Read Messages <i class="fas fa-arrow-right"></i></a>
                </div>
            </section>

            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="members.php?status=pending" class="action-btn"><i class="fas fa-user-plus"></i> Approve Members</a>
                    <a href="events.php?action=add" class="action-btn"><i class="fas fa-plus-circle"></i> Create New Event</a>
                    <a href="gallery.php?action=add" class="action-btn"><i class="fas fa-camera-retro"></i> Upload to Gallery</a>
                </div>
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
