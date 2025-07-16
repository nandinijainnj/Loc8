<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$db = new Database();

// Fetch pending claims
$claimsStmt = $db->conn->prepare("
    SELECT c.claim_id, c.claim_details, c.claim_date, c.status, 
           li.item_name AS lost_item_name, li.image_path AS lost_image, li.reported_by AS lost_reporter_id,
           fi.item_name AS found_item_name, fi.image_path AS found_image, fi.reported_by AS found_reporter_id
    FROM Claims c
    JOIN Lost_Items li ON c.lost_item_id = li.lost_item_id
    JOIN Found_Items fi ON c.found_item_id = fi.found_item_id
    WHERE c.status = 'Pending'
");
$claimsStmt->execute();
$pendingClaims = $claimsStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Loc8</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel - Pending Claims</h1>
        <a href="logout.php" class="button logout">Logout</a>
    </header>

    <main class="admin-container">
        <?php if (count($pendingClaims) > 0): ?>
            <?php foreach ($pendingClaims as $claim): ?>
                <div class="claim-card">
                    <h2>Claim for <?= htmlspecialchars($claim['found_item_name']) ?> by <?= htmlspecialchars($claim['lost_item_name']) ?></h2>
                    <div class="item-section">
                        <div>
                            <h3>Lost Item</h3>
                            <img src="<?= htmlspecialchars($claim['lost_image']) ?>" alt="Lost Item">
                            <p><?= htmlspecialchars($claim['lost_item_name']) ?></p>
                        </div>
                        <div>
                            <h3>Found Item</h3>
                            <img src="<?= htmlspecialchars($claim['found_image']) ?>" alt="Found Item">
                            <p><?= htmlspecialchars($claim['found_item_name']) ?></p>
                        </div>
                    </div>
                    <p><strong>Claim Details:</strong> <?= htmlspecialchars($claim['claim_details']) ?></p>
                    <form method="POST" action="processClaim.php">
                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                        <input type="hidden" name="lost_reporter_id" value="<?= $claim['lost_reporter_id'] ?>">
                        <input type="hidden" name="found_reporter_id" value="<?= $claim['found_reporter_id'] ?>">
                        <button name="action" value="approve" class="approve">Approve</button>
                        <button name="action" value="reject" class="reject">Reject</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No pending claims right now!</p>
        <?php endif; ?>
    </main>
</body>
</html>
