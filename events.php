<?php
function getEvents() {
    $filePath = 'admin/data/events.csv';
    $events = [];
    if (file_exists($filePath)) {
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            fgetcsv($handle); // Skip header row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Assuming CSV structure: id,event_name,event_date,location,description,poster
                if (count($data) >= 6) {
                    $eventDate = strtotime($data[2]);
                    // Only add future or recent events (e.g., within the last month)
                    if ($eventDate >= strtotime('-1 month')) {
                         $events[] = [
                            'id' => htmlspecialchars($data[0]),
                            'name' => htmlspecialchars($data[1]),
                            'date' => htmlspecialchars($data[2]),
                            'location' => htmlspecialchars($data[3]),
                            'description' => htmlspecialchars($data[4]),
                            'poster' => htmlspecialchars($data[5])
                        ];
                    }
                }
            }
            fclose($handle);
        }
    }
    // Sort events by date, newest first
    usort($events, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    return $events;
}

$allEvents = getEvents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Rise and Shine Chess Club</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles for mobile navigation toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
        }

        @media (max-width: 820px) {
            header nav ul {
                display: none;
                flex-direction: column;
                width: 100%;
                text-align: center;
                background-color: #4a4a4a;
                position: absolute;
                top: 70px; /* Adjust based on header height */
                left: 0;
                padding: 1rem 0;
                z-index: 999;
            }

            header nav.nav-active ul {
                display: flex;
            }

            .menu-toggle {
                display: block;
            }
            
            header {
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="logo.png" alt="Rise and Shine Chess Club"></a>
        </div>
        <nav id="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li class="active"><a href="events.php">Events</a></li>
                <li><a href="membership.php">Membership</a></li>
                <li><a href="coaching.php">Coaching</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <main class="page-container">
        <section class="hero-section" style="background-image: url('chess-9536049_1920.jpg');">
            <div class="hero-text">
                <h1>Upcoming Events</h1>
                <p>Join us for tournaments, workshops, and social gatherings.</p>
            </div>
        </section>

        <section id="event-list" class="content-section">
            <h2>Our Schedule</h2>
            <p class="section-intro">Here are the latest events from the Rise and Shine Chess Club. Click on an event to see more details or to register.</p>
            
            <div class="event-grid">
                <?php if (empty($allEvents)): ?>
                    <div class="no-events">
                        <p>There are no upcoming events scheduled at this time. Please check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allEvents as $event): ?>
                        <div class="event-card">
                            <div class="event-poster">
                                <img src="event_uploads/<?php echo $event['poster']; ?>" alt="<?php echo $event['name']; ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x400/333/FFF?text=Event';">
                            </div>
                            <div class="event-details">
                                <h3 class="event-title"><?php echo $event['name']; ?></h3>
                                <p class="event-date"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?></p>
                                <p class="event-description"><?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                <a href="event_register.php?event_id=<?php echo $event['id']; ?>" class="btn">Register Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="membership.php">Join Us</a></li>
                    <li><a href="coaching.php">Coaching</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-phone"></i> <a href="tel:+27715399671">+27 71 539 9671</a></p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:info@riseandshinechess.co.za">info@riseandshinechess.co.za</a></p>
                <p><i class="fas fa-map-marker-alt"></i> Nellmaphius, Pretoria, SA</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Rise and Shine Chess Club (Est. 2024). All Rights Reserved.</p>
        </div>
    </footer>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mainNav = document.getElementById('main-nav');

        if (menuToggle && mainNav) {
            menuToggle.addEventListener('click', () => {
                mainNav.classList.toggle('nav-active');
            });
        }
    </script>
</body>
</html>
