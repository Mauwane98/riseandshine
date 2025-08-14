<?php
// admin/members.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);
require_once __DIR__ . '/helpers/log_activity.php';
require_once __DIR__ . '/helpers/email.php';

// --- Configuration ---
define('MEMBERS_PER_PAGE', 10);
$registrations_file = __DIR__ . '/data/registrations.csv';
$message = '';

// --- Helper function to read/write CSV ---
function get_all_registrations($filePath) {
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

function save_registrations($filePath, $registrations) {
    if (($handle = fopen($filePath, 'w')) !== false) {
        if (!empty($registrations)) {
            fputcsv($handle, array_keys($registrations[0]));
            foreach ($registrations as $reg) {
                fputcsv($handle, $reg);
            }
        } else {
            fputcsv($handle, ['Full Name', 'Age', 'Email', 'Phone', 'Experience', 'Joining Fee', 'Proof File', 'Timestamp', 'Status']);
        }
        fclose($handle);
    }
}

// --- Handle Bulk Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_emails = $_POST['selected_members'] ?? [];

    if ($bulk_action !== '' && !empty($selected_emails)) {
        $all_regs = get_all_registrations($registrations_file);
        $members_to_notify = [];
        $member_names = [];
        
        if ($bulk_action === 'delete') {
            $regs_to_keep = array_filter($all_regs, function($reg) use ($selected_emails, &$member_names) {
                if (in_array($reg['Email'], $selected_emails)) {
                    $member_names[] = $reg['Full Name'];
                    return false;
                }
                return true;
            });
            save_registrations($registrations_file, $regs_to_keep);
            $message = count($selected_emails) . " member(s) deleted successfully.";
            log_action('Members Deleted', 'Deleted members: ' . implode(', ', $member_names));
        } else { // Approve, Decline, or Suspend
            $new_status = ucfirst($bulk_action);
            foreach ($all_regs as &$reg) {
                if (in_array($reg['Email'], $selected_emails)) {
                    $reg['Status'] = $new_status;
                    $members_to_notify[] = $reg;
                    $member_names[] = $reg['Full Name'];
                }
            }
            save_registrations($registrations_file, $all_regs);
            $message = count($selected_emails) . " member(s) updated to '$new_status'.";
            log_action('Members Status Changed', "Set status to '$new_status' for: " . implode(', ', $member_names));

            // --- Send Automated Emails ---
            $template_id = '';
            if ($bulk_action === 'approved') $template_id = 'template_welcome';
            elseif ($bulk_action === 'declined') $template_id = 'template_rejection';
            elseif ($bulk_action === 'suspended') $template_id = 'template_suspension';
            
            $emails_sent = 0;
            if ($template_id) {
                foreach ($members_to_notify as $member) {
                    if (send_template_email($template_id, $member)) {
                        $emails_sent++;
                    }
                }
            }
            if ($emails_sent > 0) {
                $message .= " ($emails_sent notification(s) sent).";
            }
        }
    } else {
        $message = "Please select an action and at least one member.";
    }
}

// --- Get current state from URL ---
$all_registrations = get_all_registrations($registrations_file);
$filter = $_GET['filter'] ?? 'all';
$search_query = trim($_GET['search'] ?? '');
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// --- Apply Filters and Search ---
$filtered_registrations = $all_registrations;
if ($filter !== 'all') {
    $filtered_registrations = array_filter($filtered_registrations, fn($reg) => isset($reg['Status']) && strtolower($reg['Status']) === $filter);
}
if (!empty($search_query)) {
    $filtered_registrations = array_filter($filtered_registrations, fn($reg) => stripos($reg['Full Name'] ?? '', $search_query) !== false || stripos($reg['Email'] ?? '', $search_query) !== false);
}

