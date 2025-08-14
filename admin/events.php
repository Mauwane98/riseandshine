<?php
// admin/events.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);
require_once __DIR__ . '/helpers/log_activity.php';

// --- Configuration ---
$data_dir = __DIR__ . '/data/';
$events_file = $data_dir . 'events.csv';
$upload_dir = __DIR__ . '/../event_uploads/';
$message = '';
$error = '';
$event_to_edit = null;

if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// --- Helper functions ---
function get_event_id($event) {
    return md5($event['title'] . $event['date'] . $event['time']);
}

function get_events($filePath) {
    if (!file_exists($filePath)) return [];
    $events = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if (!$header) { fclose($handle); return []; }
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 6) { // Now expecting 6 columns
                $event = [
                    'title' => $row[0], 'date' => $row[1], 'time' => $row[2],
                    'venue' => $row[3], 'description' => $row[4], 'flyer' => $row[5]
                ];
                $event['id'] = get_event_id($event);
                $events[] = $event;
            }
        }
        fclose($handle);
    }
    usort($events, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
    return $events;
}

function save_events($filePath, $events) {
    if (($handle = fopen($filePath, 'w')) !== false) {
        fputcsv($handle, ['Title', 'Date', 'Time', 'Venue', 'Description', 'Flyer']);
        usort($events, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
        foreach ($events as $event) {
            fputcsv($handle, [$event['title'], $event['date'], $event['time'], $event['venue'], $event['description'], $event['flyer'] ?? '']);
        }
        fclose($handle);
    }
}

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $events = get_events($events_file);

    if ($action === 'add_event' || $action === 'edit_event') {
        $title = trim($_POST['title']);
        $date = trim($_POST['date']);
        $time = trim($_POST['time']);
        $venue = trim($_POST['venue']);
        $description = trim($_POST['description']);
        $flyer_filename = $_POST['existing_flyer'] ?? '';

        if (empty($title) || empty($date) || empty($time) || empty($venue)) {
            $error = "Error: Title, Date, Time, and Venue are required fields.";
        } else {
            // Handle file upload
            if (isset($_FILES['flyer']) && $_FILES['flyer']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['flyer'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($file['type'], $allowed_types) && $file['size'] < 5000000) { // 5MB limit
                    $safe_filename = uniqid() . '-' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $safe_filename)) {
                        $flyer_filename = $safe_filename;
                    } else {
                        $error = "Error uploading flyer.";
                    }
                } else {
                    $error = "Invalid file type or size for flyer.";
                }
            }

            if (empty($error)) {
                if ($action === 'add_event') {
                    $events[] = ['title' => $title, 'date' => $date, 'time' => $time, 'venue' => $venue, 'description' => $description, 'flyer' => $flyer_filename];
                    $message = "Event added successfully!";
                    log_action('Event Added', "Added new event: '$title'");
                } else { // edit_event
                    $event_id_to_edit = $_POST['event_id'];
                    foreach ($events as &$event) {
                        if ($event['id'] === $event_id_to_edit) {
                            $event['title'] = $title; $event['date'] = $date; $event['time'] = $time;
                            $event['venue'] = $venue; $event['description'] = $description; $event['flyer'] = $flyer_filename;
                            break;
                        }
                    }
                    $message = "Event updated successfully!";
                    log_action('Event Edited', "Edited event: '$title'");
                }
                save_events($events_file, $events);
            }
        }
    }

    if ($action === 'delete_event') {
        $event_id_to_delete = $_POST['event_id'];
        $event_title_to_log = 'Unknown';
        $flyer_to_delete = '';
        foreach($events as $event) {
            if ($event['id'] === $event_id_to_delete) {
                $event_title_to_log = $event['title'];
                $flyer_to_delete = $event['flyer'];
                break;
            }
        }
        $events_to_keep = array_filter($events, fn($event) => $event['id'] !== $event_id_to_delete);
        save_events($events_file, $events_to_keep);
        
        if ($flyer_to_delete && file_exists($upload_dir . $flyer_to_delete)) {
            unlink($upload_dir . $flyer_to_delete);
        }
        $message = "Event deleted successfully!";
        log_action('Event Deleted', "Deleted event: '$event_title_to_log'");
    }
    
    $all_events = get_events($events_file);
} else {
    $all_events = get_events($events_file);
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    foreach ($all_events as $event) {
        if ($event['id'] === $_GET['id']) {
            $event_to_edit = $event;
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
    <title>Manage Events | Admin Panel</title>
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
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .full-width { grid-column: 1 / -1; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 5px; font-family: inherit; font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .btn-submit { padding: 10px 20px; background-color: var(--accent); color: var(--primary-dark); border: none; border-radius: 5px; font-weight: 700; cursor: pointer; justify-self: start; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.success { background-color: var(--status-approved); }
        .message.error { background-color: var(--status-declined); }
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
        @media (max-width: 576px) {
            .content { padding: 15px; }
            .card { padding: 15px; }
            .form-grid { grid-template-columns: 1fr; }
        }
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
                    <li><a href="events.php" class="active">Events</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="messages.php">Messages</a></li>
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
                <h1>Manage Events</h1>
            </header>
            
            <section class="content">
                <?php if ($message): ?><div class="message success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                <div class="card" style="margin-bottom: 30px;">
                    <h2><?= $event_to_edit ? 'Edit Event' : 'Add New Event' ?></h2>
                    <form action="events.php" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <input type="hidden" name="action" value="<?= $event_to_edit ? 'edit_event' : 'add_event' ?>">
                            <?php if ($event_to_edit): ?>
                                <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_to_edit['id']) ?>">
                                <input type="hidden" name="existing_flyer" value="<?= htmlspecialchars($event_to_edit['flyer'] ?? '') ?>">
                            <?php endif; ?>
                            
                            <div class="form-group full-width">
                                <label for="title">Event Title</label>
                                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($event_to_edit['title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="date">Event Date</label>
                                <input type="date" id="date" name="date" required value="<?= htmlspecialchars($event_to_edit['date'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="time">Event Time</label>
                                <input type="time" id="time" name="time" required value="<?= htmlspecialchars($event_to_edit['time'] ?? '') ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="venue">Venue / Location</label>
                                <input type="text" id="venue" name="venue" required value="<?= htmlspecialchars($event_to_edit['venue'] ?? '') ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required><?= htmlspecialchars($event_to_edit['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="flyer">Event Flyer (Optional)</label>
                                <input type="file" id="flyer" name="flyer" accept="image/jpeg,image/png,image/gif">
                                <?php if (!empty($event_to_edit['flyer'])): ?>
                                    <p style="margin-top:10px;">Current flyer: <a href="../event_uploads/<?= htmlspecialchars($event_to_edit['flyer']) ?>" target="_blank"><?= htmlspecialchars($event_to_edit['flyer']) ?></a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit" style="margin-top: 20px;"><?= $event_to_edit ? 'Update Event' : 'Add Event' ?></button>
                    </form>
                </div>

                <div class="card">
                    <h2>Existing Events</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date & Time</th>
                                    <th>Venue</th>
                                    <th>Flyer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($all_events)): ?>
                                    <?php foreach ($all_events as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['title']) ?></td>
                                            <td><?= date('F j, Y', strtotime($event['date'])) ?> at <?= date('g:i A', strtotime($event['time'])) ?></td>
                                            <td><?= htmlspecialchars($event['venue']) ?></td>
                                            <td>
                                                <?php if (!empty($event['flyer'])): ?>
                                                    <a href="../event_uploads/<?= htmlspecialchars($event['flyer']) ?>" target="_blank">View</a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="events.php?action=edit&id=<?= $event['id'] ?>" class="edit-btn">Edit</a>
                                                <form action="events.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_event">
                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                    <button type="submit" class="delete-btn">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center;">No events found.</td></tr>
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
