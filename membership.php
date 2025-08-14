<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership - Rise and Shine Chess Club</title>
    <meta name="description" content="Join the Rise and Shine Chess Club in Nellmapius, Pretoria. Learn about our membership benefits, fees, and how to become part of our growing community.">
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

        /* Page-specific styles adapted from your design */
        .membership-content {
            max-width: 850px;
            margin: 0 auto;
            background-color: #fff;
            padding: 2rem 3rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }
        .membership-content h2, .membership-content h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 1rem;
        }
        .membership-content h2 {
            text-align: center;
            border-bottom: none;
            font-size: 2.5rem;
        }
        .membership-content h3 {
            font-size: 1.8rem;
            margin-top: 2rem;
        }
        .membership-content p {
            margin-bottom: 1rem;
        }
        .benefits-list {
            list-style: none;
            padding-left: 0;
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }
        .benefits-list li {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            border-left: 4px solid var(--secondary-color);
            color: var(--dark-text);
        }
        .fees-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: left;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .fees-table th, .fees-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .fees-table thead {
            background-color: var(--primary-color);
            color: var(--light-text);
            font-size: 1.2rem;
        }
        .fees-table thead th {
             color: var(--secondary-color);
        }
        .fees-table tbody tr:last-child td {
            border-bottom: none;
        }
        .fees-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .cta {
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="logo.png" alt="Rise and Shine Chess Club Logo"></a>
        </div>
        <nav id="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="events.php">Events</a></li>
                <li class="active"><a href="membership.php">Membership</a></li>
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
        <section class="membership-content">
            <h2>Become a Member</h2>
            <p style="text-align: center; font-size: 1.1rem;">Join Rise and Shine Chess Club and become part of a growing chess community dedicated to development, strategy, and lifelong learning.</p>
            
            <h3>Who Can Join?</h3>
            <p>Everyone is welcome — from absolute beginners to experienced players. We offer coaching, tournaments, and a development-focused environment for all ages and skill levels.</p>
            
            <h3>Membership Benefits</h3>
            <ul class="benefits-list">
              <li>Weekly chess training sessions</li>
              <li>Access to internal club tournaments</li>
              <li>Discounts on regional tournament entries</li>
              <li>Expert coaching and mentorship</li>
              <li>Access to chess books and resources</li>
              <li>A fun and supportive chess community</li>
            </ul>
            
            <h3>Membership Fees</h3>
            <table class="fees-table">
              <thead>
                <tr>
                  <th>Age Group</th>
                  <th>Annual Joining Fee</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Under 10 Years</td>
                  <td>R100</td>
                </tr>
                <tr>
                  <td>10 – 16 Years</td>
                  <td>R150</td>
                </tr>
                <tr>
                  <td>17+ / Adults</td>
                  <td>R200</td>
                </tr>
              </tbody>
            </table>
            
            <div class="cta">
              <a href="join.php" class="btn">Register Now</a>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="events.php">Upcoming Events</a></li>
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
            <p>&copy; <?php echo date("Y"); ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
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
