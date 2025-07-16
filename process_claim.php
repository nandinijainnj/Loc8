<?php
session_start();
include 'includes/database.php';

$db = new Database();
$conn = $db->conn;

if (!isset($_SESSION['user_id'])) {
    echo "User not authenticated.";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_claim'])) {
    $found_item_id = $_POST['found_item_id'];
    $lost_item_id = $_POST['lost_item_id'];
    $claim_details = $_POST['claim_details'];

    if (empty($found_item_id) || empty($lost_item_id) || empty($claim_details)) {
        $_SESSION['notification'] = "All fields are required.";
        header("Location: process_claim.php");
        exit;
    }

    $sql = "INSERT INTO Claims (user_id, found_item_id, lost_item_id, claim_details)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $found_item_id, PDO::PARAM_INT);
    $stmt->bindValue(3, $lost_item_id, PDO::PARAM_INT);
    $stmt->bindValue(4, $claim_details, PDO::PARAM_STR);
    $stmt->execute();

    $_SESSION['notification'] = "Claim submitted successfully! You’ll be notified once the admin reviews it.";
    header("Location: process_claim.php");
    exit;
} else {
    $found_item_id = $_POST['found_item_id'] ?? null;

    $sql = "SELECT lost_item_id, item_name, description FROM Lost_Items WHERE reported_by = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $lostItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($lostItems) === 0) {
        echo "<script>alert('You can only claim an item after reporting it as a lost item.'); window.location.href = 'index.php';</script>";
        exit;        
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Submit a Claim</title>
        <link rel="stylesheet" href="css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    </head>
    <body>
    <header class="header">
        <h1 class="logo">Claim Submission</h1>
        <a href="index.php" class="button">Back to Home</a>
    </header>

    <main class="form-center-container">
        <form method="POST" action="process_claim.php" class="form-box">
            <input type="hidden" name="found_item_id" value="<?= htmlspecialchars($found_item_id); ?>">

            <label for="lost_item_id">Select your lost item:</label>
            <select name="lost_item_id" required>
                <option value="">-- Choose your lost item --</option>
                <?php foreach ($lostItems as $item): ?>
                    <option value="<?= $item['lost_item_id']; ?>">
                        <?= htmlspecialchars($item['item_name'] . " - " . $item['description']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="claim_details">Claim Details:</label>
            <textarea name="claim_details" rows="4" placeholder="Describe your claim..." required></textarea>

            <button type="submit" name="submit_claim" class="button">Submit Claim</button>
        </form>

        <?php if (isset($_SESSION['notification'])): ?>
            <div class="notification success" id="notificationBox">
                <span><?= $_SESSION['notification']; ?></span>
                <button onclick="document.getElementById('notificationBox').style.display='none';" class="button small outline">&times;</button>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>
    </main>
    </body>
    </html>

<?php } ?>
