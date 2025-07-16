<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = $_POST['claim_id'];
    $action = $_POST['action'];
    $lost_reporter_id = $_POST['lost_reporter_id'];
    $found_reporter_id = $_POST['found_reporter_id'];

    // Fetch item names for message content
    $itemDetailsStmt = $db->conn->prepare("
        SELECT li.item_name AS lost_item_name, fi.item_name AS found_item_name
        FROM Claims c
        JOIN Lost_Items li ON c.lost_item_id = li.lost_item_id
        JOIN Found_Items fi ON c.found_item_id = fi.found_item_id
        WHERE c.claim_id = :claim_id
    ");
    $itemDetailsStmt->bindParam(':claim_id', $claim_id);
    $itemDetailsStmt->execute();
    $itemDetails = $itemDetailsStmt->fetch(PDO::FETCH_ASSOC);

    $lost_item_name = $itemDetails['lost_item_name'];
    $found_item_name = $itemDetails['found_item_name'];

    // Set status and message based on action
    if (in_array($action, ['approve', 'reject'])) {
        $status = ucfirst($action); // "Approved" or "Rejected"
        $resolved_date = date('Y-m-d H:i:s');

        // Update claim status
        $updateClaimStmt = $db->conn->prepare("
            UPDATE Claims
            SET status = :status, resolved_date = :resolved_date
            WHERE claim_id = :claim_id
        ");
        $updateClaimStmt->execute([
            ':status' => $status,
            ':resolved_date' => $resolved_date,
            ':claim_id' => $claim_id
        ]);

        // Prepare messages
        if ($action === 'approve') {
            $messageToClaimant = "Your claim for the '$lost_item_name' has been approved. Kindly approach them to collect the item.";
            $messageToFoundReporter = "The other user's claim for the '$found_item_name' found by you has been approved. Kindly approach them to return the item.";
        } else {
            $messageToClaimant = "Your claim for the '$lost_item_name' has been rejected.";
            $messageToFoundReporter = "The other user's claim for the '$found_item_name' found by you has been rejected.";
        }

        // Insert message to claimant
        $insertMsgStmt = $db->conn->prepare("
            INSERT INTO Messages (sender_id, receiver_id, message_content)
            VALUES (:sender_id, :receiver_id, :message_content)
        ");
        $insertMsgStmt->execute([
            ':sender_id' => $_SESSION['user_id'],
            ':receiver_id' => $lost_reporter_id,
            ':message_content' => $messageToClaimant
        ]);

        // Insert message to found item reporter
        $insertMsgStmt->execute([
            ':sender_id' => $_SESSION['user_id'],
            ':receiver_id' => $found_reporter_id,
            ':message_content' => $messageToFoundReporter
        ]);

        // Redirect back to admin panel
        header("Location: adminPanel.php");
        exit();
    }
}
?>