// --- Pagination Logic ---
$total_members = count($filtered_registrations);
$total_pages = ceil($total_members / MEMBERS_PER_PAGE);
$offset = ($current_page - 1) * MEMBERS_PER_PAGE;
$paginated_registrations = array_slice($filtered_registrations, $offset, MEMBERS_PER_PAGE);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members | Admin Panel</title>
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
        .status-pending, .status-approved, .status-suspended, .status-declined { padding: 5px 10px; border-radius: 20px; color: #fff; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; text-align: center; display: inline-block; }
        .status-pending { background-color: var(--status-pending); }
        .status-approved { background-color: var(--status-approved); }
        .status-suspended { background-color: var(--status-suspended); }
        .status-declined { background-color: var(--status-declined); }
        .page-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .search-form { display: flex; }
        .search-form input { padding: 10px; border: 1px solid var(--border-color); border-radius: 5px 0 0 5px; min-width: 250px; }
        .search-form button { padding: 10px 15px; border: 1px solid var(--accent); background-color: var(--accent); color: var(--primary-dark); border-radius: 0 5px 5px 0; cursor: pointer; font-weight: 600; }
        .filter-buttons a { padding: 8px 16px; text-decoration: none; background-color: #eee; color: #333; border-radius: 20px; font-weight: 600; margin: 0 5px; font-size: 0.9rem; }
        .filter-buttons a.active { background-color: var(--accent); color: var(--primary-dark); }
        .action-buttons a { padding: 6px 12px; text-decoration: none; border-radius: 5px; color: #fff; font-weight: 600; font-size: 0.8rem; margin-right: 5px; }
        .approve-btn { background-color: var(--status-approved); }
        .suspend-btn { background-color: #f39c12; }
        .proof-link { font-weight: 600; }
        .pagination-controls { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); }
        .pagination-controls a { padding: 8px 16px; text-decoration: none; background-color: var(--accent); color: var(--primary-dark); border-radius: 5px; font-weight: 700; }
        .pagination-controls .disabled { background-color: #ccc; cursor: not-allowed; pointer-events: none; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; background-color: var(--status-approved); }
        .bulk-actions { display: flex; align-items: center; gap: 10px; margin-top: 20px; }
        .bulk-actions select { padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; }
        .bulk-actions button { padding: 10px 15px; border: none; background-color: var(--secondary-dark); color: white; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .export-button { padding: 10px 20px; text-decoration: none; background-color: #2980b9; color: white; border-radius: 5px; font-weight: 600; font-size: 0.9rem; }
        @media (max-width: 992px) {
            .sidebar { position: fixed; top: 0; left: 0; height: 100%; z-index: 1000; transform: translateX(-100%); }
            .sidebar.is-open { transform: translateX(0); }
            .mobile-menu-button { display: block; }
            .main-content { width: 100%; }
            .content { padding: 20px; }
            .main-header h1 { font-size: 1.5rem; }
            .page-controls { flex-direction: column; align-items: stretch; }
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
                    <li><a href="members.php" class="active">Members</a></li>
                    <li><a href="events.php">Events</a></li>
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
                <h1>Manage Members</h1>
            </header>
            
            <section class="content">
                <?php if ($message): ?>
                    <div class="message"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="page-controls">
                        <form action="members.php" method="GET" class="search-form">
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                            <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query) ?>">
                            <button type="submit">Search</button>
                        </form>
                        <div class="filter-buttons">
                            <a href="members.php?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
                            <a href="members.php?filter=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
                            <a href="members.php?filter=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">Approved</a>
                            <a href="members.php?filter=suspended" class="<?= $filter === 'suspended' ? 'active' : '' ?>">Suspended</a>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <a href="export_members.php?filter=<?= $filter ?>&search=<?= urlencode($search_query) ?>" class="export-button">Export to CSV</a>
                    </div>

                    <form action="members.php" method="POST" id="bulk-actions-form">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($paginated_registrations)): ?>
                                        <?php foreach ($paginated_registrations as $reg): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected_members[]" value="<?= htmlspecialchars($reg['Email']) ?>" class="member-checkbox"></td>
                                                <td><?= htmlspecialchars($reg['Full Name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($reg['Email'] ?? 'N/A') ?></td>
                                                <td><span class="status-<?= strtolower(htmlspecialchars($reg['Status'] ?? 'unknown')) ?>"><?= htmlspecialchars($reg['Status'] ?? 'N/A') ?></span></td>
                                                <td class="action-buttons">
                                                    <?php $current_status = strtolower($reg['Status'] ?? ''); ?>
                                                    <?php if ($current_status === 'pending'): ?>
                                                        <a href="update_member_status.php?email=<?= urlencode($reg['Email']) ?>&status=approved" class="approve-btn">Approve</a>
                                                    <?php elseif ($current_status === 'approved'): ?>
                                                        <a href="update_member_status.php?email=<?= urlencode($reg['Email']) ?>&status=suspended" class="suspend-btn" onclick="return confirm('Are you sure you want to suspend this member?');">Suspend</a>
                                                    <?php elseif ($current_status === 'suspended'): ?>
                                                         <a href="update_member_status.php?email=<?= urlencode($reg['Email']) ?>&status=approved" class="approve-btn">Re-Approve</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" style="text-align:center;">No members found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="bulk-actions">
                            <select name="bulk_action" id="bulk_action">
                                <option value="">Bulk Actions...</option>
                                <option value="approved">Approve Selected</option>
                                <option value="declined">Decline Selected</option>
                                <option value="suspended">Suspend Selected</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button type="submit">Apply</button>
                        </div>
                    </form>

                    <div class="pagination-controls">
                        <div><a href="?page=<?= $current_page - 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search_query) ?>" class="<?= $current_page <= 1 ? 'disabled' : '' ?>">Previous</a></div>
                        <div>Page <?= $current_page ?> of <?= $total_pages > 0 ? $total_pages : 1 ?></div>
                        <div><a href="?page=<?= $current_page + 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search_query) ?>" class="<?= $current_page >= $total_pages ? 'disabled' : '' ?>">Next</a></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('is-open');
        });
        document.getElementById('select-all').addEventListener('click', function(event) {
            const checkboxes = document.querySelectorAll('.member-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        });
    </script>
</body>
</html>
