<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Rise and Shine Chess Club</title>
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
                <li class="active"><a href="about.php">About</a></li>
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

    <main class="page-container">
        <section class="hero-section" style="background-image: url('chess-9536049_1920.jpg');">
            <div class="hero-text">
                <h1>About Our Club</h1>
                <p>A community built on a shared passion for chess in Pretoria.</p>
            </div>
        </section>

        <section id="our-story" class="content-section">
            <h2>Our Story</h2>
            <p class="section-intro">Established in September 2024, the Rise and Shine Chess Club is a development chess club based in Nellmaphius, Pretoria. We are officially registered with Tshwane Chess, the regional body for chess in our area. As a development club, our primary focus is on nurturing and training new and upcoming chess players, creating a strong foundation for the future of chess in our community.</p>
            <p>Our club was founded by a group of passionate players who wanted to create a welcoming space for anyone interested in chess. We believe in the power of the game to teach critical thinking, discipline, and sportsmanship. We welcome everyone, from the curious beginner picking up a piece for the first time to the seasoned player looking for a competitive challenge.</p>
        </section>

        <section id="mission-vision" class="content-section alt-bg">
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                    <p>The mission of the Rise and Shine Chess Club is to foster a vibrant and inclusive community where individuals of all ages and skill levels can discover, learn, and grow through the game of chess. We aim to cultivate critical thinking, sportsmanship, and perseverance, empowering our members to excel both on and off the chessboard.</p>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <h3><i class="fas fa-eye"></i> Our Vision</h3>
                    <p>Our vision is to be a leading hub for chess enthusiasm and development in our community, recognized for promoting intellectual growth, fostering strong social connections, and inspiring a lifelong passion for the game. We envision a future where the strategic lessons of chess empower every member to approach challenges with confidence and creativity.</p>
                </div>
            </div>
        </section>

        <section id="why-join" class="content-section">
            <h2>Why Join Us?</h2>
            <div class="coaching-programs">
                <div class="program-card">
                    <i class="fas fa-users fa-3x"></i>
                    <h3>Community</h3>
                    <p>Connect with fellow chess lovers in Nellmaphius and greater Pretoria. Make new friends and find new rivals in a supportive setting.</p>
                </div>
                <div class="program-card">
                    <i class="fas fa-trophy fa-3x"></i>
                    <h3>Development</h3>
                    <p>As a development club, our focus is on you. We provide the tools and training to help new and upcoming players flourish.</p>
                </div>
                <div class="program-card">
                    <i class="fas fa-graduation-cap fa-3x"></i>
                    <h3>Growth</h3>
                    <p>With access to experienced coaches and a community dedicated to learning, you'll have every opportunity to improve your game.</p>
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
