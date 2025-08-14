<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();
check_permission(['Admin']);

$events_file = __DIR__ . '/data/events.csv';
$registrations_file = __DIR__ . '/data/event_registrations.csv';

// --- Get Event Details ---
function getEventById($id) {
    global $events_file;
    if (file_exists($events_file) && ($handle = fopen($events_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (isset($data[0]) && $data[0] == $id) {
                fclose($handle);
                return ['id' => $data[0], 'name' => $data[1]];
            }
        }
        fclose($handle);
    }
    return null;
}

// --- Get Registrations for a specific event ---
function getRegistrationsForEvent($eventId) {
    global $registrations_file;
    $registrations = [];
    if (file_exists($registrations_file) && ($handle = fopen($registrations_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV: reg_id, event_id, event_name, registrant_name, registrant_email, timestamp
            if (isset($data[1]) && $data[1] == $eventId) {
                $registrations[] = [
                    'id' => htmlspecialchars($data[0]),
                    'name' => htmlspecialchars($data[3]),
                    'email' => htmlspecialchars($data[4]),
                    'date' => htmlspecialchars($data[5]),
                ];
            }
        }
        fclose($handle);
    }
    return $registrations;
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    header('Location: events.php');
    exit;
}

$event = getEventById($event_id);
if (!$event) {
    $_SESSION['error'] = 1;
    $_SESSION['message'] = 'Event not found.';
    header('Location: events.php');
    exit;
}

$registrations = getRegistrationsForEvent($event_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registrations - Admin</title>
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
                    <li class="active"><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="bulk_email.php"><i class="fas fa-paper-plane"></i> Bulk Email</a></li>
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
                <h2>Event Registrations</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_logged_in_user'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <section class="admin-table-container">
                <div class="table-controls">
                     <a href="events.php" class="action-btn" style="background-color: var(--secondary-text);"><i class="fas fa-arrow-left"></i> Back to Events</a>
                     <a href="export_registrations.php?event_id=<?php echo $event_id; ?>" class="btn-export"><i class="fas fa-file-csv"></i> Export This List</a>
                </div>

                <h3 style="margin-top: 2rem;">Registrants for: <?php echo htmlspecialchars($event['name']); ?></h3>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registrations)): ?>
                                <tr><td colspan="3">No one has registered for this event yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo $reg['name']; ?></td>
                                    <td><a href="mailto:<?php echo $reg['email']; ?>"><?php echo $reg['email']; ?></a></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($reg['date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
