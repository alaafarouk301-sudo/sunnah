
```php
<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Check empty fields
    if ($email === "" || $password === "") {

        $error = "من فضلك أدخل البريد الإلكتروني وكلمة المرور.";

    } else {

        // Find user by email
        $stmt = $conn->prepare(
            "SELECT id, username, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && password_verify($password, $user["password"])) {

            // Save user information in session
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            // Redirect based on role
            if ($user["role"] === "admin") {

                header("Location: admin.php");
                exit;

            } else {

                header("Location: dashboard.php");
                exit;
            }

        } else {

            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>تسجيل الدخول | سُنّة</title>

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
            <li><a href="../index.html#about">عن الموقع</a></li>
        </ul>

        <div class="nav-buttons">
            <a href="register.php" class="signup-btn">إنشاء حساب</a>
        </div>
    </nav>


    <main class="auth-section">

        <div class="auth-card">

            <div class="auth-heading">
                <span>مرحبًا بعودتك 🌿</span>
                <h1>تسجيل الدخول</h1>
                <p>سجّل دخولك وواصل رحلتك في إحياء السنن النبوية.</p>
            </div>


            <!-- Error Message -->
            <?php if ($error !== ""): ?>

                <p style="color: red; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </p>

            <?php endif; ?>


            <form class="auth-form" action="" method="POST">

                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="أدخل بريدك الإلكتروني"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="password">كلمة المرور</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="أدخل كلمة المرور"
                        required
                    >
                </div>


                <button type="submit" class="auth-submit">
                    تسجيل الدخول
                </button>

            </form>


            <p class="auth-switch">
                ليس لديك حساب؟
                <a href="register.php">إنشاء حساب جديد</a>
            </p>

            <a href="../index.html" class="back-home">العودة إلى الرئيسية</a>

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
```