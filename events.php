<?php
/**
 * Loads and categorizes events from a CSV file.
 * @param string $filePath Path to the events.csv file.
 * @return array An array containing two keys: 'upcoming' and 'past'.
 */
function get_categorized_events(string $filePath): array {
    $upcoming_events = [];
    $past_events = [];

    if (!file_exists($filePath)) {
        return ['upcoming' => [], 'past' => []];
    }

    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if (!$header) { fclose($handle); return []; }

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 6) {
                $event = [
                    'title'       => $data[0],
                    'date'        => $data[1],
                    'time'        => $data[2],
                    'venue'       => $data[3],
                    'description' => $data[4],
                    'flyer'       => $data[5]
                ];
                // Generate a unique ID for linking
                $event['id'] = md5($event['title'] . $event['date'] . $event['time']);

                if (strtotime($event['date']) >= strtotime('today')) {
                    $upcoming_events[] = $event;
                } else {
                    $past_events[] = $event;
                }
            }
        }
        fclose($handle);
    }

    usort($upcoming_events, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
    usort($past_events, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

    return ['upcoming' => $upcoming_events, 'past' => $past_events];
}

$all_events = get_categorized_events(__DIR__ . '/admin/data/events.csv');
$upcoming_events = $all_events['upcoming'];
$past_events = $all_events['past'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Club Events | Rise and Shine Chess Club</title>
  <meta name="description" content="View all upcoming and past events for the Rise and Shine Chess Club. Join us for tournaments, workshops, and community chess nights.">
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
    .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 20px;
    }
    section { padding: 60px 0; }
    h2 { font-size: 2.5rem; text-align: center; margin-bottom: 40px; color: var(--accent); }
    
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

    .event-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }
    .event-card {
      background: var(--primary-dark);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
      display: flex;
      flex-direction: column;
    }
    .event-card h4 {
      color: var(--accent);
      margin-bottom: 15px;
      font-size: 1.5rem;
    }
    .event-card .event-details {
      font-weight: 700;
      margin-bottom: 15px;
      color: #ccc;
    }
    .event-card .event-details span {
        display: block;
        margin-bottom: 5px;
    }
    .event-card .event-flyer {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .event-card .register-btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: var(--accent);
        color: var(--primary-dark);
        text-decoration: none;
        font-weight: 700;
        border-radius: 20px;
        transition: transform 0.3s;
        text-align: center;
        margin-top: auto; /* Pushes button to the bottom */
    }
    .event-card .register-btn:hover { transform: translateY(-3px); }
    .event-card.past-event { opacity: 0.7; background: #2a3a50; }
    .past-events-section { margin-top: 80px; padding-top: 60px; border-top: 1px solid #445; }

    footer {
      background-color: var(--primary-dark); text-align: center;
      padding: 20px 0; margin-top: 40px; border-top: 2px solid var(--accent);
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
        <li><a href="events.php" class="active">Events</a></li>
        <li><a href="membership.html">Membership</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
    </nav>
  </div>
</header>

<main>
  <section class="events-page">
    <div class="container">
      <h2>Upcoming Events</h2>
      <div class="event-grid">
        <?php if (!empty($upcoming_events)): ?>
          <?php foreach ($upcoming_events as $event): ?>
            <div class="event-card">
              <?php if (!empty($event['flyer'])): ?>
                <img src="event_uploads/<?= htmlspecialchars($event['flyer']) ?>" alt="<?= htmlspecialchars($event['title']) ?> Flyer" class="event-flyer">
              <?php endif; ?>
              <h4><?= htmlspecialchars($event['title']) ?></h4>
              <div class="event-details">
                  <span>📅 <?= date('F j, Y', strtotime($event['date'])) ?></span>
                  <span>⏰ <?= date('g:i A', strtotime($event['time'])) ?></span>
                  <span>📍 <?= htmlspecialchars($event['venue']) ?></span>
              </div>
              <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
              <a href="event_register.php?event_id=<?= htmlspecialchars($event['id']) ?>" class="register-btn">Register Now</a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; grid-column: 1 / -1;">No upcoming events are scheduled right now. Please check back soon for updates!</p>
        <?php endif; ?>
      </div>

      <div class="past-events-section">
        <h2>Past Events</h2>
        <!-- Past events section remains the same -->
      </div>
    </div>
  </section>
</main>

<footer>
  <div class="container">
    <p>&copy; <?= date('Y') ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
  </div>
</footer>

<script>
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
