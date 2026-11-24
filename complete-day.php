<?php

require_once "auth.php";
require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my-sunnahs.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$challengeId = (int) ($_POST["challenge_id"] ?? 0);
$day = (int) ($_POST["day"] ?? 0);

// Get challenge and make sure it belongs to this user
$query = $conn->prepare(
    "SELECT * FROM user_progress
     WHERE id = ? AND user_id = ?"
);

$query->execute([$challengeId, $userId]);
$challenge = $query->fetch(PDO::FETCH_ASSOC);

if (!$challenge || $challenge["status"] === "completed") {
    header("Location: my-sunnahs.php");
    exit;
}

// Complete day one
if ($day === 1 && empty($challenge["day1_completed_at"])) {

    $update = $conn->prepare(
        "UPDATE user_progress
         SET day1_completed_at = NOW()
         WHERE id = ? AND user_id = ?"
    );

    $update->execute([$challengeId, $userId]);
}

// Complete day two only after day one
elseif (
    $day === 2 &&
    !empty($challenge["day1_completed_at"]) &&
    empty($challenge["day2_completed_at"])
) {

    $update = $conn->prepare(
        "UPDATE user_progress
         SET day2_completed_at = NOW(),
             status = 'completed'
         WHERE id = ? AND user_id = ?"
    );

    $update->execute([$challengeId, $userId]);
}

header("Location: my-sunnahs.php");
exit;

?>