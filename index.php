<?php
// Function to get the latest events
function getLatestEvents($limit = 3) {
    $filePath = 'admin/data/events.csv';
    $events = [];
    if (file_exists($filePath) && ($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 5 && strtotime($data[2]) >= time()) { // Only future events
                $events[] = [
                    'id' => htmlspecialchars($data[0]),
                    'name' => htmlspecialchars($data[1]),
                    'date' => htmlspecialchars($data[2]),
                    'location' => htmlspecialchars($data[3]),
                    'poster' => htmlspecialchars($data[5] ?? 'default.jpg')
                ];
            }
        }
        fclose($handle);
    }
    // Sort by date, soonest first
    usort($events, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    return array_slice($events, 0, $limit);
}

// Function to get gallery images
function getGalleryImages($limit = 4) {
    $filePath = 'admin/data/gallery.csv';
    $images = [];
    if (file_exists($filePath) && ($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
             if (count($data) >= 3) {
                $images[] = [
                    'id' => htmlspecialchars($data[0]),
                    'filename' => htmlspecialchars($data[1]),
                    'caption' => htmlspecialchars($data[2])
                ];
            }
        }
        fclose($handle);
    }
    // Shuffle and pick a few random ones
    shuffle($images);
    return array_slice($images, 0, $limit);
}

$latestEvents = getLatestEvents();
$galleryImages = getGalleryImages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rise and Shine Chess Club - Nellmaphius, Pretoria</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="description" content="Welcome to the Rise and Shine Chess Club in Nellmaphius, Pretoria. A development chess club registered with Tshwane Chess.">
    <meta name="keywords" content="chess club, Pretoria chess, Nellmaphius chess, chess tournaments, learn chess, development chess">
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
                <li class="active"><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="events.php">Events</a></li>
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

    <main>
        <section class="hero-section" style="background-image: url('chess-9536049_1920.jpg');">
            <div class="hero-text">
                <h1>Rise and Shine Chess Club</h1>
                <p>Nurturing Pretoria's Future Champions in Nellmaphius</p>
                <a href="membership.php" class="btn">Join Today</a>
                <a href="events.php" class="btn btn-secondary">View Events</a>
            </div>
        </section>

        <div class="page-container">
            <section id="mission" class="content-section alt-bg">
                <h2>Our Mission</h2>
                <p class="section-intro">The mission of the Rise and Shine Chess Club is to foster a vibrant and inclusive community where individuals of all ages and skill levels can discover, learn, and grow through the game of chess. We aim to cultivate critical thinking, sportsmanship, and perseverance, empowering our members to excel both on and off the chessboard.</p>
            </section>

            <section id="upcoming-events" class="content-section">
                <h2>Upcoming Events</h2>
                <?php if (!empty($latestEvents)): ?>
                    <div class="event-grid">
                        <?php foreach ($latestEvents as $event): ?>
                            <div class="event-card">
                                <div class="event-poster">
                                    <img src="event_uploads/<?php echo $event['poster']; ?>" alt="<?php echo $event['name']; ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x400/333/FFF?text=Event';">
                                </div>
                                <div class="event-details">
                                    <h3 class="event-title"><?php echo $event['name']; ?></h3>
                                    <p class="event-date"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                                    <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?></p>
                                    <a href="event_register.php?event_id=<?php echo $event['id']; ?>" class="btn">Learn More & Register</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center;">No new events are scheduled at the moment. Please check back soon!</p>
                <?php endif; ?>
            </section>

            <section id="home-gallery" class="content-section alt-bg">
                <h2>From Our Gallery</h2>
                 <?php if (!empty($galleryImages)): ?>
                    <div class="gallery-grid">
                        <?php foreach ($galleryImages as $image): ?>
                            <div class="gallery-item">
                                <img src="gallery_uploads/<?php echo $image['filename']; ?>" alt="<?php echo $image['caption']; ?>">
                                <div class="overlay"><h4><?php echo $image['caption']; ?></h4></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 2rem;">
                         <a href="gallery.php" class="btn">View Full Gallery</a>
                    </div>
                <?php else: ?>
                     <p style="text-align: center;">The gallery is currently empty. Photos from our events will appear here soon!</p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="events.php">Upcoming Events</a></li>
                    <li><a href="membership.php">Join Us</a></li>
                    <li><a href="coaching.php">Coaching</a></li>
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
