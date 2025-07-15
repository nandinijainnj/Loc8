<?php
session_start();
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        if ($auth->register($username, $email, $password, $phone)) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Email may already be in use.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loc8 - Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Register</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required 
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            
            <input type="email" name="email" placeholder="Email" required 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            
            <input type="password" name="password" placeholder="Password" required>
            
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <input type="text" name="phone" placeholder="Phone Number" required 
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>