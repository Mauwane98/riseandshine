<?php
session_start();
require_once __DIR__ . '/helpers/auth.php';
require_login();
check_permission(['Admin']);
require_once __DIR__ . '/helpers/log_activity.php';

$registrations_file = __DIR__ . '/data/registrations.csv';
$upload_dir = __DIR__ . '/uploads/';

// --- Handle Member Deletion ---
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $all_members = [];
    $member_to_delete = null;
    $header = [];

    if (($read_handle = fopen($registrations_file, "r")) !== FALSE) {
        $header = fgetcsv($read_handle);
        while (($data = fgetcsv($read_handle, 1000, ",")) !== FALSE) {
            if ($data[0] == $id_to_delete) {
                $member_to_delete = $data;
            } else {
                $all_members[] = $data;
            }
        }
        fclose($read_handle);
    }

    if ($member_to_delete) {
        // Delete associated proof of payment file
        $proof_file = $upload_dir . $member_to_delete[6];
        if (!empty($member_to_delete[6]) && file_exists($proof_file) && is_file($proof_file)) {
            unlink($proof_file);
        }

        // Write the remaining members back to the file
        if (($write_handle = fopen($registrations_file, "w")) !== FALSE) {
            fputcsv($write_handle, $header);
            foreach ($all_members as $row) {
                fputcsv($write_handle, $row);
            }
            fclose($write_handle);
        }
        
        log_action('Member Delete', "Deleted member: {$member_to_delete[1]}");
        $_SESSION['message'] = 'Member record deleted successfully.';
    } else {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'Could not find the member to delete.';
    }
    header('Location: members.php');
    exit;
}


// --- Function to get member registrations with search and filter ---
function getMembers($statusFilter = null, $searchQuery = null) {
    $filePath = __DIR__ . '/data/registrations.csv';
    $members = [];
    if (!file_exists($filePath)) {
        return $members;
    }
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header row
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 9) {
                $member = [
                    'id' => htmlspecialchars($data[0]),
                    'name' => htmlspecialchars($data[1]),
                    'email' => htmlspecialchars($data[2]),
                    'phone' => htmlspecialchars($data[3]),
                    'age' => htmlspecialchars($data[4]),
                    'experience' => htmlspecialchars($data[5]),
                    'proof' => htmlspecialchars($data[6]),
                    'status' => htmlspecialchars($data[7]),
                    'date' => htmlspecialchars($data[8]),
                ];

                // Search filter
                if ($searchQuery && 
                    stripos($member['name'], $searchQuery) === false && 
                    stripos($member['email'], $searchQuery) === false) {
                    continue; // Skip if no match
                }

                // Status filter
                if ($statusFilter && $member['status'] != $statusFilter) {
                    continue; // Skip if no match
                }
                
                $members[] = $member;
            }
        }
        fclose($handle);
    }
    return array_reverse($members); // Show newest first
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;
$allMembers = getMembers($statusFilter, $searchQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - Admin</title>
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
                    <li class="active"><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
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
                <h2>Manage Members</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_logged_in_user'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <section class="admin-table-container">
                <div class="table-controls">
                    <div class="filter-links">
                        <a href="members.php" class="<?php echo !$statusFilter && !$searchQuery ? 'active' : ''; ?>">All</a>
                        <a href="members.php?status=pending" class="<?php echo $statusFilter == 'pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="members.php?status=approved" class="<?php echo $statusFilter == 'approved' ? 'active' : ''; ?>">Approved</a>
                        <a href="members.php?status=declined" class="<?php echo $statusFilter == 'declined' ? 'active' : ''; ?>">Declined</a>
                    </div>
                     <div>
                        <a href="bulk_email.php" class="action-btn" style="background-color: var(--success-color);"><i class="fas fa-paper-plane"></i> Send Bulk Email</a>
                        <a href="export_members.php" class="btn-export"><i class="fas fa-file-csv"></i> Export All</a>
                    </div>
                </div>
                
                <div class="search-form-container">
                    <form action="members.php" method="GET" class="search-form">
                        <input type="search" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>


                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo isset($_SESSION['error']) ? 'error' : 'success'; ?>">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Age</th>
                                <th>Experience</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allMembers)): ?>
                                <tr>
                                    <td colspan="7">No members found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allMembers as $member): ?>
                                    <tr>
                                        <td><?php echo $member['name']; ?></td>
                                        <td><a href="mailto:<?php echo $member['email']; ?>"><?php echo $member['email']; ?></a></td>
                                        <td><?php echo $member['age']; ?></td>
                                        <td><?php echo ucfirst($member['experience']); ?></td>
                                        <td>
                                            <?php if (!empty($member['proof'])): ?>
                                                <a href="uploads/<?php echo $member['proof']; ?>" target="_blank" class="action-links" style="color: var(--accent-color);">View</a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="status-badge status-<?php echo $member['status']; ?>"><?php echo ucfirst($member['status']); ?></span></td>
                                        <td class="action-links">
                                            <?php if ($member['status'] == 'pending'): ?>
                                                <a href="update_member_status.php?id=<?php echo $member['id']; ?>&status=approved" class="action-approve" title="Approve"><i class="fas fa-check"></i></a>
                                                <a href="update_member_status.php?id=<?php echo $member['id']; ?>&status=declined" class="action-decline" title="Decline"><i class="fas fa-times"></i></a>
                                            <?php endif; ?>
                                            <a href="members.php?delete=<?php echo $member['id']; ?>" class="action-delete" title="Delete" onclick="return confirm('Are you sure you want to permanently delete this member? This cannot be undone.');"><i class="fas fa-trash"></i></a>
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
