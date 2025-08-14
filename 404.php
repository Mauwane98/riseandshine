
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Rise and Shine Chess Club</title>
    <meta name="description" content="The page you're looking for doesn't exist. Return to Rise and Shine Chess Club homepage or explore our chess programs.">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .error-container {
            text-align: center;
            padding: 4rem 2rem;
            min-height: 50vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }
        
        .chess-piece {
            font-size: 4rem;
            color: var(--secondary-color);
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="logo.png" alt="Rise and Shine Chess Club"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="membership.php">Membership</a></li>
                <li><a href="coaching.php">Coaching</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-container">
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="chess-piece">♔</div>
            <h1 class="error-message">Checkmate! Page Not Found</h1>
            <p>The page you're looking for seems to have moved off the board. Don't worry, even chess grandmasters make wrong moves sometimes!</p>
            <div style="margin-top: 2rem;">
                <a href="index.php" class="btn">Return to Homepage</a>
                <a href="events.php" class="btn btn-secondary">View Our Events</a>
            </div>
            
            <div style="margin-top: 3rem;">
                <h3>Popular Pages:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="membership.php">Join Our Chess Club</a></li>
                    <li><a href="coaching.php">Chess Coaching Programs</a></li>
                    <li><a href="events.php">Upcoming Chess Tournaments</a></li>
                    <li><a href="gallery.php">Chess Club Gallery</a></li>
                </ul>
            </div>
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
</body>
</html>
