<?php
// admin/reports.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin']); // Only Admins can view reports

// --- File Paths ---
$registrations_file = __DIR__ . '/data/registrations.csv';
$events_file = __DIR__ . '/data/events.csv';

// --- Helper function to read a CSV file ---
function read_csv($filePath) {
    if (!file_exists($filePath)) return [];
    $data = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if ($header === false) { fclose($handle); return []; }
        while (($row = fgetcsv($handle)) !== false) {
            if (is_array($row) && count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}

// --- Process Data for Reports ---
$all_registrations = read_csv($registrations_file);
$all_events = read_csv($events_file);

// Membership Stats
$member_stats = [
    'total' => count($all_registrations),
    'approved' => 0,
    'pending' => 0,
    'declined' => 0,
    'suspended' => 0,
];
foreach ($all_registrations as $reg) {
    $status = strtolower($reg['Status'] ?? 'pending');
    if (isset($member_stats[$status])) {
        $member_stats[$status]++;
    }
}

// Event Stats
$event_stats = [
    'total' => count($all_events),
    'upcoming' => 0,
    'past' => 0,
];
foreach ($all_events as $event) {
    if (strtotime($event['Date']) >= strtotime('today')) {
        $event_stats['upcoming']++;
    } else {
        $event_stats['past']++;
    }
}

// --- Prepare data for charts ---
$total_members_for_chart = $member_stats['total'] > 0 ? $member_stats['total'] : 1;
$approved_percent = round(($member_stats['approved'] / $total_members_for_chart) * 100);
$pending_percent = round(($member_stats['pending'] / $total_members_for_chart) * 100);
$declined_percent = round(($member_stats['declined'] / $total_members_for_chart) * 100);
$suspended_percent = round(($member_stats['suspended'] / $total_members_for_chart) * 100);

$total_events_for_chart = $event_stats['total'] > 0 ? $event_stats['total'] : 1;
$upcoming_percent = round(($event_stats['upcoming'] / $total_events_for_chart) * 100);
$past_percent = round(($event_stats['past'] / $total_events_for_chart) * 100);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | Admin Panel</title>
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
        .report-grid { display: grid; grid-template-columns: 1fr; gap: 30px; }
        .report-card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: var(--shadow); }
        .report-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px; }
        .report-card-header h2 { margin: 0; }
        .export-button { padding: 8px 16px; text-decoration: none; background-color: #2980b9; color: white; border-radius: 5px; font-weight: 600; font-size: 0.9rem; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-item { display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; padding: 10px; border-radius: 5px; background-color: #f9f9f9; }
        .stat-item strong { font-size: 1.5rem; color: var(--primary-dark); }
        .chart-container { display: flex; justify-content: space-around; align-items: flex-end; height: 250px; border: 1px solid var(--border-color); padding: 20px 10px 0; border-radius: 5px; background: #fdfdfd; }
        .chart-bar-wrapper { display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; text-align: center; }
        .chart-bar { width: 60px; background-color: #3498db; transition: height 0.6s ease-out; position: relative; border-radius: 5px 5px 0 0; }
        .chart-bar::after { content: attr(data-value) '%'; position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-weight: 700; font-size: 1rem; color: #333; }
        .chart-label { margin-top: 10px; font-weight: 600; font-size: 0.9rem; }
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
                    <li><a href="email_templates.php">Email Templates</a></li>
                    <?php if (isset($_SESSION['admin_user_role']) && $_SESSION['admin_user_role'] === 'Admin'): ?>
                        <li><a href="reports.php" class="active">Reports</a></li>
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
                <div class="report-card-header" style="border:none; padding:0; margin:0; width: 100%;">
                    <h1>Reports & Analytics</h1>
                    <a href="export_reports.php" class="export-button">Export All Stats</a>
                </div>
            </header>
            
            <section class="content">
                <div class="report-grid">
                    <div class="report-card">
                        <div class="report-card-header"><h2>Membership Overview</h2></div>
                        <div class="stats-container">
                            <div class="stat-item"><span>Total Members</span> <strong><?= $member_stats['total'] ?></strong></div>
                            <div class="stat-item"><span>Approved</span> <strong><?= $member_stats['approved'] ?></strong></div>
                            <div class="stat-item"><span>Pending</span> <strong><?= $member_stats['pending'] ?></strong></div>
                            <div class="stat-item"><span>Suspended</span> <strong><?= $member_stats['suspended'] ?></strong></div>
                        </div>
                        <div class="chart-container">
                            <div class="chart-bar-wrapper">
                                <div class="chart-bar" style="height: <?= $approved_percent ?>%; background-color: var(--status-approved);" data-value="<?= $approved_percent ?>"></div>
                                <div class="chart-label">Approved</div>
                            </div>
                            <div class="chart-bar-wrapper">
                                <div class="chart-bar" style="height: <?= $pending_percent ?>%; background-color: var(--status-pending);" data-value="<?= $pending_percent ?>"></div>
                                <div class="chart-label">Pending</div>
                            </div>
                            <div class="chart-bar-wrapper">
                                <div class="chart-bar" style="height: <?= $suspended_percent ?>%; background-color: var(--status-suspended);" data-value="<?= $suspended_percent ?>"></div>
                                <div class="chart-label">Suspended</div>
                            </div>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-card-header"><h2>Event Overview</h2></div>
                        <div class="stats-container">
                            <div class="stat-item"><span>Total Events</span> <strong><?= $event_stats['total'] ?></strong></div>
                            <div class="stat-item"><span>Upcoming</span> <strong><?= $event_stats['upcoming'] ?></strong></div>
                            <div class="stat-item"><span>Past</span> <strong><?= $event_stats['past'] ?></strong></div>
                        </div>
                         <div class="chart-container">
                            <div class="chart-bar-wrapper">
                                <div class="chart-bar" style="height: <?= $upcoming_percent ?>%; background-color: #3498db;" data-value="<?= $upcoming_percent ?>"></div>
                                <div class="chart-label">Upcoming</div>
                            </div>
                            <div class="chart-bar-wrapper">
                                <div class="chart-bar" style="height: <?= $past_percent ?>%; background-color: #95a5a6;" data-value="<?= $past_percent ?>"></div>
                                <div class="chart-label">Past</div>
                            </div>
                        </div>
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
