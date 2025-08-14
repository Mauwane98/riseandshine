<?php
/**
 * Loads and groups gallery images by category from a CSV file.
 * @param string $filePath Path to the gallery.csv file.
 * @return array An array of images grouped by category.
 */
function get_gallery_images_by_category(string $filePath): array {
    $images_by_category = [];
    if (!file_exists($filePath)) {
        return [];
    }
    if (($handle = fopen($filePath, 'r')) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            if (isset($data[0])) {
                $image = [
                    'file' => $data[0],
                    'caption' => $data[1] ?? '',
                    'category' => trim($data[2] ?? 'General')
                ];
                // Group images by their category
                $images_by_category[$image['category']][] = $image;
            }
        }
        fclose($handle);
    }
    // Sort categories alphabetically
    ksort($images_by_category);
    return $images_by_category;
}

// --- Updated path to gallery.csv ---
$images_grouped = get_gallery_images_by_category(__DIR__ . '/admin/data/gallery.csv');
$categories = array_keys($images_grouped);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gallery | Rise and Shine Chess Club</title>
  <meta name="description" content="Explore the gallery of the Rise and Shine Chess Club, featuring photos from our tournaments, training sessions, and community events.">
  <style>
    :root {
      --primary-dark: #0d1321;
      --secondary-dark: #1d2d44;
      --accent: #fca311;
      --text-light: #e5e5e5;
      --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-main);
      line-height: 1.7;
      background-color: var(--secondary-dark);
      color: var(--text-light);
    }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    section { padding: 60px 0; }
    h2 { font-size: 2.5rem; text-align: center; margin-bottom: 20px; color: var(--accent); }
    h3 { font-size: 1.8rem; margin-bottom: 20px; color: var(--accent); border-bottom: 2px solid var(--accent); padding-bottom: 10px; }
    .intro-text { text-align: center; font-size: 1.1rem; margin-bottom: 40px; max-width: 700px; margin-left: auto; margin-right: auto; }

    header {
      background-color: var(--primary-dark); padding: 15px 0; position: sticky;
      top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }
    header .container { display: flex; justify-content: space-between; align-items: center; }
    .logo img { height: 50px; width: auto; display: block; }
    nav ul { list-style: none; display: flex; gap: 25px; }
    nav a { color: var(--text-light); text-decoration: none; font-weight: 600; padding-bottom: 5px; border-bottom: 2px solid transparent; transition: color 0.3s, border-color 0.3s; }
    nav a:hover, nav a.active { color: var(--accent); border-bottom-color: var(--accent); }
    #menu-toggle { display: none; }

    .category-filters {
        text-align: center;
        margin-bottom: 40px;
    }
    .filter-btn {
        background: var(--secondary-dark);
        color: var(--text-light);
        border: 2px solid var(--accent);
        padding: 10px 20px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        margin: 5px;
        transition: all 0.3s;
    }
    .filter-btn:hover, .filter-btn.active {
        background: var(--accent);
        color: var(--primary-dark);
    }

    .gallery-category-group { margin-bottom: 50px; }
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
    }
    .gallery-item {
      background: var(--primary-dark); border-radius: 8px; overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.3s ease;
      cursor: pointer; display: flex; flex-direction: column;
    }
    .gallery-item:hover { transform: translateY(-5px); }
    .gallery-item img { width: 100%; height: 250px; object-fit: cover; display: block; }
    .caption { padding: 15px; font-weight: 600; min-height: 60px; }
    .no-images { text-align: center; padding: 40px; background: var(--primary-dark); border-radius: 10px; }

    .lightbox {
      position: fixed; display: none; justify-content: center; align-items: center;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.9); z-index: 1000; padding: 20px;
    }
    .lightbox img { max-width: 90%; max-height: 90vh; border-radius: 8px; }
    .lightbox .close { position: absolute; top: 20px; right: 30px; font-size: 2.5rem; color: #fff; cursor: pointer; line-height: 1; }

    footer {
      background-color: var(--primary-dark); text-align: center;
      padding: 20px 0; margin-top: 60px; border-top: 2px solid var(--accent);
    }

    @media (max-width: 768px) {
      h2 { font-size: 2rem; }
      nav ul { display: none; flex-direction: column; position: absolute; top: 70px; right: 0; background: var(--primary-dark); width: 100%; padding: 20px 0; text-align: center; }
      nav ul.show { display: flex; }
      #menu-toggle { display: block; background: none; border: none; color: var(--text-light); font-size: 2rem; cursor: pointer; }
    }
  </style>
</head>
<body>

<header>
  <div class="container">
    <a href="index.php" class="logo"><img src="logo.png" alt="Rise and Shine Chess Club Logo"></a>
    <nav>
      <button id="menu-toggle" aria-label="Open Menu">&#9776;</button>
      <ul id="main-menu">
        <li><a href="index.php">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="membership.html">Membership</a></li>
        <li><a href="gallery.php" class="active">Gallery</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
    </nav>
  </div>
</header>

<main>
  <section class="gallery">
    <div class="container">
      <h2>Club Gallery</h2>
      <p class="intro-text">Take a look at some of our events, tournaments, and community activities. Click on any image to view it larger.</p>

      <?php if (!empty($categories)): ?>
        <div class="category-filters">
            <button class="filter-btn active" data-filter="all">Show All</button>
            <?php foreach ($categories as $category): ?>
                <button class="filter-btn" data-filter="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>"><?= htmlspecialchars($category) ?></button>
            <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($images_grouped)): ?>
        <div id="gallery-container">
            <?php foreach ($images_grouped as $category => $images): ?>
                <div class="gallery-category-group" data-category="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>">
                    <h3><?= htmlspecialchars($category) ?></h3>
                    <div class="gallery-grid">
                        <?php foreach ($images as $img): ?>
                            <div class="gallery-item">
                                <img src="gallery_uploads/<?= htmlspecialchars($img['file']) ?>" alt="<?= htmlspecialchars($img['caption'] ?: 'Chess Club Image') ?>" loading="lazy" />
                                <?php if (!empty($img['caption'])): ?>
                                    <div class="caption"><?= htmlspecialchars($img['caption']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="no-images">
          <p>Our gallery is currently empty. Photos from our events will be added soon!</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<div class="lightbox" id="lightbox">
  <span class="close" id="closeLightbox" aria-label="Close image viewer">&times;</span>
  <img id="lightboxImage" src="" alt="Full-size gallery image" />
</div>

<footer>
  <div class="container">
    <p>&copy; <?= date('Y') ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
  </div>
</footer>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Mobile Menu Toggle
    const menuToggle = document.getElementById('menu-toggle');
    const mainMenu = document.getElementById('main-menu');
    if (menuToggle) {
        menuToggle.addEventListener('click', () => mainMenu.classList.toggle('show'));
    }

    // Gallery Filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryGroups = document.querySelectorAll('.gallery-category-group');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Manage active button state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filter = button.getAttribute('data-filter');

            galleryGroups.forEach(group => {
                if (filter === 'all' || group.getAttribute('data-category') === filter) {
                    group.style.display = 'block';
                } else {
                    group.style.display = 'none';
                }
            });
        });
    });

    // Lightbox
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    const closeBtn = document.getElementById('closeLightbox');

    if (lightbox) {
        document.querySelectorAll('.gallery-item img').forEach(img => {
            img.addEventListener('click', () => {
                lightboxImg.src = img.src;
                lightbox.style.display = 'flex';
            });
        });
        if(closeBtn) {
            closeBtn.addEventListener('click', () => lightbox.style.display = 'none');
        }
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) lightbox.style.display = 'none';
        });
    }
  });
</script>

</body>
</html>
