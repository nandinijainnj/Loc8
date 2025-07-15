<?php
session_start();
require_once 'includes/auth.php';
$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->login($_POST['email'], $_POST['password'])) {
        header("Location: " . ($auth->isAdmin() ? "adminPanel.php" : "index.php"));
        exit();
    }
    $error = "Invalid credentials";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loc8 - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="background">
        <div class="login-container">
            <h1>Loc8</h1>
            <?php if (isset($error)): ?>
                <div class="alert"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>    
</body>
</html>
