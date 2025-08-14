<?php
session_start();
require_once 'helpers/auth.php';
require_login();
require_once 'helpers/log_activity.php';

$gallery_file = 'data/gallery.csv';
$upload_dir = '../gallery_uploads/';

function getGalleryImages() {
    global $gallery_file;
    $images = [];
    if (file_exists($gallery_file) && ($handle = fopen($gallery_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if(count($data) >= 3) {
                $images[$data[0]] = ['id' => $data[0], 'filename' => $data[1], 'caption' => $data[2]];
            }
        }
        fclose($handle);
    }
    return $images;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gallery_image'])) {
    $caption = $_POST['caption'] ?? 'Untitled';
    $filename = uniqid() . '-' . basename($_FILES['gallery_image']['name']);

    if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $upload_dir . $filename)) {
        $images = getGalleryImages();
        $id = uniqid();
        $images[$id] = ['id' => $id, 'filename' => $filename, 'caption' => $caption];
        
        $handle = fopen($gallery_file, 'w');
        fputcsv($handle, ['id', 'filename', 'caption']);
        foreach ($images as $image) {
            fputcsv($handle, $image);
        }
        fclose($handle);

        log_activity($_SESSION['username'] . " uploaded image: " . $filename);
        $_SESSION['message'] = 'Image uploaded successfully!';
    } else {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'Error uploading image.';
    }
    header('Location: gallery.php');
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $images = getGalleryImages();
    if (isset($images[$id_to_delete])) {
        $image_to_delete = $images[$id_to_delete];
        if (file_exists($upload_dir . $image_to_delete['filename'])) {
            unlink($upload_dir . $image_to_delete['filename']);
        }
        unset($images[$id_to_delete]);

        $handle = fopen($gallery_file, 'w');
        fputcsv($handle, ['id', 'filename', 'caption']);
        foreach ($images as $image) {
            fputcsv($handle, $image);
        }
        fclose($handle);

        log_activity($_SESSION['username'] . " deleted image: " . $image_to_delete['filename']);
        $_SESSION['message'] = 'Image deleted successfully!';
    }
    header('Location: gallery.php');
    exit;
}

$allImages = array_reverse(getGalleryImages());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Admin</title>
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
                    <li><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li class="active"><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="users.php"><i class="fas fa-user-shield"></i> Admin Users</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Public Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <h2>Manage Gallery</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo isset($_SESSION['error']) ? 'error' : 'success'; ?>"><?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <section class="admin-form-container">
                <h3>Upload New Image</h3>
                <form action="gallery.php" method="post" enctype="multipart/form-data" class="styled-form">
                    <div class="form-group">
                        <label for="gallery_image">Image File</label>
                        <input type="file" id="gallery_image" name="gallery_image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="caption">Caption</label>
                        <input type="text" id="caption" name="caption" placeholder="e.g., Club Tournament Finals" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Upload Image</button>
                    </div>
                </form>
            </section>

            <section class="admin-table-container">
                <h3>Uploaded Images</h3>
                <div class="gallery-admin-grid">
                    <?php if (empty($allImages)): ?>
                        <p>No images in the gallery.</p>
                    <?php else: ?>
                        <?php foreach ($allImages as $image): ?>
                        <div class="gallery-admin-item">
                            <img src="<?php echo $upload_dir . $image['filename']; ?>" alt="<?php echo $image['caption']; ?>">
                            <div class="gallery-admin-info">
                                <p><?php echo $image['caption']; ?></p>
                                <a href="?delete=<?php echo $image['id']; ?>" onclick="return confirm('Are you sure you want to delete this image?');" class="action-delete" title="Delete"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
