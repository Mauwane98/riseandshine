<?php
$password_to_hash = '';
$generated_hash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $password_to_hash = $_POST['password'];
    // Generate a secure hash for the provided password
    $generated_hash = password_hash($password_to_hash, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <style>
        :root {
            --primary-dark: #0d1321;
            --secondary-dark: #1d2d44;
            --accent: #fca311;
            --text-light: #e5e5e5;
            --success: #2ecc71;
            --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            font-family: var(--font-main);
            background-color: var(--secondary-dark);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .generator-container {
            background-color: var(--primary-dark);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        .generator-container h2 {
            color: var(--accent);
            margin-bottom: 15px;
        }
        .generator-container p {
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            background: #2a3a50;
            border: 1px solid #445;
            border-radius: 6px;
            color: var(--text-light);
            font-size: 1rem;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background-color: var(--accent);
            color: var(--primary-dark);
            font-weight: 700;
            border-radius: 30px;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .hash-result {
            margin-top: 30px;
            background-color: var(--secondary-dark);
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            border-left: 4px solid var(--success);
        }
        .hash-result h3 {
            margin-top: 0;
            color: var(--success);
        }
        .hash-result code {
            display: block;
            background: #000;
            padding: 15px;
            border-radius: 6px;
            word-wrap: break-word; /* Break long strings */
            white-space: pre-wrap; /* Preserve whitespace and wrap */
            font-family: 'Courier New', Courier, monospace;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="generator-container">
        <h2>Password Hash Generator</h2>
        <p>Enter a new password below to generate a secure hash. You can use this hash in the <code>users.csv</code> file when manually creating a new user.</p>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="text" id="password" name="password" required value="<?= htmlspecialchars($password_to_hash) ?>">
            </div>
            <button type="submit" class="btn">Generate Hash</button>
        </form>

        <?php if ($generated_hash): ?>
            <div class="hash-result">
                <h3>Generated Hash:</h3>
                <code><?= htmlspecialchars($generated_hash) ?></code>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
