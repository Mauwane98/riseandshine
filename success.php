<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Application Submitted | Rise and Shine Chess Club</title>
  <style>
    :root {
      --primary-dark: #0d1321;
      --secondary-dark: #1d2d44;
      --accent: #fca311;
      --text-light: #e5e5e5;
      --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-main);
      background-color: var(--secondary-dark);
      color: var(--text-light);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      text-align: center;
      padding: 20px;
    }
    .success-container {
      background: var(--primary-dark);
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.5);
      max-width: 600px;
      width: 100%;
    }
    .logo-wrapper {
        margin-bottom: 25px;
    }
    .logo-wrapper img {
        height: 60px;
        width: auto;
    }
    h2 {
      color: var(--accent);
      margin-bottom: 15px;
      font-size: 2rem;
    }
    p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 10px;
    }
    .btn {
      display: inline-block;
      margin-top: 25px;
      padding: 12px 28px;
      background-color: var(--accent);
      color: var(--primary-dark);
      text-decoration: none;
      font-weight: 700;
      border-radius: 30px;
      transition: transform 0.3s;
    }
    .btn:hover {
        transform: translateY(-3px);
    }
    footer {
      position: absolute;
      bottom: 0;
      width: 100%;
      padding: 20px;
      font-size: 0.9rem;
      color: rgba(229, 229, 229, 0.7);
    }
  </style>
</head>
<body>
  
  <main class="success-container">
    <div class="logo-wrapper">
      <a href="index.php" title="Back to Homepage">
        <img src="logo.png" alt="Rise and Shine Chess Club Logo">
      </a>
    </div>
    <h2>🎉 Application Submitted!</h2>
    <p>Thank you for registering with Rise & Shine Chess Club.</p>
    <p>We have received your application and will review it once your joining fee payment is confirmed.</p>
    <p>Please keep a copy of your proof of payment for your records.</p>
    <a href="index.php" class="btn">Return to Home</a>
  </main>

  <footer>
    <p>&copy; <?= date('Y') ?> Rise and Shine Chess Club | Designed by Mauwane Legacy Collective</p>
  </footer>

</body>
</html>