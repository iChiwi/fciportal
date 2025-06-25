<?php
// Auto versioning for static files | Cache fix
function auto_version($file) {
    if (file_exists($file)) {
        return $file . '?v=' . filemtime($file);
    }
    return $file;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <!-- Metadata Information -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="منصتك الشاملة للموارد والأدوات الأكاديمية لطلاب كلية الحاسبات والمعلومات" />
    <!-- Website Information -->
    <title>بوابة الطلاب — مساعد الطالب</title>
    <!-- Favicons & Stylesheets -->
    <link rel="apple-touch-icon" sizes="180x180" href="../static/img/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="../static/img/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../static/img/favicon-16x16.png" />
    <link rel="manifest" href="../static/img/site.webmanifest" />
    <link href="<?php echo auto_version('../static/css/main.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <!-- Navigation Bar (Hamburger included for mobile) -->
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
                <a href="/studentcode/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
                </a>
                <a href="/results/" class="nav-button">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <span>نتائج الطلاب</span>
                </a>
                <a href="/links/" class="nav-button">
                    <i class="fas fa-link" aria-hidden="true"></i>
                    <span>روابط الجروبات</span>
                </a>
                <a href="/admin/" class="nav-button">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>بوابة المسؤول</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Header Container -->
    <header>
        <div class="header-content">
            <h1>بوابة الطلاب</h1>
            <p>ابحث عن ملخصات المواد المختلفة التي أعدها المشرفين</p>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- File Viewer -->
            <section id="file-manager-section" class="info-section">
                <h2>
                    <i class="fas fa-file-alt"></i>
                    عارض الملفات
                </h2>
                <div id="file-browser">
                    <div class="browser-gpa">
                        <div class="form-group">
                            <select id="subject-select" name="subject" required>
                                <option value="" selected disabled>اختر المادة</option>
                            </select>
                        </div>
                    </div>
                    <div id="files-container"></div>
                </div>

                <div class="form-group">
                    <div class="form-actions">
                        <button type="button" id="back-button" class="btn btn-secondary" disabled>
                            <i class="fas fa-arrow-right"></i> العودة للخلف
                        </button>
                    </div>
                </div>

                <input type="hidden" id="current-folder" readonly>
                <input type="hidden" id="folder-path" name="folderPath" value="">
            </section>
        </div>
    </main>

    <!-- Footer Message -->
    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
    <script src="<?php echo auto_version('../static/js/student-files.js'); ?>"></script>
</body>

</html>
