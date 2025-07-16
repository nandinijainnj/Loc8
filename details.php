<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/items.php';

$auth = new Auth();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$items = new Items();
$error = '';

// Get item details from URL parameters
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

if (!in_array($type, ['lost', 'found']) || !is_numeric($id)) {
    header("Location: index.php");
    exit();
}

$item = $items->getItemDetails($id, $type);
if (!$item) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$found_item_id = $id;
$found_user_id = $item['reported_by']; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loc8 - Item Details</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Item Details</h1>
        <a href="index.php">Back to Home</a>
    </header>

    <div class="item-details">
        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">
        <h2><?= htmlspecialchars($item['item_name']) ?></h2>
        <p><strong>Type:</strong> <?= ucfirst($type) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($type === 'lost' ? $item['lost_location'] : $item['found_location']) ?></p>
        <p><strong>Reported By:</strong> <?= htmlspecialchars($item['username']) ?></p>
        <p><strong>Reported On:</strong> <?= date('F j, Y', strtotime($type === 'lost' ? $item['lost_date'] : $item['found_date'])) ?></p>

        <?php if ($type === 'found'): ?>
            <button onclick="openClaimModal()" class="button">Claim This Item</button>
        <?php endif; ?>
    </div>

    <!-- Claim Modal -->
    <div id="claimModal" style="display:none; position:fixed; top:20%; left:30%; padding:20px; background:white; border:1px solid #ccc; z-index:1000;">
      <form id="claimForm" method="POST" action="process_claim.php">
        <input type="hidden" name="found_item_id" value="<?= $found_item_id ?>">
        <input type="hidden" name="found_reporter_id" value="<?= $found_user_id ?>">
        <div id="lostItemSelect"></div>
        <h4>Before claiming an item, make sure to file a Lost report about it. Do you want to proceed?</h4>
        <button type="submit">Yes, proceed</button>
        <button type="button" onclick="closeClaimModal()">Cancel</button>
      </form>
    </div>

    <script>
    const sessionUserId = <?= json_encode($user_id) ?>;
    const foundReporterId = <?= json_encode($found_user_id) ?>;

    function openClaimModal() {
        if (sessionUserId === foundReporterId) {
            alert("You cannot claim an item you reported.");
            return;
        }

        document.getElementById("claimModal").style.display = "block";

        fetch("get_lost_items.php")
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById("lostItemSelect");

                if (data.length === 1) {
                    container.innerHTML = `<input type="hidden" name="lost_item_id" value="${data[0].id}">`;
                } else if (data.length > 1) {
                    let html = '<label>Select your lost item:</label><br><select name="lost_item_id" required>';
                    data.forEach(item => {
                        html += `<option value="${item.id}">${item.name} (${item.description})</option>`;
                    });
                    html += '</select><br><br>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = "<p>You have not reported any lost items yet.</p>";
                    document.querySelector("#claimForm button[type='submit']").disabled = true;
                }
            });
    }

    function closeClaimModal() {
        document.getElementById("claimModal").style.display = "none";
    }
    </script>
</body>
</html>
