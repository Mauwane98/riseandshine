<?php
// You can add any necessary PHP logic here, like fetching coach data from a database.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coaching - Rise and Shine Chess Club</title>
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
                <li class="active"><a href="coaching.php">Coaching</a></li>
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
                <h1>Chess Coaching</h1>
                <p>Unlock your potential with our expert guidance.</p>
            </div>
        </section>

        <section id="programs" class="content-section">
            <h2>Our Coaching Programs</h2>
            <p class="section-intro">We offer a range of coaching programs tailored to different skill levels, from absolute beginners to advanced tournament players. Our goal is to provide a structured and supportive learning environment.</p>
            
            <div class="coaching-programs">
                <div class="program-card">
                    <i class="fas fa-chess-pawn fa-3x"></i>
                    <h3>Beginner's Bootcamp</h3>
                    <p>Learn the fundamentals of chess, including rules, piece movement, basic tactics, and opening principles. Perfect for those new to the game.</p>
                    <a href="contact.php?subject=Beginner's Bootcamp Inquiry" class="btn">Inquire Now</a>
                </div>
                <div class="program-card">
                    <i class="fas fa-chess-knight fa-3x"></i>
                    <h3>Intermediate Strategy</h3>
                    <p>Develop your strategic thinking, positional understanding, and tactical vision. This program focuses on middlegame planning and endgame techniques.</p>
                    <a href="contact.php?subject=Intermediate Strategy Inquiry" class="btn">Inquire Now</a>
                </div>
                <div class="program-card">
                    <i class="fas fa-chess-king fa-3x"></i>
                    <h3>Advanced Training</h3>
                    <p>For the serious tournament player. This program involves deep opening preparation, advanced tactical patterns, and personalized game analysis.</p>
                    <a href="contact.php?subject=Advanced Training Inquiry" class="btn">Inquire Now</a>
                </div>
            </div>
        </section>

        <section id="coaches" class="content-section alt-bg">
            <h2>Meet the Coach</h2>
            <p class="section-intro">Our experienced and passionate coach is here to guide you on your chess journey.</p>

            <div class="coach-profiles" style="justify-content: center;">
                <div class="coach-card">
                    <img src="https://placehold.co/200x200/EFEFEF/333?text=Coach" alt="Coach Khothatso Teddy Makoro">
                    <div class="coach-info">
                        <h3>Khothatso Teddy Makoro</h3>
                        <p class="coach-title">Head Coach & Founder</p>
                        <p>With a deep passion for developing new talent, Khothatso provides expert guidance tailored to each student's needs, helping them build a strong foundation and a love for the game.</p>
                        <a href="contact.php?subject=Booking Inquiry for Khothatso Teddy Makoro" class="btn-secondary">Book a Session</a>
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
