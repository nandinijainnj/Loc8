<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/items.php';
require_once 'includes/database.php';

$auth = new Auth();
$userId = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user info
$db = new Database();
$stmt = $db->conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user ? $user['username'] : 'User';

// Fetch items from items.php
$items = new Items();
$filter = $_GET['filter'] ?? 'all';
$allItems = $items->getAllItems($filter);

// Fetch messages for the user
$messageStmt = $db->conn->prepare("
    SELECT m.*, u.username AS sender_name
    FROM Messages m
    JOIN Users u ON m.sender_id = u.user_id
    WHERE m.receiver_id = ?
    ORDER BY m.sent_date DESC
");
$messageStmt->execute([$userId]);
$userMessages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Loc8 - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="header-left">
        <h1>Loc8 - Digital Lost & Found System</h1>
        <nav>
            <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All-Items</a>
            <a href="?filter=lost" class="<?= $filter === 'lost' ? 'active' : '' ?>">Lost</a>
            <a href="?filter=found" class="<?= $filter === 'found' ? 'active' : '' ?>">Found</a>
        </nav>
    </div>
    <div class="header-right">
        <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
        <a href="report.php" class="button">Report Item</a>
        <a href="logout.php" class="button logout">Logout</a>
    </div>
</header>

<div class="items-grid">
    <?php foreach ($allItems as $item): ?>
        <div class="item-card" 
             data-id="<?= htmlspecialchars($item['id'] ?? '') ?>" 
             data-type="<?= htmlspecialchars($item['type'] ?? '') ?>">
            <img src="<?= htmlspecialchars($item['image_path'] ?? '') ?>" 
                 alt="<?= htmlspecialchars($item['item_name'] ?? '') ?>">
            <h3><?= htmlspecialchars($item['item_name'] ?? 'Untitled') ?></h3>
            <p><?= htmlspecialchars($item['location'] ?? 'Unknown location') ?></p>
            <small>
                <?= isset($item['date']) ? date('M d, Y', strtotime($item['date'])) : 'Date not set' ?>
            </small>
        </div>
    <?php endforeach; ?>
</div>

<!--  Message Dashboard Section -->
<button class="notification-toggle" onclick="toggleMessages()"> Messages</button>
<section id="message-panel" class="message-dashboard">
    <h2>Notification Dashboard</h2>
    <?php if (count($userMessages) > 0): ?>
        <div class="messages-container">
            <?php foreach ($userMessages as $message): ?>
                <div class="message-card">
                    <strong>From: <?= htmlspecialchars($message['sender_name']) ?></strong>
                    <p><?= htmlspecialchars($message['message_content']) ?></p>
                    <small><?= date('M d, Y h:i A', strtotime($message['sent_date'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-messages">You have no messages yet.</p>
    <?php endif; ?>
</section>


<script src="js/script.js"></script>
</body>
</html>
