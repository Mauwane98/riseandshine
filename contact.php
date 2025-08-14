<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Rise and Shine Chess Club</title>
    <meta name="description" content="Contact the Rise and Shine Chess Club for inquiries about membership, events, coaching, or general information. We are based in Pretoria, South Africa.">
    <meta name="keywords" content="chess club, Pretoria, South Africa, chess events, chess coaching, chess membership, contact chess club, Rise and Shine Chess Club">
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
                <li><a href="events.php">Events</a></li>
                <li><a href="membership.php">Membership</a></li>
                <li><a href="coaching.php">Coaching</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li class="active"><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <main class="page-container">
        <section class="hero-section" style="background-image: url('chess-9536049_1920.jpg');">
            <div class="hero-text">
                <h1>Get in Touch</h1>
                <p>We'd love to hear from you. Ask a question or say hello!</p>
            </div>
        </section>

        <section id="contact-details" class="content-section">
            <h2>Contact Information</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                <!-- Contact Form -->
                <div style="flex: 2; min-width: 300px;">
                    <h3>Send us a Message</h3>
                     <?php
                    if (isset($_SESSION['message'])) {
                        echo '<div class="message ' . (isset($_SESSION['error']) ? 'error' : 'success') . '">' . $_SESSION['message'] . '</div>';
                        unset($_SESSION['message']);
                        unset($_SESSION['error']);
                    }
                    ?>
                    <form action="process_contact.php" method="post" class="styled-form" style="margin: 0; padding:0;">
                        <?php 
                        require_once 'honeypot.php';
                        echo HoneypotProtection::generateFields();
                        ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required maxlength="100" minlength="2">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required maxlength="255">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" value="<?php echo isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : ''; ?>" required maxlength="200" minlength="5">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" required maxlength="2000" minlength="10"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Send Message</button>
                        </div>
                    </form>
                </div>
                <!-- Contact Info & Map -->
                <div style="flex: 1; min-width: 300px;">
                    <h3>Club Details</h3>
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong><br>Nellmaphius, Pretoria, South Africa</p>
                    <p><i class="fas fa-phone"></i> <strong>Phone:</strong><br><a href="tel:+27715399671">+27 71 539 9671</a></p>
                    <p><i class="fas fa-envelope"></i> <strong>Email:</strong><br><a href="mailto:info@riseandshinechess.co.za">info@riseandshinechess.co.za</a></p>

                    <div id="map" style="height: 250px; width: 100%; border-radius: 8px; margin-top: 1rem; background: #eee;">
                       <iframe
                            width="100%"
                            height="100%"
                            style="border:0; border-radius: 8px;"
                            loading="lazy"
                            allowfullscreen
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d57499.03433328813!2d28.33235314077983!3d-25.75294340332834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1e955f560e293845%3A0x2f7f63813a44f843!2sNellmapius%2C%20Pretoria!5e0!3m2!1sen!2sza!4v1668602725555!5m2!1sen!2sza">
                        </iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="events.php">Upcoming Events</a></li>
                    <li><a href="membership.php">Join Us</a></li>
                    <li><a href="coaching.php">Coaching</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
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