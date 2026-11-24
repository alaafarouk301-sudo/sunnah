

<?php

require_once "auth.php";

$username = $_SESSION["username"];

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>لوحة المستخدم | سُنّة</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <nav class="navbar">

        <a href="../index.html" class="logo">سُنّة</a>

        <ul class="nav-links">
            <li><a href="../index.html">الرئيسية</a></li>
            <li><a href="sunnahs.php">السنن</a></li>
            <li><a href="my-sunnahs.php">سنني</a></li>
        </ul>

        <div class="nav-buttons">
            <span>مرحبًا، <?= htmlspecialchars($username) ?> 🌿</span>

            <a href="logout.php" class="login-btn">
                تسجيل الخروج
            </a>
        </div>

    </nav>

    <main class="auth-section">

        <div class="auth-card">

            <div class="auth-heading">

                <span>أهلًا بعودتك 🌿</span>

                <h1>
                    مرحبًا <?= htmlspecialchars($username) ?>
                </h1>

                <p>
                    ابدأ يومك بإحياء سنة نبوية جديدة،
                    واجعل اتباع سنة النبي ﷺ جزءًا من حياتك.
                </p>

            </div>

            <a href="sunnahs.php" class="auth-submit" style="display:block; text-align:center;">
                اكتشف السنن النبوية
            </a>

            <br>

            <a href="my-sunnahs.php" class="auth-submit" style="display:block; text-align:center;">
                تابع سنني وتحدياتي
            </a>

        </div>

    </main>

    <footer class="footer">

        <h2>سُنّة</h2>

        <p>أحيِ سُنّة، واجعلها جزءًا من حياتك.</p>

        <div class="copyright">
            © 2026 سُنّة - جميع الحقوق محفوظة
        </div>

    </footer>

</body>
</html>
