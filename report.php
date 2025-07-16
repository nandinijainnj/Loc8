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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $itemName = $_POST['item_name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    
    // Validate date is not in the future
    $today = new DateTime();
    $selectedDate = new DateTime($date);
    if ($selectedDate > $today) {
        $error = "Date cannot be in the future";
    } else {
        // Handle file upload
        $imagePath = 'assets/' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            if ($items->reportItem($type, $itemName, $description, $location, $date, $imagePath, $_SESSION['user_id'])) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Failed to report item";
            }
        } else {
            $error = "Failed to upload image";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loc8 - Report Item</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Report Item</h1>
        <a href="index.php">Back to Home</a>
    </header>

    <?php if ($error): ?>
        <div class="alert"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="toggle">
            <input type="radio" name="type" value="lost" id="lost" checked>
            <label for="lost">Lost</label>
            <input type="radio" name="type" value="found" id="found">
            <label for="found">Found</label>
        </div>

        <input type="text" name="item_name" placeholder="Item Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <select name="location" required>
            <option value="">Select Location</option>
            <option value="Library">Library</option>
            <option value="TechPark 1">TechPark 1</option>
            <option value="TechPark 2">TechPark 2</option>
            <option value="University Building">University Building</option>
            <option value="Basic Engineering Lab">Basic Engineering Lab</option>
            <option value="TP Ganeshan Auditorium">TP Ganeshan Auditorium</option>
        </select>
        <input type="date" name="date" max="<?= date('Y-m-d') ?>" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Submit Report</button>
    </form>
</body>
</html>