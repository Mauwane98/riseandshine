<?php
// index.php

// --- Data Loading Functions ---

/**
 * Loads and filters upcoming events from a CSV file.
 * @param string $filePath Path to the events.csv file.
 * @return array An array of upcoming events, sorted by date.
 */
function get_upcoming_events(string $filePath): array {
    $events = [];
    if (!file_exists($filePath)) {
        return [];
    }
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle); // Skip header row
        if (!$header) { fclose($handle); return []; }
        
        while (($data = fgetcsv($handle)) !== false) {
            // Updated to handle 5 columns and check date
            if (count($data) >= 5 && strtotime($data[1]) >= strtotime('today')) {
                $events[] = [
                    'title'       => $data[0],
                    'date'        => $data[1],
                    'time'        => $data[2],
                    'venue'       => $data[3],
                    'description' => $data[4]
                ];
            }
        }
        fclose($handle);
    }
    // Sort events by date, earliest first
    usort($events, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
    return $events;
}

/**
 * Loads gallery images from a CSV file.
 * @param string $filePath Path to the gallery.csv file.
 * @return array An array of gallery images.
 */
function get_gallery_images(string $filePath): array {
    $images = [];
    if (!file_exists($filePath)) {
        return [];
    }
    if (($handle = fopen($filePath, 'r')) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            if (isset($data[0])) {
                $images[] = [
                    'file' => $data[0],
                    'caption' => $data[1] ?? ''
                ];
            }
        }
        fclose($handle);
    }
    return array_reverse($images); // Show newest first
}

