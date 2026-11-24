





<?php

require_once __DIR__ . "/../config/database.php";

// Get all categories
$categoriesQuery = $conn->query(
    "SELECT id, name, icon FROM categories ORDER BY id ASC"
);
$categories = $categoriesQuery->fetchAll(PDO::FETCH_ASSOC);

// Get selected category
$selectedCategory = isset($_GET["category"])
    ? (int) $_GET["category"]
    : 0;

// Get published Sunnahs
if ($selectedCategory > 0) {
    $sunnahsQuery = $conn->prepare(
        "SELECT sunnahs.*, categories.name AS category_name
         FROM sunnahs
         LEFT JOIN categories
         ON sunnahs.category_id = categories.id
         WHERE sunnahs.status = 'published'
         AND sunnahs.category_id = ?
         ORDER BY sunnahs.id DESC"
    );

    $sunnahsQuery->execute([$selectedCategory]);

} else {
    $sunnahsQuery = $conn->query(
        "SELECT sunnahs.*, categories.name AS category_name
         FROM sunnahs
         LEFT JOIN categories
         ON sunnahs.category_id = categories.id
         WHERE sunnahs.status = 'published'
         ORDER BY sunnahs.id DESC"
    );
}

$sunnahs = $sunnahsQuery->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>السنن النبوية | سُنّة</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Image Generation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    

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
            <a href="register.php" class="signup-btn">إنشاء حساب</a>
        </div>

    </nav>


    <!-- Page Header -->
    <section class="page-header">

        <span>اقتدِ برسول الله ﷺ</span>

        <h1>اكتشف السنن النبوية</h1>

        <p>
            تعرّف على سنن النبي ﷺ في حياتك اليومية،
            واجعل تطبيقها عادة جميلة تقربك إلى الله.
        </p>

    </section>


    <!-- Sunnahs Section -->
    <section class="sunnahs-section">

        <div class="section-heading">

            <span>تصفح حسب التصنيف</span>

            <h2>اختر مجال السنة</h2>

        </div>


        <!-- Categories Filter -->
        <div class="filter-categories">

            <a
                href="sunnahs.php"
                class="filter-btn <?= $selectedCategory === 0 ? 'active' : '' ?>"
            >
                جميع السنن
            </a>

            <?php foreach ($categories as $category): ?>

                <a
                    href="sunnahs.php?category=<?= (int) $category['id'] ?>"
                    class="filter-btn <?= $selectedCategory === (int) $category['id'] ? 'active' : '' ?>"
                >
                    <?= htmlspecialchars($category['name']) ?>
                </a>

            <?php endforeach; ?>

        </div>


        <!-- Sunnah Cards -->
        <div class="sunnah-grid" id="sunnahs-list">

            <?php if (count($sunnahs) > 0): ?>

                <?php foreach ($sunnahs as $sunnah): ?>

                    <article class="flip-card">

                        <div class="flip-card-inner">


                            <!-- Front Face -->
                            <div class="flip-card-front">

                                <span class="front-category">
                                    <?= htmlspecialchars($sunnah['category_name'] ?? 'غير مصنف') ?>
                                </span>

                                <div class="front-icon">🌿</div>

                                <span class="front-label">سُنّة نبوية</span>

                                <h3>
                                    <?= htmlspecialchars($sunnah['title']) ?>
                                </h3>

                                <div class="front-divider"></div>

                                <?php if (!empty($sunnah['description'])): ?>

                                    <p class="front-description">
                                        <?= nl2br(htmlspecialchars($sunnah['description'])) ?>
                                    </p>

                                <?php endif; ?>

                                <p class="flip-hint">
                                    اضغطي لاكتشاف الحديث والفضل
                                    <span>↶</span>
                                </p>

                            </div>


                            <!-- Back Face -->
                            <div class="flip-card-back">

                                <div class="back-header">

                                    <span>الدليل من السنة النبوية</span>

                                    <span class="back-symbol">ﷺ</span>

                                </div>


                                <!-- Hadith -->
                                <?php if (!empty($sunnah['hadith_text'])): ?>

                                    <div class="hadith-box">

                                        <p>
                                            <?= nl2br(htmlspecialchars($sunnah['hadith_text'])) ?>
                                        </p>

                                    </div>

                                <?php else: ?>

                                    <p class="missing-data">
                                        لم تتم إضافة نص الحديث أو الدعاء بعد.
                                    </p>

                                <?php endif; ?>


                                <!-- Hadith Information -->
                                <div class="hadith-info">

                                    <?php if (!empty($sunnah['narrator'])): ?>

                                        <p>
                                            <strong>الراوي:</strong>

                                            <?= htmlspecialchars($sunnah['narrator']) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($sunnah['source'])): ?>

                                        <p>
                                            <strong>المصدر:</strong>

                                            <?= htmlspecialchars($sunnah['source']) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($sunnah['hadith_number'])): ?>

                                        <p>
                                            <strong>رقم الحديث:</strong>

                                            <?= htmlspecialchars($sunnah['hadith_number']) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($sunnah['hadith_grade'])): ?>

                                        <p>
                                            <strong>درجة الحديث:</strong>

                                            <?= htmlspecialchars($sunnah['hadith_grade']) ?>
                                        </p>

                                    <?php endif; ?>

                                </div>


                                <!-- Sunnah Reward -->
                                <?php if (!empty($sunnah['reward'])): ?>

                                    <div class="reward-box">

                                        <strong>فضل هذه السنة 🌿</strong>

                                        <p>
                                            <?= nl2br(htmlspecialchars($sunnah['reward'])) ?>
                                        </p>

                                    </div>

                                <?php endif; ?>


                                <!-- Card Actions -->
                                <div class="card-actions">

                                    <!-- Share Button -->
                                    <button
                                        type="button"
                                        class="share-btn"

                                        data-title="<?= htmlspecialchars($sunnah['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"

                                        data-category="<?= htmlspecialchars($sunnah['category_name'] ?? 'غير مصنف', ENT_QUOTES, 'UTF-8') ?>"

                                        data-hadith="<?= htmlspecialchars($sunnah['hadith_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>"

                                        data-narrator="<?= htmlspecialchars($sunnah['narrator'] ?? '', ENT_QUOTES, 'UTF-8') ?>"

                                        data-source="<?= htmlspecialchars($sunnah['source'] ?? '', ENT_QUOTES, 'UTF-8') ?>"

                                        data-reward="<?= htmlspecialchars($sunnah['reward'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        اجعلها أثرًا ✨
                                    </button>


                                    <!-- Challenge Button -->
                                    <form action="start-challenge.php" method="POST">

                                        <input
                                            type="hidden"
                                            name="sunnah_id"
                                            value="<?= (int) $sunnah['id'] ?>"
                                        >

                                        <button type="submit" class="challenge-btn">
                                            ابدأ تحدي اليومين
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <p style="text-align:center; width:100%; padding:30px;">
                    لا توجد سنن منشورة في هذا التصنيف حاليًا.
                </p>

            <?php endif; ?>

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


    <!-- JavaScript -->
    <script>

        // Flip Cards
        document.querySelectorAll(".flip-card").forEach(card => {

            card.addEventListener("click", function(event) {

                if (
                    event.target.closest(".challenge-btn") ||
                    event.target.closest(".share-btn")
                ) {
                    return;
                }

                this.classList.toggle("is-flipped");

            });

        });


        // Share Sunnah as Image
        document.querySelectorAll(".share-btn").forEach(button => {

            button.addEventListener("click", async function(event) {

                event.stopPropagation();

                if (typeof html2canvas === "undefined") {

                    alert("تعذر تحميل مكتبة إنشاء الصور. تأكدي من اتصال الإنترنت.");

                    return;

                }

                const originalText = this.textContent;

                this.disabled = true;

                this.textContent = "جاري تجهيز الصورة...";

                let shareCard = null;

                try {

                    const title = this.dataset.title;
                    const category = this.dataset.category;
                    const hadith = this.dataset.hadith;
                    const narrator = this.dataset.narrator;
                    const source = this.dataset.source;
                    const reward = this.dataset.reward;


                    // Create Image Design
                    shareCard = document.createElement("div");

                    shareCard.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: -2000px;
                        width: 1080px;
                        min-height: 1350px;
                        box-sizing: border-box;
                        padding: 90px;
                        direction: rtl;
                        text-align: center;
                        font-family: Cairo, sans-serif;
                        background: #F5F3E9;
                        color: #263D32;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                    `;


                    shareCard.innerHTML = `

                        <div style="
                            width:100%;
                            min-height:1160px;
                            box-sizing:border-box;
                            border:3px solid #87977A;
                            border-radius:35px;
                            padding:65px;
                            display:flex;
                            flex-direction:column;
                            align-items:center;
                            justify-content:center;
                            gap:30px;
                        ">

                            <div style="
                                font-size:65px;
                                font-weight:800;
                                color:#263D32;
                            ">
                                سُنّة 🌿
                            </div>


                            <div class="share-category" style="
                                font-size:27px;
                                color:#87977A;
                                font-weight:700;
                            "></div>


                            <div style="
                                width:120px;
                                height:4px;
                                background:#87977A;
                            "></div>


                            <h1 style="
                                font-size:48px;
                                line-height:1.7;
                                color:#263D32;
                                margin:0;
                            "></h1>


                            <div style="
                                font-size:27px;
                                color:#526B59;
                                font-weight:600;
                            ">
                                اقتدِ برسول الله ﷺ
                            </div>


                            <div style="
                                width:100%;
                                background:#E9EBDD;
                                border-radius:25px;
                                padding:35px;
                                box-sizing:border-box;
                            ">

                                <p style="
                                    font-size:31px;
                                    line-height:2;
                                    color:#263D32;
                                    margin:0;
                                    white-space:pre-line;
                                "></p>

                            </div>


                            <div class="share-info" style="
                                font-size:23px;
                                line-height:2;
                                color:#526B59;
                                white-space:pre-line;
                            "></div>


                            <div class="share-reward" style="
                                width:100%;
                                background:#87977A;
                                color:white;
                                border-radius:20px;
                                padding:25px;
                                box-sizing:border-box;
                                font-size:27px;
                                line-height:1.8;
                            "></div>


                            <div style="
                                font-size:35px;
                                font-weight:700;
                                color:#263D32;
                                margin-top:20px;
                            ">
                                شارك الأجر 🤍
                            </div>


                            <div style="
                                font-size:21px;
                                color:#87977A;
                            ">
                                أحيِ سُنّة، واجعلها جزءًا من حياتك.
                            </div>

                        </div>

                    `;


                    // Fill Image Data
                    shareCard.querySelector(".share-category").textContent = category;

                    shareCard.querySelector("h1").textContent = title;

                    shareCard.querySelector("p").textContent =
                        hadith || "لم تتم إضافة نص الحديث بعد.";


                    const info = [];

                    if (narrator) {
                        info.push("الراوي: " + narrator);
                    }

                    if (source) {
                        info.push("المصدر: " + source);
                    }

                    shareCard.querySelector(".share-info").textContent =
                        info.join("\n");


                    shareCard.querySelector(".share-reward").textContent =
                        reward
                            ? "فضل السنة: " + reward
                            : "اتباع سنة النبي ﷺ شرف وعبادة.";


                    document.body.appendChild(shareCard);


                    // Wait for fonts
                    if (document.fonts && document.fonts.ready) {
                        await document.fonts.ready;
                    }


                    // Generate Image
                    const canvas = await html2canvas(shareCard, {

                        scale: 1,

                        backgroundColor: "#F5F3E9",

                        useCORS: true

                    });


                    const blob = await new Promise(resolve => {

                        canvas.toBlob(resolve, "image/png");

                    });


                    if (!blob) {

                        throw new Error("تعذر إنشاء الصورة");

                    }


                    const file = new File(

                        [blob],

                        "sunnah-share.png",

                        { type: "image/png" }

                    );


                    // Share or Download
                    if (

                        navigator.share &&

                        navigator.canShare &&

                        navigator.canShare({ files: [file] })

                    ) {

                        await navigator.share({

                            files: [file],

                            title: title,

                            text: "شارك الأجر 🤍"

                        });

                    } else {

                        const imageURL = URL.createObjectURL(blob);

                        const downloadLink = document.createElement("a");

                        downloadLink.href = imageURL;

                        downloadLink.download = "sunnah-share.png";

                        document.body.appendChild(downloadLink);

                        downloadLink.click();

                        downloadLink.remove();

                        setTimeout(() => URL.revokeObjectURL(imageURL), 1000);

                        alert("تم تجهيز الصورة وتنزيلها بنجاح 🤍");

                    }


                } catch (error) {

                    console.error(error);

                    if (error.name !== "AbortError") {

                        alert("حصلت مشكلة أثناء تجهيز الصورة. حاولي مرة أخرى.");

                    }

                } finally {

                    if (shareCard && shareCard.parentNode) {
                        shareCard.remove();
                    }

                    this.disabled = false;

                    this.textContent = originalText;

                }

            });

        });

    </script>

</body>

</html>
