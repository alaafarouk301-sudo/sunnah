

<?php

require_once "auth.php";
require_once __DIR__ . "/../config/database.php";

// Admin protection
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

// CSRF protection
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$error = "";

// Handle form actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request");
    }

    $action = $_POST["action"] ?? "";

    // Add or update sunnah
    if ($action === "save") {

        $id = (int) ($_POST["id"] ?? 0);
        $title = trim($_POST["title"] ?? "");
        $categoryId = (int) ($_POST["category_id"] ?? 0);
        $description = trim($_POST["description"] ?? "");
        $hadithText = trim($_POST["hadith_text"] ?? "");
        $narrator = trim($_POST["narrator"] ?? "");
        $source = trim($_POST["source"] ?? "");
        $hadithNumber = trim($_POST["hadith_number"] ?? "");
        $hadithGrade = trim($_POST["hadith_grade"] ?? "");
        $reward = trim($_POST["reward"] ?? "");
        $image = trim($_POST["image"] ?? "");
        $status = $_POST["status"] ?? "draft";

        if (!in_array($status, ["published", "draft"], true)) {
            $status = "draft";
        }

        if ($title === "" || $description === "" || $hadithText === "" || $source === "") {
            $error = "من فضلك املئي الحقول المطلوبة.";
        } else {

            try {

                if ($categoryId > 0) {
                    $categoryCheck = $conn->prepare(
                        "SELECT id FROM categories WHERE id = ?"
                    );
                    $categoryCheck->execute([$categoryId]);

                    if (!$categoryCheck->fetch()) {
                        throw new Exception("التصنيف غير موجود.");
                    }
                } else {
                    $categoryId = null;
                }

                if ($id > 0) {

                    $update = $conn->prepare(
                        "UPDATE sunnahs SET
                            category_id = ?,
                            title = ?,
                            description = ?,
                            hadith_text = ?,
                            narrator = ?,
                            source = ?,
                            hadith_number = ?,
                            hadith_grade = ?,
                            reward = ?,
                            image = ?,
                            status = ?
                         WHERE id = ?"
                    );

                    $update->execute([
                        $categoryId,
                        $title,
                        $description,
                        $hadithText,
                        $narrator,
                        $source,
                        $hadithNumber,
                        $hadithGrade,
                        $reward,
                        $image,
                        $status,
                        $id
                    ]);

                    header("Location: admin.php?success=updated");
                    exit;

                } else {

                    $insert = $conn->prepare(
                        "INSERT INTO sunnahs
                        (
                            category_id,
                            title,
                            description,
                            hadith_text,
                            narrator,
                            source,
                            hadith_number,
                            hadith_grade,
                            reward,
                            image,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    $insert->execute([
                        $categoryId,
                        $title,
                        $description,
                        $hadithText,
                        $narrator,
                        $source,
                        $hadithNumber,
                        $hadithGrade,
                        $reward,
                        $image,
                        $status
                    ]);

                    header("Location: admin.php?success=added");
                    exit;
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    // Delete sunnah
    if ($action === "delete") {

        $id = (int) ($_POST["id"] ?? 0);

        $delete = $conn->prepare(
            "DELETE FROM sunnahs WHERE id = ?"
        );

        $delete->execute([$id]);

        header("Location: admin.php?success=deleted");
        exit;
    }
}

// Success messages
if (isset($_GET["success"])) {
    $messages = [
        "added" => "تمت إضافة السنة بنجاح 🌿",
        "updated" => "تم تعديل السنة بنجاح 🌿",
        "deleted" => "تم حذف السنة بنجاح."
    ];

    $message = $messages[$_GET["success"]] ?? "";
}

// Get categories
$categoriesQuery = $conn->query(
    "SELECT id, name FROM categories ORDER BY id ASC"
);
$categories = $categoriesQuery->fetchAll(PDO::FETCH_ASSOC);

// Get sunnah for editing
$editSunnah = null;

if (isset($_GET["edit"])) {
    $editId = (int) $_GET["edit"];

    $editQuery = $conn->prepare(
        "SELECT * FROM sunnahs WHERE id = ?"
    );
    $editQuery->execute([$editId]);
    $editSunnah = $editQuery->fetch(PDO::FETCH_ASSOC);
}

// Statistics
$totalSunnahs = $conn->query(
    "SELECT COUNT(*) FROM sunnahs"
)->fetchColumn();

$publishedSunnahs = $conn->query(
    "SELECT COUNT(*) FROM sunnahs WHERE status = 'published'"
)->fetchColumn();

$draftSunnahs = $conn->query(
    "SELECT COUNT(*) FROM sunnahs WHERE status = 'draft'"
)->fetchColumn();

$totalUsers = $conn->query(
    "SELECT COUNT(*) FROM users WHERE role = 'user'"
)->fetchColumn();

// Search
$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $listQuery = $conn->prepare(
        "SELECT sunnahs.*, categories.name AS category_name
         FROM sunnahs
         LEFT JOIN categories ON sunnahs.category_id = categories.id
         WHERE sunnahs.title LIKE ?
         ORDER BY sunnahs.id DESC"
    );

    $listQuery->execute(["%$search%"]);

} else {

    $listQuery = $conn->query(
        "SELECT sunnahs.*, categories.name AS category_name
         FROM sunnahs
         LEFT JOIN categories ON sunnahs.category_id = categories.id
         ORDER BY sunnahs.id DESC"
    );
}

$sunnahs = $listQuery->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>لوحة التحكم | سُنّة</title>

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
    </ul>

    <div class="nav-buttons">
        <span>مرحبًا، <?= htmlspecialchars($_SESSION["username"]) ?></span>
        <a href="logout.php" class="login-btn">تسجيل الخروج</a>
    </div>

</nav>

<section class="page-header">

    <span>إدارة المحتوى</span>

    <h1>لوحة تحكم المسؤول</h1>

    <p>
        إدارة السنن النبوية وإضافة محتوى جديد ومتابعة المكتبة.
    </p>

</section>

<section class="admin-section">

    <?php if ($message !== ""): ?>
        <p style="text-align:center; color:green; padding:15px;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p style="text-align:center; color:red; padding:15px;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="admin-stats">

        <div class="admin-stat-card">
            <div class="admin-stat-icon">📚</div>
            <h3><?= $totalSunnahs ?></h3>
            <p>إجمالي السنن</p>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon">✅</div>
            <h3><?= $publishedSunnahs ?></h3>
            <p>سنن منشورة</p>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon">📝</div>
            <h3><?= $draftSunnahs ?></h3>
            <p>مسودات</p>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon">👥</div>
            <h3><?= $totalUsers ?></h3>
            <p>المستخدمون</p>
        </div>

    </div>

    <!-- Add / Edit Form -->
    <div class="admin-form-card">

        <div class="admin-title">

            <h2>
                <?= $editSunnah ? "تعديل السنة" : "إضافة سنة جديدة" ?>
            </h2>

            <p>أدخل بيانات السنة النبوية.</p>

        </div>

        <form method="POST" action="admin.php" class="admin-form">

            <input type="hidden" name="action" value="save">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <input type="hidden" name="id"
                   value="<?= (int) ($editSunnah["id"] ?? 0) ?>">

            <div class="admin-form-grid">

                <div class="form-group">
                    <label>عنوان السنة</label>

                    <input type="text" name="title"
                           value="<?= htmlspecialchars($editSunnah["title"] ?? "") ?>"
                           placeholder="مثال: التسمية قبل الطعام"
                           required>
                </div>

                <div class="form-group">
                    <label>التصنيف</label>

                    <select name="category_id">

                        <option value="0">اختر التصنيف</option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= (int) $category["id"] ?>"
                                <?= (int) ($editSunnah["category_id"] ?? 0) === (int) $category["id"] ? "selected" : "" ?>
                            >
                                <?= htmlspecialchars($category["name"]) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group full-width">
                    <label>وصف السنة</label>

                    <textarea name="description" rows="3" required><?= htmlspecialchars($editSunnah["description"] ?? "") ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>نص الحديث</label>

                    <textarea name="hadith_text" rows="4" required><?= htmlspecialchars($editSunnah["hadith_text"] ?? "") ?></textarea>
                </div>

                <div class="form-group">
                    <label>الراوي</label>

                    <input type="text" name="narrator"
                           value="<?= htmlspecialchars($editSunnah["narrator"] ?? "") ?>">
                </div>

                <div class="form-group">
                    <label>المصدر</label>

                    <input type="text" name="source"
                           value="<?= htmlspecialchars($editSunnah["source"] ?? "") ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>رقم الحديث</label>

                    <input type="text" name="hadith_number"
                           value="<?= htmlspecialchars($editSunnah["hadith_number"] ?? "") ?>">
                </div>

                <div class="form-group">
                    <label>درجة الحديث</label>

                    <select name="hadith_grade">

                        <option value="">اختر الدرجة</option>

                        <?php
                        $grades = ["صحيح", "حسن", "صحيح لغيره", "حسن لغيره"];
                        foreach ($grades as $grade):
                        ?>

                            <option
                                value="<?= $grade ?>"
                                <?= ($editSunnah["hadith_grade"] ?? "") === $grade ? "selected" : "" ?>
                            >
                                <?= $grade ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group full-width">
                    <label>فضل السنة</label>

                    <textarea name="reward" rows="3"><?= htmlspecialchars($editSunnah["reward"] ?? "") ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>رابط الصورة (اختياري)</label>

                    <input type="text" name="image"
                           value="<?= htmlspecialchars($editSunnah["image"] ?? "") ?>">
                </div>

            </div>

            <div class="admin-form-actions">

                <button type="submit" name="status" value="published"
                        class="admin-publish-btn">
                    <?= $editSunnah ? "حفظ التعديلات والنشر" : "نشر السنة" ?>
                </button>

                <button type="submit" name="status" value="draft"
                        class="admin-draft-btn">
                    حفظ كمسودة
                </button>

            </div>

        </form>

        <?php if ($editSunnah): ?>
            <p style="text-align:center; margin-top:15px;">
                <a href="admin.php">إلغاء التعديل</a>
            </p>
        <?php endif; ?>

    </div>

    <!-- Existing Sunnahs -->
    <div class="admin-list-section">

        <div class="admin-list-heading">

            <div>
                <h2>السنن الموجودة</h2>
                <p>إدارة المحتوى المنشور والمسودات.</p>
            </div>

            <form method="GET" action="admin.php">
                <input
                    type="search"
                    name="search"
                    class="admin-search"
                    placeholder="ابحث عن سنة..."
                    value="<?= htmlspecialchars($search) ?>">
            </form>

        </div>

        <div class="admin-table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>عنوان السنة</th>
                        <th>التصنيف</th>
                        <th>المصدر</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($sunnahs) > 0): ?>

                        <?php foreach ($sunnahs as $sunnah): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($sunnah["title"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($sunnah["category_name"] ?? "غير مصنف") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($sunnah["source"] ?? "") ?>
                                </td>

                                <td>

                                    <?php if ($sunnah["status"] === "published"): ?>
                                        <span class="status-published">منشورة</span>
                                    <?php else: ?>
                                        <span class="status-draft">مسودة</span>
                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="admin.php?edit=<?= (int) $sunnah["id"] ?>"
                                            class="edit-btn">
                                            تعديل
                                        </a>

                                        <form method="POST"
                                              action="admin.php"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه السنة؟');">

                                            <input type="hidden" name="action" value="delete">

                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                                            <input type="hidden" name="id"
                                                   value="<?= (int) $sunnah["id"] ?>">

                                            <button type="submit" class="delete-btn">
                                                حذف
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" style="text-align:center;">
                                لا توجد سنن لعرضها.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

<footer class="footer">

    <h2>سُنّة</h2>

    <p>أحيِ سُنّة، واجعلها أسلوب حياة.</p>

    <p class="copyright">
        © 2026 سُنّة. جميع الحقوق محفوظة.
    </p>

</footer>

</body>
</html>