// --- Updated paths to data files ---
$events = get_upcoming_events(__DIR__ . '/admin/data/events.csv');
$images = get_gallery_images(__DIR__ . '/admin/data/gallery.csv');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rise and Shine Chess Club - Nellmapius, Pretoria</title>
  <meta name="description" content="A community-focused chess club in Nellmapius, Pretoria, dedicated to fostering strategic thinking and intellectual growth for all ages.">
  <style>
    /* --- General Styling & Variables --- */
    :root {
      --primary-dark: #0d1321;
      --secondary-dark: #1d2d44;
      --accent: #fca311;
      --text-light: #e5e5e5;
      --text-dark: #333;
      --bg-light: #ffffff;
      --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      --container-width: 1100px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-main);
      line-height: 1.7;
      background-color: var(--secondary-dark);
      color: var(--text-light);
    }
    .container {
      max-width: var(--container-width);
      margin: 0 auto;
      padding: 0 20px;
    }
    section { padding: 60px 0; }
    h1, h2, h3, h4 { line-height: 1.3; }
    h3 { font-size: 2rem; text-align: center; margin-bottom: 40px; color: var(--accent); }

    /* --- Header & Navigation --- */
    header {
      background-color: var(--primary-dark);
      padding: 15px 0;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }
    header .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo img {
      height: 60px;
      width: auto;
      display: block;
    }
    .logo { font-size: 1.5rem; color: var(--accent); }
    nav ul { list-style: none; display: flex; gap: 25px; }
    nav a {
      color: var(--text-light);
      text-decoration: none;
      font-weight: 600;
      padding-bottom: 5px;
      border-bottom: 2px solid transparent;
      transition: color 0.3s, border-color 0.3s;
    }
    nav a:hover, nav a.active { color: var(--accent); border-bottom-color: var(--accent); }
    #menu-toggle { display: none; }

    /* --- Buttons --- */
    .btn {
      display: inline-block;
      padding: 12px 28px;
      background-color: var(--accent);
      color: var(--primary-dark);
      text-decoration: none;
      font-weight: 700;
      border-radius: 30px;
      transition: transform 0.3s, background-color 0.3s;
      border: 2px solid var(--accent);
    }
    .btn:hover { transform: translateY(-3px); }
    .btn-secondary { background: transparent; color: var(--accent); }

    /* --- Hero Section --- */
    .hero {
      background: linear-gradient(rgba(13, 19, 33, 0.8), rgba(13, 19, 33, 0.8)), url('chess-9536049_1920.jpg') no-repeat center center/cover;
      min-height: 80vh;
      display: flex;
      align-items: center;
      text-align: center;
      color: #fff;
    }
    .hero h2 { font-size: 3rem; margin-bottom: 15px; }
    .hero p { font-size: 1.2rem; max-width: 600px; margin: 0 auto 30px; }
    .hero .btn { margin: 10px; }

    /* --- Card & Grid Layouts --- */
    .card {
      background: var(--primary-dark);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .mission-vision .container, .event-grid, .gallery-grid {
      display: grid;
      gap: 30px;
    }
    .mission-vision .container { grid-template-columns: 1fr 1fr; }
    .event-grid { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
    .gallery-grid { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
    .event-card h4, .mission-vision h4 { color: var(--accent); margin-bottom: 15px; font-size: 1.5rem; }
    .event-card .event-details { font-weight: 600; color: #ccc; margin-bottom: 10px; }
    .event-card .event-details span { display: block; margin-bottom: 5px; }

    /* --- Specific Sections --- */
    #about { background-color: var(--primary-dark); text-align: center; }
    #about .btn { margin-top: 20px; }
    #membership { text-align: center; }
    #gallery .gallery-item img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      border-radius: 8px;
      display: block;
    }
    #contact { background-color: var(--primary-dark); text-align: center; }

    /* --- Footer --- */
    footer {
      background-color: var(--primary-dark);
      text-align: center;
      padding: 20px 0;
      border-top: 2px solid var(--accent);
    }

    /* --- Responsive Design --- */
    @media (max-width: 768px) {
      h3 { font-size: 1.8rem; }
      .hero h2 { font-size: 2.2rem; }
      nav ul {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 70px;
        right: 0;
        background: var(--primary-dark);
        width: 100%;
        padding: 20px 0;
        text-align: center;
      }
      nav ul.show { display: flex; }
      #menu-toggle {
        display: block;
        background: none;
        border: none;
        color: var(--text-light);
        font-size: 2rem;
        cursor: pointer;
      }
      .mission-vision .container { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<header>
  <div class="container">
    <a href="index.php" class="logo">
      <img src="logo.png" alt="Rise and Shine Chess Club Logo">
    </a>
    
    <nav>
      <button id="menu-toggle" aria-label="Open Menu">&#9776;</button>
      <ul id="main-menu">
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="membership.html">Membership</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
    </nav>
  </div>
</header>

<main>
  <section id="home" class="hero">
    <div class="container">
      <h2>Welcome to Rise and Shine Chess Club</h2>
      <p>Inspiring growth, strategy, and community through chess in Nellmapius, Pretoria.</p>
      <a href="membership.html" class="btn">Join the Club</a>
      <a href="events.php" class="btn btn-secondary">View All Events</a>
    </div>
  </section>

  <section id="about" class="about">
    <div class="container">
      <h3>About Our Club</h3>
      <p>Rise and Shine Chess Club is a development-focused club registered with Tshwane Chess, founded in September 2024 to promote chess in Nellmapius and beyond.</p>
      <a href="about.html" class="btn">Read Our Story</a>
    </div>
  </section>

  <section class="mission-vision">
    <div class="container">
      <div class="card">
        <h4>🎯 Our Mission</h4>
        <p>To foster an inclusive community where people of all ages and skill levels can grow, learn, and excel through the game of chess.</p>
      </div>
      <div class="card">
        <h4>👁️ Our Vision</h4>
        <p>To become a leading hub of chess development and enthusiasm in our community, empowering individuals through intellectual growth and strategic thinking.</p>
      </div>
    </div>
  </section>

  <section id="events" class="events">
    <div class="container">
      <h3>Upcoming Events</h3>
      <div class="event-grid">
        <?php if (!empty($events)): ?>
          <?php foreach (array_slice($events, 0, 3) as $event): ?>
            <div class="card event-card">
              <h4><?= htmlspecialchars($event['title']) ?></h4>
              <div class="event-details">
                  <span>📅 <?= date('F j, Y', strtotime($event['date'])) ?> at <?= date('g:i A', strtotime($event['time'])) ?></span>
                  <span>📍 <?= htmlspecialchars($event['venue']) ?></span>
              </div>
              <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; grid-column: 1 / -1;">No upcoming events scheduled at the moment. Please check back soon!</p>
        <?php endif; ?>
      </div>
      <div style="text-align:center; margin-top: 40px;">
        <a href="events.php" class="btn">View All Events</a>
      </div>
    </div>
  </section>

  <section id="membership" class="membership" style="background-color: var(--primary-dark);">
    <div class="container">
      <h3>Become a Member</h3>
      <p>Join our vibrant chess community to access expert coaching, participate in tournaments, and connect with fellow enthusiasts. Open to all ages and skill levels!</p>
      <a href="membership.html" class="btn" style="margin-top:20px;">Learn More & Join</a>
    </div>
  </section>

  <section id="gallery" class="gallery-preview">
    <div class="container">
      <h3>From Our Gallery</h3>
      <div class="gallery-grid">
        <?php if (!empty($images)): ?>
          <?php foreach (array_slice($images, 0, 3) as $img): ?>
            <div class="gallery-item">
              <img src="gallery_uploads/<?= htmlspecialchars($img['file']) ?>" alt="<?= htmlspecialchars($img['caption'] ?: 'Chess Club Image') ?>" loading="lazy" />
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center;">Our gallery is currently empty. Photos from our events will be added soon!</p>
        <?php endif; ?>
      </div>
      <div style="text-align:center; margin-top: 40px;">
        <a href="gallery.php" class="btn">View Full Gallery</a>
      </div>
    </div>
  </section>

  <section id="contact" class="contact" style="background-color: var(--primary-dark);">
    <div class="container">
      <h3>Get In Touch</h3>
      <p><strong>Email:</strong> info@riseandshinechess.co.za</p>
      <p><strong>Phone:</strong> 071 539 9671/p>
    </div>
  </section>
</main>

<footer>
  <div class="container">
    <p>&copy; <?= date('Y') ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
  </div>
</footer>

<script>
  // Mobile Menu Toggle
  document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menu-toggle');
    const mainMenu = document.getElementById('main-menu');
    menuToggle.addEventListener('click', function () {
      mainMenu.classList.toggle('show');
    });
  });
</script>

</body>
</html>
