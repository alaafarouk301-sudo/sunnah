
```php
<?php

require_once __DIR__ . "/../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Check empty fields
    if ($username === "" || $email === "" || $password === "" || $confirm_password === "") {

        $error = "من فضلك املئي جميع البيانات.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "البريد الإلكتروني غير صحيح.";

    } elseif ($password !== $confirm_password) {

        $error = "كلمتا المرور غير متطابقتين.";

    } elseif (strlen($password) < 8) {

        $error = "كلمة المرور يجب أن تكون 8 أحرف على الأقل.";

    } else {

        // Check if email already exists
        $checkEmail = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $checkEmail->execute([$email]);

        if ($checkEmail->fetch()) {

            $error = "هذا البريد الإلكتروني مسجل بالفعل.";

        } else {

            // Encrypt password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $insertUser = $conn->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
            );

            $insertUser->execute([
                $username,
                $email,
                $hashedPassword
            ]);

            $success = "تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>إنشاء حساب | سُنّة</title>

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
            <li><a href="../index.html#about">عن الموقع</a></li>
        </ul>

        <div class="nav-buttons">
            <a href="login.php" class="login-btn">تسجيل الدخول</a>
        </div>

    </nav>


    <!-- Register Section -->
    <main class="auth-section">

        <div class="auth-card">

            <div class="auth-heading">
                <span>ابدأ رحلتك معنا 🌿</span>

                <h1>إنشاء حساب جديد</h1>

                <p>
                    انضم إلينا وابدأ في إحياء السنن النبوية
                    واجعلها جزءًا من حياتك اليومية.
                </p>
            </div>


            <!-- Error Message -->
            <?php if ($error !== ""): ?>
                <p style="color: red; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>


            <!-- Success Message -->
            <?php if ($success !== ""): ?>
                <p style="color: green; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($success) ?>
                </p>
            <?php endif; ?>


            <!-- Register Form -->
            <form class="auth-form" action="" method="POST">

                <div class="form-group">
                    <label for="fullname">الاسم بالكامل</label>

                    <input
                        type="text"
                        id="fullname"
                        name="username"
                        placeholder="أدخل اسمك بالكامل"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
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
                        placeholder="أنشئ كلمة مرور"
                        minlength="8"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="confirm-password">تأكيد كلمة المرور</label>

                    <input
                        type="password"
                        id="confirm-password"
                        name="confirm_password"
                        placeholder="أعد كتابة كلمة المرور"
                        minlength="8"
                        required
                    >
                </div>


                <button type="submit" class="auth-submit">
                    إنشاء الحساب
                </button>

            </form>


            <p class="auth-switch">
                لديك حساب بالفعل؟
                <a href="login.php">تسجيل الدخول</a>
            </p>

            <a href="../index.html" class="back-home">
                العودة إلى الرئيسية
            </a>

        </div>

    </main>


    <!-- Footer -->
    <footer class="footer">

        <h2>سُنّة</h2>

        <p>أحيِ سُنّة، واجعلها جزءًا من حياتك.</p>

        <div class="copyright">
            © 2026 سُنّة - جميع الحقوق محفوظة
        </div>

   