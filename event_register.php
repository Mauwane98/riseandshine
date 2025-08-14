<?php
session_start();

// --- PHPMailer Integration ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- Helper Functions ---

function getEventById($id) {
    $filePath = 'admin/data/events.csv';
    if (file_exists($filePath) && ($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (isset($data[0]) && $data[0] == $id) {
                fclose($handle);
                return ['id' => htmlspecialchars($data[0]), 'name' => htmlspecialchars($data[1]), 'date' => htmlspecialchars($data[2]), 'location' => htmlspecialchars($data[3]), 'description' => htmlspecialchars($data[4]), 'poster' => htmlspecialchars($data[5])];
            }
        }
        fclose($handle);
    }
    return null;
}

// --- NEW FEATURE: Function to check if a member is approved ---
function isMemberApproved($email) {
    $filePath = 'admin/data/registrations.csv';
    if (file_exists($filePath) && ($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV: id, name, email, ..., status, ...
            if (isset($data[2]) && strtolower(trim($data[2])) == strtolower(trim($email))) {
                if (isset($data[7]) && $data[7] == 'approved') {
                    fclose($handle);
                    return true; // Member found and is approved
                }
            }
        }
        fclose($handle);
    }
    return false; // Member not found or not approved
}


// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = isset($_POST['event_id']) ? htmlspecialchars(trim($_POST['event_id'])) : '';
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($event_id) || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Please provide a valid name and email address.";
        header("Location: event_register.php?event_id=" . urlencode($event_id));
        exit();
    }
    
    // --- Membership Verification ---
    if (!isMemberApproved($email)) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Registration Failed: Only approved members of the Rise and Shine Chess Club can register for tournaments. Please apply for a membership or wait for your application to be approved.";
        header("Location: event_register.php?event_id=" . urlencode($event_id));
        exit();
    }

    $event = getEventById($event_id);
    if (!$event) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "The event you are trying to register for does not exist.";
        header("Location: events.php");
        exit();
    }

    $file_path = 'admin/data/event_registrations.csv';
    if (($file = fopen($file_path, 'a')) !== FALSE) {
        $registration_data = [ uniqid(), $event_id, $event['name'], $name, $email, date('Y-m-d H:i:s') ];
        fputcsv($file, $registration_data);
        fclose($file);

        // --- Email Notification Logic ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'cp62.domains.co.za';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@riseandshinechess.co.za';
            $mail->Password   = 'Rise&Shine02';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Admin Notification & User Confirmation... (rest of the email code is the same)
            $mail->setFrom('info@riseandshinechess.co.za', 'Event Registration');
            $mail->addAddress('info@riseandshinechess.co.za', 'Admin');
            $mail->isHTML(true);
            $mail->Subject = 'New Event Registration: ' . $event['name'];
            $mail->Body    = "An approved member has registered for the event: <b>{$event['name']}</b>.<br><br>" .
                             "<b>Registrant Name:</b> {$name}<br>" .
                             "<b>Registrant Email:</b> {$email}<br><br>";
            $mail->send();

            $mail->clearAddresses();
            $mail->addAddress($email, $name);
            $mail->Subject = 'Confirmation: You are registered for ' . $event['name'];
            $mail->Body    = "Dear {$name},<br><br>" .
                             "Thank you for registering for our event: <b>{$event['name']}</b>.<br><br>" .
                             "We look forward to seeing you there!<br><br>" .
                             "Sincerely,<br>" .
                             "The Rise and Shine Chess Club Team";
            $mail->send();

        } catch (Exception $e) { /* Log error silently */ }

        $_SESSION['message'] = "You have been successfully registered for the event!";
        header("Location: success.php");
        exit();
    } else {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = "Error: Could not process your registration. Please try again.";
        header("Location: event_register.php?event_id=" . urlencode($event_id));
        exit();
    }
}

// --- Handle Page Load ---
$event_id = isset($_GET['event_id']) ? htmlspecialchars(trim($_GET['event_id'])) : null;
$event = $event_id ? getEventById($event_id) : null;

if (!$event) {
    $_SESSION['error'] = 1;
    $_SESSION['message'] = "The requested event could not be found.";
    if (!headers_sent()) {
        header("Location: events.php");
        exit();
    }
    $errorMessage = "Event not found. Please return to the events page.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event - Rise and Shine Chess Club</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <li class="active"><a href="events.php">Events</a></li>
                <li><a href="membership.php">Membership</a></li>
                <li><a href="coaching.php">Coaching</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-container">
        <section id="register-form" class="content-section">
            <?php if (isset($errorMessage)): ?>
                <div class="message error"><?php echo $errorMessage; ?></div>
            <?php elseif ($event): ?>
                <h2>Register for: <?php echo $event['name']; ?></h2>
                <div class="event-registration-details">
                    <div class="event-poster">
                         <img src="event_uploads/<?php echo $event['poster']; ?>" alt="<?php echo $event['name']; ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x400/333/FFF?text=Event';">
                    </div>
                    <div class="event-info">
                        <p><strong><i class="fas fa-calendar-alt"></i> Date:</strong> <?php echo date('l, F jS, Y', strtotime($event['date'])); ?></p>
                        <p><strong><i class="fas fa-map-marker-alt"></i> Location:</strong> <?php echo $event['location']; ?></p>
                        <p><strong><i class="fas fa-info-circle"></i> Details:</strong></p>
                        <p><?php echo nl2br($event['description']); ?></p>
                    </div>
                </div>

                <form action="event_register.php" method="post" class="styled-form">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo '<div class="message ' . (isset($_SESSION['error']) ? 'error' : 'success') . '">' . $_SESSION['message'] . '</div>';
                        unset($_SESSION['message']);
                        unset($_SESSION['error']);
                    }
                    ?>

                    <div class="form-group">
                        <label for="name">Full Name (as per your membership)</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Membership Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Confirm Registration</button>
                    </div>
                </form>
            <?php endif; ?>
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
</body>
</html>
