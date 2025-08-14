<?php
// admin/gallery.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);
require_once __DIR__ . '/helpers/log_activity.php';

// --- Configuration ---
$data_dir = __DIR__ . '/data/';
$gallery_csv_file = $data_dir . 'gallery.csv';
$upload_dir = __DIR__ . '/../gallery_uploads/';
$message = '';
$error_message = '';

// Ensure directories exist
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// --- Helper function to read gallery data ---
function get_gallery_images($filePath) {
    if (!file_exists($filePath)) return [];
    $images = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0])) {
                $images[] = ['file' => $row[0], 'caption' => $row[1] ?? '', 'category' => $row[2] ?? 'General'];
            }
        }
        fclose($handle);
    }
    return array_reverse($images);
}

// --- Helper function to save gallery data ---
function save_gallery_images($filePath, $images) {
    $images_to_save = array_reverse($images);
    if (($handle = fopen($filePath, 'w')) !== false) {
        fputcsv($handle, ['File', 'Caption', 'Category']); // Add header
        foreach ($images_to_save as $image) {
            fputcsv($handle, [$image['file'], $image['caption'], $image['category']]);
        }
        fclose($handle);
    }
}

// --- Handle form submissions (Add/Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- ADD IMAGES ---
    if ($action === 'add_images') {
        if (isset($_FILES['images']) && !empty(array_filter($_FILES['images']['name']))) {
            $caption = trim($_POST['caption'] ?? '');
            $category = trim($_POST['category'] ?? 'General');
            $files = $_FILES['images'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $uploaded_count = 0;
            $uploaded_filenames = [];
            $images = get_gallery_images($gallery_csv_file);

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    if (in_array($files['type'][$i], $allowed_types) && $files['size'][$i] < 5000000) {
                        $safe_filename = uniqid() . '-' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($files['name'][$i]));
                        $target_path = $upload_dir . $safe_filename;

                        if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                            $images[] = ['file' => $safe_filename, 'caption' => $caption, 'category' => $category];
                            $uploaded_count++;
                            $uploaded_filenames[] = $safe_filename;
                        }
                    } else {
                         $error_message .= "File '{$files['name'][$i]}' is an invalid type or too large. ";
                    }
                }
            }
            
            if ($uploaded_count > 0) {
                save_gallery_images($gallery_csv_file, $images);
                $message = "$uploaded_count image(s) uploaded successfully!";
                log_action('Images Uploaded', "$uploaded_count image(s) added to '$category' category. Files: " . implode(', ', $uploaded_filenames));
            }
            if (!empty($error_message)) {
                 $message .= " " . $error_message;
            }

        } else {
            $message = "Error: No files uploaded or an error occurred.";
        }
    }

    // --- DELETE IMAGE ---
    if ($action === 'delete_image') {
        $file_to_delete = $_POST['image_file'] ?? '';
        if ($file_to_delete) {
            $images = get_gallery_images($gallery_csv_file);
            $images_to_keep = array_filter($images, fn($img) => $img['file'] !== $file_to_delete);

            if (count($images) > count($images_to_keep)) {
                save_gallery_images($gallery_csv_file, $images_to_keep);
                if (file_exists($upload_dir . $file_to_delete)) {
                    unlink($upload_dir . $file_to_delete);
                }
                $message = "Image deleted successfully!";
                log_action('Image Deleted', "Deleted image file: '$file_to_delete'");
            }
        }
    }
}

// --- Load all images for display ---
$all_images = get_gallery_images($gallery_csv_file);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery | Admin Panel</title>
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
        .form-group { display: flex; flex-direction: column; margin-bottom: 20px; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 5px; font-family: inherit; font-size: 1rem;
        }
        .btn-submit {
            padding: 10px 20px; background-color: var(--accent); color: var(--primary-dark);
            border: none; border-radius: 5px; font-weight: 700; cursor: pointer;
        }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.success { background-color: var(--status-approved); }
        .message.error { background-color: var(--status-declined); }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            background: #fff;
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .gallery-item .details {
            padding: 15px;
            flex-grow: 1;
        }
        .gallery-item .caption { font-size: 0.9rem; }
        .gallery-item .category-badge {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }
        .gallery-item .delete-form {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .gallery-item .delete-btn {
            padding: 8px; background-color: rgba(231, 76, 60, 0.8);
            color: #fff; border: none; border-radius: 50%;
            cursor: pointer; font-weight: 700; line-height: 1;
        }
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
                    <li><a href="gallery.php" class="active">Gallery</a></li>
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
                <h1>Manage Gallery</h1>
            </header>
            
            <section class="content">
                <?php if ($message): ?>
                    <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="card" style="margin-bottom: 30px;">
                    <h2>Upload New Images</h2>
                    <form action="gallery.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_images">
                        <div class="form-group">
                            <label for="images">Image Files (JPG, PNG, GIF - Max 5MB)</label>
                            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif" required multiple>
                        </div>
                        <div class="form-group">
                            <label for="caption">Common Caption (Optional)</label>
                            <input type="text" id="caption" name="caption" placeholder="This caption will apply to all uploaded images">
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" placeholder="e.g., Tournament, Training, Community" required>
                        </div>
                        <button type="submit" class="btn-submit">Upload Images</button>
                    </form>
                </div>

                <div class="card">
                    <h2>Existing Images</h2>
                    <div class="gallery-grid">
                        <?php if (!empty($all_images)): ?>
                            <?php foreach ($all_images as $image): ?>
                                <div class="gallery-item">
                                    <img src="../gallery_uploads/<?= htmlspecialchars($image['file']) ?>" alt="<?= htmlspecialchars($image['caption']) ?>">
                                    <div class="details">
                                        <div class="caption"><?= htmlspecialchars($image['caption'] ?: 'No caption') ?></div>
                                        <div class="category-badge"><?= htmlspecialchars($image['category']) ?></div>
                                    </div>
                                    <form class="delete-form" action="gallery.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="action" value="delete_image">
                                        <input type="hidden" name="image_file" value="<?= htmlspecialchars($image['file']) ?>">
                                        <button type="submit" class="delete-btn">&times;</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align:center;">No images in the gallery yet.</p>
                        <?php endif; ?>
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
