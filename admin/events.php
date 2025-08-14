<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();
check_permission(['Admin']);
require_once __DIR__ . '/helpers/log_activity.php';

$events_file = __DIR__ . '/data/events.csv';
$registrations_file = __DIR__ . '/data/event_registrations.csv';
$upload_dir = '../event_uploads/';

// Function to get all events
function getEvents() {
    global $events_file;
    $events = [];
    if (file_exists($events_file) && ($handle = fopen($events_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if(count($data) >= 6) {
                $events[$data[0]] = ['id' => $data[0], 'name' => $data[1], 'date' => $data[2], 'location' => $data[3], 'description' => $data[4], 'poster' => $data[5]];
            }
        }
        fclose($handle);
    }
    return $events;
}

// --- NEW FEATURE: Function to count registrations for an event ---
function getRegistrationCount($eventId) {
    global $registrations_file;
    $count = 0;
    if (file_exists($registrations_file) && ($handle = fopen($registrations_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV: reg_id, event_id, ...
            if (isset($data[1]) && $data[1] == $eventId) {
                $count++;
            }
        }
        fclose($handle);
    }
    return $count;
}


// Handle form submission for adding/editing events
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_name'])) {
    $events = getEvents();
    $id = $_POST['event_id'] ?? uniqid();
    $poster_filename = $_POST['existing_poster'] ?? '';

    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $filename = uniqid() . '-' . basename($_FILES['poster']['name']);
        if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . $filename)) {
            if (!empty($poster_filename) && file_exists($upload_dir . $poster_filename)) {
                unlink($upload_dir . $poster_filename);
            }
            $poster_filename = $filename;
        }
    }

    $events[$id] = [
        'id' => $id,
        'name' => $_POST['event_name'],
        'date' => $_POST['event_date'],
        'location' => $_POST['location'],
        'description' => $_POST['description'],
        'poster' => $poster_filename
    ];

    $handle = fopen($events_file, 'w');
    fputcsv($handle, ['id', 'event_name', 'event_date', 'location', 'description', 'poster']);
    foreach ($events as $event) {
        fputcsv($handle, $event);
    }
    fclose($handle);

    $action_type = isset($_POST['event_id']) ? 'Event Update' : 'Event Create';
    log_action($action_type, "Event: {$_POST['event_name']}");
    $_SESSION['message'] = 'Event saved successfully!';
    header('Location: events.php');
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $events = getEvents();
    if (isset($events[$id_to_delete])) {
        $deleted_event_name = $events[$id_to_delete]['name'];
        if (!empty($events[$id_to_delete]['poster']) && file_exists($upload_dir . $events[$id_to_delete]['poster'])) {
            unlink($upload_dir . $events[$id_to_delete]['poster']);
        }
        unset($events[$id_to_delete]);

        $handle = fopen($events_file, 'w');
        fputcsv($handle, ['id', 'event_name', 'event_date', 'location', 'description', 'poster']);
        foreach ($events as $event) {
            fputcsv($handle, $event);
        }
        fclose($handle);

        log_action('Event Delete', "Deleted event: {$deleted_event_name}");
        $_SESSION['message'] = 'Event deleted successfully!';
    }
    header('Location: events.php');
    exit;
}

$allEvents = array_reverse(getEvents());
$edit_event = isset($_GET['edit']) ? getEvents()[$_GET['edit']] : null;
$show_form = isset($_GET['action']) && $_GET['action'] == 'add' || $edit_event;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin</title>
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
                <h2>Manage Events</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_logged_in_user'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <section class="admin-actions">
                <a href="?action=add" class="action-btn"><i class="fas fa-plus"></i> Add New Event</a>
            </section>

            <?php if ($show_form): ?>
            <section class="admin-form-container">
                <h3><?php echo $edit_event ? 'Edit Event' : 'Add New Event'; ?></h3>
                <form action="events.php" method="post" enctype="multipart/form-data" class="styled-form">
                    <?php if ($edit_event): ?>
                        <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                        <input type="hidden" name="existing_poster" value="<?php echo $edit_event['poster']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="event_name">Event Name</label>
                        <input type="text" id="event_name" name="event_name" value="<?php echo $edit_event['name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="event_date">Date</label>
                        <input type="date" id="event_date" name="event_date" value="<?php echo $edit_event['date'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo $edit_event['location'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required><?php echo $edit_event['description'] ?? ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="poster">Event Poster</label>
                        <input type="file" id="poster" name="poster" accept="image/*">
                        <?php if ($edit_event && !empty($edit_event['poster'])): ?>
                            <p>Current poster: <a href="<?php echo $upload_dir . $edit_event['poster']; ?>" target="_blank"><?php echo $edit_event['poster']; ?></a></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Save Event</button>
                        <a href="events.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </section>
            <?php endif; ?>

            <section class="admin-table-container">
                <h3>Existing Events</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Poster</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Registrations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allEvents)): ?>
                                <tr><td colspan="5">No events found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($allEvents as $event): ?>
                                <tr>
                                    <td><img src="<?php echo $upload_dir . $event['poster']; ?>" alt="Poster" class="table-thumb" onerror="this.style.display='none'"></td>
                                    <td><?php echo htmlspecialchars($event['name']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($event['date'])); ?></td>
                                    <td>
                                        <?php $reg_count = getRegistrationCount($event['id']); ?>
                                        <a href="view_registrations.php?event_id=<?php echo $event['id']; ?>" class="action-links" style="color: var(--accent-color);"><?php echo $reg_count; ?></a>
                                    </td>
                                    <td class="action-links">
                                        <a href="?edit=<?php echo $event['id']; ?>" class="action-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="?delete=<?php echo $event['id']; ?>" onclick="return confirm('Are you sure you want to delete this event?');" class="action-delete" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
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
