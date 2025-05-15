<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>روابط الجروبات — مساعد الطالب</title>
    <meta name="description" content="منصتك الشاملة للموارد والأدوات الأكاديمية لطلاب كلية الحاسبات والمعلومات" />
    <?php 
    require_once '../config.php';
    
    function auto_version($file) {
        if (file_exists($file)) {
            return $file . '?v=' . filemtime($file);
        }
        return $file;
    }
    ?>
    <link href="<?php echo auto_version('../static/css/main.css'); ?>" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="../static/img/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="../static/img/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../static/img/favicon-16x16.png" />
    <link rel="manifest" href="../static/img/site.webmanifest" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <nav class="nav-container">
        <div class="nav-content">
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="nav-links" id="nav-links">
                <a href="/" class="nav-button">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>الصفحة الرئيسية</span>
                </a>
                <a href="/student/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/studentcode/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
                </a>
                <a href="/results/" class="nav-button">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <span>نتائج الطلاب</span>
                </a>
                <a href="/admin/" class="nav-button">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>بوابة المسؤول</span>
                </a>
            </div>
        </div>
    </nav>

    <header>
        <div class="header-content">
            <h1>روابط الجروبات</h1>
            <p>ابحث عن روابط الجروبات عن طريق اختيار الفئة المناسبة</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section id="group-section">
                <h2>
                    <i class="fas fa-users"></i>
                    روابط الجروبات
                </h2>
                <div class="group-links">
                    <select id="category-select" class="group-select" required>
                        <option value="" selected disabled>
                            اختر الفئة
                        </option>
                        <option value="official">الروابط الرسمية</option>
                        <option value="section">روابط السكاشن</option>
                        <option value="subject">روابط المواد</option>
                    </select>

                    <select id="section-group-select" class="group-select" style="display: none" required>
                        <option value="" selected disabled>
                            اختر المجموعة
                        </option>
                        <option value="A1">مجموعة A1</option>
                        <option value="A2">مجموعة A2</option>
                        <option value="A3">مجموعة A3</option>
                        <option value="A4">مجموعة A4</option>
                        <option value="A5">مجموعة A5</option>
                    </select>
                    <div id="links-container"></div>
                </div>
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
    <script src="<?php echo auto_version('../static/js/links.js'); ?>"></script>
    <script>
    const subjectData = {
        or: {
            name: "بحوث العمليات",
            links: [],
        },
        dld: {
            name: "تصميم المنطق الرقمي",
            links: [],
        },
        prog2: {
            name: "البرمجة II",
            links: [],
        },
        math2: {
            name: "الرياضيات II",
            links: [],
        },
        discrete: {
            name: "التراكيب المحددة",
            links: [],
        },
        ethics: {
            name: "الأخلاقيات",
            links: [],
        },
        hr: {
            name: "حقوق الإنسان",
            links: [],
        },
    };

    var officialLinks = [];
    const sectionRanges = {
        A1: {
            start: 1,
            end: 6
        },
        A2: {
            start: 7,
            end: 12
        },
        A3: {
            start: 13,
            end: 18
        },
        A4: {
            start: 19,
            end: 25
        },
        A5: {
            start: 26,
            end: 32
        },
    };
    </script>
</body>

</html>
