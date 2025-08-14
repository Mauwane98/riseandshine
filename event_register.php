<?php
// event_register.php

session_start();
$events_file = __DIR__ . '/admin/data/events.csv';
$registrations_file = __DIR__ . '/admin/data/event_registrations.csv';
$event_id = $_GET['event_id'] ?? null;
$event_details = null;
$message = '';
$error = '';

// --- Helper to find the specific event ---
function get_event_details($filePath, $id) {
    if (!$id || !file_exists($filePath)) return null;
    if (($handle = fopen($filePath, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 6) {
                $current_event_id = md5($data[0] . $data[1] . $data[2]);
                if ($current_event_id === $id) {
                    fclose($handle);
                    return [
                        'id' => $id, 'title' => $data[0], 'date' => $data[1], 'time' => $data[2],
                        'venue' => $data[3], 'description' => $data[4], 'flyer' => $data[5]
                    ];
                }
            }
        }
        fclose($handle);
    }
    return null;
}

// --- Find the event to display ---
$event_details = get_event_details($events_file, $event_id);

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $posted_event_id = $_POST['event_id'] ?? '';

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($posted_event_id)) {
        $error = "Please provide a valid name and email address.";
    } else {
        // Save the registration
        $header = ['EventID', 'EventTitle', 'RegistrantName', 'RegistrantEmail', 'Timestamp'];
        $data = [$posted_event_id, $event_details['title'], $name, $email, date('Y-m-d H:i:s')];

        if (!file_exists($registrations_file)) {
            $handle = fopen($registrations_file, 'w');
            fputcsv($handle, $header);
            fclose($handle);
        }

        $handle = fopen($registrations_file, 'a');
        fputcsv($handle, $data);
        fclose($handle);

        // Redirect to a success page (or show a success message)
        header('Location: success.php?type=event');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event | Rise and Shine Chess Club</title>
    <style>
        :root {
            --primary-dark: #0d1321; --secondary-dark: #1d2d44; --accent: #fca311;
            --text-light: #e5e5e5; --error: #e74c3c;
            --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body { font-family: var(--font-main); background-color: var(--secondary-dark); color: var(--text-light); line-height: 1.7; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        header { background-color: var(--primary-dark); padding: 15px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        header .container { display: flex; justify-content: space-between; align-items: center; }
        .logo img { height: 50px; }
        .card { background: var(--primary-dark); padding: 30px; border-radius: 10px; margin-top: 40px; }
        h2, h3 { color: var(--accent); }
        h2 { text-align: center; font-size: 2.5rem; margin-bottom: 10px; }
        h3 { font-size: 1.5rem; margin-bottom: 20px; }
        .event-details span { display: block; font-weight: 600; margin-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 12px; background: #2a3a50; border: 1px solid #445; border-radius: 6px; color: var(--text-light); font-size: 1rem; box-sizing: border-box; }
        .btn { display: inline-block; padding: 12px 28px; background-color: var(--accent); color: var(--primary-dark); text-decoration: none; font-weight: 700; border-radius: 30px; border: none; cursor: pointer; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .message.error { background-color: var(--error); color: #fff; }
        .message.info { background-color: #3498db; color: #fff; }
        footer { text-align: center; padding: 20px 0; margin-top: 40px; border-top: 2px solid var(--accent); }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo"><img src="logo.png" alt="Rise and Shine Chess Club Logo"></a>
            <a href="events.php" class="btn">Back to Events</a>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="card">
                <?php if ($event_details): ?>
                    <h2>Register for Event</h2>
                    <h3><?= htmlspecialchars($event_details['title']) ?></h3>
                    <div class="event-details">
                        <span>📅 <?= date('F j, Y', strtotime($event_details['date'])) ?> at <?= date('g:i A', strtotime($event_details['time'])) ?></span>
                        <span>📍 <?= htmlspecialchars($event_details['venue']) ?></span>
                    </div>

                    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                    <form action="event_register.php?event_id=<?= htmlspecialchars($event_id) ?>" method="POST">
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">
                        <div class="form-group">
                            <label for="name">Your Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn">Confirm Registration</button>
                    </form>
                <?php else: ?>
                    <div class="message error">
                        <h2>Event Not Found</h2>
                        <p>The event you are trying to register for could not be found. It may have been removed or the link is incorrect.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
        </div>
    </footer>
</body>
</html>
