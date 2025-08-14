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
        
        /* Join Page Specific Styles */
        .payment-info {
            background: #fff;
            padding: 1.5rem 2rem;
            border-radius: 8px;
            margin: 2rem 0;
            border-left: 5px solid var(--secondary-color);
            box-shadow: var(--shadow);
        }
        .payment-info h4 {
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        .payment-info ul {
            list-style: none;
            padding-left: 0;
        }
        .payment-info li {
            margin-bottom: 8px;
        }
        .error-summary {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #f5c6cb;
        }
        .error-summary ul {
            margin: 0;
            padding-left: 20px;
        }
        .form-group.checkbox-label {
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }
        .form-group.checkbox-label input {
            width: auto;
        }
        .form-group.checkbox-label label {
            margin-bottom: 0;
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
    <section class="content-section">
        <h2>Join Rise and Shine Chess Club</h2>
        <p class="section-intro">Complete the form below to become a member and unlock access to coaching, tournaments, and a passionate chess community!</p>

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
        <form class="styled-form" id="joinForm" action="process_registration.php" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" required placeholder="E.g., John Doe" value="<?= htmlspecialchars($oldData['fullName'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="5" max="99" required placeholder="Enter your current age" value="<?= htmlspecialchars($oldData['age'] ?? '') ?>">
                <p id="feeMessage" style="color: var(--secondary-color); font-weight: 600; margin-top: 8px; min-height: 1.2em;"></p>
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
            
            <button type="submit" class="btn">Submit Registration</button>
        </form>
    </section>
</main>

    <footer class="footer-bottom">
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
             <p>&copy; <?= date('Y') ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
        </div>
    </footer>

    <script src="join.js"></script>
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
