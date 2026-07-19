<?php
require_once 'db.php';
$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Tafadhali ingiza barua pepe na nywila.';
    } else {
        $conn = db_connect();
        $stmt = $conn->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($userId, $hash);

        if ($stmt->fetch() && password_verify($password, $hash)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            header('Location: chat.php');
            exit;
        }

        $error = 'Barua pepe au nywila si sahihi.';
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MarkTechHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo"><img src="image2/MY LOGO.png" alt="MarkTechHub logo" class="logo"></div>
        <div class="menu-toggle" onclick="toggleMenu()">&#9776;</div>
        <nav>
            <ul id="nav-links">
                <li><a href="index.html" data-key="home">home</a></li>
                <li><a href="about.html" data-key="about">about us</a></li>
                <li><a href="port.html" data-key="portfolio">Portfolio</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="contact.html" data-key="contact">Contact</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-hero" style="min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 420px; width: 100%; padding: 32px; background: rgba(15, 23, 42, 0.95); border-radius: 24px; box-shadow: 0 24px 80px rgba(0,0,0,.25);">
            <h1 style="margin-bottom: 18px;">Ingia</h1>
            <?php if ($error): ?>
                <div style="margin-bottom: 18px; padding: 14px 18px; background: rgba(248, 113, 113, 0.16); border-radius: 14px; color: #fee2e2;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php" style="display: grid; gap: 14px;">
                <input type="email" name="email" placeholder="Barua pepe" required>
                <input type="password" name="password" placeholder="Neno la siri" required>
                <button type="submit" class="btn cta-button">Ingia</button>
            </form>
            <p style="margin-top: 18px; color: rgba(241,245,249,0.82);">Huna akaunti? <a href="register.php" style="color: #ff7e1b;">Sajili hapa</a></p>
        </div>
    </main>
</body>
</html>
