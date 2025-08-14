<?php
session_start();

// Get errors and old data from the session if they exist
$errors = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['old_form_data'] ?? [];

// Clear them from the session so they don't reappear on refresh
unset($_SESSION['form_errors'], $_SESSION['old_form_data']);

// Generate a CSRF token for the form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Join Us | Rise and Shine Chess Club</title>
  <meta name="description" content="Register to become a member of the Rise and Shine Chess Club. Fill out the form and join our passionate chess community in Nellmapius, Pretoria.">
  <style>
    /* --- General Styling & Variables --- */
    :root {
      --primary-dark: #0d1321;
      --secondary-dark: #1d2d44;
      --accent: #fca311;
      --text-light: #e5e5e5;
      --error: #f44336;
      --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-main);
      line-height: 1.7;
      background-color: var(--secondary-dark);
      color: var(--text-light);
    }
    .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
    section { padding: 60px 0; }
    h2, h3 { color: var(--accent); }
    h2 { font-size: 2.5rem; text-align: center; margin-bottom: 20px; }
    h3 { font-size: 1.8rem; margin-top: 40px; margin-bottom: 15px; }

    /* --- Header & Navigation --- */
    header { background-color: var(--primary-dark); padding: 15px 0; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.5); }
    header .container { display: flex; justify-content: space-between; align-items: center; }
    .logo img { height: 50px; width: auto; display: block; }
    nav ul { list-style: none; display: flex; gap: 25px; }
    nav a { color: var(--text-light); text-decoration: none; font-weight: 600; padding-bottom: 5px; border-bottom: 2px solid transparent; transition: color 0.3s, border-color 0.3s; }
    nav a:hover, nav a.active { color: var(--accent); border-bottom-color: var(--accent); }
    #menu-toggle { display: none; }

    /* --- Join Page Specifics --- */
    .join-content { max-width: 800px; margin: 0 auto; }
    .join-content > p { text-align: center; font-size: 1.1rem; margin-bottom: 40px; }
    .payment-info { background: var(--primary-dark); padding: 25px; border-radius: 8px; margin: 30px 0; border-left: 5px solid var(--accent); }
    .payment-info h4 { font-size: 1.5rem; margin-top: 0; margin-bottom: 15px; }
    .payment-info ul { list-style: none; padding-left: 0; }
    .payment-info li { margin-bottom: 8px; }

    /* --- Form & Error Styling --- */
    .error-summary { background: var(--error); color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
    .error-summary ul { margin: 0; padding-left: 20px; }
    .join-form { margin-top: 30px; display: grid; gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .join-form label { font-weight: 700; margin-bottom: 5px; }
    .join-form input, .join-form select { width: 100%; padding: 12px; background: #2a3a50; border: 1px solid #445; border-radius: 6px; color: var(--text-light); font-size: 1rem; }
    .join-form .checkbox-label { flex-direction: row; align-items: center; gap: 10px; font-weight: normal; }
    .join-form input[type="checkbox"] { width: auto; accent-color: var(--accent); transform: scale(1.2); }
    .join-form button { display: inline-block; padding: 14px 28px; background-color: var(--accent); color: var(--primary-dark); font-weight: 700; border-radius: 30px; border: none; font-size: 1.1rem; cursor: pointer; margin-top: 20px; justify-self: start; }
    .error-message { color: var(--error); font-weight: 600; font-size: 0.9rem; margin-top: 5px; }

    /* --- Footer --- */
    footer { background-color: var(--primary-dark); text-align: center; padding: 20px 0; margin-top: 40px; border-top: 2px solid var(--accent); }

    /* --- Responsive Design --- */
    @media (max-width: 768px) {
      nav ul { display: none; flex-direction: column; position: absolute; top: 70px; right: 0; background: var(--primary-dark); width: 100%; padding: 20px 0; text-align: center; }
      nav ul.show { display: flex; }
      #menu-toggle { display: block; background: none; border: none; color: var(--text-light); font-size: 2rem; cursor: pointer; }
    }
  </style>
  <script src="join.js" defer></script>
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
        <li><a href="events.php">Events</a></li>
        <li><a href="membership.html" class="active">Membership</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
    </nav>
  </div>
</header>

<main>
  <section class="join">
    <div class="container">
      <div class="join-content">
        <h2>Join Rise and Shine Chess Club</h2>
        <p>Complete the form below to become a member and unlock access to coaching, tournaments, and a passionate chess community!</p>

        <?php if (!empty($errors)): ?>
          <div class="error-summary">
            <strong>Please correct the following errors:</strong>
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="payment-info">
          <h4>Joining Fee Payment Instructions</h4>
          <p>A once-off joining fee is required. Please make payment via EFT / Bank Transfer to:</p>
          <ul>
            <li><strong>Account Number:</strong> 1052515711</li>
            <li><strong>Bank Name:</strong> CAPITEC BUSINESS</li>
            <li><strong>Reference:</strong> Your Full Name</li>
          </ul>
          <p>Amount: <strong>R100</strong> (Under 10), <strong>R150</strong> (10–16), or <strong>R200</strong> (17+).</p>
        </div>
        
        <h3>Registration Form</h3>
        <form class="join-form" id="joinForm" action="process_registration.php" method="POST" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" name="fullName" required placeholder="E.g., John Doe" value="<?= htmlspecialchars($oldData['fullName'] ?? '') ?>">
          </div>
          
          <div class="form-group">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" min="5" max="99" required placeholder="Enter your current age" value="<?= htmlspecialchars($oldData['age'] ?? '') ?>">
            <p id="feeMessage" style="color: var(--accent); font-weight: 600; margin-top: 8px; min-height: 1.2em;"></p>
          </div>
          
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="E.g., your.email@example.com" value="<?= htmlspecialchars($oldData['email'] ?? '') ?>">
          </div>
          
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="Optional" value="<?= htmlspecialchars($oldData['phone'] ?? '') ?>">
          </div>
          
          <div class="form-group">
            <label for="experience">Chess Experience</label>
            <select id="experience" name="experience" required>
              <option value="">-- Please select one --</option>
              <option value="beginner" <?= (($oldData['experience'] ?? '') === 'beginner') ? 'selected' : '' ?>>Beginner (Just starting out)</option>
              <option value="intermediate" <?= (($oldData['experience'] ?? '') === 'intermediate') ? 'selected' : '' ?>>Intermediate (Play casually)</option>
              <option value="advanced" <?= (($oldData['experience'] ?? '') === 'advanced') ? 'selected' : '' ?>>Advanced (Play in tournaments)</option>
            </select>
          </div>
          
          <div class="form-group checkbox-label">
            <input type="checkbox" id="joiningFee" name="joiningFee" required <?= isset($oldData['joiningFee']) ? 'checked' : '' ?>>
            <label for="joiningFee">I understand I must pay the joining fee for my application to be approved.</label>
          </div>
          
          <div class="form-group">
            <label for="proof">Upload Proof of Payment (PDF, PNG, JPG)</label>
            <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
          </div>
          
          <div class="form-group">
            <label for="digitalSignature">Digital Signature (Type Your Full Name)</label>
            <input type="text" id="digitalSignature" name="digitalSignature" required placeholder="Type your full name again to sign" value="<?= htmlspecialchars($oldData['digitalSignature'] ?? '') ?>">
          </div>
          
          <button type="submit">Submit Registration</button>
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
    menuToggle.addEventListener('click', () => mainMenu.classList.toggle('show'));
  });
</script>

</body>
</html>