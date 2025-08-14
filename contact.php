<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Us | Rise and Shine Chess Club</title>
  <meta name="description" content="Get in touch with the Rise and Shine Chess Club. Send us a message, or find our email and phone number.">
  <style>
    /* --- General Styling & Variables --- */
    :root {
      --primary-dark: #0d1321;
      --secondary-dark: #1d2d44;
      --accent: #fca311;
      --text-light: #e5e5e5;
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
    h2 { font-size: 2.5rem; text-align: center; margin-bottom: 20px; color: var(--accent); }
    .intro-text { text-align: center; font-size: 1.1rem; margin-bottom: 50px; max-width: 700px; margin-left: auto; margin-right: auto; }

    /* --- Header & Navigation --- */
    header {
      background-color: var(--primary-dark);
      padding: 15px 0;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }
    
    .logo img {
  height: 50px; /* Adjust this value to make the logo bigger or smaller */
  width: auto;  /* This keeps the logo's proportions correct */
  display: block; /* Helps prevent extra space below the image */
}

    header .container { display: flex; justify-content: space-between; align-items: center; }
    .logo { font-size: 1.5rem; color: var(--accent); text-decoration: none; }
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

    /* --- Contact Page Specifics --- */
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 50px;
        align-items: start;
    }
    .contact-info {
        background: var(--primary-dark);
        padding: 30px;
        border-radius: 8px;
    }
    .contact-info h3 {
        color: var(--accent);
        margin-top: 0;
        margin-bottom: 20px;
    }
    .contact-info p {
        margin-bottom: 15px;
        font-size: 1.1rem;
    }
    .contact-info strong {
        color: var(--accent);
    }

    /* --- Form Styling --- */
    .contact-form { display: grid; gap: 20px; }
    .contact-form label { font-weight: 700; }
    .contact-form input, .contact-form textarea {
        width: 100%;
        padding: 12px;
        background: #2a3a50;
        border: 1px solid #445;
        border-radius: 6px;
        color: var(--text-light);
        font-size: 1rem;
    }
    .contact-form textarea { resize: vertical; min-height: 120px; }
    .contact-form button {
      display: inline-block;
      padding: 14px 28px;
      background-color: var(--accent);
      color: var(--primary-dark);
      font-weight: 700;
      border-radius: 30px;
      border: none;
      font-size: 1.1rem;
      cursor: pointer;
      justify-self: start;
    }

    /* --- Footer --- */
    footer {
      background-color: var(--primary-dark);
      text-align: center;
      padding: 20px 0;
      margin-top: 40px;
      border-top: 2px solid var(--accent);
    }

    /* --- Responsive Design --- */
    @media (max-width: 900px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
      h2 { font-size: 2rem; }
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
    }
  </style>
</head>
<body>

<header>
  <div class="container">
    <!-- Updated Logo HTML -->
    <a href="index.php" class="logo">
      <img src="logo.png" alt="Rise and Shine Chess Club Logo">
    </a>
    
    <nav>
      <button id="menu-toggle" aria-label="Open Menu">&#9776;</button>
      <ul id="main-menu">
        <li><a href="index.php">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="membership.html">Membership</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="contact.php" class="active">Contact</a></li>
      </ul>
    </nav>
  </div>
</header>

<main>
  <section class="contact">
    <div class="container">
      <h2>Get In Touch</h2>
      <p class="intro-text">If you have any questions, would like to join the club, or need more information, please don't hesitate to reach out using the form below or our contact details.</p>
      
      <div class="contact-grid">
        <div class="contact-info">
          <h3>Contact Details</h3>
          <p><strong>Email:</strong><br> info@riseandshinechess.co.za</p>
          <p><strong>Phone:</strong><br> 071 539 9671</p>
          <p><strong>Location:</strong><br> Nellmapius, Pretoria</p>
        </div>

        <form class="contact-form" action="process_contact.php" method="POST">
          <div>
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" required placeholder="Enter your full name">
          </div>
          <div>
            <label for="email">Your Email</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email address">
          </div>
          <div>
            <label for="message">Your Message</label>
            <textarea id="message" name="message" rows="5" required placeholder="Type your question or message here..."></textarea>
          </div>
          <button type="submit">Send Message</button>
        </form>
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