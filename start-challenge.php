
<?php

require_once "auth.php";
require_once __DIR__ . "/../config/database.php";

// Check if sunnah ID was sent
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["sunnah_id"])) {
    header("Location: sunnahs.php");
    exit;
}

$sunnahId = (int) $_POST["sunnah_id"];
$userId = (int) $_SESSION["user_id"];

// Check if sunnah exists and is published
$checkSunnah = $conn->prepare(
    "SELECT id FROM sunnahs
     WHERE id = ? AND status = 'published'"
);
$checkSunnah->execute([$sunnahId]);

if (!$checkSunnah->fetch()) {
    header("Location: sunnahs.php");
    exit;
}

// Check if user already has an active challenge
$checkProgress = $conn->prepare(
    "SELECT id FROM user_progress
     WHERE user_id = ?
     AND sunnah_id = ?
     AND status = 'active'"
);

$checkProgress->execute([$userId, $sunnahId]);

if ($checkProgress->fetch()) {
    header("Location: my-sunnahs.php?exists=1");
    exit;
}

// Start new challenge
$insertProgress = $conn->prepare(
    "INSERT INTO user_progress
     (user_id, sunnah_id, start_date, status)
     VALUES (?, ?, CURDATE(), 'active')"
);

$insertProgress->execute([$userId, $sunnahId]);

header("Location: my-sunnahs.php?started=1");
exit;

?>
