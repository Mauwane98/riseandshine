<?php
session_start();
require_once 'helpers/auth.php';
require_login();

// --- Function to get member registrations ---
function getMembers($statusFilter = null) {
    $filePath = 'data/registrations.csv';
    $members = [];
    if (!file_exists($filePath)) {
        return $members;
    }
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header row
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV structure: id, name, email, phone, dob, type, experience, status, date
            if (count($data) >= 8) {
                $member = [
                    'id' => htmlspecialchars($data[0]),
                    'name' => htmlspecialchars($data[1]),
                    'email' => htmlspecialchars($data[2]),
                    'phone' => htmlspecialchars($data[3]),
                    'dob' => htmlspecialchars($data[4]),
                    'type' => htmlspecialchars($data[5]),
                    'experience' => htmlspecialchars($data[6]),
                    'status' => htmlspecialchars($data[7]),
                    'date' => htmlspecialchars($data[8]),
                ];
                if ($statusFilter) {
                    if ($member['status'] == $statusFilter) {
                        $members[] = $member;
                    }
                } else {
                    $members[] = $member;
                }
            }
        }
        fclose($handle);
    }
    return array_reverse($members); // Show newest first
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
$allMembers = getMembers($statusFilter);

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
        <aside class="admin-sidebar">
            <h3>Admin Panel</h3>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
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
                <h2>Manage Members</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <section class="admin-table-container">
                <div class="table-controls">
                    <div class="filter-links">
                        <a href="members.php" class="<?php echo !$statusFilter ? 'active' : ''; ?>">All</a>
                        <a href="members.php?status=pending" class="<?php echo $statusFilter == 'pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="members.php?status=approved" class="<?php echo $statusFilter == 'approved' ? 'active' : ''; ?>">Approved</a>
                        <a href="members.php?status=declined" class="<?php echo $statusFilter == 'declined' ? 'active' : ''; ?>">Declined</a>
                    </div>
                    <a href="export_members.php" class="btn-export"><i class="fas fa-file-csv"></i> Export All</a>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo isset($_SESSION['error']) ? 'error' : 'success'; ?>">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Membership Type</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allMembers)): ?>
                            <tr>
                                <td colspan="6">No members found for this filter.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allMembers as $member): ?>
                                <tr>
                                    <td><?php echo $member['name']; ?></td>
                                    <td><a href="mailto:<?php echo $member['email']; ?>"><?php echo $member['email']; ?></a></td>
                                    <td><?php echo $member['type']; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($member['date'])); ?></td>
                                    <td><span class="status-badge status-<?php echo $member['status']; ?>"><?php echo ucfirst($member['status']); ?></span></td>
                                    <td class="action-links">
                                        <?php if ($member['status'] == 'pending'): ?>
                                            <a href="update_member_status.php?id=<?php echo $member['id']; ?>&status=approved" class="action-approve" title="Approve"><i class="fas fa-check"></i></a>
                                            <a href="update_member_status.php?id=<?php echo $member['id']; ?>&status=declined" class="action-decline" title="Decline"><i class="fas fa-times"></i></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
