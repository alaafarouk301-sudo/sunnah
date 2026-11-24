



<?php

require_once "auth.php";
require_once __DIR__ . "/../config/database.php";

$userId = (int) $_SESSION["user_id"];

// Get user's challenges
$query = $conn->prepare(
    "SELECT
        user_progress.*,
        sunnahs.title,
        sunnahs.description,
        categories.name AS category_name
     FROM user_progress
     INNER JOIN sunnahs
        ON user_progress.sunnah_id = sunnahs.id
     LEFT JOIN categories
        ON sunnahs.category_id = categories.id
     WHERE user_progress.user_id = ?
     ORDER BY user_progress.created_at DESC"
);

$query->execute([$userId]);
$challenges = $query->fetchAll(PDO::FETCH_ASSOC);

// Separate active and completed challenges
$activeChallenges = [];
$completedChallenges = [];

foreach ($challenges as $challenge) {
    if ($challenge["status"] === "completed") {
        $completedChallenges[] = $challenge;
    } else {
        $activeChallenges[] = $challenge;
    }
}

// Statistics
$totalDays = 0;

foreach ($challenges as $challenge) {
    if (!empty($challenge["day1_completed_at"])) {
        $totalDays++;
    }

    if (!empty($challenge["day2_completed_at"])) {
        $totalDays++;
    }
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>سنني | سُنّة</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">

        <a href="../index.html" class="logo">سُنّة</a>

        <ul class="nav-links">
            <li><a href="../index.html">الرئيسية</a></li>
            <li><a href="sunnahs.php">السنن</a></li>
            <li><a href="my-sunnahs.php">سنني</a></li>
        </ul>

        <div class="nav-buttons">
            <span>مرحبًا، <?= htmlspecialchars($_SESSION["username"]) ?> 🌿</span>

            <a href="logout.php" class="login-btn">تسجيل الخروج</a>
        </div>

    </nav>

    <!-- Page Header -->
    <section class="page-header">

        <span>خطوة صغيرة كل يوم 🌿</span>

        <h1>سنني</h1>

        <p>
            تابع رحلتك في تطبيق السنن النبوية،
            واحتفل بكل خطوة تقربك من هدي النبي ﷺ.
        </p>

    </section>

    <!-- My Sunnahs Section -->
    <section class="my-sunnahs-section">

        <!-- Statistics -->
        <div class="stats-grid">

            <div class="stat-card">
                <span class="stat-icon">🌱</span>
                <h2><?= count($activeChallenges) ?></h2>
                <p>تحديات نشطة</p>
            </div>

            <div class="stat-card">
                <span class="stat-icon">⭐</span>
                <h2><?= count($completedChallenges) ?></h2>
                <p>تحديات مكتملة</p>
            </div>

            <div class="stat-card">
                <span class="stat-icon">📅</span>
                <h2><?= $totalDays ?></h2>
                <p>أيام تطبيق</p>
            </div>

        </div>

        <!-- Active Challenges -->
        <div class="my-section-heading">
            <div>
                <span>واصل رحلتك</span>
                <h2>التحديات النشطة</h2>
            </div>
        </div>

        <div class="my-sunnahs-grid">

            <?php if (count($activeChallenges) > 0): ?>

                <?php foreach ($activeChallenges as $challenge): ?>

                    <?php
                    $completedDays = 0;

                    if (!empty($challenge["day1_completed_at"])) {
                        $completedDays++;
                    }

                    if (!empty($challenge["day2_completed_at"])) {
                        $completedDays++;
                    }

                    $progress = ($completedDays / 2) * 100;
                    ?>

                    <div class="challenge-card">

                        <div class="challenge-top">

                            <span class="sunnah-category">
                                <?= htmlspecialchars($challenge["category_name"] ?? "غير مصنف") ?>
                            </span>

                            <span class="challenge-status">
                                <?= $completedDays === 0 ? "اليوم الأول" : "اليوم الثاني" ?>
                            </span>

                        </div>

                        <h3>
                            <?= htmlspecialchars($challenge["title"]) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($challenge["description"]) ?>
                        </p>

                        <div class="challenge-progress">

                            <div class="progress-info">
                                <span>تقدم التحدي</span>
                                <span><?= $completedDays ?> من 2</span>
                            </div>

                            <div class="progress-bar">
                                <div
                                    class="progress-fill"
                                    style="width: <?= $progress ?>%;">
                                </div>
                            </div>

                        </div>

                        <?php if (empty($challenge["day1_completed_at"])): ?>

                            <p>ابدئي بتطبيق السنة اليوم 🌿</p>

                            <form action="complete-day.php" method="POST">

                                <input
                                    type="hidden"
                                    name="challenge_id"
                                    value="<?= (int) $challenge["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="day"
                                    value="1"
                                >

                                <button type="submit" class="main-btn">
                                    إتمام اليوم الأول ✓
                                </button>

                            </form>

                        <?php elseif (empty($challenge["day2_completed_at"])): ?>

                            <p>أحسنتِ! أتممتِ اليوم الأول، تابعي رحلتك 🌿</p>

                            <form action="complete-day.php" method="POST">

                                <input
                                    type="hidden"
                                    name="challenge_id"
                                    value="<?= (int) $challenge["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="day"
                                    value="2"
                                >

                                <button type="submit" class="main-btn">
                                    إتمام اليوم الثاني ✓
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p style="text-align:center; width:100%; padding:30px;">
                    لم تبدئي أي تحديات بعد. اكتشفي السنن وابدئي رحلتك 🌿
                </p>

            <?php endif; ?>

        </div>

        <!-- Completed Challenges -->
        <div class="my-section-heading completed-heading">

            <div>
                <span>إنجازاتك</span>
                <h2>التحديات المكتملة</h2>
            </div>

        </div>

        <?php if (count($completedChallenges) > 0): ?>

            <?php foreach ($completedChallenges as $challenge): ?>

                <div class="completed-card">

                    <div class="completed-icon">🏆</div>

                    <div class="completed-info">
                        <h3><?= htmlspecialchars($challenge["title"]) ?></h3>
                        <p>أحسنتِ! أتممتِ تحدي اليومين بنجاح.</p>
                    </div>

                    <span class="completed-label">مكتمل ✓</span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p style="text-align:center; padding:20px;">
                لا توجد تحديات مكتملة حتى الآن.
            </p>

        <?php endif; ?>

        <!-- Browse More -->
        <div class="browse-more">

            <p>هل تريدين إحياء المزيد من السنن؟</p>

            <a href="sunnahs.php" class="main-btn">
                اكتشفي السنن
            </a>

        </div>

    </section>

    <!-- Footer -->
    <footer class="footer">

        <h2>سُنّة</h2>

        <p>أحيِ سُنّة، واجعلها جزءًا من حياتك.</p>

        <div class="copyright">
            © 2026 سُنّة - جميع الحقوق محفوظة
        </div>

    </footer>

</body>
</html>
